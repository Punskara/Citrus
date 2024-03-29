<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0.2                                                            |
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

    public $ext = '';

    public $routes = array();
    public $route;

    public function __construct( $base_url ) {
        
        $request = $_SERVER['REQUEST_URI'];
        $base_url = substr( $base_url, 1 );
        if (strpos( $request, $base_url ) !== false ) {
            $request = str_replace( $base_url, '', $request );
        }
        
        $pos = strpos( $request, '?' );
        if ( $pos ) $request = substr( $request, 0, $pos );
     
        $this->request_uri = $request;
        $this->routes = array();
        
    }

    public function defaultRoutes() {
        $this->map( '/:app/:controller/:action' . $this->ext );
    }

    public function map( $rule, $target = array(), $conditions = array() ) {
        $this->routes[$rule] = new Route( 
            $rule, $this->request_uri, $target, $conditions 
        );
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
    }

    public function getRouteURL() {
        return $this->route->url;
    }
    
    private function setRoute( $route ) {
        $this->route_found = true;
        $this->route = $route;
        $params = $route->params;

        if ( isset( $params['app'] ) ) {
            $this->app = $params['app']; 
            // $this->loadAppRoutes();
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
      
    public function loadRoutes() {
        $routesFile = CITRUS_PATH . '/config/routing.php';
        
        if ( file_exists( $routesFile ) ) {
            $routing = include $routesFile;
            if ( isset( $routing['routes'] ) ) {
                $routes = $routing['routes'];
                if ( count( $routes ) ) {
                    foreach ( $routes as $route ) {
                        if ( isset( $route['url'] ) && isset( $route['target'] ) ) {
                            $this->map( 
                                $route['url'], 
                                $route['target'], 
                                isset( $route['conditions'] ) ? $route['conditions'] : Array() 
                            );
                        }
                    }
                }
            }
            // browsing apps files
            $apps = App::listApps();
            foreach ( $apps as $app ) $this->loadAppRoutes( $app );
            
         } else {
             throw new Exception( "No routing file found." );
         }
    }
    

    /**
     * Checks the existence of routing file in app config file.
     * Loads content if true.
     */

    public function loadAppRoutes( $app = null ) {
        $virtual = true;
        if ( is_null($app)) {
            $app = $this->app;
            $virtual = false;
        } 
        $routesFile = CITRUS_APPS_PATH . $app . '/config/routing.php';

        if ( file_exists( $routesFile ) ) {            
            $routes = include $routesFile;
            if ( isset( $routes['routes'] ) && is_array( $routes['routes'] ) && count( $routes ) ) {
                foreach ( $routes['routes'] as $route ) {
                    if ( isset( $route['url'] ) ) {
                        $target = Array();
                        if ( isset( $route['target'] ) ) $target = $route['target'];
                        $this->map( $route['url'], $target, isset($route['conditions']) ? $route['conditions'] : Array() );
                    }
                }
            }
        }
    }
}