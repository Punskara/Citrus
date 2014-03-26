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
 * @subpackage Citrus\mvc\App
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use \core\Citrus\sys\Exception;
use \core\Citrus\routing\Router;
use \core\Citrus\http\Response;
use \core\Citrus\http\Request;

/*abstract*/ class App {
    
    /**
     * @var string
     */
    public $name;

    /**
     * @var \core\Citrus\http\Response
     */
    public $response;

    /**
     * @var \core\Citrus\http\Request
     */
    public $request;
    
    /**
     * @var \core\Citrus\mvc\Controller
     */
    public $controller;
        
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    protected $tpl_dir;

    /**
     * @var string
     */
    private $controllers_dir;

    public $router;
    
    /*abstract*/ protected function setViewSettings() {}
    protected function beforeExecuteAction() {}

    /**
     * Constructor.
     * 
     * @param string  $name  name of the app.
     * 
     * @throws \core\Citrus\sys\Exception if the name is not referenced.
     */
    public function __construct( $name, $path, $router ) {
        $this->name     = $name;
        $this->router   = $router;
        $this->request  = new Request();
        $this->response = new Response();

        if ( $router->hasRoute() ) {
            $this->request->addParams( $this->router->getRoute()->params );
            $this->setController( $this->router->getRoute()->getParam( 'controller' ) );
        }
    }
    

    /**
     * Executes the controller method that the action determines.
     *
     * @param array $request A Request object
     *
     */
    public function executeController() {
        if ( $this->shouldExecuteController() ) {
            $this->beforeExecuteAction();
            if ( $this->controller instanceof Controller ) {
                if ( $this->controller->actionExists() ) {
                    $this->beforeExecuteAction();
                    $act = $this->controller->executeAction( $this->request );
                    if ( $act !== false ) $this->output();
                } else $this->onActionNotFound();
            } elseif ( $this->controller instanceof \Closure ) {
                $this->controller->__invoke();
                // closure test
            }
        }
    }

    protected function output() {
        Citrus::getInstance()->response->sendHeaders();
        if ( $this->controller->view ) {
            $this->setViewSettings();
            echo $this->controller->view->render();
        }
    }

    public function getControllerUrl() {
        return url_to( 
            $this->name . '/' . 
            strtolower( $this->controller->getPrefix() ) . 
            '/', 1 
        );
    }



    public function setController( $controller ) {
        $this->controller = $controller;
    }


    public function setControllersDir( $dir ) {
        if ( file_exists( $this->path . $dir ) ) 
            $this->controllers_dir = $this->path . $dir;
        else throw new Exception( "Directory `$this->path$dir` does not exist." );
    }

    public function getControllersDir() {
        return $this->controllers_dir;
    }

    public function setTplDir( $dir ) {
        if ( file_exists( $this->path . $dir ) ) 
            $this->tpl_dir = $this->path . $dir;
        else throw new Exception( "Directory `$this->path$dir` does not exist." );
    }

    public function getTplDir() {
        return $this->tpl_dir;
    }

    public function setPath( $path ) {
        $this->path = $path;
    }

    public function getPath() {
        return $this->path;
    }

    public function __toString() {
        return $this->name;
    }

    public function shouldExecuteController() {
        return true;
    }
}
