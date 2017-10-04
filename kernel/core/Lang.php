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
     * @package    STC
     * @author     Nana Axel <ax.lnana@outlook.com>
     * @copyright  Copyright (c) 2015 - 2017, Alien Technologies
     * @license    http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Language Manager Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Language
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Lang
    {

        /**
         * The language used for translations
         * @var string
         * @access private
         */
        private $language;

        /**
         * An array of key-translation associations
         * @var array
         * @access private
         */
        private $langfile = array();

        /**
         * Class __constructor
         * @param  string  $default_language  The default language to use for translations.
         *                                    If no language is set, it will be the default language
         *                                    in your config file which will be loaded.
         * @throws Exception
         */
        public function __construct($default_language = NULL)
        {
            $this->language = config_item('default_lang');

            if (NULL !== $default_language && $default_language !== '') {
                $this->language = $default_language;
            }

            $this->_load();

            // Logging Message
            log_message('info', 'Language Class Initialized');
        }

        /**
         * Load a language file
         * @return void
         * @throws Exception
         */
        private function _load()
        {
            if (file_exists( $filepath = make_path(array(APPPATH, 'ln', $this->language . '.php' )) )) {
                require $filepath;
                if (isset($lang) && is_array($lang)) {
                    $this->langfile = $lang;
                    unset($lang);
                    trigger_event_callbacks('lang', 'change', array($this->language));
                }
                else {
                    show_error("The language file \"{$this->language}.php\" doesn't contain the variable \"\$lang\".");
                }
            }
            else {
                show_error("The language file \"{$this->language}.php\" can't be located in \"".APPPATH."ln/\".");
            }
        }

        /**
         * Translate a text using the text key
         * @param  string  $text_key  The text key used to search for translation.
         * @param  array   $params    Additional text to add in the translation.
         * @return string
         * @throws Exception
         */
        public function translate($text_key, array $params = array())
        {
            if (array_key_exists($text_key, $this->langfile)) {
                if (count($params) > 0) {
                    $param  = (array) $params;
                    $temp   = array();

                    $temp[0] = $this->langfile[$text_key];

                    foreach ($param as $key => $value) {
                        $temp[$key+1] = $value;
                    }

                    return  call_user_func_array('sprintf', $temp);
                }
                else {
                    return $this->langfile[$text_key];
                }
            }
            else {
                throw new RuntimeException("The language key \"{$text_key}\" don't exists in the language file {$this->language}.php");
            }
        }

        /**
         * Change the current language
         * @param  string    $new_lang  The filename (without the php extension) to load.
         * @return STC_Lang  This instance.
         * @throws Exception
         */
        public function setLang($new_lang)
        {
            $this->language = $new_lang;
            $this->_load();
            return $this;
        }

        /**
         * Return the language file currently used
         * @return array  The array of text_key => text_translation for the current language.
         */
        public function getLang()
        {
            return $this->langfile;
        }

        /**
         * Return the language id currently used
         * @return array  The array of text_key => text_translation for the current language.
         */
        public function getLangID()
        {
            return $this->language;
        }

    }
