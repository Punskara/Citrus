<?php
include '../boot.php';
define( 'CITRUS_INDEX', basename( __FILE__ ) );

$cos->loadRouter();

$app = $cos->router->app;
$module = $cos->router->controller;
$action = $cos->router->action;

try {
    $cos->app = \core\Citrus\mvc\App::load( $app ); 
    $cos->app->createController( $module, $action );
    $cos->request->addParams( $cos->router->params );
    $cos->app->executeCtrlAction();
    $cos->done = true;
} catch ( Exception $e ) {
    \core\Citrus\Citrus::pageNotFound();
}
die();