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
     * Base Controller Class
     *
     * @package		STC
     * @subpackage	Controllers
     * @category    Base Controller
     * @author		Nana Axel
     */
    class STC_Controller {

        /**
         * The current instance of the class
         *
         * @var object
         * @access private
         */
        private static $instance ;

        /**
         * Class __constructor
         *
         * @return void
         */
        public function __construct( ) {

            // Saving the current instance
            self::$instance =& $this ;

            // Creating global vars
            foreach (is_loaded() as $var => $class) {
                $this->$var =& load_class($class);
            }

            // Autoload application files
            $this->_autoloader();

            // Execute user functions
            $user_funcs = config_item('on_init_controller');
            if (isset($user_funcs) && is_array($user_funcs)) {
                foreach ($user_funcs as $function) {
                    call_user_func_array($function, array(&$this));
                }
            }

            // Logging Message
            log_message('info', 'Controller Class Initialized');
        }

        /**
         * Return the current instance of the class
         *
         * @static
         * @return object
         */
        public static function &get_instance( ) {
            return self::$instance ;
        }

        /**
         * Files autoloader
         *
         * @return bool  FALSE if no file has been found
         */
        private function _autoloader( ) {
            if ( file_exists( APPPATH . 'inc/autoloader.php' ) ) {
                include_once ( APPPATH . 'inc/autoloader.php' ) ;
            }
            if ( !isset ( $autoload )) {
                return false ;
            }
            if ( isset ( $autoload['includes'] )) {
                foreach ( $autoload['includes'] as $item ) {
                    $this->includes( $item ) ;
                }
            }
            if ( isset ( $autoload['libraries'] ) and count( $autoload['libraries'] ) > 0 ) {
                foreach ( $autoload['libraries'] as $item ) {
                    $this->library( $item ) ;
                }
            }
        }

        public function library( $library = '' ) {
            if ( is_array( $library )) {
                foreach ( $library as $class ) {
                    $this->library( $class ) ;
                }
                return ;
            }
            if ( $library == '' ) {
                return false ;
            }
            if ( file_exists( APPPATH . 'lib/'.$library.'.php' ) ) {
                require_once ( APPPATH . 'lib/' . $library . '.php' );
            }
        }

        public function includes( $include = '' ) {
            if ( is_array( $include )) {
                foreach ( $include as $class ) {
                    $this->includes( $class ) ;
                }
            }
            if ( $include == '' ) {
                return false ;
            }
            if ( file_exists( APPPATH . 'inc/'.$include.'.php' ) ) {
                return require_once ( APPPATH . 'inc/' . $include . '.pho' );
            }
        }

    }