<?php

$config = array(
    // optionType => array(options)
    'form' => array(
        'autorun' => false,
        'form' => array(
            'formName' => array(
                'protection' => array(
                    'csrf' => array(
                        'urlReferer' => array('index', 'captcha'), //routes name
                        'timeValidity' => 60, //second
                        'allowMultiple' => true // (allow multiple pages open, optional, default is true)
                    ),
                    'captcha' => array(
                        'dataFile' => '[PATH_DATA]captcha[DS]captcha-full.xml'
                    //possible to override option value defined into dataFile
                    )
                ),
            ),
        )
    ),
    'sniffer' => array(
        'autorun' => true,
        'trapName' => 'badbottrap',
        'badCrawlerFile' => '[PATH_DATA]sniffer[DS]crawlerBad.xml',
        'goodCrawlerFile' => '[PATH_DATA]sniffer[DS]crawlerGood.xml',
        'logBadCrawler' => true,
        'logGoodCrawler' => true,
        'logUnknownCrawler' => true
    )
);
?>
