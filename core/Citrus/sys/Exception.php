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
 * @subpackage Citrus\sys\Exception
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\sys;

class Exception extends \Exception {
    public function __construct( $message ) {
        parent::__construct( $message );
    }
    
    public function __toString() {
        /*$s = '<p class="message">' . $this->getMessage() . '</p>'
           . '<p>'
           . '<code>' . $this->getFile() . '</code>, line ' . $this->getLine() . '.'
           . '</p>'
           . '<p>Trace :</p>'
           . '<ol>';
        foreach ( $this->getTrace() as $tr ) {
            $s .= '<li><code>';
            if ( isset( $tr['class'] ) ) $s .= $tr['class'];
            if ( isset( $tr['type'] ) ) $s .= $tr['type'];
            $s .= $tr['function'] . '</code> '
                . '<i>' . $tr['file'] . '</i> line ' . $tr['line']
                . '</li>';
        }
        $s .= '</ol>';*/
        $s = 'Exception';
        return $s;
    }
    
    public static function renderHtml( $exception, $message = null ) {
        $s = '';
        if ( $message ) {
            $s .= '<pre class="message">' . $message . '</pre>';
        }
        $s .= '<pre class="message">' . $exception->getMessage() . '</pre>'
           . '<p>'
           . '<code>' . $exception->getFile() . '</code>, line ' . $exception->getLine() . '.'
           . '</p>'
           . '<p>Trace :</p>'
           . '<ol>';
        foreach ( $exception->getTrace() as $tr ) {
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
}