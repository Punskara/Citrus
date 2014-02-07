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
    private $dir = '';
    private $file;
    private $file_name;
    private $linePrefix = '';
    private $events = Array();

    public function __construct( $name ) {
        $this->linePrefix = date( '[Y-m-d H:i:s]' ) . " ";
        $this->file_name = CTS_LOG_PATH . $name . '-' . date( 'Ym') . '.log';
    }
    
    public function logEvent( $str ) {
        $this->events[] = $this->linePrefix . $str;
        $this->writeLog();
    }
    
    public function writeLog() {
        if ( count( $this->events ) ) {
            $this->file = new File( $this->file_name, File::MODE_WOE );
            $this->file->open( File::MODE_WOE );
            $this->file->write( implode( "\n", $this->events ) . "\n" );
            $this->file->close();
            $this->events = Array();
        }
    }
}