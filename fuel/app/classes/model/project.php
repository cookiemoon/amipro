<?php

class Model_Project extends \Orm\Model
{
    protected static $_table_name = 'projects';

    protected static $_properties = [
        'id',
        'user_id',
        'name',
        'object_type',
        'status' => ['default' => 0],
        'progress',
        'screenshot_url',
        'colorwork_url',
        'memo',
        'created_at',
        'completed_at',
        'row_counter' => ['default' => 0],
    ];

    protected static $_belongs_to = [
        'user' => ['model_to' => 'Model_User', 'key_from' => 'user_id', 'key_to' => 'id'],
    ];

    protected static $_has_many = [
        'project_techniques' => ['model_to' => 'Model_ProjectTechnique', 'key_from' => 'id', 'key_to' => 'project_id'],
        'yarn' => ['model_to' => 'Model_Yarn', 'key_from' => 'id', 'key_to' => 'project_id'],
        'custom_chart_cells' => ['model_to' => 'Model_Customchartcell', 'key_from' => 'id', 'key_to' => 'project_id', 'cascade_save' => true, 'cascade_delete' => true],
    ];

    protected static $_has_one = [
        'custom_chart' => ['model_to' => 'Model_Customchart', 'key_from' => 'id', 'key_to' => 'project_id', 'cascade_save' => true, 'cascade_delete' => true],
    ];

    // READ
    public static function get_user_projects($user_id, $project_id = null)
    {
        try {
            if ($project_id != null) {
                $query = static::query()->where('user_id', $user_id)
                                        ->where('id', $project_id)
                                        ->related('project_techniques')
                                        ->related('yarn');

                $project = $query->get_one();
                if ($project) {
                    return self::format_project_for_display($project, true);
                } else {
                    return ['project' => []];
                }
            }
            
            $query = static::query()->where('user_id', $user_id)
                                        ->related('project_techniques')
                                        ->related('yarn');

            $projects = $query->order_by('name')
                              ->get();

            return [
                'projects'   => array_map([__CLASS__, 'format_project_for_display'], $projects),
            ];

        } catch (\Exception $e) {
            \Log::error('Get user projects error: ' . $e->getMessage());
            return ['projects' => []];
        }
    }

    public static function verify_ownership($user_id, $project_id) {
        $project = static::find($project_id);
        return $project && $project->user_id == $user_id;
    }

    protected static function format_project_for_display($project, $detail = false)
    {
        $status_map = [0 => '未着手', 1 => '進行中', 2 => '中断中', 3 => '完了', 4 => '放棄'];
        $status_text = $status_map[$project->status] ?? '不明';

        if (($project->status == 1 || $project->status == 2) && $project->progress > 0) {
            $status_text .= ': ' . $project->progress . '%';
        }

        $technique_names = [];

        if (!empty($project->project_techniques)) {
            foreach ($project->project_techniques as $pt) {
                $technique_names[] = $pt->technique;
            }
        }

        $yarn_name = null;
        $yarn_info = [];
    
        if ($detail && !empty($project->yarn)) {
            $yarn_name = [];
            foreach ($project->yarn as $yarn) {
                $full_name = '';
                if (!empty($yarn->brand)) {
                    $full_name .= $yarn->brand . ' ';
                }
                $full_name .= $yarn->name;
                if (!empty($yarn->color)) {
                    $full_name .= ' (' . $yarn->color . ')';
                }
                $yarn_name[] = $full_name;
                $yarn_info[] = [
                    'id' => $yarn->id,
                    'name' => $yarn->name,
                    'brand' => $yarn->brand,
                    'color' => $yarn->color,
                ];
            }
        } else {
            if (!empty($project->yarn)) {
                $yarn_name = '';
                $count = count($project->yarn);
                $first_yarn = reset($project->yarn);

                if (!empty($first_yarn->brand)) {
                    $yarn_name .= $first_yarn->brand . ' ';
                }

                $yarn_name .= $first_yarn->name;

                if (!empty($first_yarn->color)) {
                    $yarn_name .= ' (' . $first_yarn->color . ')';
                }

                if ($count > 1) {
                    $yarn_name .= ' 他' . ($count - 1) . '玉';
                }
            }
        }

        return [
            'id' => $project->id,
            'name' => $project->name,
            'object_type' => $project->object_type,
            'screenshot_url' => $project->screenshot_url,
            'status_text' => $status_text,
            'progress' => $project->progress,
            'created_at' => $project->created_at ? date('Y-m-d', strtotime($project->created_at)) : null,
            'completed_at' => $project->completed_at ? date('Y-m-d', strtotime($project->completed_at)) : null,
            'created_text' => $project->created_at ? date('Y年m月d日', strtotime($project->created_at)) : null,
            'completed_text' => $project->completed_at ? date('Y年m月d日', strtotime($project->completed_at)) : null,
            'status' => $project->status,
            'technique_names' => $technique_names,
            'memo' => $project->memo,
            'yarn_name' => $yarn_name,
            'yarn_info' => $yarn_info,
            'colorwork_url' => $project->colorwork_url,
        ];
    }

