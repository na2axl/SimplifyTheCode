<?php

    /**
     * Default controller
     */
    class Home extends STC_Controller
    {
        /**
         * Class _contructor
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Home page
         */
        public function index()
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

            // Use the saved language
            if ($globals->session->is('lang'))
                $language->setLang($globals->session->get('lang'));

            // Assign variables
            $template->assign('app_name',        $config->item('app_name'));
            $template->assign('app_base_url',    $config->item('base_url'));
            $template->assign('this_page_route', $router->get_uri());
            $template->assign('elapsed_time',    $benchmark->elapsed_time('controller_execution_( Home / index )_start', 'controller_execution_( Home / index )_end') * 1000);
            $template->assign('memory_usage',    $benchmark->memory_usage('controller_execution_( Home / index )_start', 'controller_execution_( Home / index )_end') * 1);

            // Render the page
            $template->render('index');
        }
    }
