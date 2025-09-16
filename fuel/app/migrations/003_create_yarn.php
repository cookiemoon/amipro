<?php


namespace Fuel\Migrations;

class Create_yarn
{
  public function up()
  {
    \DBUtil::create_table('yarn', array(
      'id' => array('constraint' => 16, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
      'user_id' => array('constraint' => 16, 'type' => 'int', 'unsigned' => true),
      'name' => array('constraint' => 32, 'type' => 'varchar'),
      'brand' => array('constraint' => 32, 'type' => 'varchar', 'null' => true),
      'color' => array('constraint' => 255, 'type' => 'varchar'),
      'size' => array('constraint' => 255, 'type' => 'varchar'),
      'fiber_animal' => array('constraint' => 1, 'type' => 'int', 'null' => true),
      'fiber_plant' => array('constraint' => 1, 'type' => 'int', 'null' => true),
      'fiber_synthetic' => array('constraint' => 1, 'type' => 'int', 'null' => true),
      'fiber_desc' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),
      'project_id' => array('constraint' => 16, 'type' => 'int', 'unsigned' => true, 'null' => true),
    ), array('id'));

    // Create foreign key index for user_id
    \DBUtil::create_index('yarn', 'user_id', 'user_id_index');
    
    // Create index for brand
    \DBUtil::create_index('yarn', 'brand', 'brand_index');

    // Create foreign key index for project_id
    \DBUtil::create_index('yarn', 'project_id', 'project_id_index');
  }

  public function down()
  {
    \DBUtil::drop_table('yarn');
  }
}