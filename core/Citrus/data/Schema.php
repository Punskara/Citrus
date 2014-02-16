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
use \core\Citrus\sys\Exception;

class Schema {
    
    /**
     * @var string
     */
    public $class_name;
    
    /**
     * @var string
     */
    public $table_name;
    
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
    public $plural_description;
    
    /**
     * @var array
     */
    public $properties;
    
    /**
     * @var array
     */
    public $many_properties = array();
    
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
     * @param $class_name  name of the class we work with.
     */
    private function __construct( $class_name ) {
        $this->class_name = $class_name;
        $this->loadSchema();
    }

    static public function getInstance( $class_name ) {
        $cache = Citrus::getInstance()->cache;
        $schema = false;
        if ( $cache->hasSchemaOfClass( $class_name ) ) {
            $schema = $cache->getSchemaOfClass( $class_name );
        } else {
            $schema = new Schema( $class_name );
            $cache->addSchema( $class_name, $schema );
        }
        return $schema;
    }
    
    
    /**
     * Reads the schema file and loads its data.
     * 
     */
    private function loadSchema() {
        $schemaFile = CTS_PATH . '/' . str_replace( 
            '\\', 
            DIRECTORY_SEPARATOR, 
            $this->class_name 
        ) . '.schema.php';
        
        $found = false;

        if ( file_exists( $schemaFile ) ) {
            $found = true;
        } elseif ( $this->hasParentSchema() ) {
            $found = true;
            $schemaFile = $this->getParentSchema();
        } else {
            throw new Exception( "Unable to find schema for $this->class_name" );
            return;
        }
        if ( $found ) {
            $schema = include $schemaFile;
            $this->table_name = $schema['table_name'];
            $this->class_name = $schema['modelType'];
            $this->description = $schema['description'];
            $this->plural_description = $schema['plural_description'];
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
            if ( isset( $schema['many_properties'] ) ) {
                $this->many_properties = $schema['many_properties'];
            }
        }
    }

    public function hasParentSchema() {
        $c = new \ReflectionClass( $this->class_name ); 
        $parent = $c->getParentClass();
        $parent_class = $parent->getName();
        return file_exists(
            CTS_PATH . '/' . str_replace( 
            '\\', 
            DIRECTORY_SEPARATOR, 
            $parent_class 
        ) . '.schema.php' );
    }

    public function getParentSchema() {
        $c = new \ReflectionClass( $this->class_name ); 
        $parent = $c->getParentClass();
        return CTS_PATH . '/' . str_replace( 
            '\\', 
            DIRECTORY_SEPARATOR, 
            $parent->getName()
        ) . '.schema.php';
    }

    /**
     * Gets all "one to one" associations
     * 
     * @return array  $assoc  array of associations
     */
    public function getAssociations() {
        $assoc = array();
        foreach ( $this->properties as $name => $attr ) {
            if ( array_key_exists( 'foreignTable', $attr ) ) {
                $assoc[$name] = $attr;
            }
        }
        return $assoc;
    }

    public function getProperty( $name ) {
        if ( isset( $this->properties[$name] ) )
            return $this->properties[$name];
        return false;
    }
}