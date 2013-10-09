<?php

$this->setStyleSheets( array(
    'install.css',
) );

$this->setJavascriptFiles( array(
    'lib/jquery-1.10.2.min.js',
    'lib/jquery.json-2.4.min.js',
    'install.js'
) );

if ( !isset( $cos ) ) $cos = \core\Citrus\Citrus::getInstance();
if ( $cos->debug ) {
    $this->addStyleSheet( '/citrus-debug/css/citrus.min.css' );
    $this->addStyleSheet( '/citrus-debug/css/font-awesome.min.css' );
    $this->addJavascript( '/citrus-debug/js/debug.min.js' );
}