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
    'weight',
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

  // --- READ ---

  // ユーザーの毛糸一覧を取得（オプションで利用可能な毛糸のみ）
  public static function get_user_yarn($user_id, $available=false)
  {
    try {
      if ($available) {
        $query = static::query()->where('user_id', $user_id)
                    ->where('project_id', null);
      } else {
        $query = static::query()->where('user_id', $user_id);
      }
      
      $yarn_items = $query->order_by(\DB::expr("brand = ''"), 'asc')
                    ->order_by('brand','asc')
                    ->order_by('name', 'asc')
                    ->get();
      
      $formatted_yarn = array_values(array_map([__CLASS__, 'format_yarn_for_display'], $yarn_items));
      
      return [
        'yarn'  => $formatted_yarn,
      ];
      
    } catch (\Exception $e) {
      \Log::error('Get user yarn with filters error: ' . $e->getMessage());

      return [
        'yarn'  => [],
      ];
    }
  }

  // フィルターオプションの取得
  protected static function get_fiber_types()
  {
    return [
      'Animal' => 'Animal',
      'Plant' => 'Plant',
      'Synthetic' => 'Synthetic',
    ];
  }

  // フィルターオプションの取得
  protected static function get_yarn_weights()
  {
    return [
      'All' => 'All',
      'Superfine' => 'Superfine',
      'Fine' => 'Fine',
      'Light' => 'Light',
      'Medium' => 'Medium',
      'Bulky' => 'Bulky',
      'Super Bulky' => 'Super Bulky',
      'Jumbo' => 'Jumbo',
    ];
  }

  // フィルターオプションの取得
  public static function get_available_filters()
  {
    return [
      'weight' => static::get_yarn_weights(),
      'fiber'  => static::get_fiber_types(),
    ];
  }

  // 毛糸データの表示用フォーマット
  protected static function format_yarn_for_display($yarn)
  {
    $fiber_types = [];

    if ($yarn->fiber_animal) {
      $fiber_types[] = 'Animal';
    }
    if ($yarn->fiber_plant) {
      $fiber_types[] = 'Plant';
    }
    if ($yarn->fiber_synthetic) {
      $fiber_types[] = 'Synthetic';
    }

    return [
      'id' => $yarn->id,
      'name' => $yarn->name,
      'brand' => $yarn->brand,
      'color' => $yarn->color,
      'weight' => $yarn->weight ? $yarn->weight : "Unknown",
      'fiber_types' => $fiber_types,
      'fiber_desc' => $yarn->fiber_desc,
      'project_name' => $yarn->project ? $yarn->project->name : "Unregistered",
      'project_id' => $yarn->project ? $yarn->project->id : null,
    ];
  }

  // --- DELETE ---

  // 毛糸を削除
  public static function delete_user_yarn($user_id, $yarn_id)
  {
    try {
      \Log::info('Attempting to delete yarn_id ' . $yarn_id . ' for user_id ' . $user_id, __METHOD__);
      $yarn = static::query()->where('id', $yarn_id)
                  ->where('user_id', $user_id)
                  ->get_one();
      
      if ($yarn) {
        $yarn->delete();
        return ['success' => true];
      } else {
        return ['success' => false, 'error' => 'not_found'];
      }
    } catch (\Exception $e) {
      \Log::error('Delete user yarn error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'server_error'];
    }
  }

  // --- CREATE ---

  // 毛糸を作成
  public static function create_yarn($user_id, $data)
  {
    \Log::info('Creating new yarn for user_id ' . $user_id, __METHOD__);
    
    if (isset($data['project_id']) && $data['project_id'] != "null") {
      $ownership = \Model_Project::verify_ownership($user_id, $data['project_id'] ?? null);
      if (!$ownership) {
        \Log::warning('Attempt to associate yarn with invalid project_id ' . $data['project_id'] . ' for user_id ' . $user_id);
        $project_id = null;
      } else {
        $project_id = $data['project_id'];
      }
    } else {
      $project_id = null;
    }

    $weight = in_array($data['weight'], array_keys(static::get_yarn_weights())) ? $data['weight'] : null;
    
    $yarn = static::forge([
      'user_id' => $user_id,
      'name' => $data['name'],
      'brand' => $data['brand'],
      'color' => $data['color'],
      'weight' => $weight,
      'fiber_animal' => $data['fiber_animal'] == "true" ? 1 : 0,
      'fiber_plant' => $data['fiber_plant'] == "true" ? 1 : 0,
      'fiber_synthetic' => $data['fiber_synthetic'] == "true" ? 1 : 0,
      'fiber_desc' => $data['fiber_desc'],
      'project_id' => $project_id ?? null,
    ]);

    if ($yarn->save()) {
      return ['success' => true, 'yarn_id' => $yarn->id];
    }

    \Log::error('Failed to save yarn for user_id ' . $user_id);
    return ['success' => false, 'message' => '毛糸の保存に失敗しました。'];
  }

  // --- UPDATE ---

  // 毛糸を編集
  public static function edit_user_yarn($user_id, $yarn_id, $data) {
    try {
      \Log::info('Attempting to edit yarn_id ' . $yarn_id . ' for user_id ' . $user_id, __METHOD__);
      $yarn = static::query()->where('id', $yarn_id)
                  ->where('user_id', $user_id)
                  ->get_one();
      
      if (!$yarn) {
        return ['success' => false, 'error' => 'not_found'];
      }

      if (isset($data['project_id']) && $data['project_id'] != "null") {
        $project = Model_Project::query()
          ->where('id', $data['project_id'])
          ->where('user_id', $user_id)
          ->get_one();
        if (!$project) {
          \Log::warning('Attempt to associate yarn with invalid project_id ' . $data['project_id'] . ' for user_id ' . $user_id);
          $yarn->project_id = null;
        } else {
          $yarn->project_id = $data['project_id'];
        }
      } else {
        $yarn->project_id = null;
      }

      $yarn->name = $data['name'];
      $yarn->brand = $data['brand'];
      $yarn->color = $data['color'];
      $yarn->weight = $data['weight'];
      $yarn->fiber_animal = $data['fiber_animal'] == "true" ? 1 : 0;
      $yarn->fiber_plant = $data['fiber_plant'] == "true" ? 1 : 0;
      $yarn->fiber_synthetic = $data['fiber_synthetic'] == "true" ? 1 : 0;
      $yarn->fiber_desc = $data['fiber_desc'];

      if ($yarn->save()) {
        return ['success' => true];
      } else {
        \Log::error('Failed to update yarn for user_id ' . $user_id . ' and yarn_id ' . $yarn_id);
        return ['success' => false, 'error' => 'server_error'];
      }
    } catch (\Exception $e) {
      \Log::error('Edit user yarn error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'server_error'];
    }
  }
}