<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');
use Symfony\Component\Yaml\Yaml;

if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost:4200'){
    header('Access-Control-Allow-Origin: http://localhost:4200');
}

/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @package		CodeIgniter
 * @subpackage	CodeIgniter
 * @category	Front-controller
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/
 */

/**
 * CodeIgniter Version
 *
 * @var	string
 *
 */

define('CI_VERSION', '3.0.6');

/**
 * CodeIgniter Languaje
 *
 * @var	string
 *
 */

define('LANGUAGE', 'spanish');

/*
 * ------------------------------------------------------
 *  Set time limit for migrating tables from database with migrate class
 * ------------------------------------------------------
 */
set_time_limit(2000);

/*
 * ------------------------------------------------------
 *  Set zlib.output_compression On  ¡Precaucion, mejor dejarlo en off!
 * ------------------------------------------------------
 */

//ini_set("zlib.output_compression", "Off");

/*
 * ------------------------------------------------------
 *  Defining timezone
 * ------------------------------------------------------
 */
date_default_timezone_set('America/La_Paz');

/*
 * ------------------------------------------------------
 *  Defining Document Root
 * ------------------------------------------------------
 */
define('DOCUMENTROOT',PWD !== '' ? PWD : $_SERVER['DOCUMENT_ROOT'] . '/');

require_once PWD . 'vendor/autoload.php';

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
require_once(BASEPATH.'core/Common.php');

if(is_file(DOCUMENTROOT."orm/map/ES_Table_Vars.php")){

    require_once DOCUMENTROOT."orm/map/ES_Table_Vars.php";
}

/*
 * ------------------------------------------------------
 *  Defining Proyect Settings
 * ------------------------------------------------------
 */

if (file_exists(BASEPATH. 'axis/config/config.php')) {

  require_once  BASEPATH. 'axis/config/config.php';

} else if (file_exists(PWD . 'isys/axis/config/config.php')) {

  require_once PWD . 'isys/axis/config/config.php';

}

if (file_exists(DOCUMENTROOT . 'app/config/config.php')) {

  require_once DOCUMENTROOT . 'app/config/config.php';

} else if (file_exists(PWD . 'app/config/config.php')) {

  require_once PWD . 'app/config/config.php';

}

if (file_exists(DOCUMENTROOT . 'app/config/config_host.php')) {

  require_once DOCUMENTROOT . 'app/config/config_host.php';

} else if (file_exists(PWD . 'app/config/config_host.php')) {

  require_once PWD . 'app/config/config_host.php';

}

if (file_exists(DOCUMENTROOT . 'app/config/config_sys.php')) {

  require_once DOCUMENTROOT . 'app/config/config_sys.php';

} else if (file_exists(PWD . 'app/config/config_sys.php')) {

  require_once PWD . 'app/config/config_sys.php';

}

//if(isset($config)){
//
//  $proyName = $config['proy_name'];
//
//  $currentPath = $config['proy_current_path'];
//
//  $hostName = $config['proy_hostname'];
//
//  $protocol = $config['proy_protocol'];
//
//} else {
//
//  echo 'No se encontro el archivo de configuracion';
//
//  exit(1);
//}

/*
 * ------------------------------------------------------
 *  Defining Proyect Settings
 * ------------------------------------------------------
 */
$proyName = $config['proy_name'];

$aServers = $config[$proyName];

$aServer = [];

foreach ($aServers as $server) {

    if($server['hostname'] == $_SERVER['SERVER_NAME'] || $server['hostname-core'] == $_SERVER['SERVER_NAME']){

        $aServer = $server;

        break;
    }
}

if(!isArray($aServer)){

    echo 'The application does not have a correct path, review the properties at the config/servers folder.';

    exit();
}

$config['proy_current_path'] = $aServer['root-path'];
$config['proy_hostname'] = $aServer['hostname'];
$config['proy_protocol'] = $aServer['protocol'];



// ----------------------- Enviroment Rules ------------------------

