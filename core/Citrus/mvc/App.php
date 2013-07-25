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
use core\Citrus\sys;

class App {
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var \core\Citrus\User
     */
    public $user;
    
    /**
     * @var string
     */
    public $titleTag;
    
    /**
     * @var \core\Citrus\mvc\Module
     */
    public $module;
    
    /**
     * @var \core\Citrus\mvc\Controller
     */
    public $ctrl;
    
    /**
     * @var \core\Citrus\View
     */
    public $view;
    
    /**
     * @var string
     */
    public $mainLayout;
    
    /**
     * @var string
     */
    public $path;
    
    /**
     * @var Array
     */
    public $security_exceptions = Array();


    public $isProtected = true;
    /**
     * Constructor.
     * 
     * @param string  $name  name of the app.
     * 
     * @throws \core\Citrus\sys\Exception if the name is not referenced.
     */
    public function __construct( $name ) {
        if ( is_dir( CITRUS_APPS_PATH . $name ) ) {
            $this->name = $name;
            $this->path = CITRUS_APPS_PATH . $name;
            $this->mainLayout = $this->path . '/templates/main.tpl.php';
            $this->view = new View( $this );
            $this->readConfig();
        } else {
            throw new sys\Exception( "Unkown app $name" );
        }
    }

    public function __toString() {
        return $this->name;
    }
    
    /**
     * Reads app config and view files
     * 
     */
    public function readConfig() {
        if ( file_exists( $this->path . '/config/config.php' ) ) {
            require_once $this->path . '/config/config.php';
        }
        if ( file_exists( $this->path . '/config/view.php' ) ) { 
            require_once $this->path . '/config/view.php';
        }
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
        // $args['moduleName'] = $ctrlName;
        if ( file_exists( $ctrlFile ) ) {
            $ctrlPath = str_replace( CITRUS_PATH, '', $args['path'] );
            $ctrlPath = str_replace( '/', '\\', $ctrlPath ) . '\Controller';
            try { 
                $r = new \ReflectionClass( $ctrlPath ); 
                $inst = $r->newInstanceArgs( $args ? $args : array() );

                $inException = in_array( $ctrlName, $this->security_exceptions );

                $this->ctrl = \core\Citrus\Citrus::apply( $inst, Array( 
                    'name' => $ctrlName, 

                    // isProtected = true  if appIsProtected   && !inException
                    // isProtected = true  if !appIsProtected  && inException
                    // isProtected = false if appIsProtected   && inException
                    // isProtected = false if !appIsProtected  && !inException
                    'isProtected' => $this->isProtected ? $inException ? false : true : $inException ? true : false
                ) );

                return $this->ctrl;
            } catch ( \Exception $e ) {
                prr($e, true);
            }
        } else {
            return false;
        }
    }
    
    
    /**
     * Executes the controller method that the action determines.
     */
    public function executeCtrlAction( $force_external_post = false ) {
        if ( $this->ctrl->actionExists() ) {
            if ( !$this->isProtected() ) {
                $act = $this->ctrl->executeAction( $force_external_post );
                if ( $act ) {
                    if ( $this->ctrl->layout === true )
                        $this->view->displayLayout();
                    else
                        echo $this->ctrl->displayTemplate();
                }
            } else $this->onActionProtected();
        } else $this->onActionNotFound();
    }


    /** 
      * @return true  if ( appIsProtected && !inException || !appIsProtected  && inException )
      * @return false if ( appIsProtected && inException || !appIsProtected  && !inException )
    */
    public function isProtected() {
        $protected = false;

        $closure = $this->isProtected;
        if ( is_object( $closure ) && get_class( $closure ) == "Closure" ) {
            $protected = $closure();
        } else {
            $protected = $closure;
        }

        $inException = in_array( $this->ctrl->name, $this->security_exceptions );
        return $protected ? 
                    $inException ? $this->ctrl->isActionProtected() : !$this->ctrl->isActionProtected() :
                    $inException ? !$this->ctrl->isActionProtected() : $this->ctrl->isActionProtected();
    }

    /**
     * Triggered when action is protected.
     * if controller doesn't have method "onActionProtected"
     */

    public function onActionProtected() {
        if ( method_exists( $this->ctrl, "onActionProtected" ) ) {
            $this->ctrl->onActionProtected();
        } else {
            $closure = $this->onActionProtected;
            if ( is_object( $closure ) && get_class( $closure ) == "Closure" ) {
                $closure();
            }
        }
    }

    /**
     * Triggered when action is not found.
     * if controller doesn't have method "onActionNotFound"
     */

    public function onActionNotFound() {
        if ( method_exists( $this->ctrl, "onActionNotFound" ) ) {
            $this->ctrl->onActionNotFound();
        } else {
            $closure = $this->onActionNotFound;
            if ( is_object( $closure ) && get_class( $closure ) == "Closure" ) {
                $closure();
            }
        }
    }

    
    /**
     * Creates a configuration file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateConfigFile() {
        if ( is_dir( $this->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/config.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $this->path . '/config/config.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }
    
    /**
     * Creates a 'view' file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateViewFile() {
        if ( is_dir( $this->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/view.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $this->path . '/config/view.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }

    /**
     * Creates a 'routing' file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateRoutingFile() {
        if ( is_dir( $this->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/routing.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $this->path . '/config/routing.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }
    
}
