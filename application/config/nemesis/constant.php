<?php

$config = array(
    //NAME => VALUE
    //required
    'ENVIRONNEMENT' => 'prod', //dev/test/prod
    'HOSTNAME' => 'nemesis/Midi-ChloriansPHP', // your hostname
    'ADMIN_NAME' => 'your admin name', // administrator name
    'ADMIN_EMAIL' => 'dreadlokeur@gmail.com', // administrator email
    'LANGUAGE_DEFAULT' => 'fr_FR',
    //Optional
    'SITE_MAINTENANCE' => false, // true/false (if true, running route "error" with param : "503")
    'SMTP_SERVER' => 'smtp.orange.fr',
    'TIMEZONE' => 'Europe/Paris',
    'TEMPLATE_DEFAULT' => 'default', //template name
    'AUTOLOADER_CACHE' => 'core', //cache name
    'AUTOLOADER_GLOBALIZER' => true,
    'GOOGLE_VERIFICATION' => '',
    'GOOGLE_UA' => '',
    //logger
    'LOGGER_LEVEL' => 4, // EMERGENCY = 0,  ALERT = 1, CRITICAL = 2, ERROR = 3, WARNING = 4, NOTICE = 5, INFO = 6, DEBUG = 7
    'LOGGER_BACKTRACE' => false,
    'LOGGER_WRITE' => true,
    'LOGGER_DISPLAY' => 'display,firebug,chrome',
    'LOGGER_MAIL' => true,
    'LOGGER_MAIL_TO_NAME' => '[ADMIN_NAME]',
    'LOGGER_MAIL_TO_EMAIL' => '[ADMIN_EMAIL]',
    'LOGGER_CACHE' => 'core', //cache name
    'LOGGER_ERROR' => true,
);
?>
