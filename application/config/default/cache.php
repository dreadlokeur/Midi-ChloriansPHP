<?php

$config = array(
    // cache name => array(options)
    'core' => array(
        'driver' => 'file', //file/apc
        'prefix' => '_', // prefix string
        'path' => '[PATH_CACHE_CORE]',
        'gc' => 'time', // Garbage collection : time/number => toutes les x secondes, ou toutes les x requests
        'gcOption' => 86400, // seconds/request
        'groups' => 'autoloader,logger' // group list separated by ,
    ),
    'default' => array(
        'driver' => 'file',
        'prefix' => '_',
        'path' => '[PATH_CACHE_DEFAULT]',
        'gc' => 'time',
        'gcOption' => 86400,
    )
);
?>
