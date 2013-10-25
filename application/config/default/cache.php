<?php

$config = array(
    'core' => array(
        'driver' => 'file',
        'prefix' => '_',
        'path' => '[PATH_CACHE_CORE]',
        'gc' => 'time',
        'gcOption' => 86400,
        'groups' => 'autoloader,logger'
    ),
    'default' => array(
        'driver' => 'file',
        'prefix' => '_',
        'path' => '[PATH_CACHE_DEFAULT]',
        'gc' => 'time',
        'gcOption' => 86400,
        'groups' => 'group1,group2'
    )
);
?>
