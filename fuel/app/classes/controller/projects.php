<?php
/**
 * FuelPHP Projects Controller
 * Place this file at: fuel/app/classes/controller/projects.php
 * Handles project listing, filtering, and dashboard functionality
 */

class Controller_Projects extends Controller_Template
{
    public $template = 'template';
    protected $current_user = null;

    public function before()
    {
        parent::before();
        
        if (\Session::get('user_id') === null) {
            Session::set('redirect_url', Uri::current());
            Response::redirect('auth/login');
        }

        $user_id = Session::get('user_id');
        $this->current_user = Model_User::find($user_id);
        
        // Set common template variables
        $this->template->title = 'あみぷろ';
        $this->template->css = array('projects.css');
        $this->template->js = array('main.js');
    }

    private function get_statuses() {
        return [
            '0' => '未着手',
            '1' => '進行中',
            '2' => '中断中',
            '3' => '完了',
            '4' => '放棄',
        ];
    }

    // Project listing page
    public function action_index()
    {
        $this->template->title = 'プロジェクト - あみぷろ';
        
        $selected_types = (array) \Input::get('types', []);
        $selected_techniques = (array) \Input::get('techniques', []);

        $token = \Security::fetch_token();
        \Session::set(\Config::get('security.csrf_token_key'), $token);

        \Log::debug('CSRF Token for projects/index: ' . $token);

        // Get project data using the filters
        $projects_data = \Model_Project::get_user_projects($this->current_user->id, [
            'search' => \Input::get('search', ''),
            'types' => $selected_types,
            'techniques' => $selected_techniques,
        ]);
        
        // Get all available filter options from the model
        $available_filters = \Model_Project::get_available_filters($this->current_user->id);
        
        // Assemble all data for the view
        $data = [
            'projects' => $projects_data['projects'],
            'search_query' => \Input::get('search', ''),
            'selected_filters' => [
                'types' => $selected_types,
                'techniques' => $selected_techniques,
            ],
            'available_filters' => $available_filters,
            'user' => $this->current_user,
            'current_tab' => 'projects'
        ];

        $this->template->content = \View::forge('projects/index', $data);
    }

    // Yarn inventory page
    public function action_yarn()
    {
        $this->template->title = '毛糸管理 - あみぷろ';

        $user_id = \Session::get('user_id');

        $search_query = \Input::get('search', '');
        $selected_fiber_types = (array) \Input::get('fiber_types', []);
        $selected_weights = (array) \Input::get('weights', []);

        $filters = [
            'search'      => $search_query,
            'fiber_types' => $selected_fiber_types,
            'weights'     => $selected_weights,
        ];

        $yarn_data = \Model_Yarn::get_user_yarn_with_filters($user_id, $filters);

        $this->template->content = \View::forge('projects/yarn', [
            'yarn_list'        => $yarn_data['yarn'],
            'filters'          => $yarn_data['filters'],
            'search_query'     => $search_query,
            'selected_filters' => [
                'fiber_types' => $selected_fiber_types,
                'weights'     => $selected_weights,
            ],
            'current_tab'      => 'yarn',
        ]);
    }

    // Project detail page
    public function action_detail($project_id = null)
    {
        if (!$project_id) {
            Response::redirect('projects');
        }
        
        // Get current user
        $user_id = Session::get('user_id');
        
        // Get project details
        $project = Model_Project::get_project_detail($project_id, $user_id);
        
        if (!$project) {
            Session::set_flash('error_message', 'プロジェクトが見つかりません。');
            Response::redirect('projects');
        }
        
        $this->template->title = $project->name . ' - あみぷろ';
        $this->template->content = View::forge('projects/detail');
        $this->template->content->set(array(
            'project' => $project,
            'user' => Session::get('user_id'),
        ));
    }

