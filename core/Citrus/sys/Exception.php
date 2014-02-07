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
        $s = 'Exception';
        return $s;
    }
    
    static public function renderHtml( $exception, $message = null ) {
        $s = '';
        if ( $message ) {
            $s .= '<pre class="message">' . $message . '</pre>';
        }
        $s .= '<pre class="message">' . get_class( $exception ) . ': ' . $exception->getMessage() . '</pre>'
           . '<p>'
           . '<code>' . $exception->getFile() . '</code>, line ' . $exception->getLine() . '.'
           . '</p>'
           . '<p>Trace :</p>'
           . '<ol>';
        foreach ( $exception->getTrace() as $tr ) {
            $s .= '<li><code>';
            if ( isset( $tr['class'] ) ) $s .= $tr['class'];
            if ( isset( $tr['type'] ) )  $s .= $tr['type'];
            $s .= $tr['function'] . '</code> ';

            if ( isset( $tr['file'] ) ) $s .= '<i>' . $tr['file'] . '</i> ';
            if ( isset( $tr['line'] ) ) $s .= 'line ' . $tr['line'];

            $s .= '</li>';
        }
        $s .= '</ol>';
 
        return $s;
    }
}