<?php

    /**
     * STC - Simplify The Code
     *
     * An open source application development framework for PHP
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2015 - 2016, Alien Technologies
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and assostcated documentation files (the "Software"), to deal
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
     * @copyright   Copyright (c) 2015 - 2016, Alien Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * PHP Global Variables Class
     *
     * @package     STC
     * @subpackage  Utilities
     * @category    PHP Global Variables
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_PHP_Globals
    {

        /**
         * The current used variable
         *
         * @var string
         * @access protected
         */
        protected $_;

        /**
         * The current used variable
         *
         * @var array
         * @access protected
         */
        protected $instances = array();

        /**
         * Class constructor
         */
        public function __construct(&$_ = NULL)
        {
            $this->_ = &$_;
        }

        /**
         *
         */
        public function __get($name)
        {
            $name = strtolower($name);
            if (array_key_exists($name, $this->instances)) {
                return $this->instances[$name];
            }
            else {
                switch ($name) {
                    case 'get':
                        return $this->instances['get'] = new STC_PHP_Globals($_GET);

                    case 'post':
                        return $this->instances['post'] = new STC_PHP_Globals($_POST);

                    case 'cookie':
                        return $this->instances['cookie'] = new STC_PHP_Globals($_COOKIE);

                    case 'session':
                        return $this->instances['session'] = new STC_PHP_Globals($_SESSION);

                    case 'files':
                        return $this->instances['files'] = new STC_PHP_Globals($_FILES);

                    default:
                        return NULL;
                }
            }
        }

        /**
         * Check if the current variable is set and is not null
         *
         * @param  string  $param  If defined, the method will check if the given key exist
         *                         in the variable.
         * @return  boolean
         */
        public function is($param = NULL)
        {
            return (NULL === $param) ? (isset($this->_) && count($this->_) > 0) : array_key_exists($param, $this->_);
        }

        /**
         * Sets a value
         *
         * @param  string|array  $id     If it's a string, the name of the value.
         *                               If it's an array, a set of name => value association.
         * @param  mixed         $value  The value of the variable.
         *
         * @return  STC_PHP_Globals
         */
        public function set($id, $value = NULL)
        {
            if (is_array($id)) {
                foreach ($id as $key => $val) {
                    $this->set($key, $val);
                }
            } else {
                $this->_[(string)$id] = $value;
            }

            return $this;
        }

        /**
         * Gets a value
         *
         * @param  string|array  $id  If it's a string, the name of the value.
         *                            If it's an array, a set of value's names.
         *                            If it's not specified, all the variable array will be returned.
         *
         * @return  mixed
         */
        public function get($id = NULL)
        {
            if (NULL === $id) {
                return $this->_;
            }
            else if (is_array($id)) {
                $session = array();
                foreach ($id as $key) {
                    $session[$key] = $this->_[$key];
                }
                return $session;
            }
            else {
                if ($this->is($id)) {
                    return $this->_[$id];
                } else {
                    return NULL;
                }
            }
        }

        /**
         * Deletes a value
         *
         * @param  string|array  $id  If it's a string, the name of the value.
         *                            If it's an array, a set of value's names.
         *                            If it's not specified, all the variable array will be deleted.
         *
         * @return  void
         */
        public function delete($id = NULL)
        {
            if (NULL === $id) {
                foreach ($this->_ as $key => $val) {
                    unset($this->_[$key]);
                }
            }
            else if (is_array($id)) {
                foreach ($id as $key) {
                    unset($this->_[$key]);
                }
            }
            else {
                unset($this->_[$id]);
            }
        }

    }