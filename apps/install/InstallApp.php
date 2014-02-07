<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0.2                                                            |
|   Contact: devs@citrus-project.net                                        |
|      Info: http://citrus-project.net                                      |
|   Support: http://citrus-project.net/documentation/                       |
| ------------------------------------------------------------------------- |
|   Authors: Rémi Cazalet                                                   |
|          : Nicolas Mouret                                                 |
|   Founder: Studio Caramia                                                 |
|  Copyright (c) 2008-2012, Studio Caramia. All Rights Reserved.            |
| ------------------------------------------------------------------------- |
|   For the full copyright and license information, please view the LICENSE |
|   file that was distributed with this source code.                        |
'---------------------------------------------------------------------------'
*/

/**
 * @package Citrus
 * @subpackage Citrus\Host
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

namespace apps\install;
use \core\Citrus\Citrus;
use \core\Citrus\mvc\App;
use \core\Citrus\mvc\View;

class InstallApp extends App {
    public $titleTag     = "Citrus";
    public $layout       = 'main';
    public $is_protected = false;

    public function onBeforeExecuteAction() {}

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