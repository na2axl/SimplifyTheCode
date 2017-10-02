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
     * Route Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Router
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Route
    {
        /**
         * The list of all registered routes
         * @var  array[STC_Route]
         * @access  private
         */
        private static $routes = array();

        /**
         * The name of the route
         * @var  string
         * @access  private
         */
        private $name;

        /**
         * The route URI
         * @var  string
         * @access  private
         */
        private $route;

        /**
         * The action to execute
         * @var  string
         * @access  private
         */
        private $action;

        /**
         * Class __constructor
         *
         * @param  string  $name    The name of the route
         * @param  string  $route   The route
         * @param  mixed   $action  The action to execute
         */
        public function __construct($name, $route, $action)
        {
            $this->name   = $name;
            $this->route  = $route;
            $this->action = $action;

            if (!array_key_exists($name, self::$routes)) {
                self::$routes[$name] =& $this;
            }
        }

        /**
         * Returns the name of the current route
         *
         * @return  string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * Returns the URI of the current route
         *
         * @param  array  $params  The list of parameters
         *                         to add in the route.
         *
         * @return  string
         */
        public function getRoute(array $params = array())
        {
            if (count($params) > 0) {
                $route = preg_replace('#(\(.+\))#', '%s', $this->route);
                array_unshift($params, $route);
                return call_user_func_array('sprintf', $params);
            }
            return $this->route;
        }

        /**
         * Returns the action of the current route
         *
         * @return  mixed
         */
        public function getAction()
        {
            return $this->action;
        }

        /**
         * Returns the route URI of the given route name
         *
         * @param  string  $name    The name of the route
         * @param  array   $params  The parameters to add in the route
         *
         * @return  string
         */
        public static function getRouteOf($name, array $params = array())
        {
            if (array_key_exists($name, self::$routes)) {
                return self::$$routes[$name]->getRoute($params);
            }

            return FALSE;
        }

        /**
         * Returns the action of the given route name
         *
         * @param  string  $name  The name of the route
         *
         * @return  mixed
         */
        public static function getActionOf($name)
        {
            if (array_key_exists($name, self::$routes)) {
                return self::$routes[$name]->getAction();
            }

            return FALSE;
        }

        /**
         * Returns all registered routes
         *
         * @return  array
         */
        public static function getRoutes()
        {
            return self::$routes;
        }
    }
