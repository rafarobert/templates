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
 * @since	Version 2.0.0
 * @filesource
 * @var CI_DB_query_builder $db              This is the platform-independent base Active Record implementation class.
 * @property CI_DB_forge $dbforge                 Database Utility Class
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Session Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author		Andrey Andreev
 * @link		https://codeigniter.com/user_guide/libraries/sessions.html
 */

use \Propel\Runtime\ActiveQuery\Criteria as Criteria;

class CI_Session {

	/**
	 * Userdata array
	 *
	 * Just a reference to $_SESSION, for BC purposes.
     * @property ES_Model $MI
	 */
	public $userdata;
	public $userTable;
    public $userIdTable;
    public $es_sessions;
    public $sessKey;


	protected $_driver = 'files';
	protected $_config;

    /**
     * @var ES_Controller $CI
     */
    protected $CI;

    /**
     * @var ES_Model $MI
     */
    protected $MI;

    /**
     * @var CI_DB_driver $db
     */
    protected $db;
	// ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param	array	$params	Configuration parameters
     * @return	void
     */
    public function __construct(array $params = array())
    {
        // Load migration language
        $this->CI = class_exists('CI_Controller') ? CI_Controller::get_instance() : null;
        $this->MI = class_exists('CI_Model') ? CI_Model::get_instance() : null;
        // They'll probably be using dbforge
        // No sessions under CLI
        if (is_cli())
        {
            log_message('debug', 'Session: Initialization under CLI aborted.');
            return;
        }
        elseif ((bool) ini_get('session.auto_start'))
        {
            log_message('error', 'Session: session.auto_start is enabled in php.ini. Aborting.');
            return;
        }
        elseif ( ! empty($params['driver']))
        {
            $this->_driver = $params['driver'];
            unset($params['driver']);
        }
        elseif ($driver = config_item('sess_driver'))
        {
            $this->_driver = $driver;
        }
        // Note: BC workaround
        elseif (config_item('sess_use_database'))
        {
            $this->_driver = 'database';
        }
        $class = $this->_ci_load_classes($this->_driver);

        // Configuration ...
        $this->_configure($params);
        $class = new $class($this->_config);

        if ($class instanceof SessionHandlerInterface)
        {
            if (is_php('5.4'))
            {
                session_set_save_handler($class, TRUE);
            }
            else
            {
                session_set_save_handler(
                    array($class, 'open'),
                    array($class, 'close'),
                    array($class, 'read'),
                    array($class, 'write'),
                    array($class, 'destroy'),
                    array($class, 'gc')
                );

                register_shutdown_function('session_write_close');
            }
        }
        else
        {
            log_message('error', "Session: Driver '".$this->_driver."' doesn't implement SessionHandlerInterface. Aborting.");
            return;
        }

        // Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
        if (isset($_COOKIE[$this->_config['cookie_name']])
            && (
                ! is_string($_COOKIE[$this->_config['cookie_name']])
                OR ! preg_match('/^[0-9a-f]{40}$/', $_COOKIE[$this->_config['cookie_name']])
            )
        )
        {
            //unset($_COOKIE[$this->_config['cookie_name']]);
        }
//        probar_sesion();

        session_start();

        // Is session ID auto-regeneration configured? (ignoring ajax requests)
        if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
            && ($regenerate_time = config_item('sess_time_to_update')) > 0
        )
        {
            if ( ! isset($_SESSION['__ci_last_regenerate']))
            {
                $_SESSION['__ci_last_regenerate'] = time();
            }
            elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
            {
                $this->sess_regenerate((bool) config_item('sess_regenerate_destroy'));
            }
        }
        // Another work-around ... PHP doesn't seem to send the session cookie
        // unless it is being currently created or regenerated
        elseif (isset($_COOKIE[$this->_config['cookie_name']]) && $_COOKIE[$this->_config['cookie_name']] === session_id())
        {
            setcookie(
                $this->_config['cookie_name'],
                session_id(),
                (empty($this->_config['cookie_lifetime']) ? 0 : time() + $this->_config['cookie_lifetime']),
                $this->_config['cookie_path'],
                $this->_config['cookie_domain'],
                $this->_config['cookie_secure'],
                TRUE
            );
        }