    public static function get_available_filters($user_id)
    {
        return [
            'types' => static::get_project_types($user_id),
            'techniques' => static::get_knitting_techniques($user_id),
        ];
    }

    protected static function get_project_types($user_id)
    {
        $result = \DB::select('object_type')
            ->from('projects')
            ->where('user_id', $user_id)
            ->where('object_type', '!=', '')
            ->distinct(true)
            ->execute()
            ->as_array();
        
        $types = array_column($result, 'object_type');
        array_unshift($types, '全件');

        return array_combine($types, $types);
    }

    protected static function get_knitting_techniques($user_id)
    {
        $result = \DB::select('technique')
            ->from('project_technique')
            ->join('projects', 'INNER')->on('project_technique.project_id', '=', 'projects.id')
            ->where('projects.user_id', $user_id)
            ->where('technique', '!=', '')
            ->distinct(true)
            ->execute()
            ->as_array();
        
        $techniques = array_column($result, 'technique');
        return array_combine($techniques, $techniques);
    }

    // CREATE
    public static function create_project(\Model_User $user, array $project_form)
    {

        \DB::start_transaction();

        try
        {
            $techniques_array = isset($project_form['techniques']) ? json_decode($project_form['techniques'], true) : [];

            $yarns = isset($project_form['yarns']) ? json_decode($project_form['yarns'], true) : [];

            $project_form_clean = array_filter($project_form, function($value) {
                return !is_null($value) && $value !== '';
            });

            $project_data = array_intersect_key($project_form_clean, array_flip([
                'name',
                'object_type',
                'status',
                'progress',
                'screenshot_url',
                'colorwork_url',
                'memo',
                'created_at',
                'completed_at',
            ]));

            $project_data['user_id'] = $user->id;
            $project = static::forge($project_data);
            
            if (!$project->save())
            {
                $error = $project->validation()->error();
                \Log::error('Project model failed to save. Validation Error: ' . print_r($error, true));
                \DB::rollback_transaction();
                return false;
            }

            $new_project_id = $project->id;

            $filtered_techniques = array_filter($techniques_array);

            if (!empty($filtered_techniques))
            {
                foreach ($filtered_techniques as $technique_name)
                {
                    if (empty(trim($technique_name))) {
                        continue;
                    }
                    $project_technique = \Model_ProjectTechnique::forge();
                    $project_technique->project_id = $new_project_id;
                    $project_technique->technique = $technique_name;
                    $project_technique->save();
                }
            }

            if (!empty($yarns))
            {
                foreach ($yarns as $curr_yarn)
                {
                    $yarn_id = $curr_yarn['id'] ?? null;
                    if (!is_numeric($yarn_id)) {
                        \Log::warning('Invalid yarn ID provided: ' . $yarn_id);
                        continue;
                    }
                    $yarn = \Model_Yarn::find($yarn_id);
                    if ($yarn && $yarn->user_id == $user->id)
                    {
                        $yarn->project_id = $new_project_id;
                        $yarn->save();
                    }
                    else
                    {
                        \Log::warning('Yarn ID ' . $yarn_id . ' not found or does not belong to user ' . $user->id);
                    }
                }
            }
            else
            {
                \Log::info('No yarn IDs provided to associate with the new project.');
            }

            \DB::commit_transaction();
            
            return $new_project_id;
        }
        catch (\Exception $e)
        {
            \DB::rollback_transaction();
            \Log::error('Failed to create project with techniques: ' . $e->getMessage());
            return false;
        }
    }

