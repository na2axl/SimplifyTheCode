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

    // Loading the Smarty Class
    require make_path(array(BASEPATH, 'lib', 'Smarty', 'Smarty.class.php'));

    /**
     * Templates Manager Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Template
     * @author      Nana Axel
     */
    class STC_Template extends Smarty
    {

        /**
         * Class __constructor
         *
         * @return void
         */
        public function __construct()
        {
            // Instanciate Smarty
            parent::__construct();

            // Set Smarty's directories
            $this->setTemplateDir(VIEWPATH . 'templates/');
            $this->setCompileDir(VIEWPATH . 'compiled/');
            $this->setConfigDir(VIEWPATH . 'configs/');
            $this->setCacheDir(VIEWPATH . 'caches/');
            $this->addPluginsDir(VIEWPATH . 'plugins/');

            // Set caching's option value
            $this->caching = config_item('cache_views');

            // Logging Message
            log_message('info', 'Template Class Initialized');
        }

        /**
         * Template Renderer
         *
         * @param string $file The file name of the template to render
         */
        public function render($file)
        {
            if ($this->exists($file)) {
                try {
                    $file = str_replace('.tpl', '', $file);
                    $this->display("{$file}.tpl");
                } catch (Exception $e) {
                    show_exception($e);
                }
            }
            else {
                throw new RuntimeException("The template file at the path \"{$this->getDirectory()}{$file}.tpl\" doesn't exist.", 1);
            }
        }

        public function exists($file)
        {
            $file = str_replace('.tpl', '', $file);
            return $this->templateExists($file . '.tpl');
        }

        /**
         * Template directory changer
         *
         * @param $folder The subfolder to use as default template directory
         */
        public function setDirectory($folder)
        {
            $this->setTemplateDir(VIEWPATH . "templates/{$folder}/");
            return $this;
        }

        public function getDirectory()
        {
            return $this->getTemplateDir(0);
        }

    }
