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
 * @subpackage Citrus\data\Model
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;

use \core\Citrus\Citrus;
use \core\Citrus\db\InsertQuery;
use \core\Citrus\db\UpdateQuery;
use \core\Citrus\db\SelectQuery;
use \core\Citrus\html\form;
use \core\Citrus\data\Schema;
use \core\Citrus\Date;
use \core\Citrus\JSON;

/**
 * This class is parent for any persistant object used with Citrus
 * Its methods are useful to CRUD objects, 
 * and to generate back office forms and lists
 */

class Model {
    
    /**
     * @var int
     */
    public $id;
    
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
     * @var \core\Citrus\data\Schema
     */
    public $schema;

    protected $toArrayExclude = Array();
    
    protected $_props = Array();

    public function __construct() {
        $this->datecreated  = new Date( $this->datecreated );
        $this->datemodified = new Date( $this->datemodified );
        $this->schema = Schema::getInstance( get_called_class() );
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
        if ( $this->schema ) {
            if ( !$this->id || $forced ) {
                $this->datecreated = date( 'Y-m-d H:i:s' );
                $this->datemodified = date( 'Y-m-d H:i:s' );
                $qry = new InsertQuery();
                $qry->table = $this->schema->table_name;
                foreach ( $this->schema->properties as $propName => $propArgs ) {
                    if ( strpos( $propName, 'date' ) !== false ) {
                        if ( $this->$propName instanceof Date ) {
                            $this->$propName = $this->$propName->format( 'Y-m-d H:i:s' );
                        } else {
                            $this->$propName = Date::parse( $this->$propName )->format( 'Y-m-d H:i:s' );
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
                $qry = new UpdateQuery();
                $qry->table = $this->schema->table_name;
                $qry->SetValue( 'datemodified', $this->datemodified );
                foreach ( $this->schema->properties as $propName => $propArgs ) {
                    if ( $propName != 'datecreated' && $propName != 'datemodified' ) {
                        if ( $this->$propName != '' ) {
                            if ( strpos( $propName, 'date' ) !== false ) {
                                if ( $this->$propName instanceof Date ) {
                                    $this->$propName = $this->$propName->format( 'Y-m-d H:i:s' );
                                } else {
                                    $this->$propName = Date::parse( $this->$propName )->format( 'Y-m-d H:i:s' );
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
    
    public function delete() {
        $db = Citrus::getInstance()->getDatabase();
        $del = $db->execute(
            "DELETE FROM {$this->schema->table_name} WHERE id = $this->id"
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
    static public function selectAll( $targetClass, $pager = false ) {
        if ( class_exists( $targetClass ) ) {   
            $cos = Citrus::getInstance();
            if ( $cos->debug ) $cos->debug->startNewTimer( '[Model::selectAll(' . $targetClass . ')]' );
            $schema = Schema::getInstance( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->$schema->table_name;
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
    static public function selectAllWhere( $targetClass, $where = array(), $pager = false, $or_where = false, $order = false, $orderType = false, $limit = false ) {
        if ( class_exists( $targetClass ) ) {   
            $cos = Citrus::getInstance();
            if ( $cos->debug ) $cos->debug->startNewTimer( '[Model::selectAllWhere(' . $targetClass . ')]' );
            $schema = Schema::getInstance( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->table_name;
            
            if ( count( $where ) > 0 ) {
                if ( $or_where ) {
                    foreach ( $where as $k=>$cond ) $where[ $k ] = $q->table . '.' . $cond ; 
                    $q->AddORWhere( $where );
                }
                else foreach ( $where as $cond ) $q->addWhere( $q->table . '.' . $cond ); 
            }
            if ( $order !== false ) {
                if ( isset( $schema->properties[$order] ) && isset( $schema->properties[$order]['modelType'] ) ) {
                    $sch2 = Schema::getInstance( $schema->properties[$order]['modelType'] );
                    $order = explode(' ', $sch2->orderColumn );
                    $order = array_shift( $order );
                    
                    if ( $orderType !== false ) $order .= ' ' . $orderType;
                    $q->AddOrder( $sch2->table_name . '.' . $order );
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
    
    
    /**
     * Counts how many objects of $targetClass exists in database 
     * according to the $where parameter.
     * 
     * @return integer  the number of objects.
     *
     * @static
     */
    static public function count( $targetClass, $where = false ) {
        $q = new SelectQuery();
        $schema = Schema::getInstance( $targetClass );
        if ($schema) {
            $q->table = $schema->table_name;
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
            $schema = Schema::getInstance( $targetClass );
            $q = new HydratableQuery( $targetClass );
            $q->columns = array( '*' );
            $q->table = $schema->table_name;

            $q->AddWhere( "$q->table.id = $id" );
            if ( isset( $schema->orderColumn ) ) {
                $order = $schema->table_name . '.' . $schema->orderColumn;
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
        $class = $this->schema->class_name;
        $assoc = $this->schema->getAssociations();
        
        // sur le nb de propriétés de l'obj ppal
        // on assigne la propriété par la valeur correspondant à son indice dans les résultats
        for ( $i = 0; $i < $nbProps; $i++ ) {
            if ( isset( $this->_props[$i] ) ) {                
                $prop = $this->_props;
                if (isset($args[$i])) {
                    if ( preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/', $args[$i] ) ) {
                        $args[$i] = new Date( $args[$i] );
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
            $db = Citrus::getInstance()->getDatabase();
            $schema = Schema::getInstance( $targetClass );
            if ( $schema->table_name && is_int( $id ) ) {
                return $db->execute( "DELETE FROM `$schema->table_name` WHERE `id` = $id" );
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
        $db = Citrus::getInstance()->getDatabase();
        $schema = Schema::getInstance( $targetClass );
        $ids = implode( ',', $ids );
        return $db->execute( "DELETE FROM `$schema->table_name` WHERE `id` IN ($ids)" );
    }


    public function serialize() {
        return serialize( $this );
    }
    
    public function toArray( $merged = array() ) {
        $lst = array_merge( get_object_vars( $this ), $merged );
        $res = array();
        $schema_keys = array_merge( 
            array_values( array_keys( $this->schema->properties ) ),
            array_values( array_keys( $this->schema->manyProperties ) )
        );
        foreach ( $schema_keys as $key ) {
            if ( !in_array( $key, $this->toArrayExclude ) ) {
                $value = $this->$key;
                if ( is_object ( $value ) && is_subclass_of( $value, '\core\Citrus\data\Model' ) ) 
                    $res[ $key ] = $value->toArray();
                elseif ( is_object ( $value ) && get_class( $value ) == 'core\Citrus\Date' )
                    $res[ $key ] = $value->format( 'Y-m-d H:i:s' );
                elseif ( is_array( $value ) )
                    $res[ $key ] = $this->__get( $key );
                else $res[ $key ] = $value;
            }
        }
        return $res;
    }
    
    public function toJSON() {
        return JSON::encode( $this->toArray() );
    }
    
}