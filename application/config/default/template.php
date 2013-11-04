<?php

$config = array(
    'default' => array(
        'path' => '[PATH_TEMPLATE_DEFAULT]',
        'charset' => 'UTF-8',
        'driver' => 'php',
        'assets' => array(
            'img' => array(
                'directory' => '[PATH_TEMPLATE_DEFAULT_ASSETS]images[DS]'
            ),
            'sound' => array(
                'directory' => '[PATH_TEMPLATE_DEFAULT_ASSETS]sounds[DS]'
            ),
            'font' => array(
                'directory' => '[PATH_TEMPLATE_DEFAULT_ASSETS]fonts[DS]'
            ),
            'module' => array(
                'directory' => '[PATH_TEMPLATE_DEFAULT_ASSETS]modules[DS]'
            ),
            'css' => array(
                'directory' => '[PATH_TEMPLATE_DEFAULT_ASSETS]css[DS]',
                'cache' => array(
                    'compress' => true,
                    'name' => 'core'//cache name
                )
            ),
            'js' => array(
                'directory' => '[PATH_TEMPLATE_DEFAULT_ASSETS]javascripts[DS]',
                'loadUrls' => true,
                'loadLangs' => true,
                'cache' => array(
                    'compress' => true,
                    'name' => 'core'//cache config name
                )
            )
        )
    )
);
?>