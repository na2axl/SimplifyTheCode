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
     * Database Manager Class
     *
     * @package		STC
     * @subpackage	Librairies
     * @category    Security
     * @author		Nana Axel
     */
    class STC_Security {

        /**
        * List of sanitize filename strings
        *
        * @var	array
        */
        public $filename_bad_chars =	array(
            '../', '<!--', '-->', '<', '>',
            "'", '"', '&', '$', '#',
            '{', '}', '[', ']', '=',
            ';', '?', '%20', '%22',
            '%3c',		// <
            '%253c',	// <
            '%3e',		// >
            '%0e',		// >
            '%28',		// (
            '%29',		// )
            '%2528',	// (
            '%26',		// &
            '%24',		// $
            '%3f',		// ?
            '%3b',		// ;
            '%3d'		// =
        );

        /**
        * Character set
        *
        * Will be overridden by the constructor.
        *
        * @var	string
        */
        public $charset = 'UTF-8';

        /**
        * XSS Hash
        *
        * Random Hash for protecting URLs.
        *
        * @var	string
        */
        protected $_xss_hash;

        /**
        * List of never allowed strings
        *
        * @var	array
        */
        protected $_never_allowed_str =	array(
            'document.cookie'	=> '[removed]',
            'document.write'	=> '[removed]',
            '.parentNode'		=> '[removed]',
            '.innerHTML'		=> '[removed]',
            '-moz-binding'		=> '[removed]',
            '<!--'				=> '&lt;!--',
            '-->'				=> '--&gt;',
            '<![CDATA['			=> '&lt;![CDATA[',
            '<comment>'			=> '&lt;comment&gt;'
        );

        /**
        * List of never allowed regex replacements
        *
        * @var	array
        */
        protected $_never_allowed_regex = array(
            'javascript\s*:',
            '(document|(document\.)?window)\.(location|on\w*)',
            'expression\s*(\(|&\#40;)', // CSS and IE
            'vbscript\s*:', // IE, surprise!
            'wscript\s*:', // IE
            'jscript\s*:', // IE
            'vbs\s*:', // IE
            'Redirect\s+30\d',
            "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
        );

        /**
        * Class constructor
        *
        * @return	void
        */
        public function __construct() {
            $this->charset = strtoupper(config_item('charset'));
        }

        // --------------------------------------------------------------------

        /**
        * XSS Clean
        *
        * Sanitizes data so that Cross Site Scripting Hacks can be
        * prevented.  This method does a fair amount of work but
        * it is extremely thorough, designed to prevent even the
        * most obscure XSS attempts.  Nothing is ever 100% foolproof,
        * of course, but I haven't been able to get anything passed
        * the filter.
        *
        * Note: Should only be used to deal with data upon submission.
        *	 It's not something that should be used for general
        *	 runtime processing.
        *
        * @link	http://channel.bitflux.ch/wiki/XSS_Prevention
        * 		Based in part on some code and ideas from Bitflux.
        *
        * @link	http://ha.ckers.org/xss.html
        * 		To help develop this script I used this great list of
        *		vulnerabilities along with a few other hacks I've
        *		harvested from examining vulnerabilities in other programs.
        *
        * @param	string|string[]	$str		Input data
        * @param 	bool		$is_image	Whether the input is an image
        * @return	string
        */
        public function xss_clean($str, $is_image = FALSE)
        {
            // Is the string an array?
            if (is_array($str))
            {
                while (list($key) = each($str))
                {
                    $str[$key] = $this->xss_clean($str[$key]);
                }

                return $str;
            }

            // Remove Invisible Characters
            $str = remove_invisible_characters($str);

            // Remove script tags
            $str = preg_replace('#<script>(.+)</script>#isU', '', $str);

            /*
            * URL Decode
            *
            * Just in case stuff like this is submitted:
            *
            * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
            *
            * Note: Use rawurldecode() so it does not remove plus signs
            */
            do
            {
                $str = rawurldecode($str);
            }
            while (preg_match('/%[0-9a-f]{2,}/i', $str));

            /*
            * Convert character entities to ASCII
            *
            * This permits our tests below to work reliably.
            * We only convert entities that are within tags since
            * these are the ones that will pose security problems.
            */
            $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $str);
            $str = preg_replace_callback('/<\w+.*/si', array($this, '_decode_entity'), $str);

            // Remove Invisible Characters Again!
            $str = remove_invisible_characters($str);

            /*
            * Convert all tabs to spaces
            *
            * This prevents strings like this: ja	vascript
            * NOTE: we deal with spaces between characters later.
            * NOTE: preg_replace was found to be amazingly slow here on
            * large blocks of data, so we use str_replace.
            */
            $str = str_replace("\t", '    ', $str);

            // Capture converted string for later comparison
            $converted_string = $str;

            // Remove Strings that are never allowed
            $str = $this->_do_never_allowed($str);

            /*
            * Makes PHP tags safe
            *
            * Note: XML tags are inadvertently replaced too:
            *
            * <?xml
            *
            * But it doesn't seem to pose a problem.
            */
            if ($is_image === TRUE)
            {
                // Images have a tendency to have the PHP short opening and
                // closing tags every so often so we skip those and only
                // do the long opening tags.
                $str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
            }
            else
            {
                $str = str_replace(array('<?', '?'.'>'), array('&lt;?', '?&gt;'), $str);
            }

            /*
            * Compact any exploded words
            *
            * This corrects words like:  j a v a s c r i p t
            * These words are compacted back to their correct state.
            */
            $words = array(
                'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
                'vbs', 'script', 'base64', 'applet', 'alert', 'document',
                'write', 'cookie', 'window', 'confirm', 'prompt'
            );

            foreach ($words as $word)
            {
                $word = implode('\s*', str_split($word)).'\s*';

                // We only want to do this when it is followed by a non-word character
                // That way valid stuff like "dealer to" does not become "dealerto"
                $str = preg_replace_callback('#('.substr($word, 0, -3).')(\W)#is', array($this, '_compact_exploded_words'), $str);
            }

            /*
            * Remove disallowed Javascript in links or img tags
            * We used to do some version comparisons and use of stripos(),
            * but it is dog slow compared to these simplified non-capturing
            * preg_match(), especially if the pattern exists in the string
            *
            * Note: It was reported that not only space characters, but all in
            * the following pattern can be parsed as separators between a tag name
            * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
            * ... however, remove_invisible_characters() above already strips the
            * hex-encoded ones, so we'll skip them below.
            */
            do
            {
                $original = $str;

                if (preg_match('/<a/i', $str))
                {
                    $str = preg_replace_callback('#<a[^a-z0-9>]+([^>]*?)(?:>|$)#si', array($this, '_js_link_removal'), $str);
                }

                if (preg_match('/<img/i', $str))
                {
                    $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', array($this, '_js_img_removal'), $str);
                }

                if (preg_match('/script|xss/i', $str))
                {
                    $str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);
                }
            }
            while ($original !== $str);

            unset($original);

            // Remove evil attributes such as style, onclick and xmlns
            $str = $this->_remove_evil_attributes($str, $is_image);

            /*
            * Sanitize naughty HTML elements
            *
            * If a tag containing any of the words in the list
            * below is found, the tag gets converted to entities.
            *
            * So this: <blink>
            * Becomes: &lt;blink&gt;
            */
            $naughty = 'alert|prompt|confirm|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|button|select|isindex|layer|link|meta|keygen|object|plaintext|style|script|textarea|title|math|video|svg|xml|xss';
            $str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', array($this, '_sanitize_naughty_html'), $str);

            /*
            * Sanitize naughty scripting elements
            *
            * Similar to above, only instead of looking for
            * tags it looks for PHP and JavaScript commands
            * that are disallowed. Rather than removing the
            * code, it simply converts the parenthesis to entities
            * rendering the code un-executable.
            *
            * For example:	eval('some code')
            * Becomes:	eval&#40;'some code'&#41;
            */
            $str = preg_replace('#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
                        '\\1\\2&#40;\\3&#41;',
                        $str);

            // Final clean up
            // This adds a bit of extra precaution in case
            // something got through the above filters
            $str = $this->_do_never_allowed($str);

            /*
            * Images are Handled in a Special Way
            * - Essentially, we want to know that after all of the character
            * conversion is done whether any unwanted, likely XSS, code was found.
            * If not, we return TRUE, as the image is clean.
            * However, if the string post-conversion does not matched the
            * string post-removal of XSS, then it fails, as there was unwanted XSS
            * code found and removed/changed during processing.
            */
            if ($is_image === TRUE)
            {
                return ($str === $converted_string);
            }

            return $str;
        }

        // --------------------------------------------------------------------

        /**
        * XSS Hash
        *
        * Generates the XSS hash if needed and returns it.
        *
        * @see		STC_Security::$_xss_hash
        * @return	string	XSS hash
        */
        public function xss_hash()
        {
            if ($this->_xss_hash === NULL)
            {
                $rand = $this->get_random_bytes(16);
                $this->_xss_hash = ($rand === FALSE)
                    ? md5(uniqid(mt_rand(), TRUE))
                    : bin2hex($rand);
            }

            return $this->_xss_hash;
        }

        // --------------------------------------------------------------------

        /**
        * Get random bytes
        *
        * @param	int	$length	Output length
        * @return	string
        */
        public function get_random_bytes($length)
        {
            if (empty($length) OR ! ctype_digit((string) $length))
            {
                return FALSE;
            }

            // Unfortunately, none of the following PRNGs is guaranteed to exist ...
            if (defined('MCRYPT_DEV_URANDOM') && ($output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) !== FALSE)
            {
                return $output;
            }


            if (is_readable('/dev/urandom') && ($fp = fopen('/dev/urandom', 'rb')) !== FALSE)
            {
                // Try not to waste entropy ...
                is_php('5.4') && stream_set_chunk_size($fp, $length);
                $output = fread($fp, $length);
                fclose($fp);
                if ($output !== FALSE)
                {
                    return $output;
                }
            }

            if (function_exists('openssl_random_pseudo_bytes'))
            {
                return openssl_random_pseudo_bytes($length);
            }

            return FALSE;
        }

        // --------------------------------------------------------------------

        /**
        * HTML Entities Decode
        *
        * A replacement for html_entity_decode()
        *
        * The reason we are not using html_entity_decode() by itself is because
        * while it is not technically correct to leave out the semicolon
        * at the end of an entity most browsers will still interpret the entity
        * correctly. html_entity_decode() does not convert entities without
        * semicolons, so we are left with our own little solution here. Bummer.
        *
        * @link	http://php.net/html-entity-decode
        *
        * @param	string	$str		Input
        * @param	string	$charset	Character set
        * @return	string
        */
        public function entity_decode($str, $charset = NULL)
        {
            if (strpos($str, '&') === FALSE)
            {
                return $str;
            }

            static $_entities;

            isset($charset) OR $charset = $this->charset;
            $flag = phpversion() == '5.4'
                ? ENT_COMPAT | ENT_HTML5
                : ENT_COMPAT;

            do
            {
                $str_compare = $str;

                // Decode standard entities, avoiding false positives
                if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches))
                {
                    if ( ! isset($_entities))
                    {
                        $_entities = array_map(
                            'strtolower',
                            phpversion() == '5.3.4'
                                ? get_html_translation_table(HTML_ENTITIES, $flag, $charset)
                                : get_html_translation_table(HTML_ENTITIES, $flag)
                        );

                        // If we're not on PHP 5.4+, add the possibly dangerous HTML 5
                        // entities to the array manually
                        if ($flag === ENT_COMPAT)
                        {
                            $_entities[':'] = '&colon;';
                            $_entities['('] = '&lpar;';
                            $_entities[')'] = '&rpar;';
                            $_entities["\n"] = '&newline;';
                            $_entities["\t"] = '&tab;';
                        }
                    }

                    $replace = array();
                    $matches = array_unique(array_map('strtolower', $matches[0]));
                    foreach ($matches as &$match)
                    {
                        if (($char = array_search($match.';', $_entities, TRUE)) !== FALSE)
                        {
                            $replace[$match] = $char;
                        }
                    }

                    $str = str_ireplace(array_keys($replace), array_values($replace), $str);
                }

                // Decode numeric & UTF16 two byte entities
                $str = html_entity_decode(
                    preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                    $flag,
                    $charset
                );
            }
            while ($str_compare !== $str);
            return $str;
        }

        // --------------------------------------------------------------------

        /**
        * Sanitize Filename
        *
        * @param	string	$str		Input file name
        * @param 	bool	$relative_path	Whether to preserve paths
        * @return	string
        */
        public function sanitize_filename($str, $relative_path = FALSE)
        {
            $bad = $this->filename_bad_chars;

            if ( ! $relative_path)
            {
                $bad[] = './';
                $bad[] = '/';
            }

            $str = remove_invisible_characters($str, FALSE);

            do
            {
                $old = $str;
                $str = str_replace($bad, '', $str);
            }
            while ($old !== $str);

            return stripslashes($str);
        }

        // ----------------------------------------------------------------

        /**
        * Strip Image Tags
        *
        * @param	string	$str
        * @return	string
        */
        public function strip_image_tags($str)
        {
            return preg_replace(array('#<img[\s/]+.*?src\s*=\s*["\'](.+?)["\'].*?\>#', '#<img[\s/]+.*?src\s*=\s*(.+?).*?\>#'), '\\1', $str);
        }

        // ----------------------------------------------------------------

        /**
        * Compact Exploded Words
        *
        * Callback method for xss_clean() to remove whitespace from
        * things like 'j a v a s c r i p t'.
        *
        * @used-by	STC_Security::xss_clean()
        * @param	array	$matches
        * @return	string
        */
        protected function _compact_exploded_words($matches)
        {
            return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
        }

        // --------------------------------------------------------------------

        /**
        * Remove Evil HTML Attributes (like event handlers and style)
        *
        * It removes the evil attribute and either:
        *
        *  - Everything up until a space. For example, everything between the pipes:
        *
        *	<code>
        *		<a |style=document.write('hello');alert('world');| class=link>
        *	</code>
        *
        *  - Everything inside the quotes. For example, everything between the pipes:
        *
        *	<code>
        *		<a |style="document.write('hello'); alert('world');"| class="link">
        *	</code>
        *
        * @param	string	$str		The string to check
        * @param	bool	$is_image	Whether the input is an image
        * @return	string	The string with the evil attributes removed
        */
        protected function _remove_evil_attributes($str, $is_image)
        {
            $evil_attributes = array('on\w*', 'style', 'xmlns', 'formaction', 'form', 'xlink:href', 'FSCommand', 'seekSegmentTime');

            if ($is_image === TRUE)
            {
                /*
                * Adobe Photoshop puts XML metadata into JFIF images,
                * including namespacing, so we have to allow this for images.
                */
                unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
            }

            do {
                $count = $temp_count = 0;

                // replace occurrences of illegal attribute strings with quotes (042 and 047 are octal quotes)
                $str = preg_replace('/(<[^>]+)(?<!\w)('.implode('|', $evil_attributes).')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is', '$1[removed]', $str, -1, $temp_count);
                $count += $temp_count;

                // find occurrences of illegal attribute strings without quotes
                $str = preg_replace('/(<[^>]+)(?<!\w)('.implode('|', $evil_attributes).')\s*=\s*([^\s>]*)/is', '$1[removed]', $str, -1, $temp_count);
                $count += $temp_count;
            }
            while ($count);

            return $str;
        }

        // --------------------------------------------------------------------

        /**
        * Sanitize Naughty HTML
        *
        * Callback method for xss_clean() to remove naughty HTML elements.
        *
        * @used-by	STC_Security::xss_clean()
        * @param	array	$matches
        * @return	string
        */
        protected function _sanitize_naughty_html($matches)
        {
            return '&lt;'.$matches[1].$matches[2].$matches[3] // encode opening brace
                // encode captured opening or closing brace to prevent recursive vectors:
                .str_replace(array('>', '<'), array('&gt;', '&lt;'), $matches[4]);
        }

        // --------------------------------------------------------------------

        /**
        * JS Link Removal
        *
        * Callback method for xss_clean() to sanitize links.
        *
        * This limits the PCRE backtracks, making it more performance friendly
        * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
        * PHP 5.2+ on link-heavy strings.
        *
        * @used-by	STC_Security::xss_clean()
        * @param	array	$match
        * @return	string
        */
        protected function _js_link_removal($match)
        {
            return str_replace($match[1],
                        preg_replace('#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                                '',
                                $this->_filter_attributes(str_replace(array('<', '>'), '', $match[1]))
                        ),
                        $match[0]);
        }

        // --------------------------------------------------------------------

        /**
        * JS Image Removal
        *
        * Callback method for xss_clean() to sanitize image tags.
        *
        * This limits the PCRE backtracks, making it more performance friendly
        * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
        * PHP 5.2+ on image tag heavy strings.
        *
        * @used-by	STC_Security::xss_clean()
        * @param	array	$match
        * @return	string
        */
        protected function _js_img_removal($match)
        {
            return str_replace($match[1],
                        preg_replace('#src=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
                                '',
                                $this->_filter_attributes(str_replace(array('<', '>'), '', $match[1]))
                        ),
                        $match[0]);
        }

        // --------------------------------------------------------------------

        /**
        * Attribute Conversion
        *
        * @used-by	STC_Security::xss_clean()
        * @param	array	$match
        * @return	string
        */
        protected function _convert_attribute($match)
        {
            return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
        }

        // --------------------------------------------------------------------

        /**
        * Filter Attributes
        *
        * Filters tag attributes for consistency and safety.
        *
        * @used-by	STC_Security::_js_img_removal()
        * @used-by	STC_Security::_js_link_removal()
        * @param	string	$str
        * @return	string
        */
        protected function _filter_attributes($str)
        {
            $out = '';
            if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
            {
                foreach ($matches[0] as $match)
                {
                    $out .= preg_replace('#/\*.*?\*/#s', '', $match);
                }
            }

            return $out;
        }

        // --------------------------------------------------------------------

        /**
        * HTML Entity Decode Callback
        *
        * @used-by	STC_Security::xss_clean()
        * @param	array	$match
        * @return	string
        */
        protected function _decode_entity($match)
        {
            // Protect GET variables in URLs
            // 901119URL5918AMP18930PROTECT8198
            $match = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', $this->xss_hash().'\\1=\\2', $match[0]);

            // Decode, then un-protect URL GET vars
            return str_replace(
                $this->xss_hash(),
                '&',
                $this->entity_decode($match, $this->charset)
            );
        }

        // --------------------------------------------------------------------

        /**
        * Do Never Allowed
        *
        * @used-by	STC_Security::xss_clean()
        * @param 	string
        * @return 	string
        */
        protected function _do_never_allowed($str)
        {
            $str = str_replace(array_keys($this->_never_allowed_str), $this->_never_allowed_str, $str);

            foreach ($this->_never_allowed_regex as $regex)
            {
                $str = preg_replace('#'.$regex.'#is', '[removed]', $str);
            }

            return $str;
        }

        // --------------------------------------------------------------------

        /**
        * Generate a captcha image
        *
        * @return	array
        */
        public function generate_captcha($data = '', $img_path = '', $img_url = '', $font_path = '') {
            $defaults = array(
                'word'		=> '',
                'img_path'	=> '',
                'img_url'	=> '',
                'img_width'	=> '150',
                'img_height'	=> '50',
                'font_path'	=> BASEPATH.'fonts/Anorexia.ttf',
                'expiration'	=> 7200,
                'word_length'	=> 8,
                'font_size'	=> 16,
                'img_id'	=> '',
                'pool'		=> '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'colors'	=> array(
                    'background'	=> array(255,255,255),
                    'border'	=> array(153,102,102),
                    'text'		=> array(204,153,153),
                    'grid'		=> array(255,182,182)
                )
            );

            foreach ($defaults as $key => $val) {
                if ( ! is_array($data) && empty($$key)) {
                    $$key = $val;
                } else {
                    $$key = isset($data[$key]) ? $data[$key] : $val;
                }
            }

            if ($img_path === '' OR $img_url === '' OR ! is_dir($img_path) OR ! is_really_writable($img_path) OR ! extension_loaded('gd')) {
                return FALSE;
            }

            // -----------------------------------
            // Remove old images
            // -----------------------------------

            $now = microtime(TRUE);

            $current_dir = @opendir($img_path);
            while ($filename = @readdir($current_dir)) {
                if (substr($filename, -4) === '.jpg' && (str_replace('.jpg', '', $filename) + $expiration) < $now) {
                    @unlink($img_path.$filename);
                }
            }

            @closedir($current_dir);

            // -----------------------------------
            // Do we have a "word" yet?
            // -----------------------------------

            if (empty($word)) {
                $word = '';
                for ($i = 0, $mt_rand_max = strlen($pool) - 1; $i < $word_length; $i++) {
                    $word .= $pool[mt_rand(0, $mt_rand_max)];
                }
            }
            elseif ( ! is_string($word)) {
                $word = (string) $word;
            }

            // -----------------------------------
            // Determine angle and position
            // -----------------------------------
            $length	= strlen($word);
            $angle	= ($length >= 6) ? mt_rand(-($length-6), ($length-6)) : 0;
            $x_axis	= mt_rand(6, (360/$length)-16);
            $y_axis = ($angle >= 0) ? mt_rand($img_height, $img_width) : mt_rand(6, $img_height);

            // Create image
            // PHP.net recommends imagecreatetruecolor(), but it isn't always available
            $im = function_exists('imagecreatetruecolor')
                ? imagecreatetruecolor($img_width, $img_height)
                : imagecreate($img_width, $img_height);

            // -----------------------------------
            //  Assign colors
            // ----------------------------------

            is_array($colors) OR $colors = $defaults['colors'];

            foreach (array_keys($defaults['colors']) as $key) {
                // Check for a possible missing value
                is_array($colors[$key]) OR $colors[$key] = $defaults['colors'][$key];
                $colors[$key] = imagecolorallocate($im, $colors[$key][0], $colors[$key][1], $colors[$key][2]);
            }

            // Create the rectangle
            imagefilledrectangle($im, 0, 0, $img_width, $img_height, $colors['background']);

            // -----------------------------------
            //  Create the spiral pattern
            // -----------------------------------
            $theta		= 1;
            $thetac		= 7;
            $radius		= 16;
            $circles	= 20;
            $points		= 32;

            for ($i = 0, $cp = ($circles * $points) - 1; $i < $cp; $i++) {
                $theta += $thetac;
                $rad = $radius * ($i / $points);
                $x = ($rad * cos($theta)) + $x_axis;
                $y = ($rad * sin($theta)) + $y_axis;
                $theta += $thetac;
                $rad1 = $radius * (($i + 1) / $points);
                $x1 = ($rad1 * cos($theta)) + $x_axis;
                $y1 = ($rad1 * sin($theta)) + $y_axis;
                imageline($im, $x, $y, $x1, $y1, $colors['grid']);
                $theta -= $thetac;
            }

            // -----------------------------------
            //  Write the text
            // -----------------------------------

            $use_font = ($font_path !== '' && file_exists($font_path) && function_exists('imagettftext'));
            if ($use_font === FALSE) {
                ($font_size > 5) && $font_size = 5;
                $x = mt_rand(0, $img_width / ($length / 3));
                $y = 0;
            } else {
                ($font_size > 30) && $font_size = 30;
                $x = mt_rand(0, $img_width / ($length / 1.5));
                $y = $font_size + 2;
            }

            for ($i = 0; $i < $length; $i++) {
                if ($use_font === FALSE) {
                    $y = mt_rand(0 , $img_height / 2);
                    imagestring($im, $font_size, $x, $y, $word[$i], $colors['text']);
                    $x += ($font_size * 2);
                } else {
                    $y = mt_rand($img_height / 2, $img_height - 3);
                    imagettftext($im, $font_size, $angle, $x, $y, $colors['text'], $font_path, $word[$i]);
                    $x += $font_size;
                }
            }

            // Create the border
            imagerectangle($im, 0, 0, $img_width - 1, $img_height - 1, $colors['border']);

            // -----------------------------------
            //  Generate the image
            // -----------------------------------
            $img_url = rtrim($img_url, '/').'/';

            if (function_exists('imagejpeg')) {
                $img_filename = $now.'.jpg';
                imagejpeg($im, $img_path.$img_filename);
            } elseif (function_exists('imagepng')) {
                $img_filename = $now.'.png';
                imagepng($im, $img_path.$img_filename);
            } else {
                return FALSE;
            }

            $img = '<img '.($img_id === '' ? '' : 'id="'.$img_id.'"').' src="'.$img_url.$img_filename.'" style="width: '.$img_width.'; height: '.$img_height .'; border: 0;" alt=" " />';
            imagedestroy($im);

            return array('word' => $word, 'time' => $now, 'image' => $img, 'filename' => $img_filename, 'url' => $img_url.$img_filename);
        }

    }
