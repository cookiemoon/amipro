<?php

namespace Fuel\Migrations;

class Create_custom_chart
{
  public function up()
  {
    \DBUtil::create_table('custom_chart', [
      'project_id' => ['type' => 'int', 'constraint' => 16, 'unsigned' => true],
      'size_x'  => ['type' => 'int', 'constraint' => 6],
      'size_y'  => ['type' => 'int', 'constraint' => 6],
    ], ['project_id']);
  }

  public function down()
  {
    \DBUtil::drop_table('project_technique');
  }
}