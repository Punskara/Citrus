<?php

$this->view->setStyleSheets( array(
) );


$this->view->setJavascriptFiles( array(
) );


if ( !isset( $cos ) ) $cos = \core\Citrus\Citrus::getInstance();
if ( $cos->debug ) {
    $this->view->addStyleSheet( '/citrus-debug/css/citrus.min.css' );
    $this->view->addStyleSheet( '/citrus-debug/css/font-awesome.min.css' );
    $this->view->addJavascript( '/citrus-debug/js/debug.min.js' );
}
