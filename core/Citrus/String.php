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
 * @subpackage Citrus\String
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus;

class String {
    static public function slug( $string, $sep = '-' ) {
        $clean = iconv( 'UTF-8', 'ASCII//TRANSLIT', $string );
        $clean = preg_replace( "/[^a-zA-Z0-9\/_|+ -]/", '', $clean );
        $clean = preg_replace( "/[\/_|+ -]+/", $sep, $clean );
        $clean = strtolower( trim( $clean, '-' ) );
        return $clean;
    }
    
    static public function accent( $search ) {
        $search = preg_replace( '/[àâä]/iu', 'a', $search );
        $search = preg_replace( '/[éèëê]/iu', 'e', $search );
        $search = preg_replace( '/[îï]/iu', 'i', $search );
        $search = preg_replace( '/[ôö]/iu', 'o', $search );
        $search = preg_replace( '/[ùûü]/iu', 'u', $search );  
        return $search;
    }

    static public function br2nl( $string, $isXHTML = false ) {
        return $string;
        $br = $isXHTML ? "<br />" : "<br>";
        return str_replace( $br, "\n", $string );
    }

    static public function splitCamelCase( $s ) {
        return preg_split( 
            '/([[:upper:]][[:lower:]]+)/', $s, null, 
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY 
        );
    }
}