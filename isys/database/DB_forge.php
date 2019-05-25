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
 * @since    Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Database Forge Class
 *
 * @category    Database
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/database/
 *
 * @var CI_Controller $CI
 */
abstract class CI_DB_forge
{

    /**
     * Database object
     *
     * @var    object
     */
    protected $db;

    /**
     * Fields data
     *
     * @var    array
     */
    public $fields = array();

    /**
     * Keys data
     *
     * @var    array
     */
    public $keys = array();

    /**
     * Primary Keys data
     *
     * @var    array
     */
    public $primary_keys = array();

    /**
     * Database character set
     *
     * @var    string
     */
    public $db_char_set = '';

    // --------------------------------------------------------------------

    /**
     * CREATE DATABASE statement
     *
     * @var    string
     */
    protected $_create_database = 'CREATE DATABASE %s';

    /**
     * DROP DATABASE statement
     *
     * @var    string
     */
    protected $_drop_database = 'DROP DATABASE %s';

    /**
     * CREATE TABLE statement
     *
     * @var    string
     */
    protected $_create_table = "%s %s (%s\n)";

    /**
     * CREATE TABLE IF statement
     *
     * @var    string
     */
    protected $_create_table_if = 'CREATE TABLE IF NOT EXISTS';

    /**
     * CREATE TABLE keys flag
     *
     * Whether table keys are created from within the
     * CREATE TABLE statement.
     *
     * @var    bool
     */
    protected $_create_table_keys = FALSE;

    /**
     * DROP TABLE IF EXISTS statement
     *
     * @var    string
     */
    protected $_drop_table_if = 'DROP TABLE IF EXISTS';

    /**
     * RENAME TABLE statement
     *
     * @var    string
     */
    protected $_rename_table = 'ALTER TABLE %s RENAME TO %s;';

    /**
     * UNSIGNED support
     *
     * @var    bool|array
     */
    protected $_unsigned = TRUE;

    /**
     * NULL value representation in CREATE/ALTER TABLE statements
     *
     * @var    string
     */
    protected $_null = '';

