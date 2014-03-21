<?php

function autoload( $class ) {
    $file = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
    echo __DIR__ . "/../$file.php\n";
    if ( file_exists( __DIR__ . "/../../../$file.php" ) ) {
        require_once( __DIR__ . "/../../../$file.php" );
    }
}
spl_autoload_register( 'autoload' );

$t = new \core\Citrus\routing\Router( "/", "" );