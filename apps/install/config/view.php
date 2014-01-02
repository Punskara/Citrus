<?php

$this->layout_file = CITRUS_APPS_PATH . 'install/templates/main.tpl.php';

$this->setCSS( array( 
    '/static/install/css/install.css',
) );
// $this->addCSS( CITRUS_PROJECT_URL . 'static/install/css/install.css' );

$this->setJS( array(
    'lib/jquery-1.10.2.min.js',
    'lib/jquery.json-2.4.min.js',
    '/static/install/js/install.js'
) );
$this->addJS( CITRUS_PROJECT_URL . 'static/install/js/install.js' );

if ( !isset( $cos ) ) $cos = \core\Citrus\Citrus::getInstance();
if ( $cos->debug ) {
    $this->addCSS( '/citrus-debug/css/citrus.min.css' );
    $this->addCSS( '/citrus-debug/css/font-awesome.min.css' );
    $this->addJS( '/citrus-debug/js/debug.min.js' );
}