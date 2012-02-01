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

class File {
    public $resource;
    public $mode;
    
    const MODE_ROS = 'r';
    const MODE_ROE = 'r+';
    const MODE_WOS = 'w';
    const MODE_RWS = 'w+';
    const MODE_WOE = 'a';
    const MODE_RWE = 'a+';
    
    public function __construct( $resource, $mode ) {
        $this->resource = $this->open( $resource, $mode );
    }
    
    public function open( $resource, $mode ) {
        return fopen( $resource, $mode );
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
}