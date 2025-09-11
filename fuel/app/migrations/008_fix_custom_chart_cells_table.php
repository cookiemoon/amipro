<?php

namespace Fuel\Migrations;

class Fix_custom_chart_cells_table
{
    public function up()
    {
        \DBUtil::drop_fields('custom_chart_cells', ['color_r', 'color_g', 'color_b']);

        \DBUtil::add_fields('custom_chart_cells', [
            'color' => ['type' => 'varchar', 'constraint' => 7, 'after' => 'position_y']
        ]);
    }

    public function down()
    {
        \DBUtil::add_fields('custom_chart_cells', [
            'color_r' => ['type' => 'int', 'constraint' => 8, 'after' => 'position_y'],
            'color_g' => ['type' => 'int', 'constraint' => 8, 'after' => 'color_r'],
            'color_b' => ['type' => 'int', 'constraint' => 8, 'after' => 'color_g'],
        ]);

        \DBUtil::drop_fields('custom_chart_cells', ['color']);
    }
}