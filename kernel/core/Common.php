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
     * @package     STC
     * @author      Nana Axel
     * @copyright   Copyright (c) 2015 - 2016, Centers Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Common Functions
     *
     * A set of functions used in all the system.
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Common Functions
     * @author      Nana Axel
     */

    if ( ! function_exists('is_ajax_request')) {
        /**
         * Is Ajax request ?
         *
         * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
         *
         * @return bool
         */
        function is_ajax_request( ) {
            return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        }
    }

    if ( ! function_exists('make_path')) {
        /**
         * Implode all parts of $path and return a valid path
         *
         * @param  array  $path  Parts of the path to build
         * @return string
         */
        function make_path( array $path ) {
            return implode(DIRECTORY_SEPARATOR, array_map(function($field) {
                return trim($field, '/\\');
            }, $path) );
        }
    }

    if ( ! function_exists('is_ssl')) {
        /**
         * Checks if the application is under SSL
         *
         * @return bool
         */
        function is_ssl( ) {
            if ( array_key_exists( 'HTTPS', $_SERVER ) ) {
                if ( 'on' === strtolower( $_SERVER['HTTPS'] )) {
                    return TRUE;
                }
                if ( '1' === $_SERVER['HTTPS'] ) {
                    return TRUE;
                }
            }

            if ( array_key_exists( 'HTTP_X_FORWARDED_PROTO', $_SERVER ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
                return TRUE;
            }

            if ( ! empty( $_SERVER['HTTP_FRONT_END_HTTPS'] ) && strtolower( $_SERVER['HTTP_FRONT_END_HTTPS'] ) !== 'off') {
                return TRUE;
            }

            if ( array_key_exists( 'SERVER_PORT', $_SERVER ) && ( '443' === $_SERVER['SERVER_PORT'] ) ) {
                return TRUE ;
            }

            return FALSE ;
        }
    }

    if ( ! function_exists('base_url')) {
        /**
         * Gets the base url of the application
         *
         * Checks if the base_url configuration item is set, otherwise guess the application
         * base url and auto detect the protocol (http/https)
         *
         * @return string
         */
        function base_url( ) {
            if ( config_item('base_url') !== '' ) {
                $url = rtrim( config_item('base_url'), '/' ) . '/' ;
            }
            else {
                $FCPATH_fix = str_replace( '\\', '/', FCPATH ) ;
                $script_filename_dir = dirname( $_SERVER['SCRIPT_FILENAME'] ) ;
                if ( $script_filename_dir . '/' === $FCPATH_fix ) {
                    $path = preg_replace( '#/[^/]*$#', '', $_SERVER['PHP_SELF'] ) ;
                }
                else {
                    if ( FALSE !== strpos( $_SERVER['SCRIPT_FILENAME'], $FCPATH_fix )) {
                        $directory = str_replace( FCPATH, '', $script_filename_dir ) ;
                        $path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '', $_SERVER['REQUEST_URI'] ) ;
                    }
                    elseif ( FALSE !== strpos( $FCPATH_fix, $script_filename_dir )) {
                        $subdirectory = substr( $FCPATH_fix, strpos( $FCPATH_fix, $script_filename_dir ) + strlen( $script_filename_dir )) ;
                        $path = preg_replace( '#/[^/]*$#', '', $_SERVER['REQUEST_URI'] ) . $subdirectory ;
                    }
                    else {
                        $path = $_SERVER['REQUEST_URI'];
                    }
                }
                $schema = is_ssl( ) ? 'https://' : 'http://' ; // set_url_scheme() is not defined yet
                $url = $schema . $_SERVER['HTTP_HOST'] . $path ;
            }
            return $url;
        }
    }

    if ( ! function_exists('is_php')) {
        /**
         * Determines if the current version of PHP is equal to or greater than the supplied value
         *
         * @param	string  $version  The version of PHP to check
         * @return	bool	          TRUE if the current version is $version or higher
         */
        function is_php( $version ) {
            static $_is_php;
            $version = (string) $version;

            if (NULL === $_is_php) {
                $_is_php = array();
            }

            if ( ! array_key_exists($version, $_is_php)) {
                $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
            }

            return $_is_php[$version];
        }
    }

    if ( ! function_exists('is_really_writable')) {
        /**
         * Tests for file writability
         *
         * is_writable() returns TRUE on Windows servers when you really can't write to
         * the file, based on the read-only attribute. is_writable() is also unreliable
         * on Unix servers if safe_mode is on.
         *
         * @link   https://bugs.php.net/bug.php?id=54709
         * @param  string  $file  The path to the file to check the writability
         * @return bool  TRUE if the file is really writable
         */
        function is_really_writable( $file ) {
            // If we're on a Unix server with safe_mode off we call is_writable
            if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR ! ini_get('safe_mode'))) {
                return is_writable($file);
            }

            /* For Windows servers and safe_mode "on" installations we'll actually
             * write a file then read it. Bah...
             */
            if (is_dir($file)) {
                $file = make_path( array( $file, md5(mt_rand()) ) );
                if (($fp = @fopen($file, 'ab')) === FALSE) {
                    return FALSE;
                }

                fclose($fp);
                @chmod($file, 0777);
                @unlink($file);
                return TRUE;
            }
            elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }

            fclose($fp);
            return TRUE;
        }
    }

    if ( ! function_exists('load_class')) {
        /**
         * Class registry
         *
         * This function acts as a singleton. If the requested class does not
         * exist it is instantiated and set to a static variable. If it has
         * previously been instantiated the variable is returned.
         *
         * @param   string  $class      The class name being requested
         * @param   string  $directory  The directory where the class should be found
         * @param   string  $param      An optional argument to pass to the class constructor
         * @return  object
         * @throws  RuntimeException  Can't load the STC_Controller class with this function
         */
        function &load_class( $class, $directory = 'core', $param = NULL ) {
            static $_classes;

            if (NULL === $_classes) {
                $_classes = array();
            }

            // Does the class exist? If so, we're done...
            if (array_key_exists($class, $_classes) && NULL !== $_classes[$class]) {
                return $_classes[$class];
            }

            $name = FALSE;

            // Look for the class first in the local application/libraries folder
            // then in the native system/libraries folder
            foreach (array(APPPATH, BASEPATH) as $path) {
                $file_path = make_path( array( $path, $directory, $class . '.php' ) );
                if (file_exists($file_path)) {

                    $name = $class;

                    if ($path === BASEPATH) {
                        $name = 'STC_' . $class;
                    }

                    // For integrity reasons, we can't load the STC_Controller class
                    // with this function... so...
                    if ($class === 'Controller' && $path === BASEPATH && $directory === 'core') {
                        throw new RuntimeException("Can't load the STC base controller class with this function. Use get_controller_instance() instead.");
                    }

                    if (class_exists($name, FALSE) === FALSE) {
                        require_once $file_path;
                    }

                    break;
                }
            }

            // Did we find the class?
            if ($name === FALSE) {
                log_message('error', 'Unable to locate the specified class: ' . $class . '.php');
                // Note: We use exit() rather than show_error() in order to avoid a
                // self-referencing loop with the Exceptions class
                set_status_header(503);
                echo 'Unable to locate the specified class: ' . $class . '.php';
                exit(5); // EXIT_UNK_CLASS
            }

            // Keep track of what we just loaded
            is_loaded($class);

            $_classes[$class] = (NULL !== $param) ? new $name($param) : new $name();
            return $_classes[$class];
        }
    }

    if ( ! function_exists('is_loaded')) {
        /**
         * Keeps track of which libraries have been loaded. This function is
         * called by the load_class() function above
         *
         * @param	string
         * @return	array
         */
        function &is_loaded( $class = NULL ) {
            static $_is_loaded = array();

            if (NULL !== $class) {
                $_is_loaded[strtolower($class)] = $class;
            }

            return $_is_loaded;
        }
    }

    if ( ! function_exists('get_config')) {
        /**
         * Loads the main config.php file
         *
         * This function lets us grab the config file even if the Config class
         * hasn't been instantiated yet
         *
         * @param  array $replace
         * @return array
         */
        function &get_config( array $replace = array() ) {
            static $config;

            if (NULL === $config) {
                // We assume first we want to load the configuration file of a specific environment
                $file_path = make_path( array( APPPATH, 'inc', ENVIRONMENT, 'config.php' ) );
                $found = FALSE;
                if (file_exists($file_path)) {
                    $found = TRUE;
                    require $file_path;
                }
                elseif (file_exists($file_path = make_path( array( APPPATH, 'inc', 'config.php' ) ))) {
                    $found = TRUE;
                    require $file_path;
                }

                if ( ! $found) {
                    log_message('error', 'The configuration file does not exist.');
                    set_status_header(503);
                    echo 'The configuration file does not exist.';
                    exit(3); // EXIT_CONFIG
                }

                // Does the $config array exist in the file?
                if ( ! is_array($config)) {
                    log_message('error', 'Your config file does not appear to be formatted correctly.');
                    set_status_header(503);
                    echo 'Your config file does not appear to be formatted correctly.';
                    exit(3); // EXIT_CONFIG
                }
            }

            // Are any values being dynamically added or replaced?
            foreach ($replace as $key => $val) {
                $config[$key] = $val;
            }

            return $config;
        }
    }

    if ( ! function_exists('config_item')) {
        /**
         * Returns the specified config item
         *
         * @param	string
         * @return	mixed
         */
        function config_item( $item ) {
            static $_config;

            if (NULL === $_config) {
                // references cannot be directly assigned to static variables, so we use an array
                $_config[0] =& get_config();
            }

            return isset($_config[0][$item]) ? $_config[0][$item] : NULL;
        }
    }

    if ( ! function_exists('get_mimes')) {
        /**
         * Returns the MIME types array from config/mimes.php
         *
         * @return  array
         */
        function &get_mimes( ) {
            static $_mimes;

            if (NULL === $_mimes) {
                if (file_exists($filepath = make_path(array(APPPATH, 'inc', ENVIRONMENT, 'mimes.php')))) {
                    $_mimes[0] = include $filepath;
                }
                elseif (file_exists($filepath = make_path(array(APPPATH, 'inc', 'mimes.php')))) {
                    $_mimes[0] = include $filepath;
                }
                else {
                    $_mimes[0] = array();
                }
            }

            return $_mimes[0];
        }
    }

    if ( ! function_exists('is_cli')) {
        /**
         * Is CLI?
         *
         * Test to see if a request was made from the command line.
         *
         * @return 	bool
         */
        function is_cli( ) {
            return (PHP_SAPI === 'cli' OR defined('STDIN'));
        }
    }

    if ( ! function_exists('show_error')) {
        /**
         * Error Handler
         *
         * This function lets us invoke the exception class and
         * display errors using the standard error template located
         * in app/views/templates/errors/general.tpl
         * This function will send the error page directly to the
         * browser and exit.
         *
         * @param	string $message
         * @param	int    $status_code
         * @param	string $heading
         * @return	void
         */
        function show_error( $message, $status_code = 500, $heading = 'An Error Was Encountered' ) {
            static $_error;

            $status_code = abs($status_code);
            if ($status_code < 100) {
                $exit_status = $status_code + 9; // 9 is EXIT__AUTO_MIN
                if ($exit_status > 125) { // 125 is EXIT__AUTO_MAX
                    $exit_status = 1; // EXIT_ERROR
                }

                $status_code = 500;
            }
            else {
                $exit_status = 1; // EXIT_ERROR
            }

            if (NULL === $_error) {
                $_error[0] =& load_class('Exceptions');
            }

            echo $_error[0]->show_error($heading, $message, 'general', $status_code);
            exit($exit_status);
        }
    }

    if ( ! function_exists('show_404')) {
        /**
         * 404 Page Handler
         *
         * This function is similar to the show_error() function above
         * However, instead of the standard error template it displays
         * 404 errors.
         *
         * @param	bool   $log_error
         * @return	void
         */
        function show_404( $log_error = TRUE ) {
            static $_error;

            if ($_error === NULL) {
                $_error[0] =& load_class('Exceptions');
            }

            echo $_error[0]->show_404($log_error);
            exit(4); // EXIT_UNKNOWN_FILE
        }
    }

    if ( ! function_exists('show_exception')) {
        /**
         * Exception Page Handler
         *
         * This function is similar to the show_error() function above
         * However, instead of the standard error template it displays
         * exception errors.
         *
         * @param	Exception
         * @return	void
         */
        function show_exception(Exception $e) {
            static $_error;

            if ($_error === NULL) {
                $_error[0] =& load_class('Exceptions');
            }

            echo $_error[0]->show_exception($e);
            exit(1); // EXIT_ERROR
        }
    }

    if ( ! function_exists('log_message')) {
        /**
         * Error Logging Interface
         *
         * We use this as a simple mechanism to access the logging
         * class and send messages to be logged.
         *
         * @param   string  $level    The error level: 'error', 'debug' or 'info'
         * @param   string  $message  The error message
         * @return  void
         */
        function log_message( $level, $message ) {
            static $_log;

            if (config_item('enable_logging') === TRUE) {
                if ($_log === NULL) {
                    // references cannot be directly assigned to static variables, so we use an array
                    $_log[0] =& load_class('Log');
                }

                $_log[0]->write_log($level, $message);
            }
        }
    }

    if ( ! function_exists('set_status_header')) {
        /**
         * Set HTTP Status Header
         *
         * @param   int     $code  The status code
         * @param   string  $text  The status description
         * @return  void
         */
        function set_status_header( $code = 200, $text = '' ) {
            if (is_cli()) {
                return;
            }

            if (empty($code) OR ! is_numeric($code)) {
                show_error('Status codes must be numeric', 500);
            }

            if (empty($text)) {
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

                if (array_key_exists($code, $stati)) {
                    $text = $stati[$code];
                }
                else {
                    show_error('No status text available. Please check your status code number or supply your own message text.', 500);
                }
            }

            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header('Status: '.$code.' '.$text, TRUE);
            }
            else {
                $server_protocol = (array_key_exists('SERVER_PROTOCOL', $_SERVER) && NULL !== $_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
                header($server_protocol.' '.$code.' '.$text, TRUE, $code);
            }
        }
    }

    if ( ! function_exists('remove_invisible_characters')) {
        /**
         * Remove Invisible Characters
         *
         * This prevents sandwiching null characters
         * between ascii characters, like Java\0script.
         *
         * @param   string  $str
         * @param   bool    $url_encoded
         * @return  string
         */
        function remove_invisible_characters( $str, $url_encoded = TRUE ) {
            $non_displayables = array();

            // every control character except newline (dec 10),
            // carriage return (dec 13) and horizontal tab (dec 09)
            if ($url_encoded) {
                $non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
                $non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
            }

            $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

            do {
                $str = preg_replace($non_displayables, '', $str, -1, $count);
            }
            while ($count);

            return $str;
        }
    }

    if ( ! function_exists('html_escape')) {
        /**
         * Returns HTML escaped variable.
         *
         * @param   mixed  $var            The input string or array of strings to be escaped.
         * @param   bool   $double_encode  Set to FALSE prevents escaping twice.
         * @return  mixed  The escaped string or array of strings as a result.
         */
        function html_escape( $var, $double_encode = TRUE ) {
            if (empty($var)) {
                return $var;
            }

            if (is_array($var)) {
                return array_map('html_escape', $var, array_fill(0, count($var), $double_encode));
            }

            return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
        }
    }

    if ( ! function_exists('translate')) {
        /**
         * Return the translation of a text in the chosen lang.
         *
         * @param   string  $string  The key for identifying the translated value
         * @param   array   $param   Additional words to add in the translated value
         * @param   string  $lang    The language to use for translating
         *
         * @return  string
         */
        function translate( $string, array $param = array(), $lang = NULL ) {
            static $_language;

            if ($_language === NULL) {
                $_language[0] =& load_class('Lang');
            }

            $bkp = $_language[0]->getLangID();
            if (NULL !== $lang && is_string($lang)) {
                $_language[0]->setLang($lang);
            }
            $txt = $_language[0]->translate($string, $param);

            $_language[0]->setLang($bkp);

            return $txt;
        }
    }

    if ( ! function_exists('smarty_modifier_translate')) {
        /**
         * Return the translation of a text in a Smarty template
         *
         * @param   string  $string  The key for identifying the translated value
         * @param   array   $param   Additional words to add in the translated value
         * @param   string  $lang    The language to use for translation
         *
         * @return  string
         */
        function smarty_modifier_translate($string, array $param = array(), $lang = NULL) {
            return translate($string, $param, $lang);
        }
    }

    if ( ! function_exists('trigger_event_callbacks')) {
        /**
         * Triggers callbacks from event
         *
         * @param  string  $ev_name   The name of the event
         * @param  string  $cb_name   The name of the set of callbacks
         * @param  array   $cb_param  The parameters to use in callbacks
         */
        function trigger_event_callbacks( $ev_name, $cb_name, $cb_param ) {
            static $_events;

            if ($_events === NULL) {
                $_events[0] =& load_class('Events');
            }

            $_events[0]->$ev_name->trigger($cb_name, $cb_param);
        }
    }

    if ( ! function_exists('add_event_callback')) {
        /**
         * Adds callbacks for event
         *
         * @param  string    $ev_name  The name of the event
         * @param  string    $cb_name  The name of the set of callbacks
         * @param  callable  $cb_func  The callback
         */
        function add_event_callback( $ev_name, $cb_name, $cb_func ) {
            static $_events;

            if ($_events === NULL) {
                $_events[0] =& load_class('Events');
            }

            $_events[0]->$ev_name->on($cb_name, $cb_func);
        }
    }

    if ( ! function_exists('remove_event_callback')) {
        /**
         * Removes callbacks for event
         *
         * @param  string  $ev_name  The name of the event
         * @param  string  $cb_name  The name of the set of callbacks
         */
        function remove_event_callback( $ev_name, $cb_name ) {
            static $_events;

            if ($_events === NULL) {
                $_events[0] =& load_class('Events');
            }

            $_events[0]->$ev_name->off($cb_name);
        }
    }

    if ( ! function_exists('event_callback_exists')) {
        /**
         * Checks if exist a set of callbacks for event
         *
         * @param  string  $ev_name  The name of the event
         * @param  string  $cb_name  The name of the set of callbacks
         */
        function event_callback_exists( $ev_name, $cb_name ) {
            static $_events;

            if ($_events === NULL) {
                $_events[0] =& load_class('Events');
            }

            return $_events[0]->$ev_name->is($cb_name);
        }
    }
