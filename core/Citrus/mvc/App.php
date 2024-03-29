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
 * @package Citrus\mvc
 * @subpackage Citrus\mvc\App
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use core\Citrus\http\Request;
use core\Citrus\Citrus;
use core\Citrus\sys\Exception;

abstract class App {
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var \core\Citrus\mvc\Controller
     */
    public $controller;
        
    /**
     * @var string
     */
    public $path;
    
    /**
     * @var Boolean
     */
    public $is_protected = false;

    /**
     * @var Closure
     */
    public $isAccessAllowed;

    /**
     * @var Closure
     */
    public $onActionProtected;

    /**
     * @var string
     */
    public $tpl_dir;
    
    abstract protected function setViewSettings();
    protected function onBeforeExecuteAction() {}

    /**
     * Constructor.
     * 
     * @param string  $name  name of the app.
     * 
     * @throws \core\Citrus\sys\Exception if the name is not referenced.
     */
    public function __construct( $name ) {
        $this->name = $name;
        $this->tpl_dir = CITRUS_APPS_PATH . $this->name . '/templates/';
        if ( !is_dir( $this->tpl_dir ) ) 
            throw new Exception( "Application template directory doesn't exist." );
    }

    public function __toString() {
        return $this->name;
    }
 
    /**
     * Creates the controller which will execute the action requested.
     *
     * @param array $name Controller name
     * @param array $action Action to be performed
     *
     * @return \core\Citrus\mvc\Controller|boolean Whether if the controller file exists or not.
     */
    public function createController( $name, $action ) {
        if ( !$name ) $this->controller = new Controller( $action );
        else {
            $args       = Array( 'action' => $action );
            $path       = $this->path . '/modules/' . $name;
            $class_file = $path . '/Controller.php';
            $class_name = str_replace( CITRUS_PATH, '', $path );
            $class_name = str_replace( '/', '\\', $class_name ) . '\Controller';

            if ( file_exists( $class_file ) && class_exists( $class_name ) ) {
                $r = new \ReflectionClass( $class_name ); 
                $this->controller = $r->newInstanceArgs( $args ? $args : array() );
                $this->controller->name = $name;

                if ( $this->controller->is_protected == null ) {
                    $this->controller->is_protected = $this->is_protected;
                }
                return $this->controller;
            } else {
                throw new Exception( "Unable to find controller '$name'" );
                return false;
            }
        }
        return $this->controller;
    }
    

    /**
     * Executes the controller method that the action determines.
     *
     * @param array $name Controller name
     * @param array $action Action to be performed
     *
     * @return \core\Citrus\mvc\Controller|boolean Whether if the controller file exists or not.
     */
    public function executeCtrlAction() {
        $cos = Citrus::getInstance();
        if ( $this->controller->actionExists() ) {
            $this->onBeforeExecuteAction();
            $act = $this->controller->executeAction( $cos->request );
            if ( $act !== false ) $this->output();
        } else $this->onActionNotFound();
    }

    protected function output() {
        Citrus::getInstance()->response->sendHeaders();
        if ( $this->controller->view ) {
            $this->setViewSettings();
            echo $this->controller->view->render();
        }
    }

    /**
     * Triggered when action is protected.
     * if controller doesn't have method "onActionProtected"
     */

    protected function onActionProtected() {
        Controller::pageForbidden();
    }

    /**
     * Triggered when action is not found.
     * if controller doesn't have method "onActionNotFound"
     */

    protected function onActionNotFound() {
        Controller::pageNotFound();
    }


    /**
     * Creates an instance of requested App.
     * @param $name Name of requested App
     * @throws Exception when requested app class is not found.
     * @return subclass of \core\Citrus\mvc\App
     */

    static public function load( $name ) {
        $app_path = $name;
        $class_name = str_replace( '/', '\\', '/apps/' . $app_path . '/' . ucfirst( $name ) . 'App' );
        if ( class_exists( $class_name ) ) {
            $r = new \ReflectionClass( $class_name ); 
            $app = $r->newInstanceArgs( array( $name ) );            
            $app->path = CITRUS_APPS_PATH . $app_path;
            return $app;
        } else {
            throw new Exception( "Unable to find app '$name'" );
            return false;
        }
    }

    /**
     * Gets list of applications existing in filesystem.
     *
     * @return array An array of apps names
     */
    static public function listApps() {
        $dir = CITRUS_APPS_PATH;
        $apps = array();
        
        if ( is_dir( $dir ) ) {
            if ( $dh = opendir( $dir ) ) {
                while ( ( $file = readdir( $dh ) ) !== false ) {
                    if ( substr( $file, 0, 1) != '.' ) {
                        if ( is_dir( CITRUS_APPS_PATH . $file ) ) {
                            $apps[] = $file;
                        }
                    }
                }
                closedir( $dh );
            }
        }
        return $apps;
    }
}