if($aServer['type-env'] == 'dev'){

    define('ENVIRONMENT', 'development');

} else if($aServer['type-env'] == 'test') {

    define('ENVIRONMENT', 'testing');

} else if($aServer['type-env'] == 'prod') {

    define('ENVIRONMENT', 'production');
}



// ----------------------- Configuraciones para desarrollo ---------------------------
//if($hostName == '127.0.0.1' || $hostName == 'localhost')
//{
//    define('ENVIRONMENT', 'development');
//    define('LOCALFOLDER', "$proyName/");
//    $rootPath = strhas(DOCUMENTROOT, $proyName) ? DOCUMENTROOT : DOCUMENTROOT . "$proyName/";
//    $webServer = "$protocol://$hostName/$proyName/";
//}
//else if ($hostName == "local.$proyName.com" || strstr($hostName,'127.0.0.'))
//{
//    define('ENVIRONMENT', 'development');
//    define('LOCALFOLDER', "$proyName/");
//    $rootPath = strhas(DOCUMENTROOT, $proyName) ? DOCUMENTROOT : DOCUMENTROOT . "$proyName/";
//    $webServer = "$protocol://$hostName/";
//}
//else if ($hostName == '192.168.1.10' || $hostName == '192.168.2.103' )
//{
//    define('ENVIRONMENT', 'development');
//    define('LOCALFOLDER', '');
//    $rootPath = DOCUMENTROOT;
//    $webServer = "$protocol://$hostName/";
//}
// ------------------------------------------------------------------------------------
// ------------------------ Configuraciones para Testing ------------------------------
//else if ($hostName == "test.$proyName.com" || $hostName == '192.168.1.11')
//{
//    define('ENVIRONMENT', 'testing');
//    define('LOCALFOLDER', '');
//    $rootPath = DOCUMENTROOT;
//    $webServer = "$protocol://$hostName/";
//}
// ------------------------------------------------------------------------------
// ------------------ Configuraciones para produccion --------------------------
//else if ($hostName == "desarrollo.estic.com.bo" || $hostName == '192.168.2.21')
//{
//    define('ENVIRONMENT', 'production');
//    define('LOCALFOLDER', '');
//    $rootPath = DOCUMENTROOT;
//    $webServer = "$protocol://$hostName/";
//}
// ------------------------------------------------------------------------------

//else {
//    echo "<h2>Ocurrio un error con la direccion IP o el nombre del hostname: $hostName, verifica que el mismo este configurado en el servidor</h2>";
//    echo dump($_SERVER);
//    exit();
//}

define('LOCALFOLDER', $aServer['root-path']);
define('DIRECTORY',DOCUMENTROOT.$aServer['root-path']);
define('ROOTPATH', DOCUMENTROOT.$aServer['root-path']);
define('WEBSERVER', $aServer['origin']);
define('WEBASSETS', $aServer['origin'].'assets/');
define('WEBROOT', $aServer['origin']);
define('PROTOCOL', $aServer['protocol']);
define('SERVERNAME', isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $aServer['hostname']);


/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

