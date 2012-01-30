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
 * @package Citrus\data
 * @subpackage Citrus\data\Schema
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\Citrus;
use \core\Citrus\User;
use \core\Citrus\utils;

/**
 * @todo finish associations classes (getMultipleAssociations)
 */
class Schema {
    
    /**
     * @var string
     */
    public $className;
    
    /**
     * @var string
     */
    public $tableName;
    
    /**
     * @var array
     */
    public $schema;
    
    /**
     * @var string
     */
    public $description;
    
    /**
     * @var string
     */
    public $pluralDescription;
    
    /**
     * @var array
     */
    public $properties;
    
    /**
     * @var array
     */
    public $adminColumns;
    
    /**
     * @var string
     */
    public $orderColumn;
    
    /**
     * @var string
     */
    public $orderSort;
    
    
    /**
     * Constructor.
     * 
     * @param $className  name of the class we work with.
     */
    public function __construct( $className ) {
        $this->className = $className;
        $this->getSchema();
    }
    
    
    /**
     * Reads the schema file and loads its data.
     * 
     */
    public function getSchema() {
        $className = str_replace( '\\', '/', $this->className );
        $schemaFile = CITRUS_PATH . '/' . str_replace( 
            '_', 
            DIRECTORY_SEPARATOR, 
            $className 
        ) . '.schema.php';
        
        if ( file_exists( $schemaFile ) ) {
            $schema = include $schemaFile;
            $this->tableName = $schema['tableName'];
            $this->className = $schema['modelType'];
            $this->description = $schema['description'];
        	$this->pluralDescription = $schema['pluralDescription'];
        	$this->properties = $schema['properties'];
        	$this->gender = isset( $schema['gender'] ) ? $schema['gender'] : 'm';
        	if ( isset( $schema['orderColumn'] ) ) {
        	    $this->orderColumn = $schema['orderColumn'];
        	}
        	if ( isset( $schema['orderSort'] ) ) {
        	    $this->orderSort = $schema['orderSort'];
        	}
        	if ( isset( $schema['adminColumns'] ) ) {
        	    $this->adminColumns = $schema['adminColumns'];
        	}
        }
    }
    
    public function getFilters() {
        if ( is_array( $this->schema ) ) {
            foreach ( $this->schema['properties'] as $prop ) {
                vexp($prop['type']);
            }
        }
    }
    
    /**
     * Gets the one to one associations of the class
     * 
     * @return array  $assoc  array of associations
     */
    public function getAssociations() {
        $assoc = array();
        foreach ( $this->properties as $propName => $propAttr ) {
            if ( array_key_exists( 'foreignTable', $propAttr ) ) {
                $assoc[$propName] = $propAttr;
            }
        }
        return $assoc;
    }
    
    
    /**
     * Gets the one to many associations of the class
     * 
     * @return array  $assoc  array of associations
     */
    public function getMultipleAssociations() {
        $mAssoc = array();
        foreach ( $this->multipleAssociations as $tableName => $props ) {
            #$mAssoc[];
        }
        return $assoc;
    }
    
    
    /**
     * Gets all the associations of the class
     * 
     * @return array  $assoc  array of associations
     */
    public static function getModelAssociations( $modelType ) {
        $modelType = str_replace( '\\', '/', $modelType );
        $schemaFile = CITRUS_PATH . str_replace( 
            '_', 
            DIRECTORY_SEPARATOR, 
            $modelType 
        ) . '.schema.php';
        
        $props = array();
        if ( file_exists( $schemaFile ) ) {
            $schema = include $schemaFile;
        	$props = $schema['properties'];
        }
		$assoc = array();
        if ( $props ) {
            foreach ( $props as $propName => $propAttr ) {
                if ( array_key_exists( 'foreignTable', $propAttr ) ) {
                    $assoc[$propName] = $propAttr;
                }
            }
        }
        return $assoc;
    }
    
    
    /**
     * Gets the database table name of the class.
     * 
     * @return string  $tableName  name of the table.
     */
    static public function getTableName( $modelType ) {
        if ( strpos( $modelType, '\\core\\Citrus' ) !== false ) {
            $modelType = str_replace( '\\core\\', '', $modelType );
        }
        $schemaFile = CITRUS_CLASS_PATH . str_replace( 
            '_', 
            DIRECTORY_SEPARATOR, 
            str_replace( '\\', '/', $modelType )
        ) . '.schema.php';
        $props = array();
        $tableName = '';

        if ( file_exists( $schemaFile ) ) {
            $schema = include $schemaFile;
        	$tableName = $schema['tableName'];
        }
        return $tableName;
    }
    
    
    /**
     * Gets all the object properties
     * 
     * @return array  $props  array of schema properties
     */
    public static function getProperties( $modelType ) {
        $schemaFile = CITRUS_CLASS_PATH . str_replace( 
            '_', 
            DIRECTORY_SEPARATOR, 
            $modelType 
        ) . '.schema.php';
        $props = array();

        if ( file_exists( $schemaFile ) ) {
            $schema = include $schemaFile;
        	$props = $schema['properties'];
        }
        return $props;
    }
    
