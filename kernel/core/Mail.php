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
    * @package    STC
    * @author     Nana Axel <ax.lnana@outlook.com>
    * @copyright  Copyright (c) 2015 - 2016, Alien Technologies
    * @license    http://opensource.org/licenses/MIT    MIT License
    * @filesource
    */

    defined('BASEPATH') OR exit('No direct script access allowed');

    // Loading PHPMailer Classes
    require make_path( array(BASEPATH, 'lib', 'phpMailer', 'class.pop3.php') );
    require make_path( array(BASEPATH, 'lib', 'phpMailer', 'class.post.php') );
    require make_path( array(BASEPATH, 'lib', 'phpMailer', 'class.smtp.php') );
    require make_path( array(BASEPATH, 'lib', 'phpMailer', 'class.phpmailer.php') );
    require make_path( array(BASEPATH, 'lib', 'phpMailer', 'class.phpmaileroauthgoogle.php') );
    require make_path( array(BASEPATH, 'lib', 'phpMailer', 'class.phpmaileroauth.php') );

    /**
     * Mail Class
     *
     * @package     STC
     * @subpackage  Libraries
     * @category    Mails
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_Mail
    {

        /**
         * Create an instance of the class PHPMailer
         * @return PHPMailer
         */
        public function &instance($oauthSupport = FALSE)
        {
            $m = $oauthSupport ? new PHPMailerOAuth() : new PHPMailer();
            $m->setLanguage(translate('lang_name_short'));
            return $m;
        }

    }
