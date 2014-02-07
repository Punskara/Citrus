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
 * @package Citrus\sys
 * @subpackage Citrus\sys\Timer
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\sys;

class Timer {
    private $startTime;
    private $endTime;
    public $execTime;
    public $label;
    
    public function __construct( $label ) {
        $this->label = $label;
        $this->startTime = microtime( true );
    }
    
    public function start() {
        $this->startTime = microtime( true );
    }
    
    public function stop() {
        $this->endTime = microtime( true );
        $this->execTime = $this->endTime - $this->startTime;
    }
    
    public function getExecTime() {
        return round( $this->execTime * 1000, 3 );
    }
}