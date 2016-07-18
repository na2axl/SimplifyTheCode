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
     * Base Model Class
     *
     * @package		STC
     * @subpackage	Models
     * @category    Base Model
     * @author		Nana Axel
     */
     class STC_Model {

        /**
         * Store the name of loaded models
         *
         * @var  array
         * @access  private
         */
        private $_stc_models = array();

        /**
         * Class constructor
         *
         * @return void
         */
        public function __construct() {
            log_message('info', 'Model class Initialized');
        }

        /**
         * __get() Magic method
         *
         * Allows models to use STC's loaded classes
         * with the same syntax as controllers.
         *
         * @param  string  $class  The class to load
         *
         * @return object
         */
        public function __get($class) {
            $class = strtolower($class);
            return get_controller_instance()->$class;
        }

        /**
         * Model Loader
         *
         * Loads and instantiates models.
         *
         * @param	string	$model		Model name
         * @param	string	$name		An optional object name to assign to
         * @param	bool	$db_conn	An optional database connection configuration to initialize
         * @return	object
         */
        public function load($model, $name = '') {
            if (empty($model)) {
                return $this;
            } elseif (is_array($model)) {
                foreach ($model as $key => $value) {
                    is_int($key) ? $this->load($value, '') : $this->load($key, $value);
                }
                return $this;
            }

            $path = '';

            // Is the model in a sub-folder? If so, parse out the filename and path.
            if (($last_slash = strrpos($model, '/')) !== FALSE) {
                // The path is in front of the last slash
                $path = substr($model, 0, ++$last_slash);

                // And the model name behind it
                $model = substr($model, $last_slash);
            }

            if (empty($name)) {
                $name = $model;
            }

            if (in_array($name, $this->_stc_models, TRUE)) {
                return $this;
            }

            $STC =& get_controller_instance();
            if (isset($STC->$name)) {
                throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: '.$name);
            }

            $model = ucfirst(strtolower($model));
            $class = 'MDL_'.$model;
            if ( ! class_exists($class)) {
                if ( ! file_exists(APPPATH.'mdl/'.$path.$model.'.php')) {
                    throw new RuntimeException('Unable to locate the model you have specified: '.$model);
                }

                require_once(APPPATH.'mdl/'.$path.$model.'.php');

                if ( ! class_exists($class, FALSE)) {
                    throw new RuntimeException(APPPATH."mdl/".$path.$model.".php exists, but doesn't declare class ".$model);
                }
            } elseif ( ! is_subclass_of($class, 'STC_Model')) {
                throw new RuntimeException("Class ".$model." already exists and doesn't extend STC_Model");
            }

            $this->_stc_models[] = $name;
            $STC->$name = new $class();
            return $this;
        }

     }