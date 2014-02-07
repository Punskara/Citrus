<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0.2                                                            |
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
 * @subpackage Citrus\data\HydratableQuery
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\Citrus;
use \core\Citrus\data;
use \core\Citrus\db\SelectQuery;

/**
 * This class generates an SQL query and hydrates objects with results
 */

class HydratableQuery extends SelectQuery {
    /**
     * type of object obtained
     * @var string
     */
    public $targetClass;

    /**
     * schema associations
     * @var array
     */
    public $assocs;
    
    
    /**
     * Constructor.
     *
     * @param string  $targetClass  type of object we fetch.
     *
     * @throws \Exception  if $targetClass is not a referenced class.
     */
    public function __construct( $targetClass ) {
        parent::__construct();
        if ( class_exists( $targetClass ) ) {
            $this->targetClass = $targetClass;
            $this->table = data\Schema::getTableName( $targetClass );
            $this->assocs = data\Schema::getModelAssociations( $targetClass );
            $this->columns = array( '*' );
        } else {
            throw new \Exception( "Cannot hydrate query: targetClass '$targetClass' does not exist." );
        }
    }
    
    /**
     * Hydrates objects
     * 
     * @return object|array  an object or an array of objects of $targetClass
     */
    public function hydrate() {
        $out = $this->hydrateList();
        switch ( count( $out ) ) {
            case 0: return null;
            case 1: return $out[0];
        }
        return $out;
    }
    
    
    /**
     * Hydrates a list of objects
     *
     * @return a list of hydrated objects
     *
     * @throws \Exception  if $this->targetClass is null or empty
     */
    public function hydrateList() {
        if ( !$this->targetClass ) throw new \Exception( "Cannot hydrate query: no target class specified." );
        $i = 0;
        
        foreach ( $this->assocs as $colName => $attr ) {
            $table = $this->AddAlias( $attr['foreignTable'], 'i' . $i );
            $join =  'i' . $i . '.' . $attr['foreignReference'] . " = " . $this->table . '.' . $colName;
            $this->AddLeftJoin( $table, $join );
            $i++;
        }
        $res = $this->Execute()->fetchAll( \PDO::FETCH_NUM );
        $ids = array();
        $out = array();
        foreach ( $res as $row ) {
            $item = $this->HydrateRow( $row );
            if ( !in_array( $item->id, $ids ) ) {
                $out[] = $item;
                $ids[] = $item->id;
            }
        }
        return $out;
    }
    
    
    /**
     * Hydrates an object
     *
     * @param array $row the row of the resultset
     * @return a hydrated object
     *
     * @throws \Exception  if $this->targetClass is null or empty
     */
    public function hydrateRow( $row ) {   
        if ( !$this->targetClass ) throw new \Exception( "Cannot hydrate query: no target class specified." );
        $inst = new $this->targetClass();
        $inst->hydrate( $row );
        return $inst;
    }
    
    public function count() {
        if ( !$this->targetClass ) throw new \Exception( "Cannot hydrate query: no target class specified." );
        $i = 0;
        $this->columns = array( "COUNT(DISTINCT(`$this->table`.`id`))" );
        /*$this->leftJoins = Array();
        foreach ( $this->assocs as $colName => $attr ) {
            $table = $this->AddAlias( $attr['foreignTable'], 'i' . $i );
            $join =  'i' . $i . '.' . $attr['foreignReference'] . " = " . $this->table . '.' . $colName;
            $this->AddLeftJoin( $table, $join );
            $i++;
        }*/
        $res = $this->Execute()->fetchColumn( 0 );
        // $this->leftJoins = Array();
        // $this->columns = Array();
        return $res;
    }
}