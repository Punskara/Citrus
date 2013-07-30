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
 * @subpackage Citrus\html\form\InputPassword
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus\html\form;

class InputPassword extends FormElement {	
	public function __toString() {
		$attrs = array();
		$attrs[] = 'type="password"';
		if ( $this->id ) $attrs[] = 'id="' . $this->id . '"';
		if ( $this->name ) $attrs[] = 'name="' . $this->name . '"';
		#$attrs[] = 'value="' . $this->value . '"';
		$attrs = implode( ' ', $attrs );
        $this->classes[] = 'form-control';
        $classes = $this->classesString();
        
        return "<input " . $attrs . " " . $classes . " />";
	}
}