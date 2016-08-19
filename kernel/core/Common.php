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
     * Common Functions
     *
     * A set of functions used in all the system.
     *
     * @package		STC
     * @subpackage	Libraries
     * @category    Common Functions
     * @author		Nana Axel
     */

    if ( ! function_exists('is_ssl')) {
        /**
         * Checks if the application is under SSL
         *
         * @return bool
         */
        function is_ssl( ) {
            if ( isset ( $_SERVER['HTTPS'] )) {
                if ( 'on' == strtolower( $_SERVER['HTTPS'] ))
                    return TRUE ;
                if ( '1' == $_SERVER['HTTPS'] )
                    return TRUE ;
            }
            elseif ( isset ( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] )) {
                return TRUE ;
            }
            return FALSE ;
        }
    }

    if ( ! function_exists('base_url')) {
        /**
         * Gets the base url of the application
         *
         * Checks if the base_url config is set, otherwise guess the application
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
                if ( $script_filename_dir . '/' == $FCPATH_fix ) {
                    $path = preg_replace( '#/[^/]*$#i', '', $_SERVER['PHP_SELF'] ) ;
                }
                else {
                    if ( FALSE !== strpos( $_SERVER['SCRIPT_FILENAME'], $FCPATH_fix )) {
                        $directory = str_replace( FCPATH, '', $script_filename_dir ) ;
                        $path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '', $_SERVER['REQUEST_URI'] ) ;
                    }
                    elseif ( FALSE !== strpos( $FCPATH_fix, $script_filename_dir )) {
                        $subdirectory = substr( $FCPATH_fix, strpos( $FCPATH_fix, $script_filename_dir ) + strlen( $script_filename_dir )) ;
                        $path = preg_replace( '#/[^/]*$#i', '', $_SERVER['REQUEST_URI'] ) . $subdirectory ;
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
        function is_php($version) {
            static $_is_php;
            $version = (string) $version;

            if ( ! isset($_is_php[$version])) {
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
        * @link	https://bugs.php.net/bug.php?id=54709
        * @param	string   The path to the file to check the writability
        * @return	bool     TRUE if the file is realy writable
        */
        function is_really_writable($file) {
            // If we're on a Unix server with safe_mode off we call is_writable
            if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR ! ini_get('safe_mode'))) {
                return is_writable($file);
            }

            /* For Windows servers and safe_mode "on" installations we'll actually
            * write a file then read it. Bah...
            */
            if (is_dir($file)) {
                $file = rtrim($file, '/').'/'.md5(mt_rand());
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
        * @param	string	the class name being requested
        * @param	string	the directory where the class should be found
        * @param	string	an optional argument to pass to the class constructor
        * @return	object
        */
        function &load_class($class, $directory = 'core', $param = NULL) {
            static $_classes = array();

            // Does the class exist? If so, we're done...
            if (isset($_classes[$class])) {
                return $_classes[$class];
            }

            $name = FALSE;

            // Look for the class first in the local application/libraries folder
            // then in the native system/libraries folder
            foreach (array(APPPATH, BASEPATH) as $path) {
                if (file_exists($path.$directory.'/'.$class.'.php')) {
                    $name = 'STC_'.$class;

                    if (class_exists($name, FALSE) === FALSE) {
                        require_once($path.$directory.'/'.$class.'.php');
                    }

                    break;
                }
            }

            // Did we find the class?
            if ($name === FALSE) {
                // Note: We use exit() rather than show_error() in order to avoid a
                // self-referencing loop with the Exceptions class
                set_status_header(503);
                echo 'Unable to locate the specified class: '.$class.'.php';
                exit(5); // EXIT_UNK_CLASS
            }

            // Keep track of what we just loaded
            is_loaded($class);

            $_classes[$class] = isset($param)
                ? new $name($param)
                : new $name();
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
        function &is_loaded($class = NULL) {
            static $_is_loaded = array();

            if (isset($class)) {
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
        * @param	array
        * @return	array
        */
        function &get_config(Array $replace = array()) {
            static $config;

            if (empty($config)) {
                $file_path = APPPATH.'inc/config.php';
                $found = FALSE;
                if (file_exists($file_path)) {
                    $found = TRUE;
                    require($file_path);
                }

                // Is the config file in the environment folder?
                if (file_exists($file_path = APPPATH.'inc/'.ENVIRONMENT.'/config.php')) {
                    require($file_path);
                }
                elseif ( ! $found) {
                    set_status_header(503);
                    echo 'The configuration file does not exist.';
                    exit(3); // EXIT_CONFIG
                }

                // Does the $config array exist in the file?
                if ( ! isset($config) OR ! is_array($config)) {
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
        function config_item($item) {
            static $_config;

            if (empty($_config)) {
                // references cannot be directly assigned to static variables, so we use an array
                $_config[0] =& get_config();
            }

            return isset($_config[0][$item]) ? $_config[0][$item] : NULL;
        }
    }

    if ( ! function_exists('is_https')) {
        /**
        * Is HTTPS?
        *
        * Determines if the application is accessed via an encrypted
        * (HTTPS) connection.
        *
        * @return	bool
        */
        function is_https() {
            if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
                return TRUE;
            }
            elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                return TRUE;
            }
            elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
                return TRUE;
            }

            return FALSE;
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
        function is_cli() {
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
        * @param	string
        * @param	int
        * @param	string
        * @return	void
        */
        function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered') {
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

            $_error =& load_class('Exceptions');
            echo $_error->show_error($heading, $message, 'general', $status_code);
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
        * @param	string
        * @param	bool
        * @return	void
        */
        function show_404($page = '', $log_error = TRUE) {
            $_error =& load_class('Exceptions', 'core');
            $_error->show_404($page, $log_error);
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
            $_error =& load_class('Exceptions', 'core');
            echo $_error->show_exception($e);
            exit(4); // EXIT_UNKNOWN_FILE
        }
    }

    if ( ! function_exists('log_message')) {
        /**
        * Error Logging Interface
        *
        * We use this as a simple mechanism to access the logging
        * class and send messages to be logged.
        *
        * @param	string	the error level: 'error', 'debug' or 'info'
        * @param	string	the error message
        * @return	void
        */
        function log_message($level, $message) {
            static $_log;

            if (config_item('enable_logging') === TRUE) {
                if ($_log === NULL) {
                    // references cannot be directly assigned to static variables, so we use an array
                    $_log[0] =& load_class('Log', 'core');
                }

                $_log[0]->write_log($level, $message);
            }
        }
    }

    if ( ! function_exists('set_status_header')) {
        /**
        * Set HTTP Status Header
        *
        * @param	int	the status code
        * @param	string
        * @return	void
        */
        function set_status_header($code = 200, $text = '')
        {
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

                if (isset($stati[$code])) {
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
                $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
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
        * @param	string
        * @param	bool
        * @return	string
        */
        function remove_invisible_characters($str, $url_encoded = TRUE) {
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
        * @param	mixed	$var		The input string or array of strings to be escaped.
        * @param	bool	$double_encode	$double_encode set to FALSE prevents escaping twice.
        * @return	mixed			The escaped string or array of strings as a result.
        */
        function html_escape($var, $double_encode = TRUE) {
            if (empty($var)) {
                return $var;
            }

            if (is_array($var)) {
                return array_map('html_escape', $var, array_fill(0, count($var), $double_encode));
            }

            return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
        }
    }

    if ( ! function_exists('get_the_translation')) {
        /**
         * Return the translation of a text in the choosen lang.
         *
         * @param string $key   The key for identifying the translating
         * @param array  $param Additionals words to add in the translating
         * @param string $lang  The language to use for translating
         *
         * @return string
         */
        function get_the_translation($string, Array $param = array(), $lang = FALSE) {
            $language =& load_class('Lang', 'core');

            if (FALSE !== $lang) {
                $language->setLang($lang);
            }

            return $language->translate($string, $param);
        }
    }

    if ( ! function_exists('smarty_modifier_translate')) {
        /**
         * Return the translation of a text in a Smarty template
         *
         * @param string $key   The key for identifying the translation
         * @param array  $param Additionals words to add in the translation
         * @param string $lang  The language to use for translation
         *
         * @return string
         */
        function smarty_modifier_translate($string, array $param = array(), $lang = false) {
            return get_the_translation($string, $param, $lang);
        }
    }