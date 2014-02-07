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
 * @package Citrus
 * @subpackage Citrus\Host
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

namespace core\Citrus;

class Host {
    
    /**
     * @var string
     */
    public $domain;
    
    /**
     * @var string
     */
    public $root_path;
    
    /**
     * @var boolean
     */
    public $debug;
    
    /**
     * @var array
     */
    public $services = array();
    

    /**
     * Constructor
     * 
     * @param string  $domain  a http host.
     * @param string  $root_path  the path from the document root to the citrus index.php main file.
     * @param array   $services  an array of citrus services.
     */
    public function __construct( $domain, $root_path, $services ) {
        $this->domain       = $domain;
        $this->root_path    = $root_path;
        $this->services     = $services;
    }
    
    public function __toString() {
        return $this->domain;
    }

    public function getURL() {
        return CITRUS_SCHEME . $this->domain . $this->root_path;
    }
}