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



function image_tag( $src, $args = null ) {
    $tag = '<img src="' . CITRUS_PROJECT_URL . 'images/' . $src . '" ';
    if ( is_array( $args ) && count( $args ) ) {
        foreach ( $args as $attr => $val ) {
            $tag .= $attr . '="' . $val . '" ';
        }
    }
    $tag .= '/>';
    
    return $tag;
}

function include_slice( $partial, $vars = null ) {
    $cos = core\Citrus\Citrus::getInstance();
    if ( $cos->debug ) {
        $cos->debug->startNewTimer( "partial " . $partial );
    }
    if ( is_array( $vars ) ) {
        extract( $vars, EXTR_OVERWRITE );
    }
    $file = $cos->app->controller->path . "/templates/_$partial.tpl.php";
    if ( file_exists( $file ) ) {
        include $file;
    }
    if ( $cos->debug ) {
        $cos->debug->stopLastTimer();
    }
}

function include_slice_global( $partial, $vars = null ) {
    $cos = core\Citrus\Citrus::getInstance();
    if ( $cos->debug ) {
        $cos->debug->startNewTimer( "partial " . $partial );
    }
    if ( is_array( $vars ) ) {
        extract( $vars, EXTR_OVERWRITE );
    }
    $file = $cos->app->path . "/templates/_$partial.tpl.php";
    if ( file_exists( $file ) ) {
        include $file;
    }
    if ( $cos->debug ) {
        $cos->debug->stopLastTimer();
    }
}

function link_to( $url, $text, $args = null ) {
    $extraParams = '';
    $attributes = '';
    if ( is_array( $args ) && count( $args ) ) {
        $attributes = array();
        foreach ( $args as $attr => $val ) {
            if ( $attr != 'extraParams' ) {
                $attributes[] = $attr . '="' . $val . '"';
            }
        }
        if ( isset( $args['extraParams'] ) ) {
            $extraParams .= '?' . $args['extraParams'];
            $extraParams = htmlentities( $extraParams );
        }
    }
    
    if ( substr( $url, 0, 1 ) == '/' && substr( CITRUS_PROJECT_URL, strlen( CITRUS_PROJECT_URL ) - 1, 1 ) ) {
        $location = substr( CITRUS_PROJECT_URL, 0, strlen( CITRUS_PROJECT_URL ) - 1 );
        $location .= $url . $extraParams;
    } else {
        $location = CITRUS_PROJECT_URL . $url . $extraParams;
    }
    $attributes = ' ' . implode( ' ', $attributes );
    return '<a href="' . $location . '"' . $attributes . '>' . $text . '</a>';
}

function form_tag( $method, $url, $args = null ) {
    $extraParams = '';
    $attributes = '';
    if ( is_array( $args ) && count( $args ) ) {
        $attributes = array();
        foreach ( $args as $attr => $val ) {
            if ( $attr != 'extraParams' ) {
                $attributes[] = $attr . '="' . $val . '"';
            }
        }
        if ( isset( $args['extraParams'] ) ) {
            $extraParams .= '?' . $args['extraParams'];
            $extraParams = htmlentities( $extraParams );
        }
    }
 
    if ( substr( $url, 0, 1 ) == '/' && substr( CITRUS_PROJECT_URL, strlen( CITRUS_PROJECT_URL ) - 1, 1 ) ) {
        $action = substr( CITRUS_PROJECT_URL, 0, strlen( CITRUS_PROJECT_URL ) - 1 );
        $action .= $url . $extraParams;
    } else {
        $action = CITRUS_PROJECT_URL . $url . $extraParams;
    }
    $attributes = ' ' . implode( ' ', $attributes );
    return '<form action="' . $action . '" method="' . $method . '"' . $attributes . '>';
}

function email_encode( $email ) {
    $ret_string = "";
    $len = strlen( $email );
    for( $x = 0; $x < $len; $x++ ) {
        $ord = ord( substr( $email, $x, 1 ) );
        $ret_string .= "&#$ord;";
    }
    return $ret_string;
}

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

function read_folder($dir = "root_dir/dir", $grep=false, $listDir = array())
{
    if($handler = opendir($dir)) {
        while (($sub = readdir($handler)) !== FALSE) {
            if (substr($sub,0,1) != "." && $sub != "Thumb.db" && $sub != "Thumbs.db") {
                if ((is_file($dir."/".$sub) && !$grep) || (is_file($dir."/".$sub) && preg_match( $grep, $sub) )) {
                    $listDir[] = $dir."/".$sub;
                } elseif (is_dir($dir."/".$sub)) {
                    $listDir = read_folder($dir.$sub, $grep, $listDir);
                }
            }
        }
        closedir($handler);
    }
    return $listDir;
}