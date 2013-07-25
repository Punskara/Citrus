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
 * @subpackage Citrus\sys\Debug
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\sys;
use \core\Citrus\Citrus;
use \core\Citrus\http;

class Debug {
    
    public $queries = array();
    public $queryString = '';
    public $timers = array();
    public $timer;
    public $request;
    
    static $debug = false;
    
    public function __construct() {
        $this->request = new http\Request();
    }
    
    public function getQueries() {
        return count( $this->queries ) ? $this->queries : false;
    }
    
    public function debugBar() {
        $this->timer->stop();
        $queryString = htmlentities( $this->request->queryString );
        $s = "<div id=\"CitrusDebugBar\">\n";
        
        $s .= "<div class=\"content\">\n";
        $s .= '<a href="#" onclick="$(\'.citrusDebugQString\').slideToggle(300);return false;">Request</a> ';
        $s .= '<a href="#" onclick="$(\'.citrusDebugSQLQueries\').slideToggle(300);return false;">SQL (' . count( $this->queries ) . ')</a> ';
        $s .= '<a href="#" onclick="$(\'.citrusDebugTimer\').slideToggle(300);return false;">Time (' . $this->timer->getExecTime() . ' ms)</a> ';
        $s .= '<a href="#" onclick="$(\'#CitrusDebugBar\').fadeOut();return false;">Close</a> ';
        $s .= "</div>\n";
        
        $s .= "\t<div class=\"citrusDebugQString citrusDebugPane\">\n";
        $s .= "\t\t<ul>\n";
        $s .= "\t\t\t<li>Query string : $queryString</li>\n";
        $s .= "\t\t\t<li>Method : {$this->request->method}</li>\n";
        $s .= "\t\t</ul>\n";
        $s .= "</div>\n";
        if ( count( $this->queries ) ) {
            $s .= "\t\t<div class=\"citrusDebugSQLQueries citrusDebugPane\">\n";
            $s .= "\t\t<ol>\n";
            foreach ( $this->queries as $q ) {
                $s .= "\t\t\t<li>$q</li>";
            }
            $s .= "\t\t</ol>";
            $s .= "\t</div>";
        }
        if ( $this->timers ) {
            $s .= "\t\t<div class=\"citrusDebugTimer citrusDebugPane\">\n";
            $s .= "\t\t<ol>\n";
            foreach ( $this->timers as $timer ) {
                $s .= "<li>$timer->label: ";
                $s .= $timer->getExecTime();
                $s .= " ms</li>";
            }
            $s .= "\t\t</ol>\n";
            $s .= "</div>\n";
        }
        $s .= '<script type="text/javascript">';
        //$s .= "$('#CitrusDebugBar').draggable({ axis: 'y', handle: '.content', containment: 'body' });";
        $s .= '</script>';
        $s .= "</div>\n";
        return $s;
    }
    
    public function startNewTimer( $label ) {
        $this->timers[] = new Timer( $label );
    }
    
    public function stopLastTimer() {
        $timer = end( $this->timers );
        $timer->stop();
    }
    public function stopFirstTimer() {
        $timer = $this->timers[0];
        $timer->stop();
    }
    
    public static function handleException( $exception, $debug = false, $message = null ) {
        #$cos = Citrus::getInstance();
        $debug = self::$debug;
        $response = new http\Response();
        $response->code = '500';
        $response->message = $debug ? strip_tags( $exception->getMessage() ) : 'An error occured.';
        $response->sendHeaders();
        #$cos->logger->logEvent( $exception->getMessage() );
        if ( $debug ) {
            $exceptTpl = file_get_contents( CITRUS_PATH . '/core/Citrus/sys/templates/exception.tpl' );
            $msg = Exception::renderHtml( $exception, $message );
            $exceptTpl = preg_replace( '#\{citrus_exception\}#', $msg, $exceptTpl );
        } else {            
            $exceptTpl = file_get_contents( CITRUS_PATH . '/core/Citrus/sys/templates/exception_lite.tpl' );
        }
        die( $exceptTpl );
    }
    
    public static function handleError( $number, $msg, $file, $line, $context ) {
        $cos = Citrus::getInstance();
        $response = new http\Response();
        $response->code = '500';
        $response->message = $cos->debug ? strip_tags( $msg ) : 'An error occured.';
        $response->sendHeaders();
        $stack = debug_backtrace();
        $logger = new Logger( 'error' );
        $logger->logEvent( $msg . ' ' . $file . ' on line ' . $line );
        $logger->writeLog();
        $err = new Error( $number, $msg, $file, $line, $context, $stack );

        if ( $cos->debug ) {
            $errorTpl = file_get_contents( CITRUS_PATH . '/core/Citrus/sys/templates/error.tpl' );
            $msg = Error::renderHtml( $err );
            $errorTpl = preg_replace( '#\{citrus_error\}#', $msg, $errorTpl );
        } else {
            $errorTpl = file_get_contents( CITRUS_PATH . '/core/Citrus/sys/templates/error_lite.tpl' );
        }
        die( $errorTpl );
    }
    

    /**
     * Shows last error if exists
     */
    public function showErrorIfExists() {
        $err = error_get_last();
        if ( $err != null ) {
            list( $type, $message, $file, $line ) = array_values( $err );
            self::handleError( $type, $message, $file, $line, null );
        }
    }
}