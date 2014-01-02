<?php

define( 'CITRUS_PATH', dirname( __FILE__ ) );
define( 'CITRUS_CLASS_PATH', CITRUS_PATH . '/core/' );
define( 'CITRUS_APPS_PATH', CITRUS_PATH . '/apps/' );
define( 'CITRUS_LIB_PATH', CITRUS_CLASS_PATH . 'lib/' );
define( 'CITRUS_WWW_PATH', CITRUS_PATH . '/www/' );
define( 'CITRUS_LOG_PATH', CITRUS_PATH . '/log/' );
define( 'CITRUS_RW_EXT', '.html' );

define( 'CITRUS_SCHEME', isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' );

include_once CITRUS_PATH . '/include/functions.php';
include_once CITRUS_CLASS_PATH . '/Citrus/Citrus.php';

session_start();
require CITRUS_CLASS_PATH . 'doctrine-orm/Doctrine/ORM/Tools/Setup.php';

$lib = CITRUS_CLASS_PATH . "doctrine-orm/";
Doctrine\ORM\Tools\Setup::registerAutoloadDirectory( $lib );

try {
    $cos = core\Citrus\Citrus::getInstance( !isset( $_SERVER['HTTP_HOST'] ) );
} catch ( core\Citrus\sys\Exception $e ) {
    core\Citrus\sys\Debug::handleException( $e, true );
}