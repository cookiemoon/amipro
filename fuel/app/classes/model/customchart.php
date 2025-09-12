<?php

class Model_Customchart extends \Orm\Model
{
    protected static $_table_name = 'custom_chart';

    protected static $_primary_key = ['project_id'];

    protected static $_properties = [
        'project_id',
        'size_x',
        'size_y',
    ];

    protected static $_belongs_to = [
        'project' => [
            'key_from' => 'project_id',
            'model_to' => 'Model_Project',
            'key_to'   => 'id',
        ],
    ];

    protected static $_has_many = [
        'cells' => [
            'key_from' => 'project_id',
            'model_to' => 'Model_Customchartcell',
            'key_to'   => 'project_id',
            'cascade_save' => true,
            'cascade_delete' => true,
        ],
    ];

    // --- READ ---

    // カラーチャートデータの取得
    public static function get_chart($user_id, $project_id) {
        $ownership = \Model_Project::verify_ownership($user_id, $project_id);
        if (!$ownership) {
            return ['success' => false, 'message' => 'Project not found or access denied'];
        }

        \Log::info('Retrieving custom chart for project_id: ' . $project_id, __METHOD__);

        try {
            $custom_chart = static::query()
                ->where('project_id', $project_id)
                ->get_one();

            if (!$custom_chart) {
                return ['success' => true, 'chart' => null];
            }

            // Load associated cells
            $cells = \Model_Customchartcell::query()
                ->where('project_id', $project_id)
                ->get();

            $cells_data = [];
            foreach ($cells as $cell) {
                $cells_data[] = [
                    'x' => $cell->position_x,
                    'y' => $cell->position_y,
                    'color' => $cell->color,
                ];
            }

            return [
                'success' => true,
                'chart' => [
                    'size_x' => $custom_chart->size_x,
                    'size_y' => $custom_chart->size_y,
                    'cells'  => $cells_data,
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Get custom chart error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error retrieving custom chart'];
        }
    }

    // --- CREATE / UPDATE / DELETE ---

    // カラーチャートデータの保存
    // 作成、編集、削除も可能
    public static function save_chart($user_id, $project_id, $height, $width, $cells_data) {
        $ownership = \Model_Project::verify_ownership($user_id, $project_id);
        if (!$ownership) {
            return ['success' => false, 'message' => 'Project not found or access denied'];
        }

        \Log::info('Saving custom chart for project_id: ' . $project_id, __METHOD__);

        try {
            \DB::start_transaction();

            $custom_chart = static::query()->where('project_id', $project_id)->get_one();

            if (!$custom_chart) {
                $custom_chart = static::forge();
                $custom_chart->project_id = $project_id;
                
            } 

            $custom_chart->size_x = $width;
            $custom_chart->size_y = $height;

            $custom_chart->save();

            \Model_Customchartcell::query()->where('project_id', $project_id)->delete();

            foreach ($cells_data as $cell) {
                $new_cell = \Model_Customchartcell::forge();
                $new_cell->project_id = $project_id;
                $new_cell->position_x = $cell['x'];
                $new_cell->position_y = $cell['y'];
                $new_cell->color = $cell['color'];
                $new_cell->save();
            }

            \DB::commit_transaction();

            return ['success' => true];
        } catch (\Exception $e) {
            \DB::rollback_transaction();
            \Log::error('Save custom chart error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error saving custom chart'];
        }
    }
}