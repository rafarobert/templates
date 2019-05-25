<?php
/**
 * Created by PhpStorm.
 * User: Rafael
 * Date: 28/08/2018
 * Time: 12:12 AM
 */

// ================= Base routes ====================
$route['sys'] = 'backend/sys/dashboard';
$route['base'] = 'backend/estic/dashboard';
$route['sys/dashboard'] = 'backend/sys/dashboard';
// ================= Migration paths ================
$route['sys/migrate/fromdatasys'] = 'sys/migrate/fromdatabase';
$route['sys/migrate/([a-zA-Z]+)/(:num)'] = 'sys/migrate/run/$0/$1/$2';
$route['sys/migrate/([a-zA-Z]+)/(:num)'] = 'sys/migrate/run/$0/$1/$2';
$route['sys/migrate/([a-zA-Z]+)'] = 'sys/migrate/run/$0/$1';
$route['sys/migrate/([a-zA-Z]+)'] = 'sys/migrate/run/$0/$1';
$route['sys/migrate/(:num)'] = 'sys/migrate/run/$0/$1';
$route['sys/migrate/(:num)'] = 'sys/migrate/run/$0/$1';
// ===================================================
