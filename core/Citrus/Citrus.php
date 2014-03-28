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
 * @package Citrus
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus;

use \core\Citrus\mvc\App;
use \core\Citrus\mvc\View;
use \core\Citrus\mvc\Controller;
use \core\Citrus\mvc\NoControllerFoundException;
use \core\Citrus\sys\Config;
use \core\Citrus\sys\Cache;
use \core\Citrus\sys\Debug;
use \core\Citrus\sys\Exception;
use \core\Citrus\sys\Timer;
use \core\Citrus\sys\Logger;
use \core\Citrus\http\Response;
use \core\Citrus\http\Request;
use \core\Citrus\routing\Router;
use \core\Citrus\routing\NoRouteFoundException;

class Citrus {

    /**
     * @access public
     * @var \core\Citrus\db\Connection
     */
    private $db;
    
    /**
     * @access public
     * @var boolean
     */
    public $debug;

    /**
     * @access public
     * @var \core\Citrus\mvc\App
     */
    public $app;
    
    /**
     * @access public
     * @var \core\Citrus\Host
     */
    public $host;

    /**
     * @access public
     * @var array
     */
    public $config;
    
    /**
     * @access public
     * @var Array of \core\Citrus\sys\Logger objects
     */
    public $loggers;
    
    /**
     * @access public
     * @var \core\Citrus\routing\Router
     */
    public $router;
    
    /**
     * @access public
     * @var array
     */
    public $services = Array();
    
    /**
     * @access public
     * @var string
     */
    static public $config_file = '/config/config.inc.php';
    
    /**
     * @access public
     * @var string
     */
    static public $default_config_file = '/config/config.inc.default.php';
    
    /**
     * @access public
     * @var \core\Citrus\Citrus
     */
    private static $instance = null;
    
    /**
     * @access public
     * @var \core\Citrus\sys\Cache
     */
    public $cache;
    
    /**
     * @access public
     * @var boolean
     */
    private $done = false;

    /**
     * @access public
     * @var \core\Citrus\http\Response
     */
    public $response;

    /**
     * @access public
     * @var \core\Citrus\http\Request
     */
    public $request;

    private $globals = Array();
    
    /**
     * Accessor
     *
     * @return \core\Citrus\Citrus
     */
    static public function getInstance( $cli = false ) {
        if ( is_null( self::$instance ) )
            self::$instance = new Citrus( $cli );

        return self::$instance;
    }

    /**
     * Constructor.
     * 
     * @throws \core\Citrus\sys\Exception if no valid host is found.
     */
    private function __construct() {}
    
    private function registerAutoload() {
        spl_autoload_register( Array( __CLASS__, 'autoload' ) );
    }
    

    static function autoload( $class ) {
        $file = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
        if ( file_exists( CTS_PATH . "/$file.php" ) ) {
            require_once( CTS_PATH . "/$file.php" );
        }
    }

    public function bootCLI() {
        $this->registerAutoload();
    }

    /**
     * Boots the system and its services.
     * 
     * @throws \core\Citrus\sys\Exception if no host or no valid host is found
     */
    public function boot( $app, $cfg ) {
        session_start();
        // $this->registerAutoload();

        if ( isset( $_SERVER['HTTP_HOST'] ) ) {

            $this->cache = new Cache();
            
            // exception handling
            set_exception_handler( Array( 
                '\core\Citrus\sys\Debug', 
                'handleException' 
            ) );

            // error handling
            set_error_handler( 
                Array( '\core\Citrus\sys\Debug', 'handleError') , 
                E_ALL
                // -1 & ~E_NOTICE & ~E_USER_NOTICE 
            );

            // shutdown handling
            register_shutdown_function( Array( '\core\Citrus\Citrus', 'shutDown' ) );

            $this->request  = new Request();
            $this->response = new Response();
            try {
                $this->loadConfiguration();
                $this->startServices();

                $this->app = $this->loadApp( $app );
                $this->startApp();

                $this->response->content = $this->app->output();
                $this->response->sendHeaders();

                echo $this->response->content;

            } catch ( NoRouteFoundException $e ) {
                Controller::pageNotFound();
            } catch ( NoControllerFoundException $e ) {
                Controller::pageNotFound();
            } catch ( Exception $e ) {
                Debug::handleException( $e, $this->debug );
                return;
            }


            $this->done = true;
        }
    }

