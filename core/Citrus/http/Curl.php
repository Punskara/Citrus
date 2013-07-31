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
 * @subpackage Citrus\http\Curl
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;

class Curl {
    /**
     * @var array : POST data to send
     **/
    protected $_post;

    /**
     * @var array cURL options
     **/
    protected $_options;

    /**
     * La ressource cURL
     **/
    protected $_ch;

    /**
     * Constructor
     * @param  string  URL to send the request to.
     * @throws \Exception if cURL is not activated
     **/
    public function __construct( $url ) {
        if ( !extension_loaded( 'curl' ) ) {
            throw new \Exception( "cURL extension is not available." );
        }
        $this->_ch = curl_init( $url );
        $this->_options = array();
    }

    public function __get( $name ) {
        $result = NULL;
        if ( defined( $name ) ) {
            $val = constant( $name );
            if ( isset( $this->_options[$val] ) ) {
                $result = $this->_options[$val];
            }
        }
        return $resultat;
    }

    public function __set( $name, $val ) {
        if ( defined( $name ) && preg_match( '/^CURLOPT_(?!POSTFIELDS)/', $name ) ) {
            $this->_options[constant( $name )] = $val;
        } else {
            throw new \Exception( "'$name' option invalid or protected" );
        }
    }

    public function __isset( $name ) {
        return ( defined( $name ) && isset( $this->_options[constant( $name )] ) );
    }

    public function __unset( $name ) {
        if ( defined( $name ) && isset( $this->_options[constant( $name )] ) ) {
            unset( $this->_options[constant( $name )] );
        }
    }

    public function __toString() {
        return sprintf( "%s (%s)", __CLASS__, curl_getinfo( $this->_ch, CURLINFO_EFFECTIVE_URL ) );
    }

    /**
     * Sets the timeout duration
     * @param timestamp  $timeout  timeout diration
     **/
    public function setTimeout( $timeout )
    {
        $timeout = intval( $timeout );
        if ( $timeout > 0 ) {
            $this->CURLOPT_TIMEOUT = $timeout;
            $this->CURLOPT_CONNECTTIMEOUT = $timeout;
        }
    }

    /**
     * Adds data to send by POST
     * @param string  $fieldName  name of the field
     * @param mixed  value  value of the field
     * @return boolean
     **/
    public function addPostData( $fieldName, $val ) {
        if ( !isset( $this->_post[$fieldName] ) && !is_array( $val ) ) {
            $this->_post[$fieldName] = $val;
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * Sets the post data to an empty array
     */
    public function clearPostData() {
        $this->_post = array();
    }

    /**
     * Adds a file to send by POST
     * @param string  fieldName  name of the field
     * @param string  file  The file to send
     * @throws \Exception if the file doesn't exist or is not regular
     **/
    public function addPostFile( $fieldName, $file ) {
        if ( is_file( $file ) ) {
            $this->_post[$fieldName] = '@' . realpath( $file );
        } else {
            throw new \Exception( "file '$file' does'nt exist or isn't a regular file" );
        }
    }

    /**
     * Executes the request
     * @param string  $outFile  the content of the result
     * @return string|boolean  the content of remote page or true, if $outFile is set.
     * @throws \Exception if there is a cURL or output error
     **/
    public function doRequest( $outFile = FALSE ) {
        if ( $this->_options ) {
            if ( function_exists( 'curl_setopt_array' ) ) {
                curl_setopt_array( $this->_ch, $this->_options );
            } else {
                foreach ( $this->_options as $option => $val ) {
                    curl_setopt( $this->_ch, $option, $val );
                }
            }
        }
        if ( $outFile ) {
            @ $fp = fopen( $outFile, 'w' );
            if ( !$fp ) {
                throw new \Exception( "Unable to open file '$outFile' for writing." );
            }
            curl_setopt( $this->_ch, CURLOPT_FILE, $fp );
        } else {
            curl_setopt( $this->_ch, CURLOPT_RETURNTRANSFER, 1 );
        }
        if ( $this->_post ) {
            curl_setopt( $this->_ch, CURLOPT_POST, 1 );
            curl_setopt( $this->_ch, CURLOPT_POSTFIELDS, $this->_post );
        }
        $ret = curl_exec( $this->_ch );
        if ( $outFile ) {
            fclose( $fp );
        }
        if ( $ret === FALSE ) {
            throw new CurlExecFailedException( "Error: '" . curl_error( $this->_ch ) . "'" );
        }
        return $ret;
    }

    /**
     * Destructor
     **/
    public function __destruct() {
        unset( $this->_options );
        unset( $this->_post );
        curl_close( $this->_ch );
    }

    public function printDebug() {
        //print_r( curl_getinfo( $this->_ch ) );
        foreach ( curl_getinfo( $this->_ch ) as $k => $v ) {
            echo $k . ': ' . $v . '<br/>';
        }
        if ( curl_errno( $this->_ch ) != 0 ) {
            echo 'Error #' . curl_errno( $this->_ch ) . ': ' . curl_error( $this->_ch );
        }
    }
}
