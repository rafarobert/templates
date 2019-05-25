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

/**
 * Common Functions
 *
 * Loads the base classes and executes the request.
 *
 * @package		CodeIgniter
 * @subpackage	CodeIgniter
 * @category	Common Functions
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/
 */

// ------------------------------------------------------------------------

if ( ! function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string
	 * @return	bool	TRUE if the current version is $version or higher
	 */
	function is_php($version)
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_is_php[$version];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_really_writable'))
{
	/**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link	https://bugs.php.net/bug.php?id=54709
	 * @param	string
	 * @return	bool
	 */
	function is_really_writable($file)
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR ! ini_get('safe_mode')))
		{
			return is_writable($file);
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if (is_dir($file))
		{
			$file = rtrim($file, '/').'/'.md5(mt_rand());
			if (($fp = @fopen($file, 'ab')) === FALSE)
			{
				return FALSE;
			}

			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);
			return TRUE;
		}
		elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		return TRUE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('load_class'))
{
	/**
	 * Class registry
	 *
	 * This function acts as a singleton. If the requested class does not
	 * exist it is instantiated and set to a static variable. If it has
	 * previously been instantiated the variable is returned.
	 *
	 * @param	string	the class name being requested
	 * @param	string	the directory where the class should be found
	 * @param	string	an optional argument to pass to the class constructor
	 * @return	object
	 */
	function &load_class($class, $directory = 'libraries', $param = NULL)
	{
		static $_classes = array();

		// Does the class exist? If so, we're done...
		if (isset($_classes[$class]))
		{
			return $_classes[$class];
		}

		$name = FALSE;

		// Look for the class first in the local application/libraries folder
		// then in the native system/libraries folder
		foreach (array(APPPATH, BASEPATH, ORMPATH) as $path)
		{
			if (file_exists($path.$directory.'/'.$class.'.php'))
			{
				$name = 'CI_'.$class;

				if (class_exists($name, FALSE) === FALSE)
				{
					require_once($path.$directory.'/'.$class.'.php');
				}

				break;
			}
		}

		// Is the request a class extension? If so we load it too
		if (file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
		{
			$name = config_item('subclass_prefix').$class;

			if (class_exists($name, FALSE) === FALSE)
			{
				require_once(APPPATH.$directory.'/'.$name.'.php');
			}
		} else if(file_exists(BASEPATH.'axis/'.config_item('subclass_prefix').$class.'.php'))
		{
            $name = config_item('subclass_prefix').$class;

            if (class_exists($name, FALSE) === FALSE)
            {
                require_once(BASEPATH.'axis/'.$name.'.php');
            }
        } else if(file_exists($directory)){
            $name = $class;

            if (class_exists($name, FALSE) === FALSE)
            {
                require_once($directory);
            }
        }

		// Did we find the class?
		if ($name === FALSE)
		{
			// Note: We use exit() rather than show_error() in order to avoid a
			// self-referencing loop with the Exceptions class
			set_status_header(503);
			if(compareStrStr(ENVIRONMENT, 'production')){
			    include APPPATH."layouts/pages/error_503.php";
			    exit();
            }
			echo 'Unable to locate the specified class: '.$class.'.php';
			exit(5); // EXIT_UNK_CLASS
		}

		// Keep track of what we just loaded
		is_loaded($class);

		$_classes[$class] = isset($param)
			? new $name($param)
			: new $name();
		return $_classes[$class];
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('is_loaded'))
{
	/**
	 * Keeps track of which libraries have been loaded. This function is
	 * called by the load_class() function above
	 *
	 * @param	string
	 * @return	array
	 */
	function &is_loaded($class = '')
	{
		static $_is_loaded = array();

		if ($class !== '')
		{
			$_is_loaded[strtolower($class)] = $class;
		}

		return $_is_loaded;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_config'))
{
	/**
	 * Loads the main config.php file
	 *
	 * This function lets us grab the config file even if the Config class
	 * hasn't been instantiated yet
	 *
	 * @param	array
	 * @return	array
	 */
	function &get_config(Array $replace = array())
	{
		static $config;

		if (empty($config))
		{
			$file_path = APPPATH . 'config/config.php';
			$found = FALSE;
			if (file_exists($file_path))
			{
				$found = TRUE;
				require($file_path);
			}

            $file_path = BASEPATH . 'axis/config/config.php';
            $found = FALSE;
            if (file_exists($file_path))
            {
                $found = TRUE;
                require($file_path);
            }

			// Is the config file in the environment folder?
			if (file_exists($file_path = APPPATH . 'config/'.ENVIRONMENT.'/config.php'))
			{
				require($file_path);
			}
			elseif ( ! $found)
			{
				set_status_header(503);
				echo 'The configuration file does not exist.';
				exit(3); // EXIT_CONFIG
			}

			// Does the $config array exist in the file?
			if ( ! isset($config) OR ! is_array($config))
			{
				set_status_header(503);
				echo 'Your config file does not appear to be formatted correctly.';
				exit(3); // EXIT_CONFIG
			}
		}

		// Are any values being dynamically added or replaced?
		foreach ($replace as $key => $val)
		{
			$config[$key] = $val;
		}

		return $config;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('config_item'))
{
	/**
	 * Returns the specified config item
	 *
	 * @param	string
	 * @return	mixed
	 */
	function config_item($item)
	{
		static $_config;

		if (empty($_config))
		{
			// references cannot be directly assigned to static variables, so we use an array
			$_config[0] =& get_config();
		}

		return isset($_config[0][$item]) ? $_config[0][$item] : NULL;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_mimes'))
{
	/**
	 * Returns the MIME types array from config/mimes.php
	 *
	 * @return	array
	 */
	function &get_mimes()
	{
		static $_mimes;

		if (empty($_mimes))
		{
			if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/mimes.php'))
			{
				$_mimes = include(APPPATH.'config/'.ENVIRONMENT.'/mimes.php');
			}
			elseif (file_exists(APPPATH.'config/mimes.php'))
			{
				$_mimes = include(APPPATH.'config/mimes.php');
			}
			else
			{
				$_mimes = array();
			}
		}

		return $_mimes;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_https'))
{
	/**
	 * Is HTTPS?
	 *
	 * Determines if the application is accessed via an encrypted
	 * (HTTPS) connection.
	 *
	 * @return	bool
	 */
	function is_https()
	{
		if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		{
			return TRUE;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		{
			return TRUE;
		}
		elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
		{
			return TRUE;
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_cli'))
{

	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 *
	 * @return 	bool
	 */
	function is_cli()
	{
		return (PHP_SAPI === 'cli' OR defined('STDIN'));
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('show_error'))
{
	/**
	 * Error Handler
	 *
	 * This function lets us invoke the exception class and
	 * display errors using the standard error template located
	 * in application/views/errors/error_general.php
	 * This function will send the error page directly to the
	 * browser and exit.
	 *
	 * @param	string
	 * @param	int
	 * @param	string
	 * @return	void
	 */
	function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
	{
		$status_code = abs($status_code);
		if ($status_code < 100)
		{
			$exit_status = $status_code + 9; // 9 is EXIT__AUTO_MIN
			if ($exit_status > 125) // 125 is EXIT__AUTO_MAX
			{
				$exit_status = 1; // EXIT_ERROR
			}

			$status_code = 500;
		}
		else
		{
			$exit_status = 1; // EXIT_ERROR
		}

		$_error =& load_class('Exceptions', 'core');

        /**
         * @var Model_Logs $CI
         */
        if(!strstr($message,'estic/logs')){


          $framePath = getframePath('es','logs');
          if(is_dir($framePath)){
            if(file_exists($framePath.'Ctrl_Logs.php') &&
              file_exists($framePath.'Model_Logs.php') &&
              is_dir($framePath.'views/')){

              if(class_exists('Ctrl_Logs')){
                $CI = Ctrl_Logs::create()->init();
                $data['heading'] = $heading;
                $data['action'] = $CI->uri->uri_string;
                $data['message'] = $message;
                $data['exit_status'] = $exit_status;
                $data['code'] = $status_code;
                $data['post'] = $CI->input->post();$data['level'] = $_error->ob_level;
                $CI->model_logs->save($data);
              }
            } else {
              echo "El modulo estic/logs no pudo ser encontrado, revisa que la direccion este bien establecida
              ";
            }
          } else {
            echo "El modulo estic/logs no pudo ser encontrado, revisa que la direccion este bien establecida
            ";
          }
          return true;
        } else{
          echo $_error->show_error($heading, $message, 'error_general', $status_code);
        }

//		exit($exit_status);
	}
}

if ( ! function_exists('show_error_handled'))
{
	/**
	 * Error Handler
	 *
	 * This function lets us invoke the exception class and
	 * display errors using the standard error template located
	 * in application/views/errors/error_general.php
	 * This function will send the error page directly to the
	 * browser and exit.
	 *
	 * @param	string
	 * @param	int
	 * @param	string
	 * @return	void
	 */
    function show_error_handled($message, $status_code = 500, $heading = 'An Error Was Encountered')
    {
        $status_code = abs($status_code);
        if ($status_code < 100)
        {
            $exit_status = $status_code + 9; // 9 is EXIT__AUTO_MIN
            if ($exit_status > 125) // 125 is EXIT__AUTO_MAX
            {
                $exit_status = 1; // EXIT_ERROR
            }

            $status_code = 500;
        }
        else
        {
            $exit_status = 1; // EXIT_ERROR
        }

        $_error =& load_class('Exceptions', 'core');
        echo $_error->show_error($heading, $message, 'error_general', $status_code);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('show_404'))
{
	/**
	 * 404 Page Handler
	 *
	 * This function is similar to the show_error() function above
	 * However, instead of the standard error template it displays
	 * 404 errors.
	 *
	 * @param	string
	 * @param	bool
	 * @return	void
	 */
	function show_404($page = '', $log_error = TRUE)
	{
		$_error =& load_class('Exceptions', 'core');
		$_error->show_404($page, $log_error);
		exit(4); // EXIT_UNKNOWN_FILE
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('log_message'))
{
	/**
	 * Error Logging Interface
	 *
	 * We use this as a simple mechanism to access the logging
	 * class and send messages to be logged.
	 *
	 * @param	string	the error level: 'error', 'debug' or 'info'
	 * @param	string	the error message
	 * @return	void
	 */
	function log_message($level, $message)
	{
		static $_log;

		if ($_log === NULL)
		{
			// references cannot be directly assigned to static variables, so we use an array
			$_log[0] =& load_class('Log', 'core');
		}

		$_log[0]->write_log($level, $message);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_status_header'))
{
	/**
	 * Set HTTP Status Header
	 *
	 * @param	int	the status code
	 * @param	string
	 * @return	void
	 */
	function set_status_header($code = 200, $text = '')
	{
		if (is_cli())
		{
			return;
		}

		if (empty($code) OR ! is_numeric($code))
		{
			show_error('Status codes must be numeric', 500);
		}

		if (empty($text))
		{
			is_int($code) OR $code = (int) $code;
			$stati = array(
				100	=> 'Continue',
				101	=> 'Switching Protocols',

				200	=> 'OK',
				201	=> 'Created',
				202	=> 'Accepted',
				203	=> 'Non-Authoritative Information',
				204	=> 'No Content',
				205	=> 'Reset Content',
				206	=> 'Partial Content',

				300	=> 'Multiple Choices',
				301	=> 'Moved Permanently',
				302	=> 'Found',
				303	=> 'See Other',
				304	=> 'Not Modified',
				305	=> 'Use Proxy',
				307	=> 'Temporary Redirect',

				400	=> 'Bad Request',
				401	=> 'Unauthorized',
				402	=> 'Payment Required',
				403	=> 'Forbidden',
				404	=> 'Not Found',
				405	=> 'Method Not Allowed',
				406	=> 'Not Acceptable',
				407	=> 'Proxy Authentication Required',
				408	=> 'Request Timeout',
				409	=> 'Conflict',
				410	=> 'Gone',
				411	=> 'Length Required',
				412	=> 'Precondition Failed',
				413	=> 'Request Entity Too Large',
				414	=> 'Request-URI Too Long',
				415	=> 'Unsupported Media Type',
				416	=> 'Requested Range Not Satisfiable',
				417	=> 'Expectation Failed',
				422	=> 'Unprocessable Entity',

				500	=> 'Internal Server Error',
				501	=> 'Not Implemented',
				502	=> 'Bad Gateway',
				503	=> 'Service Unavailable',
				504	=> 'Gateway Timeout',
				505	=> 'HTTP Version Not Supported'
			);

			if (isset($stati[$code]))
			{
				$text = $stati[$code];
			}
			else
			{
				show_error('No status text available. Please check your status code number or supply your own message text.', 500);
			}
		}

		if (strpos(PHP_SAPI, 'cgi') === 0)
		{
			header('Status: '.$code.' '.$text, TRUE);
		}
		else
		{
			$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';

			header($server_protocol.' '.$code.' '.$text, TRUE, $code);
		}
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_error_handler'))
{
	/**
	 * Error Handler
	 *
	 * This is the custom error handler that is declared at the (relative)
	 * top of CodeIgniter.php. The main reason we use this is to permit
	 * PHP errors to be logged in our own log files since the user may
	 * not have access to server logs. Since this function effectively
	 * intercepts PHP errors, however, we also need to display errors
	 * based on the current error_reporting level.
	 * We do that with the use of a PHP error template.
	 *
	 * @param	int	$severity
	 * @param	string	$message
	 * @param	string	$filepath
	 * @param	int	$line
	 * @return	void
	 */
	function _error_handler($severity, $message, $filepath, $line)
	{
		$is_error = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

		// When an error occurred, set the status header to '500 Internal Server Error'
		// to indicate to the client something went wrong.
		// This can't be done within the $_error->show_php_error method because
		// it is only called when the display_errors flag is set (which isn't usually
		// the case in a production environment) or when errors are ignored because
		// they are above the error_reporting threshold.
		if ($is_error)
		{
			set_status_header(500);
		}

		// Should we ignore the error? We'll get the current error_reporting
		// level and add its bits with the severity bits to find out.
		if (($severity & error_reporting()) !== $severity)
		{
			return;
		}

		$_error =& load_class('Exceptions', 'core');
		$_error->log_exception($severity, $message, $filepath, $line);

		// Should we display the error?
		if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors')))
		{
			$_error->show_php_error($severity, $message, $filepath, $line);
		}

		// If the error is fatal, the execution of the script should be stopped because
		// errors can't be recovered from. Halting the script conforms with PHP's
		// default error handling. See http://www.php.net/manual/en/errorfunc.constants.php

        /**
         * @var Model_Logs $CI
         */
        if(validate_modulo('estic','logs')){
            $CI = Ctrl_Logs::create()->init();
            $data['severity'] = $severity;
            $data['message'] = $message;
            $data['file'] = $filepath;
            $data['line'] = $line;
            $data['post'] = $CI->input->post();
            $data['level'] = $_error->ob_level;
            $CI->model_logs->save($data);
        }

		if ($is_error)
		{
			exit(1); // EXIT_ERROR
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_exception_handler'))
{
	/**
	 * Exception Handler
	 *
	 * Sends uncaught exceptions to the logger and displays them
	 * only if display_errors is On so that they don't show up in
	 * production environments.
	 *
	 * @param	Exception	$exception
	 * @return	void
	 */

	function _exception_handler($exception)
	{
		$_error =& load_class('Exceptions', 'core');
		$_error->log_exception('error', 'Exception: '.$exception->getMessage(), $exception->getFile(), $exception->getLine());

		// Should we display the error?
		if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors')))
		{
			$_error->show_exception($exception);
		}

		/**
         * @var Model_Logs $CI
         */
		if(validate_modulo('estic','logs')){
            $CI = Ctrl_Logs::create()->init();
            $data['message'] = $exception->getMessage();
            $data['code'] = $exception->getCode();
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            $data['previous'] = $exception->getPrevious();
            $data['trace'] = $exception->getTraceAsString();
            $data['level'] = $_error->ob_level;
            $data['post'] = $CI->input->post();

            $CI->model_logs->save($data);
        }
        echo $exception->getMessage();
		exit(1); // EXIT_ERROR
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_shutdown_handler'))
{
	/**
	 * Shutdown Handler
	 *
	 * This is the shutdown handler that is declared at the top
	 * of CodeIgniter.php. The main reason we use this is to simulate
	 * a complete custom exception handler.
	 *
	 * E_STRICT is purposively neglected because such events may have
	 * been caught. Duplication or none? None is preferred for now.
	 *
	 * @link	http://insomanic.me.uk/post/229851073/php-trick-catching-fatal-errors-e-error-with-a
	 * @return	void
	 */
	function _shutdown_handler()
	{


		$last_error = error_get_last();
		if (isset($last_error) &&
			($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
		{
			_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('remove_invisible_characters'))
{
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('html_escape'))
{
	/**
	 * Returns HTML escaped variable.
	 *
	 * @param	mixed	$var		The input string or array of strings to be escaped.
	 * @param	bool	$double_encode	$double_encode set to FALSE prevents escaping twice.
	 * @return	mixed			The escaped string or array of strings as a result.
	 */
	function html_escape($var, $double_encode = TRUE)
	{
		if (empty($var))
		{
			return $var;
		}

        if(is_object($var))
        {
            $var = std2array($var);
        }

		if (is_array($var))
		{
			foreach (array_keys($var) as $key)
			{
				$var[$key] = html_escape($var[$key], $double_encode);
			}

			return $var;
		}


		return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_stringify_attributes'))
{
	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param	mixed	string, array, object
	 * @param	bool
	 * @return	string
	 */
	function _stringify_attributes($attributes, $js = FALSE)
	{
		$atts = NULL;

		if (empty($attributes))
		{
			return $atts;
		}

		if (is_string($attributes))
		{
			return ' '.$attributes;
		}

		$attributes = (array) $attributes;

		foreach ($attributes as $key => $val)
		{
			$atts .= ($js) ? $key.'='.$val.',' : ' '.$key.'="'.$val.'"';
		}

		return rtrim($atts, ',');
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('function_usable'))
{
	/**
	 * Function usable
	 *
	 * Executes a function_exists() check, and if the Suhosin PHP
	 * extension is loaded - checks whether the function that is
	 * checked might be disabled in there as well.
	 *
	 * This is useful as function_exists() will return FALSE for
	 * functions disabled via the *disable_functions* php.ini
	 * setting, but not for *suhosin.executor.func.blacklist* and
	 * *suhosin.executor.disable_eval*. These settings will just
	 * terminate script execution if a disabled function is executed.
	 *
	 * The above described behavior turned out to be a bug in Suhosin,
	 * but even though a fix was commited for 0.9.34 on 2012-02-12,
	 * that version is yet to be released. This function will therefore
	 * be just temporary, but would probably be kept for a few years.
	 *
	 * @link	http://www.hardened-php.net/suhosin/
	 * @param	string	$function_name	Function to check for
	 * @return	bool	TRUE if the function exists and is safe to call,
	 *			FALSE otherwise.
	 */
	function function_usable($function_name)
	{
		static $_suhosin_func_blacklist;

		if (function_exists($function_name))
		{
			if ( ! isset($_suhosin_func_blacklist))
			{
				$_suhosin_func_blacklist = extension_loaded('suhosin')
					? explode(',', trim(ini_get('suhosin.executor.func.blacklist')))
					: array();
			}

			return ! in_array($function_name, $_suhosin_func_blacklist, TRUE);
		}

		return FALSE;
	}
}

if (!function_exists('strhas')) {
    function strhas($string, $obj)
    {
        $strCase1 = ucfirst($obj);
        $strCase2 = strtoupper($obj);
        $strCase3 = strtolower($obj);
        if(!validateVar($string)){
            return false;
        }
        if(strpos($string,$obj) > -1 || strpos($string,$strCase1) > -1 || strpos($string,$strCase2) > -1 || strpos($string,$strCase3) > -1){
            return true;
        } else if(preg_match("/\b$obj\b/",$string)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('compareStrStr')) {
    function compareStrStr($string1, $string2, $anyway = true)
    {
        if(validateVar($string1) && validateVar($string1)){
            if($anyway){
                if($string1 == $string2 || $string1 == ucfirst($string2) || $string1 == strtoupper($string2) || strhas($string1, $string2)){
                    return true;
                } else {
                    return false;
                }
            } else {
                return $string1 == $string2 ? true : false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('compareBoolBool')) {
    function compareBoolBool($string1, $bool, $anyway = true)
    {
        if ($anyway) {
            if ($string1 == $bool) {
                return true;
            } else {
                return false;
            }
        } else {
            return $string1 == $bool ? true : false;
        }
    }
}

if (!function_exists('compareStrNum')) {
    function compareStrNum($string1, $num, $anyway = true)
    {
        if($anyway){
            if($string1 == $num ){
                return true;
            } else {
                return false;
            }
        } else {
            return $string1 == $num ? true : false;
        }
    }
}

if (!function_exists('compareArrayStr')) {
    function compareArrayStr($array, $index, $string2, $anyway = true)
    {
        $string1 = '';
        if (validateArray($array, $index)) {
            if (isset($array[$index])) {
                $string1 = $array[$index];
            } else {
                return false;
            }
            return compareStrStr($string1, $string2, $anyway);
        }
        return false;
    }
}

if (!function_exists('compareArrayBool')) {
    function compareArrayBool($array, $index, $bool2, $anyway = true)
    {
        $bool1 = '';
        if (validateArray($array, $index)) {
            if (isset($array[$index])) {
                $bool1 = $array[$index];
            } else {
                return false;
            }
            return compareBoolBool($bool1, $bool2, $anyway);
        }
        return false;
    }
}

if (!function_exists('compareArrayArray')) {
    function compareArrayArray($arrayItems, $arrayIn, $anyway = true)
    {
        $founds = 0;
        foreach ($arrayItems as $item){
            if(in_array($item, $arrayIn)){
                $founds++;
            }
        }
        return $founds > 0 ? true : false;
    }
}

if (!function_exists('compareArrayNum')) {
    function compareArrayNum($array, $index, $num, $anyway = true)
    {
        $string1 = '';
        if(is_array($array) && (is_string($index) || is_numeric($index))){
            if(isset($array[$index])){
                $string1 = $array[$index];
            } else {
                return false;
            }
        } else {
            return false;
        }
        return compareStrNum($string1,$num,$anyway);
    }
}

if (!function_exists('validateVar')) {
    function validateVar($val, $type = 'string', $bEmpty = true)
    {
        switch ($type) {
            case 'string':
                if (is_string($val)) {
                    if($bEmpty){
                        if ($val != '') {
                            return true;
                        }
                    } else {
                        return true;
                    }
                }
                break;
            case 'numeric':
                if (is_numeric($val)) {
                    if($bEmpty){
                        if ($val != 0) {
                            return true;
                        }
                    } else {
                        return true;
                    }
                }
                break;
            case 'bool':
                if (is_bool($val)) {
                    return $val;
                }
                break;
            case 'array':
                if (is_array($val)) {
                    if($bEmpty){
                        if (count($val) > 0 && $val != []) {
                            return true;
                        }
                    } else {
                        return true;
                    }
                }
                break;
            case 'object':
                if (is_object($val)) {
                    return true;
                }
                break;
        }
        return false;
    }
}

if (!function_exists('isArray')) {
    function isArray($array, $bEmpty = true)
    {
        return validateVar($array, 'array', $bEmpty);
    }
}
if (!function_exists('isString')) {
    function isString($str, $bEmpty = true)
    {
        return validateVar($str, 'string', $bEmpty = true);
    }
}
if (!function_exists('isObject')) {
    function isObject($object, $bEmpty = true)
    {
        return validateVar($object, 'object', $bEmpty);
    }
}
if (!function_exists('isNumeric')) {
    function isNumeric($num, $bEmpty = true)
    {
        return validateVar($num, 'numeric', $bEmpty);
    }
}
if (!function_exists('isJson')) {
    function isJson($string) {
        if(isString($string)){
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }
}
if (!function_exists('valNumeric')) {
    function valNumeric($num)
    {
        if(is_float((float)$num)){
            return floatval((float)$num);
        } else if(is_int((int)$num)){
            return intval((int)$num);
        } else {
            return $num;
        }
    }
}
if (!function_exists('isBoolean')) {
    function isBoolean($bool, $bEmpty = true)
    {
        return validateVar($bool, 'bool', $bEmpty);
    }
}
if (!function_exists('isCollection')) {
    function isCollection($data)
    {
        $bToReturn =false;

        $array = $data;

        if (isObject($data)){

            $array = std2array($data);

        }

        if(isArray($array)){

            $dataKeys = array_keys($array);

            foreach ($dataKeys as $key){

                if(!isNumeric($key, false)){

                    $bToReturn = false;

                    break;

                } else {

                    $bToReturn = true;
                }
            }

            return $bToReturn;

        }
    }
}

if (!function_exists('validateArray')) {
    function validateArray($array, $index, $bIsEmpty = true)
    {
        $excepts = ['',[],null];
        if(validateVar($array,'array') && (is_string($index) || is_numeric($index))){
            if($bIsEmpty){
                if(isset($array[$index]) && !in_array($array[$index],$excepts)){
                    return true;
                } else {
                    return false;
                }
            } else {
                if(isset($array[$index])){
                    return true;
                } else {
                    return false;
                }
            }

        } else {
            return false;
        }
    }
}

if (!function_exists('strReplace')) {
    function strReplace($searchs,$replace,$str){
        $strReplaced = $str;
        $aSearchs = array();
        if(!isArray($searchs)){
            $aSearchs[] = $searchs;
        } else {
            $aSearchs = $searchs;
        }
        foreach ($aSearchs as $char){
            $strReplaced = str_replace($char,$replace,$strReplaced);
        }
        return $strReplaced;
    }
}
if (!function_exists('inArray')) {
    function inArray($key,$array,$bEmpty = true)
    {
        return validateArray($array,$key, $bEmpty);
    }
}

if (!function_exists('arrayHas')) {
    function arrayHas($array, $index, $bEmpty = true)
    {
        if(isArray($array,$bEmpty) && (isString($index,$bEmpty) || isNumeric($index,$bEmpty))){
            if(isset($array[$index]) && $array[$index] != "" && $array[$index] != []){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('objectHas')) {
    function objectHas($object, $index, $bEmpty = true, $bSetObject = false, $bUcFirst = false)
    {
        $response = true;
        if(isObject($object,$bEmpty) && (isString($index,$bEmpty) || isNumeric($index,$bEmpty))){
            $isset = false;
            foreach ($object as $key => $val){
                if($bSetObject){
                    if($bUcFirst){
                        if(ucfirst(setObject($key)) == $index){
                            $isset = true;
                            $response = $key;
                            break;
                        } else {
                            $response = false;
                        }
                    } else {
                        if(setObject($key) == $index){
                            $isset = true;
                            $response = $key;
                            break;
                        } else {
                            $response = false;
                        }
                    }
                } else {
                    if($key == $index){
                        $isset = $response = true;
                        break;
                    } else {
                        $response = false;
                    }
                }

            }
            if($isset){
                if($bEmpty){
                    if($object->$index != "" && $object->$index != [] && $object->$index != null){
                        return $response;
                    } else {
                        return false;
                    }
                } else {
                    return $response;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}


if (!function_exists('dump')) {

    function dump ($var, $label = 'Dump', $echo = TRUE)
    {
        // Store dump in variable

        ob_start();

        var_dump($var);

        $output = ob_get_clean();

        // Add formatting

        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);

        $output = '<pre style="background: #FFFEEF; color: #000; border: 1px dotted #000; padding: 10px; margin: 10px 0; text-align: left;">' . $label . ' => ' . $output . '</pre>';

        // Output

        if ($echo == TRUE) {

            echo $output;
        }
        else {

            return $output;
        }
    }
}

if (!function_exists('dump_exit')) {

    function dump_exit($var, $label = 'Dump', $echo = TRUE) {

        dump ($var, $label, $echo);

        exit;
    }
}

if (!function_exists('createFolder')) {

    function createFolder($dir)
    {
        if (!is_dir($dir)) {

            if (!mkdir($dir, 0777, true)) {

                die('Fallo al crear el folder ' . $dir);

                return false;
            }
            chmod($dir, 0777);

            $mensaje = "El directorio " . $dir . " se ha creado exitosamente";

            return true;

        } else {

            if (is_dir($dir)) {

                $mensaje = "El Directorio " . $dir . " ya fue creado";

                return true;

            } else {

                return false;
            }
        }
    }
}

if (!function_exists('probar_sesion')) {

    function probar_sesion()
    {
        session_start();
        if (isset($_SESSION['contador'])){
            $_SESSION['contador'] = $_SESSION['contador']+1;
        }else{
            $_SESSION['contador']=1;
        }
        echo "Prueba de Session: (El contador debe incrementarse en 1) Valor del contador ".$_SESSION['contador'] .'<br>';


        if (!is_writable(session_save_path())) {
            echo '<br><br>La ruta "'.session_save_path().'" no tiene permisos de escritura en PHP!';
        }

        dump_exit($_SESSION);
    }
}

if (!function_exists('setObject')) {

    function setObject($nameWithDashes, $blcFirst = true, $bReturnUcNames = true)
    {
        if(strstr($nameWithDashes,'_')){
            $aNames = explode('_',$nameWithDashes);
        } else if(strstr($nameWithDashes, ' ')){
            $aNames = explode(' ',$nameWithDashes);
        } else if(strstr($nameWithDashes,'-')){
          $aNames = explode('-',$nameWithDashes);
        } else {
            $aNames[] = $nameWithDashes;
        }
        if($bReturnUcNames){
            $callback = function($name){
                return ucfirst($name);
            };
            $aNames = array_map($callback,$aNames);
        }
        $strImploded = implode('',$aNames);
        $strImploded = str_replace(' ', '', $strImploded);
        if($blcFirst){
            return lcfirst($strImploded);
        } else {
            return $strImploded;
        }
    }
}

if (!function_exists('getFilesFrom')) {

    function getFilesFrom($dir, $with_ext = false)
    {
        $files = [];

        if (is_dir($dir)) {

            if ($gestor = opendir($dir)) {

                while (false !== ($entrada = readdir($gestor))) {

                    if ($entrada != '.' && $entrada != '..') {

                        $files[] = $entrada;
                    }
                }
                closedir($gestor);
            }

            if ($with_ext) {

                return $files;

            } else {

                foreach ($files as $i => $name) {

                    $files[$i] = explode('.', $name)[0];
                }
            }
        }
        return $files;
    }
}


if (!function_exists('deleteFile')) {

    function deleteFile($file)
    {

        $fh = fopen($file, 'a');

        fclose($fh);

        unlink($file);
    }
}

if (!function_exists('deleteDir')) {

    function deleteDir($dir)
    {
        if (!is_dir($dir)) {

            mkdir($dir);
        }

        rmdir($dir);
    }
}

if (!function_exists('rrmdir')) {

    function rrmdir($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }
}

if (!function_exists('array2str')) {

    function array2str($array)
    {
        if(is_array($array)){
            $str = '|';
            foreach ($array as $item){
                if(isString($item)){
                    $str .= $item.'|';
                }
            }
            return $str;
        } else {
            return $array;
        }
    }
}
if (!function_exists('array2std')) {

    function array2std($array) {
        if(!is_array($array)) {
            return $array;
        }

        $stdClass = json_decode(json_encode($array), false);

        if(is_object($stdClass)) {
            return $stdClass;
        } else {
            $stdClass = new stdClass();
            if (validateVar($array,'array')){
                foreach ($array as $name=>$value) {
                    $stdClass->$name = array2std($value);
                }
                return $stdClass;
            } else {
                return $array;
            }
        }
    }
}

if (!function_exists('std2array')) {

    function std2array($std)
    {
        if(isArray($std)){
            return $std;
        }
        $array = json_decode(json_encode($std), true);

        if(isArray($array)){
            return $array;
        } else {
            $array = array();
            if (isObject($std)){
                foreach ($std as $name => $value) {
                    $array[$name] = std2array($value);
                }
                return $array;
            } else {
                return $std;
            }
        }
    }
}

if (!function_exists('setMessage')) {

    function setMessage($aData, $added, $aSearched = [])
    {
        $aExcepts = ['password','status','estado'];
        $message = $added.': ';
        if(isArray($aData)){
            foreach ($aData as $key => $data){
                if(isArray($aSearched)){
                    foreach ($aSearched as $search){
                        if($key == $search && validateVar($data) && !strstr($data,'/')){
                            $message .= "$key: $data | ";
                        }
                    }
                } else {
                    if(validateVar($data) && !strstr($data,'/') && !in_array($key,$aExcepts)){
                        $message .= "$key: $data, ";
                    }
                }
            }
        } else if(isString($aData)){
            return $aData;
        }
        if($message == ""){
            return ucfirst("$added.");
        } else {
            return ucfirst("$message.");
        }
    }
}
if (!function_exists('unsetall')) {

    function unsetall($array, $index)
    {
        foreach ($array as $key => $cont){
            if($cont == $index){
                unset($array[$key]);
            }
        }
        return $array;
    }
}

if (!function_exists('hash_sha')) {

    function hash_sha($string)
    {
        return hash('sha512', $string . config_item('encryption_key'));
    }
}

if (!function_exists('setTitleFromObject')) {

    function setTitleFromObject($object,$aValues)
    {
        $title = '';
        if(validateVar($aValues, 'array')){
            foreach ($aValues as $value){
                if(validateVar($object->$value)){
                    $title .= $object->$value . ' ';
                } else if(validateVar($object->$value, 'array') || validateVar($object->$value, 'object')){
                    foreach ($object->$value as $val){
                        $title .= $val . ' ';
                    }
                } else {
                    $title .= $object->$value . ' ';
                }
            }
        } else if(validateVar($aValues)){
            if(validateVar($object->$aValues)){
                $title .= $object->$aValues . ' ';
            } else if(validateVar($object->$aValues, 'array') || validateVar($object->$aValues, 'object')){
                foreach ($object->$aValues as $val){
                    $title .= $val . ' ';
                }
            } else {
                $title .= $object->$aValues . ' ';
            }
        }
        return $title;
    }
}

if (!function_exists('setInputData')) {

    function setInputData($data,$object)
    {
        if(is_object($object)){
            $aValues = std2array($object);
            foreach ($aValues as $name => $value){
                $data["data-$name"] = $value;
            }
        }
        return $data;
    }
}

if (!function_exists('myEach')) {
    function myEach(&$arr) {
        $key = key($arr);
        $result = ($key === null) ? false : [$key, current($arr), 'key' => $key, 'value' => current($arr)];
        next($arr);
        return $result;
    }
}

if (!function_exists('countStd')) {
    function countStd($std) {
        $array = std2array($std);
        return validateVar($array,'array') ? count($array) : false;
    }
}

if (!function_exists('str2array')) {
    function str2array($str){
        if(isJson($str)){
            $str = std2array(json_decode($str));
        } else {
            if(isString($str) && !isNumeric($str)){
                if(strpos($str,'[') > -1 && strpos($str,']') > -1){
                    $str = str_replace('"','',$str);
                    $str = str_replace('[','',$str);
                    $str = str_replace(']','',$str);
                    if (isString($str)){
                        $str = explode(',',$str);
                        $str = array_combine($str,$str);
                    } else {
                        $str = [$str];
                    }
                }
            } else if(isArray($str)){
                foreach ($str as $key => $subValue){
                    if(isArray($subValue)){
                        foreach ($subValue as $key2 => $subValue2){
                            if(isString($subValue2)){
                                if(strpos($subValue2,'[') > -1 && strpos($subValue2,']') > -1){
                                    $subValue2 = str_replace('"','',$subValue2);
                                    $subValue2 = str_replace('[','',$subValue2);
                                    $subValue2 = str_replace(']','',$subValue2);
                                    $subValue2 = explode(',',$subValue2);
                                    $str[$key][$key2] = array_combine($subValue2,$subValue2);
                                }
                            }
                        }
                    } else if(isString($subValue)){
                        if(strpos($subValue,'[') > -1 && strpos($subValue,']') > -1){
                            $subValue = str_replace('"','',$subValue);
                            $subValue = str_replace('[','',$subValue);
                            $subValue = str_replace(']','',$subValue);
                            $subValue = explode(',',$subValue);
                            $str[$key] = array_combine($subValue,$subValue);
                        }
                    }
                }
            }
        }
        if(isArray($str,false)){
            return $str;
        } else if(!isString($str) && isObject($str)){
            return std2array($str);
        } else if(strstr($str,'|') && substr_count($str,'|') > 1){
            $aIds = array();
            $aParts = explode('|',$str);
           foreach ($aParts as $part){
               if($part != ""){
                   $aIds[] = $part;
               }
           }
           return $aIds;
        } else {
            return $str;
        }
    }
}

if (!function_exists('verifyArraysInResult')) {

    function verifyArraysInResult($result){
        $result = std2array($result);
        if(validateVar($result, 'array')){
            if(validateVar($result,'array')){
                $result = array_map('str2array',$result);
            } else {
                $result = array_map('str2array',std2array($result));
            }
            $result = array2std($result);
        }
        return $result;
    }
}

if (!function_exists('suprTagInStr')) {

    function suprTagInStr($tag, $str){
        if(validateVar($str) && validateVar($tag)){
            $aStr = explode($tag,$str);
            $str = implode('',$aStr);
            return $str;
        } else {
            return $str;
        }
    }
}


if(!function_exists('suprstr')){
    function suprstr($str,$needle){
        $letters = explode($needle, $str);
        return implode('',$letters);
    }
}


if (!function_exists('setLabel')) {
    function setLabel($name, $bUcFirst = false, $bDetectId = true)
    {
        $aSigns = ['-','_','*'];
        $nameSign = '';
        foreach ($aSigns as $sign){
            if(strstr($name,$sign)){
                $nameSign = $sign;
                break;
            }
        }
        if($nameSign == ''){
            return ucfirst($name);
        } else {
            $words = explode($nameSign, $name);
            if($bUcFirst){
                $funct = function($key){
                    return ucfirst($key);
                };
                $words = array_map($funct,$words);
            }
            if($bDetectId){
                $funct = function($key){
                    return ucfirst(suprstr($key,'id_'));
                };
                $words = array_map($funct, $words);
            }
            return $title = implode(' ', $words);
        }
    }
}

//    if (!function_exists('setObject')) {
//        function setObject($name)
//        {
//            $sign = strpos($name,'_') > -1 ? '_' : (strpos($name,'-') > -1 ? '-' : '');
//            if($sign == ''){
//                return $name;
//            } else {
//                $words = explode($sign, $name);
//                $title = '';
//                foreach ($words as $word){
//                    $title .= ucfirst($word);
//                }
//                return lcfirst($title);
//            }
//        }
//    }

if (!function_exists('setSingularPlural')) {

    function setSingularPlural($sub_mod)
    {
//        (1 letra) mañanas, detalles, vasos
//        (2 letras) sesiones,
        if(strpos($sub_mod,'_') > -1){
            $names = explode('_', $sub_mod);
        } else {
            $names = [$sub_mod];
        }
        $namesPlural = [];
        $namesSingular = [];
        $aVocales = ['a','o','e','i','u'];
        $aVocalesfuertes = ['a','e','o'];
        $aVocalesdebiles = ['i','u'];
        $aDiptongos = ['ua','au','ai','ia','ei','ie','oi','io','uo','ou'];
        // ********** si la tercera letra es un de estas se resta las dos ultimas letras osea 'es', ejemplo: sesiones  ***************
        $aConsonantes = ['b','c','d','f','g','h','j','k','l','m','n','ñ','p','q','r','s','t','v','w','x','y','z'];
        // ********** si la tercera letra es un de estas se resta la primera letra del final osea la 's', ejemplo: detalles ***************
        $aConsonantesEspeciales = ['l','n'];
        $englishWords = config_item('english_words');
        foreach ($names as $i => $name) {
            $pos1 = strlen($name) - 5;
            $pos2 = strlen($name) - 4;
            $pos3 = strlen($name) - 3;
            $pos4 = strlen($name) - 2;
            $pos5 = strlen($name) - 1;
            $fifthLetter = substr($name, $pos1, 1);
            $fourthLetter = substr($name, $pos2, 1);
            $thirdLetter = substr($name, $pos3, 1);
            $secondLetter = substr($name, $pos4, 1);
            $firstLetter = substr($name, $pos5, 1);
//            $thirdIsConsonant = in_array($thirdLetter,$aVocales) ? false : true;
//            $bIsEspecial = in_array($secondLetter,$aVocalesEspeciales) ? true : false;

            // ******************************* words excepted in english ******************************
            foreach ($englishWords as $word){
                if(strpos($name,$word) > -1){
                    $namesPlural[$i] = $name;
                    $namesSingular[$i] = substr($name, 0, strlen($name) - 1);
                    $_sub_mod_s = count($namesSingular) > 1 ? implode('_', $namesSingular) : $namesSingular[0];
                    $_sub_mod_p = count($namesPlural) > 1 ? implode('_', $namesPlural) : $namesPlural[0];
                    if(count($names) == 1){
                        return [$_sub_mod_s, $_sub_mod_p];
                    }
                }
            }
            // ******************************************************************************************

            if ($firstLetter == 's' ) {
                if (in_array($secondLetter,$aVocales)) {
                    if(in_array($thirdLetter,$aConsonantes)){
                        if(in_array($fifthLetter.$fourthLetter,$aDiptongos)){
                            $namesSingular[$i] = substr($name, 0, strlen($name) - 2);
                        } else {
                            if (in_array($secondLetter, $aVocalesdebiles)){
                                $namesSingular[$i] = substr($name, 0, strlen($name) - 2);
                            } else if(in_array($secondLetter, $aVocalesfuertes)){
                                $namesSingular[$i] = substr($name, 0, strlen($name) - 1);
                            }
                        }
                    } else if(in_array($thirdLetter,$aVocales)){
                        if(in_array($secondLetter,$aVocalesfuertes)){
                            $namesSingular[$i] = substr($name, 0, strlen($name) - 1);
                        } elseif (in_array($secondLetter,$aVocalesdebiles)){
                            $namesSingular[$i] = substr($name, 0, strlen($name) - 2);
                        }
                    }
                } else if(in_array($secondLetter,$aConsonantes)){
                    $namesSingular[$i] = substr($name, 0, strlen($name) - 1);
                } else {
                    $namesSingular[$i] = substr($name, 0, strlen($name) - 1);
                }
                $namesPlural[$i] = $name;
            } else {
                $namesSingular[$i] = $name;
                $namesPlural[$i] = $name . 's';
            }
        }
        $_sub_mod_s = count($namesSingular) > 1 ? implode('_', $namesSingular) : $namesSingular[0];
        $_sub_mod_p = count($namesPlural) > 1 ? implode('_', $namesPlural) : $namesPlural[0];

        return [$_sub_mod_s, $_sub_mod_p];
    }
}

if (!function_exists('getModSubMod')) {
    function getModSubMod($table)
    {
        if (substr_count($table, '_') == 1) {
            return explode('_', $table);
        } else {
            $parts = explode('_', $table);
            $mod = $parts[0];
            unset($parts[0]);
            return [$mod, implode('_', $parts)];
        }
    }
}

if (!function_exists('cleanWhiteSpaces')) {
    function cleanWhiteSpaces($str)
    {
        return str_replace(' ', '', strval(trim($str," \t\n\r")));
    }
}

if (!function_exists('safe_json_encode')) {
    function safe_json_encode($value, $options = 0, $depth = 512){
        $encoded = json_encode($value, $options, $depth);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $encoded;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
            case JSON_ERROR_UTF8:
                $clean = utf8ize($value);
                return safe_json_encode($clean, $options, $depth);
            default:
                return 'Unknown error'; // or trigger_error() or throw new Exception()

        }
    }
}

if (!function_exists('utf8ize')) {
    function utf8ize($mixed) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = utf8ize($value);
            }
        } else if (is_string ($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }
}

if (!function_exists('clean')) {
    function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = strtolower($string);
        $string = cleanString($string);

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
}

if (!function_exists('hyphenize')) {

    function hyphenize($string) {
        $dict = array(
            "I'm"      => "I am",
            "thier"    => "their",
            // Add your own replacements here
        );
        return strtolower(
            preg_replace(
                array( '#[\\s-]+#', '#[^A-Za-z0-9\. -]+#' ),
                array( '-', '' ),
                // the full cleanString() can be downloaded from http://www.unexpectedit.com/php/php-clean-string-of-utf8-chars-convert-to-similar-ascii-char
                cleanString(
                    str_replace( // preg_replace can be used to support more complicated replacements
                        array_keys($dict),
                        array_values($dict),
                        urldecode($string)
                    )
                )
            )
        );
    }

}

if (!function_exists('cleanString')) {

    function cleanString($text) {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }
}



if (!function_exists('validate_modulo')) {

    function validate_modulo($mod, $subMod, $status = 500, $heading = ''){

        $CI = CI_Controller::get_instance();
        $sys = config_item('sys');
        if(validateVar($mod) && validateVar($subMod)){

          $modSign = isset($sys[$mod]['sign']) ? $sys[$mod]['sign'] : $mod;

          if($CI->db->table_exists("$modSign"."_$subMod")){
              $framePath = getframePath($modSign,$subMod);
              if(is_dir($framePath)){
                  if(file_exists($framePath.'Ctrl_'.ucfirst($subMod).'.php') &&
                      file_exists($framePath.'Model_'.ucfirst($subMod).'.php') &&
                      is_dir($framePath.'views/') &&
                      class_exists('Ctrl_'.ucfirst($subMod))
                  ){

                      return true;
                  }
              }
              show_error("El modulo $mod/$subMod no pudo ser encontrado, revisa que la direccion este bien establecida");
          } else {
              show_error("La tabla ".$modSign.'_'.$subMod." no se encuentra en la base de datos");
          }
          return false;
        }
    }


}



if (!function_exists('arrayKey')) {

    function arrayKey($ind,$array){

        $keyNum = -1;

        foreach ($array as $key => $val){

            if($ind == $val || strhas($val,$ind)){

                $keyNum = $key;

                break;
            }
        }

        return $keyNum;

    }


}


if (!function_exists('strGet')) {

    function strGet($str,$key){

        $strToReturn = '';

        if(strstr($str,$key)){

            $aExploded = explode($key,$str);

            return $aExploded[1];

        } else {

            return '';

        }
    }


}
