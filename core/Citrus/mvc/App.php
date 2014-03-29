<?php
/*
 * This file is part of Citrus. 
 *
 * (c) Rémi Cazalet <remi@caramia.fr>
 * Nicolas Mouret <nicolas@caramia.fr>
 *
 * For the full copyright and license information, please viewview the LICENSE
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
     * @var \core\Citrus\http\Request
     */
    public $request;
    
    /**
     * @var \core\Citrus\mvc\Controller
     */
    protected $controller;
        
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    protected $tpl_dir;

    /**
     * @var \core\Citrus\mvc\View
     */
    protected $view;


    /**
     * @var \core\Citrus\routing\Router
     */
    public $router;
    
    /*abstract*/ protected function setViewSettings() {}
    protected function beforeExecuteAction() {}

    /**
     * Constructor.
     * 
     * @param string  $name  name of the app.
     * @param \core\Citrus\routing\Router  $router app router
     * 
     * @throws \core\Citrus\sys\Exception if the name is not referenced.
     */
    public function __construct( $name, $router ) {
        $this->name     = $name;
        $this->router   = $router;
        $this->request  = new Request();

        if ( $this->router->hasMatchedRoute() ) {
            $this->request->addParams( $this->router->getRoute()->params );
            $this->setController( $this->router->getRoute()->getParam( 'cos_controller' ) );
        }
    }
    

    /**
     * Executes the controller method that the action determines.
     *
     * @param array $request A Request object
     *
     */
    public function executeController( $request ) {
        if ( $this->shouldExecuteController() ) {
            $this->beforeExecuteAction();
            return $this->controller->__invoke( $request );
        }
        return false;
    }

    public function output() {
        if ( $this->view ) {
            $this->setViewSettings();
            return $this->view->render();
        }
        return "";
    }

    public function shouldExecuteController() {
        return true;
    }

    public function setController( $controller ) {
        $this->controller = $controller;
        return $this->controller;
    }

    public function getController() {
        return $this->controller;
    }

    public function __toString() {
        return $this->name;
    }

    public function setView( $view ) {
        $this->view = $view;
    }

    public function getView() {
        return $this->view;
    }
}
