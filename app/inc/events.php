<?php

    defined('BASEPATH') OR exit('No direct script access allowed');

    /*
     *----------------------------------------------------------------------
     * CONTROLLER INITIALIZATION CALLBACK
     *----------------------------------------------------------------------
     *
     * This function will be called just after the initialization of the main
     * controller. Note that this function has one parameter which represent
     * the main controller instance.
     *
     */
    add_event_callback('controller', 'init', function( &$controller ) {
        // YOUR FUNCTION
    });

    /*
     *----------------------------------------------------------------------
     * LANGUAGE CHANGE CALLBACK
     *----------------------------------------------------------------------
     *
     * This function will be called just after the change of the language files
     * used. Note that this function has one parameter which represent the
     * short name of the new language (ex: en or fr).
     *
     */
    add_event_callback('lang', 'change', function( $language ) {
        // YOUR FUNCTION
    });

    /*
     *----------------------------------------------------------------------
     * ERROR OCCURRED CALLBACK
     *----------------------------------------------------------------------
     *
     * This function will be called just after an error occurred.
     * Note that this function has two parameters which represent the header
     * of the error and his message body.
     *
     */
    add_event_callback('exceptions', 'error', function ($heading, $message) {
        // YOUR FUNCTION
    });

    /*
     *----------------------------------------------------------------------
     * EXCEPTION THROWN CALLBACK
     *----------------------------------------------------------------------
     *
     * This function will be called just after an exception has been thrown.
     * Note that this function has one parameter which represent the exception
     * thrown.
     *
     */
    add_event_callback('exceptions', 'exception', function( $exception ) {
        // YOUR FUNCTION
    });
