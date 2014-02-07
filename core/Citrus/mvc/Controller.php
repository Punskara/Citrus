<?php
/*
 * This file is part of Citrus. 
 *
 * (c) Rémi Cazalet <remi@caramia.fr>
 * Nicolas Mouret <nicolas@caramia.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package Citrus\mvc
 * @subpackage Citrus\mvc\Controller
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use \core\Citrus\sys\Debug;
use \core\Citrus\sys\Exception;
use \core\Citrus\utils\File;
use \core\Citrus\String;

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
     * @var \core\Citrus\mvc\View
     */
    public $view;
    
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
    public function __construct( $action ) {
        $this->action = $action;
    }
    
    /**
     * Executes the action given by request. 
     * 
     */
    public function executeAction( $request ) {
        $cos = Citrus::getInstance();
        $action = "do_$this->action";
            if ( $cos->logger )
                $cos->logger->logEvent( 'Launching action ' . $action . ' on module ' . $this->name );
            if ( $cos->debug )
                $cos->debug->startNewTimer( "action " . $action );

            $tpl_name = $this->action;
            $this->view = new View( $tpl_name );

            $this->view->layout = !$request->isXHR;
            $this->$action( $request );

            if ( $cos->debug ) $cos->debug->stopLastTimer();

            return true;
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
     * Exception action : action that is executed if the system catches an exception.
     *
     * @param Exception|\core\Citrus\sys\Exception  $e  The exception to handle.
     */
    public function do_Exception( $e ) {
        $cos = Citrus::getInstance();
        Debug::handleException( $e, $cos->debug );
    }
    

    /**
     * Shows up the default "Page not found" template
     * Is executed if the action doesn't exist.
     *
     * @param string  $message  A message to display in the 404 page.
     */
    static public function pageNotFound( $message = null ) {
        $cos = Citrus::getInstance();
        $cos->response->code = '404';
        $cos->response->message = $message;
        $cos->response->sendHeaders();
        ob_start();
        include CTS_PATH . '/core/Citrus/http/templates/pageNotFound.tpl' ;
        $tpl = ob_get_contents();
        ob_end_clean();
        echo $tpl;
        exit;
    }
    
    /**
     * Shows up the default "Page forbidden" template
     * Can be executed when the action is not allowed.
     *
     * @param string  $message  A message to display in the 404 page.
     */
    static public function pageForbidden( $message = null ) {
        $cos = Citrus::getInstance();
        $cos->response->code = '403';
        $cos->response->message = $message;
        $cos->response->sendHeaders();
        ob_start();
        include CTS_PATH . '/core/Citrus/http/templates/pageForbidden.tpl' ;
        $tpl = ob_get_contents();
        ob_end_clean();
        echo $tpl;
        exit;
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
    

    public function do_static( $request ) {
        $cos = Citrus::getInstance();
        $this->view = false;
        $uri = $_SERVER['REQUEST_URI']; 
        $file_ext = $request->param( 'ext' ); 
        $file_type = $request->param( 'type' ); 
        $file_name = $request->param( 'file' ); 
        $file_path = $cos->app->path . "/static/$file_type/$file_name$file_ext";
        $content = "";
        $file_ext = substr( $file_ext, 1 );
        if ( !file_exists( $file_path ) ) 
            $file_path = CTS_WWW_PATH . substr( $uri, 1 );

        if ( file_exists( $file_path ) ) {
            $content = file_get_contents( $file_path );
            switch ( $file_ext ) {
                case 'js':
                    $cos->response->contentType = 'application/javascript';
                    break;
                case 'css':
                    $cos->response->contentType = 'text/css';
                    break;
                default:
                    $cos->response->contentType = File::getType( $file_path );
                    break;
            }            
        } else self::pageNotFound();
        $cos->response->setCacheHeaders( $file_path );
        if ( $cos->response->code == '200' ) echo $content;
    }
}
