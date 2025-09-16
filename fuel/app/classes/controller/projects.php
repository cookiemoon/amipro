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
    }

    // プロジェクト管理ページの表示
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

        $this->template->js = 'projects.js';
        $this->template->css = 'projects.css';

        $data = [
            'available_filters' => $available_filters,
            'selected_filters' => $selected_filters,
            'available_yarn' => $available_yarn['yarn'],
            'current_tab' => 'projects',
            'search_query' => '',
        ];

        $this->template->content = View::forge('projects/index', $data);
    }

    // 毛糸管理ページの表示
    public function action_yarn()
    {
        $this->format = 'html';
        
        $this->template->title = '毛糸 - あみぷろ';
        $available_filters = \Model_Yarn::get_available_filters();
        $projects_data = \Model_Project::get_user_projects($this->current_user->id);

        $this->template->js = 'yarn.js';
        $this->template->css = 'projects.css';

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

        $this->template->content = View::forge('projects/yarn', $data);
    }

    // プロジェクトデータの取得
    public function action_data()
    {
        $projects_data = \Model_Project::get_user_projects($this->current_user->id);

        return $this->response([
            'success' => true,
            'projects' => $projects_data['projects']
        ], 200, ['Content-Type' => 'application/json']);
    }

    // 毛糸データの取得
    public function action_yarn_data()
    {
        $yarn_data = \Model_Yarn::get_user_yarn($this->current_user->id);

        return $this->response([
            'success' => true,
            'yarn' => $yarn_data['yarn']
        ], 200, ['Content-Type' => 'application/json']);
    }

    // プロジェクトまたは毛糸の作成
    public function post_create()
    {   
        if (!\Security::check_token()) {
            return $this->response(['success' => false, 'error' => '不正な操作が検出されました。'], 400);
        }

        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 405);
        }

        $item_type = \Input::post('item_type');

        if ($item_type === 'project') {

            $val = \Validation::forge();
            $val->add('name', 'プロジェクト名')->add_rule('required')->add_rule('max_length', 32);
            $val->add('object_type', 'プロジェクトタイプ')->add_rule('required')->add_rule('max_length', 10);
            $val->add('status', '進行状況')->add_rule('numeric_min', 0)->add_rule('numeric_max', 4);
            $val->add('created_at', '開始日')->add_rule('required')->add_rule('valid_date');
            $val->add('completed_at', '完成日')->add_rule('valid_date');
            $val->add('progress', '進捗')->add_rule('numeric_min', 0)->add_rule('numeric_max', 100);
            if ($val->run()) {
                $project_id = \Model_Project::create_project(
                    $this->current_user,
                    \Input::post()
                );
                if ($project_id) {
                    return $this->response(['success' => true, 'project_id' => $project_id, 'new_csrf_token' => \Security::fetch_token()]);
                } else {
                    return $this->response(['success' => false, 'error' => "エラーが発生しました。", 'new_csrf_token' => \Security::fetch_token()], 500);
                }
            }
            return $this->response(['success' => false, 'error' => "情報を正しく入力してください。", 'new_csrf_token' => \Security::fetch_token()], 400);

        } elseif ($item_type === 'yarn') {

            $val = \Validation::forge();
            $val->add('name', '毛糸名')->add_rule('required')->add_rule('max_length', 32);
            $val->add('color', '色')->add_rule('max_length', 255);
            $val->add('brand', 'ブランド')->add_rule('max_length', 32);
            $val->add('weight', '太さ')->add_rule('max_length', 255);
            $val->add('fiber_desc', '繊維')->add_rule('max_length', 255);
            if ($val->run()) {
                $yarn_id = \Model_Yarn::create_yarn(
                    $this->current_user->id,
                    \Input::post()
                );
                if ($yarn_id) {
                    return $this->response(['success' => true, 'yarn_id' => $yarn_id, 'new_csrf_token' => \Security::fetch_token()]);
                } else {
                    return $this->response(['success' => false, 'error' => "エラーが発生しました。", 'new_csrf_token' => \Security::fetch_token()], 500);
                }
            }
            return $this->response(['success' => false, 'error' => "情報を正しく入力してください。", 'new_csrf_token' => \Security::fetch_token()], 400);

        } else {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }
    }

    // プロジェクトまたは毛糸の削除
    public function post_delete()
    {
        if (!\Security::check_token()) {
            return $this->response(['success' => false, 'error' => '不正な操作が検出されました。'], 400);
        }

        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 405);
        }

        $item_type = \Input::post('item_type');
        $item_id = \Input::post('item_id');

        \Log::info('Deleting item of type ' . $item_type . ' with ID ' . $item_id);

        if ($item_type === 'project') {
            $result = \Model_Project::delete_user_project($this->current_user->id, $item_id);
        } elseif ($item_type === 'yarn') {
            $result = \Model_Yarn::delete_user_yarn($this->current_user->id, $item_id);
        } else {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }

        if ($result['success']) {
            return $this->response(['success' => true]);
        } else {
            if ($result['error'] === 'not_found') {
                return $this->response(['success' => false, 'error' => "情報が見つかりませんでした。", 'new_csrf_token' => \Security::fetch_token()], 404);
            } else {
                return $this->response(['success' => false, 'error' => "エラーが発生しました。", 'new_csrf_token' => \Security::fetch_token()], 500);
            }
        }
    }

    // プロジェクトまたは毛糸の編集
    public function post_edit()
    {
        if (!\Security::check_token()) {
            return $this->response(['success' => false, 'error' => '不正な操作が検出されました。'], 400);
        }

        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 405);
        }

        $item_type = \Input::post('item_type');
        $item_id = \Input::post('item_id');
        $data = \Input::post();

        if ($item_type === 'project') {
            $result = \Model_Project::edit_user_project($this->current_user->id, $item_id, $data);
        } elseif ($item_type === 'yarn') {
            $result = \Model_Yarn::edit_user_yarn($this->current_user->id, $item_id, $data);
        } else {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }

        if ($result['success']) {
            return $this->response(['success' => true, 'new_csrf_token' => \Security::fetch_token()]);
        } else {
            if ($result['error'] === 'not_found') {
                return $this->response(['success' => false, 'error' => "情報が見つかりませんでした。", 'new_csrf_token' => \Security::fetch_token()], 404);
            } else {
                return $this->response(['success' => false, 'error' => "エラーが発生しました。", 'new_csrf_token' => \Security::fetch_token()], 500);
            }
        }
    }

    // プロジェクト詳細ページの表示
    public function action_detail($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);

        if (!$project) {
            throw new HttpNotFoundException;
        }

        $data['project'] = $project;
        $data['title']   = $project['name'] . ' - あみぷろ';

        $this->template->js = 'project-detail.js';
        $this->template->css = 'detail.css';

        $this->template->content = View::forge('projects/detail', $data);
    }

    // プロジェクト詳細データの取得
    public function action_detail_data($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);
        $available_yarn = \Model_Yarn::get_user_yarn($this->current_user->id, true);

        if (!$project) {
            return $this->response(['success' => false, 'error' => '情報が見つかりませんでした。'], 404);
        }

        return $this->response([
            'success' => true,
            'project' => $project,
            'available_yarn' => $available_yarn['yarn']
        ]);
    }

    // カラーチャートページの表示
    public function action_color($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);

        if (!$project) {
            throw new HttpNotFoundException;
        }

        $this->template->js = 'color.js';
        $this->template->css = 'detail.css';

        $data['project'] = $project;
        $data['title']   = $project['name'] . ' - あみぷろ';

        $this->template->content = View::forge('projects/color', $data);
    }

    // カラーチャートデータの取得
    public function action_color_data($id)
    {
        $project = \Model_Project::get_user_projects($this->current_user->id, $id);
        $chart = \Model_Customchart::get_chart($this->current_user->id, $id);

        if (!$project) {
            return $this->response(['success' => false, 'error' => '情報が見つかりませんでした。'], 404);
        }

        if (!$chart['success']) {
            return $this->response(['success' => false, 'error' => 'エラーが発生しました。'], 500);
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

    // カラーチャートページのオプション保存
    public function post_preference()
    {
        if (!\Security::check_token()) {
            return $this->response(['success' => false, 'error' => '不正な操作が検出されました。'], 400);
        }

        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 405);
        }

        $default_page = \Input::post('default_page');
        if ($default_page && !in_array($default_page, ['screenshot', 'custom'])) {
            return $this->response(['success' => false, 'error' => '情報を正しく入力してください。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }

        $stitch_shape = \Input::post('stitch_shape');
        if ($stitch_shape && !in_array($stitch_shape, ['square', 'knit'])) {
            return $this->response(['success' => false, 'error' => '情報を正しく入力してください。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }

        if ($default_page) \Cookie::set('default_page', $default_page, 60 * 60 * 24 * 30);
        if ($stitch_shape) \Cookie::set('stitch_shape', $stitch_shape, 60 * 60 * 24 * 30);

        return $this->response(['success' => true, 'new_csrf_token' => \Security::fetch_token()]);
    }

    // カラーチャートデータの保存
    public function post_chart($id)
    {
        if (!\Security::check_token()) {
            return $this->response(['success' => false, 'error' => '不正な操作が検出されました。'], 400);
        }
        
        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 405);
        }

        $width = (int) \Input::post('width', 20);
        $height = (int) \Input::post('height', 20);
        $cells_data = json_decode(\Input::post('cells', '[]'), true);

        if ($width < 1 || $width > 50 || $height < 1 || $height > 50) {
            return $this->response(['success' => false, 'error' => '情報を正しく入力してください。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }

        $cells_data = array_filter($cells_data, function($cell) use ($width, $height) {
            return isset($cell['x'], $cell['y'], $cell['color']) &&
                   is_int($cell['x']) && is_int($cell['y']) &&
                   $cell['x'] >= 0 && $cell['x'] < $width &&
                   $cell['y'] >= 0 && $cell['y'] < $height &&
                   preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $cell['color']);
        });

        if (count($cells_data) === 0) {
            $cells_data = [];
        }

        $result = \Model_Customchart::save_chart(
            $this->current_user->id,
            $id,
            $width,
            $height,
            $cells_data
        );

        if ($result["success"]) {
            return $this->response(['success' => true, 'new_csrf_token' => \Security::fetch_token()]);
        } else {
            if ($result['error'] === 'not_found') {
                return $this->response(['success' => false, 'error' => "情報が見つかりませんでした。", 'new_csrf_token' => \Security::fetch_token()], 404);
            } else {
                return $this->response(['success' => false, 'error' => "エラーが発生しました。", 'new_csrf_token' => \Security::fetch_token()], 500);
            }
        }
    }

    // 段数カウンターの更新
    public function post_rows($id)
    {
        if (!\Security::check_token()) {
            return $this->response(['success' => false, 'error' => '不正な操作が検出されました。'], 400);
        }

        if (!\Input::is_ajax()) {
            return $this->response(['success' => false, 'error' => '操作は実行できません。', 'new_csrf_token' => \Security::fetch_token()], 405);
        }

        $row_count = \Input::post('row_count');

        if ($row_count < 0 || $row_count > 999 || $row_count === null || $row_count === '' || !is_numeric($row_count)) {
            return $this->response(['success' => false, 'error' => '情報を正しく入力してください。', 'new_csrf_token' => \Security::fetch_token()], 400);
        }

        $result = \Model_Project::update_row_count($this->current_user->id, $id, $row_count);

        if ($result['success']) {
            return $this->response(['success' => true, 'new_csrf_token' => \Security::fetch_token()]);
        } else {
            if ($result['error'] === 'not_found') {
                return $this->response(['success' => false, 'error' => "情報が見つかりませんでした。", 'new_csrf_token' => \Security::fetch_token()], 404);
            } else {
                return $this->response(['success' => false, 'error' => "エラーが発生しました。", 'new_csrf_token' => \Security::fetch_token()], 500);
            }
        }
    }
}