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
 * @package Citrus\html\form
 * @subpackage Citrus\html\form\FormElement
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus\html\form;
use \core\Citrus\html;

class FormElement extends html\Element {
    
    public $name = '';
	public $value = '';
	public $label = '';
	
	public $classes = array();
	
	public function __construct( $name, $label, $id, $classes = array(), $value = '' ) {
	    $this->name = $name;
	    $this->label = $label;
	    $this->id = $id;
	    $this->value = $value;
	    $this->classes = $classes;
	}
	
	public function classesString() {
	    $classes = '';
	    if ( count( $this->classes ) ) {
		    $classes = array();
		    foreach ( $this->classes as $class ) {
		        $classes[] = $class;
		    }
		    $classes = 'class="' . implode( ' ', $classes ) . '"';
		}
		return $classes;
	}
}