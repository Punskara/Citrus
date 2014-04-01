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
    static public function filterVar( $var_name, $type, $method = "GET", $params = Array() ) {
        $typesCorresp = array(
            'int'       => FILTER_VALIDATE_INT,
            'float'     => FILTER_VALIDATE_FLOAT,
            'string'    => FILTER_SANITIZE_STRING,
            'text'      => FILTER_SANITIZE_SPECIAL_CHARS,
            'boolean'   => FILTER_VALIDATE_BOOLEAN,
            'email'     => FILTER_VALIDATE_EMAIL,
            'datetime'  => FILTER_VALIDATE_REGEXP,
            'array'     => array(
                'filter' => FILTER_VALIDATE_INT,
                'flags'  => FILTER_FORCE_ARRAY,
            ),
        );
        $method_int = INPUT_GET;
        $args = $params;

        /*if ( $method == "POST" ) { 
            $method_int = INPUT_POST;
            $args = $_POST;
        }*/
        
        $inarray_type = '';
        if ( preg_match("/array\[([A-Za-z])+\]/", $type ) ) {
            $inarray_type = preg_replace( "/(array\[)([A-Za-z]+)(\])/", "$2", $type );
            $type = 'array';
            if ( array_key_exists( $inarray_type, $typesCorresp ) ) {
                $typesCorresp['array']['filter'] = $typesCorresp[$inarray_type];
            }
        }
        $value = false;
        if ( array_key_exists( $type, $typesCorresp ) ) {
            switch ( $type ) {
                case 'datetime': 
                    $value = filter_var( 
                        $args[$var_name], 
                        FILTER_VALIDATE_REGEXP, array( "options" => array(
                            "regexp" => '/[0-9]{2}\\/[0-9]{2}\\/[0-9]{4}/'
                    ) ) );
                    if ( $value ) {
                        $value = implode( '-', array_reverse( explode( '/', $value ) ) );
                    }
                break;
                case 'boolean': 
                    $value = filter_var( $args[$var_name], $typesCorresp['boolean'] );
                    if ( !$value ) {
                        $value = 0;
                    }
                break;
                case 'array':
                    $request_params = $_GET;
                    if ( isset( $args[$var_name] ) ) $value = $args[$var_name];
                    if ( !is_array( $value ) ) {
                        if ( $inarray_type == 'int' && preg_match( "#^[0-9+,]*[0-9]+$#", $value ) ) {
                            $value = explode( ',', $value );
                            if ( is_array( $value ) ) {
                                $value = filter_var_array( 
                                    Array( $var_name => $value ), 
                                    Array( $var_name => $typesCorresp['array'] ) 
                                );
                                $value = $value[$var_name];
                            }
                        }
                    }
                break;
                default:
                     $value = filter_var( $args[$var_name], $typesCorresp[$type] );
                     $value = trim( $value, ',' );
                 break;
            }
            if ( $typesCorresp[$type] == FILTER_SANITIZE_SPECIAL_CHARS ) {
                # gros patch qui tache
                $value = html_entity_decode( $value );
            }
            return $value;
        }
    }
}