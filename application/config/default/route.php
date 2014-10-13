<?php

$config = array(
    /*
     * 'routeName' => array(
     *      //controller name class  (case-insensitive), based on controllers namespace
     *      'controller' => 'index',
     *      //optionals
     *      //rules
     *      'rules' => array(
     *          'ruleName',
     *          'ruleName/([0-9a-zA-Z]+)/([a-z]+)/([0-9]+)'
     *      ),
     *      //methods into controller called... (possibility pass arguments)
     *      'methods' => array(
     *          'captcha' => array('[[1]]', '[[2]]', '[[3]]')
     *      ),
     *      'regex' => true, (true|false, check regex into rules default is false)
     *      'requireSsl' => false,  (true|false, default is false)
     *      'requireAjax' => false,  (true|false, default is false)
     *      'autoSetAjax' => true,  (true|false, turn on ajax controller, when request is ajax, optional default is true)
     *      'requireHttpMethod' => 0 (0 => 'GET', 1 => 'HEAD', 2 => 'POST', 3 => 'PUT', 4 => 'DELETE', 5 => 'TRACE', 6 => 'OPTIONS', 7 => 'CONNECT', 8 => 'PATCH', optional default is null (all))
     *      'httpResponseStatusCode' => code (must be an integer, default is null),
     *      'httpProtocol' => protocol (must be a string, default is null)
     * 
     *  ),
     */
    'index' => array(
        'controller' => 'index',
    ),
    'captcha' => array(
        'regex' => true,
        'rules' => array(
            'captcha/([0-9a-zA-Z]+)/([a-z]+)',
            'captcha/([0-9a-zA-Z]+)/([a-z]+)/([0-9]+)'
        ),
        'controller' => 'index',
        'methods' => array(
            'captcha' => array('[[1]]', '[[2]]', '[[3]]')
        )
    ),
    'language' => array(
        'regex' => true,
        'rules' => array(
            'language/([A-Za-z0-9_]+)'
        ),
        'controller' => 'index',
        'methods' => array(
            'language' => array('[[1]]')
        )
    ),
    'error' => array(
        'regex' => true,
        'rules' => array(
            'error/([0-9]+)'
        ),
        'controller' => 'error',
        'methods' => array(
            'show' => array('[[1]]')
        )
    ),
    'debugger' => array(
        'regex' => true,
        'rules' => array(
            'error/debugger/([a-z]+)'
        ),
        'controller' => 'error',
        'methods' => array(
            'debugger' => array('[[1]]')
        )
    ),
);
?>
