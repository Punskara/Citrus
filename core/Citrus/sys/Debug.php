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
            $s .= '<a href="#" id="showDebugBar" class="icon-bug">&nbsp;</a>' . "\n";
            $s .= "<div class=\"content\">\n";
                $s .= '<a href="#request" id="tamere"><i class="icon-exchange"></i>Request</a> ';
                $s .= '<a href="#sql"><i class="icon-tasks"></i>SQL (' . count( $this->queries ) . ')</a> ';
                $s .= '<a href="#timer"><i class="icon-time"></i>Time (' . $this->timer->getExecTime() . ' ms)</a> ';
                $s .= '<a href="#close"><i class="icon-remove"></i>Remove</a> ';
            $s .= "</div>\n";
        
            $s .= "\t<div id=\"citrusDebugQString\" class=\"citrusDebugPane\">\n";
                $s .= "\t\t<ul>\n";
                $s .= "\t\t\t<li>Query string : $queryString</li>\n";
                $s .= "\t\t\t<li>Method : {$this->request->method}</li>\n";
                $s .= "\t\t</ul>\n";
            $s .= "</div>\n";
        
            $s .= "\t\t<div id=\"citrusDebugSQLQueries\" class=\"citrusDebugPane\">\n";
                if ( count( $this->queries ) ) {
                    $s .= "\t\t<ol>\n";
                    foreach ( $this->queries as $q ) {
                        $s .= "\t\t\t<li><pre>$q</pre></li>";
                    }
                    $s .= "\t\t</ol>";
                }
            $s .= "\t</div>";
            $s .= "\t\t<div id=\"citrusDebugTimer\" class=\"citrusDebugPane\">\n";
                if ( $this->timers ) {
                    $s .= "\t\t<ol>\n";
                    foreach ( $this->timers as $timer ) {
                        $s .= "<li>$timer->label: ";
                        $s .= $timer->getExecTime();
                        $s .= " ms</li>";
                    }
                    $s .= "\t\t</ol>\n";
                }
            $s .= "</div>\n";
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

        // $err = new Error( $number, $msg, $file, $line, $context, $stack );

        if ( $cos->debug ) {

            // $msg = Error::renderHtml( $err );
            $errorTpl = self::renderErrorHtml( $number, $msg, $file, $line, $context );
            
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

    public static function renderErrorHtml( $number, $msg, $file, $line, $context, $message = null, $trace = false ) {
        $s = '';
        if ( $message ) {
            $s .= '<p class="message">' . $message . '</p>';
        }
        $s .= '<p class="message">' . $msg . '</p>'
           . '<p>'
           . '<code>' . $file . '</code>, line ' . $line . '.'
           . '</p>';
        if ( $trace ) {
            $s .= '<p>Trace :</p>';
            $s .= '<ol>';
            foreach ( $err->getTrace() as $tr ) {
                $s .= '<li><code>';
                if ( isset( $tr['class'] ) ) $s .= $tr['class'];
                if ( isset( $tr['type'] ) ) $s .= $tr['type'];
                $s .= $tr['function'] . '</code> '
                    . '<i>' . $tr['file'] . '</i> line ' . $tr['line']
                    . '</li>';
            }
            $s .= '</ol>';
        }
        $errorTpl = file_get_contents( CITRUS_PATH . '/core/Citrus/sys/templates/error.tpl' );
        $errorTpl = preg_replace( '#\{citrus_error\}#', $s, $errorTpl );
        return $errorTpl;
    }
}