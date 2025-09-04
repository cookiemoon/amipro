<?php
/**
 * Yarn Model
 * This file has been corrected for fatal errors.
 */
class Model_Yarn extends \Orm\Model // FIX: Must extend \Orm\Model
{
    protected static $_table_name = 'yarn';
    
    protected static $_properties = [
        'id', 'user_id', 'name', 'brand', 'color', 'weight', 'fiber_content',
        'yardage', 'grams', 'price', 'purchase_date', 'notes', 'image_path',
        'created_at', 'updated_at',
    ];

    protected static $_observers = [
        'Orm\Observer_CreatedAt' => ['events' => ['before_insert'], 'mysql_timestamp' => true],
        'Orm\Observer_UpdatedAt' => ['events' => ['before_update'], 'mysql_timestamp' => true],
    ];

    protected static $_belongs_to = array(
        'user' => array(
            'key_from' => 'user_id',
            'model_to' => 'Model_User',
            'key_to' => 'id',
        )
    );

    protected static $_has_many = array(
        'projects' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Project',
            'key_to' => 'yarn_id',
        )
    );

    public static function get_user_yarn_with_filters($user_id, $filters = [])
    {
        try {
            $query = static::query()->where('user_id', $user_id);
            
            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $query->where_open()
                      ->where('name', 'LIKE', $search)
                      ->or_where('brand', 'LIKE', $search)
                      ->where_close();
            }
            
            // NOTE: Add logic for fiber_type and weight filters here if needed
            
            $yarn_items = $query->order_by('created_at', 'desc')->get();
            
            $formatted_yarn = array_map([__CLASS__, 'format_yarn_for_display'], $yarn_items);
            
            return [
                'yarn'    => $formatted_yarn,
                'filters' => [
                    'fiber_types' => static::get_fiber_types(),
                    'weights'     => static::get_yarn_weights()
                ],
            ];
            
        } catch (\Exception $e) {
            \Log::error('Get user yarn with filters error: ' . $e->getMessage());

            return [
                'yarn'    => [],
                'filters' => [
                    'fiber_types' => [],
                    'weights'     => [],
                ],
            ];
        }
    }

    public static function create_yarn($user_id, $data)
    {
        // ... (validation logic should be in the controller)
        $yarn = static::forge([
            'user_id' => $user_id,
            'name' => $data['name'],
            'brand' => $data['brand'],
            'color' => $data['color'],
            'weight' => $data['weight'],
        ]);

        if ($yarn->save()) {
            return ['success' => true, 'yarn_id' => $yarn->id];
        }
        return ['success' => false, 'message' => '毛糸の保存に失敗しました。'];
    }

    // ADDED: This helper method was missing.
    public static function get_fiber_types()
    {
        return [
            'animal' => '動物性繊維',
            'plant' => '植物性繊維',
            'synthetic' => '化学繊維',
        ];
    }
    
    public static function get_yarn_weights()
    {
        return [ 'lace' => 'レース', 'fingering' => 'フィンガリング', 'sport' => 'スポーツ', 'dk' => 'DK', 'worsted' => 'ワーステッド'];
    }
    
    // ... (other helper methods like format_yarn_for_display)
    protected static function format_yarn_for_display($yarn)
    {
        return $yarn->to_array(); // Simplified for example
    }
}