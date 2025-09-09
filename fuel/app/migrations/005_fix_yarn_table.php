<?php

namespace Fuel\Migrations;

class Fix_yarn_table
{
    public function up()
    {
        \DBUtil::modify_fields('yarn', [
            'color' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        \DBUtil::modify_fields('yarn', [
            'size' => [
                'name' => 'weight',
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {

        \DBUtil::modify_fields('yarn', [
            'weight' => [
                'name' => 'size',
                'type' => 'varchar',
                'constraint' => 255,
                'null' => false,
            ],
        ]);

        \DBUtil::modify_fields('yarn', [
            'color' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => false,
            ],
        ]);
    }
}