        $this->_ci_init_vars();
        log_message('info', "Session: Class initialized using '".$this->_driver."' driver.");
    }

    // ------------------------------------------------------------------------

    /**
	 * CI Load Classes
	 *
	 * An internal method to load all possible dependency and extension
	 * classes. It kind of emulates the CI_Driver library, but is
	 * self-sufficient.
	 *
	 * @param	string	$driver	Driver name
	 * @return	string	Driver class name
	 */
	protected function _ci_load_classes($driver)
	{
		// PHP 5.4 compatibility
		interface_exists('SessionHandlerInterface', FALSE) OR require_once(BASEPATH.'libraries/Session/SessionHandlerInterface.php');

		$prefix = config_item('subclass_prefix');

		if ( ! class_exists('CI_Session_driver', FALSE))
		{
			require_once(
				file_exists(APPPATH.'libraries/Session/Session_driver.php')
					? APPPATH.'libraries/Session/Session_driver.php'
					: BASEPATH.'libraries/Session/Session_driver.php'
			);

			if (file_exists($file_path = APPPATH.'libraries/Session/'.$prefix.'Session_driver.php'))
			{
				require_once($file_path);
			}
		}

		$class = 'Session_'.$driver.'_driver';

		// Allow custom drivers without the CI_ or MY_ prefix
		if ( ! class_exists($class, FALSE) && file_exists($file_path = APPPATH.'libraries/Session/drivers/'.$class.'.php'))
		{
			require_once($file_path);
			if (class_exists($class, FALSE))
			{
				return $class;
			}
		}

		if ( ! class_exists('CI_'.$class, FALSE))
		{
			if (file_exists($file_path = APPPATH.'libraries/Session/drivers/'.$class.'.php') OR file_exists($file_path = BASEPATH.'libraries/Session/drivers/'.$class.'.php'))
			{
				require_once($file_path);
			}

			if ( ! class_exists('CI_'.$class, FALSE) && ! class_exists($class, FALSE))
			{
				throw new UnexpectedValueException("Session: Configured driver '".$driver."' was not found. Aborting.");
			}
		}

		if ( ! class_exists($prefix.$class, FALSE) && file_exists($file_path = APPPATH.'libraries/Session/drivers/'.$prefix.$class.'.php'))
		{
			require_once($file_path);
			if (class_exists($prefix.$class, FALSE))
			{
				return $prefix.$class;
			}
			else
			{
				log_message('debug', 'Session: '.$prefix.$class.".php found but it doesn't declare class ".$prefix.$class.'.');
			}
		}

		return 'CI_'.$class;
	}

	// ------------------------------------------------------------------------

	/**
	 * Configuration
	 *
	 * Handle input parameters and configuration defaults
	 *
	 * @param	array	&$params	Input parameters
	 * @return	void
	 */
	protected function _configure(&$params)
	{
		$expiration = config_item('sess_expiration');

		if (isset($params['cookie_lifetime']))
		{
			$params['cookie_lifetime'] = (int) $params['cookie_lifetime'];
		}
		else
		{
			$params['cookie_lifetime'] = ( ! isset($expiration) && config_item('sess_expire_on_close'))
				? 0 : (int) $expiration;
		}

		isset($params['cookie_name']) OR $params['cookie_name'] = config_item('sess_cookie_name');
		if (empty($params['cookie_name']))
		{
			$params['cookie_name'] = ini_get('session.name');
		}
		else
		{
			ini_set('session.name', $params['cookie_name']);
		}

		isset($params['cookie_path']) OR $params['cookie_path'] = config_item('cookie_path');
		isset($params['cookie_domain']) OR $params['cookie_domain'] = config_item('cookie_domain');
		isset($params['cookie_secure']) OR $params['cookie_secure'] = (bool) config_item('cookie_secure');

		session_set_cookie_params(
			$params['cookie_lifetime'],
			$params['cookie_path'],
			$params['cookie_domain'],
			$params['cookie_secure'],
			TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
		);

		if (empty($expiration))
		{
			$params['expiration'] = (int) ini_get('session.gc_maxlifetime');
		}
		else
		{
			$params['expiration'] = (int) $expiration;
			ini_set('session.gc_maxlifetime', $expiration);
		}

		$params['match_ip'] = (bool) (isset($params['match_ip']) ? $params['match_ip'] : config_item('sess_match_ip'));

		isset($params['save_path']) OR $params['save_path'] = config_item('sess_save_path');

		$this->_config = $params;

		// Security is king
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.hash_function', 1);
		ini_set('session.hash_bits_per_character', 4);

    }

	// ------------------------------------------------------------------------

	/**
	 * Handle temporary variables
	 *
	 * Clears old "flash" data, marks the new one for deletion and handles
	 * "temp" data deletion.
	 *
	 * @return	void
	 */
	protected function _ci_init_vars()
	{
		if ( ! empty($_SESSION['__ci_vars']))
		{
			$current_time = time();

			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				if ($value === 'new')
				{
					$_SESSION['__ci_vars'][$key] = 'old';
				}
				// Hacky, but 'old' will (implicitly) always be less than time() ;)
				// DO NOT move this above the 'new' check!
				elseif ($value < $current_time)
				{
					unset($_SESSION[$key], $_SESSION['__ci_vars'][$key]);
				}
			}

			if (empty($_SESSION['__ci_vars']))
			{
				unset($_SESSION['__ci_vars']);
			}
		}

		$this->userdata =& $_SESSION;
	}

	// ------------------------------------------------------------------------

	/**
	 * Mark as flash
	 *
	 * @param	mixed	$key	Session data key(s)
	 * @return	bool
	 */
	public function mark_as_flash($key)
	{
		if (is_array($key))
		{
			for ($i = 0, $c = count($key); $i < $c; $i++)
			{
				if ( ! isset($_SESSION[$key[$i]]))
				{
					return FALSE;
				}
			}

			$new = array_fill_keys($key, 'new');

			$_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars'])
				? array_merge($_SESSION['__ci_vars'], $new)
				: $new;

			return TRUE;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return FALSE;
		}

		$_SESSION['__ci_vars'][$key] = 'new';
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get flash keys
	 *
	 * @return	array
	 */
	public function get_flash_keys()
	{
		if ( ! isset($_SESSION['__ci_vars']))
		{
			return array();
		}

		$keys = array();
		foreach (array_keys($_SESSION['__ci_vars']) as $key)
		{
			is_int($_SESSION['__ci_vars'][$key]) OR $keys[] = $key;
		}

		return $keys;
	}

	// ------------------------------------------------------------------------

	/**
	 * Unmark flash
	 *
	 * @param	mixed	$key	Session data key(s)
	 * @return	void
	 */
	public function unmark_flash($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) OR $key = array($key);

		foreach ($key as $k)
		{
			if (isset($_SESSION['__ci_vars'][$k]) && ! is_int($_SESSION['__ci_vars'][$k]))
			{
				unset($_SESSION['__ci_vars'][$k]);
			}
		}

		if (empty($_SESSION['__ci_vars']))
		{
			unset($_SESSION['__ci_vars']);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Mark as temp
	 *
	 * @param	mixed	$key	Session data key(s)
	 * @param	int	$ttl	Time-to-live in seconds
	 * @return	bool
	 */
	public function mark_as_temp($key, $ttl = 300)
	{
		$ttl += time();

		if (is_array($key))
		{
			$temp = array();

			foreach ($key as $k => $v)
			{
				// Do we have a key => ttl pair, or just a key?
				if (is_int($k))
				{
					$k = $v;
					$v = $ttl;
				}
				else
				{
					$v += time();
				}

				if ( ! isset($_SESSION[$k]))
				{
					return FALSE;
				}

				$temp[$k] = $v;
			}

			$_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars'])
				? array_merge($_SESSION['__ci_vars'], $temp)
				: $temp;

			return TRUE;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return FALSE;
		}

		$_SESSION['__ci_vars'][$key] = $ttl;
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get temp keys
	 *
	 * @return	array
	 */
	public function get_temp_keys()
	{
		if ( ! isset($_SESSION['__ci_vars']))
		{
			return array();
		}

		$keys = array();
		foreach (array_keys($_SESSION['__ci_vars']) as $key)
		{
			is_int($_SESSION['__ci_vars'][$key]) && $keys[] = $key;
		}

		return $keys;
	}

	// ------------------------------------------------------------------------

	/**
	 * Unmark flash
	 *
	 * @param	mixed	$key	Session data key(s)
	 * @return	void
	 */
	public function unmark_temp($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) OR $key = array($key);

		foreach ($key as $k)
		{
			if (isset($_SESSION['__ci_vars'][$k]) && is_int($_SESSION['__ci_vars'][$k]))
			{
				unset($_SESSION['__ci_vars'][$k]);
			}
		}

		if (empty($_SESSION['__ci_vars']))
		{
			unset($_SESSION['__ci_vars']);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * __get()
	 *
	 * @param	string	$key	'session_id' or a session data key
	 * @return	mixed
	 */
	public function __get($key)
	{
		// Note: Keep this order the same, just in case somebody wants to
		//       use 'session_id' as a session data key, for whatever reason
		if (isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}
		elseif ($key === 'session_id')
		{
			return session_id();
		}

		return NULL;
	}

	// ------------------------------------------------------------------------

	/**
	 * __isset()
	 *
	 * @param	string	$key	'session_id' or a session data key
	 * @return	bool
	 */
	public function __isset($key)
	{
		if ($key === 'session_id')
		{
			return (session_status() === PHP_SESSION_ACTIVE);
		}

		return isset($_SESSION[$key]);
	}

	// ------------------------------------------------------------------------

	/**
	 * __set()
	 *
	 * @param	string	$key	Session data key
	 * @param	mixed	$value	Session data value
	 * @return	void
	 */
	public function __set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	// ------------------------------------------------------------------------

	/**
	 * Session destroy
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @return	void
	 */
	public function sess_destroy()
	{
	  $sess = session_status();
    if($sess == 2){
      session_destroy();
    } else {
	    unset($_SESSION);
    }
	}

	// ------------------------------------------------------------------------

	/**
	 * Session regenerate
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	bool	$destroy	Destroy old session data flag
	 * @return	void
	 */
	public function sess_regenerate($destroy = FALSE)
	{
		$_SESSION['__ci_last_regenerate'] = time();
		session_regenerate_id($destroy);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get userdata reference
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @returns	array
	 */
	public function &get_userdata()
	{
		return $_SESSION;
	}

	// ------------------------------------------------------------------------

	/**
	 * Userdata (fetch)
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	string	$key	Session data key
	 * @return	mixed	Session data value or NULL if not found
	 */
	public function userdata($key = NULL)
	{
		if (isset($key))
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
		}
		elseif (empty($_SESSION))
		{
			return array();
		}

		$userdata = array();
		$_exclude = array_merge(
			array('__ci_vars'),
			$this->get_flash_keys(),
			$this->get_temp_keys()
		);

		foreach (array_keys($_SESSION) as $key)
		{
			if ( ! in_array($key, $_exclude, TRUE))
			{
				$userdata[$key] = $_SESSION[$key];
			}
		}

		return $userdata;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set userdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$data	Session data key or an associative array
	 * @param	mixed	$value	Value to store
	 * @return	void
	 */
	public function set_userdata($data, $value = NULL)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$_SESSION[$key] = $value;
			}

			return;
		}

		$_SESSION[$data] = $value;
	}

	// ------------------------------------------------------------------------

	/**
	 * Unset userdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$data	Session data key(s)
	 * @return	void
	 */
	public function unset_userdata($key)
	{
		if (is_array($key))
		{
			foreach ($key as $k)
			{
				unset($_SESSION[$k]);
			}

			return;
		}

		unset($_SESSION[$key]);
	}

	// ------------------------------------------------------------------------

	/**
	 * All userdata (fetch)
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @return	array	$_SESSION, excluding flash data items
	 */
	public function all_userdata()
	{
		return $this->userdata();
	}

	// ------------------------------------------------------------------------

	/**
	 * Has userdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	string	$key	Session data key
	 * @return	bool
	 */
	public function has_userdata($key)
	{
		return isset($_SESSION[$key]);
	}

	// ------------------------------------------------------------------------

	/**
	 * Flashdata (fetch)
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	string	$key	Session data key
	 * @return	mixed	Session data value or NULL if not found
	 */
	public function flashdata($key = NULL)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) && ! is_int($_SESSION['__ci_vars'][$key]))
				? $_SESSION[$key]
				: NULL;
		}

		$flashdata = array();

		if ( ! empty($_SESSION['__ci_vars']))
		{
			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				is_int($value) OR $flashdata[$key] = $_SESSION[$key];
			}
		}

		return $flashdata;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set flashdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$data	Session data key or an associative array
	 * @param	mixed	$value	Value to store
	 * @return	void
	 */
	public function set_flashdata($data, $value = NULL)
	{
		$this->set_userdata($data, $value);
		$this->mark_as_flash(is_array($data) ? array_keys($data) : $data);
	}

	// ------------------------------------------------------------------------

	/**
	 * Keep flashdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$key	Session data key(s)
	 * @return	void
	 */
	public function keep_flashdata($key)
	{
		$this->mark_as_flash($key);
	}

	// ------------------------------------------------------------------------

	/**
	 * Temp data (fetch)
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	string	$key	Session data key
	 * @return	mixed	Session data value or NULL if not found
	 */
	public function tempdata($key = NULL)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) && is_int($_SESSION['__ci_vars'][$key]))
				? $_SESSION[$key]
				: NULL;
		}

		$tempdata = array();

		if ( ! empty($_SESSION['__ci_vars']))
		{
			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				is_int($value) && $tempdata[$key] = $_SESSION[$key];
			}
		}

		return $tempdata;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set tempdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$data	Session data key or an associative array of items
	 * @param	mixed	$value	Value to store
	 * @param	int	$ttl	Time-to-live in seconds
	 * @return	void
	 */
	public function set_tempdata($data, $value = NULL, $ttl = 300)
	{
		$this->set_userdata($data, $value);
		$this->mark_as_temp(is_array($data) ? array_keys($data) : $data, $ttl);
	}

	// ------------------------------------------------------------------------

	/**
	 * Unset tempdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$data	Session data key(s)
	 * @return	void
	 */
	public function unset_tempdata($key)
	{
		$this->unmark_temp($key);
	}

	public function create_table_es_sessions(){
        $this->es_sessions = $this->MI->create_es_sessions();
    }

    public function getIdUserLoggued(){

      $CI = CI_Controller::get_instance();
      $sys = config_item('sys');

      if($CI->db->table_exists('es_users')){
        $framePath = getframePath('estic','users');
        if(is_dir($framePath)){
          if(file_exists($framePath.'Ctrl_Users.php') &&
            file_exists($framePath.'Model_Users.php') &&
            is_dir($framePath.'views/') &&
            class_exists('Ctrl_Users')
          ){
            $this->CI->initUsers(true);
            $this->sessKey = config_item('sess_key_admin');
            if($this->has_userdata($this->sessKey)) {
              $aDataSession = $this->userdata($this->sessKey);
              return validateArray($aDataSession, 'IdUser') ? $aDataSession['IdUser'] : (validateArray($aDataSession, 'id_user') ? $aDataSession['id_user'] : '');
            } else if(inArray($this->sessKey, $this->userdata)){
              return $this->userdata[$this->sessKey]['id_user'];
            } else {
              return null;
            }
          }
        }
        echo "El modulo estic/users no pudo ser encontrado, revisa que la direccion este bien establecida";
      } else {
        echo "La tabla es_users no se encuentra en la base de datos";
      }
	}

    public function getDataUserLoggued(){
        $this->sessKey = config_item('sess_key_admin');
        if($this->CI->initUsers(true)){
            if($this->has_userdata($this->sessKey)) {
                $aDataSession = $this->userdata($this->sessKey);
                return $this->CI->model_users->setFromData($aDataSession);
            } else {
                return null;
            }
        } else if(inArray($this->sessKey, $this->userdata)){
            return array2std($this->userdata[$this->sessKey]);
        } else {
            return '';
        }
    }

    public function getObjectUserLoggued(){
	    if(validate_modulo('estic','users')){
            $this->CI->load->model('estic/model_users');
            $data = $this->getDataUserLoggued();
            if($data){
                return $this->CI->model_users->setFromData($data);
            } else {
                return null;
            }
        } else {
	        return null;
        }

    }

    public function isLoguedin(){

        if($this->has_userdata(config_item('sess_key_estic'))) {
            return true;
        } else if($this->has_userdata(config_item('sess_key_admin'))) {
            return true;
        } else if($this->has_userdata(config_item('sess_key_sys'))) {
            return true;
        }
        return false;
    }

  public function isLoguedinBase(){
    $this->sessKey = config_item('base_loggedin');
    if($this->has_userdata($this->sessKey)) {
      return true;
    }
    return false;
  }

  public function isLoguedinAdmin(){
    $this->sessKey = config_item('admin_loggedin');
    if($this->has_userdata($this->sessKey)) {
      return true;
    }
    return false;
  }

  public function isLoguedinSys(){
    $this->sessKey = config_item('sys_loggedin');
    if($this->has_userdata($this->sessKey)) {
      return true;
    }
    return false;
  }

    public function _unique_email($id = ''){
        // Do NOT validate if email already exists
        // Unless it's the email for the current user

        if($id == '' && $this->CI->router->class != 'ajax'){
            $id = $this->CI->uri->segment(4);
        }
        $this->CI->db->where('email', $this->CI->input->post('email'));

        if($this->CI->input->post('signinMethod')){
            $this->CI->db->where('signin_method', $this->CI->input->post('signinMethod'));
        } else if($this->CI->input->post('signin_method')){
            $this->CI->db->where('signin_method', $this->CI->input->post('signin_method'));
        }

        !$id || $this->CI->db->where("$this->userIdTable !=", $id);

        $user = $this->CI->model_users->get();
        if(count($user)){
            $this->CI->form_validation->set_message('_unique_email', 'Ya existe ese %s registrado');
            return false;
        }
        return true;
    }

    public function signUp($mod = 'users'){
        if (is_object($oUser = $this->getDataUserLoggued())){
            $uri = $this->CI->input->post('uri_string') ? $this->CI->input->post('uri_string') : ($this->CI->uri->uri_string() ? $this->CI->uri->uri_string() :
                ($oUser->id_role == 1 ? 'estic/dashboard' : 'admin/dashboard'));
            if($uri == 'estic/sessions/signup'){
                $uri = WEBSERVER.($oUser->id_role == 1 ? 'estic/dashboard' : 'admin/dashboard');
            } else {
                $uri = WEBSERVER.$uri;
            }
            redirect($uri);
        } else {
            // Redirect a user if he's already logged in
            $this->CI->load->model('estic/model_roles');
            $this->CI->load->model('estic/model_users_roles');
            $dashboard = "admin/dashboard";
            $this->isLoguedin() == FALSE || redirect($dashboard);
            $roles = $this->CI->model_roles->find();
            // Set form
            $rules = $this->CI->model_users->rules_register;
            $this->CI->form_validation->set_rules($rules);

            if(validateVar($roles,'array')){
                // Process form
                if($this->CI->form_validation->run() == true){
                    // We can login and redirect
                    if($this->_unique_email()){
                        $data = $this->CI->input->post();
                        /**
                         * @var Model_Roles $role
                         */
                        $data['id_role'] = 9;

//                    foreach ($roles as $role) {
//                        if ($data['id_role'] == $role->getIdRole()){
//                            $data["id_role"] = $role->getIdRole();
//                        }
//                    }
                        $data = $this->CI->model_users->save($data);
                        $data['id_unidad'] = 100;
                        $data['from_session'] = true;
                        $this->CI->model_users_roles->save($data);

                        $this->login();
                        redirect($dashboard);
                    } else {
                        $this->set_flashdata('error', 'El email introducido ya existe');
//                        echo '<script>estic.warning("El email introducido ya existe")</script>';
                    }
                }
            }
            // Load view
            $oUser = new stdClass();
            $oUser->name = $this->CI->input->post('name');
            $oUser->email = $this->CI->input->post('email');
            $oUser->password = $this->CI->input->post('password');
            $this->CI->data['subLayout'] = "register";
            $this->CI->data['oUser'] = $oUser;
        }
    }

    public function login($mod = null){

        if (is_object($oUser = $this->getDataUserLoggued())){
            $uri = $this->CI->input->post('uri_string') ? $this->CI->input->post('uri_string') : ($this->CI->uri->uri_string() ? $this->CI->uri->uri_string() :
                ($oUser->id_role == 1 ? 'estic/dashboard' : 'admin/dashboard'));
            if($uri == 'estic/sessions/login'){
                $uri = WEBSERVER.($oUser->id_role == 1 ? 'estic/dashboard' : 'admin/dashboard');
            } else {
                $uri = WEBSERVER.$uri;
            }
            redirect($uri);
        } else {
            $this->CI->load->model('estic/model_users_roles');

            $emailPost = $this->CI->input->get_post_request('email');
            $passwordPost = $this->hash($this->CI->input->get_post_request('password'));

            $ngEmailPost = $this->CI->input->post('ngemail');
            $ngPasswordPost = $this->hash($this->CI->input->post('ngpassword'));

            $oUser = $this->CI->model_users->get_by(array(
                'email' => $emailPost ? $emailPost : ($ngEmailPost ? $ngEmailPost : ''),
                'password' => $passwordPost ? $passwordPost : ($ngPasswordPost ? $ngPasswordPost : ''),
                0 => 'id_role',
                1 => 'name',
                2 => 'lastname',
                3 => 'id_user',
                4 => 'email'
            ), true, true);

            if(is_object($oUser)){
                // log in user
                $data = std2array($oUser);
                $oUser = $this->CI->model_users->setFromData($data);
                $oUsersRoles = $this->CI->model_users_roles->filterByIdUser($oUser->getIdUser());
                /**
                 * @var Model_Users_roles $oUserRole
                 */
                $data['ids_roles'] = array();
                foreach ($oUsersRoles as $oUserRole){
                    $data['ids_roles'][] = $oUserRole->getIdRole();
                }
                if(!in_array($oUser->getIdRole(), $data['ids_roles'])){
                    $data['ids_roles'][] = $oUser->getIdRole();
                }
                $data['loggedin'] = TRUE;
                $this->set_userdata($this->sessKey,$data);
                $uri = $this->CI->input->post('uri_string') ? WEBSERVER.$this->CI->input->post('uri_string') : ($this->CI->uri->uri_string() ? WEBSERVER.$this->CI->uri->uri_string() :
                    ($oUser->id_role == 1 ? WEBSERVER.'estic/dashboard' : WEBSERVER.'admin/dashboard'));

                if(isset($_SERVER['SHELL'])){
                  return $oUser;
                } else {
                  if($this->CI->input->post('login') == 'Desbloquear'){
                    redirect('admin/dashboard');
                  } else {
                    redirect($uri);
                  }
                }
            } else {
                $this->CI->data['errors']['login'] = 'El usuario no ';
                $this->CI->data['subLayout'] = 'login';
                return false;
            }
        }
    }

    public function forgotPassword(){
        $this->CI->data['subLayout'] = "forgot-password";
    }

    public function logout(){
        $this->sess_destroy();
        $this->CI->data['subLayout'] = 'pages/login';
        redirect(WEBSERVER.'admin');
    }

    public function locked(){
        $this->sess_destroy();
        $this->CI->data['subLayout'] = 'pages/lockscreen';
    }

    public function hash($string){
      if($string != null){
          return hash('sha512', $string . config_item('encryption_key'));
      } else {
        return null;
      }
    }
}

