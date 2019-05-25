<?php
/**
 * Created by PhpStorm.
 * User: Rafael
 * Date: 28/08/2018
 * Time: 12:31 AM
 */

use Symfony\Component\Yaml\Yaml;

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| If this is not set then CodeIgniter will try guess the protocol, domain
| and path to your installation. However, you should always configure this
| explicitly and never rely on auto-guessing, especially in production
| environments.
|
*/

$config['sys_title'] = 'Estic - Admin';
$config['sys_name'] = 'estic Framework para desarrollo agil';

$config['sess_key_admin'] = 'admin_loggedin';
$config['sess_key_estic'] = 'estic_loggedin';
$config['sess_key_sys'] = 'sys_loggedin';
$config['sess_key'] = 'loggedin';
$config['sess_table'] = 'ci_users';
$config['sess_idTable'] = 'id_user';
$config['sess_object'] = 'oUser';

$config['var_excepts'] = [null,'',""];
$config['tab_excepts'] = ['migrations'];

$config['tab_titles'] = ['nombre','name','title','titulo','apellido','lastname'];

$config['isysDirs']= array(
    'modules' => [
        'sys' => 'HMVC',
    ],
    'migrations' => [
        'tables' => 'TAB'
    ]
);

$config['ormDirs'] = array(
    'crud' => [
        'admin' => 'HMVC',
        'base' => 'HMVC',
        'front' => 'HMVC'
    ]
);

$config['dirs'] = array(
    'isys' => [
        'modules' => [
            'base' => 'HMVC',
        ],
    ],
    'app' => [
        'modules' => [
            'admin' => 'HMVC',
            'base' => 'HMVC',
            'front' => 'HMVC'
        ],
        'layouts' => [
            'backend' => 'HMVC',
            'frontend' => 'HMVC'
        ],
        'services' => 'HMVC',
    ],
    'orm' => [
        'crud' => [
            'admin' => 'HMVC',
            'base' => 'HMVC',
            'front' => 'HMVC'
        ],
        'migrations' => [
            'tables' => 'TAB'
        ]
    ]
);


$config['mig_table'] = 'es_tables';
$config['mig_path'] = 'orm/migrations/tables/';

$config['english_words'] = ['files','sessions','roles','settings','cities','modules','tables','domains'];
$config['controlFields'] = ['status','estado','change_count','id_user_modified','id_user_created','date_modified','date_created'];


// -------------------------- Configuraciones para la subida de archivos ------------------------
$config['file_max_size'] = 100000000;
$config['file_types'] = 'gif|jpg|png|jpeg|pdf|docx|xlsx|zip|mp4|mp3';
$config['file_types_js'] = '.gif,.jpg,.png,.jpeg,.pdf,.docx,.xlsx,.zip,.mp4,.mp3,image/*,application/pdf,video/*,audio/*';
$config['file_without_tumbs'] = '.xlsx|.docx|.zip|.pdf|.mp4|.mp3';
$config['img_max_width'] = 10000;
$config['img_max_height'] = 10000;
// --------------------------- Fin configuraciones para la subida de archivos -------------------------

// -------------------------- Configuraciones del proyecto -------------------------------

$config['soft_name'] = 'herbalife';
$config['soft_user_id'] = '1';
$config['soft_user'] = 'rafaelgutierrez';
$config['soft_user_name'] = 'Rafael Gutierrez Gaspar';
$config['soft_user_email'] = 'rafarobertgu@gmail.com';
$config['soft_user_role'] = '1';

$config['appDirs'] = array(
  'modules' => [
    'admin' => 'HMVC',
    'front' => 'HMVC'
  ],
);


$file_content = file_get_contents(PWD.'orm/propel.yml');
$options = Yaml::parse($file_content);
$config['database']['dev']['name'] = '';
$config['database']['dev']['user'] = '';
$config['database']['dev']['pass'] = '';
$config['database']['prod']['name'] = '';
$config['database']['prod']['user'] = '';
$config['database']['prod']['pass'] = '';
$config['database']['test']['name'] = '';
$config['database']['test']['user'] = '';
$config['database']['test']['pass'] = '';

foreach ($options['propel']['database']['connections'] as $dbMode => $dbSettings){
    $dbName = strGet($dbSettings['dsn'],'dbname=');

  if(strstr($dbName,'dev')){
    $config['database']['dev']['name'] = $dbName;
    $config['database']['dev']['user'] = $dbSettings['user'];
    $config['database']['dev']['pass'] = $dbSettings['password'];
  } else if(strstr($dbName,'prod')){
    $config['database']['prod']['name'] = $dbName;
    $config['database']['prod']['user'] = $dbSettings['user'];
    $config['database']['prod']['pass'] = $dbSettings['password'];
  } else if(strstr($dbName,'test')){
    $config['database']['test']['name'] = $dbName;
    $config['database']['test']['user'] = $dbSettings['user'];
    $config['database']['test']['pass'] = $dbSettings['password'];
  }
}
