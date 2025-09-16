<?php

namespace Fuel\Migrations;

class Create_project_technique
{
  public function up()
  {
    \DBUtil::create_table('project_technique', [
      'project_id' => ['type' => 'int', 'constraint' => 16, 'unsigned' => true],
      'technique'  => ['type' => 'varchar', 'constraint' => 255],
    ], 
    ['project_id', 'technique']);
  }

  public function down()
  {
    \DBUtil::drop_table('project_technique');
  }
}