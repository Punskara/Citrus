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
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\db;
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
    
    protected $_props;

    public function __construct() {
        $this->datecreated = new \core\Citrus\Date( $this->datecreated );
        $this->datemodified = new \core\Citrus\Date( $this->datemodified );
        $this->schema = new data\Schema( get_class( $this ) );
    }
    
    public function save() {
        $tableName = $this->schema->tableName;
        if ( $this->schema ) {
            if ( !$this->id ) {
                $this->datecreated = date( 'Y-m-d H:i:s' );
                $this->datemodified = date( 'Y-m-d H:i:s' );
                $qry = new db\InsertQuery();
                $qry->table = $this->schema->tableName;
                foreach ( $this->schema->properties as $propName => $propArgs ) {
                    if ( strpos( $propName, 'date' ) !== false ) {
                        if ( $this->$propName instanceof \core\Citrus\Date ) {
                            $this->$propName = $this->$propName->format( 'Y-m-d H:i:s' );
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
                foreach ( $this->schema->properties as $propName => $propArgs ) {
                    if ( $propName != 'datecreated' ) {
                        if ( $this->$propName != '' ) {
                            $qry->SetValue( $propName, $this->$propName );
                        } elseif ( $propArgs['null'] == true ) {
                            $qry->SetValue( $propName, null );
                        }
                    }
                }
                $qry->AddWhere( 'id = ' . $this->id );
                $rec = $qry->Execute();
            }
            return $rec;
        } else return false;
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
            if ( isset( $details['inputType'] ) && $details['inputType'] != 'InputFile' ) {
                $value = \core\Citrus\Filter::filterVar( $propName, $details['type'] );
                if ( $value !== false ) {
                    $this->$propName = $value;
                    $this->args[$propName] = $value;
                } else {
                    if ( isset( $details['foreignReference'] ) && isset( $details['foreignTable'] ) ) {
                        $value = null;
                    } else {
                        $value = "";
                    }
                    $this->$propName = $value;
                    $this->args[$propName] = $value;
                }
            }
        }
    }

    public function delete( $id ) {
        $cos = \core\Citrus\Citrus::getInstance();
        $tableName = $this->tableName ? $this->tableName : $this->schema->tableName;
        $del = $cos->db->execute(
            "DELETE FROM $tableName WHERE id = $id"
        );
        return $del;
    }
    
    /*public static function deleteSeveral( $ids ) {
        $cos = Citrus::getInstance();
        $tableName = $this->tableName ? $this->tableName : $this->schema->schema['tableName'];
        $del = false;
        
        if ( is_array( $ids ) ) {
            $del = $cos->db->execute(
                "DELETE FROM `$tableName` WHERE `id` IN ($id)"
            );
        }
        return $del;
    }*/
    
    
    /**
     * Generates an HTML form from the schema of the object
     */
    public function generateForm() {
        $this->form = new Form\Form();
        $props = $this->schema->properties;
        $this->form->elements['modelType'] = new Form\InputHidden( 
            'modelType', '', 'modelType', array(), $value = $this->schema->className
        );
        foreach ( $props as $propName => $propAttr ) {
            if ( isset( $propAttr['inputType'] ) ) {
                if ( class_exists( '\\core\\Citrus\\Form\\' . $propAttr['inputType'] ) ) {
                    $element = '\\core\\Citrus\\Form\\' . $propAttr['inputType'];
                    $classes = ( isset( $propAttr['null'] ) && $propAttr['null'] == false ) ? array( 'required' ) : array();
                    $this->form->elements[$propName] = new $element( 
                        $propName, 
                        isset( $propAttr['formLabel'] ) ? $propAttr['formLabel'] : '', 
                        $propName,
                        $classes,
                        $this->$propName
                    );
                    if ( $element == '\\core\\Citrus\\Form\\SelectOne' ) {
                        if ( isset( $propAttr['modelType'] ) ) {
                            $this->form->elements[$propName]->makeOptions( 
                                $propAttr['modelType'],
                                isset( $propAttr['firstBlank'] )
                            );
                        } elseif ( isset( $propAttr['options'] ) ) {
                            $this->form->elements[$propName]->options = $propAttr['options'];
                        }
                    }
                }
            }
        }
        #return $this->form->html;
    }
    
    /**
     * Displays generated HTML form
     */
    public function displayForm() {
        $render = '';
        foreach ( $this->form->elements as $elt ) {
            $render .= $this->form->renderElement( $elt->name );
        }
        return $render;
    }
    
    protected function _getProps() {
        $this->_props = array();
        foreach ( $this->schema->properties as $prop => $attr ) {
            $this->_props[] = $prop;
        }
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
            return $list;
        } 
        return false;
    }
    
    public static function selectAllLimit( $targetClass, $limit, $pager = false ) {
        if ( class_exists( $targetClass ) ) {   
            $schema = self::getSchema( $targetClass );
            $q = new HydratableQuery( $schema->className );
            $q->columns = array( '*' );
            $q->table = $schema->tableName;
        
            if ( isset( $schema->orderColumn ) ) {
                $order = $schema->orderColumn;
                if ( isset( $schema->orderSort ) ) {
                    $order .= ' ' . $schema->orderSort;
                }
                $q->addOrder( $order );
            }  
            if ( is_int( $limit ) ) {
                $q->limitStart = 0;
                $q->limitCount = $limit;
            }
            $list = $q->hydrateList();
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
        if ( class_exists( $targetClass ) ) {
            $sc = new data\Schema( $targetClass );
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
        $q = new SelectQuery();
        $schema = self::getSchema( $targetClass );
        $q->table = $schema->tableName;
        $q->columns = array( "COUNT(`id`)" );
        if ( $where ) $q->addWhere( $where );
        return $q->execute()->fetchColumn( 0 );
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
        if ( !is_int( $id ) || !class_exists( $targetClass ) ) {
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
    * Hydrates the object
    * 
    * @deprecated Replaced by the use of HydratableQuery
    *
    * @param array $args  class properties
    *
    * @return false|object whether if the object is found or not
    */
    public function hydrate( $args ) {
        $this->_getProps();
        $nbProps = count( $this->_props );
        $class = $this->schema->className;
        $assoc = $this->schema->getAssociations();
        
        // sur le nb de propriétés de l'obj ppal
        // on assigne la propriété par la valeur correspondant à son indice dans les résultats
        for ( $i = 0; $i < $nbProps; $i++ ) {
            if ( isset( $this->_props[$i] ) ) {
                $prop = $this->_props;
                if ( preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/', $args[$i] ) ) {
                    $args[$i] = new \core\Citrus\Date( $args[$i] );
                }
                $this->$prop[$i] = $args[$i];
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
            $cos = \core\Citrus::getInstance();
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
	
	public function toArray() {
		return get_object_vars( $this );
	}
	
	public function toJSON() {
		return \core\Citrus\JSON::encode( $this->toArray() );
	}
}