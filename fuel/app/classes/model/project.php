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
    ];

    // Get all projects for a user with optional search and filters
    public static function get_user_projects($user_id, $options = [])
    {

        try {
            $query = static::query()->where('user_id', $user_id)->related('project_techniques');

            // Apply search and filter conditions
            static::apply_search_filters($query, $options);

            $total_count = $query->count();

            $projects = $query->order_by('created_at', 'name')
                              ->get();

            return [
                'projects'   => array_map([__CLASS__, 'format_project_for_display'], $projects),
            ];

        } catch (\Exception $e) {
            \Log::error('Get user projects error: ' . $e->getMessage());
            return ['projects' => []];
        }
    }

    // Helper method to apply search and filter conditions to the query
    private static function apply_search_filters(\Orm\Query $query, array $options)
    {
        // Apply search filter
        if (!empty($options['search'])) {
            $search = '%' . $options['search'] . '%';
            $query->where_open();
            $query->where('name', 'LIKE', $search);
            $query->where_close();
        }

        // Apply type filter if present
        if (!empty($options['types'])) {
            $query->where('object_type', 'IN', $options['types']);
        }

        // Apply technique filter if present
        \Log::debug('Applying technique filters: ' . print_r($options['techniques'], true));
        if (!empty($options['techniques'])) {
            $subquery = \DB::select('project_id')
                ->from('project_technique')
                ->where('technique', 'IN', $options['techniques']);
            $query->where('id', 'IN', $subquery);
        }
    }

    // Format a project for display
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

        return [
            'id' => $project->id,
            'name' => $project->name,
            'object_type' => $project->object_type,
            'screenshot_url' => $project->screenshot_url,
            'status_text' => $status_text,
            'progress' => $project->progress,
            'status' => $project->status,
            'technique_names' => $technique_names, // Pass the fetched techniques
        ];
    }

    public static function create_project(\Model_User $user, array $project_data, array $techniques_array)
    {

        \DB::start_transaction();

        try
        {
            \Log::info('Collecting data for project. User ID: ' . $user->id);
            $project_data['user_id'] = $user->id;
            $project = static::forge($project_data);
            
            if (!$project->save())
            {
                $error = $project->validation()->error();
                \Log::error('Project model failed to save. Validation Error: ' . print_r($error, true));
                \DB::rollback_transaction();
                return false;
            }

            \Log::info('Project saved successfully. Project ID: ' . $project->id);

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

    /**
     * Get all available filter options for a specific user.
     * This is the single source of truth for the filter panel.
     */
    public static function get_available_filters($user_id)
    {
        return [
            'types' => static::get_project_types($user_id),
            'techniques' => static::get_knitting_techniques($user_id),
        ];
    }

    /**
     * Helper to get distinct project types for a user.
     */
    public static function get_project_types($user_id)
    {
        $result = \DB::select('object_type')
            ->from('projects')
            ->where('user_id', $user_id)
            ->where('object_type', '!=', '')
            ->distinct(true)
            ->execute()
            ->as_array();
        
        // Flatten the array from [['object_type' => 'Hat']] to ['Hat' => 'Hat']
        $types = array_column($result, 'object_type');
        return array_combine($types, $types);
    }

    /**
     * Helper to get distinct techniques for a user.
     */
    public static function get_knitting_techniques($user_id)
    {
        $result = \DB::select('technique')
            ->from('project_technique')
            ->join('projects', 'INNER')->on('project_technique.project_id', '=', 'projects.id')
            ->where('projects.user_id', $user_id)
            ->where('technique', '!=', '')
            ->distinct(true)
            ->execute()
            ->as_array();
        
        // Flatten the array from [['technique' => 'Cables']] to ['Cables' => 'Cables']
        $techniques = array_column($result, 'technique');
        return array_combine($techniques, $techniques);
    }
}