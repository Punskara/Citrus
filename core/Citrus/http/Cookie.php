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
|  Copyright (c) 2008-2014, Studio Caramia. All Rights Reserved.            |
| ------------------------------------------------------------------------- |
|   For the full copyright and license information, please view the LICENSE |
|   file that was distributed with this source code.                        |
'---------------------------------------------------------------------------'
*/

/**
 * @package Citrus\http
 * @subpackage Citrus\http\Cookie
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;
/**
 * A simple class to handle cookies
 */
class Cookie {
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $value;
    
    public function ___construct() {
        
    }
    
    
    /**
     * Sets a cookie.
     * 
     * @param string  $name  The name of the cookie
     * @param string  $value  The value of the cookie
     * @param timestamp  $expire  Expiration date of the cookie
     */
    static public function set( $name, $value, $expire ) {
        setCookie( $this->name, $value, )
    }
    
    
    /**
     * Gets a cookie from its name.
     * 
     * @param string  $name  The name of the cookie
     *
     * @return string|boolean whether the cookie is found or not.
     */
    static public function get( $name ) {
        if ( isset( $_COOKIE[$name] ) ) {
            return $_COOKIE[$name];
        }
        return false;
    }
}