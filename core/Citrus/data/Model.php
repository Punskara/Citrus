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
 * @subpackage Citrus\data\Model
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\db;
use \core\Citrus;
use \core\Citrus\html\form;
use \core\Citrus\data;

/**
 * This class is parent for any persistant object used with Citrus
 * Its methods are useful to CRUD objects, 
 * and to generate back office forms and lists
 */

class Model {
    
    /**
     * @### Id @ ##Column(type="integer") @GeneratedValue
     */
    #private $id = 0;
    
    /**
     * @var array
     */
    public $args;

    /**
     * @Column(type="datetime")
     *
     * @var \core\Citrus\Date
     */
    private $datecreated;

    /**
     * @Column(type="datetime")
     *
     * @var \core\Citrus\Date
     */
    private $datemodified;
    
    /**
     * @var string
     */
    public $tableName;
    
    /**
     * @var \core\Citrus\data\Schema
     */
    public $schema;
    
    /**
     * @var \core\Citrus\html\form\Form
     */
    public $form;
    
    protected $_props = Array();

    public function __construct() {
        $this->datecreated = new \core\Citrus\Date( $this->datecreated );
        $this->datemodified = new \core\Citrus\Date( $this->datemodified );
        
        
        $cos = \core\Citrus\Citrus::getInstance();
        $this_class = get_class( $this );
        if ( $cos->cache->hasSchemaOfClass( $this_class ) ) {
            $this->schema = $cos->cache->getSchemaOfClass( $this_class );
        } else {
            $this->schema = new data\Schema( get_class( $this ) );
            $cos->cache->addSchema( get_class( $this ), $this->schema );
        }
    }
    
    public function __get( $name ) {
        if ( property_exists( $this, $name ) ) {
            return $this->$name;
        }
    }
    
    public function __set( $name, $value ) {
        if ( property_exists( $this, $name ) ) {
            $this->$name = $value;
        }
    }
    
    public function save( $forced = false ) {
        $tableName = $this->schema->tableName;
        if ( $this->schema ) {
            if ( !$this->id || $forced ) {
                $this->datecreated = date( 'Y-m-d H:i:s' );
                $this->datemodified = date( 'Y-m-d H:i:s' );
                $qry = new db\InsertQuery();
                $qry->table = $this->schema->tableName;
                foreach ( $this->schema->properties as $propName => $propArgs ) {
                    if ( strpos( $propName, 'date' ) !== false ) {
                        if ( $this->$propName instanceof \core\Citrus\Date ) {
                            $this->$propName = $this->$propName->format( 'Y-m-d H:i:s' );
                        } else {
                            $this->$propName = \core\Citrus\Date::parse( $this->$propName )->format( 'Y-m-d H:i:s' );
                        }
                    }
                    if ( $this->$propName != '' ) {
                        $qry->SetValue( $propName, $this->$propName );
                    }
                }
                $rec = $qry->Execute();
                //*/
                if ( $rec ) {
                    $this->id = $rec;
                }
            } else {
                $this->datemodified = date( 'Y-m-d H:i:s' );
                $qry = new db\UpdateQuery();
                $qry->table = $this->schema->tableName;
                $qry->SetValue( 'datemodified', $this->datemodified );
                foreach ( $this->schema->properties as $propName => $propArgs ) {
                    if ( $propName != 'datecreated' && $propName != 'datemodified' ) {
                        if ( $this->$propName != '' ) {
                            if ( strpos( $propName, 'date' ) !== false ) {
                                if ( $this->$propName instanceof \core\Citrus\Date ) {
                                    $this->$propName = $this->$propName->format( 'Y-m-d H:i:s' );
                                } else {
                                    $this->$propName = \core\Citrus\Date::parse( $this->$propName )->format( 'Y-m-d H:i:s' );
                                }
                            }
                            $qry->SetValue( $propName, $this->$propName );
                        } elseif ( isset($propArgs['null']) && $propArgs['null'] == true ) {
                            $qry->SetValue( $propName, null );
                        } elseif ( $propArgs['type'] == 'boolean' && $this->$propName == 0 ) {
                            $this->$propName = false;
                            $qry->SetValue( $propName, $this->$propName );
                        }
                    }
                }
                // exit;
                $qry->AddWhere( 'id = ' . $this->id );
                $rec = $qry->Execute();
            }
            return $rec;
        } else {
            return false;
        } 
    }
    
