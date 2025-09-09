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
    ];

    // READ
    public static function get_user_projects($user_id)
    {

        try {
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

    protected static function format_project_for_display($project)
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

        return [
            'id' => $project->id,
            'name' => $project->name,
            'object_type' => $project->object_type,
            'screenshot_url' => $project->screenshot_url,
            'status_text' => $status_text,
            'progress' => $project->progress,
            'status' => $project->status,
            'technique_names' => $technique_names, // Pass the fetched techniques
            'yarn_name' => $yarn_name,
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

            $yarn_id = isset($project_form['yarn_id']) ? $project_form['yarn_id'] : null;

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

            \Log::debug('Attempting to associate yarn ID ' . $yarn_id . ' with new project ID ' . $new_project_id);
            if (!empty($yarn_id))
            {
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
            else
            {
                \Log::info('No yarn ID provided to associate with the new project.');
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

    // TODO: DELETE

    // TODO: UPDATE
}