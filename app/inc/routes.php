<?php

    defined('BASEPATH') OR exit('No direct script access allowed');

    // The defult controller
    $routes['default'] = 'Home';

    // This controller will be called to switch the language
    $routes['lang/(:str)'] = 'Tools/setLang/$1';
    $routes['lang/(:str)/(:any)'] = 'Tools/setLang/$1/$2';