switch (ENVIRONMENT)
{
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'production':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;
//        if (version_compare(PHP_VERSION, '5.3', '>='))
//        {
//            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
//        }
//        else
//        {
//            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
//        }
//        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}


/*
 * ------------------------------------------------------
 *  Defining Proyect Front Settings
 * ------------------------------------------------------
 */
//if(file_exists(DOCUMENTROOT . 'app/config/config_frt.php'))
//{
//    require_once DOCUMENTROOT . 'app/config/config_frt.php';
//
//} else if(file_exists(PWD . 'app/config/config_frt.php')) {
//
//  require_once PWD . 'app/config/config_frt.php';
//
//} else {
//
//    echo 'No se encontro el archivo de configuracion';
//
//    exit(1);
//}

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder than the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server. If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
$application_folder = 'app';

/*
 *---------------------------------------------------------------
 * VIEW FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view folder out of the application
 * folder set the path to the folder here. The folder can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application folder. If you
 * do move this, use the full server path to this folder.
 *
 * NO TRAILING SLASH!
 */
$view_folder = 'layouts';

/*
 *---------------------------------------------------------------
 * ORM FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view folder out of the application
 * folder set the path to the folder here. The folder can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application folder. If you
 * do move this, use the full server path to this folder.
 *
 * NO TRAILING SLASH!
 */
$orm_folder = 'orm';


/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 */
// The directory name, relative to the "controllers" folder.  Leave blank
// if your controller is not in a sub-folder within the "controllers" folder
// $routing['directory'] = '';

// The controller class file name.  Example:  mycontroller
// $routing['controller'] = '';

// The controller function you wish to be called.
// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 */
// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

// Set the current directory correctly for CLI requests
if (defined('STDIN'))
{
    chdir(dirname(__FILE__));
}
//
//if (($_temp = realpath($system_path)) !== FALSE)
//{
//    $system_path = $_temp.'/';
//}
//else
//{
//    // Ensure there's a trailing slash
//    $system_path = rtrim($system_path, '/').'/';
//}

if ( ! is_dir($system_path) && validateVar(PWD))
{
  $system_path = PWD.$system_path;
}
if( ! is_dir($application_folder) && validateVar(PWD)){

  $application_folder = PWD.$application_folder;
}
if ( ! is_dir($orm_folder) && validateVar(PWD)) {

  $orm_folder = PWD.$orm_folder;
}

// Is the system path correct?

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));


// Path to the front controller (this file)
define('FCPATH', str_replace('\\', '/', dirname(__FILE__).'/'));


// Is the system path correct?

if (is_dir($system_path))
{
  if (($_temp = realpath($system_path)) !== FALSE)
  {
    $system_path = $_temp.DIRECTORY_SEPARATOR;
  }
}
else
{
  if ( ! is_dir(BASEPATH.$system_path.DIRECTORY_SEPARATOR))
  {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
    exit(3); // EXIT_CONFIG
  }
}



// The path to the "application" folder

if (is_dir($application_folder))
{
    if (($_temp = realpath($application_folder)) !== FALSE)
    {
//        $application_folder = $_temp.DIRECTORY_SEPARATOR;
    }

    define('APPPATH', str_replace('\\', '/', $_temp.DIRECTORY_SEPARATOR));
}
else
{
    if ( ! is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3); // EXIT_CONFIG
    }

    define('APPPATH', str_replace('\\', '/', BASEPATH.$application_folder.DIRECTORY_SEPARATOR));
}

// The path to the "orm" folder

if (is_dir($orm_folder))
{
    if (($_temp = realpath($orm_folder)) !== FALSE)
    {
//      $orm_folder= $_temp.DIRECTORY_SEPARATOR;
    }

    define('ORMPATH', str_replace('\\', '/', $orm_folder.DIRECTORY_SEPARATOR));
}
else
{
    if ( ! is_dir($orm_folder.DIRECTORY_SEPARATOR))
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3); // EXIT_CONFIG
    }

    define('ORMPATH', str_replace('\\', '/', PWD.$orm_folder.DIRECTORY_SEPARATOR));
}

