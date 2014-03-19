<?php

namespace core\Citrus\mvc;

use \core\Citrus\sys\Exception;

class NoControllerFoundException extends Exception {
    public function __construct( $name = "" ) {
        parent::__construct( "Controller $name not found" );
    }
}