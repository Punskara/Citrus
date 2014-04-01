<?php

namespace core\Citrus\routing;

use \core\Citrus\sys\Exception;

class NoRouteFoundException extends Exception {
    public function __construct() {
        parent::__construct( "No route found" );
    }
}