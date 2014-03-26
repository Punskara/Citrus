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
    // public $params;

    public $routes = array();
    public $route;

    public function __construct( $request_uri, $base_url = "" ) {
        $base_url = substr( $base_url, 1 );

        if (strpos( $request_uri, $base_url ) !== false ) {
            $request_uri = str_replace( $base_url, '', $request_uri );
        }
        
        $pos = strpos( $request_uri, '?' );
        if ( $pos ) $request_uri = substr( $request_uri, 0, $pos );
     
        $this->request_uri = $request_uri;
        $this->routes = array();
        
    }

    public function map( $rule, $target = array(), $conditions = array() ) {
        $this->routes[$rule] = new Route( 
            $rule, $this->request_uri, $target, $conditions 
        );
        return $this;
    }
/*
    public function quickMap( $rule, $controller, $conditions = array() ) {
        $rt = new Route( 
            $rule, $this->request_uri, Array( "controller" => $controller ), $conditions 
        );
        // $rt->controller = $controller;
        $this->routes[$rule] = $rt;
        return $this;
    }*/
    
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

    public function executeRoutes( $routes ) {
        if ( count( $routes ) ) {
            foreach( $routes as $r ) {
                $route = new Route( 
                    $r["url"], 
                    $this->request_uri, 
                    isset( $r["target"] ) ? $r["target"] : Array(), 
                    isset( $r["conditions"] ) ? $r['conditions'] : Array()
                );
                if ( $route->is_matched ) {
                    $this->setRoute( $route );
                    return $route;
                }
            }
            // throw new NoRouteFoundException();
        }
        return false;
    }

    public function getRouteURL() {
        return $this->route->url;
    }

    public function getController() {
        return $this->controller;
    }

    public function setController( $controller ) {
        $this->controller = $controller;
    }
    
    private function setRoute( $route ) {
        $this->route = $route;
    }   

    public function getRoute() {
        return $this->route;
    }

    public function hasRoute() {
        return $this->route instanceof Route;
    }
}