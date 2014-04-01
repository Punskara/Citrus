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
use \core\Citrus\mvc\Controller;
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
    public $done = false;

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

    
    /**
     * Boots the system and its services.
     * 
     * @throws \core\Citrus\sys\Exception if no host or no valid host is found
     */
    public function boot() {
        session_start();
        // $this->registerAutoload();

        if ( isset( $_SERVER['HTTP_HOST'] ) ) {

            $this->cache = new Cache();
            
            # exception handling
            set_exception_handler( Array( 
                '\core\Citrus\sys\Debug', 
                'handleException' 
            ) );

            # error handling
            set_error_handler( 
                Array( '\core\Citrus\sys\Debug', 'handleError') , 
                -1 & ~E_NOTICE & ~E_USER_NOTICE 
            );

            # shutdown handling
            register_shutdown_function( Array( '\core\Citrus\Citrus', 'shutDown' ) );

            $this->request = new Request();
            $this->response = new Response();
            try {
                $this->loadConfiguration();
                $this->startServices();
                $this->loadRouter();
                $this->request->addParams( $this->router->params );

                $app        = $this->router->app;
                $controller = $this->router->controller;
                $action     = $this->router->action;
                $this->app  = App::load( $app ); 
                $this->app->createController( $controller, $action );
                $this->app->executeCtrlAction();

            } catch ( NoRouteFoundException $e ) {
                Controller::pageNotFound();
            } catch ( Exception $e ) {
                Debug::handleException( $e, $this->debug );
                return;
            }


            $this->done = true;
        }
    }

    public function bootCLI() {
        $this->registerAutoload();
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
                    $inst = $r->newInstanceArgs( $args ? $args : Array() );
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
                    ini_set( 'display_errors', 0 );
                    error_reporting( E_ALL );
                }
                define( 'CTS_PROJECT_URL', $this->host->root_path );
            } else {
                throw new Exception( "No valid host found." );
            }
        }
    }
    
    
    /**
     * Loads the routing system.
     *
     * @throws \core\Citrus\sys\Exception if no routing file is found.
     */
    public function loadRouter() {
        $this->router = new Router( $this->host->root_path );
        $this->router
            ->loadRoutes()
            ->defaultRoutes()
            ->execute();
    }
    
    /**
     * Starts all enabled services.
     * 
     */
    public function startServices() {
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
        if ( $cos->debug ) {
            $cos->getLogger( 'debug' )->logEvent( $msg );
            // if ( !$cos->request->is_XHR && $cos->app != 'frontend' ) echo $cos->debug->debugBar();
        }
        unset( $cos );
    }   
    
    public function getController() {
        return $this->app->controller;
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
}
