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
 * @package Citrus\http
 * @subpackage Citrus\http\Request
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;
use \core\Citrus\Citrus;
use \core\Citrus\Filter;

/**
 * This class handles the HTTP request and its parameters.
 */
class Request {
    
    /**
     * @var string
     */
    public $method;
    
    /**
     * @var array
     */
    public $params = array();
    
    /**
     * @var integer
     */
    public $input_method;
    
    /**
     * @var string
     */
    public $query_string;
    
    /**
     * @var string
     */
    public $referer;

    /**
     * @var string
     */
    public $uri;
    
    /**
     * @var boolean  Determines whether the request is passed by AJAX or not.
     */
    public $is_XHR;

    public $address;

    /**
     * Constructor
     */
    public function __construct() {
        $this->method       = $_SERVER['REQUEST_METHOD'];
        $this->query_string = $_SERVER['QUERY_STRING'];
        $this->uri          = $_SERVER['REQUEST_URI'];
        $this->referer      = isset( $_SERVER['HTTP_REFERER'] ) 
                                ? $_SERVER['HTTP_REFERER'] 
                                : null ;
        $this->address      = isset( $_SERVER['REMOTE_ADDR'] ) 
                                ? $_SERVER['REMOTE_ADDR'] : null;

        $this->is_XHR = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) 
                        && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

        if ( $this->method == 'POST' ) {
            $this->input_method = INPUT_POST;
            $this->params       = $_POST;
            $this->files        = $_FILES;
        } else {
            $this->input_method  = INPUT_GET;
            $this->params       = $_GET;
        }
    }
    
    
    /**
     * Gets the param named $name
     *
     * @param string  $name  Name of the parameter
     * @param integer  $filter  An integer that determines the type of the parameter (see php filters)
     * 
     * @return mixed|boolean  Whether the parameter is found or not.
     */
    public function param( $name, $filter = "string" ) {
        if ( isset( $this->params[$name] ) ) {
            return Filter::filterVar( $name, $filter, $this->method, $this->params );
        }
        return false;
    }

    /**
     * Adds a parameter to the request parameters
     *
     * @param array  $params  all the parameters you want (key => value).
     *
     */
    public function addParams( $params = array() ) {
        $this->params = array_merge( $this->params, $params );
    }

    public function getFiles() {
        if ( isset( $_FILES ) ) return $_FILES;
        return Array();
    }

    public function getFile( $name ) {
        if ( isset( $_FILES[$name] ) ) return $_FILES[$name];
        return false;
    }
}