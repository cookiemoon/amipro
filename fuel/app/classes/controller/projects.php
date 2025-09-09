<?php

class Controller_Projects extends Controller_Hybrid
{
    public $template = 'template';
    protected $current_user;
    protected $format = 'json';

    public function before()
    {
        parent::before();

        $user_id = \Session::get('user_id');

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

    public function action_yarns()
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
    }

    public function post_yarn()
    {   
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => 'Invalid request'], 400);
        }

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
            //$result = \Model_Project::delete_user_project($this->current_user->id, $item_id);
            return $this->response(['success' => false, 'error' => 'Project deletion not implemented'], 400);
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
            //$result = \Model_Project::edit_user_project($this->current_user->id, $item_id, $data);
            return $this->response(['success' => false, 'error' => 'Project editing not implemented'], 400);
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

}