    // Create new project page
    public function action_create()
    {
        $this->template->title = '新しいプロジェクト - あみぷろ';

        // Get the yarn data by calling the main filtering method
        $user_id = \Session::get('user_id');
        $yarn_data = \Model_Yarn::get_user_yarn_with_filters($user_id);

        // Assemble all data for the view into a single array
        $data = [
            'statuses'             => $this->get_statuses(),
            'available_techniques' => \Model_Project::get_knitting_techniques(),
            'user_yarn'            => $yarn_data['yarn'], // Extract the yarn list
            'form_data'            => \Session::get_flash('form_data', []),
            'error_message'        => \Session::get_flash('error_message'),
        ];

        // Pass the entire $data array to the view
        $this->template->content = \View::forge('projects/create', $data);
    }

    // Handle new project creation
    public function action_process_create()
    {
        if (Input::method() !== 'POST') {
            Response::redirect('projects/create');
        }

        if (!Security::check_token()) {
            Session::set_flash('error_message', 'セキュリティトークンが無効です。');
            Response::redirect('projects/create');
        }

        $user_id = Session::get('user_id');
        $post_data = \Input::post();

        $status = isset($post_data['status']) ? $post_data['status'] : 0;
        $start_date = !empty($post_data['start_date']) ? $post_data['start_date'] : date('Y-m-d');
        $completion_date = isset($post_data['completion_date']) && !empty($post_data['completion_date']) ? $post_data['completion_date'] : null;
        
        // Get form data
        $form_data = array(
            'name' => Input::post('name'),
            'object_type' => Input::post('project_type'),
            'memo' => Input::post('notes'),
            'created_at' => $start_date,
            'status' => $post_data['status'],
            'progress' => ($status == '1' || $status == '2') ? $post_data['progress'] : null,
            'completed_at' => ($status == '3') ? $completion_date : null,
            'screenshot_url' => Input::post('screenshot_url'),
            'colorwork_url' => Input::post('colorwork_url'),
        );

        try {
            $submitted_techniques = \Input::post('techniques', []);
            $project_id = Model_Project::create_project($this->current_user, $form_data, $submitted_techniques);
            
            if ($project_id) {
                \Session::set_flash('success', '新しいプロジェクトが作成されました。');
                \Response::redirect('dashboard');
            } else {
                Session::set_flash('error_message', 'プロジェクトの作成に失敗しました。');
                Session::set_flash('form_data', $form_data);
                Response::redirect('projects/create');
            }

            //TODO: save yarn associations if any
            
        } catch (Exception $e) {
            Session::set_flash('error_message', $e->getMessage());
            Session::set_flash('form_data', $form_data);
            Response::redirect('projects/create');
        }
    }

    public function action_filter()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false)), 400);
        }

        $user_id = Session::get('user_id');
        
        $filters = array(
            'search' => Input::post('search', ''),
            'types' => (array) \Input::post('types', []),
            'techniques' => (array) \Input::post('techniques', []),
        );

        try {
            $projects_data = Model_Project::get_user_projects($user_id, $filters);
            
            return Response::forge(json_encode(array(
                'success' => true,
                'projects' => $projects_data['projects'],
            )), 200, array('Content-Type' => 'application/json'));
            
        } catch (Exception $e) {
            return Response::forge(json_encode(array(
                'success' => false,
                'error' => 'フィルタリング中にエラーが発生しました。'
            )), 500, array('Content-Type' => 'application/json'));
        }
    }

    // Update project progress via AJAX
    public function action_update_progress($project_id = null)
    {
        if (!$project_id || Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false)), 400);
        }

        $user_id = Session::get('user_id');
        $progress = Input::post('progress', 0);
        
        try {
            $success = Model_Project::update_progress($project_id, $user_id, $progress);
            
            return Response::forge(json_encode(array(
                'success' => $success,
                'message' => $success ? '進捗が更新されました。' : '進捗の更新に失敗しました。'
            )), 200, array('Content-Type' => 'application/json'));
            
        } catch (Exception $e) {
            return Response::forge(json_encode(array(
                'success' => false,
                'error' => $e->getMessage()
            )), 500, array('Content-Type' => 'application/json'));
        }
    }
}