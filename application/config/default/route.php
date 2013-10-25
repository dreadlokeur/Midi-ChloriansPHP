<?php

$config = array(
    'index' => array(
        'controller' => 'index',
    ),
    'captcha' => array(
        'rules' => array(
            'captcha/([0-9]+)/([a-z]+)/'
        ),
        'controller' => 'index',
        'methods' => array(
            'captcha' => array('[[1]]', '[[2]]')
        )
    ),
    'language' => array(
        'rules' => array(
            'language'
        ),
        'controller' => 'index',
        'methods' => array(
            'language'
        )
    ),
    'debugger' => array(
        'rules' => array(
            'error/debugger'
        ),
        'controller' => 'error',
        'methods' => array(
            'debugger'
        )
    ),
    'error400' => array(
        'rules' => array(
            'error/badRequest'
        ),
        'controller' => 'error',
        'methods' => array(
            'badRequest'
        )
    ),
    'error401' => array(
        'rules' => array(
            'error/unauthorized'
        ),
        'controller' => 'error',
        'methods' => array(
            'unauthorized'
        )
    ),
    'error403' => array(
        'rules' => array(
            'error/forbidden'
        ),
        'controller' => 'error',
        'methods' => array(
            'forbidden'
        )
    ),
    'error404' => array(
        'rules' => array(
            'error/notFound'
        ),
        'controller' => 'error',
        'methods' => array(
            'notFound'
        )
    ),
    'error405' => array(
        'rules' => array(
            'error/methodNotAllowed'
        ),
        'controller' => 'error',
        'methods' => array(
            'methodNotAllowed'
        )
    ),
    'error500' => array(
        'rules' => array(
            'error/internalServerError'
        ),
        'controller' => 'error',
        'methods' => array(
            'internalServerError'
        )
    ),
    'error502' => array(
        'rules' => array(
            'error/badGateway'
        ),
        'controller' => 'error',
        'methods' => array(
            'badGateway'
        )
    ),
    'error503' => array(
        'rules' => array(
            'error/serviceUnavailable'
        ),
        'controller' => 'error',
        'methods' => array(
            'serviceUnavailable'
        )
    ),
    //tests
    'test' => array(
        'forceSsl' => true,
        'regex' => true,
        'rules' => array(
            'test/([0-9]+)/',
        //'fr_FR/test/([0-9]+)/',
        ),
        'controller' => 'index',
        'methods' => array(
            'test' => array('[[1]]')
        )
    ),
    'test2' => array(
        'regex' => true,
        'rules' => array(
            'test2/([0-9]+)/([a-z]+)/',
        //'fr_FR/test2/([0-9]+)/([a-z]+)/'
        ),
        'controller' => 'index',
        'methods' => array(
            'test2' => array('[[1]]', '[[2]]')
        )
    ),
    'test3' => array(
        'rules' => array(
            'index/action'
        ),
        'controller' => 'index',
        'methods' => array(
            'action'
        )
    ),
);
?>
