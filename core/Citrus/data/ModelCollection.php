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
 * @subpackage Citrus\data\ModelCollection
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\Citrus;

/**
 * This class creates an object handling a HydratableQuery
 * with flexibility
 */

class ModelCollection {
    /**
     * @var Array
     */
    public $items = Array();

    /**
     * @var String
     */
    public $targetClass;

    /**
     * @var core\Citrus\data\HydratableQuery
     */
    public $query;
    
    public function __construct( $targetClass ) {
        if ( class_exists( $targetClass ) ) {   
            $this->targetClass = $targetClass;
            $schema = Model::getSchema( $this->targetClass );
            $cos = Citrus::getInstance();
            if ( $cos->cache->hasSchemaOfClass( $targetClass ) ) {
                $schema = $cos->cache->getSchemaOfClass( $targetClass );
            } else {
                $schema = Model::getSchema( $targetClass );
                $cos->cache->addSchema( $targetClass, $schema );
            }
            
            $this->query = new HydratableQuery( $targetClass );
            $this->query->columns = array( '*' );
            $this->query->table = $schema->tableName;
        }
    }
    
    /**
     * launches the query and stores results into $items;
     */
    public function fetch() {
        $this->query->columns = Array( '*' );
        $this->items = $this->query->hydrateList();
    }
    

    /**
     * Builds an array containing the results of the query
     * indexed by choosen column
     * 
     * @param $col    String    index
     * @return Array
     */
    public function toArrayIndexedBy( $col ) {
        if ( property_exists( $this->targetClass, $col ) ) {
            $array = Array();
            if ( count( $this->items ) > 0 ) foreach ( $this->items as $item ) {
                $array[$item->$col] = $item;
            }
            return $array;
        }
        return false;
    }
    

    /**
     * Returns the number of entries in Array $items
     * @return Integer
     */
    public function count() {
        return count( $this->items );
    }
}

