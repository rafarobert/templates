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
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link    https://codeigniter.com
 * @since    Version 3.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Class
 *
 * All migrations should implement this, forces up() and down() and gives
 * access to the CI super-global.
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Libraries
 * @author        Reactor Engineers
 * @link
 *
 * @property CI_DB_query_builder $db              This is the platform-independent base Active Record implementation class.
 * @property CI_DB_forge $dbforge                 Database Utility Class
 * @property CI_Benchmark $benchmark              This class enables you to mark points and calculate the time difference between them.<br />  Memory consumption can also be displayed.
 * @property CI_Calendar $calendar                This class enables the creation of calendars
 * @property CI_Cart $cart                        Shopping Cart Class
 * @property CI_Config $config                    This class contains functions that enable config files to be managed
 * @property CI_Controller $controller            This class object is the super class that every library in.<br />CodeIgniter will be assigned to.
 * @property CI_Email $email                      Permits email to be sent using Mail, Sendmail, or SMTP.
 * @property CI_Encrypt $encrypt                  Provides two-way keyed encoding using XOR Hashing and Mcrypt
 * @property CI_Exceptions $exceptions            Exceptions Class
 * @property CI_Form_validation $form_validation  Form Validation Class
 * @property CI_Ftp $ftp                          FTP Class
 * @property CI_Hooks $hooks                      Provides a mechanism to extend the base system without hacking.
 * @property CI_Image_lib $image_lib              Image Manipulation class
 * @property CI_Input $input                      Pre-processes global input data for security
 * @property CI_Lang $lang                        Language Class
 * @property CI_Loader $load                      Loads views and files
 * @property CI_Log $log                          Logging Class
 * @property CI_Model $model                      CodeIgniter Model Class
 * @property CI_Output $output                    Responsible for sending final output to browser
 * @property CI_Pagination $pagination            Pagination Class
 * @property CI_Parser $parser                    Parses pseudo-variables contained in the specified template view,<br />replacing them with the data in the second param
 * @property CI_Profiler $profiler                This class enables you to display benchmark, query, and other data<br />in order to help with debugging and optimization.
 * @property CI_Router $router                    Parses URIs and determines routing
 * @property CI_Session $session                  Session Class
 * @property CI_Sha1 $sha1                        Provides 160 bit hashing using The Secure Hash Algorithm
 * @property CI_Table $table                      HTML table generation<br />Lets you create tables manually or from database result objects, or arrays.
 * @property CI_Trackback $trackback              Trackback Sending/Receiving Class
 * @property CI_Typography $typography            Typography Class
 * @property CI_Unit_test $unit_test              Simple testing class
 * @property CI_Upload $upload                    File Uploading Class
 * @property CI_URI $uri                          Parses URIs and determines routing
 * @property CI_User_agent $user_agent            Identifies the platform, browser, robot, or mobile devise of the browsing agent
 * @property CI_Validation $validation            //dead
 * @property CI_Xmlrpc $xmlrpc                    XML-RPC request handler class
 * @property CI_Xmlrpcs $xmlrpcs                  XML-RPC server class
 * @property CI_Zip $zip                          Zip Compression Class
 * @property CI_Javascript $javascript            Javascript Class
 * @property CI_Jquery $jquery                    Jquery Class
 * @property CI_Utf8 $utf8                        Provides support for UTF-8 environments
 * @property CI_Security $security                Security Class, xss, csrf, etc...
 * @property ES_Model_Tables $model_tables
 * @property ES_Model_Tables $oTable
 */
class CI_Migration
{/**
     * @var ES_Controller $CI
     */
    protected $CI;

    /**
     * @var ES_Controller $CI
     */
    protected $MI;

    public $settings;
    public $fields;
    public $_ext_php = '.php';
    public $_ext_txt = '.txt';
    public $_ext_js = '.js';
    public $_mod;
    public $_mvc = 'ctrl';
    public $_sub_mod;
    public $_sub_mod_p;
    public $_sub_mod_s;
    public $_mod_type;
    public $_fields;
    public $_keys;
    public $_table_name;
    public $_id_table;
    public $_sub_mod_ctrl;
    public $_sub_mod_model;

    public $_dir_root_mod;
    public $_dir_root_store;
    //public $_dir_migrations_files = APPPATH . "migrations/tables/";
    public $_base_path = APPPATH;
    public $_dir_migrations = '';

    public $_dir_sub_mod;
    public $_dir_sub_mod_views;
    public $_dir_sub_mod_views_content;
    public $_dir_migration;
    public $_dir_mod_migration;
    public $_dir_mod;
    public $_dir_mod_mac;

    public $_file_migration_index = 0;

    public $_file_sub_mod_ctrl;
    public $_file_sub_mod_model;
    public $_file_sub_mod_view_index;
    public $_file_sub_mod_view_edit;
    public $_file_sub_mod_view_cnt;
    public $_file_sub_mod_view_lib;
    public $_file_migration;

    public $_dir_sub_mod_migrate_views;

    public $_dir_sto_sub_mod;
    public $_dir_sto_sub_mod_views;
    public $_dir_sto_sub_mod_views_content;
    public $_dir_sto_migration;
    public $_dir_sto_mod_migration;
    public $_dir_sto_mod;
    public $_dir_sto_mod_mac;

    public $_file_sto_sub_mod_ctrl;
    public $_file_sto_sub_mod_model;
    public $_file_sto_sub_mod_view_index;
    public $_file_sto_sub_mod_view_edit;
    public $_file_sto_sub_mod_view_cnt;
    public $_file_sto_sub_mod_view_lib;
    public $_file_sto_migration;

    public $_migration_files = [];
    public $bReset;

    /**
     * Whether the library is enabled
     *
     * @var bool
     */
    protected $_migration_enabled = FALSE;

    /**
     * Migration numbering type
     *
     * @var    bool
     */
    protected $_migration_type = 'sequential';

    /**
     * Path to migration classes
     *
     * @var string
     */
    protected $_migration_path = NULL;

    /**
     * Path to migration classes
     *
     * @var string
     */
    protected $_dir_migration_tables = NULL;

    /**
     * Current migration version
     *
     * @var mixed
     */
    public $_migration_version = 0;

    /**
     * Database table with migration info
     *
     * @var string
     */
    protected $_migration_table = 'migrations';

    /**
     * Whether to automatically run migrations
     *
     * @var    bool
     */
    protected $_migration_auto_latest = FALSE;

    /**
     * Migration basename regex
     *
     * @var string
     */
    protected $_migration_regex;

    /**
     * Error message
     *
     * @var string
     */
    protected $_error_string = '';

    /**
     * Initialize Migration Class
     *
     * @param    array $config
     * @return    void
     */

    public function __construct($config = array())
    {
        $this->CI = CI_Controller::get_instance();

        // Only run this constructor on main library load
        if (!in_array(get_class($this), array('CI_Migration', config_item('subclass_prefix') . 'Migration'), TRUE)) {
            return;
        }

        foreach ($config as $key => $val) {
            $this->{'_' . $key} = $val;
        }

        $this->verifyAppOrBase();

        log_message('info', 'Migrations Class Initialized');

        // Are they trying to use migrations while it is disabled?
        if ($this->_migration_enabled !== TRUE) {
            show_error('Migrations has been loaded but is disabled or set up incorrectly.');
        }

        // If not set, set it
        $this->_migration_path !== '' OR $this->_migration_path = $this->_base_path . 'migrations/';
        // Add trailing slash if not set
        $this->_migration_path = rtrim($this->_migration_path, '/') . '/';

        // If not set, set it - TIC

        $this->_dir_migration_tables = config_item('dirMigrationFiles');

        // Load migration language
        $this->lang->load('migration');

        // They'll probably be using dbforge
        $this->load->dbforge();

        // Make sure the migration table name was set.
        if (empty($this->_migration_table)) {
            show_error('Migrations configuration file (migration.php) must have "migration_table" set.');
        }

        // Migration basename regex
        $this->_migration_regex = ($this->_migration_type === 'timestamp')
            ? '/^\d{14}_(\w+)$/'
            : '/^\d{3}_(\w+)$/';

        // Make sure a valid migration numbering type was set.
        if (!in_array($this->_migration_type, array('sequential', 'timestamp'))) {
            show_error('An invalid migration numbering type was specified: ' . $this->_migration_type);
        }

        if ($this->_migration_files == null) {
            $this->_migration_files = $this->find_migrations();
            $this->CI->migrationFiles = $this->_migration_files;
        }


        // If the migrations table is missing, make it
        if (!$this->db->table_exists($this->_migration_table)) {
            $this->dbforge->add_field(array(
                'version' => array('type' => 'BIGINT', 'constraint' => 20),
            ));

            $this->dbforge->create_table($this->_migration_table, TRUE);

            $this->db->insert($this->_migration_table, array('version' => 0));
        }

        // Do we auto migrate to the latest migration?
        if ($this->_migration_auto_latest === TRUE && !$this->latest()) {
            show_error($this->error_string());
        }
    }

    // --------------------------------------------------------------------

    /**
     * Migrate to a schema version
     *
     * Calls each migration step required to get to the schema version of
     * choice
     *
     * @param    string $target_version Target schema version
     * @return    mixed    TRUE if no migrations are found, current version string on success, FALSE on failure
     */
    public function version($target_version, $modSign = '')
    {
        // Note: We use strings, so that timestamp versions work on 32-bit systems
        $current_version = $this->_get_version();

        if ($this->_migration_type === 'sequential') {
            $target_version = sprintf('%03d', $target_version);
        } else {
            $target_version = (string)$target_version;
        }

        if ($modSign != '') {
            $modSign = (string)$modSign;
        }

        $migrations = $this->_migration_files;

        if ($modSign != '') {
            if (isset($migrations[$modSign][$target_version])) {
                if ($target_version > 0 && !isset($migrations[$modSign][$target_version])) {
                    $this->_error_string = sprintf($this->lang->line('migration_not_found'), $target_version);
                    return FALSE;
                }
            }
        } else {
            if ($target_version > 0 && !isset($migrations[$target_version])) {
                $this->_error_string = sprintf($this->lang->line('migration_not_found'), $target_version);
                return FALSE;
            }

        }

        if ($target_version > $current_version) {
            $method = 'up';
        } elseif ($target_version < $current_version) {
            $method = 'down';
            // We need this so that migrations are applied in reverse order
            if ($modSign != '') {
                krsort($migrations[$modSign]);
            } else {
                krsort($migrations);
            }

        } else {
            // Well, there's nothing to migrate then ...
            return TRUE;
        }

        // Validate all available migrations within our target range.
        //
        // Unfortunately, we'll have to use another loop to run them
        // in order to avoid leaving the procedure in a broken state.
        //
        // See https://github.com/bcit-ci/CodeIgniter/issues/4539
        $pending = array();
        foreach ($migrations[$modSign] as $number => $file) {
            // Ignore versions out of our range.
            //
            // Because we've previously sorted the $migrations array depending on the direction,
            // we can safely break the loop once we reach $target_version ...
            if ($method === 'up') {
                if ($number <= $current_version) {
                    continue;
                } elseif ($number > $target_version) {
                    break;
                }
            } else {
                if ($number > $current_version) {
                    continue;
                } elseif ($number <= $target_version) {
                    break;
                }
            }

            // Check for sequence gaps
            if ($this->_migration_type === 'sequential') {
                if (isset($previous) && abs($number - $previous) > 1) {
                    $this->_error_string = sprintf($this->lang->line('migration_sequence_gap'), $number);
                    return FALSE;
                }

                $previous = $number;
            }

            include_once($file);
            $class = 'Migration_' . ucfirst(strtolower($this->_get_migration_name(basename($file, '.php'))));

            // Validate the migration file structure
            if (!class_exists($class, FALSE)) {
                $this->_error_string = sprintf($this->lang->line('migration_class_doesnt_exist'), $class);
                return FALSE;
            }
            // method_exists() returns true for non-public methods,
            // while is_callable() can't be used without instantiating.
            // Only get_class_methods() satisfies both conditions.
            elseif (!in_array($method, array_map('strtolower', get_class_methods($class)))) {
                $this->_error_string = sprintf($this->lang->line('migration_missing_' . $method . '_method'), $class);
                return FALSE;
            }

            $pending[$number] = array($class, $method);
        }

        // Now just run the necessary migrations
        foreach ($pending as $number => $migration) {
            log_message('debug', 'Migrating ' . $method . ' from version ' . $current_version . ' to version ' . $number);

            $migration[0] = new $migration[0];
            call_user_func($migration);
            $current_version = $number;
            $this->_update_version($current_version);
        }

        // This is necessary when moving down, since the the last migration applied
        // will be the down() method for the next migration up from the target
        if ($current_version <> $target_version) {
            $current_version = $target_version;
            $this->_update_version($current_version);
        }

        log_message('debug', 'Finished migrating to ' . $current_version);
        return $current_version;
    }

    // --------------------------------------------------------------------

    /**
     * Sets the schema to the latest migration
     *
     * @return    mixed    Current version string on success, FALSE on failure
     */
    public function latest()
    {
        $migrations = $this->find_migrations();

        if (empty($migrations)) {
            $this->_error_string = $this->lang->line('migration_none_found');
            return FALSE;
        }

        $last_migration = basename(end($migrations));

        // Calculate the last migration step from existing migration
        // filenames and proceed to the standard version migration
        return $this->version($this->_get_migration_number($last_migration));
    }

    // --------------------------------------------------------------------

    /**
     * Sets the schema to the migration version set in config
     *
     * @return    mixed    TRUE if no migrations are found, current version string on success, FALSE on failure
     */
    public function current()
    {
        return $this->version($this->_migration_version);
    }

    // --------------------------------------------------------------------

    /**
     * Error string
     *
     * @return    string    Error message returned as a string
     */
    public function error_string()
    {
        return $this->_error_string;
    }

    // --------------------------------------------------------------------

