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
 * @package functions
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

function vexp( $var, $pre = false ) {
    $st = '';
    if ( $pre ) $st .= '<pre>';
    $st .= var_export( $var, true );
    if ( $pre ) $st .= '</pre>';
    echo $st;
} 

function prr( $var, $pre = false ) {
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