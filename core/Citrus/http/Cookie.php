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