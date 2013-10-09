<?php

namespace apps\install;
use \core\Citrus\Citrus;
use \core\Citrus\mvc;
use \core\Citrus\sys;

class Installer {
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
                        $app->generateRoutingFile();
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
            $modulePath = CITRUS_APPS_PATH . $app . '/modules/' . $name;
            if ( !is_dir( $modulePath ) ) {
                $mainDir = mkdir( $modulePath, 0755 );
                if ( $mainDir ) {
                    $config = mkdir( $modulePath . '/config', 0755 );
                    $templates = mkdir( $modulePath . '/templates', 0755 );
                    if ( $config && $templates ) {
                        $module = new mvc\Controller( $name, CITRUS_APPS_PATH . $app );
                        $module->name = $name;
                        $this->generateControllerFile( $module );
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
        $cos = Citrus::getInstance();
        $dir = CITRUS_CLASS_PATH . $cos->projectName . '/';

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

    /**
     * Creates a configuration file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateAppConfigFile( $app ) {
        if ( is_dir( $app->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/config.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $app->path . '/config/config.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }
    
    /**
     * Creates a 'view' file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateViewFile( $app ) {
        if ( is_dir( $app->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/view.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $app->path . '/config/view.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }

    /**
     * Creates a 'routing' file from the template.
     *
     * @return boolean whether the file could be created or not.
     */
    public function generateAppRoutingFile( $app ) {
        if ( is_dir( $app->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/routing.tpl';
            $tpl =  fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            
            $write = false;
            if ( $content ) {
                $file = fopen( $app->path . '/config/routing.php', 'w' );
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
    public function generateControllerFile( $ctrl ) {
        if ( is_dir( $ctrl->path ) ) {
            $templateFile = CITRUS_PATH . '/core/Citrus/mvc/templates/controller.tpl';
            $tpl = fopen( $templateFile, 'r' );
            $content = fread( $tpl, filesize( $templateFile ) );
            fclose( $tpl );
            $content = str_replace( "{ModuleName}", ucfirst( $ctrl->name ), $content );
            $write = false;
            if ( $content ) {
                $file = fopen( $ctrl->path . '/modules/' . $ctrl->name . '/Controller.php', 'w' );
                $write = fwrite( $file, $content );
                fclose( $file );
            }
            return $write;
        } else return false;
    }
    
}