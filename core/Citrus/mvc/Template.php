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
 * @package Citrus\mvc
 * @subpackage Citrus\mvc\Template
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;

class Template {
    
    public $vars = array();
    public $name;
    public $pageTitle;
    
    public function __construct( $name ) {
        $this->name = $name;
    }
    
    public function display( $path ) {
        $cos = Citrus::getInstance();
        extract( $this->vars, EXTR_OVERWRITE );
        $tplContent = false;
        if ( file_exists( $path . '/templates/' . $this->name . '.tpl.php' ) ) {
            ob_start();
            include $path . '/templates/' . $this->name . '.tpl.php';
            $tplContent = ob_get_contents();
            ob_get_clean();
        }
        return $tplContent;
    }
    
    public function assign( $var, $val = null ) {
        if ( is_array( $var ) || $var instanceof Traversable ) {
			foreach ( $var as $k => $v ) {
				$this->vars[$k] = $v;
			}
		} else {
			$this->vars[ $var ] = $val;
		}
		return $val;
    }
}