// The path to the "$view_folder" folder
if(validateVar(APPPATH)){

  if (is_dir(APPPATH.$view_folder))
  {
    if (($_temp = realpath(APPPATH.$view_folder)) !== FALSE)
    {
//      $view_folder = $_temp.DIRECTORY_SEPARATOR;
    }

    define('VIEWPATH', str_replace('\\', '/', $_temp.DIRECTORY_SEPARATOR));
  }
  else
  {
    if ( ! is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
    {
      header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
      echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
      exit(3); // EXIT_CONFIG
    }

    define('VIEWPATH', str_replace('\\', '/', APPPATH.$view_folder.DIRECTORY_SEPARATOR));
  }

}
//
//// The path to the "views" folder
//if ( ! is_dir($view_folder))
//{
//    if ( ! empty($view_folder) && is_dir($view_folder.DIRECTORY_SEPARATOR))
//    {
//        $view_folder = APPPATH.$view_folder;
//    }
//    elseif ( ! is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
//    {
//        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
//        echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
//        exit(3); // EXIT_CONFIG
//    }
//    else
//    {
//        $view_folder = APPPATH.'layouts';
//    }
//}
//
//if (($_temp = realpath($view_folder)) !== FALSE)
//{
//    $view_folder = $_temp.DIRECTORY_SEPARATOR;
//}
//else
//{
//    $view_folder = rtrim($view_folder, '/\\').DIRECTORY_SEPARATOR;
//}

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */

define('MODULEPATH',APPPATH.'modules/');
define('STORAGE_PATH',APPPATH.'migrations/storage/');

// Composer autoload
// require_once ROOTPATH . 'vendor/autoload.php';

// Propel2 config load
//require_once ROOTPATH . 'vendor/propel/propel/src/Propel/Runtime/Propel.php';
require_once ROOTPATH . 'orm/config/config.php';
//Propel::init(ROOTPATH . 'orm/config/config.php');


//require_once ROOTPATH . 'vendor/propel/propel/bin/Propel.php';

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */

	if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php'))
	{
		require_once(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
	}

	require_once(APPPATH.'config/constants.php');

/*
 * ------------------------------------------------------
 * Security procedures
 * ------------------------------------------------------
 */

if ( ! is_php('5.4'))
{
	ini_set('magic_quotes_runtime', 0);

	if ((bool) ini_get('register_globals'))
	{
		$_protected = array(
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_REQUEST',
			'_SESSION',
			'_ENV',
			'_COOKIE',
			'GLOBALS',
			'HTTP_RAW_POST_DATA',
			'system_path',
			'application_folder',
			'view_folder',
			'_protected',
			'_registered'
		);

		$_registered = ini_get('variables_order');
		foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal)
		{
			if (strpos($_registered, $key) === FALSE)
			{
				continue;
			}

			foreach (array_keys($$superglobal) as $var)
			{
				if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE))
				{
					$GLOBALS[$var] = NULL;
				}
			}
		}
	}
}


/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');

/*
 * ------------------------------------------------------
 *  Set the subclass_prefix
 * ------------------------------------------------------
 *
 * Normally the "subclass_prefix" is set in the config file.
 * The subclass prefix allows CI to know if a core class is
 * being extended via a library in the local application
 * "libraries" folder. Since CI allows config items to be
 * overridden via data set in the main index.php file,
 * before proceeding we need to know if a subclass_prefix
 * override exists. If so, we will set this value now,
 * before any classes are loaded
 * Note: Since the config file data is cached it doesn't
 * hurt to load it here.
 */
	if ( ! empty($assign_to_config['subclass_prefix']))
	{
		get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
	}

/*
 * ------------------------------------------------------
 *  Should we use a Composer autoloader?
 * ------------------------------------------------------
 */
	if ($composer_autoload = config_item('composer_autoload'))
	{
		if ($composer_autoload === TRUE)
		{
			file_exists(ROOTPATH.'vendor/autoload.php')
				? require_once(ROOTPATH.'vendor/autoload.php')
				: log_message('error', '$config[\'composer_autoload\'] is set to TRUE but '.ROOTPATH.'vendor/autoload.php was not found.');
		}
		elseif (file_exists($composer_autoload))
		{
			require_once($composer_autoload);
		}
		else
		{
			log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: '.$composer_autoload);
		}
	}


/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 *
 * Note: It is important that Config is loaded first as
 * most other classes depend on it either directly or by
 * depending on another class that uses it.
 *
 */
$CFG =& load_class('Config', 'core');

