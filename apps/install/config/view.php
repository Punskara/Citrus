<?php

$this->view->setStyleSheets( array(
    'install.css',
) );

$this->view->setJavascriptFiles( array(
    'lib/jquery-1.10.2.min.js',
    'lib/jquery.json-2.4.min.js',
    'install.js'
) );

if ( !isset( $cos ) ) $cos = \core\Citrus\Citrus::getInstance();
if ( $cos->debug ) {
    $this->view->addStyleSheet( '/citrus-debug/css/citrus.min.css' );
    $this->view->addStyleSheet( '/citrus-debug/css/font-awesome.min.css' );
    $this->view->addJavascript( '/citrus-debug/js/debug.min.js' );
}