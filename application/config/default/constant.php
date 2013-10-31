<?php

$config = array(
    //NAME => VALUE
    //required
    'ENVIRONNEMENT' => 'dev', //dev/test/prod
    'HOSTNAME' => 'localhost/Midi-ChloriansPHP', // your hostname
    'ADMIN_NAME' => 'your admin name', // administrator name
    'ADMIN_EMAIL' => 'your admin email', // administrator email
    'LANGUAGE_DEFAULT' => 'fr_FR',
    //Optional
    'SITE_MAINTENANCE' => false, // true/false (if true, running route "error" with param : "503")
    'SMTP_SERVER' => '',
    'TIMEZONE' => 'Europe/Paris',
    'LOG_LEVEL' => 4, // EMERGENCY = 0,  ALERT = 1, CRITICAL = 2, ERROR = 3, WARNING = 4, NOTICE = 5, INFO = 6, DEBUG = 7
    'LOG_BACKTRACE' => false,
    'LOG_DISPLAY' => 'display,firebug,chrome',
    'MAIL_LOG' => false,
    'LOG_MAIL_TO_NAME' => '[ADMIN_NAME]',
    'LOG_MAIL_TO_EMAIL' => '[ADMIN_EMAIL]',
    'LOG_WRITE' => false,
    'TEMPLATE_DEFAULT' => 'default', //template name
    'AUTOLOADER_CACHE' => 'core', //cache name
    'LOGGER_CACHE' => 'core', //cache name
    'AUTOLOADER_GLOBALIZER' => true,
    'GOOGLE_VERIFICATION' => '',
    'GOOGLE_UA' => '',
);
?>