// Do we have any manually set config items in the index.php file?
if (isset($assign_to_config) && is_array($assign_to_config))
{
    foreach ($assign_to_config as $key => $value)
    {
        $CFG->set_item($key, $value);
    }
}

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 * ------------------------------------------------------
 */
	$BM =& load_class('Benchmark', 'core');
	$BM->mark('total_execution_time_start');
	$BM->mark('loading_time:_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */
	$EXT =& load_class('Hooks', 'core');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_system');


/*
 * ------------------------------------------------------
 * Important charset-related stuff
 * ------------------------------------------------------
 *
 * Configure mbstring and/or iconv if they are enabled
 * and set MB_ENABLED and ICONV_ENABLED constants, so
 * that we don't repeatedly do extension_loaded() or
 * function_exists() calls.
 *
 * Note: UTF-8 class depends on this. It used to be done
 * in it's constructor, but it's _not_ class-specific.
 *
 */
	$charset = strtoupper(config_item('charset'));
	ini_set('default_charset', $charset);

	if (extension_loaded('mbstring'))
	{
		define('MB_ENABLED', TRUE);
		// mbstring.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('mbstring.internal_encoding', $charset);
		// This is required for mb_convert_encoding() to strip invalid characters.
		// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
		mb_substitute_character('none');
	}
	else
	{
		define('MB_ENABLED', FALSE);
	}

	// There's an ICONV_IMPL constant, but the PHP manual says that using
	// iconv's predefined constants is "strongly discouraged".
	if (extension_loaded('iconv'))
	{
		define('ICONV_ENABLED', TRUE);
		// iconv.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('iconv.internal_encoding', $charset);
	}
	else
	{
		define('ICONV_ENABLED', FALSE);
	}

	if (is_php('5.6'))
	{
		ini_set('php.internal_encoding', $charset);
	}

/*
 * ------------------------------------------------------
 *  Load compatibility features
 * ------------------------------------------------------
 */

	require_once(BASEPATH.'core/compat/mbstring.php');
	require_once(BASEPATH.'core/compat/hash.php');
	require_once(BASEPATH.'core/compat/password.php');
	require_once(BASEPATH.'core/compat/standard.php');

/*
 * ------------------------------------------------------
 *  Instantiate the UTF-8 class
 * ------------------------------------------------------
 */
	$UNI =& load_class('Utf8', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the URI class
 * ------------------------------------------------------
 */
	$URI =& load_class('URI', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
	$RTR =& load_class('Router', 'core', isset($routing) ? $routing : NULL);

/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
	$OUT =& load_class('Output', 'core');

/*
 * ------------------------------------------------------
 *	Is there a valid cache file? If so, we're done...
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE)
	{
		exit;
	}

/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
	$SEC =& load_class('Security', 'core');

/*
 * ------------------------------------------------------
 *  Load the Input class and sanitize globals
 * ------------------------------------------------------
 */
	$IN	=& load_class('Input', 'core');

/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
	$LANG =& load_class('Lang', 'core');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
	// Load the base controller class
	require_once BASEPATH.'core/Controller.php';

	/**
	 * Reference to the CI_Controller method.
	 *
	 * Returns current CI instance object
	 *
	 * @return CI_Controller
	 */
	function &get_instance()
	{
		return CI_Controller::get_instance();
	}

	if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php'))
	{
		require_once APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
	}
	else if(file_exists(BASEPATH.'axis/'.$CFG->config['subclass_prefix'].'Controller.php'))
	{
        require_once BASEPATH.'axis/'.$CFG->config['subclass_prefix'].'Controller.php';
    }

	// Set a mark point for benchmarking
	$BM->mark('loading_time:_base_classes_end');



    function getframePath($modulo = '', $subMod = ''){
        $URI =& load_class('URI', 'core');
        $RTR =& load_class('Router', 'core', isset($routing) ? $routing : NULL);
        $CONF =& get_config();
        $SYS = $CONF['sys'];
        $DIRS = $CONF['dirs'];
        $isBasePath = false;
        $isAppPath = false;
        $isOrmPath = false;
        $validating = false;

        // **************** Establece el nombre del modulo *****************
        if($modulo == ''){
            $modulo = !in_array($RTR->module,$CONF['var_excepts']) ? $RTR->module : $URI->segments[1];
        } else {
            if(is_array($SYS[$modulo])){
                $modulo = $SYS[$modulo]['name'];
            } else {
                $modulo = $SYS[$modulo];
            }
        }
        $directory = $RTR->directory;
        // **************** Establece el nombre de la clase ****************
        if($subMod == ''){
            $class = $RTR->class;
        } else {
            $class = $subMod;
            $validating = true;
        }

        $bIsDir = false;
        foreach ($DIRS as $root => $dirs){
            foreach ($dirs as $dir => $mods){
              if(is_array($mods)){

                foreach ($mods as $mod => $type){
                    if($type == "HMVC"){
                        if(is_dir($ruta = ROOTPATH . "$root/$directory/$class/") && $class != ''){
                            $bIsDir = true;
                        } else if (is_dir($ruta = ROOTPATH . "$root/$dir/$modulo/$class/") && $class != '') {
                            $bIsDir = true;
                        }

                        if($bIsDir){
                            $class = "Ctrl_".ucfirst($class);
                            $classFile = $ruta."$class.php";
                            if(is_file($classFile)){
                                $content = file_get_contents($classFile);
                                $cont_funct = explode('function', $content);
                                unset($cont_funct[0]);
                                $funct = function($array){
                                    return str_replace(' ','',explode('(',$array)[0]);
                                };
                                $functions = array_map($funct,$cont_funct);
                                if(in_array($RTR->method, $functions) && !$validating){
                                    if($subMod == ''){
                                        return ROOTPATH."$root/";
                                    } else {
                                        $directorio = ROOTPATH."$root/$dir/$modulo/$subMod/";
                                        if(is_dir($directorio)){
                                            return $directorio;
                                        } else {
                                            return ROOTPATH."$root/";
                                        }
                                    }
                                } else {
                                    if($subMod == ''){
                                        return ROOTPATH."$root/";
                                    } else {
                                        $directorio = ROOTPATH."$root/$dir/$modulo/$subMod/";
                                        if(is_dir($directorio)){
                                            return $directorio;
                                        } else {
                                            return ROOTPATH."$root/";
                                        }
                                    }
                                }
                            } else {
                                if($subMod == ''){
                                    return ROOTPATH."$root/";
                                } else {
                                    $directorio = ROOTPATH."$root/$dir/$modulo/$subMod/";
                                    if(is_dir($directorio)){
                                        return $directorio;
                                    } else {
                                        return ROOTPATH."$root/";
                                    }
                                }
                            }
                        }
                    }
                }
              } else {
                if($subMod == ''){
                  return ROOTPATH."$root/";
                } else {
                  $directorio = ROOTPATH."$root/$dir/$modulo/$subMod/";
                  if(is_dir($directorio)){
                    return $directorio;
                  } else {
                    return ROOTPATH."$root/";
                  }
                }
              }
            }
        }
        return null;
    }

/*
 * ------------------------------------------------------
 *  Sanity checks
 * ------------------------------------------------------
 *
 *  The Router class has already validated the request,
 *  leaving us with 3 options here:
 *
 *	1) an empty class name, if we reached the default
 *	   controller, but it didn't exist;
 *	2) a query string which doesn't go through a
 *	   file_exists() check
 *	3) a regular request for a non-existing page
 *
 *  We handle all of these as a 404 error.
 *
 *  Furthermore, none of the methods in the app controller
 *  or the loader class can be called via the URI, nor can
 *  controller methods that begin with an underscore.
 */

	$e404 = FALSE;

    if(isset($URI->segments[1])) {
        $framePath = getframePath();
    } else {
        $framePath = APPPATH;
    }

    if ($RTR->directory == null )
    {
        $RTR->directory = 'testFrame/';
    }

    $ctrlClass =  'Ctrl_'.ucfirst($RTR->class);
    $class = $RTR->class;
    $directory = $RTR->directory;
    $method = $RTR->method;


    $RTR->framePath =  $framePath;

    if(strstr($directory,"/$class/")){
      $file = "$framePath".$directory."$ctrlClass.php";
    } else {
      $file = "$framePath".$directory."$class/$ctrlClass.php";
    }

	if (empty($class) OR ! file_exists($file))
	{
		$e404 = TRUE;
	}
	else
	{
		require_once($framePath.$RTR->directory.$class.'/'.$ctrlClass.'.php');

		if ( ! class_exists($ctrlClass, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method))
		{
			$e404 = TRUE;
		}
		elseif (method_exists($ctrlClass, '_remap'))
		{
			$params = array($method, array_slice($URI->rsegments, 2));
			$method = '_remap';
		}
		// WARNING: It appears that there are issues with is_callable() even in PHP 5.2!
		// Furthermore, there are bug reports and feature/change requests related to it
		// that make it unreliable to use in this context. Please, DO NOT change this
		// work-around until a better alternative is available.
		elseif ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($ctrlClass)), TRUE))
		{
			$e404 = TRUE;
		}
	}

	if ($e404)
	{
		if ( ! empty($RTR->routes['404_override']))
		{
			if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2)
			{
				$error_method = 'index';
			}

			$error_class = ucfirst($error_class);

			if ( ! class_exists($error_class, FALSE))
			{
				if (file_exists(APPPATH.$RTR->directory.'/'.$error_class.'.php'))
				{
					require_once(APPPATH.$RTR->directory.'/'.$error_class.'.php');
					$e404 = ! class_exists($error_class, FALSE);
				}
				// Were we in a directory? If so, check for a global override
				elseif ( ! empty($RTR->directory) && file_exists(APPPATH.$RTR->directory.'/'.$error_class.'.php'))
				{
					require_once(APPPATH.$error_class.'.php');
					if (($e404 = ! class_exists($error_class, FALSE)) === FALSE)
					{
						$RTR->directory = '';
					}
				}
			}
			else
			{
				$e404 = FALSE;
			}
		}

		// Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
		if ( ! $e404)
		{
			$class = $error_class;
			$method = $error_method;

			$URI->rsegments = array(
				1 => $class,
				2 => $method
			);
		}
		else
		{
			show_404($RTR->directory.$class.'/'.$method);
		}
	}

	if ($method !== '_remap')
	{
		$params = array_slice($URI->rsegments, 2);
	}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the requested controller
 * ------------------------------------------------------
 */
	// Mark a start point so we can benchmark the controller
	$BM->mark('controller_execution_time_( '.$ctrlClass.' / '.$method.' )_start');

	$CI = new $ctrlClass();

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller_constructor');


