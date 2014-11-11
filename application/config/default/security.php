<?php

$config = array(
    //security name => options
    'csrf' => array(
        'adaptater' => 'csrf', //class name (implement \framework\security\IAdaptater)
        'autorun' => false, //autorun (optionnal default is false)
        'urlsReferer' => 'index', //routes name, must be an array, or a string for one (optional, default accepte all urls)
        'timeValidity' => 600, // token time validity in second (optional default is : 0 (no limit))
        'allowMultiple' => true, // (allow multiple pages open, optional, default is true)
        'errorRedirect' => true, // (redirect 403 if is invalid, optional, default is false)
        'sessionKeyTokenName' => 'csrfToken', // subfix session token name (optional, default is csrfToken)
        'sessionKeyTokenTimeName' => 'csrfTokenTime', // subfix session token time name (optional, default is csrfTokenTime)
        'tokenName' => 'csrf' //token name (optional, default is security name)
    ),
    'captcha' => array(
        'adaptater' => 'captcha', // class name (implement \framework\security\IAdaptater)
        'autorun' => false, //autorun (optionnal default is false)
        'dataFile' => '[PATH_DATA]captcha[DS]captcha-full.xml', // (optional, possible to override option value defined into dataFile)
        'errorRedirect' => false, // redirect 403 if is invalid (optional, default is false
        'sessionKeyName' => 'captcha', //  subfix session key name (optional, default is captcha)
    ),
    'crawler' => array(
        'adaptater' => 'crawler', // class name (implement \framework\security\IAdaptater)
        'autorun' => true, // autorun (optionnal default is false)
        'queryName' => 'crawlertrap', // query name (optionnal default is "crawlertrap")
        'sessionKeyName' => 'crawler', // subfix session key name (optional, default is crawler)
        'badCrawlers' => '[PATH_DATA]crawler[DS]bad.xml', //datas file
        'goodCrawlers' => '[PATH_DATA]crawler[DS]good.xml', //datas file
        'badCrawlerBan' => true, //redirect 403 if is catched (optionnal default is "true")
        'goodCrawlerBan' => false, //redirect 403 if is catched (optionnal default is "false")
        'unknownCrawlerBan' => true, //redirect 403 if is catched (optionnal default is "true")
        'badCrawlerLog' => true, //log if is catched (optionnal default is "true")
        'goodCrawlerLog' => true, //log if is catched (optionnal default is "true")
        'unknownCrawlerLog' => true, //log if is catched (optionnal default is "true")
    ),
    /*'flooder' => array(
        'adaptater' => 'flooder', // class name (implement \framework\security\IAdaptater)
        'autorun' => false, // autorun (optionnal default is false)
        'maxAttempts' => 10, // attemps fail max (optional, default is 10)
        'errorRedirect' => true, // (redirect 403 if is blacklisted, optional, default is true)
        'cache' => 'core', // cache name (required)
        'httpMethods' => array('POST', 'GET'), // check on http method, must be an array, or a string for one (GET, HEAD, POST, PUT', DELETE, TRACE, OPTIONS, CONNECT, PATCH, optional default is null)
    ),*/
);
?>
