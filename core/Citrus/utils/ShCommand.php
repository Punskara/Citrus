<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0.2                                                            |
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
 * @subpackage Citrus\utils\ShCommand
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\utils;

class ShCommand {
    public $bin = '/usr/bin/php';
    public $file;
    public $options;
    public $argv;
    public $target;
    public $isBackground = true;
    private $_pipe = array();
    
    public function __construct( $file, $options, $args='', $target = '/dev/null', $background = true ) {
        #$this->bin = $_SERVER['PATH'];
        $this->args = $args;
        $this->file = $file;
        if ( isset( $target ) ) {
            $this->target = $target;
        }
        if ( $background === false ) {
            $this->isBackground = false;
        }
    }
    
    public function execute() {
        if ( !$this->file || $this->file == '' ) {
            throw new \Exception( 'You must specify a file to execute.' );
        }
        if ( !is_file( $this->file ) ) {
            throw new \Exception( 'File does not exist.' );
        }
        $bg = $this->isBackground ? ' &' : '';
        $pipe = count( $this->_pipe ) > 0 ? '|' . implode( '|', $this->_pipe ) : '';
        $com = $this->bin . ' ' . $this->options . ' ' . $this->file . ' ' . $this->args . ' '. $pipe . ' > ' . $this->target . $bg;
        $res = exec( $com , $result );
        return $res;
    }
    
    public function pipe($str) {
    	$this->_pipe[] = $str;
    }
}