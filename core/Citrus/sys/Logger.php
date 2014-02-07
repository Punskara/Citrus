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
 * @subpackage Citrus\sys\Logger
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\sys;

use \core\Citrus\utils\File;

class Logger {
    private $_dir = '';
    private $_file;
    private $_fileName;
    private $_linePrefix = '';
    public $events;

    public function __construct( $name ) {
        $this->_linePrefix = date( '[Y-m-d H:i:s]' ) . " ";
        $this->_fileName = CITRUS_LOG_PATH . $name . '-' . date( 'Ym') . '.log';
    }
    
    public function logEvent( $str ) {
        $this->events[] = $this->_linePrefix . $str;
    }
    
    public function writeLog() {
        if ( count( $this->events ) ) {
            $this->_file = new File( $this->_fileName, File::MODE_WOE );
            $this->_file->write( implode( "\n", $this->events ) . "\n" );
            $this->_file->close();
        }
    }
}