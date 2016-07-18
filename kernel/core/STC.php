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

    /**
     * System Initialization File
     *
     * Load required files and executes requests
     *
     * @package     STC
     * @subpackage  STC
     * @category    Front-Controller
     * @author      Nana Axel
     */

    /**
     * STC current version
     *
     * @var string
     */
    define( 'STC_VERSION', '1.0.0' );

    // --------------------------------------------------------------------
    // Loading user session
    // --------------------------------------------------------------------
    session_start();

    // --------------------------------------------------------------------
    // Loading global functions
    // --------------------------------------------------------------------
    require_once ( BASEPATH . 'core/Common.php' );

    // --------------------------------------------------------------------
    // Loading application functions
    // --------------------------------------------------------------------
    require_once ( APPPATH . 'inc/common.php' );

    // --------------------------------------------------------------------
    // Loading application routes
    // --------------------------------------------------------------------
    require_once ( APPPATH . 'inc/routes.php' );

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
            foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal) {
                if (strpos($_registered, $key) === FALSE) {
                    continue;
                }

                foreach (array_keys($$superglobal) as $var) {
                    if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE)) {
                        $GLOBALS[$var] = NULL;
                    }
                }
            }
        }
    }

    // --------------------------------------------------------------------
    // Loading Router
    // --------------------------------------------------------------------
    $STC_RTR =& load_class('Router');
    $STC_RTR->_set_routing();

    // --------------------------------------------------------------------
    // Loading Benchmark
    // --------------------------------------------------------------------
    $STC_BM  =& load_class('Benchmark');

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
    // Loading Base Controller
    // --------------------------------------------------------------------
    require_once ( BASEPATH . 'core/Controller.php' );
	/**
	 * Reference to the STC_Controller method.
	 *
	 * Returns current STC instance object
	 *
	 * @return object
	 */
	function &get_controller_instance() {
		return STC_Controller::get_instance();
	}

    // --------------------------------------------------------------------
    // Getting the requested page
    // --------------------------------------------------------------------

    $class   = $STC_RTR->fetch_class();
    $method  = $STC_RTR->fetch_method();

	header('Content-Type: text/html; charset='.config_item('charset'));

    // Mark a benchmark start point
    $STC_BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

    include_once (APPPATH.'/ctr/'.$class.'.php');
    $thispage = new $class();

    if (method_exists($thispage, '_remap')) {
        $thispage->_remap($method, array_slice($STC_RTR->rsegments, 2));
    }
    else {
        if ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($thispage)))) {
            if ( ! empty($STC_RTR->routes['404_override'])) {
                $x = explode('/', $STC_RTR->routes['404_override']);
                $class = $x[0];
                $method = (isset($x[1]) ? $x[1] : 'index');
                if ( ! class_exists($class)) {
                    if ( ! file_exists(APPPATH.'/ctr/'.$class.'.php')) {
                        show_404();
                    }

                    include_once (APPPATH.'/ctr/'.$class.'.php');
                    unset($thispage);
                    $thispage = new $class();
                }
            }
            else {
                show_404();
            }
        }

        call_user_func_array(array(&$thispage, $method), array_slice($STC_RTR->rsegments, 2));
    }

    // Mark a benchmark end point
    $STC_BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');
