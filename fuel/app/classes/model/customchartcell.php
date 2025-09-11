<?php

class Model_Customchartcell extends \Orm\Model
{
    protected static $_table_name = 'custom_chart_cells';

    // Define the composite primary key
    protected static $_primary_key = ['project_id', 'position_x', 'position_y'];

    // Define the table columns
    protected static $_properties = [
        'project_id',
        'position_x',
        'position_y',
        'color',
    ];

    // Define the relationship back to the main project model
    protected static $_belongs_to = [
        'project' => [
            'key_from' => 'project_id',
            'model_to' => 'Model_Project',
            'key_to'   => 'id',
        ],
    ];
}