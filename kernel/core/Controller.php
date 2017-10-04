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
     * Base Controller Class
     *
     * @package     STC
     * @subpackage  Controllers
     * @category    Base Controller
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Controller
    {

        /**
         * The current instance of the class
         * @var STC_Controller
         * @access private
         */
        private static $instance;

        /**
         * Class __constructor
         */
        public function __construct( )
        {
            // Saving the current instance
            self::$instance =& $this ;

            // Creating global vars
            foreach (is_loaded() as $var => $class) {
                $this->$var =& load_class($class);
            }

            // Autoload application files
            $this->_autoload();

            // Trigger Controller events
            $this->events->controller->trigger('init', array(&$this));

            // Logging Message
            log_message('info', 'Controller Class Initialized');
        }

        /**
         * Return the current instance of the class
         * @static
         * @return STC_Controller
         */
        public static function &get_instance( )
        {
            return self::$instance ;
        }

        /**
         * Files autoloader
         * @access private
         * @return bool  FALSE if no file has been found
         */
        private function _autoload( )
        {
            if ( file_exists( $filepath = make_path(array(APPPATH, 'inc', 'autoloader.php')) ) ) {
                include_once $filepath;
            }
            if ( ! isset ( $autoload )) {
                return FALSE ;
            }

            if ( ! is_array( $autoload )) {
                log_message('error', 'Your autoload file is not formatted correctly: The variable "$autoload" is not an array.');
                show_error('Your autoload file is not formatted correctly: The variable "$autoload" is not an array.');
            }

            foreach ( $autoload as $file ) {
                if ( file_exists( $filepath = make_path(array(APPPATH, 'lib', str_replace('.php', '', $file) . '.php')) ) ) {
                    require $filepath;
                }
                else {
                    log_message('error', 'Unable to autoload the file: ' . $filepath);
                }
            }
        }

    }
