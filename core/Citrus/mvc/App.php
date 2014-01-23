<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0                                                            |
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
use core\Citrus\sys;

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
    

    abstract public function setViewSettings();


    /**
     * Constructor.
     * 
     * @param string  $name  name of the app.
     * 
     * @throws \core\Citrus\sys\Exception if the name is not referenced.
     */
    public function __construct( $name ) {
        $this->name = $name;
    }

    public function __toString() {
        return $this->name;
    }
    
    /**
     * scans the modules directory and checks the existence 
     * of the module named $module. Returns true if the module exists, else returns false.
     *
     * @param string $module module name
     * @return boolean
     */
    public function moduleExists( $module ) {
        $dir = $this->path . '/modules';
        $found = false;
        if ( is_dir( $dir ) ) {
            if ( $dh = opendir( $dir ) ) {
                while ( ( $file = readdir( $dh ) ) !== false && !$found ) {
                    if ( substr( $file, 0, 1) != '.' ) {
                        $found = ( $file == $module );
                    }
                }
                closedir( $dh );
            }
        }
        return $found;
    }
 
    /**
     * Creates the controller which will execute the action requested.
     *
     * @param array $args Arguments to pass to the contructor of the controller
     * @param array $props Properties to set on the construction of the controller
     *
     * @return \core\Citrus\mvc\Controller|boolean Whether if the controller file exists or not.
     */
    public function createController( $ctrlName, $action ) {
        $args['action'] = $action;
        $args['path'] = $this->path . '/modules/' . $ctrlName;
        $ctrlFile = $args['path'] . '/Controller.php';
        $ctrlPath = str_replace( CITRUS_PATH, '', $args['path'] );
        $ctrlPath = str_replace( '/', '\\', $ctrlPath ) . '\Controller';

        if ( !$ctrlName ) {
            $this->controller = new Controller( $action );
        } else {
            if ( file_exists( $ctrlFile ) && class_exists( $ctrlPath ) ) {
                $r = new \ReflectionClass( $ctrlPath ); 
                $inst = $r->newInstanceArgs( $args ? $args : array() );
                $this->controller = Citrus::apply( $inst, Array( 
                    'name' => $ctrlName, 
                ) );
                if ( $this->controller->is_protected == null ) {
                    $this->controller->is_protected = $this->is_protected;
                }
                return $this->controller;
            } else {
                throw new sys\Exception( "Unable to find controller '$ctrlName'" );
                return false;
            }
        }
        return $this->controller;

    }
    
    
    /**
     * Executes the controller method that the action determines.
     */
    public function executeCtrlAction( $force_external_post = false ) {
        $cos = Citrus::getInstance();
        if ( $this->controller->actionExists() ) {
            $act = $this->controller->executeAction( $cos->request, $force_external_post );
            if ( $act !== false ) $this->output();
        } else $this->onActionNotFound();
    }

    public function output() {
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

    public function onActionProtected() {
        $this->controller->onActionProtected();
    }

    /**
     * Triggered when action is not found.
     * if controller doesn't have method "onActionNotFound"
     */

    public function onActionNotFound() {
        $this->controller->onActionNotFound();
    }

    public function getControllerUrl() {
        return CITRUS_PROJECT_URL . $this->name . '/' . $this->controller->name . '/';
    }

    static public function load( $name ) {
        $app_path = $name;
        $class_name = str_replace( '/', '\\', '/apps/' . $app_path . '/' . ucfirst( $name ) . 'App' );
        if ( class_exists( $class_name ) ) {
            $r = new \ReflectionClass( $class_name ); 
            $app = $r->newInstanceArgs( array( $name ) );            
            $app->path = CITRUS_APPS_PATH . $app_path;
            return $app;
        } else {
            throw new sys\Exception( "Unable to find app '$name'" );
            return false;
        }
    }
}
