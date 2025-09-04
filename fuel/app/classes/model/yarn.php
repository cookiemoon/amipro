<?php

class Model_Yarn extends \Orm\Model
{
    protected static $_table_name = 'yarn';
    
    protected static $_properties = [
        'id',
        'user_id',
        'name',
        'brand',
        'color',
        'size',
        'fiber_animal',
        'fiber_plant',
        'fiber_synthetic',
        'fiber_desc',
        'project_id',
    ];

    protected static $_belongs_to = array(
        'user' => array(
            'key_from' => 'user_id',
            'model_to' => 'Model_User',
            'key_to' => 'id',
        )
    );

    protected static $_has_one = array(
        'project' => array(
            'key_from' => 'project_id',
            'model_to' => 'Model_Project',
            'key_to' => 'id',
        )
    );

    public static function get_user_yarn($user_id)
    {
        try {
            $query = static::query()->where('user_id', $user_id);
            
            $yarn_items = $query->order_by('created_at', 'desc')->get();
            
            $formatted_yarn = array_map([__CLASS__, 'format_yarn_for_display'], $yarn_items);
            
            return [
                'yarn'    => $formatted_yarn,
            ];
            
        } catch (\Exception $e) {
            \Log::error('Get user yarn with filters error: ' . $e->getMessage());

            return [
                'yarn'    => [],
            ];
        }
    }

    public static function get_project_yarn($project_id)
    {
        try {
            $yarn_items = static::query()->where('project_id', $project_id)
                                        ->order_by('created_at', 'desc')
                                        ->get();
            
            $formatted_yarn = array_map([__CLASS__, 'format_yarn_for_display'], $yarn_items);
            
            return ['yarn' => $formatted_yarn];
            
        } catch (\Exception $e) {
            \Log::error('Get project yarn error: ' . $e->getMessage());
            return ['yarn' => []];
        }
    }

    public static function create_yarn($user_id, $data)
    {
        $yarn = static::forge([
            'user_id' => $user_id,
            'name' => $data['name'],
            'brand' => $data['brand'],
            'color' => $data['color'],
            'weight' => $data['weight'],
            'size' => $data['size'],
            'fiber_animal' => isset($data['fiber']['animal']) ? 1 : 0,
            'fiber_plant' => isset($data['fiber']['plant']) ? 1 : 0,
            'fiber_synthetic' => isset($data['fiber']['synthetic']) ? 1 : 0,
            'fiber_desc' => $data['fiber_desc'],
            'project_id' => isset($data['project_id']) ? $data['project_id'] : null,
        ]);

        if ($yarn->save()) {
            return ['success' => true, 'yarn_id' => $yarn->id];
        }

        \Log::error('Failed to save yarn for user_id ' . $user_id);
        return ['success' => false, 'message' => '毛糸の保存に失敗しました。'];
    }

    public static function get_fiber_types()
    {
        return [
            '動物性繊維' => '動物性繊維',
            '植物繊維' => '植物繊維',
            '合成繊維' => '合成繊維',
        ];
    }
    
    public static function get_yarn_weights()
    {
        return [
            '極細' => '極細',
            '合細' => '合細',
            '中細' => '中細',
            '合太' => '合太',
            '並太' => '並太',
            '極太' => '極太',
            '超極太' => '超極太',
        ];
    }

    public static function get_available_filters()
    {
        return [
            'weight' => static::get_yarn_weights(),
            'fiber'  => static::get_fiber_types(),
        ];
    }
    
    protected static function format_yarn_for_display($yarn)
    {
        $weight_map = [
            0 => '極細',
            1 => '合細',
            2 => '中細',
            3 => '合太',
            4 => '並太',
            5 => '極太',
            6 => '超極太',
        ];

        return [
            'id' => $yarn->id,
            'name' => $yarn->name,
            'brand' => $yarn->brand,
            'color' => $yarn->color,
            'weight' => isset($weight_map[$yarn->weight]) ? $weight_map[$yarn->weight] : '不明',
            'size' => $yarn->size,
            'fiber_types' => [
                'animal' => $yarn->fiber_animal ? '動物性繊維' : null,
                'plant' => $yarn->fiber_plant ? '植物繊維' : null,
                'synthetic' => $yarn->fiber_synthetic ? '合成繊維' : null,
            ],
            'fiber_desc' => $yarn->fiber_desc,
            'project_id' => $yarn->project_id,
        ];
    }
}