<?php

namespace Fuel\Migrations;

class Create_users
{
  public function up()
  {
    \DBUtil::create_table('users', array(
      'id' => array('constraint' => 16, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
      'username' => array('constraint' => 255, 'type' => 'varchar'),
      'password' => array('constraint' => 255, 'type' => 'varchar'),
      'created_at' => array('type' => 'datetime'),
    ), array('id'));

    // Create unique index for username
    \DBUtil::create_index('users', 'username', 'username_unique', 'UNIQUE');
  }

  public function down()
  {
    \DBUtil::drop_index('users', 'username_unique');

    \DBUtil::drop_table('users');
  }
}