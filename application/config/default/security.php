<?php

$config = array(
    //security name => options
    'csrf' => array(
        'adaptater' => 'csrf', //class name (implement \framework\security\IAdaptater)
        'autorun' => false, //autorun (optionnal default is false)
        'urlsReferer' => array('index'), //routes name
        'timeValidity' => 600, //second
        'allowMultiple' => true, // (allow multiple pages open, optional, default is true)
        'errorRedirect' => true, // (redirect 403 if is invalid, optional, default is false)
    ),
    'captcha' => array(
        'adaptater' => 'captcha', //class name (implement \framework\security\IAdaptater)
        'dataFile' => '[PATH_DATA]captcha[DS]captcha-full.xml', // possible to override ooption value defined into dataFile
        'errorRedirect' => false, // (redirect 403 if is invalid, optional, default is false)
    ),
    'sniffer' => array(
        'adaptater' => 'sniffer', //class name (implement \framework\security\IAdaptater)
        'autorun' => true, //autorun (optionnal default is false)
        'trapName' => 'badbottrap',
        'badCrawlerFile' => '[PATH_DATA]sniffer[DS]crawlerBad.xml',
        'goodCrawlerFile' => '[PATH_DATA]sniffer[DS]crawlerGood.xml',
        'logBadCrawler' => true,
        'logGoodCrawler' => true,
        'logUnknownCrawler' => true
    )
);
?>
