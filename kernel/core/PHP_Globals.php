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
     * @package	STC
     * @author	Nana Axel
     * @copyright	Copyright (c) 2015 - 2016, Centers Technologies
     * @license	http://opensource.org/licenses/MIT	MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * PHP Global Variables Class
     *
     * @package		STC
     * @subpackage	Utilities
     * @category    PHP Global Variables
     * @author		Nana Axel
     */
    class STC_PHP_Globals {

        /**
         * The get class instance
         *
         * @var array
         */
        public $get;

        /**
         * The post class instance
         *
         * @var array
         */
        public $post;

        /**
         * The cookie class instance
         *
         * @var array
         */
        public $cookie;

        /**
         * The session class instance
         *
         * @var array
         */
        public $session;

        /**
         * The files class instance
         *
         * @var array
         */
        public $files;

        /**
         * The current used variable
         *
         * @var string
         * @access protected
         */
        protected $_;

        /**
         * Class constructor
         */
        public function __construct() {
            // Initializing G_P_C_S_F classes
            $this->get      =  new STC_PHP_Globals_GET();
            $this->post     =  new STC_PHP_Globals_POST();
            $this->cookie   =  new STC_PHP_Globals_COOKIE();
            $this->session  =  new STC_PHP_Globals_SESSION();
            $this->files    =  new STC_PHP_Globals_FILES();
        }

        /**
         * Check if the current variable is set and is not null
         *
         * @return  boolean
         */
        public function is_set() {
            return isset($this->{$this->_}) && count($this->{$this->_}) > 0;
        }

        /**
         * Sets a value
         *
         * @param  string|array  $id     If it's a string, the name of the value.
         *                               If it's an array, a set of name => value association.
         * @param  mixed         $value  The value of the variable.
         *
         * @return  object  The current class instance to make chainable method calls.
         */
        public function set_value($id, $value = NULL) {

            if (is_array($id)) {
                foreach ($id as $key => $val) {
                    $this->set_value($key, $val);
                }
            } else {
                $this->{$this->_}[strval($id)] = $value;
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
        public function get_value($id = NULL) {

            if ( ! isset($id)) {
                return $this->{$this->_};
            }
            else if (is_array($id)) {
                $session = array();
                foreach ($id as $key) {
                    $session[$key] = $this->{$this->_}[$key];
                }
                return $session;
            }
            else {
                if (isset($this->{$this->_}[$id])) {
                    return $this->{$this->_}[$id];
                } else {
                    return FALSE;
                }
            }

        }

        /**
         * Deletes a value
         *
         * @param  string|array  $id  If it's a string, the name of the value.
         *                            If it's an array, a set of value's names.
         *                            If it's not specified, all the variable array will be returned.
         *
         * @return  mixed
         */
        public function delete_value($id = NULL) {

            if ( ! isset($id)) {
                unset($this->{$this->_});
            }
            else if (is_array($id)) {
                foreach ($id as $key) {
                    unset($this->{$this->_}[$key]);
                }
            }
            else {
                unset($this->{$this->_}[$id]);
            }

        }

    }

    /**
     * STC_PHP_Globals_GET
     */
    class STC_PHP_Globals_GET extends STC_PHP_Globals {

        /**
         * The $_GET variable
         *
         * @var array
         * @access protected
         */
        protected $_get;

        public function __construct() {
            $this->_get =& $_GET;
            $this->_    = '_get';
        }
    }

    /**
     * STC_PHP_Globals_POST
     */
    class STC_PHP_Globals_POST extends STC_PHP_Globals {

        /**
         * The $_POST variable
         *
         * @var array
         * @access protected
         */
        protected $_post;

        public function __construct() {
            $this->_post =& $_POST;
            $this->_     = '_post';
        }
    }

    /**
     * STC_PHP_Globals_COOKIE
     */
    class STC_PHP_Globals_COOKIE extends STC_PHP_Globals {

        /**
         * The $_COOKIE variable
         *
         * @var array
         * @access protected
         */
        protected $_cookie;

        public function __construct() {
            $this->_cookie =& $_COOKIE;
            $this->_       = '_cookie';
        }
    }

    /**
     * STC_PHP_Globals_SESSION
     */
    class STC_PHP_Globals_SESSION extends STC_PHP_Globals {

        /**
         * The $_SESSION variable
         *
         * @var array
         * @access protected
         */
        protected $_session;

        public function __construct() {
            $this->_session =& $_SESSION;
            $this->_        = '_session';
        }
    }

    /**
     * STC_PHP_Globals_FILES
     */
    class STC_PHP_Globals_FILES extends STC_PHP_Globals {

        /**
         * The $_FILES variable
         *
         * @var array
         * @access protected
         */
        protected $_files;

        public function __construct() {
            $this->_files =& $_FILES;
            $this->_      = '_files';
        }
    }
