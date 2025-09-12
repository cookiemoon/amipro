<?php

class Model_Customchartcell extends \Orm\Model
{
    protected static $_table_name = 'custom_chart_cells';

    protected static $_primary_key = ['project_id', 'position_x', 'position_y'];

    protected static $_properties = [
        'project_id',
        'position_x',
        'position_y',
        'color',
    ];

    protected static $_belongs_to = [
        'project' => [
            'key_from' => 'project_id',
            'model_to' => 'Model_Project',
            'key_to'   => 'id',
        ],
    ];
}