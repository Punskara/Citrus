<?php

define( 'CTS_PATH', __DIR__ );
define( 
    'CTS_IS_DEV', 
    !isset( $_SERVER['REMOTE_ADDR'] ) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1'
);
define( 'CTS_APPS_DIR', '/apps/' );

define( 'CTS_CLASS_PATH', CTS_PATH . '/core/' );
define( 'CTS_APPS_PATH', CTS_PATH . CTS_APPS_DIR );
define( 'CTS_LIB_PATH', CTS_CLASS_PATH . 'lib/' );
define( 'CTS_WWW_PATH', CTS_PATH . '/www/' );
define( 'CTS_LOG_PATH', CTS_PATH . '/var/log/' );
define( 'CTS_SCHEME', isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' );

require_once CTS_PATH . '/include/functions.php';
require_once CTS_CLASS_PATH . '/Citrus/Citrus.php';

