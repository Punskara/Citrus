<?php

namespace core\Citrus\sys;

class Cache {
    public $models = Array();
    public $schemas = Array();
    public $views = Array();
    
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