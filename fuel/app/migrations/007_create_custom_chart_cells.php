<?php

namespace Fuel\Migrations;

class Create_custom_chart_cells
{
  public function up()
  {
    \DBUtil::create_table('custom_chart_cells', [
      'project_id' => ['type' => 'int', 'constraint' => 16, 'unsigned' => true],
      'position_x'  => ['type' => 'int', 'constraint' => 6],
      'position_y'  => ['type' => 'int', 'constraint' => 6],
      'color_r' => ['type' => 'int', 'constraint' => 8],
      'color_g' => ['type' => 'int', 'constraint' => 8],
      'color_b' => ['type' => 'int', 'constraint' => 8],
    ],
    ['project_id', 'position_x', 'position_y']);
  }

  public function down()
  {
    \DBUtil::drop_table('project_technique');
  }
}