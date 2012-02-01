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
use \core\Citrus\sys;

class Router {
    
    public $request_uri;
    public $app;
    public $module;
    public $action;
    public $params;
    
    public $default_app;
    public $default_module;
    public $default_action;

    public $ext = '.(html|json)';

    public $routes = array();
    
    public function __construct( $default_app, $baseUrl ) {
        $this->default_app = $default_app;
        
        $request = $_SERVER['REQUEST_URI'];
        $baseUrl = substr( $baseUrl, 1 );
        if (strpos( $request, $baseUrl ) !== false ) {
            $request = str_replace( $baseUrl, '', $request );
        }
        
        $pos = strpos( $request, '?' );
        if ( $pos ) $request = substr( $request, 0, $pos );
     
        $this->request_uri = $request;
        $this->routes = array();
        
    }

    public function defaultRoutes() {
        $this->map( '/:app/' );
        $this->map( '/:action' . $this->ext );
        $this->map( '/:app/:module/' );
        $this->map( '/:app/:module/:action' . $this->ext );
        $this->map( '/:app/:module/:id/:action' . $this->ext, array(), array( 'id' => '[0-9]+' ) );
        $this->map( '/:app/:module/:action' . $this->ext );
    }

    public function map( $rule, $target = array(), $conditions = array() ) {
        $this->routes[$rule] = new Route( 
            $rule, $this->request_uri, $target, $conditions 
        );
    }
    
    public function execute() {
        if ( count( $this->routes ) ) {
            foreach($this->routes as $route) {
                if ($route->is_matched) {
                    $this->setRoute($route);
                    break;
                }
            }
        }
    }
    
    private function setRoute( $route ) {
        $this->route_found = true;
        $params = $route->params;

        if ( isset( $params['app'] ) ) {
            $this->app = $params['app']; 
            $this->loadAppRoutes();
            unset( $params['app'] );
        } else if ( empty( $this->app ) ) { 
            $this->app = $this->default_app;
            $this->loadAppRoutes();
        }

        if ( isset( $params['module'] ) ) {
            $this->module = $params['module']; 
            unset( $params['module'] );
        }

        if ( isset( $params['action'] ) ) {
            $this->action = $params['action']; 
            unset( $params['action'] );
        }

        if ( isset( $params['id'] ) ) {
            $this->id = $params['id']; 
        } 
        $this->params = array_merge( $params, $_GET );

        
        if ( empty( $this->module ) ) { 
            $this->module = $this->default_module;
        }
        if ( empty( $this->action ) ) {
            $this->action = $this->default_action;
        }
        if ( empty( $this->id ) ) {
            $this->id = null;
        }

        $w = explode( '_', $this->module );

        foreach( $w as $k => $v ) {
            $w[$k] = ucfirst( $v );
        }

        $this->module_name = implode('', $w);
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
                            $this->map( $route['url'], $route['target'] );
                        }
                    }
                }
            }
         } else {
             throw new sys\Exception( "No routing file found." );
         }
    }
      
    public function loadAppRoutes() {
        $routesFile = CITRUS_PATH . '/apps/' . $this->app . '/config/routing.php';
        if ( file_exists( $routesFile ) ) {
            $routes = include $routesFile;
            if ( isset( $routes['default']['module'] ) && isset( $routes['default']['action'] ) ) {
                $this->default_module = $routes['default']['module'];
                $this->default_action = $routes['default']['action'];
            }
            if ( is_array( $routes ) && count( $routes ) ) {
                foreach ( $routes as $route ) {
                    if ( isset( $route['url'] ) && isset( $route['target'] ) ) {
                        $this->map( $route['url'], $route['target'] );
                    }
                }
            }
        } else {
            throw new sys\Exception( "No routing file found for app '$this->app'." );
        }
    }
}