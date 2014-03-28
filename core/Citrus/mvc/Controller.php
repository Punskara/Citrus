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
use \core\Citrus\http\Response;

/**
 * The C in MVC. Communicate with Citrus, the Model and the View to
 * execute the right action.
 */
abstract class Controller {
    
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
     * Contructor
     * 
     * @param string  $action  Name of the action we want to execute.
     */ 
    public function __construct( $action ) {
        $this->action = $action;
    }

    public function getView() {
        return $this->view;
    }

    public function setView( $path ) {
        $tpl_name   = strtolower( $this->getPrefix() ) . '/' . $this->action;
        $this->view = new View( $path . '/' . $tpl_name );
        return $this;
    }

    /**
     * Executes the action given by request. 
     * 
     */
    public function executeAction( $request ) {
        $cos = Citrus::getInstance();
        $action = "do_$this->action";

        if ( $cos->debug ) {
            $cos->getLogger( 'debug' )
                ->logEvent( 
                    'Launching action ' . $action . ' on module ' . $this->name 
                );
            $cos->debug->startNewTimer( "action " . $action );
        }

        $this->$action( $request );

        if ( $cos->debug ) $cos->debug->stopLastTimer();

        return true;
    }

    public function __invoke( $request ) {
        if ( !$this->actionExists() )
            return self::pageNotFound( $request );

        $this->executeAction( $request );
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
    static public function pageNotFound( $request, $message = null ) {
        $v = new View( CTS_PATH . '/core/Citrus/http/templates/pageNotFound' );
        $content = $request->is_XHR ? "" : $v->render();
        $rsp = new Response( 404, Array(), $content, $message );
        $rsp->sendHeaders();
        echo $rsp->content;
    }
    
    /**
     * Shows up the default "Page forbidden" template
     * Can be executed when the action is not allowed.
     *
     * @param string  $message  A message to display in the 404 page.
     */
    static public function pageForbidden( $message = null ) {
        $v = new View( CTS_PATH . '/core/Citrus/http/templates/pageForbidden' );
        $content = $request->is_XHR ? "" : $v->render();
        $rsp = new Response( 401, Array(), $content, $message );
        $rsp->sendHeaders();
        echo $rsp->content;
    }
    
    /**
     * Determines if the action exists.
     *
     * @return boolean True if the method exists, else false.
     */
    public function actionExists() {
        return method_exists( $this, 'do_' . $this->action );
    }

    public function do_static( $request ) {
        $cos        = Citrus::getInstance();
        $this->view = false;
        $uri        = $_SERVER['REQUEST_URI']; 
        $file_ext   = $request->param( 'ext' ); 
        $file_type  = $request->param( 'type' ); 
        $file_name  = $request->param( 'file' ); 
        $file_path  = $cos->app->getPath() . "/static/$file_type/$file_name$file_ext";
        $content    = "";
        $file_ext   = substr( $file_ext, 1 );

        if ( !file_exists( $file_path ) ) 
            $file_path = CTS_WWW_PATH . substr( $uri, 1 );

        if ( file_exists( $file_path ) ) {
            $content = file_get_contents( $file_path );
            switch ( $file_ext ) {
                case 'js':
                    $cos->response->content_type = 'application/javascript';
                    break;
                case 'css':
                    $cos->response->content_type = 'text/css';
                    break;
                default:
                    $cos->response->content_type = File::getType( $file_path );
                    break;
            }            
        } else self::pageNotFound();
        $cos->response->setCacheHeaders( $file_path );
        if ( $cos->response->code == '200' ) echo $content;
    }

    public function renderView() {
        if ( $this->view->templateExists() )
            return $this->view->render();
        return "";
    }
}
    