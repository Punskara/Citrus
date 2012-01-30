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
 * @subpackage Citrus\mvc\Controller
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use core\Citrus\http;
use core\Citrus\sys;

/**
 * The C in MVC. Communicate with Citrus, the Model and the View to
 * execute the right action.
 */
class Controller {
    
    /**
     * @var string
     */
    public $action;
    
    /**
     * @var \core\Citrus\http\Request
     */
    public $request;
    
    /**
     * @var \core\Citrus\mvc\Template
     */
    public $template;
    
    /**
     * @var string
     */
    public $pageTitle;
    
    /**
     * @var boolean
     */
    public $layout = true;
    
    /**
     * @var string
     */
    public $metadesc;
    
    /**
     * @var string
     */
    public $metakey;
    
    /**
     * @var string
     */
    public $path;
    
    /**
     * @var string
     */
    public $moduleName;
    
    /**
     * Contructor
     * 
     * @param string  $action  Name of the action we want to execute.
     */ 
    public function __construct( $action ) {    
        $this->action = $action;
        $this->request = new http\Request();
        $this->template = new Template( $action );
    }
    
    
    /**
     * Executes the action given by request. 
     * Stops if the request method is POST and referer is external.
     * Creates the http response and executes do_PageNotFound if action
     * doesn't exist.
     * 
     * @see do_PageNotFound
     */
    public function executeAction() {
        if ( $this->request->method == 'POST' && !$this->request->refererIsInternal() ) {
            exit;
        }
        $cos = Citrus::getInstance();
        $response = new http\Response();
        if ( $this->actionExists() ) {
            $action = "do_$this->action";
            $response = new http\Response();
            try {
                if ( $cos->logger ) {
                    $cos->logger->logEvent( 'Launching action ' . $action . ' on module ' . $cos->app->module->name );
                }
                if ( $cos->debug ) {
                    $cos->debug->startNewTimer( "action " . $action );
                }
                
                $this->$action();
                
                $response = new http\Response();
                $response->code = '200';
                $response->sendHeaders();
                
                if ( $cos->debug ) {
                    $cos->debug->stopLastTimer();
                }
            } catch ( Exception $e ) {
                $this->do_Exception( $e );
                exit;
            } catch ( \PDOException $e ) {
                $this->do_Exception( $e );
            }
        } else {
            $message = $cos->debug ? 'Action do_' . $this->action . ' does not exist.' : 'The page you requested does not exist.';
            $this->do_PageNotFound( $message );
        }
    }
    
    /**
     * Exception action : action that is executed if the system catches an exception.
     *
     * @param Exception|\core\Citrus\sys\Exception  $e  The exception to handle.
     */
    public function do_Exception( $e ) {
        $cos = Citrus::getInstance();
        sys\Debug::handleException( $e, $cos->debug );
    }
    
    /**
     * Is executed if the action doesn't exist.
     *
     * @param string  $message  A message to display in the 404 page.
     * @see \core\Citrus\Citrus::pageNotFound()
     */
    public function do_PageNotFound( $message = null ) {
        Citrus::pageNotFound( $message );
    }
    
    /**
     * Determines if the action exists.
     *
     * @return boolean True if the method exists, else false.
     */
    public function actionExists() {
        return method_exists( $this, 'do_' . $this->action );
    }
    
    
    /**
     * Display the action template.
     *
     * @param string  $path  The path to the template.
     *
     * @return  string  The content of the template.
     */
    public function displayTemplate( $path ) {
        return $this->template->display( $path );
    }
    
    /**
     * Loads the template of another action than the action given.
     * 
     * @param  string  $action  the action which we want to load the template
     */
    public function loadActionTemplate( $action ) {
        $tplFile = $this->module->path . '/templates/' . $action . '.tpl.php';
        if ( file_exists( $tplFile ) ) {
            $this->app->view->template = $tplFile;
            $this->template->name = $action;
        }
    }
    
    
    /**
     * Generic function to save an object in database. Only accessible in POST method.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app. If used with AJAX, this will only display "ok".
     */
    public function do_save() {
        $cos = Citrus::getInstance();
        if ( $this->request->method != 'POST' ) {
            if ( $cos->debug ) {
                throw new Exception( 'Bad method request' );
            } else {
                Citrus::pageNotFound();
            }
        } else {
            #vexp($_POST);
            $type = $this->request->param( 'modelType', FILTER_SANITIZE_STRING );
            if ( class_exists( $type ) ) {
                $inst = new $type();
                if ( isset( $_FILES ) ) {
            		foreach ( $_FILES as $name => $file ) {
            			if ( !empty( $file['name'] ) ) {
            			    if ( $inst->$name ) {
								unlink( CITRUS_PATH . 'web/upload' . $inst->$name );
							}
            				$upld = new kos_http_Uploader( $file );
            				$upld->readFile();
            				$up = $upld->moveFile( CITRUS_PATH . '/web/upload/' );
            				$inst->args[$name] = $inst->$name = '/web/upload/' . $upld->fileName;
            			}
            		}
            	}
                $inst->hydrateByFilters();
                $rec = $inst->save();
                #vexp($rec);exit();
                if ( $this->request->isXHR ) {
                    if ( $rec ) {
                        echo "ok";
                        exit;
                    }
                } else {
                    $loc = "index.php?cos_app={$cos->app->name}&cos_module={$cos->app->module->name}&cos_action=index";
                    Http::redirect( $loc );
                }
            }
        }
    }
    
    /**
     * Generic function to delete an object from database.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app.
     * 
     * @param string  $resourceType  Name of the class of the object we want to delete.
     * 
     * @todo Throw an exception if class $resourceType doesn't exist
     * @todo Prevent this function to be executed if method is not POST
     * @todo Handle the case that the request is sent with AJAX.
     */
    public function do_delete( $resourceType = null ) {
        $cos = Citrus::getInstance();
        $module = $this->module->name;
        $id = $this->request->param( 'id', FILTER_VALIDATE_INT );
        if ( $id ) {
            if ( class_exists( $resourceType ) ) {
                data\Model::deleteOne( $resourceType, $id );
            }
            $loc = "index.php?cos_app={$cos->app->name}&cos_module={$cos->app->module->name}&cos_action=index";
            Http::redirect( $loc );
        }
    }

    /**
     * Generic function to delete several objects from database.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app.
     * 
     * @param string  $resourceType  Name of the class of the object we want to delete.
     * 
     * @todo Throw an exception if class $resourceType doesn't exist
     * @todo Prevent this function to be executed if method is not POST
     * @todo Handle the case that the request is sent with AJAX.
     */
    public function do_deleteSeveral( $resourceType = null ) {
        $module = $this->module->name;
        $ids = $_POST['delete'];
        if ( is_array( $ids ) ) {
            if ( class_exists( $resourceType ) ) {
                data\Model::deleteSeveral( $resourceType, $ids );
            }
            $loc = "index.php?cos_app={$cos->app->name}&cos_module={$cos->app->module->name}&cos_action=index";
            Http::redirect( $loc );
        }
    }
    public function do_captcha() {
        $this->layout = false;
        $cap = new Captcha();
        $cap->display();
    }
}