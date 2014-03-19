<?php

define( 
    'CTS_IS_DEV', 
    !isset( $_SERVER['REMOTE_ADDR'] ) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1'
);
define( 
    'CTS_SCHEME',
    isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' 
);

define( 'CTS_PATH',         __DIR__ );
define( 'CTS_APPS_DIR',     CTS_PATH . '/apps/' );
define( 'CTS_CORE_PATH',    CTS_PATH . '/core/' );
define( 'CTS_APPS_PATH',    CTS_PATH . CTS_APPS_DIR );
define( 'CTS_LIB_PATH',     CTS_CORE_PATH . 'lib/' );
define( 'CTS_WWW_PATH',     CTS_PATH . '/www/' );
define( 'CTS_LOG_PATH',     CTS_PATH . '/var/log/' );

spl_autoload_register(function( $class ) {
    $file = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
    if ( file_exists( __DIR__ . "/$file.php" ) ) {
        require_once( __DIR__ . "/$file.php" );
    }
});

require_once __DIR__ . '/include/functions.php';