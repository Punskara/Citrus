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
 * @version $Id$
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
     * @var string
     */
    public $baseUrl;
    
    /**
     * @access public
     * @var boolean
     */
    public $debug = true;

    /**
     * @access public
     * @var \core\Citrus\mvc\App
     */
    public $app;
    
    /**
     * @access public
     * @var string
     */
    public $webDir = '';
    
    /**
     * @access public
     * @var \core\Citrus\User
     */
    public $user;
    
    /**
     * @access public
     * @var \core\Citrus\Host
     */
    public $host;
    
    /**
     * @access public
     * @var boolean
     */
    public $hasRewriteEngine;
    
    /**
     * @access public
     * @var string
     */
    public $lang;

    /**
     * @access public
     * @var string
     */
    public $siteName;
    
    /**
     * @access public
     * @var string
     */
    public $projectName;
    
    
    /**
     * @access public
     * @var \core\Citrus\mvc\Template
     */
    public $template;
    
    
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
    
    #public $defaults = array( 'app' => 'frontend' );
    
    /**
     * @access public
     * @var string
     */
    public static $configFile = '/config/config.inc.php';
    
    /**
     * @access public
     * @var string
     */
    public static $configFileInstall = '/config/config.inc.default.php';
    
    
    /**
     * @access public
     * @var \core\Citrus\Citrus
     */
    private static $instance = null;
    
    
    /**
     * Accessor
     *
     * @return \core\Citrus\Citrus
     */
    public static function getInstance() {
        if ( is_null( self::$instance ) ) {
            try {
                self::$instance = new Citrus();
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
    private function __construct() {
        $this->Boot();
        #if ( $this->host instanceof core\Citrus\Host ) {
        if ( get_class( $this->host ) == 'core\\Citrus\\Host' ) {
            $this->hasRewriteEngine = $this->host->services['hasRewriteEngine'];
            if ( $this->debug ) {
                ini_set( 'display_errors', 1 );
            }
            
            define( 'CITRUS_PROJECT_URL', $this->host->baseUrl );
            define( 'CITRUS_WEB_DIR', CITRUS_PROJECT_URL . 'web/');
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
        if ( $this->config ) {
            date_default_timezone_set( $this->config['cos_Timezone'] );
            
            $this->siteName = $this->config['siteName'];
            $this->projectName = $this->config['projectName'];
            
            # loading hosts
            $CitrusHosts = $this->config['hosts'];
            $CitrusHost = false;

            if ( count( $CitrusHosts ) ) {
                foreach ( $CitrusHosts as $host => $args ) {
                    if ( strpos( $_SERVER['HTTP_HOST'], $args['httpHost'] ) !== false ) {
                        $r = new \ReflectionClass( '\core\Citrus\Host' );
                        $inst = $r->newInstanceArgs( $args ? $args : array() );
                        if ( $inst ) {
                            $this->host = $inst;
                            $this->startServices();
                        } else {
                            throw new sys\Exception( "Provided host is not a valid Citrus host." );
                        }
                    }
                }
            } else {
                throw new sys\Exception( "No valid host found" );
            }
        }
        
        # exception handling
        set_exception_handler( array( '\\core\\Citrus\\sys\\Debug', 'handleException' ) );
        set_error_handler( array( '\\core\\Citrus\\sys\\Debug', 'handleError' ) );
    }


    /**
     * Loads configuration file.
     *
     * @throws \core\Citrus\sys\Exception if no file is found.
     */
    public function loadConfiguration() {
        if ( !$this->config ) {
            if ( file_exists( CITRUS_PATH . '/' . self::$configFile ) ) {
                $this->config = include_once CITRUS_PATH . self::$configFile;
            } else if ( file_exists( CITRUS_PATH . '/' . self::$configFileInstall ) ) {
                $this->config = include_once CITRUS_PATH . self::$configFileInstall;
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
        if ( !isset( $this->config['defaultApp'] ) ) {
            throw new sys\Exception( "Unable to find defaults routing settings. Please check config file." );
        } else {
            $app = $this->config['defaultApp'];
            $this->router = new routing\Router( $app, $this->host->baseUrl );
            try {
                $this->router->loadRoutes();
            } catch ( sys\Exception $e ) {
                sys\Debug::handleException( $e, $this->debug );
            }

            $this->router->defaultRoutes();
            $this->router->execute();
        }
    }
    
    public function checkUser( $login, $password ) {
        return $this->db->execute( 
        "SELECT * FROM user WHERE login = '$login' AND password = '$password'"
        )->fetchObject( '\\core\\Citrus\\User' );
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
    
    public function generateApp( $name ) {
        if ( is_dir( CITRUS_APPS_PATH ) ) {
            if ( !is_dir(  CITRUS_APPS_PATH . '/' . $name ) ) {
                $mainDir = mkdir( CITRUS_APPS_PATH . '/' . $name, 0755 );
                if ( $mainDir ) {
                    $modules = mkdir( CITRUS_APPS_PATH . '/' . $name . '/modules', 0755 );
                    $config = mkdir( CITRUS_APPS_PATH . '/' . $name . '/config', 0755 );
                    $templates = mkdir( CITRUS_APPS_PATH . '/' . $name . '/templates', 0755 );
                    if ( $modules && $config && $templates ) {
                        $app = new mvc\App( $name );
                        $app->generateConfigFile();
                        $app->generateViewFile();
                        return true;
                    }
                } 
            } else {
                throw new sys\Exception( "App already exists" );
            }
        }
        return false;
    }
    
    public function generateModule( $name, $app, $resourceType = null ) {
        if ( is_dir( CITRUS_APPS_PATH ) ) {
            $modulePath = CITRUS_APPS_PATH . $app . '/modules/' . ucfirst( $name );
            if ( !is_dir( $modulePath ) ) {
                $mainDir = mkdir( $modulePath, 0755 );
                if ( $mainDir ) {
                    $config = mkdir( $modulePath . '/config', 0755 );
                    $templates = mkdir( $modulePath . '/templates', 0755 );
                    if ( $config && $templates ) {
                        $module = new mvc\Module( $name, CITRUS_APPS_PATH . $app );
                        $module->generateConfigFile();
                        $module->generateControllerFile();
                        return true;
                    }
                } 
            } else {
                throw new sys\Exception( "Module already exists" );
            }
        }
        return false;
    }
    
    
    /**
     * Gets list of applications existing in filesystem.
     *
     * @return array An array of apps names
     */
    public function getAppsList() {
        $dir = CITRUS_APPS_PATH;
        $apps = array();
        
        if ( is_dir( $dir ) ) {
            if ( $dh = opendir( $dir ) ) {
                while ( ( $file = readdir( $dh ) ) !== false ) {
                    if ( substr( $file, 0, 1) != '.' ) {
                        if ( is_dir( CITRUS_APPS_PATH . $file ) ) {
                            $apps[] = $file;
                        }
                    }
                }
                closedir( $dh );
            }
        }
        return $apps;
    }
    
    
    /**
     * Gets list of modules existing in the directory of given $app.
     *
     * @param $app  string  App we want to scan.
     *
     * @return array An array of apps names
     */
    public function getModulesList( $app ) {
        $dir = CITRUS_APPS_PATH . $app . '/modules/';
        $modules = array();
        
        if ( is_dir( $dir ) ) {
            if ( $dh = opendir( $dir ) ) {
                while ( ( $file = readdir( $dh ) ) !== false ) {
                    if ( substr( $file, 0, 1) != '.' ) {
                        if ( is_dir( $dir . $file ) ) {
                            $modules[] = $file;
                        }
                    }
                }
                closedir( $dh );
            }
        }
        return $modules;
    }
    
    
    /**
     * Builds SQL schema and writes it in a .sql file.
     *
     * @deprecated moved in \core\Citrus\data\Schema
     * @return boolean
     */
    public function buildSQLSchema() {
        $dir = CITRUS_CLASS_PATH . $this->projectName . '/';

        $mainSchema = array();
        $listeSchemas = read_folder( $dir , "/.schema.php$/" );
        
        foreach (  $listeSchemas as $schema ) {
            $schem = include $schema;
            if ( is_array( $schem ) && count( $schem ) ) $mainSchema[] = $schem;
        }
        
        if ( count( $mainSchema ) ) {
            $query = "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            foreach ( $mainSchema as $item ) {
                $primary = Array();
                $query .= "#------------ " . $item['tableName'] . " ------------\n";
                $query .= 'DROP TABLE IF EXISTS `' . $item['tableName'] . "`;\n";
                $query .= 'CREATE TABLE `' . $item['tableName'] . "` (\n";
                if ( isset( $item['properties'] ) ) {
                    $i = 0;
                    $queryProps = array();
                    $indexes = '';
                    $engine = '';
                    foreach ( $item['properties'] as $propName => $keys ) {
                        $queryProps[$i] = "  `$propName` ";
                        if ( isset( $keys['primaryKey'] ) && $keys['type'] == 'int' && isset( $keys['autoincrement'] ) && $keys['autoincrement'] == true ) {
                            $queryProps[$i] .= 'BIGINT NOT NULL AUTO_INCREMENT';
                        } else {
                            if ( $keys['type'] == 'string' ) {
                                $queryProps[$i] .= 'VARCHAR(';
                                $queryProps[$i] .= isset( $keys['length'] ) ? $keys['length'] : '255';
                                $queryProps[$i] .= ')';
                            } elseif ( $keys['type'] == 'text' ) {
                                $queryProps[$i] .= 'LONGTEXT';
                            } elseif ( $keys['type'] == 'blob' ) {
                                $queryProps[$i] .= 'BLOB';
                            } elseif ( $keys['type'] == 'boolean' ) {
                                $queryProps[$i] .= 'BOOLEAN';
                            } elseif ( $keys['type'] == 'datetime' ) {
                                $queryProps[$i] .= 'DATETIME';
                            } elseif ( $keys['type'] == 'int' && isset( $keys['foreignTable'] ) ) {
                                $queryProps[$i] .= 'BIGINT';
                            } elseif ( $keys['type'] == 'int' ) {
                                $queryProps[$i] .= 'INT';
                            } elseif ( $keys['type'] == 'float' ) {
                                $queryProps[$i] .= 'FLOAT';
                            }
                            if ( isset( $keys['null'] ) && $keys['null'] === false ) {
                                $queryProps[$i] .= ' NOT';
                            }
                            $queryProps[$i] .= ' NULL';
                        }
                        if ( isset( $keys['primaryKey'] ) ) $primary[] = $propName;
                        $i++;

                        if ( array_key_exists( 'foreignTable', $keys ) && array_key_exists( 'foreignReference', $keys ) ) {
                            $fk = "  FOREIGN KEY (`" . $propName . "`) REFERENCES "
                                       . "`" . $keys['foreignTable'] . "` (`" . $keys['foreignReference'] . "`) ";
                            if ( isset( $keys['onDelete'] ) ) {
                                $fk .= " ON DELETE " . $keys['onDelete'];
                            } elseif ( $keys['null'] === true ) {
                                $fk .= " ON DELETE SET NULL";
                            }
                            if ( isset( $keys['onUpdate'] ) ) {
                                $fk .= " ON UPDATE " . $keys['onUpdate'];
                            } else {
                                $fk .= " ON UPDATE CASCADE";
                            }
                            $indexes[] = $fk;
                        }
                    }
                    
                    if (count($primary) > 0) 
                        $indexes[] = "  PRIMARY KEY(`". implode( "`,`", $primary ) ."`)";
                    
                    if ( $indexes != '' ) {
                        $queryProps[] .= implode( ",\n", $indexes );
                    }
                    $query .= implode( ",\n", $queryProps );
                    $query .= "\n)";
                    $query .= " ENGINE = InnoDB";
                    $query .= ";\n\n";
                }
            }
            $query .= "# Restores the foreign key checks, as we unset them at the begining\n";
            $query .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
 //           $query .= User::createTable();
 //           $query .= User::insertFirstUser();
            $file = fopen( CITRUS_PATH . '/include/schema.sql', 'w' );
            $write = fwrite( $file, $query );
            fclose( $file );
            return $write;
        }
        return false;
    }
    
    
    
    public static function pageNotFound( $message = null ) {
        $cos = Citrus::getInstance();
        $response = new http\Response();
        $response->code = '404';
        $response->message = $message;
        $response->sendHeaders();
        ob_start();
        include CITRUS_PATH . '/core/Citrus/http/templates/pageNotFound.tpl' ;
        $tpl = ob_get_contents();
        ob_end_clean();
        echo $tpl;
        exit;
    }
    
    
    /**
     * Starts all enabled services.
     * 
     */
    public function startServices() {
        $services = $this->host->services;
        if ($services['debug']['active']) {
            sys\Debug::$debug = true;
            $this->debug = new sys\Debug();
            $this->debug->timer = new sys\Timer( "total" );
            $this->debug->timer->start();
        }
        else $this->debug = false;

        if ( isset( $services['logger'] ) && $services['logger']['active'] == true ) {
            $this->logger = new sys\Logger( $this->host->httpHost );
            $this->logger->logEvent( 'Logging service started.' );
        }
        if ( isset( $services['db'] ) && $services['db']['active'] == true ) {
            if ( isset( $services['db']['connection'] ) ) {
                try {
                    list ( $dsn, $username, $password ) = $services['db']['connection'];
                    $this->db = new db\Connection( $dsn, $username, $password );
                    
                    // orm configuration
                    $paths = array( CITRUS_CLASS_PATH . $this->projectName . '/' );
                    $isDevMode = $this->debug;
                    $this->orm = db\Orm::getInstance( $services['db']['connection'], $paths, $isDevMode );
                    
                    if ( $this->debug ) {
                        $this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
                    }
                } catch ( \PDOException $e ) {
                    sys\Debug::handleException( $e, $this->debug );
                }
            }
        }
    }
    
    public function shutdown() {
        ob_flush();
        if ( $this->logger ) {
            $this->logger->logEvent( 'Shutting down…' );
            $this->logger->writeLog();
        }
        unset($this);
        exit;
    }
    
    public function getController() {
        return $this->app->module->ctrl;
    }
}
