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
 * @package functions
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

function vexp( $var, $pre = true ) {
    $st = '';
    if ( $pre ) $st .= '<pre>';
    $st .= var_export( $var, true );
    if ( $pre ) $st .= '</pre>';
    echo $st;
}

function vexpm() {
    $st = '';
    $nb = func_num_args();
    $args = func_get_args();
    foreach ( $args as $var ) {
        $st .= '<pre>';
        $st .= var_export( $var, true );
        $st .= '</pre>';
    }
    echo $st;
} 

function diex( $var ) {
    $st = '<pre>';
    $st .= var_export( $var, true );
    $st .= '</pre>';
    die( $st );
}


function prr( $var, $pre = true ) {
    $st = '';
    if ( $pre ) $st .= '<pre>';
    $st .= print_r( $var, true );
    if ( $pre ) $st .= '</pre>';
    echo $st;
}

function url_to( $route, $ret = false ) {
    $url = CTS_PROJECT_URL . $route;
    if ( $ret ) return $url;
    echo $url;
}