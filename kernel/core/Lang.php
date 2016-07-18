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
     * Language Manager Class
     *
     * @package		STC
     * @subpackage	Librairies
     * @category    Language
     * @author		Nana Axel
     */
    class STC_Lang {

        /**
         * The language used for translations
         *
         * @var string
         * @access private
         */
        private $language;

        /**
         * An array of key-translation associations
         *
         * @var array
         * @access private
         */
        private $langfile = array();

        /**
         * Class __constructor
         *
         * @param  string  $default_language  The default language to use for translations.
         *                                    If no language is set, it will be the default language
         *                                    in your config file which will be loaded.
         *
         * @return void
         */
        public function __construct($default_language = null) {

            $this->language = config_item('default_lang');

            if (isset($default_language) && !empty($default_language)) {
                $this->language = $default_language;
            }

            $this->_load();

            // Logging Message
            log_message('info', 'Language Class Initialized');

        }

        /**
         * Load a language file
         *
         * @return void
         *
         * @throws STC_LangException
         */
        private function _load() {

            if (file_exists( APPPATH . 'ln/'.$this->language.'.php' )) {
                require_once ( APPPATH . 'ln/'.$this->language.'.php' );
                $this->langfile = $LANG;
            }
            else {
                throw new STC_LangException("The language file {$this->language}.php can't be located in \"".APPPATH."ln/\"", 1);
            }

        }

        public function translate($text_key, $params = null) {

            if (isset($this->langfile[$text_key])) {
                if (isset($params) && !empty($params)) {
                    $param  = (array) $params;
                    $temp   = array();

                    $temp[0] = $this->langfile[$text_key];

                    foreach ($param as $key => $value) {
                        $temp[$key+1] = $value;
                    }

                    return  call_user_func_array('sprintf', $temp);
                } else {
                    return $this->langfile[$text_key];
                }
            } else {
                throw new STC_LangException("The language key \"{$text_key}\" don't exists in the language file {$this->language}.php", 1);
            }

        }

        public function setLang($new_lang) {

            $this->language = $new_lang;
            $this->_load();

        }

        public function getLang() {

            return $this->langfile;

        }

    }

    /**
     * Dummy class used to throw exceptions
     *
     * @package		STC
     * @subpackage	Librairies
     * @category	Language
     * @author		Nana Axel
     * @ignore
     */
    class STC_LangException extends Exception {}
