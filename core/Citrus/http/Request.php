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
 * @package Citrus\http
 * @subpackage Citrus\http\Request
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;
use \core\Citrus;

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
    public $inputMethod;
    
    /**
     * @var string
     */
    public $queryString;
    
    /**
     * @var string
     */
    public $referer;
    
    /**
     * @var boolean  Determines whether the request is passed by AJAX or not.
     */
    public $isXHR;
    
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->queryString = $_SERVER['QUERY_STRING'];
        $this->referer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null ;
        $this->isXHR = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        if ( $this->method == 'POST' ) {
            $this->inputMethod = INPUT_POST;
            $this->params = $_POST;
        } else {
            $this->inputMethod = INPUT_GET;
            $this->params = $_GET;
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
    public function param( $name, $filter ) {
        if ( isset( $this->params[$name] ) ) {
            return filter_var( $this->params[$name], $filter );
        }
        return false;
    }
    
    /**
     * Gets a variable sent by POST
     * 
     * @param string  $key  Name of the variable.
     * 
     * @return mixed|array|boolean  If found, the value of the variable, else false. If key is not provided, returns all the $_POST array.
     */
    public function post( $key = null ) {
        if ( $key ) {
            if ( isset( $_POST[$key] ) ) {
                return $_POST[$key];
            } else {
                return false;
            }
        } else {
            return $_POST;
        }
    }
    
    /**
     * Gets a variable sent by GET
     * 
     * @param string  $key  Name of the variable.
     * 
     * @return mixed|array|boolean  If found, the value of the variable, else false. If key is not provided, returns all the $_GET array.
     */
    public function get( $key ) {
        if ( $key ) {
            if ( isset( $_GET[$key] ) ) {
                return $_GET[$key];
            } else {
                return false;
            }
        } else {
            return $_GET;
        }
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
    
    
    /**
     * Determines if the referer is internal or not by comparing it to the host.
     * 
     * @return boolean
     */
    public function refererIsInternal() {
        $cos = Citrus\Citrus::getInstance();
        $referer = parse_url( $this->referer );
        if ( isset( $referer['host'] ) ) {
            return $cos->host->httpHost == $referer['host'];
        }
        return false;
    }
}