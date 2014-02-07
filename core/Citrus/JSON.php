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
 * @package Citrus
 * @subpackage Citrus\JSON
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus;

/**
 * Simple JSON class
 */
class JSON {
    
    /**
     * Encodes $value in JSON
     * 
     * @param mixed  $value  value to encode
     * @param integer  $options  see http://php.net/manual/en/function.json-encode.php
     */
    static public function encode( $value, $options = 0 ) {
        return json_encode( $value );
    }
    
    
    /**
     * Decodes a JSON object
     *
     * @param string  $json  the JSON object to decode
     * @param boolean  $cleanEntities  whether or not we clean html in the json string
     * @param boolean  $stripSlashes  whether we strip the slashes in the json string or not
     * @param boolean  $assoc  When TRUE, returned objects will be converted into associative arrays.
     * @param integer  $depth  User specified recursion depth.
     * 
     * @see http://www.php.net/manual/en/function.json-decode.php
     *
     * @return mixed|array the convertion of the object.
     */
    static public function decode( $json, $cleanEntities = false, $stripSlashes, $assoc = false, $depth = 512 ) {
        if ( $cleanEntities ) $json = html_entity_decode( $json );
        if ( $stripSlashes ) $json = stripslashes( $json );
        return json_decode( $json, $assoc );
    }
}