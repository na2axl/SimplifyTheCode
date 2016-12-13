<?php

    defined('BASEPATH') OR exit('No direct script access allowed');

    /*
     *----------------------------------------------------------------------
     * BASE SITE URL
     *----------------------------------------------------------------------
     *
     * This is your root URL (the URL to your SimplifyTheCode root). Is the
     * URL you use to access your site. Write your URL with a trailing slash.
     *
     * Exemple: http://example.com/
     *
     * If it's not set STC will try guess URL (protocol, domain, and path to
     * your installation). But is HIGHLY recommended to configure this explicitly
     * and never rely on auto-guessing, especially in production environments.
     *
     */
    $config['base_url'] = 'http://127.0.0.1:8000/';

    /*
     *----------------------------------------------------------------------
     * DATABASE SERVER
     *----------------------------------------------------------------------
     *
     * This is your database server's URL. Is the URL you use to connect to
     * your database. Write your URL WITHOUT a trailing slash.
     *
     * Exemple: mysql.example.com
     *
     */
    $config['db_server'] = 'localhost';

    /*
     *----------------------------------------------------------------------
     * DATABASE USERNAME
     *----------------------------------------------------------------------
     *
     * This is the username you use to connect to your database.
     *
     */
    $config['db_user'] = 'root';

    /*
     *----------------------------------------------------------------------
     * DATABASE PASSWORD
     *----------------------------------------------------------------------
     *
     * This is the password you use to connect to your database.
     *
     */
    $config['db_pass'] = '';

    /*
     *----------------------------------------------------------------------
     * DATABASE NAME
     *----------------------------------------------------------------------
     *
     * This is the name of your database.
     *
     */
    $config['db_name'] = 'iai_social_network';

    /*
     *----------------------------------------------------------------------
     * APPLICATION NAME
     *----------------------------------------------------------------------
     *
     * [OPTIONAL]This is the name of your application.
     *
     */
    $config['app_name'] = 'SimplifyTheCode';

    /*
     *----------------------------------------------------------------------
     * DEFAULT APPLICATION LANGUAGE
     *----------------------------------------------------------------------
     *
     * This is the default language to use for your application. Make sure that
     * the language file corresponding exists in the language folder. Otherwise
     * an error will occur.
     *
     */
    $config['default_lang'] = 'fr';

    /*
     *----------------------------------------------------------------------
     * DEFAULT CHARSET
     *----------------------------------------------------------------------
     *
     * This is the default character charset to use for your application.
     *
     */
    $config['charset'] = 'utf-8';

    /*
     *----------------------------------------------------------------------
     * Views Caching
     *----------------------------------------------------------------------
     *
     * Activate/Deactivate the caching of generated views. It's recommended to
     * set TRUE for production environment.
     *
     */
    $config['cache_views'] = FALSE;

    /*
     *----------------------------------------------------------------------
     * ENABLE QUERY STRINGS
     *----------------------------------------------------------------------
     *
     * Activate/Deactivate the use of query strings in urls.
     *
     */
    $config['enable_query_strings'] = FALSE;

    /*
     *----------------------------------------------------------------------
     * MESSAGES LOGGING
     *----------------------------------------------------------------------
     *
     * Activate/Deactivate the logging of internal message and events of STC.
     *
     */
    $config['enable_logging'] = TRUE;

    /*
     *----------------------------------------------------------------------
     * LOG PATH
     *----------------------------------------------------------------------
     *
     * This is the path to the folder who stores logging files. Ignore to use
     * the default folder.
     *
     */
    $config['log_path'] = '';

    /*
     *----------------------------------------------------------------------
     * LOG LEVEL
     *----------------------------------------------------------------------
     *
     * This is the level of events to log. Possible values are:
     *  - 1 (ERROR)
     *  - 2 (DEBUG)
     *  - 3 (INFO)
     *  - 4 (ALL)
     *
     */
    $config['log_level'] = 4;

    /*
     *----------------------------------------------------------------------
     * LOG FILE PERMISSIONS
     *----------------------------------------------------------------------
     *
     * This is the file permission to use when STC create a log file.
     *
     */
    $config['log_file_permissions'] = 0644;

    /*
     *----------------------------------------------------------------------
     * LOG FILE EXTENSION
     *----------------------------------------------------------------------
     *
     * This is the file extension of the log file.
     *
     */
    $config['log_file_extension'] = 'log';

    /*
     *----------------------------------------------------------------------
     * LOG DATE FORMAT
     *----------------------------------------------------------------------
     *
     * This is the date format to use when logging an event.
     *
     */
    $config['log_date_format'] = 'Y-m-d H:i:s';

    /*
     *----------------------------------------------------------------------
     * COOKIE PATH
     *----------------------------------------------------------------------
     *
     * This is the path used when writing and reading cookies.
     *
     */
    $config['cookie_path'] = '/';

    /*
     *----------------------------------------------------------------------
     * COOKIE DOMAIN
     *----------------------------------------------------------------------
     *
     * This is the domain to use when writing and reading cookies.
     * Ex: example.com
     *
     */
    $config['cookie_domain'] = '';

    /*
     *----------------------------------------------------------------------
     * COOKIE HTTP ONLY
     *----------------------------------------------------------------------
     *
     * This set if cookies have to be used only for HTTP connections, no
     * Javascript.
     *
     */
    $config['cookie_httponly'] = FALSE;

    /*
     *----------------------------------------------------------------------
     * COOKIE SECURE
     *----------------------------------------------------------------------
     *
     * This set if cookies are secured through HTTPS.
     *
     */

    $config['cookie_secure'] = ( 'https' === parse_url( base_url(), PHP_URL_SCHEME ) );

    /*
     *----------------------------------------------------------------------
     * COOKIE PREFIX
     *----------------------------------------------------------------------
     *
     * This is the prefix used for cookies' names.
     *
     */
    $config['cookie_prefix'] = 'stc_';

    /*
     *----------------------------------------------------------------------
     * CSRF PROTECTION
     *----------------------------------------------------------------------
     *
     * This set if cookies are under CSRF protection.
     *
     */
    $config['csrf_protection'] = TRUE;

    /*
     *----------------------------------------------------------------------
     * CSRF EXPIRE
     *----------------------------------------------------------------------
     *
     * This is the time interval to refresh the CSRF protection token.
     *
     */
    $config['csrf_expire'] = 0;

    /*
     *----------------------------------------------------------------------
     * CSRF TOKEN NAME
     *----------------------------------------------------------------------
     *
     * This is the name of CSRF token in the $_POST array.
     *
     */
    $config['csrf_token_name'] = 'csrf_token';

    /*
     *----------------------------------------------------------------------
     * CSRF COOKIE NAME
     *----------------------------------------------------------------------
     *
     * This is the name of CSRF token ine the $_COOKIE array.
     *
     */
    $config['csrf_cookie_name'] = 'csrf_token';

    /*
     *----------------------------------------------------------------------
     * CSRF EXCLUDE URIs
     *----------------------------------------------------------------------
     *
     * This is the list of uris to exclude from CSRF token check.
     *
     */
    $config['csrf_exclude_uris'] = array();

    /*
     *----------------------------------------------------------------------
     * CSRF REGENERATE
     *----------------------------------------------------------------------
     *
     * Activate/Deactivate the regeneration of the CSRF token on each request.
     *
     */
    $config['csrf_regenerate'] = TRUE;

    /*
     *----------------------------------------------------------------------
     * CUSTOM CONFIG VALUES
     *----------------------------------------------------------------------
     *
     * You can set your own configuration values by following this example.
     *
     */
    // $config['your_config_key'] = 'your_value';
    $config['assets_url'] = $config['base_url'] . 'assets/';
