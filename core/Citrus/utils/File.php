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
 * @package Citrus\utils
 * @subpackage Citrus\utils\File
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\utils;
use \core\Citrus\sys\Exception;

class File {
    public $path;
    public $resource;
    public $mode;
    public $finfo;
    
    const MODE_ROS = 'r';
    const MODE_ROE = 'r+';
    const MODE_WOS = 'w';
    const MODE_RWS = 'w+';
    const MODE_WOE = 'a';
    const MODE_RWE = 'a+';
    
    public function __construct( $path ) {
        $this->path = $path;
    }
    
    public function open( $mode ) {
        $this->resource = fopen( $this->path, $mode );
    }
    public function write( $str ) {
        if ( $this->resource ) {
            return fwrite( $this->resource, $str );
        } return false;
    }
    
    public function close() {
        if ( $this->resource ) {
            fclose( $this->resource );
        }
    }

    static public function delete( $path ) {
        if ( file_exists( $path ) ) @unlink( $path );
    }

    public function exists() {
        return file_exists( $this->path );
    }

    static public function getType( $path ) {
        if ( file_exists( $path ) ) {
            $finfo = new \finfo( \FILEINFO_MIME );
            return $finfo->file( $path );
        }
        throw new Exception( "Cannot get file type : file not found" );
        
    }

    public function getContent() {
        if ( $this->exists() ) {
            return file_get_contents( $this->path );
        }
        throw new Exception( "Unable to get file content : file not found." );
        return false;
    }

    static public function getSize( $file, $precision = 2 ) {

    }
}