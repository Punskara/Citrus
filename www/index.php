<?php
include '../boot.php';
define( 'CITRUS_INDEX', basename( __FILE__ ) );

$cos->loadRouter();

$app = $cos->router->app;
$module = $cos->router->controller;
$action = $cos->router->action;

$cos->app = new \core\Citrus\mvc\App( $app );
if ( $cos->app->moduleExists( $module ) ) {
    $cos->app->createModule( $module, $action );
    
    if ( isset( $_SESSION['CitrusUser'] ) && get_class( $_SESSION['CitrusUser'] ) ) {
        $cos->user = $_SESSION['CitrusUser'];
    } if (isset($_SESSION['CitrusUserId'])) {
        $cos->user = \core\Citrus\data\Model::selectOne(
            '\core\Citrus\User', (integer) $_SESSION['CitrusUserId']);
        if ( !$cos->user ) $cos->user = new \core\Citrus\User();
    } else {
        $cos->user = new \core\Citrus\User();
    }

    $actionSecure = $cos->app->module->getSecurity( $action );
    if ( $cos->router->app == 'backend' && $cos->app->module->isSecure && !$cos->user->isLogged() && $actionSecure ) {
        $redir = $cos->projectName . '_REDIRECT_URI';
        if (!isset($_SESSION[$redir])) {
            $_SESSION[$redir] = substr( $_SERVER['REQUEST_URI'], strlen( CITRUS_PROJECT_URL ) );
        }
        
        core\Citrus\http\Http::redirect( '/backend/Main/login.html' );
    }

    $cos->getController()->request->addParams( $cos->router->params );
    $cos->app->executeCtrlAction();
    $cos->shutdown();
} else {
    core\Citrus\Citrus::pageNotFound();
}