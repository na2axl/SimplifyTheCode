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
     * APPLICATION LOADER
     *
     * You can edit this file to change default
     * values of the environment.
     */

    /**
     * APPLICATION ENVIRONMENT
     *
     * Edit this constant to load different configurations depending
     * on your current environment. This constant can be set to anything
     * but default values are :
     *  -- testing
     *  -- development
     *  -- production
     */
    if (! defined('ENVIRONMENT')) {
        define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
    }

    /**
     * ERROR REPORTING
     *
     * According to your current environment, we have to change the level of
     * the error_reporting() function.
     */
    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(-1);
            ini_set('display_errors', 1);
        break;

        case 'testing':
        case 'production':
            ini_set('display_errors', 0);
            if (version_compare(PHP_VERSION, '5.3', '>=')) {
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            }
            else {
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
            }
        break;

        default:
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'The application environment is not set correctly.';
            exit(1); // EXIT_ERROR
        break;
    }

    /**
     * KERNEL FOLDER PATH
     *
     * This variable have to contain the path to the "kernel" folder. You can
     * enter only the name of the folder if this one is in the same folder than
     * this front controller.
     */
    $KERNEL_PATH = "kernel";

    /**
     * APPLICATION FOLDER PATH
     *
     * This variable have to contain the path to the "app" folder which this front
     * controller will use to render pages. You can enter only the name of the folder
     * if this one is in the same folder than this front controller.
     */
    $APP_PATH = "app";

    /**
     * VIEW FOLDER PATH
     *
     * By default, the "view" folder is in the "app" folder. But if you want to use
     * another "view" folder, you have to enter the path of this folder in this
     * variable. Otherwise leave it as it.
     */
    $VIEW_PATH = '';


    // --------------------------------------------------------------------
    // END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
    // --------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------

    // --------------------------------------------------------------------
    // SETTINGS PATH CONSTANTS
    // --------------------------------------------------------------------

    // THIS file name
    define( 'SELF', pathinfo(__FILE__, PATHINFO_BASENAME) );

    // Kernel folder path
    define( 'BASEPATH', str_replace('\\', '/', $KERNEL_PATH).DIRECTORY_SEPARATOR );

    // Path to the front controller (this file)
    define( 'FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR );

    // Smarty folder directory
    define( 'SMARTY_DIR', BASEPATH . 'lib/Smarty/' );

    // Application folder path
    if (is_dir($APP_PATH)) {
        if (($path = realpath($APP_PATH)) !== FALSE) {
            $APP_PATH = $path;
        }
        define( 'APPPATH',  str_replace('\\', '/', $APP_PATH).DIRECTORY_SEPARATOR );
    }
    else {
        if (! is_dir(FCPATH.$APP_PATH.DIRECTORY_SEPARATOR)) {
			header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
			echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
			exit(3); // EXIT_CONFIG
        }
        define( 'APPPATH', FCPATH.$APP_PATH.DIRECTORY_SEPARATOR );
    }

	// Views folder path
	if (! is_dir($VIEW_PATH)) {
		if (! empty($VIEW_PATH) && is_dir(APPPATH.$VIEW_PATH.DIRECTORY_SEPARATOR)) {
			$VIEW_PATH = APPPATH.$VIEW_PATH;
		}
		elseif (! is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR)) {
			header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
			echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
			exit(3); // EXIT_CONFIG
		}
		else {
			$VIEW_PATH = APPPATH.'views';
		}
	}

	if (($path = realpath($VIEW_PATH)) !== FALSE) {
		$VIEW_PATH = $path.DIRECTORY_SEPARATOR;
	}
	else {
		$VIEW_PATH = rtrim($VIEW_PATH, '/\\').DIRECTORY_SEPARATOR;
	}

	define('VIEWPATH', $VIEW_PATH);


    // --------------------------------------------------------------------
    // LOADING THE LOADER FILE
    // --------------------------------------------------------------------
    // And start the fun...
    // --------------------------------------------------------------------

    require_once ( BASEPATH . 'core/STC.php' );

?>