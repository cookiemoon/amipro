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
    // Root redirect to login
    '_root_' => 'auth/login',
    
    // Authentication routes
    'login' => 'auth/login',
    'register' => 'auth/register',
    'auth/login' => 'auth/login',
    'auth/process_login' => 'auth/process_login',
    'auth/register' => 'auth/register',
    'auth/process_register' => 'auth/process_register',
    'auth/logout' => 'auth/logout',

    // Dashboard (after login) - redirect to projects
    'dashboard' => 'projects/index',

    // Project Management Routes
    'projects' => 'projects/index',
    'projects/index' => 'projects/index',
    'projects/yarn' => 'projects/yarn',
    'projects/create' => 'projects/create',
    'projects/process_create' => 'projects/process_create',
    'projects/detail/(:num)' => 'projects/detail/$1',
    'projects/edit/(:num)' => 'projects/edit/$1',
    'projects/update/(:num)' => 'projects/update/$1',
    'projects/delete/(:num)' => 'projects/delete/$1',
    
    // AJAX Routes
    'projects/filter' => 'projects/filter',
    'projects/update_progress/(:num)' => 'projects/update_progress/$1',
    
    // Yarn Management Routes
    'yarn' => 'projects/yarn',
    'yarn/create' => 'yarn/create',
    'yarn/edit/(:num)' => 'yarn/edit/$1',
    'yarn/delete/(:num)' => 'yarn/delete/$1',
    
    // Static pages
    'about' => 'pages/about',
    'contact' => 'pages/contact',
    
    // API routes (if needed)
    'api/auth/login' => 'api/auth/login',
    'api/auth/register' => 'api/auth/register',
    'api/auth/logout' => 'api/auth/logout',
    
    // Catch-all route (keep this last)
    '_404_' => 'welcome/404',
);
