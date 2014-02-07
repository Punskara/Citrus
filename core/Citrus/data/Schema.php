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
 * @package Citrus\data
 * @subpackage Citrus\data\Schema
 * @author Rémi Cazalet <remi@caramia.fr>
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
    public $manyProperties = array();
    
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

    public $orderColumnDefined;
    public $linkColumns = Array();
    
    
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
            if ( isset( $schema['orderColumnDefined'] ) ) {
                $this->orderColumnDefined = $schema['orderColumnDefined'];
            }
            if ( isset( $schema['linkColumns'] ) ) {
                $this->linkColumns = $schema['linkColumns'];
            }
            if ( isset( $schema['manyProperties'] ) ) {
                $this->manyProperties = $schema['manyProperties'];
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
    static public function getModelAssociations( $modelType ) {
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
    static public function getProperties( $modelType ) {
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

    public function getProperty( $name ) {
        if ( isset( $this->properties[$name] ) )
            return $this->properties[$name];
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