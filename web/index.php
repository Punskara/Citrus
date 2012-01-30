<?php

include '../boot.php';
define( 'CITRUS_INDEX', basename( __FILE__ ) );

$lang   = filter_input( INPUT_GET, 'cos_lang', FILTER_SANITIZE_STRING );


if ( !$lang )   { $lang = 'fr'; }

$cos->lang = $lang;

$cos->loadRouter();

$app = $cos->router->app;
$module = $cos->router->module;
$action = $cos->router->action;
$cos->app = new \core\Citrus\mvc\App( $app );

if ( $cos->app->moduleExists( $module ) ) {
    $cos->app->createModule( $module, $action );
    if ( isset( $_SESSION['CitrusUser'] ) && get_class( $_SESSION['CitrusUser'] ) ) {
        $cos->user = $_SESSION['CitrusUser'];
    } else {
        $cos->user = new core\Citrus\User();
    }
    $actionSecure = $cos->app->module->getSecurity( $action );

    $cos->getController()->request->addParams( $cos->router->params );
    $cos->app->executeCtrlAction();
    $cos->shutdown();                   
} else {
    core\Citrus\Citrus::pageNotFound();
}