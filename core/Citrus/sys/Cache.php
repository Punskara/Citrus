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
 * @package Citrus\sys
 * @subpackage Citrus\sys\Cache
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

namespace core\Citrus\sys;

class Cache {
    public $models  = Array();
    public $schemas = Array();
    public $views   = Array();
    
    public function __construct() {
        
    }
    
    public function addModel( $model ) {
        
    }
    
    public function modelExists( $class_name ) {
        
    }
    
    public function hasSchemaOfClass( $class_name ) {
        return isset( $this->schemas[$class_name] );
    }
    
    public function getSchemaOfClass( $class_name ) {
        return $this->schemas[$class_name];
    }
    
    public function addSchema( $class_name, $schema ) {
        $this->schemas[$class_name] = $schema;
    }
}