    /**
     * @deprecated Any use of $args will be removed
     */
    public function hydrateWithArgs() {
        if ( is_array( $this->args ) ) {
            $args = $this->args;
            foreach ( $args as $name => $value ) {
                $this->$name = $value;
                if ( strpos( $name, 'date' ) !== false ) {
                    $this->$name = new \core\Citrus\Date( $this->$name );
                }
            }
        }
    }
    
    
    /**
     * Hydrates an object with filtered vars in _POST
     */
    public function hydrateByFilters() {
        $props = $this->schema->properties;
        $this->args = array();
        foreach ( $props as $propName => $details ) {
            if ( isset( $_POST[$propName] ) ) {
                if ( isset( $details['inputType'] ) && $details['inputType'] != 'InputFile' ) {
                    $value = \core\Citrus\Filter::filterVar( $propName, $details['type'], 'POST', $_POST );
                    if ( $value !== false ) {
                        $this->$propName = $value;
                    } else {
                        if ( isset( $details['foreignReference'] ) && isset( $details['foreignTable'] ) ) {
                            $value = null;
                        } else {
                            $value = "";
                        }
                        $this->$propName = $value;
                    }
                }
            }
        }
    }
    
    public function hydrateManyByFilters() {
        $props = $this->schema->manyProperties;
        $this->args = array();
        foreach ( $props as $propName => $details ) {
            if ( isset( $_POST[$propName] ) ) {
                $value = \core\Citrus\Filter::filterVar( $propName, 'string', 'POST', $_POST );
                if ( $value !== false ) $this->$propName = $value;
                else $this->$propName = '';
            }
        }
    }

    public function delete() {
        $cos = \core\Citrus\Citrus::getInstance();
        $tableName = $this->tableName ? $this->tableName : $this->schema->tableName;
        $del = $cos->db->execute(
            "DELETE FROM $tableName WHERE id = $this->id"
        );
        return $del;
    }
    
    protected function _getProps() {
        // $this->_props = array();
        if ( count( $this->_props ) == 0 ) {
            foreach ( $this->schema->properties as $prop => $attr ) {
                $this->_props[] = $prop;
            }
        }
        return $this->_props;
    }
    