/*
 * ------------------------------------------------------
 *  Call the requested method
 * ------------------------------------------------------
 */
$methodsExcepts = ['signup','login'];

$classExcepts = ['ajax'];

// if User logu¿gued in continue

if($RTR->module == 'frontend'){

    $response = call_user_func_array(array(&$CI, $method), $params);

    $CI->data['subview'] = isset($CI->data['subview']) ? $CI->data['subview'] : '';

    $CI->load->view($CI->data['layout'], $CI->data);

} else {


    if(objectHas($CI,'oUserLogguedIn')){

        $response = call_user_func_array(array(&$CI, $method), $params);

        // or if the url is in an except path
    } else if(in_array($method ,$methodsExcepts) || in_array($class,$classExcepts)){

        $response = call_user_func_array(array(&$CI, $method), $params);

    } else if($CI->fromFiles){

        $response = call_user_func_array(array(&$CI, $method), $params);

    } else if($method == 'index' && $class == 'dashboard') {

        $response = call_user_func_array(array(&$CI, $method), $params);

    } else {

        $response = [];
    }

    $CI->data['response'] = $response;

// If the url comes from ajax show as json



    if($CI->fromAjax || $ctrlClass == 'Ctrl_Ajax' || $CI->input->post('fromAjax') || $class == 'ajax'){

        if($response){

            echo safe_json_encode($response);

        } else {

            $CI->data['subLayout'] = 'pages/lockscreen';
            $CI->load->view($CI->data['layout'], $CI->data);
        }

    } else if(isset($_SERVER['SHELL'])) {

        if(objectHas($CI,'oUserLogguedIn')){

            echo 'done!
        ';
        } else {

            echo 'Algo salio mal, probablemente debes iniciar sesión
        ';
        }

        exit(1);

    } else {

        if(!objectHas($CI,'oUserLogguedIn')){

            $CI->data['subLayout'] = 'pages/login';
        }
        // if not it displays the content
        $CI->data['subview'] = isset($CI->data['subview']) ? $CI->data['subview'] : '';
        $CI->load->view($CI->data['layout'], $CI->data);
    }

}

	// Mark a benchmark end point
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('display_override') === FALSE)
	{
		$OUT->_display();
	}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_system');

