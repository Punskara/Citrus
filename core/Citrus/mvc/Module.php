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
 * @subpackage Citrus\mvc\Module
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;

/**
 * This class manages a module (that owns a controller)
 */
class Module {
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $path;
    
    /**
     * @var boolean
     */
    public $isProtected = false;
    
    /**
     * @var array
     */
    public $actionsProtected;
    
    /**
     * @var \core\Citrus\mvc\Controller
     */
    public $ctrl;
    
    
    /**
     * Constructor
     *
     * @param string $name Name of the module
     * @param string $appPath Path to the app that contains the module.
     */
    public function __construct( $name, $appPath ) {
        $this->name = $name;
        $this->path = $appPath . '/modules/' . $name;
        // $this->readConfig();
    }
    
    /**
     * Creates the controller which will execute the action requested.
     *
     * @param array $args Arguments to pass to the contructor of the controller
     * @param array $props Properties to set on the construction of the controller
     *
     * @return \core\Citrus\mvc\Controller|boolean Whether if the controller file exists or not.
     */
    public function createController( $args = null, $props = null ) {
        $ctrlFile = $this->path . '/Controller.php';
        $args['path'] = $this->path;
        $args['moduleName'] = $this->name;
        if ( file_exists( $ctrlFile ) ) {
            //include_once $ctrlFile;
            $ctrlName = ucfirst( $this->name ) . '_Controller';
            $ctrlName = str_replace( CITRUS_PATH, '', $this->path );
            $ctrlName = str_replace( '/', '\\', $ctrlName ) . '\Controller';
            $r = new \ReflectionClass( $ctrlName );
            $inst = $r->newInstanceArgs( $args ? $args : array() );
            $this->ctrl = !$props ? $inst : Citrus::apply( $inst, $props );
            return $this->ctrl;
        } else {
            return false;
        }
    }

    /**
     * Calls the configuration file of the module (located in module_dir/config/)
     */
    public function readConfig() {
        if ( file_exists( $this->path . '/config/config.php' ) ) {
            include $this->path . '/config/config.php';
        }
    }
    
    /**
     * Creates a configuration file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateConfigFile() {
        if ( is_dir( $this->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/module.config.tpl';
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
     * Creates a controller class file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateControllerFile() {
        if ( is_dir( $this->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/Controller.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            $content = str_replace( "{ModuleName}", ucfirst( $this->name ), $content );
            $write = false;
            if ( $content ) {
                $file = fopen( $this->path . '/Controller.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }
    
    /**
     * Gets the security settings for the required action.
     *
     * @param string $action Action name.
     *
     * @return boolean Whether the action is protected or not.
     */
    public function getSecurity( $action = null ) {
        $actionProtected = $this->isProtected;
        if ( $action && isset( $this->actionsProtected[$action] ) ) {
            $actionProtected = $this->actionsProtected[$action];
        }
        return $actionProtected;
    }
}