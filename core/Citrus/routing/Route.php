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
 * @package Citrus\routing
 * @subpackage Citrus\routing\Route
 * @author Dan Sosedoff <dan.sosedoff@gmail.com>
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



/**
 * Thanks to http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
 */

 
namespace core\Citrus\routing;

class Route {
    public $is_matched = false;
    public $params;
    public $url;
    private $conditions;
    
    public function __construct( $url, $request_uri, $target, $conditions ) {
        $this->url = $url;
        $this->params = array();
        $this->conditions = $conditions;
        $p_names = array(); $p_values = array();
     
        preg_match_all( '@:([\w]+)@', $url, $p_names, PREG_PATTERN_ORDER );
        $p_names = $p_names[0];
     
        $url_regex = preg_replace_callback( 
            '@:[\w]+@', 
            array( $this, 'regex_url' ), 
            $url 
        );
        $url_regex .= '/?';
        
        if ( preg_match( '@^' . $url_regex . '$@', $request_uri, $p_values ) ) {
            array_shift( $p_values );
            foreach( $p_names as $index => $value ) {
                $this->params[substr( $value, 1 )] = urldecode( $p_values[$index] );
            }
            foreach( $target as $key => $value ) {
                $this->params[$key] = $value;
            }
            $this->is_matched = true;
        }
     
        unset( $p_names ); 
        unset( $p_values );        
    }

    function regex_url($matches) {
        $key = str_replace( ':', '', $matches[0] );
        if ( array_key_exists( $key, $this->conditions ) ) {
            return '(' . $this->conditions[$key] . ')';
        } 
        else {
            return '([a-zA-Z0-9_\+\-%]+)';
        }
  }
}