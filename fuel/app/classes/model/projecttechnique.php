<?php

class Model_ProjectTechnique extends \Orm\Model
{
  protected static $_table_name = 'project_technique';

  protected static $_primary_key = ['project_id', 'technique'];

  protected static $_properties = [
    'project_id',
    'technique',
  ];

  protected static $_belongs_to = [
    'project' => [
      'key_from' => 'project_id',
      'model_to' => 'Model_Project',
      'key_to'   => 'id',
    ],
  ];
}