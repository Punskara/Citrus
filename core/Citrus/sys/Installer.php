<?php

namespace core\Citrus\sys;
use \core\Citrus\Citrus;
use \core\Citrus\mvc\App;
use \core\Citrus\sys;

class Installer {

    private $tpl_dir;

    public function __construct() {
        $this->tpl_dir = __DIR__ . '/templates/';
    }

    public function appExists( $name ) {
        $app_path = CTS_APPS_PATH . $name;
        return is_dir( $app_path );
    }

    public function generateApp( $name ) {
        echo CTS_APPS_PATH;
        if ( !is_writable( CTS_PATH ) ) {
            throw new sys\Exception( "Apps directory is not writeable!" );
        }
        if ( !is_dir( CTS_APPS_PATH ) ) {
            mkdir( CTS_APPS_PATH, 0755 );
        }
        if ( !$this->appExists( $name ) ) {
            $app_path = CTS_APPS_PATH . $name;
            $mainDir = mkdir( $app_path, 0755 );
            if ( $mainDir ) {
                $modules    = mkdir( $app_path . '/controllers', 0755 );
                $templates  = mkdir( $app_path . '/templates', 0755 );
                $config     = mkdir( $app_path . '/config', 0755 );
                if ( $modules && $templates && $config ) {
                    $this->generateAppClassFile( $name );
                    $this->generateAppRoutingFile( $name );
                    return true;
                }
            } 
        } else {
            throw new sys\Exception( "App already exists" );
        }
        return false;
    }
    
    public function generateModule( $name, $app, $resourceType = null ) {
        if ( !is_writable( CTS_APPS_PATH ) ) {
            throw new sys\Exception( "Apps directory is not writeable!" );
        }
        if ( !is_dir( CTS_APPS_PATH . $app ) ) {
            throw new sys\Exception( "Unable to find app directory!" );
        }
        if ( !is_writable( CTS_APPS_PATH . $app ) ) {
            throw new sys\Exception( "App directory is not writeable!" );
        }
        $modulePath = CTS_APPS_PATH . $app . '/controllers/';
        if ( is_dir( $modulePath ) || mkdir( $modulePath, 0755 ) ) {
            $this->generateControllerFile( $name, $app );
            return true;
        } else {
            throw new sys\Exception( "Module already exists" );
        }
        return false;
    }
    
    
    /**
     * Gets list of applications existing in filesystem.
     *
     * @return array An array of apps names
     */
    public function getAppsList() {
        return App::listApps();
    }
    
    
    /**
     * Gets list of modules existing in the directory of given $app.
     *
     * @param $app  string  App we want to scan.
     *
     * @return array An array of apps names
     */
    public function getModulesList( $app ) {
        $dir = CTS_APPS_PATH . $app . '/modules/';
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
     * Creates a 'routing' file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateAppRoutingFile( $app_name ) {
        $path = CTS_APPS_PATH . $app_name;
        if ( is_dir( $path ) ) {
            $templateFile = $this->tpl_dir . 'routing.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $path . '/config/routing.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }


    /**
     * Creates a controller class file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateControllerFile( $name, $app ) {
        $path = CTS_APPS_PATH . $app;
        $name = ucfirst( strtolower( $name ) ) . 'Controller.php';
        $full_path = $path . '/controllers/' . $name;

        if ( file_exists( $full_path ) ) {
            throw new Exception( "Module already exists!" );
        }
        if ( is_dir( $path ) ) {
            $templateFile = $this->tpl_dir . 'controller.tpl';
            $tpl = fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            $content = str_replace( "{app_name}", $app, $content );
            $content = str_replace( "{module_name}", $name, $content );
            $write = false;
            if ( $content ) {
                $file = fopen( $full_path, 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }
    
    public function generateAppClassFile( $app_name ) {
        $path = CTS_APPS_PATH . '/' . $app_name;
        if ( is_dir( $path ) ) {
            $templateFile = $this->tpl_dir . 'App.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            $write = false;
            if ( $content ) {
                $app_class  = ucfirst( $app_name ) . 'App';
                $content    = str_replace( "{app_name}", $app_name, $content );
                $content    = str_replace( "{app_class}", $app_class, $content );
                $file       = fopen( $path . '/' . $app_class . '.php', 'w' );
                $write      = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }   

    static public function readFolder( $path, $grep, $list = Array() ) {
        if ( $handler = opendir( $path ) ) {
            while ( ( $sub = readdir( $handler ) ) !== false ) {
                if ( substr( $sub, 0, 1 ) != "." ) {
                    if ( ( is_file( $path . "/" . $sub ) && !$grep) || ( is_file( $path . "/" . $sub ) && preg_match( $grep, $sub ) ) ) {
                        $listDir[] = $path . "/" . $sub;
                    } elseif (is_dir( $path . "/" . $sub ) ) {
                        $listDir = self::readFolder( $path . $sub, $grep, $listDir );
                    }
                }
            }
            closedir( $handler );
        }
        return $listDir;
    }

}