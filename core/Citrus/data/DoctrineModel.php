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
 * @subpackage Citrus\data\DoctrineModel
 * @author Nicolas MOURET <nicolas@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;

abstract class DoctrineModel {
    
    public $args;
    
    /**
     * Methodes abstraite simulée (message d'erreur associé) 
     */
    
    public function __set( $prop, $value ) {
        \core\Citrus\sys\Debug::handleException( 
            new \core\Citrus\sys\Exception('Undefined function : __set() in class '.get_called_class ())
        , true);
    }
    public function __get( $prop ) {
        \core\Citrus\sys\Debug::handleException( 
            new \core\Citrus\sys\Exception('fUndefined function : __get() in class '.get_called_class ())
        , true);
    }
    public function __setTime() {
        \core\Citrus\sys\Debug::handleException( 
            new \core\Citrus\sys\Exception('Undefined function : __setTime() in class '.get_called_class ())
        , true);
    }
    
    /**
     * Methodes standard 
     */
    public function save() {
        $cos = \core\Citrus\Citrus::getInstance();
        $em = $cos->orm->entityMgr;
        $this->persist();
        $em->flush();
    }
    
    public function persist() {
        $cos = \core\Citrus\Citrus::getInstance();
        $em = $cos->orm->entityMgr;
        if ( $this->__setTime() ) $em->persist($this);
    }
    
    public function hydrateByFilters() {
        $sch = call_user_func_array ( Array( get_called_class (), 'getSchema' ), array() );
        foreach ( $sch->properties as $propName => $details ) {
            if ( isset( $details['inputType'] ) && $details['inputType'] != 'InputFile' ) {
                $value = \core\Citrus\Filter::filterVar( $propName, $details['type'] );
                if ( $value === false ) {
                    if ( isset( $details['foreignReference'] ) && isset( $details['foreignTable'] ) ) $value = null;
                    else $value = "";
                }
                $this->$propName = $value;
            }
        }
    }
    
    
    public function hydrate( $args ) {
        $sch = call_user_func_array ( Array( get_called_class (), 'getSchema' ), array() );
        $nbProps = count( $sch->properties );
        $class = $sch->className;
        $assoc = $sch->getAssociations();

        $x = new \ReflectionClass( get_class( $this ) );
        foreach ( $args as $prop => $val ) {
            if ( $x->hasProperty($prop) ) {
                if ( is_string($val) && preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/', $val ) ) $val = new \core\Citrus\Date( $args[$i] );
                $this->$prop = $val;
            }
        }
    }
    
    public function generateForm() {
        $this->form = new Form\Form();
        $sch = call_user_func_array ( Array( get_called_class (), 'getSchema' ), array() );
        $this->form->elements['modelType'] = new Form\InputHidden( 
            'modelType', '', 'modelType', array(), $value = $this->schema->className
        );
        foreach ( $sch->properties as $propName => $propAttr ) {
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
    }
    
    public function displayForm() {
        $render = '';
        foreach ( $this->form->elements as $elt ) {
            $render .= $this->form->renderElement( $elt->name );
        }
        return $render;
    }

    /**
     * Methodes statiques 
     */

    public static function delete( $id ) {
        $cos = \core\Citrus\Citrus::getInstance();
        $em = $cos->orm->entityMgr;
        $elem = $em->find( get_called_class () , $id);
        $em->remove( $elem );
        $em->flush();
    }
    
    static public function deleteSeveral( $ids ) {
        foreach ( $ids as $id ) self::delete( $id );
    }
    
    public static function selectAll( $pager = false ) {

        $schema = self::getSchema();
        $query = "SELECT u FROM " . get_called_class () . " u ";

        if ( isset( $schema->orderColumn ) ) {
            $order = $schema->orderColumn;
            if ( isset( $schema->orderSort ) ) $order .= ' ' . $schema->orderSort;
            $query .= ' ORDER BY u.' . $order;
        } 
        
        if ( $pager ) $query .= ' LIMIT '. $pager->limitStart . ', ' . $pager->rowsPerPage;
        
        $cos = \core\Citrus\Citrus::getInstance();
        $em = $cos->orm->entityMgr;
        return $em->createQuery( $query )->getResult();
    }
    
    public static function getSchema( $sc = false ) {
        if ( class_exists( get_called_class () ) ) $sc = new Schema( get_called_class () );
        return $sc;
    }
    
    static public function count( $where = false ) {
        $query = 'SELECT COUNT(u) FROM ' . get_called_class () . ' u';
        if ( $where ) $query.=' WHERE ' . $where;
        $cos = \core\Citrus\Citrus::getInstance();
        $em = $cos->orm->entityMgr;
        return $em->createQuery( $query )->getSingleScalarResult();
    } 
    
    public static function selectOne( $id ) {
        if ( !is_int( $id ) ) {
            return false;
        } else {
            $query = "SELECT u FROM " . get_called_class () . " u WHERE u.id = ".$id;
            $cos = \core\Citrus\Citrus::getInstance();
            $em = $cos->orm->entityMgr;
            return $em->createQuery( $query )->getSingleResult();
        }
    }
}