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
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use core\Citrus\http;
use core\Citrus\sys;
use core\Citrus\utils;

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
     * @var string
     */
    public $name;
    
    /**
     * @var \core\Citrus\http\Request
     */
    public $request;
    
    /**
     * @var \core\Citrus\mvc\View
     */
    public $view;
    
    /**
     * @var string
     */
    public $path;
    
    /**
     * @var string
     */
    public $moduleName;
    
    /**
     * @var Boolean
     */
    public $is_protected;


    /**
     * @var Array
     */
    public $security_exceptions = Array();

    /**
     * Contructor
     * 
     * @param string  $action  Name of the action we want to execute.
     */ 
    public function __construct( $action, $path = '' ) {    
        $this->action = $action;
        $this->view = new View( $action );
        $this->path = $path;
    }
    
    
    /**
     * Executes the action given by request. 
     * Stops if the request method is POST and referer is external.
     * Creates the http response and executes do_PageNotFound if action
     * doesn't exist.
     * 
     * @see do_PageNotFound
     */
    public function executeAction( $request, $force_external_post = false ) {
        $cos = Citrus::getInstance();
        if ( $request->method == 'POST' && !$request->refererIsInternal() && !$force_external_post ) {
            $this->do_PageNotFound();
            return true;
        }

        $action = "do_$this->action";
        try {
            if ( $cos->logger )
                $cos->logger->logEvent( 'Launching action ' . $action . ' on module ' . $this->name );
            if ( $cos->debug )
                $cos->debug->startNewTimer( "action " . $action );

            $this->view->layout = !$request->isXHR;
            $this->$action( $request );

            if ( $cos->debug ) $cos->debug->stopLastTimer();

            return true;
        } catch ( \core\Citrus\sys\Exception $e ) {
            $this->do_Exception( $e );
            return false;
        } catch ( \PDOException $e ) {
            $this->do_Exception( $e );
            return false;
        }
    }

    /** 
      * @return true  if ( ctrlIsProtected && !inException || !ctrlIsProtected  && inException )
      * @return false if ( ctrlIsProtected && inException || !ctrlIsProtected  && !inException )
    */
    public function isActionProtected() {
        $inException = in_array( $this->action, $this->security_exceptions );
        return $this->is_protected ? $inException ? false : true : $inException ? true : false;
    }


    /**
     * Triggered when action is protected
     */
    public function onActionProtected() {
        $this->do_PageForbidden();
    }

    /**
     * Triggered when action is not found
     */
    public function onActionNotFound() {
        $this->do_PageNotFound();
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
     * Shows up the default "Page not found" template
     * Is executed if the action doesn't exist.
     *
     * @param string  $message  A message to display in the 404 page.
     * @see \core\Citrus\Citrus::pageNotFound()
     */
    public function do_PageNotFound( $message = null ) {
        Citrus::pageNotFound( $message );
    }

    /**
     * Shows up the default "Page forbidden" template
     * Can be executed when the action is not allowed.
     *
     * @param string  $message  A message to display in the 404 page.
     * @see \core\Citrus\Citrus::pageNotFound()
     */
    public function do_PageForbidden( $message = null ) {
        Citrus::pageForbidden( $message );
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
     * Displays the action template.
     *
     * @return  string  The content of the template.
     */
    public function displayTemplate() {
        return $this->view->display();
    }
    
    public function getPath() {
        return dirname( __FILE__ );
    }

    public function do_static( $request ) {
        $cos = Citrus::getInstance();
        $this->view = false;
        $uri = $_SERVER['REQUEST_URI']; 
        $file_ext = $request->param( 'ext' ); 
        $file_type = $request->param( 'type' ); 
        $file_name = $request->param( 'file' ); 
        $file_path = $cos->app->path . "/static/$file_type/$file_name$file_ext";
        $content = "";
        if ( !file_exists( $file_path ) ) 
            $file_path = CITRUS_WWW_PATH . substr( $uri, 1 );

        if ( file_exists( $file_path ) ) {
            switch ( $file_type ) {
                case 'js':
                    $cos->response->contentType = 'application/javascript';
                    if ( $cos->debug ) {
                        $content = file_get_contents( $file_path );
                    } else {
                        $content = \core\lib\JShrink\Minifier::minify( file_get_contents( $file_path ) );
                    }
                    break;
                case 'css':
                    $cos->response->contentType = 'text/css';
                    $content = file_get_contents( $file_path );
                    break;
                default:
                    $cos->response->contentType = utils\File::getType( $file_path );
                    $content = file_get_contents( $file_path );
                    break;
            }            
        } else Citrus::pageNotFound();
        $cos->response->setCacheHeaders( $file_path );
        if ( $cos->response->code == '200' ) echo $content;
    }
}
