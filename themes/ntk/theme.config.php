<?php
return array(
    'extends' => 'root',
    'css' => array(
        'vendor/font-awesome.min.css',
        'vendor/bootstrap-slider.css',
        'print.css:print',
        'assets/styles.css',
        'assets/vufind.css',
        'ntk.css',
        'datepicker.css',
    ),
    'js' => array(
        'vendor/jquery.min.js',
        'vendor/bootstrap.min.js',
        'vendor/bootstrap-accessibility.min.js',
        'vendor/typeahead.js',
        'vendor/rc4.js',
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
            'flashmessages' => 'VuFind\View\Helper\Bootstrap3\Factory::getFlashmessages',
            'layoutclass' => 'VuFind\View\Helper\Bootstrap3\Factory::getLayoutClass',
        ),
        'invokables' => array(
            'highlight' => 'VuFind\View\Helper\Bootstrap3\Highlight',
            'search' => 'VuFind\View\Helper\Bootstrap3\Search',
            'vudl' => 'VuDL\View\Helper\Bootstrap3\VuDL',
        )
    )
);
