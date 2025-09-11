<?php

class Controller_Projects extends Controller_Hybrid
{
    public $template = 'template';
    protected $current_user;
    protected $format = 'json';

    public function before()
    {
        parent::before();

        if (\Session::get('user_id')) {
            $user_id = \Session::get('user_id');
        } elseif (\Cookie::get('user_id')) {
            $user_id = \Cookie::get('user_id');
            \Session::set('user_id', $user_id);
        } else {
            $user_id = null;
        }

        $this->current_user = $user_id ? \Model_User::find($user_id) : null;
        if (!$this->current_user) \Response::redirect('auth/login');
        if (!$this->is_restful()) {
            $this->template->js = 'main.js';
            $this->template->css = 'projects.css';
        }
    }

    public function action_index()
    {
        $this->format = 'html';
        
        $this->template->title = 'プロジェクト - あみぷろ';
        $available_filters = \Model_Project::get_available_filters($this->current_user->id);
        $available_yarn = \Model_Yarn::get_user_yarn($this->current_user->id, true);

        $selected_filters = [
            'types'      => [],
            'techniques' => [],
        ];

        $data = [
            'available_filters' => $available_filters,
            'selected_filters' => $selected_filters,
            'available_yarn' => $available_yarn['yarn'],
            'current_tab' => 'projects',
            'search_query' => '',
        ];

        return $this->response(View::forge('projects/index', $data));
    }

    public function action_yarn()
    {
        $this->format = 'html';
        
        $this->template->title = '毛糸 - あみぷろ';
        $available_filters = \Model_Yarn::get_available_filters();
        $projects_data = \Model_Project::get_user_projects($this->current_user->id);

        $selected_filters = [
            'weight'      => [],
            'fiber' => [],
        ];

        $data = [
            'available_filters' => $available_filters,
            'selected_filters' => $selected_filters,
            'available_projects' => $projects_data['projects'],
            'current_tab' => 'yarn',
            'search_query' => '',
        ];

        return $this->response(View::forge('projects/yarn', $data));
    }

