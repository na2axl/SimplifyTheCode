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
    $config['base_url'] = '';

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
    $config['db_server'] = '127.0.0.1';

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
    $config['db_name'] = '';

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
    $config['default_lang'] = 'en';

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
     * MESSAGES LOGGING
     *----------------------------------------------------------------------
     *
     * Activate/Deactivate the logging of internal message and events of STC.
     *
     */
    $config['enable_logging'] = FALSE;

    /*
     *----------------------------------------------------------------------
     * LOG PATH
     *----------------------------------------------------------------------
     *
     * This the path to the folder who stores logging files.
     *
     */
    $config['log_path'] = '';

    $config['log_level'] = 4;

    $config['log_file_permissions'] = 0644;

    $config['log_date_format'] = 'Y-m-d H:i:s';

    /*
     *----------------------------------------------------------------------
     * CONTROLLER INITIALIZATION CALLBACKS
     *----------------------------------------------------------------------
     *
     * This is an array of functions's names that will be called just after
     * the initialization of the main controller. Note that these functions
     * have one parameter which represent the main controller instance.
     *
     */
    $config['on_init_controller'] = array();