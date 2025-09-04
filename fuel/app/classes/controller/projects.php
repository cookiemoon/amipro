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
        $selected_filters = [
            'types'      => [],
            'techniques' => [],
        ];

        $data = [
            'available_filters' => $available_filters,
            'selected_filters' => $selected_filters,
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

        $selected_filters = [
            'weight'      => [],
            'fiber' => [],
        ];

        $data = [
            'available_filters' => $available_filters,
            'selected_filters' => $selected_filters,
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

    public function get_create_data()
    {
        $yarn_data = \Model_Yarn::get_user_yarn_list($this->current_user->id);
        return $this->response([
            'success'              => true,
            'statuses'             => ['0' => '未着手', '1' => '進行中', '2' => '中断中', '3' => '完了', '4' => '放棄'],
            'available_techniques' => \Model_Project::get_knitting_techniques(),
            'user_yarn'            => $yarn_data,
        ]);
    }

    public function post_create()
    {
        $val = \Validation::forge();
        $val->add('name', 'プロジェクト名')->add_rule('required');
        $val->add('object_type', 'プロジェクトタイプ')->add_rule('required');

        if ($val->run()) {
            $project_id = \Model_Project::create_with_techniques(
                $this->current_user,
                \Input::post(),
                \Input::post('techniques', [])
            );
            if ($project_id) {
                return $this->response(['success' => true, 'project_id' => $project_id]);
            }
        }
        return $this->response(['success' => false, 'errors' => $val->error_message()], 400);
    }
}