    public function action_data()
    {
        $projects_data = \Model_Project::get_user_projects($this->current_user->id);

        return $this->response([
            'success' => true,
            'projects' => $projects_data['projects']
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function action_yarn_data()
    {
        $yarn_data = \Model_Yarn::get_user_yarn($this->current_user->id);

        return $this->response([
            'success' => true,
            'yarn' => $yarn_data['yarn']
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function post_create()
    {   
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $item_type = \Input::post('item_type');

        if ($item_type === 'project') {

            $val = \Validation::forge();
            $val->add('name', 'プロジェクト名')->add_rule('required');
            $val->add('object_type', 'プロジェクトタイプ')->add_rule('required');
            if ($val->run()) {
                $project_id = \Model_Project::create_project(
                    $this->current_user,
                    \Input::post()
                );
                if ($project_id) {
                    return $this->response(['success' => true, 'project_id' => $project_id]);
                }
            }
            return $this->response(['success' => false, 'errors' => $val->error_message()], 400);

        } elseif ($item_type === 'yarn') {

            $val = \Validation::forge();
            $val->add('name', '毛糸名')->add_rule('required');
            if ($val->run()) {
                $yarn_id = \Model_Yarn::create_yarn(
                    $this->current_user->id,
                    \Input::post()
                );
                if ($yarn_id) {
                    return $this->response(['success' => true, 'yarn_id' => $yarn_id]);
                }
            }
            return $this->response(['success' => false, 'errors' => $val->error_message()], 400);

        } else {
            return $this->response(['success' => false, 'error' => 'Invalid item type'], 400);
        }
    }

    public function post_delete()
    {
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $item_type = \Input::post('item_type');
        $item_id = \Input::post('item_id');

        \Log::info('Deleting item of type ' . $item_type . ' with ID ' . $item_id);

        if ($item_type === 'project') {
            $result = \Model_Project::delete_user_project($this->current_user->id, $item_id);
        } elseif ($item_type === 'yarn') {
            $result = \Model_Yarn::delete_user_yarn($this->current_user->id, $item_id);
        } else {
            return $this->response(['success' => false, 'error' => 'Invalid item type'], 400);
        }

        if ($result['success']) {
            return $this->response(['success' => true]);
        } else {
            return $this->response(['success' => false, 'error' => $result['message']], 400);
        }
    }

    public function post_edit()
    {
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $item_type = \Input::post('item_type');
        $item_id = \Input::post('item_id');
        $data = \Input::post();

        if ($item_type === 'project') {
            $result = \Model_Project::edit_user_project($this->current_user->id, $item_id, $data);
        } elseif ($item_type === 'yarn') {
            $result = \Model_Yarn::edit_user_yarn($this->current_user->id, $item_id, $data);
        } else {
            return $this->response(['success' => false, 'error' => 'Invalid item type'], 400);
        }

        if ($result['success']) {
            return $this->response(['success' => true]);
        } else {
            return $this->response(['success' => false, 'error' => $result['message']], 400);
        }
    }

    public function action_logout()
    {
        Session::destroy();
        Response::redirect('login');
    }

    public function action_detail($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);

        \Log::debug('Fetched project: ' . print_r($project, true));

        if (!$project) {
            throw new HttpNotFoundException;
        }

        $data['project'] = $project;
        $data['title']   = $project['name'] . ' - あみぷろ';

        return Response::forge(View::forge('projects/detail', $data));
    }

    public function action_detail_data($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);
        $available_yarn = \Model_Yarn::get_user_yarn($this->current_user->id, true);

        if (!$project) {
            return $this->response(['success' => false, 'error' => 'Not found']);
        }

        return $this->response([
            'success' => true,
            'project' => $project,
            'available_yarn' => $available_yarn['yarn']
        ]);
    }

    public function action_color($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);

        if (!$project) {
            throw new HttpNotFoundException;
        }

        $data['project'] = $project;
        $data['title']   = $project['name'] . ' - あみぷろ';

        return Response::forge(View::forge('projects/color', $data));
    }

    public function action_color_data($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);
        $chart = \Model_Customchart::get_chart($this->current_user->id, $id);

        if (!$project) {
            return $this->response(['success' => false, 'error' => 'Not found']);
        }

        if (!$chart['success']) {
            return $this->response(['success' => false, 'error' => 'Error retrieving chart data']);
        }

        $stitch_shape = \Cookie::get('stitch_shape', 'square');
        $default_page = \Cookie::get('default_page', 'screenshot');

        return $this->response([
            'success' => true,
            'project' => $project,
            'chart' => $chart['chart'],
            'stitch_shape' => $stitch_shape,
            'default_page' => $default_page
        ]);
    }

    public function post_preference()
    {
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $default_page = \Input::post('default_page');
        if ($default_page && !in_array($default_page, ['screenshot', 'custom'])) {
            return $this->response(['success' => false, 'error' => 'Invalid default page'], 400);
        }

        $stitch_shape = \Input::post('stitch_shape');
        if ($stitch_shape && !in_array($stitch_shape, ['square', 'knit'])) {
            return $this->response(['success' => false, 'error' => 'Invalid stitch shape'], 400);
        }

        if ($default_page) \Cookie::set('default_page', $default_page, 60 * 60 * 24 * 30);
        if ($stitch_shape) \Cookie::set('stitch_shape', $stitch_shape, 60 * 60 * 24 * 30);

        return $this->response(['success' => true]);
    }

    public function post_chart($id)
    {
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

        $width = (int) \Input::post('width', 20);
        $height = (int) \Input::post('height', 20);
        $cells_data = json_decode(\Input::post('cells', '[]'), true);
        \Log::debug('Received chart data: ' . print_r($cells_data, true));

        if ($width < 1 || $width > 50 || $height < 1 || $height > 50) {
            return $this->response(['success' => false, 'error' => 'Width and height must be between 1 and 50'], 400);
        }

        \Log::debug('Width: ' . $width . ', Height: ' . $height);

        $cells_data = array_filter($cells_data, function($cell) use ($width, $height) {
            return isset($cell['x'], $cell['y'], $cell['color']) &&
                   is_int($cell['x']) && is_int($cell['y']) &&
                   $cell['x'] >= 0 && $cell['x'] < $width &&
                   $cell['y'] >= 0 && $cell['y'] < $height &&
                   preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $cell['color']);
        });

        \Log::debug('Filtered cells data: ' . print_r($cells_data, true));

        if (count($cells_data) === 0) {
            return $this->response(['success' => true, 'message' => 'No cells to save']);
        }

        $result = \Model_Customchart::save_chart(
            $this->current_user->id,
            $id,
            $width,
            $height,
            $cells_data
        );

        if ($result["success"]) {
            return $this->response(['success' => true]);
        } else {
            return $this->response(['success' => false, 'message' => $result["message"]], 500);
        }
    }
}