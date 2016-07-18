<?php

    /**
     * STC - Simplify The Code
     *
     * An open source application development framework for PHP
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2015 - 2016, Centers Technologies
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
     * @package	STC
     * @author	Nana Axel
     * @copyright	Copyright (c) 2015 - 2016, Centers Technologies
     * @license	http://opensource.org/licenses/MIT	MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Database Manager Class
     *
     * @package		STC
     * @subpackage	Librairies
     * @category    Database
     * @author		Nana Axel
     */
    class STC_OpenDB extends PDO {

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
        private $pdo_instance = NULL;

        /**
         * Registered SQL operators
         *
         * @var array
         * @access private
         */
        private $operators = array('<', '<=', '=', '<>', '>=', '>');

        /**
         * Class __constructor
         *
         * @param  string  $datatbase  The name of the database
         * @param  string  $table      The name of the table
         *
         * @return void
         */
        public function __construct($table = "", $database = NULL, $server = NULL, $user = NULL, $pass = NULL) {
            $this->setDB($database, $table, $server, $user, $pass);
        }

        public function setDB($database = NULL, $table = NULL, $server = NULL, $user = NULL, $pass = NULL) {
            $this->database = (isset($database) && $database != '') ? $database : config_item('db_name');
            $this->table = (isset($table) && $table != '') ? $table : $this->table;
            $this->hostname = (isset($server) && $server != '') ? $server   : config_item('db_server');
            $this->username = (isset($user) && $server != '')   ? $user     : config_item('db_user');
            $this->password = (isset($pass) && $server != '')   ? $pass     : config_item('db_pass');
            $this->close();
            $this->_instanciate();
        }

        public function from($table) {
            $this->table = $table;
            return $this;
        }

        /**
         * Connect to the database / Instanciate PDO
         *
         * @return void
         *
         * @throws PDOException
         */
        private function _instanciate() {
            try {
                $this->pdo_instance = new PDO('mysql:host='.$this->hostname.';dbname='.$this->database, $this->username, $this->password, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE, PDO::ATTR_PERSISTENT => TRUE));

                // Logging Message
                log_message('info', 'Database Class Initialized');

            }
            catch (PDOException $e) {
                show_exception($e);
            }
        }

        protected function _parse_where_clause($conditions) {
            $conds     = '';
            $count_or  = 0;
            $count_and = 0;
            $operand   = '=';

            if (is_array($conditions)) {
                foreach ($conditions as $key => $value) {
                    if (is_array($value)) {
                        $conds .= ($count_or != 0) ? ' OR ' : '';
                        $count_and = 0;
                        foreach ($value as $field => $data) {
                            $conds .= ($count_and != 0) ? ' AND ' : '';
                            foreach ($this->operators as $operator) {
                                if (in_array($operator, explode(' ', $key)) || in_array($operator, str_split($key))) {
                                    $operand = $operator;
                                    break;
                                }
                            }
                            $conds .= $field . $operand . $this->pdo_instance->quote($data);
                            $count_and++;
                        }
                        $count_or++;
                    } else {
                        $conds .= ($count_and != 0) ? ' AND ' : '';
                        foreach ($this->operators as $operator) {
                            if (in_array($operator, explode(' ', $key)) || in_array($operator, str_split($key))) {
                                $operand = $operator;
                                break;
                            }
                        }
                        $conds .= $key . $operand . $this->pdo_instance->quote($value);
                        $count_and++;
                    }
                }
            } else {
                $conds = $conditions;
            }

            return $conds;
        }

        /**
         * Execute the SELECT SQL query
         *
         * @param  mixed  $fields      The fields to select. This value can be an array of fields,
         *                             or a string of fields (according to the SELECT SQL query syntax).
         * @param  mixed  $conditions  The conditions used in the WHERE clause. This value can be an array of
         *                             field => value associations (this will create a field *operator* value condition) or
         *                             a string of conditions (according to the SELECT SQL query syntax).
         * @param  mixed  $order_by    The condition used in the ORDER BY clause. This value can be an array of
         *                             values, or a string (according to the SELECT SQL query syntax).
         * @param  mixed  $limit       The condition used in the LIMIT clause. This value can be an array of values,
         *                             or a string (according to the SELECT SQL query syntax).
         *
         * @throws STC_OpenDBException
         *
         * @return array
         */
        protected function _select($fields, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $conds = NULL;
            $count = 0;
            $datas = array();

            // Constructing the fields list
            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }

            // Constructing the WHERE clause's conditions list
            $conds = $this->_parse_where_clause($conditions);

            // Constructing the ORDER BY clause's condition
            if (is_array($order_by)) {
                $order_by = implode(' ', $order_by);
            }

            // Constructing the LIMIT clause's condition
            if (is_array($limit)) {
                $limit = implode(', ', $limit);
            }

            // Constructing the SELECT query string
            $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ((isset($conds)) ? ' WHERE ' . $conds : '') . ((isset($order_by)) ? ' ORDER BY ' . $order_by . ' ' : ' ') . ((isset($limit)) ? 'LIMIT ' . $limit : '');

            // Preparing the query
            $getFieldsDatas = $this->pdo_instance->prepare($query);

            // Executing the query
            if ($getFieldsDatas->execute() !== FALSE) {
                return $getFieldsDatas;
            }
            else {
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);
            }

        }

        public function select($fields, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $select = $this->_select($fields, $conditions, $order_by, $limit);
            return $select->fetch(PDO::FETCH_LAZY);
        }

        public function select_array($fields, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $select = $this->_select($fields, $conditions, $order_by, $limit);
            $result = array();

            while ($r = $select->fetch(PDO::FETCH_LAZY)) {
                $result[] = (array) $r;
            }

            return $result;
        }

        public function select_object($fields, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $select = $this->_select($fields, $conditions, $order_by, $limit);
            $result = array();

            while ($r = $select->fetch(PDO::FETCH_OBJ)) {
                $result[] = $r;
            }

            return $result;
        }

        private function _join($fields, $joinparams, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $jcond = '';
            $conds = '';
            $count = 0;
            $datas = array();

            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }

            $conds = $this->_parse_where_clause($conditions);

            $count = 0;
            if (is_array($joinparams)) {
                foreach ($joinparams as $joinparam) {
                    $jcond .= ' ' . $joinparam['side'] . ' JOIN ' . $joinparam['db'] . ' ON ' . $joinparam['cond'] . ' ';
                }
            }

            $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' .  $jcond . ' ' . (($conds != '') ? ' WHERE ' . $conds : '') . (($order_by != NULL) ? ' ORDER BY ' . $order_by . ' ' : ' ') . $limit;

            $getFieldsDatas = $this->pdo_instance->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $getFieldsDatas->setFetchMode(PDO::FETCH_LAZY);
                return $getFieldsDatas->fetch();
            }
            else
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);

        }

        public function join($fields, $joinparams, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $join = $this->_join($fields, $joinparams, $conditions, $order_by, $limit);
            return $join->fetch(PDO::FETCH_LAZY);
        }

        public function join_array($fields, $joinparams, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $join = $this->_join($fields, $joinparams, $conditions, $order_by, $limit);
            $result = array();

            while ($r = $join->fetch(PDO::FETCH_LAZY)) {
                $result[] = (array) $r;
            }

            return $result;
        }

        public function join_object($fields, $joinparams, $conditions = NULL, $order_by = NULL, $limit = NULL) {
            $join = $this->_join($fields, $joinparams, $conditions, $order_by, $limit);
            $result = array();

            while ($r = $join->fetch(PDO::FETCH_OBJ)) {
                $result[] = $r;
            }

            return $result;
        }

        public function count($fields, $conditions = NULL, $limit = NULL) {
            $conds = '';
            $count = 0;
            $datas = array();

            if (is_array($fields)) {
                $field = implode(',', $fields);
            }

            $conds = $this->_parse_where_clause($conditions);

            $query = 'SELECT COUNT(' . ((isset($field)) ? $field : $fields) . ') AS opendb_count FROM ' . $this->table . (($conds != '') ? ' WHERE ' . $conds .' ' : ' ') . $limit;

            $getFieldsDatas = $this->pdo_instance->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE) {
                $data = $getFieldsDatas->fetch();
                return intval($data['opendb_count']);
            }
            else
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);

        }

        public function insert($fieldsAndValues) {

            $fields = array();
            $values = array();

            foreach ($fieldsAndValues as $field => $value) {
                $fields[] = $field;
                $values[] = $this->pdo_instance->quote($value);
            }

            $field = implode(',', $fields);
            $value = implode(',', $values);

            $query = 'INSERT INTO ' . $this->table . '(' . $field . ') VALUES(' . $value . ')';

            $getFieldsDatas = $this->pdo_instance->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE)
                 return TRUE;

            else
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);

        }

        public function update($fieldsAndValues, $conditions = NULL) {

            $updates = '';
            $conds   = '';
            $count   = count($fieldsAndValues);

            if (is_array($fieldsAndValues)) {
                $values = array();
                foreach ($fieldsAndValues as $field => $value) {
                    $count--;
                    $updates .= "{$field} = ".$this->pdo_instance->quote($value);
                    $updates .= ($count != 0) ? ', ' : '';
                }
            }
            else $updates = $fieldsAndValues;

            $count   = 0;

            $conds = $this->_parse_where_clause($conditions);

            $query = 'UPDATE ' . $this->table . ' SET ' . $updates . (($conds != '') ? ' WHERE ' . $conds : '');

            $getFieldsDatas = $this->pdo_instance->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE)
                 return TRUE;

            else
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);

        }

        public function delete($conditions) {

            $conds   = '';
            $count   = 0;

            $conds = $this->_parse_where_clause($conditions);

            $query = 'DELETE FROM ' . $this->table . (($conds != '') ? ' WHERE ' . $conds : '');

            $getFieldsDatas = $this->pdo_instance->prepare($query);

            if ($getFieldsDatas->execute() !== FALSE)
                 return TRUE;

            else
                throw new STC_OpenDBException($getFieldsDatas->errorInfo()[2]);

        }

        public function query($query, $options = NULL) {
            return $this->pdo_instance->query($query, isset($options) ? $options : array());
        }

        public function prepare($query, $options = NULL) {
            return $this->pdo_instance->prepare($query, isset($options) ? $options : array());
        }

        public function close() {
            $this->pdo_instance = FALSE;
        }

    }

    /**
     * Dummy class used to throw exceptions
     */
    class STC_OpenDBException extends Exception { }