    /**
     * Data accessor : loads all objects of type $targetClass in database
     * 
     * @param $targetClass  string  Type of object
     * @param $pager  \core\Citrus\data\Pager|boolean  Pager object
     *
     * @static
     */
    public static function selectAll( $targetClass, $pager = false ) {
        if ( class_exists( $targetClass ) ) {   
            $cos = \core\Citrus\Citrus::getInstance();
            if ( $cos->debug ) $cos->debug->startNewTimer( '[Model::selectAll(' . $targetClass . ')]' );
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;
            if ( isset( $schema->orderColumn ) ) {
                $order = $schema->orderColumn;
                if ( isset( $schema->orderSort ) ) {
                    $order .= ' ' . $schema->orderSort;
                }
                $q->AddOrder( $q->table . '.' . $order );
            } 
            if ( $pager ) {
                $q->limitStart = $pager->limitStart;
                $q->limitCount = $pager->rowsPerPage;
            }
            $list = $q->hydrateList();
            if ( $cos->debug ) $cos->debug->stopLastTimer();
            return $list;
        } 
        return false;
    }
    
    
    /**
     * Data accessor : loads all objects of type $targetClass in database
     * 
     * @param $targetClass  string  Type of object
     * @param $pager  \core\Citrus\data\Pager|boolean  Pager object
     *
     * @static
     */
    public static function selectAllWhere( $targetClass, $where = array(), $pager = false, $or_where = false, $order = false, $orderType = false, $limit = false ) {
        if ( class_exists( $targetClass ) ) {   
            $cos = \core\Citrus\Citrus::getInstance();
            if ( $cos->debug ) $cos->debug->startNewTimer( '[Model::selectAllWhere(' . $targetClass . ')]' );
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;
            
            if ( count( $where ) > 0 ) {
                if ( $or_where ) {
                    foreach ( $where as $k=>$cond ) $where[ $k ] = $q->table . '.' . $cond ; 
                    $q->AddORWhere( $where );
                }
                else foreach ( $where as $cond ) $q->addWhere( $q->table . '.' . $cond ); 
            }
            if ( $order !== false ) {
                if ( isset( $schema->properties[$order] ) && isset( $schema->properties[$order]['modelType'] ) ) {
                    $sch2 = self::getSchema( $schema->properties[$order]['modelType'] );
                    $order = explode(' ', $sch2->orderColumn );
                    $order = array_shift( $order );
                    
                    if ( $orderType !== false ) $order .= ' ' . $orderType;
                    $q->AddOrder( $sch2->tableName . '.' . $order );
                } else {
                    if ( $orderType !== false ) {
                        $order .= ' ' . $orderType;
                    }
                    $q->AddOrder( $q->table . '.' . $order );
                }
            } else if ( isset( $schema->orderColumn ) ) {
                $order = $schema->orderColumn;
                if ( isset( $schema->orderSort ) ) {
                    $order .= ' ' . $schema->orderSort;
                }
                $q->AddOrder( $q->table . '.' . $order );
            } 
            if ( $pager ) {
                $q->limitStart = $pager->limitStart;
                $q->limitCount = $pager->rowsPerPage;
            } 
            if ( is_int( $limit ) ) {
                $q->limitStart = 0;
                $q->limitCount = $limit;
            }
            $list = $q->hydrateList();
            if ( $cos->debug ) $cos->debug->stopLastTimer();
            return $list;
        } 
        return false;
    }
    
