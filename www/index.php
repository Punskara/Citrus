<?php
require_once '../boot.php';
// define( "CTS_INIT_FILE", basename( __FILE__ ) );
// define( "CTS_INIT_FILE", "index" ); 
// \core\Citrus\Citrus::getInstance()->boot( new \core\Citrus\sys\Config() );

try {
    $router = new core\Citrus\routing\Router( $_SERVER['REQUEST_URI'] );
    $router->map( "/testapp/", Array( 'controller' => function() {
        echo "tamere";
    } ) )->execute();


    $app = new \core\Citrus\mvc\App( "main", CTS_PATH . '/testapp/', $router );

    // vexp( $router->hasRoute() );

    $app->executeController();
} catch ( Exception $e ) {
    vexp( $e );
}