    /**
     * DEFAULT value representation in CREATE/ALTER TABLE statements
     *
     * @var    string
     */
    protected $_default = ' DEFAULT ';

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param    object &$db Database object
     * @return    void
     */
    public function __construct(&$db)
    {
        $this->db =& $db;
        log_message('info', 'Database Forge Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Create database
     *
     * @param    string $db_name
     * @return    bool
     */
    public function create_database($db_name)
    {
        if ($this->_create_database === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        } elseif (!$this->db->query(sprintf($this->_create_database, $db_name, $this->db->char_set, $this->db->dbcollat))) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unable_to_drop') : FALSE;
        }

        if (!empty($this->db->data_cache['db_names'])) {
            $this->db->data_cache['db_names'][] = $db_name;
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Drop database
     *
     * @param    string $db_name
     * @return    bool
     */
    public function drop_database($db_name)
    {
        if ($this->_drop_database === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        } elseif (!$this->db->query(sprintf($this->_drop_database, $db_name))) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unable_to_drop') : FALSE;
        }

        if (!empty($this->db->data_cache['db_names'])) {
            $key = array_search(strtolower($db_name), array_map('strtolower', $this->db->data_cache['db_names']), TRUE);
            if ($key !== FALSE) {
                unset($this->db->data_cache['db_names'][$key]);
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Add Key
     *
     * @param    string $key
     * @param    bool $primary
     * @return    CI_DB_forge
     */
    public function add_key($key, $primary = FALSE)
    {
        // DO NOT change this! This condition is only applicable
        // for PRIMARY keys because you can only have one such,
        // and therefore all fields you add to it will be included
        // in the same, composite PRIMARY KEY.
        //
        // It's not the same for regular indexes.
        if (is_array($key)) {

            if ($primary === TRUE) {
                foreach ($key as $one) {
                    $this->add_key($one, $primary);
                }
                return $this;
            } else {
                foreach ($key as $name => $attr) {

                    if (is_array($attr)) {

                        $primary = false;

                        //$this->add_key(array($name => $attr),$primary);
                        //$attr = array_merge($attr,array('name' => $name));
                        $this->keys[] = array($name => $attr);

                    } else {

                        if (is_string($attr) && is_like($attr, 'primary')) {

                            $this->primary_keys[] = $name;
                            //$this->add_key($name, $primary);

                        }
                    }
                }
            }
        } else {
            if ($primary === TRUE) {
                $this->primary_keys[] = $key;
            } else {
                $this->keys[] = $key;
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Add Field
     *
     * @param    array $field
     * @return    CI_DB_forge
     */
    public function add_field($field)
    {
        if (is_string($field)) {
            if ($field === 'id') {
                $this->add_field(array(
                    'id' => array(
                        'type' => 'INT',
                        'constraint' => 9,
                        'auto_increment' => TRUE
                    )
                ));
                $this->add_key('id', TRUE);
            } else {
                if (strpos($field, ' ') === FALSE) {
                    show_error('Field information is required for that operation.');
                }

                $this->fields[] = $field;
            }
        }

        if (is_array($field)) {
            $this->fields = array_merge($this->fields, $field);
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Create Table
     *
     * @param    string $table Table name
     * @param    bool $if_not_exists Whether to add IF NOT EXISTS condition
     * @param    array $attributes Associative array of table attributes
     * @return    bool
     */
    public function create_table($table, $if_not_exists = FALSE, array $attributes = array())
    {
        if ($table === '') {
            show_error('A table name is required for that operation.');
        } else {
            $table = $this->db->dbprefix . $table;
        }

        if (count($this->fields) === 0) {
            show_error('Field information is required.');
        }

        $sql = $this->_create_table($table, $if_not_exists, $attributes);

        if (is_bool($sql)) {
            $this->_reset();
            if ($sql === FALSE) {
                return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
            }
        }

        if (($result = $this->db->query($sql)) !== FALSE) {
            empty($this->db->data_cache['table_names']) OR $this->db->data_cache['table_names'][] = $table;

            // Most databases don't support creating indexes from within the CREATE TABLE statement
            if (!empty($this->keys)) {
                for ($i = 0, $sqls = $this->_process_indexes($table), $c = count($sqls); $i < $c; $i++) {
                    $this->db->query($sqls[$i]);
                }
            }
        }

        $this->_reset();
        return $result;
    }

    // --------------------------------------------------------------------

    /**
     * Create Table
     *
     * @param    string $table Table name
     * @param    bool $if_not_exists Whether to add 'IF NOT EXISTS' condition
     * @param    array $attributes Associative array of table attributes
     * @return    mixed
     */
    protected function _create_table($table, $if_not_exists, $attributes)
    {
        if ($if_not_exists === TRUE && $this->_create_table_if === FALSE) {
            if ($this->db->table_exists($table)) {
                return TRUE;
            } else {
                $if_not_exists = FALSE;
            }
        }

        $sql = ($if_not_exists)
            ? sprintf($this->_create_table_if, $this->db->escape_identifiers($table))
            : 'CREATE TABLE';

        $columns = $this->_process_fields(TRUE);
        for ($i = 0, $c = count($columns); $i < $c; $i++) {
            $columns[$i] = ($columns[$i]['_literal'] !== FALSE)
                ? "\n\t" . $columns[$i]['_literal']
                : "\n\t" . $this->_process_column($columns[$i]);
        }

        $columns = implode(',', $columns)
            . $this->_process_primary_keys($table);

        // Are indexes created from within the CREATE TABLE statement? (e.g. in MySQL)
        if ($this->_create_table_keys === TRUE) {
            $columns .= $this->_process_indexes($table);
        }

        // _create_table will usually have the following format: "%s %s (%s\n)"
        $sql = sprintf($this->_create_table . '%s',
            $sql,
            $this->db->escape_identifiers($table),
            $columns,
            $this->_create_table_attr($attributes)
        );

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * CREATE TABLE attributes
     *
     * @param    array $attributes Associative array of table attributes
     * @return    string
     */
    protected function _create_table_attr($attributes)
    {
        $sql = '';

        foreach (array_keys($attributes) as $key) {
            if (is_string($key)) {
                $sql .= ' ' . strtoupper($key) . ' ' . $attributes[$key];
            }
        }

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Drop Table
     *
     * @param    string $table_name Table name
     * @param    bool $if_exists Whether to add an IF EXISTS condition
     * @return    bool
     */
    public function drop_table($table_name, $if_exists = FALSE)
    {
        if ($table_name === '') {
            return ($this->db->db_debug) ? $this->db->display_error('db_table_name_required') : FALSE;
        }

        if (($query = $this->_drop_table($this->db->dbprefix . $table_name, $if_exists)) === TRUE) {
            return TRUE;
        }

        $query = $this->db->query($query);

        // Update table list cache
        if ($query && !empty($this->db->data_cache['table_names'])) {
            $key = array_search(strtolower($this->db->dbprefix . $table_name), array_map('strtolower', $this->db->data_cache['table_names']), TRUE);
            if ($key !== FALSE) {
                unset($this->db->data_cache['table_names'][$key]);
            }
        }

        return $query;
    }

    // --------------------------------------------------------------------

    /**
     * Drop Table
     *
     * Generates a platform-specific DROP TABLE string
     *
     * @param    string $table Table name
     * @param    bool $if_exists Whether to add an IF EXISTS condition
     * @return    string
     */
    protected function _drop_table($table, $if_exists)
    {
        $sql = 'DROP TABLE';

        if ($if_exists) {
            if ($this->_drop_table_if === FALSE) {
                if (!$this->db->table_exists($table)) {
                    return TRUE;
                }
            } else {
                $sql = sprintf($this->_drop_table_if, $this->db->escape_identifiers($table));
            }
        }

        return $sql . ' ' . $this->db->escape_identifiers($table);
    }

    // --------------------------------------------------------------------

    /**
     * Rename Table
     *
     * @param    string $table_name Old table name
     * @param    string $new_table_name New table name
     * @return    bool
     */
    public function rename_table($table_name, $new_table_name)
    {
        if ($table_name === '' OR $new_table_name === '') {
            show_error('A table name is required for that operation.');
            return FALSE;
        } elseif ($this->_rename_table === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        $result = $this->db->query(sprintf($this->_rename_table,
                $this->db->escape_identifiers($this->db->dbprefix . $table_name),
                $this->db->escape_identifiers($this->db->dbprefix . $new_table_name))
        );

        if ($result && !empty($this->db->data_cache['table_names'])) {
            $key = array_search(strtolower($this->db->dbprefix . $table_name), array_map('strtolower', $this->db->data_cache['table_names']), TRUE);
            if ($key !== FALSE) {
                $this->db->data_cache['table_names'][$key] = $this->db->dbprefix . $new_table_name;
            }
        }

        return $result;
    }

    // --------------------------------------------------------------------

    /**
     * Column Add
     *
     * @todo    Remove deprecated $_after option in 3.1+
     * @param    string $table Table name
     * @param    array $field Column definition
     * @param    string $_after Column for AFTER clause (deprecated)
     * @return    bool
     */
    public function add_column($table, $field, $_after = NULL)
    {
        // Work-around for literal column definitions
        is_array($field) OR $field = array($field);

        foreach (array_keys($field) as $k) {
            // Backwards-compatibility work-around for MySQL/CUBRID AFTER clause (remove in 3.1+)
            if ($_after !== NULL && is_array($field[$k]) && !isset($field[$k]['after'])) {
                $field[$k]['after'] = $_after;
            }

            $this->add_field(array($k => $field[$k]));
        }

        $sqls = $this->_alter_table('ADD', $this->db->dbprefix . $table, $this->_process_fields());
        $this->_reset();
        if ($sqls === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        for ($i = 0, $c = count($sqls); $i < $c; $i++) {
            if ($this->db->query($sqls[$i]) === FALSE) {
                return FALSE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Column Drop
     *
     * @param    string $table Table name
     * @param    string $column_name Column name
     * @return    bool
     */
    public function drop_column($table, $column_name)
    {
        $sql = $this->_alter_table('DROP', $this->db->dbprefix . $table, $column_name);
        if ($sql === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        return $this->db->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Column Modify
     *
     * @param    string $table Table name
     * @param    string $field Column definition
     * @return    bool
     */
    public function modify_column($table, $field)
    {
        // Work-around for literal column definitions
        is_array($field) OR $field = array($field);

        foreach (array_keys($field) as $k) {
            $this->add_field(array($k => $field[$k]));
        }

        if (count($this->fields) === 0) {
            show_error('Field information is required.');
        }

        $sqls = $this->_alter_table('CHANGE', $this->db->dbprefix . $table, $this->_process_fields());
        $this->_reset();
        if ($sqls === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        for ($i = 0, $c = count($sqls); $i < $c; $i++) {
            if ($this->db->query($sqls[$i]) === FALSE) {
                return FALSE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * ALTER TABLE
     *
     * @param    string $alter_type ALTER type
     * @param    string $table Table name
     * @param    mixed $field Column definition
     * @return    string|string[]
     */
    protected function _alter_table($alter_type, $table, $field)
    {
        $sql = 'ALTER TABLE ' . $this->db->escape_identifiers($table) . ' ';

        // DROP has everything it needs now.
        if ($alter_type === 'DROP') {
            return $sql . 'DROP COLUMN ' . $this->db->escape_identifiers($field);
        }

        $sql .= ($alter_type === 'ADD')
            ? 'ADD '
            : $alter_type . ' COLUMN ';

        $sqls = array();
        for ($i = 0, $c = count($field); $i < $c; $i++) {
            $sqls[] = $sql
                . ($field[$i]['_literal'] !== FALSE ? $field[$i]['_literal'] : $this->_process_column($field[$i]));
        }

        return $sqls;
    }

    // --------------------------------------------------------------------

    /**
     * Process fields
     *
     * @param    bool $create_table
     * @return    array
     */
    protected function _process_fields($create_table = FALSE)
    {
        $fields = array();

        foreach ($this->fields as $key => $attributes) {
            if (is_int($key) && !is_array($attributes)) {
                $fields[] = array('_literal' => $attributes);
                continue;
            }

            $attributes = array_change_key_case($attributes, CASE_UPPER);

            if ($create_table === TRUE && empty($attributes['TYPE'])) {
                continue;
            }

            isset($attributes['TYPE']) && $this->_attr_type($attributes);

            $field = array(
                'name' => $key,
                'new_name' => isset($attributes['NAME']) ? $attributes['NAME'] : NULL,
                'type' => isset($attributes['TYPE']) ? $attributes['TYPE'] : NULL,
                'length' => '',
                'unsigned' => '',
                'null' => '',
                'unique' => '',
                'default' => '',
                'auto_increment' => '',
                '_literal' => FALSE
            );

            isset($attributes['TYPE']) && $this->_attr_unsigned($attributes, $field);

            if ($create_table === FALSE) {
                if (isset($attributes['AFTER'])) {
                    $field['after'] = $attributes['AFTER'];
                } elseif (isset($attributes['FIRST'])) {
                    $field['first'] = (bool)$attributes['FIRST'];
                }
            }

            $this->_attr_default($attributes, $field);

            if (isset($attributes['NULL'])) {
                if ($attributes['NULL'] === TRUE) {
                    $field['null'] = empty($this->_null) ? '' : ' ' . $this->_null;
                } else {
                    $field['null'] = ' NOT NULL';
                }
            } elseif ($create_table === TRUE) {
                $field['null'] = ' NOT NULL';
            }

            $this->_attr_auto_increment($attributes, $field);
            $this->_attr_unique($attributes, $field);

            if (isset($attributes['COMMENT'])) {
                $field['comment'] = $this->db->escape($attributes['COMMENT']);
            }

            if (isset($attributes['TYPE']) && !empty($attributes['CONSTRAINT'])) {
                switch (strtoupper($attributes['TYPE'])) {
                    case 'ENUM':
                    case 'SET':
                        $attributes['CONSTRAINT'] = $this->db->escape($attributes['CONSTRAINT']);
                    default:
                        $field['length'] = is_array($attributes['CONSTRAINT'])
                            ? '(' . implode(',', $attributes['CONSTRAINT']) . ')'
                            : '(' . $attributes['CONSTRAINT'] . ')';
                        break;
                }
            }

            $fields[] = $field;
        }

        return $fields;
    }

    // --------------------------------------------------------------------

    /**
     * Process column
     *
     * @param    array $field
     * @return    string
     */
    protected function _process_column($field)
    {
        return $this->db->escape_identifiers($field['name'])
            . ' ' . $field['type'] . $field['length']
            . $field['unsigned']
            . $field['default']
            . $field['null']
            . $field['auto_increment']
            . $field['unique'];
    }

    // --------------------------------------------------------------------

    /**
     * Field attribute TYPE
     *
     * Performs a data type mapping between different databases.
     *
     * @param    array &$attributes
     * @return    void
     */
    protected function _attr_type(&$attributes)
    {
        // Usually overridden by drivers
    }

    // --------------------------------------------------------------------

    /**
     * Field attribute UNSIGNED
     *
     * Depending on the _unsigned property value:
     *
     *    - TRUE will always set $field['unsigned'] to 'UNSIGNED'
     *    - FALSE will always set $field['unsigned'] to ''
     *    - array(TYPE) will set $field['unsigned'] to 'UNSIGNED',
     *        if $attributes['TYPE'] is found in the array
     *    - array(TYPE => UTYPE) will change $field['type'],
     *        from TYPE to UTYPE in case of a match
     *
     * @param    array &$attributes
     * @param    array &$field
     * @return    void
     */
    protected function _attr_unsigned(&$attributes, &$field)
    {
        if (empty($attributes['UNSIGNED']) OR $attributes['UNSIGNED'] !== TRUE) {
            return;
        }

        // Reset the attribute in order to avoid issues if we do type conversion
        $attributes['UNSIGNED'] = FALSE;

        if (is_array($this->_unsigned)) {
            foreach (array_keys($this->_unsigned) as $key) {
                if (is_int($key) && strcasecmp($attributes['TYPE'], $this->_unsigned[$key]) === 0) {
                    $field['unsigned'] = ' UNSIGNED';
                    return;
                } elseif (is_string($key) && strcasecmp($attributes['TYPE'], $key) === 0) {
                    $field['type'] = $key;
                    return;
                }
            }

            return;
        }

        $field['unsigned'] = ($this->_unsigned === TRUE) ? ' UNSIGNED' : '';
    }

    // --------------------------------------------------------------------

    /**
     * Field attribute DEFAULT
     *
     * @param    array &$attributes
     * @param    array &$field
     * @return    void
     */
    protected function _attr_default(&$attributes, &$field)
    {
        if ($this->_default === FALSE) {
            return;
        }

        if (array_key_exists('DEFAULT', $attributes)) {
            if ($attributes['DEFAULT'] === NULL) {
                $field['default'] = empty($this->_null) ? '' : $this->_default . $this->_null;

                // Override the NULL attribute if that's our default
                $attributes['NULL'] = TRUE;
                $field['null'] = empty($this->_null) ? '' : ' ' . $this->_null;
            } else {
                $field['default'] = $this->_default . $this->db->escape($attributes['DEFAULT']);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Field attribute UNIQUE
     *
     * @param    array &$attributes
     * @param    array &$field
     * @return    void
     */
    protected function _attr_unique(&$attributes, &$field)
    {
        if (!empty($attributes['UNIQUE']) && $attributes['UNIQUE'] === TRUE) {
            $field['unique'] = ' UNIQUE';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Field attribute AUTO_INCREMENT
     *
     * @param    array &$attributes
     * @param    array &$field
     * @return    void
     */
    protected function _attr_auto_increment(&$attributes, &$field)
    {
        if (!empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === TRUE && stripos($field['type'], 'int') !== FALSE) {
            $field['auto_increment'] = ' AUTO_INCREMENT';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Process primary keys
     *
     * @param    string $table Table name
     * @return    string
     */
    protected function _process_primary_keys($table)
    {
        $sql = '';

        for ($i = 0, $c = count($this->primary_keys); $i < $c; $i++) {
            if (!isset($this->fields[$this->primary_keys[$i]])) {
                unset($this->primary_keys[$i]);
            }
        }

        if (count($this->primary_keys) > 0) {
            $sql .= ",\n\tCONSTRAINT " . $this->db->escape_identifiers('pk_' . $table)
                . ' PRIMARY KEY(' . implode(', ', $this->db->escape_identifiers($this->primary_keys)) . ')';
        }

        $this->primary_keys = [];

        return $sql;
    }

    // --------------------------------------------------------------------

    /**
     * Process indexes
     *
     * @param    string $table
     * @return    string
     */
    protected function _process_indexes($table)
    {
        $sqls = array();

        for ($i = 0, $c = count($this->keys); $i < $c; $i++) {
            if (is_array($this->keys[$i])) {
                for ($i2 = 0, $c2 = count($this->keys[$i]); $i2 < $c2; $i2++) {
                    if (!isset($this->fields[$this->keys[$i][$i2]])) {
                        unset($this->keys[$i][$i2]);
                        continue;
                    }
                }
            } elseif (!isset($this->fields[$this->keys[$i]])) {
                unset($this->keys[$i]);
                continue;
            }

            is_array($this->keys[$i]) OR $this->keys[$i] = array($this->keys[$i]);

            $sqls[] = 'CREATE INDEX ' . $this->db->escape_identifiers($table . '_' . implode('_', $this->keys[$i]))
                . ' ON ' . $this->db->escape_identifiers($table)
                . ' (' . implode(', ', $this->db->escape_identifiers($this->keys[$i])) . ');';
        }

        return $sqls;
    }

    // --------------------------------------------------------------------

    /**
     * Reset
     *
     * Resets table creation vars
     *
     * @return    void
     */
    protected function _reset()
    {
        $this->fields = $this->keys = $this->primary_keys = array();
    }

    public function tableExists($table, $database = ''){
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $CI->db->query($sql)->row();
        if (count((array)$result)) {
            return true;
        }
        return false;

    }

    public function hasRelation($table1, $id1, $table2, $id2, $constraint = '', $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT TABLE_NAME,COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE where CONSTRAINT_SCHEMA='$database' and TABLE_NAME='$table1' and COLUMN_NAME='$id1' and REFERENCED_TABLE_NAME='$table2' and REFERENCED_COLUMN_NAME='$id2'";
        if ($constraint == '') {
            $constraint = '';
        } else {
            $sql .= " and CONSTRAINT_NAME='$constraint'";
        }
        $result = $CI->db->query($sql)->row();
        if (count((array)$result)) {
            return true;
        }
        return false;
    }

    public function getPrimaryKeyFromTable($table, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "Select COLUMN_NAME from information_schema.`COLUMNS` where TABLE_SCHEMA='$database' and TABLE_NAME='$table' and COLUMN_KEY='PRI'";
        $result = $CI->db->query($sql)->row();
        if (count((array)$result)) {
            return $result->COLUMN_NAME;
        }
        return false;
    }

    public function getTableNameByIdTable($idTable, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "Select TABLE_NAME from information_schema.`COLUMNS` where TABLE_SCHEMA='$database' and COLUMN_NAME='$idTable' and COLUMN_KEY='PRI'";
        $result = $CI->db->query($sql)->row();
        if (count((array)$result)) {
            return $result->TABLE_NAME;
        }
        return false;
    }

    public function getTables($database = '')
    {
        $CI = CI_Controller::get_instance();

        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='$database'";
        $result = $CI->db->query($sql)->result();
        if (count($result)) {
            $results = json_decode(json_encode($result), true);
            $results = array_column($result,'TABLE_NAME');
            $aResult = array();
            foreach ($results as $k => $value){
                $aResult[$value] = setLabel($value);
            }
            return $aResult;
        }
        return false;
    }

    public function getArrayDBTables($database = '')
    {
        $CI = CI_Controller::get_instance();

        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='$database'";
        $result = $CI->db->query($sql)->result();
        if (count($result)) {
            $results = json_decode(json_encode($result), true);
            $results = array_column($result,'TABLE_NAME');
            $aResult = array();
            foreach ($results as $k => $value){
                $aResult[] = $value;
            }
            return $aResult;
        }
        return false;
    }

    public function getTableFields($table, $database = '')
    {
        $CI = CI_Controller::get_instance();

        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'";
        $result = $CI->db->query($sql)->result();
        if (count($result)) {
            return $result;
        }
        return false;
    }

    public function getArrayColumnsKey($table, $database = '')
    {
        $CI = CI_Controller::get_instance();

        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_KEY = 'MUL' AND TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'";
        $results = $CI->db->query($sql)->result();
        if (count($results)) {
            $aResults = array();
            foreach ($results as $result){
                $aResults[] = $result->COLUMN_NAME;
            }
            return $aResults;
        }
        return false;
    }

    public function fieldExistsInDB($table, $field, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "Select * from information_schema.`COLUMNS` where TABLE_SCHEMA='$database' and TABLE_NAME='$table' and COLUMN_NAME='$field'";
        $result = $CI->db->query($sql, false, true)->row();
        if (count((array)$result)) {
            return $result;
        }
        return false;
    }

    public function setRelation($table, $id, $tableReferenced, $idReferenced, $idConstraint, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "ALTER TABLE `$table` ADD CONSTRAINT `$idConstraint` FOREIGN KEY (`$id`) REFERENCES `$tableReferenced` (`$idReferenced`) ON UPDATE CASCADE ON DELETE CASCADE";
        $CI->db->query($sql);
    }

    public function removeRelation($table, $idConstraint, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "ALTER TABLE `$table` DROP FOREIGN KEY `$idConstraint`";
        $CI->db->query($sql);
    }

    public function setPrimaryKey($table, $id, $bAutoIncrement, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        if ($bAutoIncrement) {
            $sql = "ALTER TABLE $table ADD PRIMARY KEY AUTO_INCREMENT (`$id`)";
        } else {
            $sql = "ALTER TABLE $table ADD PRIMARY KEY (`$id`)";
        }
        $CI->db->query($sql);
    }

    public function getTableCommentsFromDB($table, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT table_comment FROM information_schema.tables WHERE table_schema = '$database' AND table_name = '$table'";
        return $CI->db->query($sql)->row();
    }

    public function setTableCommentsFromDB($tableComments ,$table, $database = ''){
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        if(validateVar($tableComments,'array')){
            $jsonComment = json_encode($tableComments);
            $sql = "ALTER TABLE $table COMMENT = '$jsonComment'";
            return $CI->db->query($sql);
        }
        return false;
    }

    public function getFieldCommentsFromDB($field, $table, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$field'";

        $json = $CI->db->query($sql)->row()->COLUMN_COMMENT;
        $stdComment = json_decode($json);
        $aExtraSettings = json_decode(json_encode($stdComment), true);
        return $aExtraSettings;
    }

    public function setFieldCommentsFromDB($fieldComments ,$field, $table, $database = ''){
        $CI = CI_Controller::get_instance();
        if(validateVar($fieldComments,'array')){
            $name = $field['name'];
            $type = $field['type'];
            $length = $field['constraint'];
            $unsigned = $field['unsigned'] ? 'unsigned': '';
            $null = $field['null'] ? 'null': 'not null';
            $jsonComment = json_encode($fieldComments);
            $sql = "ALTER TABLE $table MODIFY titulo $type($length) $unsigned $null COMMENT '$jsonComment'";
            return $CI->db->query($sql);
        }
        return false;
    }

    public function getArrayTableNamesFromDB($database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA LIKE '$database' ORDER BY CREATE_TIME ASC";

        $result = $CI->db->query($sql)->result();
        return array_column(json_decode(json_encode($result), true), "TABLE_NAME");
    }

    public function getTableRelations($table, $database = '')
    {
        $CI = CI_Controller::get_instance();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $sql = "SELECT CONSTRAINT_NAME, COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE table_schema = '$database' AND table_name = '$table' AND CONSTRAINT_NAME NOT LIKE 'PRIMARY'";
        $stdResult = $CI->db->query($sql)->result();
        $aResult = json_decode(json_encode($stdResult), true);
        if (count((array)$aResult)) {
            $aRelations = [];
            foreach ($aResult as $relation) {
                $aRelations[$relation['CONSTRAINT_NAME']] = array(
                    'table' => $relation['REFERENCED_TABLE_NAME'],
                    'idLocal' => $relation['COLUMN_NAME'],
                    'idForeign' => $relation['REFERENCED_COLUMN_NAME']
                );
            }
            return $aRelations;
        } else {
            return [];
        }
    }

    public function getArrayFieldsFromTable($table = '', $bWithTableNames = false)
    {
        $CI = CI_Controller::get_instance();
        $excepts = config_item('controlFields');
        $tables = array();
        $aTables = array();
        $database = $CI->db->database;

        if(is_bool($table)) {
            $bWithTableNames = true;
        } else if ($table == '') {
            $tables = $this->getArrayTableNamesFromDB();
        } else {
            $tables[] = $table;
        }
        foreach ($tables as $tab) {
            $sql = "SHOW COLUMNS FROM `$tab` FROM `$database`";
            $data = json_decode(json_encode($CI->db->query($sql)->result()), true);
            foreach ($data as $key => $value){
                $data[$key]['tabName'] = $tab;
            }
            $pk = $this->getPrimaryKeyFromTable($tab);
            $callback = function ($field) {
                $fieldName = $field['Field'];
                preg_match("/[A-Za-z-]+/", $field['Type'], $fieldType);
                preg_match("/[0-9]{1,11}/", $field['Type'], $fieldConstraint);
                $fieldUnsigned = strpos($field['Type'], 'unsigned') > -1 ? TRUE : FALSE;
                $fieldNull = $field['Null'] ? TRUE : FALSE;
                $fieldAutoIncrement = $field['Extra'] == 'auto_increment' || strpos($field['Extra'], 'auto_increment') > -1 ? TRUE : FALSE;
                $field['tabName'] = $field['tabName'];
                $field['field'] = $field['Field'];
                $field['type'] = isset($fieldType[0]) ? $fieldType[0] : "";
                $field['constraint'] = isset($fieldConstraint[0]) ? $fieldConstraint[0] : "";
                $field['unsigned'] = $fieldUnsigned;
                $field['null'] = $fieldNull;
                $field['key'] = $field['Key'];
                $field['default'] = $field['Default'];
                $field['auto_increment'] = $fieldAutoIncrement;
                $field['extra'] = $field['Extra'];
                unset($field['Field']);
                unset($field['Type']);
                unset($field['Null']);
                unset($field['Key']);
                unset($field['Default']);
                unset($field['Extra']);
                return [$fieldName => $field];
            };
            $aTable = array_map($callback, $data);
            $aRelations = $this->getTableRelations($tab);
            foreach ($aTable as $key => $aFields) {
                $colName = array_keys($aFields)[0];
                $aExtraSettings = $this->getFieldCommentsFromDB($colName, $tab);
                $aExtras = $aExtraSettings != null ? $aExtraSettings : [];
                $aFields = array_merge($aFields[$colName], $aExtras);
                if (!validateArray($aFields, 'validate') && !compareArrayNum($aFields,'validate',0)&& !in_array($colName, $excepts)) {
                    $aFields['validate'] = 'required';
                }
                if (validateVar($aRelations, 'array')) {
                    $constraintName = array_keys($aRelations)[0];
                    $aOptionsRelated = array_values($aRelations);
                    foreach ($aOptionsRelated as $i => $options) {
                        if ($options['idLocal'] == $colName) {
                            $aFields = array_merge($aFields, ['idForeign' => $options['idForeign'], 'table' => $options['table']]);
                        }
                    }
                }
                if ($aFields['key'] != "PRI") {
                    unset($aFields['key']);
                    unset($aFields['auto_increment']);
                } else {
                    unset($aFields['key']);
                }
                if (in_array($colName, $excepts)) {
                    if (compareStrStr($colName, 'estado')) {
                        $aFields['label'] = "Estado";
                        $aFields['input'] = "radio";
                        $aFields['options'] = ["ENABLED", "DISABLED"];
                    }
                    if (compareStrStr($colName, 'change_count')) {
                        $aFields['label'] = "Numero de Cambios de este registro";
                        $aFields['input'] = "disabled";
                    }
                    if (compareStrStr($colName, 'id_user_modified')) {
                        $aFields['label'] = "Nombre del usuario que modifico el registro";
                        $aFields['selectBy'] = ["name", "lastname"];
                        $aFields['input'] = "disabled";
                    }
                    if (compareStrStr($colName, 'id_user_created')) {
                        $aFields['label'] = "Nombre del usuario que creo el registro";
                        $aFields['selectBy'] = ["name", "lastname"];
                        $aFields['input'] = "disabled";
                    }
                    if (compareStrStr($colName, 'date_modified')) {
                        $aFields['label'] = "Fecha de modificación";
                        $aFields['input'] = "disabled";
                    }
                    if (compareStrStr($colName, 'date_created')) {
                        $aFields['label'] = "Fecha de creación";
                        $aFields['input'] = "disabled";
                    }
                }
                $aTables[$tab][$colName] = $aFields;
                $aTables[$tab][$colName]['pk'] = $pk;
            }
        }
        if($bWithTableNames){
            return $aTables;
        } else if(count($aTables) == 1){
            return $aTables[$tab];
        } else {
            return $aTables;
        }
    }

    public function getArrayTablesSettingsFromDB($table = '', $database = '')
    {
        $CI = CI_Controller::get_instance();
        $tables = array();
        $aTables = array();
        if ($database == '') {
            $database = $CI->db->database;
        }
        $comment = $this->getTableCommentsFromDB($table);
        $stdComment = json_decode($comment->table_comment);
        $aSettings = $stdComment != null ? json_decode(json_encode($stdComment), true) : [];
        return $aSettings;
    }

    public function updateMigrationTable($idMigration)
    {
        $CI = CI_Controller::get_instance();

        if ($CI->dbforge->tableExists('migrations')) {
            $oMigrations = $CI->db->get('migrations')->result();
            if (count($oMigrations) > 1) {
                $this->drop_table('migrations');
                $this->updateMigrationTable($idMigration);
            } else if (isset($oMigrations[0])) {
                $CI->db->query('DELETE FROM `migrations` WHERE `version`=' . $oMigrations[0]->version);
                $CI->db->query('INSERT INTO `migrations`(`version`) VALUES (' . intval($idMigration - 1) . ')');
            } else {
                $CI->db->query('INSERT INTO `migrations`(`version`) VALUES (' . intval($idMigration - 1) . ')');
            }
        } else {
            $fields = array(
                'version' => array(
                    'type' => 'bigint',
                    'constraint' => '20',
                ),
            );
            $this->add_field($fields);
            $this->create_table('migrations');
            $this->updateMigrationTable($idMigration);
        }
    }

    public function getPrimaryKeysOfTables($bJustPrimaryKeys = false, $bInverse = true)
    {
        $CI = CI_Controller::get_instance();
        $database = $CI->db->database;

        $sql = "SELECT COLUMN_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$database' AND COLUMN_KEY = 'PRI'";
        $result = $CI->db->query($sql)->result();
        if (count((array)$result)) {
            $aColumnNames = array_column(json_decode(json_encode($result), true), "COLUMN_NAME");
            $aTableNames = array_column(json_decode(json_encode($result), true), "TABLE_NAME");
            if($bJustPrimaryKeys){
                return $aColumnNames;
            } else {
                if($bInverse){
                    return array_combine($aColumnNames, $aTableNames);
                } else {
                    return array_combine($aTableNames, $aColumnNames);
                }
            }
        }
        return false;
    }

    public function getForeignKeyOfTables($bJustFkKeys = false)
    {
        $CI = CI_Controller::get_instance();
        $database = $CI->db->database;

        $sql = "SELECT COLUMN_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$database' AND COLUMN_KEY = 'MUL'";
        $result = $CI->db->query($sql)->result();
        if (count((array)$result)) {
            $aColumnNames = array_column(json_decode(json_encode($result), true), "COLUMN_NAME");
            $aTableNames = array_column(json_decode(json_encode($result), true), "TABLE_NAME");
            if($bJustFkKeys){
                return $aColumnNames;
            } else {
                return array_combine($aColumnNames, $aTableNames);
            }
        }
        return false;
    }

    public function create_es_sessions()
    {
        $CI = CI_Controller::get_instance();
        $database = $CI->db->database;

        $sql = "
        CREATE TABLE IF NOT EXISTS `es_sessions` (
                `id` varchar(128) NOT NULL,
                `ip_address` varchar(45) NOT NULL,
                `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
                `data` blob NOT NULL,
                `id_user` int(10) unsigned
                KEY `es_sessions_timestamp` (`timestamp`)
        );
        ";
        $this->db->query($sql);
    }

    public function setDeleted($tableName, $pk, $id){
        if($this->db->field_exists('estado',$tableName)){
            $this->db->set('estado','DELETED');
        } else if($this->db->field_exists('status',$tableName)){
            $this->db->set('status','DELETED');
        }
        $this->db->where($pk, $id);
        return $this->db->update($tableName);
    }
}
