<?php
return array(
    'extends' => 'root',
    'css' => array(
        'naseptavac.css',
        'vendor/font-awesome.min.css',
        'vendor/bootstrap-slider.css',
        'print.css:print',
        'assets/styles.css',
        'assets/vufind.css',
        'ntk.css',
        'datepicker.css',
    ),
    'js' => array(
        'vendor/base64.js:lt IE 10', // btoa polyfill
        'vendor/jquery.min.js',
        'vendor/bootstrap.min.js',
        'vendor/bootstrap-accessibility.min.js',
        'vendor/rc4.js',
        'vendor/validator.min.js',
        'autocomplete.js',
        'common.js',
        'lightbox.js',
        'yahoo-dom-event.js',
        'lang.js',
        'json-min.js',
        'yui-min.js',
        'NTK.js',
        'bootstrap-datepicker.js',
        'bootstrap-datepicker.cs.js',
        'assets/modernizr.min.js',
    ),
    'less' => array(
        'active' => false,
        'compiled.less'
    ),
    'favicon' => 'vufind-favicon.ico',
    'helpers' => array(
        'factories' => array(
            'transsub' => 'VuFind\View\Helper\Root\Factory::getTransSub',
            'flashmessages' => 'VuFind\View\Helper\Bootstrap3\Factory::getFlashmessages',
            'layoutclass' => 'VuFind\View\Helper\Bootstrap3\Factory::getLayoutClass',
            'recaptcha' => 'VuFind\View\Helper\Bootstrap3\Factory::getRecaptcha',
        ),
        'invokables' => array(
            'highlight' => 'VuFind\View\Helper\Bootstrap3\Highlight',
            'search' => 'VuFind\View\Helper\Bootstrap3\Search',
        )
    )
);
