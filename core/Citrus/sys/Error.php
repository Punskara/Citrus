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
 * @package Citrus\sys
 * @subpackage Citrus\sys\Error
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\sys;

class Error {
    public $number;
    public $msg;
    public $file;
    public $line;
    public $context;
    
    public function __construct( $number, $msg, $file, $line, $context, $stack ) {
        $this->number = $number;
        $this->msg = $msg;
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
        $this->stack = $stack;
    }
    
    public static function renderHtml( $err, $message = null ) {
        $s = '';
        if ( $message ) {
            $s .= '<p class="message">' . $message . '</p>';
        }
        $s .= '<p class="message">' . $err->msg . '</p>'
           . '<p>'
           . '<code>' . $err->file . '</code>, line ' . $err->line . '.'
           . '</p>'
           . '<p>Trace :</p>'
           . '<ol>';
        foreach ( $err->getTrace() as $tr ) {
            $s .= '<li><code>';
            if ( isset( $tr['class'] ) ) $s .= $tr['class'];
            if ( isset( $tr['type'] ) ) $s .= $tr['type'];
            $s .= $tr['function'] . '</code> '
                . '<i>' . $tr['file'] . '</i> line ' . $tr['line']
                . '</li>';
        }
        $s .= '</ol>';
        return $s;
    }
    
    public function getTrace() {
        $i = 0;
        foreach ( $this->stack as $i => $node ) {
            if ( isset( $node['file'] ) )       $str[$i]['file'] = $node['file'];
            if ( isset( $node['line'] ) )       $str[$i]['line'] = $node['line'];
            if ( isset( $node['type'] ) )       $str[$i]['type'] = $node['type'];
            if ( isset( $node['class'] ) )      $str[$i]['class'] = $node['class'];
            if ( isset( $node['function'] ) )   $str[$i]['function'] = $node['function'];
            $i++;
        }
        return $str;
    }
}