    /**
     * Retrieves list of available migration scripts
     *
     * @return    array    list of migration file paths sorted by version
     */
    public function find_migrations($bWithSubModules = true)
    {
        $migrations = array();
        if ($this->_dir_migration_tables == null) {
            $this->_dir_migration_tables[] = DOCUMENTROOT.config_item('mig_path');
        }

        if ($bWithSubModules) {
            $sys = config_item('sys');
            foreach ($sys as $modSign => $setting) {
                foreach ($this->_dir_migration_tables as $dir) {
                    foreach (glob($dir . $modSign . '/' . '*_*.php') as $file) {
                        $name = basename($file, '.php');

                        // Filter out non-migration files
                        if (preg_match($this->_migration_regex, $name)) {
                            $number = $this->_get_migration_number($name);

                            // There cannot be duplicate migration numbers
                            if (isset($migrations[$modSign][$number])) {
                                $this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $number);
                                show_error($this->_error_string);
                            }

                            $migrations[$modSign][$number] = $file;
                        }
                    }
                }
            }
        } else {
            foreach (glob($this->_migration_path . '*_*.php') as $file) {
                $name = basename($file, '.php');

                // Filter out non-migration files
                if (preg_match($this->_migration_regex, $name)) {
                    $number = $this->_get_migration_number($name);

                    // There cannot be duplicate migration numbers
                    if (isset($migrations[$number])) {
                        $this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $number);
                        show_error($this->_error_string);
                    }

                    $migrations[$number] = $file;
                }
            }
        }
        ksort($migrations);
        return $migrations;
    }

    // --------------------------------------------------------------------

    /**
     * Extracts the migration number from a filename
     *
     * @param    string $migration
     * @return    string    Numeric portion of a migration filename
     */
    protected function _get_migration_number($migration)
    {
        return sscanf($migration, '%[0-9]+', $number)
            ? $number : '0';
    }

    // --------------------------------------------------------------------

    /**
     * Extracts the migration class name from a filename
     *
     * @param    string $migration
     * @return    string    text portion of a migration filename
     */
    protected function _get_migration_name($migration)
    {
        $parts = explode('_', $migration);
        array_shift($parts);
        return implode('_', $parts);
    }

    // --------------------------------------------------------------------

    /**
     * Retrieves current schema version
     *
     * @return    string    Current migration version
     */
    protected function _get_version()
    {
        $row = $this->db->select('version')->get($this->_migration_table)->row();
        return $row ? $row->version : '0';
    }

    // --------------------------------------------------------------------

    /**
     * Stores the current schema version
     *
     * @param    string $migration Migration reached
     * @return    void
     */
    protected function _update_version($migration)
    {
        $this->db->update($this->_migration_table, array(
            'version' => $migration
        ));
    }

    // --------------------------------------------------------------------

    /**
     * Enable the use of CI super-global
     *
     * @param    string $var
     * @return    mixed
     */
    public function __get($var)
    {
        return isset(get_instance()->$var) ? get_instance()->$var : null;
    }

    // *************************************************************
    // Crea la tabla migration si no existe en la base de datos
    // *************************************************************
    public function start($id_migration, $bForce_update = false)
    {
        $_REQUEST['id_migration'] = $id_migration;
        $this->dbforge->updateMigrationTable($id_migration);
    }

    // ************************************************************************************************
    // ********************* Se agrego para la creacion dinamica de los modulos ************************
    // ************************************************************************************************
    public function create_or_alter_table($tableLocal)
    {
        $exists = false;

        if (count($this->dbforge->fields) < 1) {
            header("Refresh:0");
        }
        // =========================== La Construccion de keys y fields debe estar al inicio ======================
        $this->_keys = $this->dbforge->keys;
        $this->_fields = $this->dbforge->fields;
        // ================================================= o =================================================
        if ($this->dbforge->tableExists($tableLocal)) {
            $actual_table = $this->save_or_update_table($tableLocal);
        } else {
            $this->dbforge->create_table($tableLocal);
            if (isset($this->_keys)) {
                $this->_update_indexes_foreignKeys($this->_keys, $this->_fields, $tableLocal);
            }
        }
        $this->_table_name = $tableLocal;
    }

    protected function _update_indexes_foreignKeys($keys, $fields, $localTable)
    {
        $localTableId = $this->dbforge->getPrimaryKeyFromTable($localTable);
        $fields_new_table = array_keys($fields);

        if (is_array($keys) && count($keys)) {
            foreach ($keys as $i => $key) {
                foreach ($key as $constraintName => $settings) {
                    if(validateArray($settings, 'table')){
                        if (validateVar($settings,'array') && $this->dbforge->tableExists($settings['table'])) {
                            $tableForeign = $settings['table'];
                            $idForeign = $settings['idForeign'];
                            $idLocal = $settings['idLocal'];

                            if (in_array($idLocal, $fields_new_table)) {
                                $fk_field = [];
                                $fk_table = validateVar($tableForeign) ? $this->db->field_data($tableForeign) : [];
                                foreach ($fk_table as $i => $set) {
                                    if ($set->name == $idForeign && $set->primary_key) {
                                        $fk_field = $set;
                                        break;
                                    }
                                }
                                if (count((array)$fk_field)) {
                                    if ($fields[$idLocal]['type'] == $fk_field->type ||
                                        $fields[$idLocal]['type'] == strtoupper($fk_field->type) ||
                                        $fields[$idLocal]['type'] == ucfirst($fk_field->type) &&
                                        $fields[$idLocal]['default'] == $fk_field->default ||
                                        $fields[$idLocal]['default'] == strtoupper($fk_field->default) ||
                                        $fields[$idLocal]['default'] == ucfirst($fk_field->default) &&
                                        intval($fields[$idLocal]['constraint']) == intval($fk_field->max_length)
                                    ) {
                                        if (!$this->dbforge->hasRelation($localTable, $idLocal, $tableForeign, $idForeign, $constraintName)) {
                                            if ($this->dbforge->fieldExistsInDB($localTable, $idLocal)) {
                                                $this->dbforge->setRelation($localTable, $idLocal, $tableForeign, $idForeign, $constraintName);
                                            } else {
                                                header("Refresh:0");
                                            }
                                        }
                                    } else {
                                        show_error('Verifica que el campo ' . $idLocal . ' de la actual tabla ' . $localTable . ' tenga las mismas propiedades en la tabla ' . $tableForeign);
                                    }
                                } else {
                                    show_error('Verifica que el campo ' . $idLocal . ' ha sido instanciado de la misma manera en la tabla:' . $tableForeign);
                                }
                            } else if ($this->db->field_exists($idLocal, $localTable)) {
                                if ($this->dbforge->hasRelation($localTable, $idLocal, $tableForeign, $idForeign, $constraintName)) {
                                    if ($this->dbforge->fieldExistsInDB($localTable, $idLocal)) {
                                        $this->dbforge->removeRelation($localTable, $constraintName);
                                    }
                                }
                            }
                        } else {
                            $migIndex = $this->getMigrationIndexFromTableName($settings['table']);
                            list($modSign, $submod) = getModSubMod($settings['table']);
                            redirect("base/migrate/$modSign/$migIndex");
                        }
                    }
                }
            }
        }
    }

    public function getMigrationIndexFromTableName($tableLocal)
    {
        list($modSign, $subMod) = getModSubMod($tableLocal);
        $files = $this->CI->migrationFiles;
        if ($modSign == null) {
            $modSign = $subMod;
        }
        $migrationTabs = $files[$modSign];
        foreach ($migrationTabs as $index => $name) {
            if (strpos($name, $tableLocal)) {
                return $index;
            }
        }
        return 0;
    }

    public function save_or_update_table($tableLocal)
    {
        $actual_table = json_decode(json_encode($this->db->field_data($tableLocal)), true);
        $new_table = $this->dbforge->fields;
        $actual_table = $this->verify_columns_deleted($actual_table, $new_table, $tableLocal);
        list($new_table, $actual_table) = $this->verify_migration_table($actual_table, $new_table, $tableLocal);
        list($new_table, $actual_table) = $this->order_migration_table($actual_table, $new_table, $tableLocal);
        $this->dbforge->fields = $new_table;

        return $actual_table;
    }

    public function verify_columns_deleted($actual_table, $new_table, $tableLocal)
    {
        $keys = $this->dbforge->keys;
        $existe = false;
        foreach ($actual_table as $keyA => $valueA) {
            foreach ($new_table as $keyN => $valueN) {
                if ($valueA['name'] == $keyN) {
                    $existe = true;
                    break;
                } else {
                    $existe = false;
                }
            }
            if (!$existe) {
                $this->_update_indexes_foreignKeys($keys, $new_table, $tableLocal);
                $this->dbforge->drop_column($tableLocal, $valueA['name']);
                array_splice($actual_table, $keyA, 1);
            }
        }
        return $actual_table;
    }

    public function verify_migration_table($actual_table, $new_table, $tableLocal)
    {
        $keys = $this->dbforge->keys;
        $new_table_b = $new_table;
        // ***********************************************************
        // Se convierte la nueva tabla en base a la tabla ya existente
        // ************************************************************

        $table_converted = array();
        $i = 0;
        foreach ($new_table as $keyN => $valueN) {
            $table_converted[$i] = $valueN;
            $table_converted[$i]['name'] = $keyN;
            $i++;
        }
        $new_table = $table_converted;

        // **************************************************************************
        // Se obtiene los nombres de la nueva y actual tabla para ver las diferencias
        // **************************************************************************

        $fields_new_table = array_column($new_table, 'name');
        $fields_actual_table = array_column($actual_table, 'name');

        $diffs = array_diff($fields_new_table, $fields_actual_table);

        if (count($diffs)) {
            foreach ($diffs as $key => $field) {
                $auto_increment = false;
                array_push($actual_table, $new_table[$key]);
                $aField = array($field => $new_table[$key]);
                if (explode('_', $field)[0] == 'id') {
                    if (isset($aField[$field]['auto_increment'])) {
                        unset($aField[$field]['auto_increment']);
                        $auto_increment = true;
                    }
                }

                unset($aField[$field]['name']);
                $this->dbforge->fields = $aField;
                if (!$this->db->field_exists($field, $tableLocal)) {
                    $this->dbforge->add_column($tableLocal, $aField);
                }
                $this->_update_primary_key($field, $actual_table, $tableLocal, $auto_increment);

                if (isset($keys)) {
                    $this->_update_indexes_foreignKeys($keys, $new_table_b, $tableLocal);
                }
            }
        }

        // **********************************************************************
        // Entra si se cambio algun parametro en las propiedades de cada columna
        // **********************************************************************

        foreach ($new_table as $key => $value) {
            $params_nuevos = array_keys($value);
            $params_actual = array_keys($actual_table[$key]);
            $diffs = array_diff($params_nuevos, $params_actual);
            if (count($diffs)) {
                $aField = array($value['name'] => $new_table[$key]);
                unset($aField[$value['name']]['name']);
                $this->dbforge->fields = $aField;
                if ($this->db->field_exists($aField, $tableLocal)) {
                    $this->dbforge->modify_column($tableLocal, $aField);
                }
            }
        }
        return [$new_table, $actual_table];
    }

    public function order_migration_table($actual_table, $new_table, $tableLocal)
    {
        $columnsT1 = array_column($new_table, 'name');
        $columnsT2 = array_column($actual_table, 'name');
        $namesT1 = array_flip($columnsT1);
        $namesT2 = array_flip($columnsT2);

        $namesT2 = array_replace($namesT1, $namesT2);
        $nuevo_orden = array();

        foreach ($namesT2 as $key => $value) {
            $nuevo_orden[] = $actual_table[$value];
        }
        $actual_table = $nuevo_orden;

        $nuevo_orden = array();
        foreach ($new_table as $key => $value) {
            $nuevo_orden[$value['name']] = $value;
            unset($nuevo_orden[$value['name']]['name']);
        }
        $new_table = $nuevo_orden;

        return [$new_table, $actual_table];
    }

    public function set_settings($tableSettings, $tableName)
    {
        if (count($tableSettings)) {
            $this->fields = $fields = $this->dbforge->fields != [] ? $this->dbforge->fields : ($this->_fields == [] ? header("Refresh:0") : $this->_fields);

            $pkTable = $this->dbforge->getPrimaryKeyFromTable($tableName);
            // ******************************************************************************************
            // *********************** Si la tabla modulos existe se agrega el modulo actual*************
            // *********** de lo contrario se redirecciona a la creacion de la tabla modulos ************
            // ******************************************************************************************
            $idMigTable = $this->saveTable($tableSettings, $tableName, $pkTable);

            $defaultData = $this->setDataDefault($tableName, $pkTable, $fields, $idMigTable, $tableSettings);
            // *****************************************************************************************
            // ************************* Se crea el Modelo, Vista Controlador **************************
            // *****************************************************************************************
            if (validateArray($tableSettings, 'ctrl') || validateVar($tableSettings['ctrl'], 'bool')) {
                $ctrlData = $this->createCtrl2($tableName, $pkTable, $fields, $defaultData);
            }
            if (validateArray($tableSettings, 'model') || validateVar($tableSettings['model'], 'bool')) {
                $modelData = $this->createModel2($tableName, $pkTable, $fields, $tableSettings, $defaultData, $ctrlData);
            }
            if (validateArray($tableSettings, 'views') || validateVar($tableSettings['views'], 'bool')) {
                $this->createViewFiles($tableName, $pkTable, $fields, $tableSettings, $defaultData);
            }
        }
    }

    private function saveTable($tableSettings, $tableName, $tablePk)
    {
        $sys = config_item('sys');
        if (validate_modulo('estic', 'tables')) {
          $core = $sys['core'];
            $migIndex = $sys[$core]['id'];
            $migSign = $sys[$core]['sign'];
            $migTable = config_item('mig_table');


            list($modSign, $submod) = getModSubMod($tableName);
            $modName = $sys[$modSign];
            list($modModSign, $modSubmod) = getModSubMod($migTable);
            $modModName = $sys[$modModSign];
            $modIdTable = $this->dbforge->getPrimaryKeyFromTable($migTable);
            $modModName = $sys[$modModName]['name'];
            $modModId = $sys[$modName]['id'];


            if ($this->input->validate('id_migration')) {
                $id_migration = $this->input->get('id_migration');
            } else {
                $oMigrations = $this->db->get('migrations')->result();
                $id_migration = $oMigrations[0]->version + 1;
            }

//            $idModuleTable = intval($idMigTable.'0');
            $this->load->library('session');

            if (validate_modulo('estic','sessions') && validate_modulo('estic','users')){
              $sessUser = $this->session->getDataUserLoggued();
              if(isObject($sessUser)) {
                  if($sessUser->id_role != 1){
                      show_error('El cambio que se desea realizar, requiere permisos del administrador, porfavor contactate con sistemas para continuar');
                      exit();
                  }
              } else {
                  show_error('Para realizar esta accion debes iniciar sesion.');
                  exit();
              }
            } else if(!validate_modulo('estic','users')){
                show_error_handled("El modulo users no se encuentraba creado, debido a ello no se pudo registrar la tabla $tableName, al momento de la migracion: $id_migration, verifica que el modulo base/usere se encuentra creado para evitar este error");
                return $id_migration;
            }
            if (validate_modulo('estic', 'tables') && validate_modulo('estic','tables_roles')){

              if (validate_modulo($modModName, $modSubmod)) {
                  // $sessUser = $this->session->getObjectUserLoggued();

  //                $this->CI->initModulesTables(true);
  //                $oModuleTable = $this->CI->model_modules_tables->findOneByIdModuleTable($id_migration);

                  $data = array(
                      'title' => validateArray($tableSettings, 'title') ? $tableSettings['title'] : setLabel($submod,true),
                      'table_name' => $tableName,
                      'icon' => validateArray($tableSettings, 'icon') ? $tableSettings['icon'] : '',
                      'url_edit' => validateArray($tableSettings, 'url') ? $tableSettings['url'].'/edit' : $sys[$modName]['dir'] . "$submod/edit",
                      'url_index' => validateArray($tableSettings, 'url') ? $tableSettings['url'].'/index' : $sys[$modName]['dir'] . "$submod/index",
                      'url' => validateArray($tableSettings, 'url') ? $tableSettings['url'] : $sys[$modName]['dir'] . "$submod",
                      'description' => validateArray($tableSettings, 'descripcion') ? $tableSettings['descripcion'] : '',
                      'status' => validateArray($tableSettings, 'estado') ? $tableSettings['estado'] : 'enabled',
                      'listed' => validateArray($tableSettings, 'bIsListed') ? $tableSettings['bIsListed'] : 'enabled',
                      'id_module' => $modModId,
                      'id_role' => 1
                  );

                  if($this->CI->initTables(true)){
                      $oTable = $this->CI->model_tables->findOneByIdTable($id_migration);
                      $data['change_count'] = isObject($oTable) ? $oTable->getChangeCount() : 0;
                      if (isObject($oTable)) {
                          $data = $this->CI->model_tables->save($data, $id_migration);
                      } else {
                          show_error("Se intenta crear una nueva tabla $tableName, con la migracion $id_migration, para ello debe estar registraba en la tabla es_tables");
  //                    $data = $this->CI->model_tables->save($data, null, $id_migration);
                      }
                  };

                  if($this->CI->initTablesRoles(true)){
                      $oTableRoles = $this->CI->model_tables_roles->findOneByIdTable($id_migration);
                      $data['id_role'] = 1;
                      if(isObject($oTableRoles)){
                          $data = $this->CI->model_tables_roles->save($data,$id_migration);
                      } else {
                          $data = $this->CI->model_tables_roles->save($data,null,$id_migration);
                      }
                  };

  //                $data['id_module'] = $modModId;
  //                if(isObject($oModuleTable)){
  //                    $data = $this->CI->model_modules_tables->save($data,$id_migration);
  //                } else {
  //                    $data = $this->CI->model_modules_tables->save($data,null,$id_migration);
  //                }

              } else if ($tableName != $migTable) {
                  redirect("sys/migrate/$migSign/$migIndex");
              }
            } else if ($tableName != $migTable) {
              redirect("sys/migrate/$migSign/$migIndex");
            }
            return $id_migration;
        }
    }

    private function getIdUserDefault()
    {
        if ($this->db->table_exists('es_users')) {
            $this->db->where('id_user', 1);
            $oUser = $this->db->get('es_users')->row();
            $siteDomain = config_item('site_domain');
            if (is_object($oUser)) {
                return valNumeric($oUser->id_user);
            } else {
                $data = array(
                    'id_user' => 1,
                    'name' => 'Rafael',
                    'lastname' => 'Gutierrez',
                    'email' => "rafael@$siteDomain",
                    'password' => hash_sha('123'),
                    'date_created' => date('Y-m-d H:i:s'),
                    'date_modified' => date('Y-m-d H:i:s')
                );
                $this->db->set($data);
                if ($this->db->insert('es_users')) {
                    $idUser =  $data['id_user'];
                };
                $this->updateUserRoleDefault($idUser);
            }
        }
    }

    private function updateUserRoleDefault($idUser = null)
    {
        if ($this->db->table_exists('es_roles')) {
            $this->db->where('id_role', 1);
            $oRole = $this->db->get('es_roles')->row();
            if (is_object($oRole)) {
                $this->db->where('id_user', $idUser);
                $oUser = $this->db->get('es_users')->row();
                $data = array(
                    'id_role' => $oRole->id_role
                );
                $this->db->set($data);
                $this->db->update('es_users', $data);
                return $oRole->id_role;
            } else {
                $data = array(
                    'id_role' => 1,
                    'name' => 'Super Admin',
                    'description' => 'Administrador con todos los privilegios',
                    'id_user_created' => $idUser,
                    'id_user_modified' => $idUser,
                    'date_created' => date('Y-m-d H:i:s'),
                    'date_modified' => date('Y-m-d H:i:s')
                );
                $this->db->set($data);
                $this->db->insert('es_roles');

                $this->db->where('id_user', $idUser);
                $oUser = $this->db->get('es_users')->row();
                $data = array(
                    'id_user' => $idUser,
                    'id_role' => $data['id_role']
                );
                $this->db->set($data);
                $this->db->update('es_users', $data);
            }
        }
    }

    public function setDataDefault($tableName, $pkTable, $fields, $idMigTable, $tableSettings)
    {
        $sys = config_item('sys');
        $excepts = array_merge(config_item('controlFields'), [$pkTable]);
        $allFields = array_keys($fields);
        $aFieldsColumnsKey = $this->dbforge->getArrayColumnsKey($tableName);
        if($aFieldsColumnsKey != null){
            foreach ($aFieldsColumnsKey as $keyNum => $fieldFkName) {
                if (compareStrStr($fieldFkName, 'id_user_modified')) {
                    unset($aFieldsColumnsKey[$keyNum]);
                }
                if (compareStrStr($fieldFkName, 'id_user_created')) {
                    unset($aFieldsColumnsKey[$keyNum]);
                }
            }
        }

        $vFieldsViews = $this->getEditViews($idMigTable, $fields, $allFields, $excepts);
        $vFieldsNames = array_diff($allFields, $excepts);
        $vFields = [];
        foreach ((array)$vFieldsNames as $name) {
            $vFields[setObject($name)] = $fields[$name];
        }
        list($modSign, $submod) = getModSubMod($tableName);
        list($subModS, $subModP) = setSingularPlural($submod);
        $modName = $sys[$modSign];
        list($modS, $modP) = setSingularPlural($modName);
        $data = array();

        list($vFieldsChecked, $fieldImg, $fieldPass, $fieldHidden, $data) = $this->checkInputFields($vFields, $data);
        $aFieldsNames = array_keys($vFieldsChecked);
        list($pkTableS,$pkTableP) = setSingularPlural($pkTable);
        $data["validatedFieldsNames"] = var_export($aFieldsNames, true);
        $data["userCreated"] = config_item('soft_user');
        $data["dateCreated"] = date('d/m/Y');
        $data["timeCreated"] = date("g:i a");
        $data["dbName"] = $this->db->database;
        $data["tableName"] = $tableName;
        $data["UcTableP"] = ucfirst($subModP);
        $data["UcObjTableP"] = ucfirst(setObject($subModP));
        $data["UcObjTableS"] = ucfirst(setObject($subModS));
        $data["lcObjTableS"] = '$'.ucfirst(setObject($subModS));
        $data["UcTableModel"] = ucfirst($submod);
        $data["UcTableS"] = ucfirst($subModS);
        $data["UcModS"] = ucfirst($modS);
        $data["UcModP"] = ucfirst($modP);
        $data["idTable"] = $pkTable;
        $data["idObjTable"] = setObject($pkTable);
        $data["UcIdObjTable"] = ucfirst(setObject($pkTable));
        $data["UcIdObjTableS"] = ucfirst(setObject($pkTableS));
        $data["lcIdObjTable"] = lcfirst(setObject($pkTable));
        $data["pkTableVar"] = $pkTable;
        $data["lcTableP"] = lcfirst($subModP);
        $data['$lcTableS'] = '$' . lcfirst($subModS);
        $data['lcTableS'] = lcfirst($subModS);
        $data["lcModS"] = lcfirst($modName);
        $data["lcmodSign"] = strtolower($modSign);
        $data["tableTitle"] = validateArray($tableSettings, 'title') ? $tableSettings['title'] : setLabel($subModS);
        $data['editView'] = '';


        list($data, $aEditViewsPhpContent) = $this->loadEditViews($fields, $vFieldsViews, $data, $tableName);

        return [$modSign, $submod, $subModS, $subModP, $data, $vFields, $aEditViewsPhpContent, $fieldImg, $fieldPass, $fieldHidden];
    }

    public function checkInputFields($vFields, $data)
    {
        $vFieldsBackup = $vFields;
        $fieldPass = '';
        $fieldHidden = '';
        $fieldImg = false;
        $data['setADBTablesRefFields'] = '';
        $bDBTable = false;
        foreach ($vFields as $name => $settings) {
            if (compareArrayStr($settings, 'input', 'hidden')) {
                $fieldHidden = $name;
                unset($vFieldsBackup[$name]);
            }
            if (compareArrayStr($settings, 'input', 'password')) {
                $fieldPass = $name;
                unset($vFieldsBackup[$name]);
            } else if (strpos($name, 'pass') > -1) {
                $fieldPass = $name;
                unset($vFieldsBackup[$name]);
            }
            if (compareArrayStr($settings, 'input', 'image') || compareArrayStr($settings, 'input', 'file')) {
                $fieldImg = $name;
                unset($vFieldsBackup[$name]);
            } else if (strpos($name, 'img') > -1) {
                $fieldImg = $name;
                unset($vFieldsBackup[$name]);
            }

            if (!$bDBTable && validateArray($settings, 'options') && compareArrayStr($settings, 'options', 'db_tabs')) {
                $data['idDBTableRef'] = validateArray($settings, 'db_idTableRef') ? $settings['db_idTableRef'] : 'idDBTableRef';
                $data['fieldDBTableRef'] = validateArray($settings, 'db_fieldTableRef') ? $settings['db_fieldTableRef'] : 'fieldDBTableRef';
                $data['setADBTablesRefFields'] .= $this->load->view(["template_controller" => 'setADBTablesRefFields'], $data, true, true, true);
                $bDBTable = true;
            }
        }

        return [$vFieldsBackup, $fieldImg, $fieldPass, $fieldHidden, $data];
    }

    public function getFieldNameEditView($fields, $idSettings, $editView, $tableName = '')
    {
        $aFieldsNames = array_keys($fields);
        if (in_array($idSettings, $aFieldsNames)) {
            return $idSettings;
        } else {
            foreach ($fields as $name => $fieldSettings) {
                if (validateArray($fieldSettings, 'idForeign')) {
                    if ($fieldSettings['idForeign'] == $idSettings) {
                        return $idSettings;
                    }
                } else {
                    show_error("Para armar la vista $editView, se necesita una relacion en la tabla: $tableName con la la tabla es_settings, 
                    introduce una fila en la tabla que se relacione con es_options, por ejemplo: id_option_tipo_$editView, 
                    y que filtre en base a ese tipo de opciones");
                }
            }
        }
    }

    public function loadEditViews($fields, $vFieldsViews, $data, $tableName)
    {
        $aPhpContentEditViews = array();
        $data['editView'] = '';
        $data['validatedControllerFieldsEditView'] = '';
        $data['viewLoadEditData'] = '';
        $data['anchorToEditView'] = '';
        $data['linkToEditView'] = '';
        $data["validatedModelFieldsEditView"] = '';
        $tableTitle = $data['tableTitle'];
        $aEditNameViewsSettings = class_exists('CiSettingsQuery') ? CiSettingsQuery::create()->select(['id_setting', 'edit_tag'])->find()->getData() : [];
        if(validateVar($aEditNameViewsSettings, 'array')){
            $aIdsSettings = array_column($aEditNameViewsSettings, 'id_setting');
            $aTagsSettings = array_column($aEditNameViewsSettings, 'edit_tag');
            $aEditViewSettings = array_combine($aTagsSettings, $aIdsSettings);
        }
        if (validateVar($vFieldsViews, 'array')) {
            foreach ($vFieldsViews as $fieldLink => $editViews) {
                if (validateVar($editViews, 'array')) {
                    foreach ($editViews as $vNameView => $vFieldsView) {
//                        $vNameViewTitle = ucfirst(setObject($vNameView,true));
                        if (validateVar($vFieldsView, 'array')) {
                            // ********************* Para el View Edit ***************************
                            list($htmlFormContentEditView, $aEachNamesEditView, $modalsContentEditView) = $this->setInputFields($fields, $vFieldsView, $data);
                            list($data) = $this->setEachFields($fields, $aEachNamesEditView, $data);
                            $data['htmlFieldsEditForm'] = $htmlFormContentEditView;
                            if (isset(explode('-', $vNameView)[1])) {
                                $name = explode('-', $vNameView)[1];
                            } else {
                                $name = '';
                                show_error("El nombre del edit tag: $vNameView debe introducirce en el formato edit-nombre, de la tabla: $tableName");
                            }
                            $data['editView'] = "$name/";
                            $data['tableTitle'] = $tableTitle . ' para: ' . setLabel($name, true);
                            $aPhpContentEditViews[$fieldLink][$vNameView] = $this->load->view("template_edit", $data, true, true, true);
                            $data['tableTitle'] = $tableTitle;
                            // ********************* Para el Model ***************************
                            $data['rulesNameEditView'] = '$rules_' . str_replace('-', '_', $vNameView);
                            $data = $this->getPhpFieldsRules($vFieldsView, $data['pkTableVar'], $data);
                            $data["validatedModelFieldsEditView"] .= $this->load->view(["template_ES_Model" => "validatedModelFieldsEditView"], $data, true, true, true);

                            list($vFieldsIniChecked, $fieldIniImg, $fieldIniPass, $fieldHidden, $data) = $this->checkInputFields($vFieldsView, $data);
                            if (strhas($vNameView, 'ini')) {
                                $data['editView'] = $vNameView;
                                $data["validatedFieldsEditIni"] = var_export(array_keys($vFieldsIniChecked), true);
                                $data['editNameView'] = explode('-', $vNameView)[1];
                            } else {
                                // ********************* Para el Controller ***************************
                                $data['fieldsEditView'] = var_export(array_keys($vFieldsIniChecked), true);
                                $data['editView'] = $vNameView;
                                $data['editNameView'] = explode('-', $vNameView)[1];
                                $data['validatedControllerFieldsEditView'] .= $this->load->view(["template_ES_Ctrl" => "validatedControllerFieldsEditView"], $data, true, true, true);
                                $data['editNameView'] = explode('-', $vNameView)[1];
                                $data['indexEditNameView'] = validateArray($aEditViewSettings, $vNameView) ? $aEditViewSettings[$vNameView] : '';
                            }
                            // ********************* Para el View Index ***************************
                            $data['indexEditViewTitle'] = setLabel($data['editNameView'], true);
                            $data['anchorToEditView'] .= $this->load->view(["template_index" => "anchorToEditView"], $data, true, true, true);
                            $data['fieldEditView'] = $fieldLink;
                            $data['linkToEditView'] = $this->load->view(["template_index" => "linkToEditView"], $data, true, true, true);
                        }
                    }
                }
            }
        }
        return [$data, $aPhpContentEditViews];
    }

    public function verifyRelatedTables($relations){
        $vRelatedWithOutExcepts = $relations;
        foreach ($vRelatedWithOutExcepts as $fkName => $settings) {
            $idForeign = $settings['idForeign'];
            foreach ($vRelatedWithOutExcepts as $fkName2 => $settings2){
                $break = false;
                if(inArray('filterBy',$settings) && is_array($settings['filterBy'])){
                    $aFiltersBy = $settings['filterBy'];
                    foreach ($aFiltersBy as $keyFilter => $valFilter) {
                        if(inArray($keyFilter,$this->fields)){
                            $break = true;
                        }
                    }
                }
                if($break){
                    break;
                }
                if($settings2['pk'] == $idForeign){
                    unset($relations[$fkName2]);
                }
            }
        }
        return $relations;
    }
    public function createCtrl2($tableName, $pkTable, $fields, $default = [])
    {
        $sys = config_item('sys');
        $aDBTablesPks = $this->dbforge->getPrimaryKeysOfTables(true);
        $aDBTablesFks = $this->dbforge->getForeignKeyOfTables(true);
        $aMixDbPkFk = array_merge($aDBTablesPks,$aDBTablesFks);
        $excepts = array_merge(config_item('controlFields'), [$pkTable]);
        list($modSign, $submod, $subModS, $subModP, $data, $vFields, $aEditViewsPhpContent, $fieldImg, $fieldPass, $fieldHidden) = $default;

        $data['initVarsForeignTable'] = '';
        $data['loadModelsForeignTable'] = '';
        $data['setObjectForeignTable'] = '';
        $data['initFieldsSelectBy'] = '';
        $data['initFieldsFilterBy'] = '';
        $data['setFieldsForeignTable'] = '';
        $data['setForeignTableFields'] = '';
        $data['compareFieldsForeignTable'] = '';
        $data['setObjFieldsFilterBy'] = '';
        $relationsUnique = $this->getTableRelations($fields, true);
        $relationsWithOutExcepts = $this->getTableRelations($fields, false, true, true);

        $relationsUnique = $this->verifyRelatedTables($relationsUnique);
        $relationsWithOutExcepts = $this->verifyRelatedTables($relationsWithOutExcepts);

        foreach ((array)$relationsUnique as $fkName => $settings) {
            list($fModSign, $fSubmod) = getModSubMod($settings['table']);
            $fModName = $sys[$fModSign];
            list($fSubModS, $fSubModP) = setSingularPlural($fSubmod);
            list($fModS, $fModP) = setSingularPlural($sys[$fModName]['name']);
            $data['lcFkObjFieldP'] = '$' . lcfirst(setObject($fSubModP));
            $data['UcFkObjFieldP'] = ucfirst(setObject($fSubModP));
            $data['lcFkTableP'] = lcfirst($fSubModP);
            $data['UcFkTableP'] = ucfirst($fSubModP);
            $data['lcFkModS'] = lcfirst($fModS);
            $data['lcFkModP'] = lcfirst($fModP);
            $data['initVarsForeignTable'] .= $this->load->view(["template_ES_Ctrl" => "initVarsForeignTable"], $data, true, true, true);

            $data['loadModelsForeignTable'] .= $this->load->view(["template_ES_Ctrl" => "loadModelsForeignTable"], $data, true, true, true);
        }
        for ($ind = 0; $ind < 3; $ind++) {
            $aLoaded = [];
            if ($ind == 2) {
                $selector = 'compareFieldsForeignTable';
            } else if ($ind == 1) {
                $selector = 'setFieldsForeignTable';
            } else if ($ind == 0) {
                $selector = 'initFieldsSelectBy';
            }

            foreach ($relationsWithOutExcepts as $fkName => $settings) {
                // --------------------------------------- Template Controller ------------------------------------------------
                if (isset($settings['divider'])) {
                    $data['divider'] = $settings['divider'];
                } else {
                    $data['divider'] = ' ';
                }
                list($fModSign, $fSubmod) = getModSubMod($settings['table']);
                $fModName = $sys[$fModSign];
                list($fSubModS, $fSubModP) = setSingularPlural($fSubmod);
                list($fModS, $fModP) = setSingularPlural($sys[$fModName]['name']);
                if ($fSubModP == "options") {
                    if (validateArray($settings, 'field')) {
                        $object = strhas($settings['field'], 'id_') ? explode('id_', $settings['field'])[1] : $settings['field'];
                    } else {
                        $object = $fSubModP;
                    }
                    $data['lcFkObjFieldP'] = lcfirst(setObject($object, true));
                    $data['UcFkObjFieldP'] = ucfirst(setObject($object, true));
                } else {
                    $data['lcFkObjFieldP'] = lcfirst(setObject($fSubModP, true));
                    $data['UcFkObjFieldP'] = ucfirst(setObject($fSubModP, true));
                }
                $data['lcFkTableP'] = lcfirst($fSubmod);
                $data['UcFkTableP'] = ucfirst($fSubmod);
                $data['lcFkModS'] = lcfirst($fModS);
                $data['lcFkModP'] = lcfirst($fModP);
                list($data) = $this->validateFkTable($data, $fields, $settings, $sys,null, $aMixDbPkFk, $tableName);

                if ($ind == 1) {
                    $data['fkLcTableP'] = $data['lcFkTableP'];
                    $data['idFkLcTableP'] = $settings['idForeign'];
                    $data['idLocalLcTableP'] = $settings['field'];
                    $data['setForeignTableFields'] .= $this->load->view(["template_ES_Model" => 'setForeignTableFields'], $data, true, true, true);
                }
                // --------------------------------------------------------------------------------------------------------


                // ---------------------------------------- Template ES_Controller ----------------------------------------
                if (validateArray($settings, 'filterBy') && validateArray($settings, 'idForeign')) {
//                    if ($ind != 2) {
//                        $data[$selector] .= $this->load->view(["template_ES_Ctrl" => $selector], $data, true, true, true);
//                        $aLoaded[] = $data['lcFkTableP'];
//                    }
                    if ($ind == 0) {
//                        $data['fFieldsRef'] = inArray('selectBy',$settings) ? var_export($settings['selectBy'], true) : [];
                        foreach ($settings['filterBy'] as $field => $filter) {
                            list($filterS,$filterP) = setSingularPlural($filter);
                            $data['UcObjField'] = ucfirst(setObject($field));
                            $data['indexFilterBy'] = $filter;
                            $data['lcObjFilterByP'] = lcfirst(setObject($filterP));
                            $data['UcObjFilterByP'] = ucfirst(setObject($filterP));
                            $data['initFieldsFilterBy'] .= $this->load->view(["template_ES_Ctrl" => 'initFieldsFilterBy'], $data, true, true, true);
                            $data['setObjFieldsFilterBy'] .= $this->load->view(["template_ES_Ctrl" => 'setObjFieldsFilterBy'], $data, true, true, true);
                        }
                        $aLoaded[] = $data['lcFkTableP'];
                    }
                } else {
                    if (!in_array($data['lcFkTableP'], $aLoaded) && $ind != 2) {
                        $data[$selector] .= $this->load->view(["template_ES_Ctrl" => $selector], $data, true, true, true);
                        $aLoaded[] = $data['lcFkTableP'];
                    }
                }
                if (validateArray($data, 'setOfFkSettings')) {
                    $bVerified = false;
                    if (!in_array($data['lcFkTableP'], $aLoaded)) {
                        $bVerified = true;
                    }
                    if ($bVerified) {
                        $data = $this->verifySubModOptions($fSubModP, $settings, $data);
                        if ($ind == 2) {
                            list($data, $aLoaded) = $this->verifySetOfSettings($data, $ind, $selector, $aLoaded);
                        } else {
                            $data[$selector] .= $this->load->view(["template_ES_Ctrl" => $selector], $data, true, true, true);
                            $aLoaded[] = $data['lcFkTableP'];
                        }
                    } else if ($data['lcFkTableP'] == 'options') {
                        $data = $this->verifySubModOptions($fSubModP, $settings, $data);
                        list($data, $aLoaded) = $this->verifySetOfSettings($data, $ind, $selector, $aLoaded);
                    }
                    unset($data['setOfFkSettings']);
                }
                // -------------------------------------------------------------------------------------------------------------
            }
        }

        if($tableName == 'es_files'){
            $data["validateFieldImgUpload1"] = $this->load->view(["template_controller" => "validateFieldImgUpload1"], $data, true, true);
            $data["validateFieldImgUpload2"] = $this->load->view(["template_controller" => "validateFieldImgUpload2"], $data, true, true);
            $data["validateFieldImgUpload4"] = $this->load->view(["template_ES_Ctrl" => "validateFieldImgUpload4"], $data, true, true);
        }
        if($tableName == 'es_users'){
            $data["validateUserSavedForRolling1"] = $this->load->view(["template_controller" => "validateUserSavedForRolling1"], $data, true, true);
            $data["validateUserSavedForRolling2"] = $this->load->view(["template_ES_Ctrl" => "validateUserSavedForRolling2"], $data, true, true);
            $data["validateUsersSavedForPersonTable1"] = $this->load->view(["template_controller" => "validateUsersSavedForPersonTable1"], $data, true, true);
            $data["validateUsersSavedForPersonTable2"] = $this->load->view(["template_ES_Ctrl" => "validateUsersSavedForPersonTable2"], $data, true, true);
            $data["validateUsersSavedForEstudentTable1"] = $this->load->view(["template_controller" => "validateUsersSavedForEstudentTable1"], $data, true, true);
            $data["validateUsersSavedForEstudentTable2"] = $this->load->view(["template_ES_Ctrl" => "validateUsersSavedForEstudentTable2"], $data, true, true);

        }

        if ($fieldImg != '') {
            $data['lcField'] = $fieldImg;
            $data['lcObjField'] = setObject($fieldImg);
            $data["validateFieldsImgsIndex"] = $this->load->view(["template_ES_Model" => "validateFieldsImgsIndex"], $data, true, true);
            $data["validateFieldImgIndex"] = $this->load->view(["template_controller" => "validateFieldImgIndex"], $data, true, true);
            $data["validateFieldImgUpload3"] = $this->load->view(["template_controller" => "validateFieldImgUpload3"], $data, true, true);
        }
        if ($fieldPass != '') {
            $data['lcField'] = $fieldPass;
            $data['lcObjField'] = setObject($fieldPass);
            $data["validateFieldPassword"] = $this->load->view(["template_controller" => "validateFieldPassword"], $data, true, true);
        }
        $data["extraFunctions"] = $this->getExtraFunctions($tableName);
        $modName = $sys[$modSign];
        $phpCrudContent = $this->load->view("template_ES_Ctrl", $data, true, true, true);
        $phpCtrlContent = $this->load->view("template_controller", $data, true, true, true);
        $framePathOrm = ROOTPATH . "orm/crud/$modName/";
        $framePathApp = ROOTPATH . "app/modules/$modName/";
        if (createFolder($framePathOrm)) {
            if (createFolder($framePathOrm . "$submod/")) {
                write_file($framePathOrm . "$submod/ES_Ctrl_" . ucfirst($submod) . $this->_ext_php, $phpCrudContent);
            }
        }
        if (createFolder($framePathApp)) {
            if (createFolder($framePathApp . "$submod/")) {
                $ctrlFile = $framePathApp . "$submod/Ctrl_" . ucfirst($submod) . $this->_ext_php;
                if (!file_exists($ctrlFile)) {
                    write_file($ctrlFile, $phpCtrlContent);
                }
            }
        }
        return $data;
    }

    private function verifySubModOptions($fSubModP, $settings, $data)
    {
        if ($fSubModP == "options") {
            if (validateArray($settings, 'field')) {
                $object = strhas($settings['field'], 'id_') ? explode('id_', $settings['field'])[1] : $settings['field'];
            } else {
                $object = $fSubModP;
            }
            $data['lcFkObjFieldP'] = setObject($object, true, true);
            $data['UcFkObjFieldP'] = setObject($data['setOfFkSettings']['UcFkObjFieldP'], true, true);
        } else {
            $data['lcFkObjFieldP'] = setObject($data['setOfFkSettings']['lcFkObjFieldP'], true, true);
            $data['UcFkObjFieldP'] = setObject($data['setOfFkSettings']['UcFkObjFieldP'], true, true);
        }
        $data['lcFkTableP'] = $data['setOfFkSettings']['lcFkTableP'];
        $data['fFieldsRef'] = $data['setOfFkSettings']['fFieldsRef'];
        return $data;
    }

    private function verifySetOfSettings($data, $ind, $selector, $aLoaded)
    {
        if ($ind == 2) {
            $data['t1Contents'] = $data['setOfFkSettings']['t1Contents'];
            $data['t1FieldRef'] = $data['setOfFkSettings']['t1FieldRef'];
            $data['t2Contents'] = $data['setOfFkSettings']['t2Contents'];
            $data['t2FieldRef'] = $data['setOfFkSettings']['t2FieldRef'];
            $data[$selector] .= $this->load->view(["template_ES_Ctrl" => $selector], $data, true, true, true);
            $aLoaded[] = $data['lcFkTableP'];
        }
        return [$data, $aLoaded];
    }

    public function createModel2($tableName, $pkTable, $fields, $tableSettings = [], $default = [], $ctrlData = [])
    {
        $sys = config_item('sys');

        list($modSign, $submod, $subModS, $subModP, $data, $vFields) = $default;
        $data['setForeignTableFields'] = inArray('setForeignTableFields',$ctrlData) ? $ctrlData['setForeignTableFields'] : null;
        $data['validateFieldsImgsIndex'] = inArray('validateFieldsImgsIndex', $ctrlData) ? $ctrlData['validateFieldsImgsIndex'] : null;
        $data['loadModelsForeignTable'] = inArray('loadModelsForeignTable', $ctrlData) ? $ctrlData['loadModelsForeignTable'] : null;
//        $data = $this->getPhpFieldsProperties($fields, $data);
        $data = $this->getPhpFieldsRules($fields, $pkTable, $data);
//        $data = $this->getPhpFieldsRules($vFields, $pkTable, $data,true);
        $data = $this->getPhpStdFields($tableName, $pkTable, $data);

        $modName = $sys[$modSign];
        $phpModelContent = $this->load->view("template_model", $data, true, true, true);
        $phpTraitContent = $this->load->view("template_ES_Model", $data, true, true, true);

        $framePathOrm = ROOTPATH . "orm/crud/$modName/";
        $framePathApp = ROOTPATH . "app/modules/$modName/";
        if (createFolder($framePathOrm)) {
            if (createFolder($framePathOrm . "$submod/")) {
                write_file($framePathOrm . "$submod/ES_Model_" . ucfirst($submod) . $this->_ext_php, $phpTraitContent);
            }
        }
        if (createFolder($framePathApp)) {
            if (createFolder($framePathApp . "$submod/")) {
                $modelFile = $framePathApp . "$submod/Model_" . ucfirst($submod) . $this->_ext_php;
                if (!file_exists($modelFile)) {
                    write_file($modelFile, $phpModelContent);
                }
            }
        }
        return $data;
    }

    public function createViewIndex($tableName, $pkTable, $fields, $tableSettings = [], $default = [])
    {
        $sys = config_item('sys');

        list($modSign, $submod, $subModS, $subModP, $data, $vFields) = $default;
        $data["tableHeaderHtmlTitles"] = $this->setHtmlHeaderTitles($fields, $vFields, $tableSettings);
        $data["tableBodyHtmlFields"] = $this->setHtmlBodyFields($fields, $vFields, $tableSettings, $subModS, $subModP);
//        list($data) = $this->loadEditViews($fields, $vFieldsViews, $data, $tableSettings);
        $phpContent = $this->load->view("template_index", $data, true, true, true);
        $modName = $sys[$modSign];
        $frameAppPath = ROOTPATH . "app/modules/$modName/";
        if (createFolder($frameAppPath)) {
            if (createFolder($frameAppPath . "$submod/")) {
                if (createFolder($frameAppPath . "$submod/views/")) {
                    $filePath = $frameAppPath . "$submod/views/index" . $this->_ext_php;
                    if (!file_exists($filePath)) {
                        write_file($filePath, $phpContent);
                    }
                }
            }
        }
    }

    public function createViewEdit($tableName, $pkTable, $fields, $tableSettings = [], $default = [])
    {
        $sys = config_item('sys');
        list($modSign, $submod, $subModS, $subModP, $data, $vFields, $aPhpContentEditViews) = $default;
        list($htmlFormContent, $aEachNames, $modalsContent) = $this->setInputFields($fields, $vFields, $data, $tableName);
        list($data) = $this->setEachFields($fields, $aEachNames, $data);
        $data['htmlFieldsEditForm'] = $htmlFormContent;

        // **************** no quitar - permite diferenciar entre las vistas extras que se agrega como edit-ini *************
        $data['editView'] = '';
        $phpContent = $this->load->view("template_edit", $data, true, true, true);
        $phpContent .= $modalsContent;

        $modName = $sys[$modSign];
        $frameAppPath = ROOTPATH . "app/modules/$modName/";
        if (createFolder($frameAppPath)) {
            if (createFolder($frameAppPath . "$submod/")) {
                if (createFolder($frameAppPath . "$submod/views/")) {
                    $filePath = $frameAppPath . "$submod/views/edit" . $this->_ext_php;
//                    if (!file_exists($filePath)) {
                        write_file($filePath, $phpContent);
//                    }
                    if (validateVar($aPhpContentEditViews, 'array')) {
                        foreach ($aPhpContentEditViews as $fieldLinkEditView => $aTagsEditView) {
                            if (validateVar($aTagsEditView, 'array')) {
                                foreach ($aTagsEditView as $tagEditView => $phpContentEditView) {
                                    $fileEditViewPath = $frameAppPath . "$submod/views/$tagEditView" . $this->_ext_php;
//                                    if (!file_exists($fileEditViewPath)) {
                                        write_file($fileEditViewPath, $phpContentEditView);
//                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function setEachFields($fields, $aEachNames, $data)
    {
        if (validateVar($aEachNames, 'array')) {
            foreach ($aEachNames as $i => $eachName) {
                // TODO: Solo funciona para un each, modificar para que funcione para varios
                $tableEach = $fields[$eachName]['table'];
                $idTableEach = $eachName;
                list($modEach, $submodEach) = getModSubMod($tableEach);
                list($tableEachS, $tableEachP) = setSingularPlural($submodEach);
                $data["UcEachTableP"] = ucfirst($tableEachP);
                $data["UcEachTableS"] = ucfirst($tableEachS);
                $data["idEachTable"] = "$$idTableEach";
                $data["startInsertEachOne"] = $this->load->view(["template_edit" => "startInsertEachOne"], $data, true, true);
                $data["endInsertEachOne"] = $this->load->view(["template_edit" => "endInsertEachOne"], $data, true, true);
            }
        }
        return [$data];
    }

    private function setInputFields($fields, $vFields, $data, $tableName = '')
    {
        $sys = config_item('sys');
        $modal = false;
        $htmlFormContent = '';
        $aEachNames = [];
        $modalsContent = '';
        $bIsTextArea = false;
        $aRdsChks = ['radios','radio','checkbox','checkboxes'];
        foreach ($vFields as $name => $settings) {

            $inputData = array(
                "name" => validateArray($settings, 'name') ? setObject(lcfirst($settings['name'])) : lcfirst(setObject($name)),
                "id" => validateArray($settings, 'id') ? $settings['id'] : "input" . ucfirst(setObject($name)),
                "class" => "form-control " . (validateArray($settings, 'class') ? $settings['class'] : ""),
                "placeholder" => validateArray($settings, 'placeholder') ? $settings['placeholder'] : '',
            );
            $typeForm = validateArray($settings, 'input') ? $settings['input'] : 'default';
            $data['lcInputId'] = "input" . ucfirst(setObject($name));
            $data['UcInputId'] = "input" . ucfirst(setObject($name));
            $data['lcInputName'] = setObject(lcfirst($name));
            $data['lcField'] = lcfirst($name);
            $data['lcObjField'] = lcfirst(setObject($name));
            $data['UcObjField'] = ucfirst(setObject($name));
            $data['lcErrorForField'] = setObject(lcfirst($name));

            if (validateArray($settings, 'onclick')) {
                $inputData['onclick'] = $settings['onclick'];
                if ((strstr($settings['onclick'], 'Modal') || strstr($settings['onclick'], 'modal')) && !$modal) {
                    $modal = true;
                }
            }
            if (validateArray($settings, 'onchange')) {
                $inputData['onchange'] = $settings['onchange'];
                if (strhas($settings['onchange'], 'modal') && !$modal) {
                    $modal = true;
                }
            }
            // *********************** Atributos dentro del input : <input class.. id.. > ****************
            if (validateArray($settings, 'subTable')) {
                $inputData['subTable'] = $settings['subTable'];
            }
            if (validateArray($settings, 'subView')) {
                $inputData['subView'] = $settings['subView'];
            }
            if (validateArray($settings, 'button')) {
                $settings['button']['id'] = $inputData['id'];
                $settings['button']['class'] = $inputData['class'];
                $inputData['button'] = $settings['button'];
                if (validateArray($settings['button'], 'onclick')) {
                    if ((strstr($settings['button']['onclick'], 'Modal') || strstr($settings['button']['onclick'], 'modal')) && !$modal) {
                        $modal = true;
                        $data["lcSecondInputFormType"] = 'button';
                        $data["printSecondItem"] = $this->load->view(["template_form_with_options" => "printSecondItem"], $data, true, true);
                    }
                }
            }
            if (validateArray($settings, 'table')) {
                $inputData['table'] = $settings['table'];
            }
            if (validateArray($settings, 'action')) {
                $inputData['action'] = $settings['action'];
            }
            if (validateArray($settings, 'content')) {
                $inputData['content'] = $settings['content'];
            }
            if (validateArray($settings, 'insertWith')) {
                $inputData['with'] = $settings['insertWith'];
            }
            if (validateArray($settings, 'helpText')) {
                $inputData['helpText'] .= $settings['helpText'];
            }
            if (validateArray($settings, 'insertEachOne')) {
                $aEachNames[] = $name;
            }
            if (validateArray($settings, 'data')) {
                foreach ($settings['data'] as $data_key => $data_value) {
                    $inputData["data-$data_key "] = $data_value;
                }
            }
            if (compareArrayStr($settings, 'input', 'disabled')) {
                $inputData['disabled'] = '';
            }
            if (compareArrayStr($settings, 'input', 'hidden')) {
                $inputData['class'] = 'display-none';
            }
            if (compareArrayStr($settings, 'input', 'button')) {
                $inputData['content'] = $name;
                $inputData['class'] .= "btn btn-primary btn-rounded btn-block ";
            }
            // ********************************************************************************************
            if (compareArrayStr($settings, 'type', 'text')) {
                $typeForm = 'textarea';
                $inputData["class"] .= "textTinymce ";
                $bIsTextArea = true;
            } else if (compareArrayStr($settings, 'type', 'varchar') || compareArrayStr($settings, 'type', 'longvarchar')) {
                if (validateArray($settings, 'password')) {
                    $typeForm = 'password';
                } else if (validateArray($settings, 'constraint')){
                    $constraint = intval($settings['constraint']);
                    if($constraint >= 500){
                        $typeForm = 'textarea';
                        $inputData["class"] .= "textTinymce ";
                        $bIsTextArea = true;
                    }
                } else if (compareArrayStr($settings, 'input', 'hidden')) {
                    $typeForm = 'hidden';
                    $inputData["class"] = 'display-none';
                } else if (compareArrayStr($settings,'input','image') || compareArrayStr($settings,'input','file')){
                    $typeForm = 'hidden';
                }
                if ($this->bInputHasOptions($settings)) {
                    list($typeForm, $inputData, $data) = $this->getInputType($settings, $inputData, $data);
                    if (!validateArray($settings, 'options')) {
                        $inputData['options'] = [];
                    }
                    if (validateArray($settings, 'options')) {
                        $inputData['options'] = $settings['options'];
                        $inputData['class'] .= inArray($typeForm,array_flip($aRdsChks),false) ? 'form-control ' : 'chosen-select ';
                    }
                }
            } else if (compareArrayStr($settings, 'type', 'int') || compareArrayStr($settings, 'type', 'decimal')) {
                if (compareArrayStr($settings, 'class', 'dial') && !validateArray($settings, 'idForeign')) {
                    $inputData['data-fgColor'] = !validateArray($settings, 'data') ? "#1AB394" : (validateArray($settings['data'], 'fgColor') ? $settings['data']['fgColor'] : "#1AB394");
                    $inputData['data-width'] = !validateArray($settings, 'data') ? "70" : (validateArray($settings['data'], 'width') ? $settings['data']['width'] : "70");
                    $inputData['data-height'] = !validateArray($settings, 'data') ? "70" : (validateArray($settings['data'], 'height') ? $settings['data']['height'] : "70");
                    $inputData['data-step'] = !validateArray($settings, 'data') ? "10" : (validateArray($settings['data'], 'step') ? $settings['data']['step'] : "10");
                    $inputData['class'] .= 'dial m-r-sm ';
                }
                if ($this->bInputHasOptions($settings)) {
                    list($typeForm, $inputData, $data) = $this->getInputType($settings, $inputData, $data);
                    if (validateArray($settings, 'options')) {
                        $inputData['options'] = $settings['options'];
                        $inputData['class'] = !inArray($typeForm,array_flip($aRdsChks),false) ? 'form-control ' : 'chosen-select ';
                    }
                } else {
                    $typeForm = 'number';
                }
            } else if (compareArrayStr($settings, 'type', 'date')) {
                $typeForm = 'input';
                $inputData["size"] = 16;
                $inputData["readonly"] = true;
            } else if (compareArrayStr($settings, 'type', 'datetime')) {
                $typeForm = 'input';
                $inputData["size"] = 16;
                $inputData["readonly"] = true;
//                $inputData["class"] .= "datepicker ";
            }

            list($data, $typeForm, $bIsForeing) = $this->validateFkTable($data, $fields, $settings, $sys, $typeForm,$tableName);
            $data['lcInputFormType'] = $typeForm;
            if(isset($settings['selectBy']) && in_array('selectBy',$settings)){
                if(is_array($settings['selectBy'])){
                    foreach ($settings['selectBy'] as $select){
                        if(strstr($select,'id_')){
                            $related = ucfirst(str_replace('id_','',$settings['idForeign']));
                            list($relatedS,$relatedP) = setSingularPlural($related);
                            $data['oRelatedFieldP'] = "\$o".$relatedP;
                            $data['relatetionsOption'] = $this->load->view(["template_form_with_options" => "relatetionsOption"], $data, true, true);
                        }
                    }
                }
            }
            if (compareArrayStr($settings, 'input', 'hidden')) {
                $inputData['class'] = 'display-none';
                $data['UcInputLabel'] = '';
            } else {
                $data['UcInputLabel'] = validateArray($settings, 'label') ? $settings['label'] : setLabel($name,true);
            }
            if (compareArrayStr($settings, 'options', 'db_tabs')) {
                $data['objOptions'] = '$aDBTables';
            }
            if (compareArrayStr($settings, 'options', 'db_tab_ref')) {
                $data['objOptions'] = '$aDBTableRef';
            }
            if (compareArrayStr($settings, 'options', 'db_tabs_fields')) {
                $data['objOptions'] = '$aDBTableFields';
            }

            $data['inputData'] = var_export($inputData, true);
            if (validateArray($settings, 'password') || compareArrayStr($settings, 'input', 'password')) {
                $data['lcTableId'] = $data['pkTableVar'];
                $data['lcInputPassConfId'] = "fieldConfirm" . ucfirst($name);
                $data['UcInputPassConfLabel'] = "Confirmar " . ucfirst($name);
                $data['UcInputPassConfPlaceholder'] = "Confirmar contraseña";
                $htmlFormContent .= $this->load->view("template_form_password", $data, true, true);
            } else if (compareArrayStr($settings, 'input', 'image') || compareArrayStr($settings, 'input', 'file')) {
                $htmlFormContent .= $this->load->view("template_form_img", $data, true, true);
            } else if (compareArrayStr($settings, 'input', 'button')) {
                $htmlFormContent .= $this->load->view("template_form_button", $data, true, true);
            } else if (compareArrayStr($settings, 'input', 'datetime')) {
                $htmlFormContent .= $this->load->view("template_form_datetime", $data, true, true);
            } else if (compareArrayStr($settings, 'input', 'date')) {
                $htmlFormContent .= $this->load->view("template_form_date", $data, true, true);
            } else if (validateArray($settings, 'options') || $bIsForeing) {
                $htmlFormContent .= $this->load->view("template_form_with_options", $data, true, true, true);
            } else if($bIsTextArea){
                $htmlFormContent .= $this->load->view("template_form_textarea", $data, true, true, true);
                $bIsTextArea = false;
            } else {
                $htmlFormContent .= $this->load->view("template_form_default", $data, true, true);
            }
            if ($modal) {
                $modal = false;
                $modalsContent .= "
            <?=modal('" . $inputData['id'] . "Modal')?>";
            }
            unset($data["printSecondItem"]);
            unset($data['relatetionsOption']);
        }
        return [$htmlFormContent, $aEachNames, $modalsContent];
    }

    private function bInputHasOptions($settings)
    {
        if (compareArrayStr($settings, 'input', 'radio') ||
            compareArrayStr($settings, 'input', 'radios') ||
            compareArrayStr($settings, 'input', 'checkbox') ||
            compareArrayStr($settings, 'input', 'checkboxes') ||
            compareArrayStr($settings, 'input', 'select') ||
            compareArrayStr($settings, 'input', 'dropdown') ||
            compareArrayStr($settings, 'input', 'multiselect') ||
            validateArray($settings, 'options') ||
            validateArray($settings, 'idForeign')
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function getInputType($settings, $inputData, $data)
    {

        $formType = 'default';
        if ($this->inputRadios($settings)) {
            $inputData['class'] .= 'i-checks ';
            $formType = 'radios';
        } else if ($this->inputRadio($settings)) {
            $inputData['class'] .= 'i-checks ';
            $formType = 'radio';
        }else if ($this->inputCheckboxes($settings)) {
            $inputData['name'] = $inputData['name'] . '[]';
            $inputData['class'] .= 'i-checks ';
            $formType = 'checkboxes';
            $data['lcErrorForField'] .= '[]';
        } else if ($this->inputCheckbox($settings)) {
            $formType = 'checkbox';
            $inputData['class'] .= 'i-checks ';
            $inputData['name'] .= '[]';
            $data['lcErrorForField'] .= '[]';
        } else if ($this->inputMultiselect($settings)) {
            $inputData['name'] .= '[]';
            $inputData['multiple'] = '';
            $inputData['class'] = 'chosen-select ';
            $formType = 'multiselect';
            $data['lcErrorForField'] .= '[]';
        } else if ($this->inputSelect($settings)) {
            $inputData['class'] = 'chosen-select ';
            $formType = 'select';
        } else if ($this->inputDropdown($settings)) {
            $formType = 'dropdown';
            $inputData['class'] = 'chosen-select ';
        } else if ($this->inputStatic($settings)) {
            $formType = 'static';
        } else {
            $formType = 'default';
        }
        return [$formType, $inputData, $data];
    }

    private function inputRadio($settings)
    {
        return compareArrayStr($settings, 'input', 'radio');
    }

    private function inputRadios($settings)
    {
        return compareArrayStr($settings, 'input', 'radios');
    }

    private function inputCheckbox($settings)
    {
        return compareArrayStr($settings, 'input', 'checkbox');
    }

    private function inputCheckboxes($settings)
    {
        return compareArrayStr($settings, 'input', 'checkboxes');
    }

    private function inputSelect($settings)
    {
        return compareArrayStr($settings, 'input', 'select') || validateArray($settings, 'idForeign');
    }

    private function inputMultiselect($settings)
    {
        return compareArrayStr($settings, 'input', 'multiselect');
    }

    private function inputDropdown($settings)
    {
        return compareArrayStr($settings, 'input', 'dropdown');
    }

    private function inputStatic($settings)
    {
        return compareArrayStr($settings, 'input', 'static');
    }

    private function validateFkTable($data, $fields, $settings, $sys, $typeForm = null, $aMixDbPkFk = [], $tableName = '')
    {
        $bIsForeing = false;
        if (validateArray($settings, 'idLocal') || validateArray($settings, 'field')) {
            $idLocal = isset($settings['idLocal']) ? $settings['idLocal'] : $settings['field'];
            //TODO: cuando es un array en selectBy, verificar que cuando se apunta a una columna id de otra tabla se extraiga las columnas del selectBy de dicha columna referenciada
            if(!validateArray($fields,$idLocal)){
                $settingsTable = $settings['tabName'];
                $fields = $this->{"table_$settingsTable"};
            }
            if(validateArray($fields,$idLocal)){

                if(validateArray($fields[$idLocal], 'idForeign')){

                    list($fields,$fkTableName,$fkTableFields,$vFkTableFieldRef,$vFkTableFieldRefArray) = $this->FK_get_SelectByFieldsFromFkField($fields,$idLocal,$tableName);
//                if(in_array($vFkTableFieldRef,$aMixDbPkFk)){
//                    list($fkTableName,$fkTableFields,$vFkTableFieldRef,$vFkTableFieldRefArray) = $this->getSelectByFieldsFromFkField($fkTableFields,$vFkTableFieldRef);
//                }
                    if(!isset($fields[$idLocal]['selectBy'])){
                        show_error("El Campo $idLocal de la table $tableName hace refencia a una tabla foranea , se debe establecer mediante los parametros json a que campo hara referencia en la otra table que no sea el id de la misma");
                    } else {
                        $originFkTableFieldRef = $fields[$idLocal]['selectBy'];
                        list($data, $typeForm, $bIsForeing, $fkTableFieldRefSettings) = $this->FK_set_DataForSelectingFields($fkTableName, $fkTableFields, $vFkTableFieldRef, $originFkTableFieldRef, $vFkTableFieldRefArray, $settings, $tableName, $idLocal, $data, $sys);

                        if (!validateArray($fields[$idLocal], 'selectBy')) {
                            $data[]['selectBy'] = compareArrayStr($fkTableFieldRefSettings,'selectBy','nombre') ? $fkTableFieldRefSettings['selectBy'] : '';
//                    if (validateArray($fkTableFields, $vFkTableFieldRef)) {
//                        $fkTableFieldRefSettings = $fkTableFields[$vFkTableFieldRef];
//                    } else {
//                        show_error("El parametro $vFkTableFieldRef no existe en la tabla $fkTableName, revisa los parametros json de la tabla: " . $data["tableName"] . '.');
//                    }
                            $data['objOptions'] = '$data["options"]';
                            $bIsForeing = false;
                        }
                    }
                } else if(validateArray($fields[$idLocal], 'options')){
                    $data['objOptions'] = '$data["options"]';
                    $bIsForeing = false;
                }
            }
        } else {
            $data['objOptions'] = '$data["options"]';
            $bIsForeing = false;
        }
        return [$data, $typeForm, $bIsForeing];
    }

    private function getTableRelations($fields, $bUnique = false, $bUniqueFilters = false, $bWithOutExcepts = FALSE)
    {
        $tab_titles = config_item('tab_titles');
        $column = 'table';
        $excepts = array_merge(config_item('controlFields'));
        $aDuplicated = array();
        $aUniqueRelations = array();
        initStaticTableVars($this);

        foreach ($fields as $name => $settings) {
            if (validateArray($settings, 'filterBy')) {
                if ($bUniqueFilters && validateArray($settings, 'idForeign') && validateArray($settings, 'table')) {
                    $aDuplicated[] = $settings['table'];
                }
                $filterByKeys = array_keys($settings['filterBy']);

                foreach ($filterByKeys as $filterBy) {
                    if ($this->dbforge->getTableNameByIdTable($filterBy)) {
                        $tableFilterByFields = "table_" . $settings['table'];
                        $aUniqueRelations[$filterBy] = array(
                            'table' => $this->dbforge->getTableNameByIdTable($filterBy),
                            'selectBy' => validateArray($this->$tableFilterByFields, $filterBy) ? (validateArray($this->$tableFilterByFields[$filterBy], 'selectBy') ? $this->$tableFilterByFields[$filterBy]['selectBy'] : []) : [],
                            'filterBy' => validateArray($this->$tableFilterByFields, $filterBy) ? (validateArray($this->$tableFilterByFields[$filterBy], 'filterBy') ? $this->$tableFilterByFields[$filterBy]['filterBy'] : []) : [],
                            'idForeign' => $filterBy,
                            'field' => $name
                        );
                    }
                }
            }
        }
        $PkTables = $this->dbforge->getPrimaryKeysOfTables(false,false);
        if ($bUnique) {
            $aTableNames = array_unique(array_column($fields, $column));
        } else {
            $aTableNames = array_column($fields, 'table');
        }
        if ($bWithOutExcepts) {
            foreach ($fields as $name => $field) {
                if (in_array($name, $excepts) && $name != 'id_user_modified' && $name != 'id_user_created') {
                    unset($fields[$name]);
                }
            }
        }

        $aTableNames = array_merge($aTableNames, $aDuplicated);
        foreach ($aTableNames as $tableName) {
            foreach ($fields as $fkName => $settings) {
                if (validateArray($settings, 'table') && validateArray($settings, 'idForeign')) {
                    if ($tableName == $settings['table']) {
                        if(validateArray($settings, 'selectBy')) {
                            if(in_array($settings['selectBy'], $PkTables)){
                                $idFromSelectBy = $settings['selectBy'];
                                $pkTablesFlipped = array_flip($PkTables);
                                $tableNameFromSelectByRef = $pkTablesFlipped[$idFromSelectBy];

                                $aUniqueRelations[$idFromSelectBy] = $this->{"table_$tableName"}[$idFromSelectBy];

                                $vSelectBy = validateArray($aUniqueRelations[$idFromSelectBy], 'selectBy') ? $aUniqueRelations[$idFromSelectBy]['selectBy'] : '';
                                if(validateVar($vSelectBy,'array')){
                                    $vTestSelectBy = $vSelectBy[0];
                                } else {
                                    $vTestSelectBy = $vSelectBy;
                                }

                                $fkTableFields = $this->{"table_$tableNameFromSelectByRef"};
                                if (!validateArray($fkTableFields, $vTestSelectBy) && in_array($vTestSelectBy, $tab_titles)){
                                    unset($aUniqueRelations[$idFromSelectBy]['selectBy']);
                                    foreach ($tab_titles as $title){
                                        if(validateArray($fkTableFields, $title)){
                                            $aUniqueRelations[$idFromSelectBy]['selectBy'][] = $title;
                                        }
                                    }
                                }
                                unset($settings['selectBy']);
                            }
                        }
                        $aUniqueRelations[$fkName] = $settings;
                        unset($fields[$fkName]);
                        break;
                    }
                }
            }
        }
        return $aUniqueRelations;
    }

    public function setHtmlHeaderTitles($fields, $validFields, $tableSettings)
    {
        $content = "";
        list($vFields, $numFields) = $this->getValidatedFieldsWithTableSettings($fields, $validFields, $tableSettings);
        $numFields = $numFields == 0 ? 5 : $numFields;
        foreach ($vFields as $name => $settings) {
            $inputLabel = validateArray($settings, 'label') ? $settings['label'] : setLabel($name,true);
            $inputLabel = ucfirst($inputLabel);
            $content .= "<th>$inputLabel</th>
                ";
            if ($numFields) {
                $numFields--;
            } else {
                break;
            }
        }
        if (!validateArray($tableSettings, 'no_date_created') && validateArray($fields, 'date_created')) {
            $content .= "<th>" . $fields['date_created']["label"] . "</th>
            ";
        }
        return $content;
    }

    private function setHtmlBodyFields($fields, $validFields, $tableSettings, $subModS, $subModP)
    {
        $oUcTableS = 'o' . ucfirst($subModS);
        $oUcObjTableS = 'o' . ucfirst(setObject($subModS));
        $content = "";
        $aPksOfTables = $this->dbforge->getPrimaryKeysOfTables();
        $aFksOfTables = $this->dbforge->getForeignKeyOfTables();
        $aPKorFKofTables = array_merge($aPksOfTables, $aFksOfTables);
        $aIdsPkOrFk = array_keys($aPKorFKofTables);
//        $aTablesPkOrFk = array_values($aPKorFKofTables);
//        $aFlipedPksOfTables = array_flip($aPKorFKofTables);
        list($vFields, $numFields) = $this->getValidatedFieldsWithTableSettings($fields, $validFields, $tableSettings);
        $numFields = $numFields == 0 ? 5 : $numFields;
        foreach ($vFields as $name => $settings) {
            if (compareArrayStr($settings, 'input', 'image') || compareArrayStr($settings, 'input', 'img') || compareArrayStr($settings, 'input', 'file')) {
                $content .= "<td><?= img('assets/img/$subModP/thumbs/'.\$$oUcObjTableS->$name" . "_thumb1); ?></td>               
                ";
            } else {
                $aFieldsSelectBy = validateArray($settings, 'selectBy') ? $settings['selectBy'] : [];
                $aFieldsToJoin = $this->analizeFieldsSelectBy($aFieldsSelectBy, $aPKorFKofTables, $aIdsPkOrFk);
                if (isString($aFieldsToJoin) && in_array($name, $aPksOfTables)) {
                    $content .= "<td><?= \$$oUcObjTableS->".'get'.ucfirst(setObject($name)).ucfirst(setObject($aFieldsToJoin))."(); ?></td>               
                ";
                } else if (isArray($aFieldsToJoin)) {
                    $html = '';
                    foreach ($aFieldsToJoin as $fieldToJoin) {
                        $html .= "\$$oUcObjTableS->".'get'.ucfirst(setObject($name)).ucfirst(setObject($fieldToJoin)).'() ." ".';
                    }
                    $html = substr($html, 0, strlen($html) - 6);
                    $content .= "<td><?= $html; ?></td>               
                                ";
                } else {
                    $content .= "<td><?= \$$oUcObjTableS->".'get'.ucfirst(setObject($name))."(); ?></td>               
                    ";
                }
            }
            if ($numFields) {
                $numFields--;
            } else {
                break;
            }
        }
        if (!validateArray($tableSettings, 'no_date_created') && validateArray($fields, 'date_created')) {
            $content .= "<td><?= \$$oUcObjTableS->" . "getDateCreated(); ?></td>
            ";
        }
        return $content;
    }

    private function getValidatedFieldsWithTableSettings($fields, $validFields, $tableSettings)
    {
        $numIndexFields = 0;
        $numExtra = 0;
        $indexFields = array();
        $except = array();

        if (validateArray($tableSettings, 'indexFields')) {
            $indexFields = $tableSettings['indexFields'];
        }
        if (validateArray($tableSettings, 'numListed')) {
            $numIndexFields = $tableSettings['numListed'];
        } else {
            $numIndexFields = 5;
        }

        if (validateVar($indexFields, 'array') && validateVar($numIndexFields, 'numeric')) {
            $num = count($indexFields);
            if ($numIndexFields > $num) {
                $except = $indexFields;
            } else {
                $except = array_splice($indexFields, $num - $numIndexFields);
            }
        } else if (validateVar($indexFields, 'array')) {
            $numIndexFields = count($indexFields);
        }
        $aAllNamesFields = array_keys($fields);
        $aNamesValidFields = array_keys($validFields);
        $aNamesDiffAllFiedsExcepts = array_diff($aAllNamesFields, $except);
        $aNamesInteExceptValid = array_intersect($aNamesDiffAllFiedsExcepts, $aNamesValidFields);
        $aNamesVerifiedFields = array_merge($except, $aNamesInteExceptValid);
        $vFields = array();
        foreach ($aNamesVerifiedFields as $fieldName) {
            if ($numIndexFields > 0) {
                $vFields[$fieldName] = validateArray($fields, $fieldName) ? $fields[$fieldName] : null;
                $numIndexFields--;
            }
        }

        return [$vFields, $numIndexFields];
    }

//    private function getPhpFieldsProperties($fields, $data)
//    {
//        $content = "";
//        $data["packGettersFunctions"] = "";
//        foreach ($fields as $name => $field) {
//            $type =
//                compareStrStr($field['type'], 'datetime') ||
//                compareStrStr($field['type'], 'date') ||
//                compareStrStr($field['type'], 'text') ||
//                compareStrStr($field['type'], 'varchar') ? "string" :
//                    (compareStrStr($field['type'], 'int') ? "int" : "");
//            $content .= "
//             /**
//                * The value for the $name field.
//                *
//                * @var        $type
//                */
//             public $$name;
//        ";
//            $data['UcObjField'] = ucfirst(setObject($name));
//            $data['lcField'] = lcfirst(setObject($name));
//            $data["packGettersFunctions"] .= $this->load->view(["template_ES_Model" => "packGettersFunctions"], $data, true, true, true);
//        }
//        $data["fieldsProperties"] = $content;
//        return $data;
//    }

    private function getPhpFieldsRules($fields, $pkTable, $data)
    {
        $rules = array();
        $rulesWithPass = array();

        $excepts = array_merge(config_item('controlFields'), [$pkTable]);

        $phpGlobalVars = '';
        $data["localPackForGetData"] = '';
        $data["localPackForToArray"] = '';
        $data["foreignPackForGetData"] = '';
        $data["foreignPackForToArray"] = '';
        $data["packFindOneByFunctions"] = '';
        $data["packFilterByFunctions"] = '';
        $data["packSelectByFunctions"] = '';
        $data["packGettersFunctions"] = '';
        $data["packSettersFunctions"] = '';
        $data["globalLocalFieldsVars"]  = '';
        $data["globalStaticFieldName"]  = '';
        $data["globalStaticLocalVars"]  = '';
        $data["globalLocalWithForeignFieldsVars"] = '';
        $data["packLocalForeignGettersFunctions"] = '';

        $aStaticVars = [];
        foreach ($fields as $name => $settings) {
            if(arrayHas($settings,'options')) {
                if(isArray($settings['options'])){
                    foreach ($settings['options'] as $option){
                        if(!in_array($option,$aStaticVars)){
                            $data['lcVarStaticOption'] = $option;
                            $data['lcObjStaticOption'] = ucfirst(setObject('$opt'.ucfirst($option),false));
                            $data["globalStaticLocalVars"] .= $this->load->view(["template_ES_Model" => "globalStaticLocalVars"], $data, true, true, true);
                            $aStaticVars[] = $option;
                        }
                    }
                }
            }
            $data['lcVarStaticFieldName'] = $settings['field'];
            $data['lcObjStaticFieldName'] = '$field'.ucfirst(setObject($settings['field'], false));
            $data["globalStaticFieldName"] .= $this->load->view(["template_ES_Model" => "globalStaticFieldName"], $data, true, true, true);

            // ========================================= inicio - getPhpFieldsProperties ======================================
            if(compareStrStr($settings['type'], 'datetime') ||
                compareStrStr($settings['type'], 'date') ||
                compareStrStr($settings['type'], 'text') ||
                compareStrStr($settings['type'], 'varchar')){
                $type = "string";
                $data['defaultDataVal'] = "''";
            } else if(compareArrayStr($settings,'type', 'int')){
                $type = "int";
                if(inArray('idForeign',$settings) || compareArrayBool($settings,'auto_increment',true)){
                    $data['defaultDataVal'] = 'null';
                } else {
                    $data['defaultDataVal'] = 0;
                }
            } else {
                $type = "";
                $data['defaultDataVal'] = "''";
            }

            // ------------------- Setting Getters and Setters ----------------------
            $data['UcObjField'] = ucfirst(setObject($name));
            $data['lcField'] = lcfirst($name);
            $data['lcLocalField'] = lcfirst($name);
            $data['$lcField'] = '$'.lcfirst($name);
            $data['lcObjField'] = '$'.lcfirst(setObject($name));
            $data["packGettersFunctions"] .= $this->load->view(["template_ES_Model" => "packGettersFunctions"], $data, true, true, true);
            $data["localPackForGetData"] .= $this->load->view(["template_ES_Model" => "localPackForGetData"], $data, true, true, true);
            $data["localPackForToArray"] .= $this->load->view(["template_ES_Model" => "localPackForToArray"], $data, true, true, true);
            $data["packSettersFunctions"] .= $this->load->view(["template_ES_Model" => "packSettersFunctions"], $data, true, true, true);
            $data["packFindOneByFunctions"] .= $this->load->view(["template_ES_Model" => "packFindOneByFunctions"], $data, true, true, true);
            $data["packFilterByFunctions"] .= $this->load->view(["template_ES_Model" => "packFilterByFunctions"], $data, true, true, true);
//            $data["packSelectByFunctions"] .= $this->load->view(["template_ES_Model" => "packSelectByFunctions"], $data, true, true, true);
            // ----------------------------------------------------------------------

            // --------------------- setting Global Vars ---------------------------------------
            $data['lcVarLocalField'] = '$'.lcfirst($name);
            $data['lcLocalField'] = lcfirst($name);
            $data['lcObjLocalField'] = '$'.lcfirst(setObject($name));
            $data['UcObjLocalField'] = ucfirst(setObject($name));
            $data['lcField'] = lcfirst($name);
            $data['dataType'] = $type;
            $data["globalLocalFieldsVars"] .= $this->load->view(["template_ES_Model" => "globalLocalFieldsVars"], $data, true, true, true);
            if(validateArray($settings,'idForeign') && validateArray($settings,'selectBy')){
                $data['dataType'] = $type;
                if(validateVar($settings['selectBy'],'array')){
                    foreach ($settings['selectBy'] as $nameSelect){
                        $data['lcVarForeignField'] = '$'.lcfirst($nameSelect);
                        $data['lcForeignField'] = lcfirst($nameSelect);
                        $data['UcObjForeignField'] = ucfirst(setObject($nameSelect));
                        $data['lcObjForeignField'] = lcfirst(setObject($nameSelect));
                        $data["globalLocalWithForeignFieldsVars"] .= $this->load->view(["template_ES_Model" => "globalLocalWithForeignFieldsVars"], $data, true, true, true);
                        $data['UcObjField'] = ucfirst(setObject($name.'_'.ucfirst($nameSelect)));
                        $data['lcField'] = lcfirst($nameSelect);
                        $data['lcObjField'] = lcfirst(setObject($nameSelect));
                        $data["packLocalForeignGettersFunctions"] .= $this->load->view(["template_ES_Model" => "packLocalForeignGettersFunctions"], $data, true, true, true);
                        $data["foreignPackForGetData"] .= $this->load->view(["template_ES_Model" => "foreignPackForGetData"], $data, true, true, true);
                        $data["foreignPackForToArray"] .= $this->load->view(["template_ES_Model" => "foreignPackForToArray"], $data, true, true, true);
                    }
                } else if(validateVar($settings['selectBy'])){
                    $data['lcForeignField'] = lcfirst($settings['selectBy']);
                    $data['lcObjForeignField'] = lcfirst(setObject($settings['selectBy']));
                    $data['UcObjForeignField'] = ucfirst(setObject($settings['selectBy']));
                    $data["globalLocalWithForeignFieldsVars"] .= $this->load->view(["template_ES_Model" => "globalLocalWithForeignFieldsVars"], $data, true, true, true);
                    $data['UcObjField'] = ucfirst(setObject($name.'_'.ucfirst($settings['selectBy'])));
                    $data['lcField'] = lcfirst($settings['selectBy']);
                    $data['lcObjField'] = lcfirst(setObject($settings['selectBy']));
                    $data["foreignPackForGetData"] .= $this->load->view(["template_ES_Model" => "foreignPackForGetData"], $data, true, true, true);
                    $data["foreignPackForToArray"] .= $this->load->view(["template_ES_Model" => "foreignPackForToArray"], $data, true, true, true);
//                    $data["packGettersFunctions"] .= $this->load->view(["template_ES_Model" => "packGettersFunctions"], $data, true, true, true);
                }
            } else {
                $data["globalLocalWithForeignFieldsVars"] .= '';
            }
            // ----------------------------------------------------------------------------

            // ======================================= fin - getPhpFieldsProperties ===========================================

            if ($this->bInputHasOptions($settings) && ($this->inputMultiselect($settings) || $this->inputCheckboxes($settings))) {
                $name .= '[]';
            }
            if (!in_array($name, $excepts)) {
                if (strhas($name, 'password')) {
                    $rulesWithPass[$name] = array(
                        "password" => array(
                            "field" => lcfirst(setObject($name)),
                            "label" => validateArray($settings, 'label') ? $settings['label'] : setLabel($name, true),
                            "rules" => $this->getRulesByField($settings),),
                        "password_confirm" => array(
                            "field" => lcfirst(setObject("password_confirm")),
                            "label" => setLabel("password_confirm",true),
                            "rules" => "trim|matches[$name]",
                        ),
                    );
                } else {
                    if (!compareArrayStr($settings, 'input', 'image') || !compareArrayStr($settings, 'input', 'file') && !compareArrayStr($settings, 'input', 'password')) {
                        $rules[$name] = array(
                            "field" => lcfirst(setObject($name)),
                            "label" => validateArray($settings, 'label') ? $settings['label'] : setLabel($name,true),
                            "rules" => $this->getRulesByField($settings)
                        );
                        $rulesWithPass[$name] = array(
                            "field" => lcfirst(setObject($name)),
                            "label" => validateArray($settings, 'label') ? $settings['label'] : setLabel($name,true),
                            "rules" => $this->getRulesByField($settings)
                        );
                    }
                }
            }
        }
        $data["tableRules"] = var_export($rulesWithPass, true);
        $data["tableRulesEdit"] = var_export($rules, true);
        $data["tableRulesEditView"] = var_export($rulesWithPass, true);

        return $data;
    }

    private function getPhpStdFields($tableName, $pkTable, $data)
    {
        list($modSign, $submod) = getModSubMod($tableName);
        list($subModS, $subModP) = setSingularPlural($submod);
        $result = $this->dbforge->getTableFields($tableName);
        $object = new stdClass();
        $content = "";
        foreach ($result as $field) {
            $columnName = $field->COLUMN_NAME;
            if ($columnName == $pkTable) {
                $content .= "\$this->$subModS->$columnName = '';
            ";
            } else {
                $content .= "\$this->$subModS->$columnName = '';
            ";
            }
        }
        $data["stdFields"] = $content;
        return $data;
    }

    private function getRulesByField($field)
    {
        $rules = "trim|";
        if (validateArray($field, 'constraint')) {
            $size = $field['constraint'];
            $rules .= "max_length[$size]|";
        }
        if (compareArrayStr($field, 'input', 'password')) {
            $rules .= "matches[password_confirm]|";
        }
        if (validateArray($field, 'validate')) {
            if (validateVar($field['validate'], 'array')) {
                foreach ($field['validate'] as $rule) {
                    if (compareStrStr($rule, 'email')) {
                        $rule = 'valid_email';
                    } else if (compareStrStr($rule, 'phone') || compareStrStr($rule, 'mobile') || compareStrStr($rule, 'number') || compareStrStr($rule, 'num')) {
                        $rule = 'numeric';
                    } else if (compareStrStr($rule, 'url') || compareStrStr($rule, 'link') || compareStrStr($rule, 'website')) {
                        $rule = 'valid_url';
                    } else if (compareStrStr($rule, 'ip') || compareStrStr($rule, 'ip_address')) {
                        $rule = 'valid_ip';
                    }
                }
            } else if (validateVar($field['validate'], 'string')) {
                $rules .= $field['validate'] . "|";
            }
        }
        $rules = substr($rules, 0, strlen($rules) - 1);
        return $rules;
    }

    private function getExtraFunctions($tableName)
    {
        $content = "";
        if ($tableName == 'es_sessions') {
            $content .= '
            public function login(){
                $this->session->login();
            }
            public function logout(){
                $this->session->logout();
            }
            public function signup(){
                $this->session->signUp();
            }
            public function forgot_password(){
                $this->session->forgotPassword();
            }
            ';
            return $content;
        }
    }

    protected function _update_primary_key($field, $fields, $tableLocal, $auto_increment = false)
    {
        $fields_cols = array_column($fields, 'name');
        if (explode('_', $field)[0] == 'id') {
            $id_table = '';
            foreach ($fields_cols as $item) {
                if (explode('_', $item)[0] == 'id') {
                    list($id, $table) = explode('_', $item);
                    if (strpos($tableLocal, $table) > -1) {
                        $id_table = $item;
                        break;
                    }
                }
            }
            if ($id_table == '') {
                list($id, $table) = explode('_', $field);
                if (strpos($tableLocal, $table) > -1) {
                    $id_table = $field;
                }
            }
            if ($id_table == '') {
                $this->dbforge->setPrimaryKey($tableLocal, $field, $auto_increment);
            }
        }
    }

    private function verifyAppOrBase()
    {
        if (isset($this->CI->uri->segments[3])) {
            $this->_base_path = $this->CI->uri->segments[3] == 'es' || $this->CI->uri->segments[3] == 'tic' ? BASEPATH : APPPATH;
            $this->_dir_migrations_files = $this->CI->uri->segments[3] == 'es' || $this->CI->uri->segments[3] == 'tic' ? BASEPATH . "migrations/tables/" : APPPATH . "migrations/tables/";
            $this->_dir_root_store = $this->CI->uri->segments[3] == 'es' || $this->CI->uri->segments[3] == 'tic' ? BASEPATH . "migrations/storage/" : APPPATH . "migrations/storage/";
        }
    }

    public function createViewFiles($tableName, $pkTable, $fields, $settings = [], $defaultData = [])
    {
        $this->createViewIndex($tableName, $pkTable, $fields, $settings, $defaultData);
        $this->createViewEdit($tableName, $pkTable, $fields, $settings, $defaultData);

    }

    private function analizeFieldsSelectBy($aFieldsSelectBy, $aPKorFKofTables, $aIdsPkOrFk)
    {
        $aFieldsToJoin = [];
        if (validateVar($aFieldsSelectBy)) {
            if (in_array($aFieldsSelectBy, $aIdsPkOrFk)) {
                $selectByTableName = $aPKorFKofTables[$aFieldsSelectBy];
                $aFieldsToJoin = validateArray($this->{"table_$selectByTableName"}[$aFieldsSelectBy], 'selectBy') ? $this->{"table_$selectByTableName"}[$aFieldsSelectBy]['selectBy'] : $aFieldsSelectBy;
            } else {
                $aFieldsToJoin = $aFieldsSelectBy;
            }
            return $aFieldsToJoin;
        } else if (validateVar($aFieldsSelectBy, 'array')) {
            foreach ($aFieldsSelectBy as $key => $fieldSelectBy) {
                if (in_array($fieldSelectBy, $aIdsPkOrFk)) {
                    $selectByTableName = $aPKorFKofTables[$fieldSelectBy];
                    $aFieldsToJoin = $this->{"table_$selectByTableName"}[$fieldSelectBy]['selectBy'];
                    break;
                } else {
                    $aFieldsToJoin[] = $fieldSelectBy;
                }
            }
            return $aFieldsToJoin;
        } else {
            return '';
        }
    }

    private function getEditViewsFromTableSettings($tableSettings, $fields, $allFields, $excepts)
    {
        $vIdFieldForViews = null;
        $vFieldsViews = array();
        if (validateVar($tableSettings, 'array')) {
            foreach ($tableSettings as $key => $settings) {
                if (validateVar($settings, 'array')) {
                    foreach ($settings as $editName => $ditFields) {
                        if (validateVar($editName) && strhas($editName, 'edit-')) {
                            $vIdFieldForViews = $key;
                            if (strhas($editName, 'ini')) {
                                $fieldsViews = $ditFields;
                                foreach ($fields as $name => $editFieldSettings) {
                                    if ((in_array($name, $fieldsViews) || validateArray($editFieldSettings, 'idForeign')) && !in_array($name, $excepts)) {
                                        $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                    }
                                }
                            } else {
                                $fieldsViews = $ditFields;
                                foreach ($allFields as $name) {
                                    if (in_array($name, $fieldsViews)) {
                                        $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                    }
                                }
                            }
                        }
                    }
                } else if (validateVar($settings)) {
                    $editName = $key;
                    $ditFields = $settings;
                    if (validateVar($editName) && strhas($editName, 'edit-')) {
                        $vIdFieldForViews = $key;
                        if (strhas($editName, 'ini')) {
                            $fieldsViews = $ditFields;
                            foreach ($fields as $name => $editFieldSettings) {
                                if ((in_array($name, $fieldsViews) || validateArray($editFieldSettings, 'idForeign')) && !in_array($name, $excepts)) {
                                    $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                }
                            }
                        } else {
                            $fieldsViews = $ditFields;
                            foreach ($allFields as $name) {
                                if (in_array($name, $fieldsViews)) {
                                    $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                }
                            }
                        }
                    }
                }
            }
        }
        return [$vIdFieldForViews, $vFieldsViews];
    }

    /**
     * @var CiSettings $sysSetting
     * @var CiOptions $sysOption
     */
    private function getEditViews($idMigTable, $fields, $allFields, $excepts)
    {
        $vIdFieldForViews = null;
        $vFieldsViews = array();
        $aEditViews = array();
        $sysSettings = class_exists('CiSettingsQuery') ? CiSettingsQuery::create()->filterByIdTabla($idMigTable)->find() : [];
        foreach ($sysSettings as $sysSetting) {
            $idSetting = $sysSetting->getIdSetting();
            $aSysOptions = CiOptionsQuery::create()->filterByIdSetting($idSetting)->find();
            if (validateVar($aSysOptions->toArray(), 'array')) {
                foreach ($aSysOptions as $sysOption) {
                    $fieldsEditView = array_diff(str2array($sysSetting->getFields()),$excepts);
                    $aEditViews[$sysSetting->getIdField()][$sysOption->getEditTag()] = $fieldsEditView;
                }
            }
        }

        if (validateVar($aEditViews, 'array')) {
            foreach ($aEditViews as $key => $settings) {
                if (validateVar($settings, 'array')) {
                    foreach ($settings as $editName => $ditFields) {
                        if (validateVar($editName) && strhas($editName, 'edit-')) {
                            $vIdFieldForViews = $key;
                            if (strhas($editName, 'ini')) {
                                $fieldsViews = $ditFields;
                                foreach ($fields as $name => $editFieldSettings) {
                                    if ((in_array($name, $fieldsViews) || validateArray($editFieldSettings, 'idForeign')) && !in_array($name, $excepts)) {
                                        $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                    }
                                }
                            } else {
                                $fieldsViews = $ditFields;
                                foreach ($allFields as $name) {
                                    if (in_array($name, $fieldsViews)) {
                                        $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                    }
                                }
                            }
                        }
                    }
                } else if (validateVar($settings)) {
                    $editName = $key;
                    $ditFields = $settings;
                    if (validateVar($editName) && strhas($editName, 'edit-')) {
                        $vIdFieldForViews = $key;
                        if (strhas($editName, 'ini')) {
                            $fieldsViews = $ditFields;
                            foreach ($fields as $name => $editFieldSettings) {
                                if ((in_array($name, $fieldsViews) || validateArray($editFieldSettings, 'idForeign')) && !in_array($name, $excepts)) {
                                    $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                }
                            }
                        } else {
                            $fieldsViews = $ditFields;
                            foreach ($allFields as $name) {
                                if (in_array($name, $fieldsViews)) {
                                    $vFieldsViews[$key][$editName][$name] = $fields[$name];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $vFieldsViews;
    }

    private function FK_get_SelectByFieldsFromFkField($fields,$idLocal,$tableName)
    {
        $tab_titles = config_item('tab_titles');
        $vFkTableFieldRefArray = array();
        $fkTableName = '';
        $fkTableIdName = '';
        $fkTableFields = array();
        $vFkTableFieldRef = '';
        $fkTableFieldRef = '';
        if (validateArray($fields[$idLocal],'idForeign')) {

            if (validateArray($fields[$idLocal], 'table')) {
                $fkTableName = $fields[$idLocal]['table'];
                $fkTableIdName = $fields[$idLocal]['idForeign'];
            } else {
                show_error('No se pudo encontrar la referencia selectBy, revisa las llaves foraneas: ' . $idLocal);
            }

            $fkTableFields = $this->dbforge->getArrayFieldsFromTable($fkTableName);

            if(validateArray($fields[$idLocal],'selectBy')) {
                $fkTableFieldRef = $fields[$idLocal]['selectBy'];
//                $fkTableFields = $fields[$idLocal]['selectBy'];
            } else if(validateArray($fkTableFields[$fkTableIdName],'selectBy')){
                $fkTableFieldRef = $fkTableFields[$fkTableIdName]['selectBy'];
                $fields[$idLocal]['selectBy'] = $fkTableFieldRef;
            } else {
                foreach ($tab_titles as $title){
                    if(validateArray($fkTableFields, $title)){
                        $fkTableFields[$fkTableIdName]['selectBy'][] = $title;
                        $fields[$idLocal]['selectBy'][] = $title;
                    }
                }
                if(validateArray($fkTableFields[$fkTableIdName],'selectBy')){
                    $fkTableFieldRef = $fkTableFields[$fkTableIdName]['selectBy'];
                } else {
                    $fkTableFieldRef = '';
                }
            }

            if (validateVar($fkTableFieldRef) || validateVar($fkTableFieldRef,'array')) {

                if(validateVar($fkTableFieldRef,'array')){
                    $vFkTableFieldRefArray = $fkTableFieldRef;
                    $vFkTableFieldRef = $fkTableFieldRef[0];
                } else {
                    $vFkTableFieldRef = $fkTableFieldRef;
                }

                if (!validateArray($fkTableFields, $vFkTableFieldRef) && in_array($vFkTableFieldRef, $tab_titles)){
                    unset($fields[$idLocal]['selectBy']);
                    unset($vFkTableFieldRefArray);
                    unset($vFkTableFieldRef);
                    foreach ($tab_titles as $title){
                        if(validateArray($fkTableFields, $title)){
                            $vFkTableFieldRef = $title;
                            $fields[$idLocal]['selectBy'][] = $title;
                            $vFkTableFieldRefArray[] = $title;
                        }
                    }
                }

                if (validateArray($fkTableFields, $vFkTableFieldRef)){

                    if (validateArray($fkTableFields[$vFkTableFieldRef], 'table')) {

                        // ****************** verifica fkField dentro de otro fkField *******************

                        if (validateArray($fkTableFields[$vFkTableFieldRef], 'selectBy') && validateArray($fkTableFields[$vFkTableFieldRef], 'idForeign')) {
                            if (validateArray($fkTableFields[$vFkTableFieldRef], 'table')) {
                                $fkfkTableName = $fkTableFields[$vFkTableFieldRef]['table'];
                            } else {
                                show_error('11. No se pudo encontrar la referencia selectBy, revisa las llaves foraneas: ' . $vFkTableFieldRef);
                            }
                        }
                        if(validateArray($fkTableFields[$vFkTableFieldRef],'selectBy')){

                            $fkfkTableFieldRef = $fkTableFields[$vFkTableFieldRef]['selectBy'];
                            $fkfkTableFields = $this->dbforge->getArrayFieldsFromTable($fkfkTableName);
                            $vFkFkTableFieldRef = '';
                            if (validateVar($fkfkTableFieldRef, 'array')) {
                                $vFkFkTableFieldRef = $fkfkTableFieldRef[0];
                            } else if(validateVar($fkfkTableFieldRef)){
                                $vFkFkTableFieldRef = $fkfkTableFieldRef;
                            }

                            // se vuelve a resetear las vareables
                            if (validateVar($vFkFkTableFieldRef)) {
                                if (!validateArray($fkfkTableFields, $vFkFkTableFieldRef) && in_array($vFkFkTableFieldRef, $tab_titles)){
                                    foreach ($tab_titles as $title){
                                        if(validateArray($fkfkTableFields, $title)){
                                            $vFkTableFieldRef = $title;
                                            $vFkFkTableFieldRef = $title;
                                            $vFkTableFieldRefArray[] = $title;
                                        }
                                    }
                                } else {
                                    $vFkTableFieldRefArray = $fkfkTableFieldRef;
                                }

                                if(validateArray($fkfkTableFields,$vFkFkTableFieldRef)){
                                    $fkfkTableFieldRefSettings = $fkfkTableFields[$vFkFkTableFieldRef];
                                    if (validateArray($fkfkTableFieldRefSettings, 'idForeign') && validateArray($fkfkTableFieldRefSettings, 'selectBy')) {

                                        // ************** para setear el setOfFkSettings ***********
                                        $newTableSettings = $fkTableFields[$vFkTableFieldRef];
                                        list($fModSign, $fSubmod) = getModSubMod($newTableSettings['table']);
                                        list($fSubModS, $fSubModP) = setSingularPlural($fSubmod);
                                        $data['setOfFkSettings']['t1FieldRef'] = $newTableSettings['field'];
                                        $data['setOfFkSettings']['t2Contents'] = setObject($fSubModP, true, true);
                                        $data['setOfFkSettings']['t2FieldRef'] = $newTableSettings['idForeign'];

                                        // **********************************
                                        $fkTableFields = $fkfkTableFields;
                                        $vFkTableFieldRef = $vFkFkTableFieldRef;
                                    } else {
                                        $fkTableFields = $fkfkTableFields;
                                        $fkTableName = $fkfkTableName;
                                        $vFkTableFieldRef = validateVar($vFkTableFieldRefArray) ? $vFkTableFieldRefArray : $vFkTableFieldRefArray[0];
                                        $vFkTableFieldRefArray = validateVar($vFkTableFieldRefArray, 'array') ? $vFkTableFieldRefArray : [$vFkTableFieldRefArray];
                                    }
                                } else {
                                    $fkTableFields = $fkfkTableFields;
                                    $fkTableName = $fkfkTableName;
                                }
                            }
                        } else {
                            show_error("1. El parametro $vFkTableFieldRef no existe en la tabla $fkTableName, revisa los parametros json de la tabla: " . $tableName . '.');
                        }
                        // ******************************************************************************
                    }
                } else {
                    show_error("2. El parametro $vFkTableFieldRef no existe en la tabla $fkTableName, revisa los parametros json de la tabla: " . $tableName . '.');
                }
            } else {
                $vFkTableFieldRef = $fkTableFieldRef;
            }
        }
        return [$fields,$fkTableName,$fkTableFields,$vFkTableFieldRef,$vFkTableFieldRefArray];
    }

    private function FK_set_DataForSelectingFields($fkTableName, $fkTableFields, $vFkTableFieldRef, $originFkTableFieldRef, $vFkTableFieldRefArray, $settings, $tableName, $idLocal, $data, $sys)
    {
        $typeForm = '';
        $bIsForeing = false;
        $fkTableFieldRefSettings = array();

        if(validateVar($fkTableFields, 'array')){
            if (validateArray($fkTableFields, $vFkTableFieldRef)) {
                $fkTableFieldRefSettings = $fkTableFields[$vFkTableFieldRef];
            } else {
                show_error("3. El parametro $vFkTableFieldRef no existe en la tabla $fkTableName, revisa los parametros json de la tabla: " . $tableName . '.');
            }
            list($fModSign, $fSubmod) = getModSubMod($fkTableName);
            $fModName = $sys[$fModSign];
            list($fSubModS, $fSubModP) = setSingularPlural($fSubmod);
            list($fModS, $fModP) = setSingularPlural($sys[$fModName]['name']);
            $bIsForeing = true;

            if (validateArray($fkTableFieldRefSettings, 'idForeign') && validateArray($fkTableFieldRefSettings, 'selectBy')) {
                if (validateVar($fkTableFieldRefSettings['selectBy'])) {
                    $fkTableFieldRefSettings['selectBy'] = [$fkTableFieldRefSettings['selectBy']];
                }
                if (validateArray($fkTableFieldRefSettings, 'filterBy')) {
                    $fkTableFieldRefSettings['selectBy'] = array_merge($fkTableFieldRefSettings['selectBy'], $fkTableFieldRefSettings['filterBy']);
                }
                $data['fFieldsRef'] = var_export($settings['selectBy'], true);
                list($ffMod, $ffSubmod) = getModSubMod($fkTableFieldRefSettings['table']);
                list($ffSubModS, $ffSubModP) = setSingularPlural($ffSubmod);

//            if ($fSubModP == "options") {
//                if (validateArray($settings, 'field')) {
//                    $object = strhas($settings['field'], 'id_') ? explode('id_', $settings['field'])[1] : $settings['field'];
//                } else {
//                    $object = $fSubModP;
//                }
//                $data['setOfFkSettings']['lcFkObjFieldP'] = setObject($object, true, true);
//                $data['setOfFkSettings']['UcFkObjFieldP'] = setObject($object, true);
//                if (validateArray($settings, 'insertEachOne')) {
//                    $data['objOptions'] = var_export(['/$id_' . $fSubModS => '/$o' . setObject($fSubModS, true)], true);
//                } else {
//                    $data['objOptions'] = '$o' . setObject($object, true);
//                }
//            } else {
                $data['setOfFkSettings']['lcFkObjFieldP'] = setObject($fSubModP, true, true);
                $data['setOfFkSettings']['UcFkObjFieldP'] = setObject($ffSubModP, true);
                if (validateArray($settings, 'insertEachOne')) {
                    $data['objOptions'] = var_export(['/$id_' . $fSubModS => '/$o' . setObject($fSubModS, false)], true);
                } else {
                    $data['objOptions'] = '$o' . setObject($fSubModP, false);
                }
//            }
                $data['setOfFkSettings']['lcFkTableP'] = $fSubModP;
                $data['setOfFkSettings']['fFieldsRef'] = var_export($fkTableFieldRefSettings['selectBy'], true);

                if ($fSubModP == "options") {
                    if (validateArray($settings, 'field')) {
                        $object = strhas($settings['field'], 'id_') ? explode('id_', $settings['field'])[1] : $settings['field'];
                    } else {
                        $object = $fSubModP;
                    }
                    $data['setOfFkSettings']['t1Contents'] = setObject($object, true, true);
                } else {
                    $data['setOfFkSettings']['t1Contents'] = setObject($fSubModP, true, true);
                }

                $data['setOfFkSettings']['t1FieldRef'] = isset($data['setOfFkSettings']['t1FieldRef']) ? $data['setOfFkSettings']['t1FieldRef'] : $fkTableFieldRefSettings['field'];
                $data['setOfFkSettings']['t2Contents'] = isset($data['setOfFkSettings']['t2Contents']) ? $data['setOfFkSettings']['t2Contents'] : setObject($ffSubModP, true, true);
                $data['setOfFkSettings']['t2FieldRef'] = isset($data['setOfFkSettings']['t2FieldRef']) ? $data['setOfFkSettings']['t2FieldRef'] : $fkTableFieldRefSettings['idForeign'];

                $typeForm = validateArray($settings, 'input') ? $settings['input'] : (validateArray($fkTableFieldRefSettings, 'input') ? $fkTableFieldRefSettings['input'] : 'select');
            } else {
//                $fields[$idLocal]['selectBy'] = $vFkTableFieldRefArray;
                $fields[$idLocal]['selectBy'] = isArray($originFkTableFieldRef) ? $originFkTableFieldRef : [$originFkTableFieldRef];

//                    if (validateVar($fields[$idLocal]['selectBy'])) {
//                        $fields[$idLocal]['selectBy'] = [$fields[$idLocal]['selectBy']];
//                    }
                if (validateArray($fields[$idLocal], 'filterBy')) {
                    $fields[$idLocal]['selectBy'] = array_merge($fields[$idLocal]['selectBy'], $fields[$idLocal]['filterBy']);
                }

                $data['fFieldsRef'] = var_export($fields[$idLocal]['selectBy'], true);
                $typeForm = validateArray($settings, 'input') ? $settings['input'] : 'select';
                if (validateArray($settings, 'insertEachOne')) {
                    $data['objOptions'] = var_export(['/$id_' . $fSubModS => '/$o' . setObject($fSubModS, false)], true);
                } else {
                    if(inArray('filterBy',$settings)){
                        if(isArray($settings['filterBy'])){
                            foreach ($settings['filterBy'] as $field => $value){
                                list($filterS, $filterP) = setSingularPlural($value);
                                $data['objOptions'] = '$o' . setObject($filterP, false);
                            }
                        } else {
                            $data['objOptions'] = '$o' . setObject($fSubModP, false);
                        }
                    } else {
                        $data['objOptions'] = '$o' . setObject($fSubModP, false);
                    }
                }
            }
        }

        return [$data, $typeForm, $bIsForeing, $fkTableFieldRefSettings];
    }
}
