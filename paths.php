<?php

/**
 * Root paths
 * 
 * @copyright  Copyright 2013 - MidichlorianPHP and contributors
 * @author     NAYRAND Jérémie (dreadlokeur) <dreadlokeur@gmail.com>
 * @version    1.0.1dev2
 * @license    GNU General Public License 3 http://www.gnu.org/licenses/gpl.html
 * @package    MidichloriansPHP
 */
// Root paths
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', __DIR__ . DS);
define('PATH_BOOT', PATH_ROOT . 'boot' . DS);
define('PATH_FRAMEWORK', PATH_ROOT . 'framework' . DS);
define('PATH_VENDORS', PATH_ROOT . 'vendors' . DS);

//application
define('PATH_APP', PATH_ROOT . 'application' . DS);
define('PATH_LIBS', PATH_APP . 'libs' . DS);
define('PATH_CONTROLLERS', PATH_APP . 'controllers' . DS);
define('PATH_MODELS', PATH_APP . 'models' . DS);
define('PATH_VIEWS', PATH_APP . 'views' . DS);
define('PATH_DATA', PATH_APP . 'datas' . DS);
define('PATH_CACHE', PATH_APP . 'cache' . DS);
define('PATH_LOGS', PATH_APP . 'logs' . DS);
define('PATH_LANGUAGE', PATH_DATA . 'langs' . DS);
define('PATH_CONFIG', PATH_APP . 'config' . DS);
// Check config default path
if (!is_dir(PATH_CONFIG . 'default'))
    throw new \Exception('CONFIG ERROR, please set default config path');
define('PATH_CONFIG_DEFAULT', PATH_CONFIG . 'default' . DS);

// Cache paths
define('PATH_CACHE_CONFIG', PATH_CACHE . 'config' . DS);
define('PATH_CACHE_CORE', PATH_CACHE . 'core' . DS);
define('PATH_CACHE_DEFAULT', PATH_CACHE . 'default' . DS);
?>