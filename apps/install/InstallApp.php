<?php

namespace apps\install;
use \core\Citrus\Citrus;
use \core\Citrus\mvc\App;
use \core\Citrus\mvc\View;

class InstallApp extends App {
    public $titleTag     = "Citrus";
    public $layout       = 'main';
    public $is_protected = false;

    public function setViewSettings() {

        if ( !isset( $cos ) ) $cos = \core\Citrus\Citrus::getInstance();
        if ( $cos->debug ) {
            $this->controller->view->addCSS( 
                CITRUS_PROJECT_URL . 'citrus-debug/css/citrus.min.css' 
            );
            $this->controller->view->addCSS( 
                CITRUS_PROJECT_URL . 'citrus-debug/css/font-awesome.min.css'
            );
            $this->controller->view->addJS( 
                CITRUS_PROJECT_URL . 'citrus-debug/js/debug.min.js' 
            );
        }

        $this->controller->view->layout_file = $this->path . '/templates/main.tpl.php';

        $this->controller->view->setCSS( array( 
            CITRUS_PROJECT_URL . 'install/css/install.css',
        ) );

        $this->controller->view->setJS( array(
            'lib/jquery-1.10.2.min.js',
            'lib/jquery.json-2.4.min.js',
            CITRUS_PROJECT_URL . 'install/js/install.js'
        ) );
    }
}