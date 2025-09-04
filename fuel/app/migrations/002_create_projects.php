<?php


namespace Fuel\Migrations;

class Create_projects
{
    public function up()
    {
        \DBUtil::create_table('projects', array(
            'id' => array('constraint' => 16, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
            'name' => array('constraint' => 32, 'type' => 'varchar'),
            'user_id' => array('constraint' => 16, 'type' => 'int', 'unsigned' => true),
            'created_at' => array('type' => 'date'),
            'completed_at' => array('type' => 'date', 'null' => true),
            'object_type' => array('constraint' => 10, 'type' => 'varchar', 'null' => true),
            'status' => array('constraint' => 3, 'type' => 'int', 'default' => '0'),
            'progress' => array('constraint' => 7, 'type' => 'int', 'null' => true),
            'screenshot_url' => array('type' => 'text', 'null' => true),
            'colorwork_url' => array('type' => 'text', 'null' => true),
            'memo' => array('type' => 'text', 'null' => true),
            'row_counter' => array('constraint' => 16, 'type' => 'int', 'default' => 0, 'unsigned' => true),
        ), array('id'));

        // Create foreign key index for user_id
        \DBUtil::create_index('projects', 'user_id', 'user_id_index');
        
        // Create index for project_type
        \DBUtil::create_index('projects', 'object_type', 'object_type_index');
        
        // Create index for status
        \DBUtil::create_index('projects', 'status', 'status_index');
    }

    public function down()
    {
        \DBUtil::drop_table('projects');
    }
}