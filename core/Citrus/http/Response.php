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
 * @package Citrus\http
 * @subpackage Citrus\http\Response
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;

/**
 * Handles the http header response.
 */
class Response {
    /**
     * @var integer
     */
    public $code = 200;
    
    /**
     * @var string
     */
	public $message = '';
	
	/**
     * @var string
     */
	public $location = null;
	
	/**
     * @var boolean
     */
	public $enableRedirections = true;

    /**
     * @var string
     */
	public $contentType = 'text/html';
	
	/**
     * @var string
     */
	public $contentCharset = 'utf-8';


    /**
     * Sends an HTTP request
     * 
     * @param integer  $code  HTTP response code
     * @param string  $message  Response message
     * @param string  $location  Response location
     * 
     * @static
     */
	static function sent( $code = 200, $message = '', $location = null ) {
		$inst = new self( $code, $message, $location );
		$inst->sendHeaders();
		return $inst;
	}

    /**
     * Constructor
     * 
     * @param integer  $code  HTTP response code
     * @param string  $message  Response message
     * @param string  $location  Response location
     */
	function __construct( $code = 200, $message = '', $location = null ) {
		$this->code = $code;
		$this->message = $message;
		$this->location = $location;
		$this->contentType = 'text/html';

		#$this->enableRedirections = $xos->useRedirections;
		header( 'Content-type: ' . $this->contentType . ( empty( $this->contentCharset ) ? '' : '; charset=' . $this->contentCharset ), true );

		if ( isset( $_REQUEST['cos_redirect'] ) && empty( $_REQUEST['cos_redirect'] ) ) {
			$this->enableRedirections = false;
		}
	}


    /**
     * Sends http headers
     * 
     * @return boolean|integer whether the headers are already sent or not.
     */
	public function sendHeaders() {
		if ( headers_sent() ) {
			return false;
		}
		header( 'Content-type: ' . $this->contentType . ( empty( $this->contentCharset ) ? '' : '; charset=' . $this->contentCharset ), true );

		$this->message = str_replace( array( "\r", "\n" ), '', $this->message );
		if ( empty( $this->location ) ) {
			header( 'HTTP/1.1 ' . $this->code . ' ' . $this->message, true, $this->code );
		    return $this->code;
		}
	    if ( preg_match( "/[\\0-\\31]|about:|script:/i", $this->location ) ) {
			$this->location = '/';
		} elseif ( !strpos( $this->location, '://' ) ) {
		    $this->location = $xos->url( 'www' . $this->location );
		}
		if ( $this->enableRedirections ) {
		    if ( isset( $_SESSION ) ) {
				$_SESSION['response']['redirect_info'] = array(
					'STATUS' => $this->code,
					'ERROR_NOTES' => $this->message,
					'URL' => $_SERVER['REQUEST_URI'],
					'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
				);
		    }
			header( 'HTTP/1.1 ' . $this->code . ' ' . $this->message );
			header( 'Location: ' . $this->location, true, $this->code );
			echo "<body><a href='$this->location'>Page redirected. Status: $this->code, $this->message.</a></body>";
			exit();
		}
		return $this->code;
	}

}