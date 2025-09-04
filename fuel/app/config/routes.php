<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

return array(
    // --- Core Routes ---
    '_root_'  => 'projects/index', // Send logged-in users to projects
    '_404_'   => 'welcome/404',    // Your 404 page

    // --- Authentication Routes ---
    'login' => 'auth/login',
    'register' => 'auth/register',
    'logout'  => 'auth/logout',

    // --- Main Application Routes ---
    'dashboard' => 'projects/index',

    // Project routes
    'projects'                 => 'projects/index',
    'projects/create'          => 'projects/create',
    'projects/process_create'  => 'projects/process_create', // Keeps the form action
    'projects/detail/(:num)'   => 'projects/detail/$1',
    'projects/edit/(:num)'     => 'projects/edit/$1',
    'projects/update/(:num)'   => 'projects/update/$1',
    'projects/delete/(:num)'   => 'projects/delete/$1',

    // Yarn routes (now correctly under the 'projects' controller)
    'yarn'                     => 'projects/yarn',
    'yarn/create'              => 'projects/add_yarn',
    'yarn/process_create'      => 'projects/create_yarn',

    // --- AJAX / API Routes ---
    'projects/filter'          => 'projects/filter',
    'projects/yarn_filter'     => 'projects/yarn_filter',
);