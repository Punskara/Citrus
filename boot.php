<?php

define( 'CITRUS_PATH', dirname( __FILE__ ) );
define( 'CITRUS_CLASS_PATH', CITRUS_PATH . '/core/' );
define( 'CITRUS_APPS_PATH', CITRUS_PATH . '/apps/' );
define( 'CITRUS_LIB_PATH', CITRUS_CLASS_PATH . 'lib/' );
define( 'CITRUS_LOG_PATH', CITRUS_PATH . '/log/' );
define( 'CITRUS_RW_EXT', '.html' );

include_once CITRUS_PATH . '/include/functions.php';
include_once CITRUS_CLASS_PATH . '/Citrus/Citrus.php';
include_once CITRUS_LIB_PATH . 'class.phpmailer-lite.php';
include_once CITRUS_LIB_PATH . 'phpthumb-latest/ThumbLib.inc.php';

define( 'CITRUS_IS_IE6', strpos(  $_SERVER['HTTP_USER_AGENT'], "MSIE 6.0" ) !== false );
session_start();
#session_regenerate_id( true );

require CITRUS_CLASS_PATH . 'doctrine-orm/Doctrine/ORM/Tools/Setup.php';

$lib = CITRUS_CLASS_PATH . "doctrine-orm/";
Doctrine\ORM\Tools\Setup::registerAutoloadDirectory( $lib );

try {
    $cos = core\Citrus\Citrus::getInstance();    
} catch ( core\Citrus\sys\Exception $e ) {
    core\Citrus\sys\Debug::handleException( $e, true );
}