    /**
	 * Builds SQL schema and writes it in a .sql file.
	 *
	 * @return boolean
	 */
    static public function buildSQLSchema() {
        $cos = Citrus::getInstance();
	    $dir = CITRUS_CLASS_PATH . $cos->projectName . '/schemas/';
	    $mainSchema = array();
	    if ( is_dir( $dir ) ) {
            if ( $dh = opendir( $dir ) ) {
                while ( ( $file = readdir( $dh ) ) !== false ) {
                    if ( substr( $file, 0, 1) != '.' ) {
                        if ( preg_match( '/\.schema.php/', $file ) ) {
                            $schem = include $dir . $file;
                            if ( is_array( $schem ) && count( $schem ) ) {
                                $mainSchema[] = $schem;
                            }
                        }
                    }
                }
                closedir( $dh );
            }
        }
        if ( count( $mainSchema ) ) {
            $query = "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            foreach ( $mainSchema as $item ) {
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
                        if ( isset( $keys['primaryKey'] ) && $keys['type'] == 'int' ) {
                            $queryProps[$i] .= 'BIGINT NOT NULL AUTO_INCREMENT';
                        } else {
                            if ( $keys['type'] == 'string' ) {
                                $queryProps[$i] .= 'VARCHAR(';
                                $queryProps[$i] .= isset( $keys['length'] ) ? $keys['length'] : '255';
                                $queryProps[$i] .= ')';
                            } elseif ( $keys['type'] == 'text' ) {
                                $queryProps[$i] .= 'LONGTEXT';
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
                        if ( isset( $keys['primaryKey'] ) && $keys['type'] == 'int' ) {
                            $indexes[] .= "  PRIMARY KEY(`$propName`)";
                        }
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
            $query .= User::createTable();
            $query .= User::insertFirstUser();
            $file = fopen( CITRUS_PATH . '/include/schema.sql', 'w' );
            $write = fwrite( $file, $query );
            fclose( $file );
            return $write;
        }
        return false;
	}
	
	/**
	 * Creates class files from php schemas
	 * @return void
	 */
	static public function buildParentClasses() {
	    $cos = Citrus::getInstance();
	    $dir = CITRUS_CLASS_PATH . $cos->projectName . '/schemas/';
	    
	    $wrDir = CITRUS_CLASS_PATH . $cos->projectName . '/Parent/';
	    
	    $mainSchema = array();
	    
	    $tabul = '    ';
	    
	    if ( is_dir( $dir ) ) {
            if ( $dh = opendir( $dir ) ) {
                while ( ( $file = readdir( $dh ) ) !== false ) {
                    if ( substr( $file, 0, 1) != '.' ) {
                        if ( preg_match( '/\.schema.php/', $file ) ) {
                            $schem = include $dir . $file;
                            if ( is_array( $schem ) && count( $schem ) ) {
                                $mainSchema[] = $schem;
                            }
                        }
                    }
                }
                closedir( $dh );
            }
        }
        if ( count( $mainSchema ) ) {
            foreach ( $mainSchema as $item ) {
                $f = new utils\File( $wrDir . $item['modelType'] . '.php', utils\File::MODE_WOS );
                
                $f->write( "<?php\n" );
                $f->write( "/**\n* @Entity @Table(name=\"" . $item['tableName'] . "\")\n*/\n\n" );
                $f->write( "namespace core\\" . $cos->projectName . "\\Parent\\;\n" );
                $f->write( "use \\core\\Citrus\\data\\Model;\n\n" );
                $f->write( "class " .$item['modelType'] . " extends Model {\n" );
                
                if ( isset( $item['properties'] ) ) {
                    foreach ( $item['properties'] as $prop => $attrs ) {
                        if ( isset( $attrs['primaryKey'] ) ) {
                            $f->write( "$tabul/**\n$tabul* @Id @Column(type=\"integer\") @GeneratedValue\n$tabul*/\n" );
                            $f->write( $tabul . "private $" . $prop . ";\n" );
                        }
                    }
                }
                
                $f->write( "}" );
                $f->close();
                exit;
            }
        }    
	}
}