    /**
     * Creates an instance of requested App.
     * @param $name Name of requested App
     * @throws Exception when requested app class is not found.
     * @return subclass of \core\Citrus\mvc\App
     */

    private function loadApp( $app ) {
        $router = new Router( 
            $_SERVER['REQUEST_URI'],
            $this->host->root_path
        );
        $routes = $this->getAppRoutes( $app );
        $routes[] = Array(
            'url' => '/:cos_app/:cos_controller/:cos_action'
        );
        foreach ( $routes as $r ) {
            $router->map( 
                $r['url'], 
                array_key_exists( 'target', $r )     ? $r['target']     : Array(),
                array_key_exists( 'conditions', $r ) ? $r['conditions'] : Array()
            );
        }
        
        $router->executeRoutes( $routes );

        $app_class = Config::appClassName( $app );

        if ( class_exists( $app_class ) ) {
            $r      = new \ReflectionClass( $app_class ); 
            $app    = $r->newInstanceArgs( array( $app, $router ) );

            $controller = $router->getParam( "cos_controller" );

            if ( !( $controller instanceof \Closure ) ) {
                $controller = $this->createController( 
                    $app->name, $controller, $router->getParam( "cos_action" ) 
                );
            }

            if ( is_callable( $controller ) ) {
                $app->setController( $controller );
            }
            return $app;
        }
        throw new Exception( "Unable to find app '$app'" );
        // return false;
    }

    /**
     * Execute the apps controller
     * and displays the output
     *
     * @return void
     **/
    public function startApp() {
        $this->app->router->removeParams( Array( "cos_app", "cos_controller", "cos_action" ) );
        $this->request->addParams( $this->app->router->getParams() );
        $this->app->executeController( $this->request );

        // this works but it's ugly, to be recoded

        if ( $this->request->is_XHR ) {
            $this->app->setView( $this->app->getController()->view );
        } else {
            $layout = new View( CTS_APPS_PATH . '/' . $this->app . CTS_VIEW_DIR . '/layout' );
            // diex( $this->app->getController()->view );
            $layout->assign( "content", $this->app->getController()->renderView() );
            $this->app->setView( $layout );
        }
    }

    /**
     * Loads configuration file.
     *
     * @throws \core\Citrus\sys\Exception if no file is found.
     */
    public function loadConfiguration() {
        if ( !$this->config ) {
            if ( file_exists( CTS_PATH . self::$config_file ) ) {
                $this->config = include_once CTS_PATH . self::$config_file;
            } elseif ( file_exists( CTS_PATH . self::$default_config_file ) ) {
                $this->config = include_once CTS_PATH . self::$default_config_file;
            }
            if ( !$this->config ) {
                throw new Exception( "Unable to find configuration file." );
                return;
            }
            date_default_timezone_set( $this->config['default_timezone'] );
            # loading hosts
            $hosts = $this->config['hosts'];

            if ( !count( $hosts ) ) {
                throw new Exception( "No host found in configuration file." );
                return;
            }
            foreach ( $hosts as $host => $args ) {
                if ( strpos( $_SERVER['HTTP_HOST'], $args['domain'] ) !== false ) {
                    $r = new \ReflectionClass( '\core\Citrus\Host' );
                    $inst = $r->newInstanceArgs( $args );
                    if ( $inst ) {
                        $this->host = $inst;
                        break;
                    } else {
                        throw new Exception( 
                            "Provided host is not a valid Citrus host." 
                        );
                    }
                }
            }
        
            if ( get_class( $this->host ) == 'core\\Citrus\\Host' ) {
                if ( $this->debug ) {
                    error_reporting( E_ALL );
                }
                define( 'CTS_PROJECT_URL', $this->host->root_path );
            } else {
                throw new Exception( "No valid host found." );
            }
        }
    }

    private function getAppRoutes( $app = null ) {
        // $apps           = $this->listApps();
        $routes         = Array();
        $routing_file   = CTS_APPS_PATH . '/' . $app . '/config/routing.php';

        if ( file_exists( $routing_file ) ) {            
            $src = include $routing_file;
            if ( is_array( $src ) && count( $src ) )
                $routes = array_merge( $routes, $src );
        }

        return $routes;
    }

    /**
     * Loads the routing system.
     *
     * @throws \core\Citrus\sys\Exception if no routing file is found.
     */
    public function loadRouter() {
        $this->router = new Router( 
            $_SERVER['REQUEST_URI'],
            $this->host->root_path
        );
        // $this->loadRoutingConfiguration();
        // $this->router->execute();
    }
    
