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
     * Exceptions Class
     *
     * @package		STC
     * @subpackage	Libraries
     * @category	Exceptions
     * @author		Nana Axel
     */
    class STC_Exceptions {

    	/**
    	 * Nesting level of the output buffering mechanism
    	 *
    	 * @var	int
    	 */
    	public $ob_level;

    	/**
    	 * List of available error levels
    	 *
    	 * @var	array
    	 */
    	public $levels = array(
    		E_ERROR		    	=>	'Error',
    		E_WARNING   		=>	'Warning',
    		E_PARSE			    =>	'Parsing Error',
    		E_NOTICE	    	=>	'Notice',
    		E_CORE_ERROR		=>	'Core Error',
    		E_CORE_WARNING		=>	'Core Warning',
    		E_COMPILE_ERROR		=>	'Compile Error',
    		E_COMPILE_WARNING	=>	'Compile Warning',
    		E_USER_ERROR		=>	'User Error',
    		E_USER_WARNING		=>	'User Warning',
    		E_USER_NOTICE		=>	'User Notice',
    		E_STRICT    		=>	'Runtime Notice'
    	);

    	/**
    	 * Class constructor
    	 *
    	 * @return	void
    	 */
    	public function __construct() {
    		$this->ob_level = ob_get_level();
    	}

    	/**
    	 * General Error Page
    	 *
    	 * Takes an error message as input (either as a string or an array)
    	 * and displays it using the specified template.
    	 *
    	 * @param	string		$heading	Page heading
    	 * @param	string|string[]	$message	Error message
    	 * @param	string		$template	Template name
    	 * @param 	int		$status_code	(default: 500)
    	 *
    	 * @return	string	Error page output
    	 */
    	public function show_error($heading, $message, $template = 'general', $status_code = 500) {
            $template_class =& load_class('Template');
            $template_class->setDirectory('errors');

    		if ( ! is_cli()) {
    			set_status_header($status_code);
    		}

            $message = '<p class="mess">'.(is_array($message) ? implode('</p><p class="mess">', $message) : $message).'</p>';

    		if (ob_get_level() > $this->ob_level + 1) {
    			ob_end_flush();
    		}
            $template_class->assign('heading', $heading);
            $template_class->assign('message', $message);
            $template_class->assign('message', $message);
    		ob_start();
            $template_class->render($template);
    		$buffer = ob_get_contents();
    		ob_end_clean();
    		return $buffer;
    	}

    	public function show_exception(Exception $exception) {
            $template_class =& load_class('Template');
            $template_class->setDirectory('errors');

            $template = 'exception';

            $exp_type = get_class($exception);
    		$exp_mess = $exception->getMessage();
            $exp_file = $exception->getFile();
            $exp_line = $exception->getLine();

    		if (empty($message)) {
    			$message = '(null)';
    		}

    		if ( ! is_cli()) {
    			set_status_header(500);
    		}

    		if (ob_get_level() > $this->ob_level + 1) {
    			ob_end_flush();
    		}

            $template_class->assign('excp', $exception);
            $template_class->assign('type', $exp_type);
            $template_class->assign('mess', $exp_mess);
            $template_class->assign('file', $exp_file);
            $template_class->assign('line', $exp_line);
    		ob_start();
            $template_class->render($template);
    		$buffer = ob_get_contents();
    		ob_end_clean();
    		return $buffer;
    	}

        public function show_404() {
			$template_class =& load_class('Template');
            $template_class->setDirectory('errors');
            $template_class->render('404');
        }

    	/**
    	 * Native PHP error handler
    	 *
    	 * @param	int	    $severity	Error level
    	 * @param	string	$message	Error message
    	 * @param	string	$filepath	File path
    	 * @param	int	    $line		Line number
    	 * @return	string	Error page output
    	 */
    	public function show_php_error($severity, $message, $filepath, $line) {
    		$templates_path = config_item('error_views_path');
    		if (empty($templates_path))	{
    			$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
    		}

    		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

    		// For safety reasons we don't show the full file path in non-CLI requests
    		if ( ! is_cli()) {
    			$filepath = str_replace('\\', '/', $filepath);
    			if (FALSE !== strpos($filepath, '/')) {
    				$x = explode('/', $filepath);
    				$filepath = $x[count($x)-2].'/'.end($x);
    			}

    			$template = 'html'.DIRECTORY_SEPARATOR.'error_php';
    		}
    		else {
    			$template = 'cli'.DIRECTORY_SEPARATOR.'error_php';
    		}

    		if (ob_get_level() > $this->ob_level + 1) {
    			ob_end_flush();
    		}
    		ob_start();
    		include($templates_path.$template.'.php');
    		$buffer = ob_get_contents();
    		ob_end_clean();
    		echo $buffer;
    	}
    }