    // DELETE
    public static function delete_user_project($user_id, $project_id) {
        try {
            $project = static::find($project_id);
            if (!$project || $project->user_id != $user_id) {
                return ['success' => false, 'message' => 'Project not found or access denied'];
            }

            $yarns = \Model_Yarn::query()->where('project_id', $project_id)->get();
            foreach ($yarns as $yarn) {
                $yarn->project_id = null;
                $yarn->save();
            }

            \Model_ProjectTechnique::query()->where('project_id', $project_id)->delete();

            $project->delete();

            return ['success' => true];
        } catch (\Exception $e) {
            \Log::error('Error deleting project: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the project'];
        }
    }

    // TODO: UPDATE
    public static function edit_user_project($user_id, $project_id, $data) {
        try {
            $project = static::find($project_id);
            if (!$project || $project->user_id != $user_id) {
                return ['success' => false, 'message' => 'Project not found or access denied'];
            }

            $updatable_fields = [
                'name',
                'object_type',
                'status',
                'progress',
                'screenshot_url',
                'colorwork_url',
                'memo',
                'created_at',
                'completed_at',
            ];

            foreach ($updatable_fields as $field) {
                if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                    $project->$field = $data[$field];
                }
            }

            if (!$project->save()) {
                $error = $project->validation()->error();
                \Log::error('Project model failed to save during update. Validation Error: ' . print_r($error, true));
                return ['success' => false, 'message' => 'Failed to save project changes'];
            }

            if (isset($data['techniques'])) {
                $techniques_array = json_decode($data['techniques'], true);
                \Model_ProjectTechnique::query()->where('project_id', $project_id)->delete();

                $filtered_techniques = array_filter($techniques_array);
                foreach ($filtered_techniques as $technique_name) {
                    if (empty(trim($technique_name))) {
                        continue;
                    }
                    $project_technique = \Model_ProjectTechnique::forge();
                    $project_technique->project_id = $project_id;
                    $project_technique->technique = $technique_name;
                    $project_technique->save();
                }
            }

            if (isset($data['yarn']) && !empty($data['yarn'])) {
                $yarns = json_decode($data['yarn'], true);

                \Log::debug('Updating yarn associations for project ID ' . $project_id . ' with yarns: ' . print_r($yarns, true));
                
                
                $existing_yarns = \Model_Yarn::query()->where('project_id', $project_id)->get();
                if(isset($existing_yarns)) { 
                    \Log::info('Existing yarns count: ' . count($existing_yarns));
                    foreach ($existing_yarns as $yarn) {
                        $yarn->project_id = null;
                        $yarn->save();
                    }
                }

                foreach ($yarns as $curr_yarn) {
                    $yarn_id = $curr_yarn['id'] ?? null;
                    if (!is_numeric($yarn_id)) {
                        \Log::warning('Invalid yarn ID provided: ' . $yarn_id);
                        continue;
                    }
                    $yarn = \Model_Yarn::find($yarn_id);
                    if ($yarn && $yarn->user_id == $user_id) {
                        $yarn->project_id = $project_id;
                        $yarn->save();
                    } else {
                        \Log::warning('Yarn ID ' . $yarn_id . ' not found or does not belong to user ' . $user_id);
                    }
                }
            }

            return ['success' => true];
        } catch (\Exception $e) {
            \Log::error('Error updating project: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the project'];
        }
    }
}