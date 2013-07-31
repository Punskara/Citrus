<?php

$this->titleTag     = "Citrus";
$this->layout       = 'main';

$this->is_protected = false;


/*$this->isAccessAllowed  = function() use( &$self ) {
    // app : $self
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

    if ( $self->is_protected ) {
        if ( $self->controller->isActionProtected() ) $ret = $logged;
        else $ret = true;
    } else {
        if ( $self->controller->isActionProtected() ) $ret = $logged;
        else $ret = true;
    }
    return $ret;
};


$this->onActionProtected = function() {
    \core\Citrus\http\Http::redirect( CITRUS_PROJECT_URL . 'backend/login' );
};*/