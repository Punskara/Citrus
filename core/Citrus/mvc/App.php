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
 * @version $Id$
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
     * Creates a module and a controller objects to launch the action
     * 
     * @param string  $moduleName  Name of the module we want to create
     * @param string  $action  Name of the action we want to execute.
     */
    public function createModule( $moduleName, $action ) {
        $this->module = new Module( $moduleName, $this->path );
        $this->ctrl = $this->module->createController(
            array( 'action' => $action )
        );
    }
    
    
    /**
     * Executes the controller method that the action determines.
     */
    public function executeCtrlAction() {
        $this->ctrl->executeAction();
        if ( $this->ctrl->layout === true ) {
            $this->view->displayLayout();
        } else {
            echo $this->ctrl->displayTemplate( $this->module->path );
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
    
}
