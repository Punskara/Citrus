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
 * @package Citrus
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus;

class Citrus {

    /**
     * @access public
     * @var \core\Citrus\db\Connection
     */
    public $db;
    
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
     * @var \core\Citrus\sys\logger
     */
    public $logger;
    
    /**
     * @access public
     * @var \core\Citrus\routing\Router
     */
    public $router;
    
    /**
     * @access public
     * @var array
     */
    public $services = array();
    
    /**
     * @access public
     * @var string
     */
    public static $config_file = '/config/config.inc.php';
    
    /**
     * @access public
     * @var string
     */
    public static $default_config_file = '/config/config.inc.default.php';
    
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
    
    public $done = false;

    /**
     * @access public
     * @var \core\Citrus\Http\Response
     */
    public $response;

    /**
     * @access public
     * @var \core\Citrus\Http\Request
     */
    public $request;
    
    /**
     * Accessor
     *
     * @return \core\Citrus\Citrus
     */
    public static function getInstance( $cli = false ) {
        if ( is_null( self::$instance ) ) {
            try {
                self::$instance = new Citrus( $cli );
            } catch ( Exception $e ) {
                sys\Debug::handleException( $e, true );
            }
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * 
     * @throws \core\Citrus\sys\Exception if no valid host is found.
     */
    private function __construct( $cli = false ) {
        if ( $cli ) {
            $this->BootCli();
            return;
        }
        $this->Boot();
        if ( get_class( $this->host ) == 'core\\Citrus\\Host' ) {
            if ( $this->debug ) {
                ini_set( 'display_errors', 0 );
                error_reporting( E_ALL );
            }
            define( 'CITRUS_PROJECT_URL', $this->host->root_path );
        } else {
            throw new sys\Exception( "No valid Citrus Host found." );
            exit;
        }
    }
    
    private function registerAutoload() {
        spl_autoload_register( array( __CLASS__, 'autoload' ) );
    }
    
    
    /**
     * Boots the system and its services.
     * 
     * @throws \core\Citrus\sys\Exception if no host or no valid host is found
     */
    private function Boot() {
        $this->registerAutoload();
        $this->loadConfiguration();
        $this->cache = new \core\Citrus\sys\Cache();
        
        if ( $this->config ) {
            date_default_timezone_set( $this->config['default_timezone'] );
            
            # loading hosts
            $CitrusHosts = $this->config['hosts'];
            $CitrusHost = false;

            if ( count( $CitrusHosts ) ) {
                foreach ( $CitrusHosts as $host => $args ) {
                    if ( strpos( $_SERVER['HTTP_HOST'], $args['domain'] ) !== false ) {
                        $r = new \ReflectionClass( '\core\Citrus\Host' );
                        $inst = $r->newInstanceArgs( $args ? $args : array() );
                        if ( $inst ) {
                            $this->host = $inst;
                            $this->request = new http\Request();
                            $this->startServices();
                        } else {
                            throw new sys\Exception( "Provided host is not a valid Citrus host." );
                        }
                    }
                }
                $this->response = new http\Response();
            } else {
                throw new sys\Exception( "No valid host found" );
            }
        }
        
        # exception handling
        set_exception_handler( array( '\core\Citrus\sys\Debug', 'handleException' ) );

        # shutdown handling
        register_shutdown_function( Array( '\core\Citrus\Citrus', 'shutDown' ) );

        # error handling
        set_error_handler( array( '\core\Citrus\sys\Debug', 'handleError') , -1 & ~E_NOTICE & ~E_USER_NOTICE );
    }

    public function BootCli() {
        $this->registerAutoload();
    }

    /**
     * Loads configuration file.
     *
     * @throws \core\Citrus\sys\Exception if no file is found.
     */
    public function loadConfiguration() {
        if ( !$this->config ) {
            if ( file_exists( CITRUS_PATH . self::$config_file ) ) {
                $this->config = include_once CITRUS_PATH . self::$config_file;
            } elseif ( file_exists( CITRUS_PATH . self::$default_config_file ) ) {
                $this->config = include_once CITRUS_PATH . self::$config_file;
            } else {
                throw new sys\Exception( "Unable to find configuration file." );
            }
        }
    }
    
    
    /**
     * Loads the routing system.
     *
     * @throws \core\Citrus\sys\Exception if no routing file is found.
     */
    public function loadRouter() {
        $this->router = new routing\Router( $this->host->root_path );
        try {
            $this->router->loadRoutes();
        } catch ( sys\Exception $e ) {
            sys\Debug::handleException( $e, $this->debug );
        }

        $this->router->defaultRoutes();
        $this->router->execute();
}
    
    /**
     * Mass assign an object properties
     * @param object $target Target object
     * @param array $props Properties to assign
     */
    static function apply( $tgt, $props ) {
        foreach ( $props as $p => $v ) {
            $tgt->$p = $v;
        }
        return $tgt;
    }
    
    static function autoload( $class ) {
        if ( strpos( $class, __NAMESPACE__ ) !== false ) {
            $file = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, strlen( __NAMESPACE__ ) + 1 ) );
        } else {
            $file = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
        }
        $file = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
        if ( file_exists( CITRUS_PATH . "/$file.php" ) ) {
            include_once( CITRUS_PATH . "/$file.php" );
        }
    }
    
    /**
     * Starts all enabled services.
     * 
     */
    public function startServices() {
        $services = $this->host->services;
        if ( $services['debug']['active'] ) {
            sys\Debug::$debug = true;
            $this->debug = new sys\Debug( $this->request );
            $this->debug->timer = new sys\Timer( "total" );
            $this->debug->timer->start();
        }
        else $this->debug = false;

        if ( isset( $services['logger'] ) && $services['logger']['active'] == true ) {
            $this->logger = new sys\Logger( $this->host->domain );
            $this->logger->logEvent( 'Logging service started.' );
        }
    }

    /**
     * Initiates a database connection
     * according to project configuration
     */
    public function getDatabase() {
        if ( $this->db ) return $this->db;
        $config = $this->host->services;
        if ( isset( $config['db'] ) && $config['db']['active'] == true ) {
            if ( isset( $config['db']['connection'] ) ) {
                try {
                    list ( $dsn, $username, $password ) = $config['db']['connection'];
                    $this->db = new db\Connection( $dsn, $username, $password );
                    
                    if ( $this->debug ) {
                        $this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
                    }
                    return $this->db;
                } catch ( \PDOException $e ) {
                    sys\Debug::handleException( $e, $this->debug );
                }
            }
        }
    }

    /**
     * executed when Citrus stops (properly or not) 
     */
    public static function shutDown() {
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

        if ( $cos->logger ) {
            $cos->logger->logEvent( $msg );
            $cos->logger->writeLog();
        }
        unset( $cos );
    }   
    
    public function getController() {
        return $this->app->controller;
    }

    }
}
