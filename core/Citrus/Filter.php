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
 * @package Citrus
 * @subpackage Citrus\Filter
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus;

/** 
 * This class' aim is to filter data.
 *
 * @todo add filtering functions and constants to improve the class.
 */
class Filter {
   
    /**
     * Checks if the type of $value is $type
     *
     * @param string  $value  Value to check
     * @param string  $type  the type that value must be.
     */    
    public static function filterVar( $value, $type ) {
        $typesCorresp = array(
            'int'       => FILTER_VALIDATE_INT,
            'float'     => FILTER_VALIDATE_FLOAT,
            'string'    => FILTER_SANITIZE_STRING,
            'text'      => FILTER_SANITIZE_SPECIAL_CHARS,
            'boolean'   => FILTER_VALIDATE_BOOLEAN,
            'datetime'  => FILTER_VALIDATE_REGEXP,
        );
        if ( array_key_exists( $type, $typesCorresp ) ) {
            if ( $type == 'datetime' ) {
                $value = filter_input( 
                    INPUT_POST, $value, 
                    FILTER_VALIDATE_REGEXP, array( "options" => array(
                        "regexp" => '/[0-9]{2}\\/[0-9]{2}\\/[0-9]{4}/'
                ) ) );
                if ( $value ) {
                    $value = implode( '-', array_reverse( explode( '/', $value ) ) );
                }
                #else $value = '';
            } elseif ( $type == 'boolean' ) {
                $value = filter_input( INPUT_POST, $value, $typesCorresp[$type] );
                if ( !$value ) {
                    $value = 0;
                }
            } else {
                $value = filter_input( INPUT_POST, $value, $typesCorresp[$type] );
            }
            if ( $typesCorresp[$type] == FILTER_SANITIZE_SPECIAL_CHARS ) {
                # gros patch qui tache
                $value = html_entity_decode( $value );
            }
            return $value;
        }
    }
}