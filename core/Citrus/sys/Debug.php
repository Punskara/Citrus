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
    
    public function __construct( $request ) {
        $this->request = $request;
    }
    
    public function getQueries() {
        return count( $this->queries ) ? $this->queries : false;
    }
    
    public function debugBar() {
        $this->timer->stop();
        $queryString = htmlentities( $this->request->queryString );
        $s = "<div id=\"CitrusDebugBar\">\n";
            $s .= '<a href="#" id="showDebugBar" class="fa fa-bug">&nbsp;</a>' . "\n";
            $s .= "<div class=\"content\">\n";
                $s .= '<a href="#request" id="tamere"><i class="fa fa-exchange"></i>Request</a> ';
                $s .= '<a href="#sql"><i class="fa fa-tasks"></i>SQL (' . count( $this->queries ) . ')</a> ';
                $s .= '<a href="#timer"><i class="fa fa-clock-o"></i>Time (' . $this->timer->getExecTime() . ' ms)</a> ';
                $s .= '<a href="#close"><i class="fa fa-times"></i>Remove</a> ';
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
        return count( $this->timers ) - 1;
    }
    
    public function stopTimer( $id ) {
        if ( isset( $this->timers[$id] ) ) {
            $this->timers[$id]->stop();
        }
    }

    public function stopLastTimer() {
        $timer = end( $this->timers );
        $timer->stop();
    }
    public function stopFirstTimer() {
        $timer = $this->timers[0];
        $timer->stop();
    }
    
    static public function handleException( $exception, $debug = false, $message = null ) {
        $cos = Citrus::getInstance();
        $cos->response->code = '500';
        $cos->response->message = $debug ? strip_tags( $exception->getMessage() ) : 'An error occured.';
        $cos->response->sendHeaders();
        if ( $debug ) {
            $exceptTpl = file_get_contents( CTS_PATH . '/core/Citrus/sys/templates/exception.tpl' );
            $msg = Exception::renderHtml( $exception, $message );
            $exceptTpl = preg_replace( '#\{citrus_exception\}#', $msg, $exceptTpl );
        } else {            
            $exceptTpl = file_get_contents( CTS_PATH . '/core/Citrus/sys/templates/exception_lite.tpl' );
            #$cos->logger->logEvent( $exception->getMessage() );
            $msg = $exception->getMessage();
            error_log( 
                "Exception: '" . $exception->getMessage() . "'" .
                " in " . $exception->getFile() . ', '.
                'line ' . $exception->getLine() 
            );
            $exceptTpl = preg_replace( '#\{citrus_exception\}#', $msg, $exceptTpl );
        }
        die( $exceptTpl );
    }
    
    static public function handleError( $number, $msg, $file, $line, $context ) {
        $cos = Citrus::getInstance();
        $cos->response->code = '500';
        $cos->response->message = $cos->debug ? strip_tags( $msg ) : 'An error occured.';
        $cos->response->sendHeaders();
        $stack = debug_backtrace();
        $cos->getLogger( 'error' )->logEvent( 
            '[error] [client ' . $cos->request->address . '] ' . 
            $msg . ' in ' . $file . ' on line ' . $line 
        );

        $cos->debug ?
            $errorTpl = self::renderErrorHtml( $number, $msg, $file, $line, $context ) :
            $errorTpl = file_get_contents( CTS_PATH . '/core/Citrus/sys/templates/error_lite.tpl' );
        
        if ( $cos->debug ) die( $errorTpl );
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

    static public function renderErrorHtml( $number, $msg, $file, $line, $context, $message = null, $trace = false ) {
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
        $errorTpl = file_get_contents( CTS_PATH . '/core/Citrus/sys/templates/error.tpl' );
        $errorTpl = preg_replace( '#\{citrus_error\}#', $s, $errorTpl );
        return $errorTpl;
    }
}