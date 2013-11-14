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
    public $contentType = 'text/html';
    
    /**
     * @var string
     */
    public $contentCharset = 'utf-8';

    public $headers = Array();

    /**
     * Sends http headers
     * 
     * @return boolean|integer whether the headers are already sent or not.
     */
    public function sendHeaders() {
        if ( headers_sent() ) {
            return false;
        }

        $this->addHeader( 
            'Content-Type', 
            $this->contentType . 
                ( empty( $this->contentCharset ) ? '' : '; charset=' . $this->contentCharset ) 
        );
        if ( count( $this->headers ) ) 
            foreach ( $this->headers as $k => $v )
                header( $k . ': ' . $v, true );
        
        $this->message = str_replace( array( "\r", "\n" ), '', $this->message );
        header( 'HTTP/1.1 ' . $this->code . ' ' . $this->message, true, $this->code );
        return $this->code;
    } 

    public function addHeader( $name, $value ) {
        $this->headers[$name] = $value;
    }

    public function removeHeader( $name ) {
        if ( isset( $this->headers[$name] ) ) unset( $this->headers[$name] );
    }
}