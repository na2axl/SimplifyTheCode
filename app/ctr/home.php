<?php

    class Home extends STC_Controller {

        public function __construct() {
            parent::__construct();
        }

        public function index() {

            // Initializing variables
            $template = $this->template;
            $routeURI = $this->router;
            $language = $this->lang;
            $database = $this->opendb;


            // Render the page
            $template->render('index');
        }
    }
