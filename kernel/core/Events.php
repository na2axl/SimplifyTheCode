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
     * Events Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Events
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Events
    {

        /**
         * The array of registered events
         * @var array
         * @access private
         */
        private static $events = array();

        /**
         * Class __constructor
         */
        public function __construct()
        {
            log_message('info', 'Events Class Initialized');
        }

        /**
         * Used to call an events like properties
         * @param string $name The name of the event
         * @return STC_Event
         */
        public function __get($name)
        {
            if (array_key_exists($name, self::$events)) {
                return self::$events[$name];
            }
            else {
                return self::$events[$name] = new STC_Event($name);
            }
        }

        /**
         * Creates a new event handler
         * @param string $name The event's name
         * @return STC_Event
         */
        public function create($name)
        {
            return $this->$name;
        }

        /**
         * Deletes an event handler
         * @param array|string $name The event's name
         */
        public function delete($name)
        {
            if (is_array($name)) {
                foreach ($name as $n) {
                    $this->delete($n);
                }
            }
            else {
                if (array_key_exists($name, self::$events)) {
                    unset(self::$events[$name]);
                }
            }
        }

    }

    /**
     * Event Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Events
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Event
    {

        /**
         * A set of callbacks used by the event
         * @var array
         * @access private
         */
        private static $callbacks = array();

        /**
         * Class __constructor
         * @param string $name The event's name
         */
        public function __construct($name)
        {
            log_message('debug', "New event created with name \"{$name}\" ");
        }

        /**
         * Triggers a callback
         * @param array|string $name
         * @param array        $params
         */
        public function trigger($name = NULL, array $params = array())
        {
            is_array($params) OR $params = array($params);

            if (NULL === $name) {
                foreach (self::$callbacks as $n => $callbacks) {
                    $this->trigger($n, $params);
                }
            }
            elseif (is_array($name)) {
                foreach ($name as $n) {
                    $this->trigger($n, $params);
                }
            }
            else {
                if ($this->is($name)) {
                    foreach (self::$callbacks[$name] as $cb) {
                        call_user_func_array($cb, $params);
                    }
                }
            }
        }

        /**
         * Adds a callback to the event
         * @param string   $name
         * @param callable $callback
         * @throws STC_EventsException
         */
        public function on($name, $callback)
        {
            if (!is_callable($callback) && !function_exists($callback)) {
                throw new STC_EventsException("The event callback you want to add with the name \"{$name}\" is neither a function nor a callable item");
            }

            self::$callbacks[$name][] = $callback;
        }

        /**
         * Deletes a callback to the event
         * @param string $name
         */
        public function off($name)
        {
            if ($this->is($name)) {
                unset(self::$callbacks[$name]);
            }
        }

        /**
         * Checks if a callback exists
         * @param string $name
         * @return bool
         */
        public function is($name)
        {
            return array_key_exists($name, self::$callbacks);
        }
    }

    /**
     * Dummy class used to throw exceptions
     * @ignore
     */
    class STC_EventsException extends Exception
    {  }