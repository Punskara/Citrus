<?php
/**
 * @author Rémi Cazalet <remi@caramia.fr>
 */

namespace core\Citrus\http;

class CurlExecFailedException extends \Exception {    
    public function __construct( $message ) {
        parent::__construct( $message );
    }
}