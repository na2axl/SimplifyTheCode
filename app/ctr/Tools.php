<?php

    /**
     * Tools controller
     */
    class Tools extends STC_Controller
    {
        /**
         * Class _contructor
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Change the current application's language
         */
        public function setLang($name, $redirect = '/')
        {
            // Initializing variables
            $benchmark = $this->benchmark;
            $config    = $this->config;
            $events    = $this->events;
            $language  = $this->lang;
            $logger    = $this->log;
            $mailer    = $this->mail;
            $model     = $this->model;
            $database  = $this->opendb;
            $globals   = $this->php_globals;
            $router    = $this->router;
            $security  = $this->security;
            $template  = $this->template;
            $upload    = $this->upload;

            // Save the new language in session
            $globals->session->set('lang', $name);

            // Redirect the user to the home page
            $router->redirect_to($redirect);
        }
    }
