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
     * @package     STC
     * @author      Nana Axel <ax.lnana@outlook.com>
     * @copyright   Copyright (c) 2015 - 2017, Alien Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    /**
     * System Initialization File
     *
     * Load required files and executes requests
     *
     * @package     STC
     * @subpackage  STC
     * @category    Front-Controller
     * @author      Nana Axel <ax.lnana@outlook.com>
     */

    /**
     * STC current version
     *
     * @var string
     */
    define( 'STC_VERSION', '2.5.0' );

    // --------------------------------------------------------------------
    // Loading user session
    // --------------------------------------------------------------------
    session_start();

    // --------------------------------------------------------------------
    // Loading global functions
    // --------------------------------------------------------------------
    require_once BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Common.php';

    // --------------------------------------------------------------------
    // Loading Base Controller
    // --------------------------------------------------------------------
    require_once BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';

    // --------------------------------------------------------------------
    // Loading Route Manager
    // --------------------------------------------------------------------
    require_once BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Route.php';

    // --------------------------------------------------------------------
    // Loading application functions
    // --------------------------------------------------------------------
    require_once APPPATH . 'inc' . DIRECTORY_SEPARATOR . 'common.php';

    // --------------------------------------------------------------------
    // Loading application routes
    // --------------------------------------------------------------------
    require_once APPPATH . 'inc' . DIRECTORY_SEPARATOR . 'routes.php';

    // --------------------------------------------------------------------
    // Loading application events
    // --------------------------------------------------------------------
    require_once APPPATH . 'inc' . DIRECTORY_SEPARATOR . 'events.php';

    // --------------------------------------------------------------------
    // Security issues
    // --------------------------------------------------------------------
    if (! is_php('5.4')) {
        ini_set('magic_quotes_runtime', 0);

        if ((bool) ini_get('register_globals')) {
            $_protected = array(
                '_SERVER',
                '_GET',
                '_POST',
                '_FILES',
                '_REQUEST',
                '_SESSION',
                '_ENV',
                '_COOKIE',
                'GLOBALS',
                'HTTP_RAW_POST_DATA',
                'KERNEL_PATH',
                'APP_PATH',
                'VIEW_PATH',
                '_protected',
                '_registered'
            );
            $_registered = ini_get('variables_order');
            foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $super) {
                if (strpos($_registered, $key) === FALSE) {
                    continue;
                }

                $keys = array_keys($$super);
                foreach ($keys as $var) {
                    if (array_key_exists($var, $GLOBALS) && ! in_array($var, $_protected, TRUE)) {
                        $GLOBALS[$var] = NULL;
                    }
                }
            }
        }
    }

    // --------------------------------------------------------------------
    // Loading Exceptions Manager
    // --------------------------------------------------------------------
    $STC_EXP =& load_class('Exceptions');

    // --------------------------------------------------------------------
    // Loading Logger
    // --------------------------------------------------------------------
    $STC_LOG =& load_class('Log');

    // --------------------------------------------------------------------
    // Loading Events Handler
    // --------------------------------------------------------------------
    $STC_EVT =& load_class('Events');

    // --------------------------------------------------------------------
    // Loading Configuration Manager
    // --------------------------------------------------------------------
    $STC_CNF =& load_class('Config');

    // --------------------------------------------------------------------
    // Loading Router
    // --------------------------------------------------------------------
    $STC_RTR =& load_class('Router');
    $STC_RTR->_set_routing();

    // --------------------------------------------------------------------
    // Loading Benchmark
    // --------------------------------------------------------------------
    $STC_BMK  =& load_class('Benchmark');

    // --------------------------------------------------------------------
    // Loading Template Manager
    // --------------------------------------------------------------------
    $STC_TMP =& load_class('Template');

    // --------------------------------------------------------------------
    // Loading Language Manager
    // --------------------------------------------------------------------
    $STC_LNG =& load_class('Lang');

    // --------------------------------------------------------------------
    // Loading Database Manager
    // --------------------------------------------------------------------
    $STC_ODB =& load_class('OpenDB');

    // --------------------------------------------------------------------
    // Loading Security Class
    // --------------------------------------------------------------------
    $STC_SEC =& load_class('Security');

    // --------------------------------------------------------------------
    // Loading Mailing Class
    // --------------------------------------------------------------------
    $STC_MSG =& load_class('Mail');

    // --------------------------------------------------------------------
    // Loading Model Class
    // --------------------------------------------------------------------
    $STC_MDL =& load_class('Model');

    // --------------------------------------------------------------------
    // Loading PHP_Globals Class
    // --------------------------------------------------------------------
    $STC_SSS =& load_class('PHP_Globals');

    // --------------------------------------------------------------------
    // Loading Upload Class
    // --------------------------------------------------------------------
    $STC_UPL =& load_class('Upload');

  	/**
  	 * Reference to the STC_Controller method.
  	 *
  	 * Returns current STC instance object
  	 *
  	 * @return STC_Controller
  	 */
  	function &get_controller_instance() {
  	   return STC_Controller::get_instance();
  	}

    // --------------------------------------------------------------------
    // Getting the requested page
    // --------------------------------------------------------------------
    try {
        if ($STC_CNF->item('enable_profiler')) {
            register_shutdown_function(
                function() use ($STC_BMK, $STC_CNF, $STC_RTR) {
                    if (!in_array($STC_RTR->fetch_class(), $STC_CNF->item('profiler_ignore'))) {
                        echo '<script src="' . $STC_CNF->kernel_url() . '/assets/forp/forp.min.js"></script>'
                        . '<script>'
                        . '(new forp.Controller())'
                            . '.setStack(' . $STC_BMK->dump_profiler(STC_Benchmark::DUMP_JSON) . ')'
                            . '.run();'
                        . '</script>';
                    }
                }
            );
        }

        set_error_handler(function($severity, $message, $filepath, $line, $errcontext) use ($STC_EXP) {
            echo $STC_EXP->show_php_error($severity, $message, $filepath, $line);
            exit();
        });

        $class   = $STC_RTR->fetch_class();
        $method  = $STC_RTR->fetch_method();

        header('Content-Type: text/html; charset=' . $STC_CNF->item('charset'));

        // Mark a benchmark start point
        $STC_BMK->mark('controller_execution_( ' . $class . ' / ' . $method . ' )_start');

        // Start the profiler
        $STC_BMK->start_profiler();

        include_once make_path( array( APPPATH, 'ctr', $STC_RTR->fetch_directory(), $class . '.php' ) );
        $controller = new $class();

        if (method_exists($controller, '_remap')) {
            $controller->_remap($method, array_slice($STC_RTR->rsegments, 2));
        }
        else {
            if ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($controller)), TRUE)) {
                if ( ! empty($STC_RTR->routes['404_override'])) {
                    $x = explode('/', $STC_RTR->routes['404_override']);
                    $class = ucfirst($x[0]);
                    $method = (isset($x[1]) ? $x[1] : 'index');
                    if ( ! class_exists($class)) {
                        if ( ! file_exists( APPPATH . 'ctr' . DIRECTORY_SEPARATOR . $class . '.php' )) {
                            show_404();
                        }

                        include_once APPPATH . 'ctr' . DIRECTORY_SEPARATOR . $class . '.php';
                        unset($controller);
                        $controller = new $class();
                    }
                }
                else {
                    show_404();
                }
            }

            call_user_func_array(array(&$controller, $method), array_slice($STC_RTR->rsegment_array(), 2));
        }

        // Stop the profiler
        $STC_BMK->stop_profiler();

        // Mark a benchmark end point
        $STC_BMK->mark('controller_execution_( ' . $class . ' / ' . $method . ' )_end');
    }
    catch (Exception $e) {
        show_exception($e);
    }
