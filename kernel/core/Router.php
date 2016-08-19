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
     * Router Class
     *
     * @package		STC
     * @subpackage	Libraries
     * @category    Router
     * @author		Nana Axel
     */
    class STC_Router {

        var $directory = '';

        var $uri       = '';

        var $class     = '';

        var $method    = '';

        var $keyval    = array();

        var $segments  = array();

        var $rsegments = array();

        var $routes    = array();

        var $default_controller = '';

        function __construct($uri = false) {
            if (false !== $uri) {
                $this->uri = $uri;
                return;
            }
            else {
                $this->_fetch_uri();
            }

            // Logging Message
            log_message('info', 'Router Class Initialized');

        }

        function _set_routing() {
            $segments = array();

    		include ( APPPATH . 'inc/routes.php' );

            $this->routes = ( ! isset($routes) OR ! is_array($routes)) ? array() : $routes;
    		unset($routes);

            $this->default_controller = ( ! isset($this->routes['default']) OR $this->routes['default'] == '') ? FALSE : strtolower($this->routes['default']);

            if (count($segments) > 0) {
                return $this->_validate_request($segments);
            }

            $this->_fetch_uri();

            if ($this->uri == '') {
                return $this->_set_default_controller();
            }

            $this->_explode_segments();
            $this->_parse_routes();
            $this->_reindex_segments();
        }

        function _explode_segments() {
            foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri)) as $val) {
                $val = trim($this->_filter_uri($val));

                if ($val != '') {
                    $this->segments[] = $val;
                }
            }
        }

        function _filter_uri($str) {
            $bad	= array('$',		'(',		')',		'%28',		'%29');
            $good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');

            return str_replace($bad, $good, $str);
        }

        private function _fetch_uri() {
            if (php_sapi_name() == 'cli' or defined('STDIN')) {
                $this->_set_uri($this->_parse_cli_args());
                return;
            }

            if ($uri = $this->_detect_uri()) {
                $this->_set_uri($uri);
                return;
            }

            $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
            if (trim($path, '/') != '' && $path != "/".SELF) {
                $this->_set_uri($path);
                return;
            }

            $path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
            if (trim($path, '/') != '') {
                $this->_set_uri($path);
                return;
            }

            if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '') {
                $this->_set_uri(key($_GET));
                return;
            }

            $this->uri = '';
            return;
        }

        public function _set_uri($string) {
            $string = remove_invisible_characters($string, false);
            $this->uri = ($string == '/') ? '' : $string;
        }

        private function _detect_uri() {
            if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME'])) {
                return '';
            }

            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
                $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            }
            elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
                $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
            }

            if (strncmp($uri, '?/', 2) === 0) {
                $uri = substr($uri, 2);
            }

            $parts = preg_split('#\?#i', $uri, 2);
            $uri = $parts[0];
            if (isset($parts[1])) {
                $_SERVER['QUERY_STRING'] = $parts[1];
                parse_str($_SERVER['QUERY_STRING'], $_GET);
            }
            else {
                $_SERVER['QUERY_STRING'] = '';
                $_GET = array();
            }

            if ($uri == '/' || empty($uri)) {
                return '/';
            }

            $uri = parse_url($uri, PHP_URL_PATH);

            return str_replace(array('//', '../'), '/', trim($uri, '/'));

        }

        private function _parse_cli_args() {
            $args = array_slice($_SERVER['argv'], 1);

            return $args ? '/' . implode('/', $args) : '';
        }

        public function get_uri() {
            return $this->uri;
        }

        private function _uri_to_segments() {
            $this->segments = explode('/', $this->uri);
        }

        private function _parse_routes() {
            $uri = implode('/', $this->segments);

            if (isset($this->routes[$uri])) {
                return $this->_set_request(explode('/', $this->routes[$uri]));
            }

            foreach ($this->routes as $key => $val) {
                $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', str_replace(':str', '[a-z0-9-_\.]+', $key)));
                if (preg_match('#^'.$key.'$#', $uri)) {
                    if (strpos($val, '$') !== false && strpos($key, '(') !== false) {
                        $val = preg_replace('#^'.$key.'$#', $val, $uri);
                    }
                    return $this->_set_request(explode('/', $val));
                }
            }

            $this->_set_request($this->segments);
        }

        private function _set_request($segments = array()) {
            $segments = $this->_validate_request($segments);

            if (count($segments) == 0) {
                return $this->_set_default_controller();
            }

            $this->set_class($segments[0]);

            if (isset($segments[1])) {
                $this->set_method($segments[1]);
            }
            else {
                $segments[1] = 'index';
                $this->set_method($segments[1]);
            }

            $this->rsegments = $segments;
        }

        private function _validate_request($segments) {
            if (count($segments) == 0) {
                return $segments;
            }

            if (file_exists(APPPATH.'/ctr/'.$segments[0].'.php')) {
                return $segments;
            }

            if (is_dir(APPPATH.'/ctr/'.$segments[0])) {
                $this->set_directory($segments[0]);
                $segments = array_slice($segments, 1);

                if (count($segments) > 0) {
                    if ( ! file_exists(APPPATH.'/ctr/'.$this->fetch_directory().$segments[0].'.php')) {
                        if ( ! empty($this->routes['404_override'])) {
                            $x = explode('/', $this->routes['404_override']);
                            $this->set_directory('');
                            $this->set_class($x[0]);
                            $this->set_method(isset($x[1]) ? $x[1] : 'index');

                            return $x;
                        }
                        else {
                            show_404();
                        }
                    }
                }
                else {
                    if (strpos($this->default_controller, '/') !== false) {
                        $x = explode('/', $this->default_controller);
                        $this->set_class($x[0]);
                        $this->set_method($x[1]);
                    }
                    else {
                        $this->set_class($this->default_controller);
                        $this->set_method('index');
                    }

                    if ( ! file_exists(APPPATH.'/ctr/'.$this->fetch_directory().$this->default_controller.'.php')) {
                        $this->directory = '';
                        return array();
                    }

                }

                return $segments;
            }

            if ( ! empty($this->routes['404_override'])) {
                $x = explode('/', $this->routes['404_override']);

                $this->set_class($x[0]);
                $this->set_method(isset($x[1]) ? $x[1] : 'index');

                return $x;
            }

            show_404();
        }

        private function _set_default_controller() {
            if (strpos($this->routes['default'], '/') !== false) {
                $x = explode('/', $this->routes['default']);

                $this->set_class($x[0]);
                $this->set_method($x[1]);
                $this->_set_request($x);
            }
            else {
                $this->set_class($this->routes['default']);
                $this->set_method('index');
                $this->_set_request(array($this->routes['default'], 'index'));
            }

            $this->_reindex_segments();

        }

        private function _uri_to_assoc($n = 3, $default = array(), $which = 'segment') {
            $total_segments = "total_{$which}s";
            $segment_array = "{$which}_array";

            if ( ! is_numeric($n)) {
                return $default;
            }

            if (isset($this->keyval[$n])) {
                return $this->keyval[$n];
            }

            if ($this->$total_segments() < $n) {
                if (count($default) == 0) {
                    return array();
                }

                $retval = array();
                foreach ($default as $val) {
                    $retval[$val] = FALSE;
                }
                return $retval;
            }

            $segments = array_slice($this->$segment_array(), ($n - 1));

            $i = 0;
            $lastval = '';
            $retval  = array();
            foreach ($segments as $seg) {
                if ($i % 2) {
                    $retval[$lastval] = $seg;
                }
                else {
                    $retval[$seg] = FALSE;
                    $lastval = $seg;
                }

                $i++;
            }

            if (count($default) > 0) {
                foreach ($default as $val) {
                    if ( ! array_key_exists($val, $retval)) {
                        $retval[$val] = FALSE;
                    }
                }
            }

            // Cache the array for reuse
            $this->keyval[$n] = $retval;
            return $retval;
        }

        public function total_segments() {
            return count($this->segments);
        }

        public function total_rsegments() {
            return count($this->rsegments);
        }

        public function segment_array() {
            return $this->segments;
        }

        public function rsegment_array() {
            return $this->rsegments;
        }

        public function uri_to_assoc($n = 3, $default = array()) {
            return $this->_uri_to_assoc($n, $default, 'segment');
        }

        public function ruri_to_assoc($n = 3, $default = array()) {
            return $this->_uri_to_assoc($n, $default, 'rsegment');
        }

        public function assoc_to_uri($array, $n = 3) {
            $temp = array();
            $tget = array();
            foreach ((array)$array as $key => $val) {
                $temp[] = $key;
                $temp[] = $val;
            }
            foreach ((array)$_GET  as $key => $val) {
                $tget[] = $key.'='.$val;
            }
            $segments = array_slice($this->segments, 0, $n-1);
            if (count($_GET) > 0)
                return implode('/', array_merge($segments, $temp)).'/?'.implode('&', $tget);
            else
                return implode('/', array_merge($segments, $temp));
        }

        private function _reindex_segments() {
            array_unshift($this->segments, NULL);
            array_unshift($this->rsegments, NULL);
            unset($this->segments[0]);
            unset($this->rsegments[0]);
        }

        public function set_class($class) {
            $this->class = str_replace(array('/', '.'), '', $class);
        }

        public function set_method($method) {
            $this->method = $method;
        }

        public function set_directory($dir) {
            $this->directory = str_replace(array('/', '.'), '', $dir).'/';
        }

        public function fetch_class() {
            return ucfirst($this->class);
        }

        public function fetch_directory() {
            return $this->directory;
        }

        public function fetch_method() {
            if ($this->method == $this->fetch_class()) {
                return 'index';
            }

            return $this->method;
        }

        function _set_overrides($routing) {
            if ( ! is_array($routing)) {
                return;
            }

            if (isset($routing['directory'])) {
                $this->set_directory($routing['directory']);
            }

            if (isset($routing['controller']) AND $routing['controller'] != '') {
                $this->set_class($routing['controller']);
            }

            if (isset($routing['function'])) {
                $routing['function'] = ($routing['function'] == '') ? 'index' : $routing['function'];
                $this->set_method($routing['function']);
            }
        }

    }
