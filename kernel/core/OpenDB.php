<?php

    /**
     * STC - Simplify The Code
     *
     * An open source application development framework for PHP
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2015 - 2017, Alien Technologies
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
     * @package     STC
     * @author      Nana Axel <ax.lnana@outlook.com>
     * @copyright   Copyright (c) 2015 - 2017, Alien Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Database Manager Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Database
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_OpenDB
    {

        /**
         * The database name
         *
         * @var string
         * @access protected
         */
        protected $database;

        /**
         * The table name
         *
         * @var string
         * @access protected
         */
        protected $table;

        /**
         * The database sercer address
         *
         * @var string
         * @access protected
         */
        protected $hostname;

        /**
         * The database username
         *
         * @var string
         * @access protected
         */
        protected $username;

        /**
         * The database password
         *
         * @var string
         * @access protected
         */
        protected $password;

        /**
         * The current PDO instance
         *
         * @var object
         * @access private
         */
        private $pdo = NULL;

        /**
         * Registered SQL operators
         *
         * @var array
         * @access private
         */
        private static $operators = array('!=', '<>', '<=', '>=', '=', '<', '>');

        /**
         * The where clause
         *
         * @var string
         * @access private
         */
        private $where = NULL;

        /**
         * The order clause
         *
         * @var string
         * @access private
         */
        private $order = NULL;

        /**
         * The limit clause
         *
         * @var string
         * @access private
         */
        private $limit = NULL;

        /**
         * Class __constructor
         *
         * @param  string  $table      The name of the table
         * @param  string  $database   The name of the database
         * @param  string  $server     The name of the server
         * @param  string  $user       The username for your database connection
         * @param  string  $pass       The password associated to the username
         */
        public function __construct($table = '', $database = NULL, $server = NULL, $user = NULL, $pass = NULL)
        {
            $this->setDB($database, $table, $server, $user, $pass);
        }

        /**
         * Changes the currently used database
         *
         * @param string $database The database's name
         * @param string $table    The table's name
         * @param string $server   The server's url
         * @param string $user     The user name
         * @param string $pass     The password
         */
        public function setDB($database = NULL, $table = NULL, $server = NULL, $user = NULL, $pass = NULL)
        {
            $this->database = (isset($database) && $database != '') ? $database : config_item('db_name');
            $this->table    = (isset($table) && $table != '')       ? $table    : $this->table;
            $this->hostname = (isset($server) && $server != '')     ? $server   : config_item('db_server');
            $this->username = (isset($user) && $server != '')       ? $user     : config_item('db_user');
            $this->password = (isset($pass) && $server != '')       ? $pass     : config_item('db_pass');
            $this->close();
            $this->_instanciate();
        }

        /**
         * Changes the currently used table
         *
         * @param string $table The table's name
         *
         * @return STC_OpenDB
         */
        public function from($table)
        {
            $this->table = $table;
            return $this;
        }

        /**
         * Add a where condition
         *
         * @param string|array $condition
         *
         * @return STC_OpenDB
         */
        public function where($condition)
        {
            // where(array('field1'=>'value', 'field2'=>'value'))
            $this->where = (NULL !== $this->where) ? $this->where . ' OR (' : '(';
            if (is_array($condition)) {
                $i = 0;
                $operand = '=';
                foreach ($condition as $field => $value) {
                    $this->where .= ($i > 0) ? ' AND ' : '';
                    if (is_int($field)) {
                        $this->where .= $value;
                    }
                    else {
                        $parts = explode(' ', $value);
                        foreach (self::$operators as $operator) {
                            if (in_array($operator, $parts, TRUE) && $parts[0] === $operator) {
                                $operand = $operator;
                            }
                        }
                        $this->where .= $field . ' ' . $operand . ' ' . str_replace($operand, '', $value);
                        $operand = '=';
                    }
                    ++$i;
                }
            }
            else {
                $this->where .= $condition;
            }
            $this->where .= ')';

            return $this;
        }

        /**
         * Add an order clause
         *
         * @param string $field
         * @param string $mode
         *
         * @return STC_OpenDB
         */
        public function order($field, $mode = 'ASC')
        {
            $this->order = " ORDER BY {$field} {$mode} ";
            return $this;
        }

        /**
         * Add a limit clause
         *
         * @param  int  $offset
         * @param  int  $count
         *
         * @return STC_OpenDB
         */
        public function limit($offset, $count)
        {
            $this->limit = " LIMIT {$offset}, {$count} ";
            return $this;
        }

        /**
         * Connect to the database / Instanciate PDO
         *
         * @throws PDOException
         */
        private function _instanciate()
        {
            try {
                $this->pdo = new PDO('mysql:host='.$this->hostname.';dbname='.$this->database, $this->username, $this->password, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE, PDO::ATTR_PERSISTENT => TRUE));

                // Logging Message
                log_message('info', 'Database Class Initialized');
            }
            catch (PDOException $e) {
                show_exception($e);
            }
        }

        /**
         * Parses a where clause
         * @deprecated 1.0.0 Use STC_OpenDB::where() instead
         */
        protected function _parse_where_clause($conditions)
        {
            throw new Exception("Deprecated. Use STC_OpenDB::where() instead");
        }

        /**
         * Reset all clauses
         * @access protected
         */
        protected function _reset_clauses()
        {
            $this->where = NULL;
            $this->order = NULL;
            $this->limit = NULL;
        }

        /**
         * Execute the SELECT SQL query
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return PDO
         */
        protected function _select($fields)
        {
            // Constructing the fields list
            if (is_array($fields)) {
                $_fields = "";
                foreach ($fields as $field => $alias) {
                    if (is_int($field))
                        $_fields .= "{$alias}, ";
                    elseif (is_string($field))
                        $_fields .= "{$field} AS {$alias}, ";
                }
                $fields = trim($_fields, ", ");
            }

            // Constructing the SELECT query string
            $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ((NULL !== $this->where) ? ' WHERE ' . $this->where : '') . ((NULL !== $this->order) ? $this->order : ' ') . ((NULL !== $this->limit) ? $this->limit : ' ');

            // Preparing the query
            $getFieldsDatas = $this->pdo->prepare($query);

            // Executing the query
            if ($getFieldsDatas->execute() !== FALSE) {
                $this->_reset_clauses();
                return $getFieldsDatas;
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }
        }

        /**
         * Selects datas in database
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return PDO
         */
        public function select($fields = '*')
        {
            return $this->_select($fields);
        }

        /**
         * Selects datas as array of arrays in database
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        public function select_array($fields = '*')
        {
            $select = $this->_select($fields);
            $result = array();

            while ($r = $select->fetch(PDO::FETCH_LAZY)) {
                $result[] = array_diff_key((array) $r, array('queryString' => 'queryString'));
            }

            return $result;
        }

        /**
         * Selects the first data result of the query
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        public function select_first($fields = '*')
        {
            $result = $this->select_array($fields);

            if (count($result))
                return $result[0];

            return NULL;
        }

        /**
         * Selects datas as array of objects in database
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        public function select_object($fields = '*')
        {
            $select = $this->_select($fields);
            $result = array();

            while ($r = $select->fetch(PDO::FETCH_OBJ)) {
                $result[] = $r;
            }

            return $result;
        }

        /**
         * Executes a SELECT ... JOIN query
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         * @param  mixed  $joinparams  The information used for jointure.
         *
         * @throws STC_OpenDBException
         *
         * @return PDO
         */
        private function _join($fields, $joinparams)
        {
            $jcond = '';

            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }

            if (is_array($joinparams)) {
                foreach ($joinparams as $joinparam) {
                    $jcond .= ' ' . $joinparam['side'] . ' JOIN ' . $joinparam['table'] . ' ON ' . $joinparam['cond'] . ' ';
                }
            }

            $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' .  $jcond . ' ' . ((NULL !== $this->where) ? ' WHERE ' . $this->where : '') . ((NULL !== $this->order) ? $this->order : ' ') . ((NULL !== $this->limit) ? $this->limit : ' ');

            $getFieldsDatas = $this->pdo->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $this->_reset_clauses();
                return $getFieldsDatas;
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }
        }

        /**
         * Selects datas in database with table joining
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         * @param  mixed  $joinparams  The information used for jointure.
         *
         * @throws STC_OpenDBException
         *
         * @return PDO
         */
        public function join($fields, $joinparams)
        {
            return $this->_join($fields, $joinparams);
        }

        /**
         * Selects datas as array of arrays in database with table joining
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         * @param  mixed  $joinparams  The information used for jointure.
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        public function join_array($fields, $joinparams)
        {
            $join = $this->_join($fields, $joinparams);
            $result = array();

            while ($r = $join->fetch(PDO::FETCH_LAZY)) {
                $result[] = array_diff_key((array) $r, array('queryString' => 'queryString'));
            }

            return $result;
        }

        /**
         * Selects datas as array of objects in database with table joining
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         * @param  mixed  $joinparams  The information used for jointure.
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        public function join_object($fields, $joinparams)
        {
            $join = $this->_join($fields, $joinparams);
            $result = array();

            while ($r = $join->fetch(PDO::FETCH_OBJ)) {
                $result[] = $r;
            }

            return $result;
        }

        /**
         * Counts datas in table
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return integer
         */
        public function count($fields = '*')
        {
            if (is_array($fields)) {
                $field = implode(',', $fields);
            }

            $query = 'SELECT COUNT(' . ((isset($field)) ? $field : $fields) . ') AS opendb_count FROM ' . $this->table . ((NULL !== $this->where) ? ' WHERE ' . $this->where : ' ') . ((NULL !== $this->limit) ? $this->limit : ' ');

            $getFieldsDatas = $this->pdo->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $this->_reset_clauses();
                $data = $getFieldsDatas->fetch();
                return (int) $data['opendb_count'];
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }

        }

        /**
         * Inserts datas in table
         *
         * @param  mixed  $fieldsAndValues  The fields and the associated values to insert.
         *
         * @throws STC_OpenDBException
         *
         * @return boolean
         */
        public function insert($fieldsAndValues)
        {
            $fields = array();
            $values = array();

            foreach ($fieldsAndValues as $field => $value) {
                $fields[] = $field;
                $values[] = $value;
            }

            $field = implode(',', $fields);
            $value = implode(',', $values);

            $query = 'INSERT INTO ' . $this->table . '(' . $field . ') VALUES(' . $value . ')';

            $getFieldsDatas = $this->pdo->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $this->_reset_clauses();
                return TRUE;
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }
        }

        /**
         * Updates datas in table
         *
         * @param  mixed  $fieldsAndValues  The fields and the associated values to update.
         *
         * @throws STC_OpenDBException
         *
         * @return boolean
         */
        public function update($fieldsAndValues)
        {
            $updates = '';
            $count   = count($fieldsAndValues);

            if (is_array($fieldsAndValues)) {
                $values = array();
                foreach ($fieldsAndValues as $field => $value) {
                    $count--;
                    $updates .= "{$field} = {$value}";
                    $updates .= ($count != 0) ? ', ' : '';
                }
            }
            else {
                $updates = $fieldsAndValues;
            }

            $query = 'UPDATE ' . $this->table . ' SET ' . $updates . ((NULL !== $this->where) ? ' WHERE ' . $this->where : '');

            $getFieldsDatas = $this->pdo->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $this->_reset_clauses();
                return TRUE;
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }
        }

        /**
         * Deletes datas in table
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        public function delete()
        {
            $query = 'DELETE FROM ' . $this->table . ((NULL !== $this->where) ? ' WHERE ' . $this->where : '');

            $getFieldsDatas = $this->pdo->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $this->_reset_clauses();
                return TRUE;
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }
        }

        /**
         * Executes a query
         *
         * @uses   PDO::query()
         *
         * @param  string  $query      The query to execute
         * @param  array   $options    PDO options
         *
         * @return mixed
         */
        public function query($query, array $options = array())
        {
            return $this->pdo->query($query, $options);
        }

        /**
         * Prepares a query
         *
         * @uses   PDO::prepare()
         *
         * @param  string  $query      The query to execute
         * @param  array   $options    PDO options
         *
         * @return PDO
         */
        public function prepare($query, array $options = array())
        {
            return $this->pdo->prepare($query, $options);
        }

        /**
         * Quotes a value
         *
         * @uses   PDO::quote()
         *
         * @param  string  $value
         *
         * @return string
         */
        public function quote($value)
        {
            return $this->pdo->quote($value);
        }

        /**
         * Closes a connection
         */
        public function close()
        {
            $this->pdo = FALSE;
        }

    }

    /**
     * Dummy class used to throw exceptions
     */
    class STC_OpenDBException extends Exception { }