    /**
     * Starts all enabled services.
     * 
     */
    private function startServices() {
        $services = $this->host->services;

        if ( $services['debug']['active'] ) {
            Debug::$debug = true;
            $this->debug = new Debug( $this->request );
            $this->debug->timer = new Timer( "total" );
            $this->debug->timer->start();
            $this->addLogger( 'debug' );
        } else $this->debug = false;

        if ( isset( $services['logger']['active'] ) 
             && $services['logger']['active'] === true 
        ) {
            $this->addLogger( 'error' );
        }
    }

    private function addLogger( $id ) {
        $this->loggers[$id] = new Logger( $this->host->domain . '.' . $id );
        return $this->loggers[$id];
    }

    public function getLogger( $id ) {
        if ( isset( $this->loggers[$id] ) ) return $this->loggers[$id];
        return $this->addLogger( $id );
    }


    /**
     * Adds an error entry in log
     *
     * @param string $content Error description
     * @return void
     **/
    function logError( $content ) {
        $this->getLogger( 'error' )->logEvent( $content );
    }

    /**
     * Initiates a database connection
     * according to project configuration
     */
    public function getDatabase() {
        if ( $this->db ) return $this->db;
        $config = $this->host->services;
        if ( isset( $config['db'] ) 
             && $config['db']['active'] === true 
             && isset( $config['db']['connection'] 
        ) ) {
            try {
                list ( 
                    $dsn, $username, $password 
                ) = $config['db']['connection'];

                $this->db = new db\Connection( $dsn, $username, $password );
                
                if ( $this->debug ) {
                    $this->db->setAttribute( 
                        \PDO::ATTR_ERRMODE, 
                        \PDO::ERRMODE_EXCEPTION 
                    );
                }
                return $this->db;
            } catch ( \PDOException $e ) {
                Debug::handleException( $e, $this->debug );
            }
        }
    }

    /**
     * executed when Citrus stops (properly or not) 
     */
    static public function shutDown() {
        $cos = Citrus::getInstance();
        if ( $cos->debug )
            $cos->debug->showErrorIfExists();
        ob_flush();

        // if cos has not ended properly
        if ( !$cos->done ) {
            $msg = "Citrus hasn't stopped properly";
        } else {
            $msg = 'Shutting down…';
        }
        if ( $cos->debug && !$cos->request->is_XHR ) {
            $cos->getLogger( 'debug' )->logEvent( $msg );
            echo $cos->debug->debugBar();
        }
        unset( $cos );
    }   
    
    public function getController() {
        return $this->app->getController();
    }

    public function getClassName( $class_name ) {
        return join( '', array_slice( explode( '\\', $name ), -1 ) );
    }

    public function setGlobal( $name, $value ) {
        $this->globals[$name] = $value;
    }

    public function getGlobal( $name ) {
        if ( isset( $this->globals[$name] ) ) return $this->globals[$name];
        return false;
    }

    /**
     * Gets list of applications existing in filesystem.
     *
     * @return array An array of apps names
     */
    public function listApps() {
        $dir    = CTS_APPS_PATH . '/';
        $apps   = array();

        if ( is_dir( $dir ) && $dh = opendir( $dir ) ) {
            while ( ( $file = readdir( $dh ) ) !== false ) {
                if ( substr( $file, 0, 1) != '.' ) {
                    if ( is_dir( $dir . $file ) ) {
                        $apps[] = $file;
                    }
                }
            }
            closedir( $dh );
        }
        return $apps;
    }

    /**
     * Creates the controller which will execute the action requested.
     *
     * @param array $name Controller name
     * @param array $action Action to be performed
     *
     * @return \core\Citrus\mvc\Controller|boolean Whether if the controller file exists or not.
     */
    public function createController( $app_name, $controller, $action = null ) {

        $pattern = "#([[:alpha:]]+)\/([[:alpha:]]+)#";
        if ( preg_match( $pattern, $controller, $matches ) ) {
            array_shift( $matches );
            list( $controller, $action ) = $matches;
        }
        if ( $app_name && $action ) {
            $class = Config::controllerClassName( $app_name, $controller );
            if ( class_exists( $class ) ) {
                $r = new \ReflectionClass( $class ); 
                $controller = $r->newInstanceArgs( Array(
                    'action' => $action,
                ) );
            }
            $controller->setView( Config::appViewPath( $app_name ) );
        }

        return $controller;
    }
}

