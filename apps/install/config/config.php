<?php

$this->titleTag             = "Citrus";
$this->layout               = 'main';

$this->isProtected 			= false;
$this->security_exceptions = Array(
    // "main",
);


/*$this->isProtected = function() {
    $cos = \core\Citrus\Citrus::getInstance();
    if ( isset( $_SESSION['CitrusUser'] ) && get_class( $_SESSION['CitrusUser'] ) ) {
        $cos->user = $_SESSION['CitrusUser'];
    } if ( isset($_SESSION['CitrusUserId'] ) ) {
        $cos->user = \core\Citrus\data\Model::selectOne(
            '\core\Citrus\User', (integer) $_SESSION['CitrusUserId']);
        if ( !$cos->user ) $cos->user = new \core\Citrus\User();
    } else {
        $cos->user = new \core\Citrus\User();
    }

    $logged = $cos->user->isLogged();
    if ( !$logged ) {
        $redir = $cos->projectName . '_REDIRECT_URI';
        if ( !isset( $_SESSION[$redir] ) ) {
            $_SESSION[$redir] = substr( $_SERVER['REQUEST_URI'], strlen( CITRUS_PROJECT_URL ) );
        }
        return true;
    }
    return false;
};*/