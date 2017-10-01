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
     * @copyright   Copyright (c) 2015 - 2016, Alien Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Config File Manager Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Library
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Config
    {

        /**
         * List of all loaded config values
         * @var array
         * @access private
         */
        private $config = array();

        /**
         * List of all loaded config files
         * @var array
         * @access private
         */
        private $is_loaded = array();

        /**
         * List of paths to search when trying to load a config file.
         * @var array
         * @access private
         */
        private $_config_paths = array(APPPATH);

        /**
         * Class __constructor
         * Sets the $config data from the primary config.php file as a class variable.
         */
        public function __construct()
        {
            $this->config =& get_config();

            // Set the base_url automatically if none was provided
            if (empty($this->config['base_url'])) {
                $this->set_item('base_url', base_url());
            }

            // Logging
            log_message('info', 'Config Class Initialized');
        }

        /**
         * Load Config File
         * @param   string  $file             Configuration file name
         * @param   bool    $use_sections     Whether configuration values should be loaded into their own section
         * @param   bool    $fail_gracefully  Whether to just return FALSE or display an error message
         * @return  bool    TRUE if the file was loaded correctly or FALSE on failure
         */
        public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
        {
            $file = ($file === '') ? 'config' : str_replace('.php', '', $file);
            $loaded = FALSE;

            foreach ($this->_config_paths as $path) {
                foreach (array($file, make_path(array(ENVIRONMENT, $file))) as $location) {
                    $file_path = make_path(array($path, 'inc', $location . '.php'));
                    if (!file_exists($file_path)) {
                        continue;
                    }

                    if (in_array($file_path, $this->is_loaded, TRUE)) {
                        return TRUE;
                    }

                    include $file_path;

                    if (!isset($config) OR !is_array($config)) {
                        if ($fail_gracefully === TRUE) {
                            return FALSE;
                        }

                        show_error("Your \"{$file_path}\" file does not appear to contain a valid configuration array.");
                    }

                    if ($use_sections === TRUE) {
                        $this->config[$file] = array_key_exists($file, $this->config) ? array_merge($this->config[$file], $config) : $config;
                    } else {
                        $this->config = array_merge($this->config, $config);
                    }

                    $this->is_loaded[] = $file_path;
                    unset($config);

                    $loaded = TRUE;
                    log_message('debug', 'Config file loaded: ' . $file_path);
                }
            }

            if ($loaded === TRUE) {
                return TRUE;
            }
            if ($fail_gracefully === TRUE) {
                return FALSE;
            }

            show_error("The configuration file  \"{$file}.php\" does not exist.");
        }

        /**
         * Fetch a config file item
         * @param   string  $item   Config item name
         * @param   string  $index  Index name
         * @return  string|null     The configuration item or NULL if the item doesn't exist
         */
        public function item($item, $index = '')
        {
            if ($index === '') {
                return array_key_exists($item, $this->config) ? $this->config[$item] : NULL;
            }

            return (array_key_exists($index, $this->config) && array_key_exists($item, $this->config[$index])) ? $this->config[$index][$item] : NULL;
        }

        /**
         * Fetch a config file item with slash appended (if not empty)
         * @param   string  $item  Config item name
         * @return  string|null    The configuration item or NULL if the item doesn't exist
         */
        public function slash_item($item)
        {
            if (!array_key_exists($item, $this->config) || $this->config[$item] === NULL) {
                return NULL;
            } elseif (trim($this->config[$item]) === '') {
                return '';
            }

            return rtrim($this->config[$item], '/') . '/';
        }

        /**
         * Application URL
         * @return  string
         */
        public function app_url()
        {
            $x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', APPPATH));
            return rtrim($this->slash_item('base_url') . end($x), DIRECTORY_SEPARATOR) . '/';
        }

        /**
         * Kernel URL
         * @deprecated  1.0.0  Encourages insecure practices
         * @return  string
         */
        public function kernel_url()
        {
            $x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));
            return rtrim($this->slash_item('base_url') . end($x), DIRECTORY_SEPARATOR) . '/';
        }

        /**
         * Base URL
         * Returns base_url [. uri_string]
         * @uses    STC_Config::_uri_string()
         * @param   string|string[] $uri       URI string or an array of segments
         * @param   string          $protocol
         * @return  string
         */
        public function base_url($uri = '', $protocol = NULL)
        {
            $base_url = $this->slash_item('base_url');

            if (NULL !== $protocol) {
                $base_url = $protocol . substr($base_url, strpos($base_url, '://'));
            }

            return $base_url . ltrim($this->_uri_string($uri), '/');
        }

        /**
         * Build URI string
         * @used-by  STC_Config::site_url()
         * @used-by  STC_Config::base_url()
         * @param    string|string[]  $uri  URI string or an array of segments
         * @return   string
         * @access   protected
         */
        protected function _uri_string($uri)
        {
            if ($this->item('enable_query_strings') === FALSE) {
                if (is_array($uri)) {
                    $uri = implode('/', $uri);
                }
                return trim($uri, '/');
            } else {
                if (is_array($uri)) {
                    return http_build_query($uri);
                }
            }

            return $uri;
        }

        /**
         * Set a config file item
         * @param    string $item Config item key
         * @param    string $value Config item value
         * @return    void
         */
        public function set_item($item, $value)
        {
            $this->config[$item] = $value;
        }

    }
