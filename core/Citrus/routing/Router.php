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
 * @package Citrus\routing
 * @subpackage Citrus\routing\Router
 * @author Dan Sosedoff <dan.sosedoff@gmail.com>
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



/**
 * Thanks to http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
 */
 
namespace core\Citrus\routing;

use \core\Citrus\Citrus;
use \core\Citrus\mvc\App;
use \core\Citrus\sys\Exception;

class Router {
    
    public $request_uri;
    public $app;
    public $controller;
    public $action;
    public $params;

    public $routes = array();
    public $route;

    public function __construct( $base_url, $request_uri ) {
        
        $request    = $request_uri;
        $base_url   = substr( $base_url, 1 );
        
        if (strpos( $request, $base_url ) !== false ) {
            $request = str_replace( $base_url, '', $request );
        }
        
        $pos = strpos( $request, '?' );
        if ( $pos ) $request = substr( $request, 0, $pos );
     
        $this->request_uri = $request;
        $this->routes = array();
        
    }

    public function map( $rule, $target = array(), $conditions = array() ) {
        $this->routes[$rule] = new Route( 
            $rule, $this->request_uri, $target, $conditions 
        );
        return $this;
    }
    
    public function execute() {
        if ( count( $this->routes ) ) {
            foreach( $this->routes as $route ) {
                if ( $route->is_matched ) {
                    $this->setRoute( $route );
                    return;
                }
            }
            throw new NoRouteFoundException();
        }
        return $this;
    }

    public function getRouteURL() {
        return $this->route->url;
    }
    
    private function setRoute( $route ) {
        $this->route = $route;
        $params      = $route->params;

        if ( isset( $params['app'] ) ) {
            $this->app = $params['app']; 
            unset( $params['app'] );
        }

        if ( isset( $params['controller'] ) ) {
            $this->controller = $params['controller']; 
            unset( $params['controller'] );
        }

        if ( isset( $params['action'] ) ) {
            $this->action = $params['action']; 
            unset( $params['action'] );
        }

        $this->params = array_merge( $params, $_GET );
    }   
}