    public static function selectAllLimit( $targetClass, $limit, $pager = false ) {
        if ( class_exists( $targetClass ) ) {   
            $cos = \core\Citrus\Citrus::getInstance();
            if ( $cos->debug ) $cos->debug->startNewTimer( '[Model::selectAllLimit(' . $targetClass . ')]' );
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $schema->className );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;
        
            if ( isset( $schema->orderColumn ) ) {
                $order = $schema->orderColumn;
                if ( isset( $schema->orderSort ) ) {
                    $order .= ' ' . $schema->orderSort;
                }
                $q->AddOrder( $q->table . '.' . $order );
            }  
            if ( is_int( $limit ) ) {
                $q->limitStart = 0;
                $q->limitCount = $limit;
            }
            $list = $q->hydrateList();
            if ( $cos->debug ) $cos->debug->stopLastTimer();
            return $list;
        } 
        return false;
    }
    
    /**
     * Gets the schema of the object
     * 
     * @param $targetClass  Type of the object
     * 
     * @return \core\Citrus\data\Schema  The schema.
     *
     * @static
     */
    static public function getSchema( $targetClass ) {  
        $sc = false;
        $cos = Citrus\Citrus::getInstance();
        if ( class_exists( $targetClass ) ) {
            if ( $cos->cache->hasSchemaOfClass( $targetClass ) ) {
                $sc = $cos->cache->getSchemaOfClass( $targetClass );
            } else {
                $sc = new data\Schema( $targetClass );
                $cos->cache->addSchema( $targetClass, $sc );
            }
        }
        return $sc;
    }
    
    
    /**
     * Counts how many objects of $targetClass exists in database 
     * according to the $where parameter.
     * 
     * @return integer  the number of objects.
     *
     * @static
     */
    static public function count( $targetClass, $where = false ) {
        $q = new db\SelectQuery();
        $schema = self::getSchema( $targetClass );
        if ($schema) {
            $q->table = $schema->tableName;
            $q->columns = array( "COUNT(`id`)" );
            if ( $where ) {
                $q->addWhere( implode("\nAND", $where ) );
            }
            return $q->execute()->fetchColumn( 0 );
        } else return 0;
    } 
    
    
    /**
    * Fetches the object of ID $id
    * 
    * @deprecated Replaced by selectOne
    *
    * @param integer  $id  object ID in database
    *
    * @return false|object whether if the object is found or not
    */
    public function getOne( $id ) {
        if ( !$id ) {
            return false;
        } else {
            $q = new HydratableQuery( $this->schema->className );
            $q->columns = array( '*' );
            $q->table = $this->schema->tableName;

            $q->AddWhere( "$q->table.id = $id" );
            if ( isset( $this->schema->orderColumn ) ) {
                $order = $this->schema->orderColumn;
                if ( isset( $this->schema->orderSort ) ) {
                    $order .= ' ' . $this->schema->orderSort;
                }
                $q->addOrder( $order );
            }
            $inst = $q->hydrate();
            return $inst;
        }
    }
    
    
    /**
    * Fetches the object of ID $id
    * 
    * @param integer  $id  object ID in database
    *
    * @return false|object whether if the object is found or not
    *
    * @static
    */
    static public function selectOne( $targetClass, $id ) {
        if ( !is_int( (int) $id ) || !class_exists( $targetClass ) ) {
            return false;
        } else {
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;

            $q->AddWhere( "$q->table.id = $id" );
            if ( isset( $schema->orderColumn ) ) {
                $order = $schema->tableName . '.' . $schema->orderColumn;
                if ( isset( $schema->orderSort ) ) {
                    $order .= ' ' . $schema->orderSort;
                }
                $q->addOrder( $order );
            }
            $inst = $q->hydrate();
            return $inst;
        }
    }
    
    /**
     * Data accessor : loads all objects of type $targetClass in database
     * 
     * @param $targetClass  string  Type of object
     * @param $where
     *
     * @static
     */
    public static function selectWhere( $targetClass, $where = array(), $or_where = false ) {
        if ( class_exists( $targetClass ) ) {   
            $cos = \core\Citrus\Citrus::getInstance();
            if ( $cos->debug ) $cos->debug->startNewTimer( '[Model::selectWhere(' . $targetClass . ')]' );
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;

             if ( count( $where ) > 0 ) {
                    if ( $or_where ) {
                        foreach ( $where as $k=>$cond ) $where[ $k ] = $q->table . '.' . $cond ; 
                        $q->AddORWhere( $where );
                    }
                    else foreach ( $where as $cond ) $q->addWhere( $q->table . '.' . $cond ); 
                }
            if ( isset( $schema->orderColumn ) ) {
                $order = $schema->orderColumn;
                if ( isset( $schema->orderSort ) ) {
                    $order .= ' ' . $schema->orderSort;
                }
                $q->AddOrder( $q->table . '.' . $order );
            } 
           
            $inst = $q->hydrate();
            if ( $cos->debug ) $cos->debug->stopLastTimer();
            return $inst;
        } 
        return false;
    }

    
    /**
    * Hydrates the object
    * 
    * @deprecated Replaced by the use of HydratableQuery
    *
    * @param array $args  class properties
    *
    * @return false|object whether if the object is found or not
    */
    public function hydrate( $args = array() ) {
        $this->_getProps();
        $nbProps = count( $this->_props );
        $class = $this->schema->className;
        $assoc = $this->schema->getAssociations();
        
        // sur le nb de propriétés de l'obj ppal
        // on assigne la propriété par la valeur correspondant à son indice dans les résultats
        for ( $i = 0; $i < $nbProps; $i++ ) {
            if ( isset( $this->_props[$i] ) ) {                
                $prop = $this->_props;
                if (isset($args[$i])) {
                    if ( preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/', $args[$i] ) ) {
                        $args[$i] = new \core\Citrus\Date( $args[$i] );
                    }
                    $this->$prop[$i] = $args[$i];
                }
            }
        }
        $j = 0;
        
        $nbCols = count( $args );
        foreach ( $assoc as $colName => $attr ) {
            $j = 0;
            $assocPropName = substr( $colName, 0, -3 );
            $assocClassName = $attr['modelType'];
            $this->$assocPropName = new $assocClassName();
            $this->$assocPropName->_getProps();
            for ( $i = $nbProps; $i < $nbCols; $i++ ) {
                if ( isset( $this->$assocPropName->_props[$j] ) ) {
                    $assocArgs = $this->$assocPropName->_props;
                    $this->$assocPropName->$assocArgs[$j] = $args[$i];
                }
                $j++;
            }
            $nbProps += count( $this->$assocPropName->_props );
        }   
    }
    
    public function hydrateAssoc() {
        $assoc = $this->schema->getAssociations();
        foreach ( $assoc as $colName => $attr ) {
            $assocPropName = substr( $colName, 0, -3 );
            // vexp($this->$assocPropName);
            // if (is_null($this->$assocPropName)) {
                $assocClassName = $attr['modelType'];
                if ( is_null($this->$colName) ) $this->$assocPropName = new $assocClassName();
                else $this->$assocPropName = call_user_func_array( array('\core\Citrus\data\Model', 'selectOne'), array( $attr['modelType'], (integer) $this->$colName ) ) ;
            // }
        }
        return $this;
    }
    
    
    /**
     * Deletes the object of type $targetClass which ID is $id
     *
     * @param string  $targetClass  type of the object
     * @param integer  $id  id of the object
     *
     * @return boolean
     *
     * @static
     */
    static public function deleteOne( $targetClass, $id ) {
        if ( class_exists( $targetClass ) ) {
            $cos = \core\Citrus\Citrus::getInstance();
            $schema = self::getSchema( $targetClass );
            if ( $schema->tableName && is_int( $id ) ) {
                return $cos->db->execute( "DELETE FROM `$schema->tableName` WHERE `id` = $id" );
            }
        }
        return false;
    }
    
    /**
     * Deletes the objects of type $targetClass which ID is in the array $ids
     *
     * @param string  $targetClass  type of the object
     * @param array  $ids  array of IDs to delete
     *
     * @return boolean
     *
     * @static
     */
    static public function deleteSeveral( $targetClass, $ids ) {
        $cos = \core\Citrus\Citrus::getInstance();
        $schema = self::getSchema( $targetClass );
        $ids = implode( ',', $ids );
        return $cos->db->execute( "DELETE FROM `$schema->tableName` WHERE `id` IN ($ids)" );
    }


    public function serialize() {
        return serialize( $this );
    }
    
    public function toArray( $merged = array() ) {
        $lst = array_merge( get_object_vars( $this ), $merged );
        $res = array();
        $schema_keys = array_values( array_keys( $this->schema->properties ) );
        // foreach( $lst as $key => $value ) {
        while( list( $key, $value ) = each( $lst ) ) { 
            if (in_array( $key , $schema_keys ) || is_subclass_of( $value, '\core\Citrus\data\Model' )) {
                if ( is_object ( $value ) && is_subclass_of( $value, '\core\Citrus\data\Model' )) $res[ $key ] = $value->toArray();
                else $res[ $key ] = $value;
            }
        }
        return $res;
    }
    
    public function toJSON() {
        return \core\Citrus\JSON::encode( $this->toArray() );
    }
    
    /**
     * Data accessor : loads all objects of type $targetClass in database
     * 
     * @param $targetClass  string  Type of object
     * @param $pager  \core\Citrus\data\Pager|boolean  Pager object
     *
     * @static
     */
    public static function select( $targetClass ) {
        if ( class_exists( $targetClass ) ) {   
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;
            return $q;
        } 
        return false;
    }
}