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
 * @package Citrus
 * @subpackage Citrus\Host
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus;

class Host {
    
    /**
     * @var string
     */
    public $httpHost;
    
    /**
     * @var string
     */
    public $baseUrl;
    
    /**
     * @var boolean
     */
    public $debug;
    
    /**
     * @var boolean
     */
    public $hasRewriteEngine;
    
    /**
     * @var array
     */
    public $services = array();
    
    /**
     * Constructor
     * 
     * @param string  $httpHost  a http host.
     * @param string  $baseUrl  the path from the document root to the citrus index.php main file.
     * @param array   $services  an array of citrus services.
     */
    public function __construct( $httpHost, $baseUrl, $services ) {
        $this->httpHost = $httpHost;
        $this->baseUrl = $baseUrl;
        $this->services = $services;
    }
    
    public function __toString() {
        return $this->httpHost;
    }
}