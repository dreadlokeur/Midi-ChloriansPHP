<?php

$config = array(
    'form' => array(
        'autorun' => false,
        'form' => array(
            'example' => array(
                'protection' => array(
                    'csrf' => array(
                        'urlReferer' => 'root,captcha',
                        'timeValidity' => 60
                    ),
                    'captcha' => array(
                        'dataFile' => '[PATH_DATA]captcha[DS]captcha-full.xml'
                    //possible to override ooption value defined into dataFile
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
