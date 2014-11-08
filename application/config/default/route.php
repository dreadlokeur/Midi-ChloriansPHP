<?php

$config = array(
    /*
     * 'routeName' => array(
     *      controller name class  (case-insensitive), based on controllers namespace, possible set a full namespace
     *      'controller' => 'index',
     * 
     *      Optionals
     *      'regex' => true, (true|false, check regex into rules default is true)
     *      'requireSsl' => false,  (true|false, default is false)
     *      'requireAjax' => false,  (true|false, default is false)
     *      'autoSetAjax' => true,  (true|false, turn on ajax controller, when request is ajax, optional default is true)
     *      'httpResponseStatusCode' => 200 (must be an integer, default is null),
     *      'httpProtocol' => protocol (must be a string, default is null)
     * 
     *      rules, must be a array or string (for one)n possibility regex, if is activate
     *      'rules' => array(
     *          'ruleName',
     *          'ruleName/([0-9a-zA-Z]+)/([a-z]+)/([0-9]+)'
     *      ),
     *      'rules' => 'ruleName',
     * 
     *      actions functions in controller, must be a array or string (for one)
     *      possibility pass arguments match into regex rules, format '[[1]]', '[[2]]' (number of match)
     *      'actions' => array(
     *          'function1' => array('[[1]]', '[[2]]', '[[3]]'),
     *          'function2',
     *      ),
     *      'actions' => 'function1',
     * 
     *      http methods require, must be a array or string (for one) (GET, HEAD, POST, PUT', DELETE, TRACE, OPTIONS, CONNECT, PATCH, optional default is null (all))
     *      'requireHttpMethods' => 'POST', 
     *      'requireHttpMethods' => array(
     *          'POST',
     *          'GET'
     *      ),
     * 
     *      security,  must be a array or string (for one), default is empty
     *      'security' => array(
     *          'security1',
     *          'security2'
     *      ),
     *      'security' => 'name',
     * 
     *  ),
     */
    'index' => array(
        'controller' => 'index',
    ),
    'captcha' => array(
        'rules' => array(
            'captcha/([0-9a-zA-Z]+)/([a-z]+)',
            'captcha/([0-9a-zA-Z]+)/([a-z]+)/([0-9]+)'
        ),
        'controller' => 'index',
        'actions' => array(
            'captcha' => array('[[1]]', '[[2]]', '[[3]]')
        )
    ),
    'language' => array(
        'rules' => array(
            'language/([A-Za-z0-9_]+)'
        ),
        'controller' => 'index',
        'actions' => array(
            'language' => array('[[1]]')
        )
    ),
    'error' => array(
        'rules' => array(
            'error/([0-9]+)'
        ),
        'controller' => 'error',
        'actions' => array(
            'show' => array('[[1]]')
        )
    ),
    'debugger' => array(
        'rules' => array(
            'error/debugger/([a-z]+)'
        ),
        'controller' => 'error',
        'actions' => array(
            'debugger' => array('[[1]]')
        )
    ),
);
?>
