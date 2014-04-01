<?php

namespace apps\{app_name};
use \core\Citrus\Citrus;
use \core\Citrus\mvc\App;
use \core\Citrus\mvc\View;

class {app_class} extends App {
    public $titleTag     = "Citrus";
    public $layout       = 'main';
    public $is_protected = false;

    public function beforeExecuteAction() {}

    public function setViewSettings() {
        if ( !isset( $cos ) ) $cos = Citrus::getInstance();
        if ( $cos->debug ) {
            $this->controller->view->addCSS( 
                CTS_PROJECT_URL . 'citrus-debug/css/citrus.min.css' 
            );
            $this->controller->view->addCSS( 
                CTS_PROJECT_URL . 'citrus-debug/css/font-awesome.min.css'
            );
            $this->controller->view->addJS( 
                CTS_PROJECT_URL . 'citrus-debug/js/debug.min.js' 
            );
        }
        $this->controller->view->layout_file = $this->path . '/templates/' . $this->layout . '.tpl.php';
    }
}