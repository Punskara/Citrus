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
 * @subpackage Citrus\html\form\InputDateTime
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus\html\form;

class InputDateTime extends FormElement {
	
	public function __toString() {
	    $this->classes[] = 'date';
	    
		$attrs = array();
		$attrs[] = 'type="text"';
		if ( $this->id ) $attrs[] = 'id="' . $this->id . '"';
		if ( $this->name ) $attrs[] = 'name="' . $this->name . '"';
		$attrs[] = 'value="' . $this->value . '"';
		$attrs = implode( ' ', $attrs );
		
		$classes = $this->classesString();
		
		$s =    '<input ' . $attrs . ' ' . $classes . ' />' . 
		        '<label for="' . $this->id . '" class="labelDate">...</label> ' .
		        'à <select name="' . $this->name . '_h">';
        for ( $i = 0; $i < 24; $i++ ) {
            $h = ( $i < 10 ) ? '0' . $i : $i;
            $selected = '';
            if ( $this->value ) {
                if ( $this->value->format( 'H' ) == $h ) {
                    $selected = ' selected="selected"';
                }
            }
            $s .= '<option value="' . $h . '"' . $selected . '>' . $h . '</option>';
        }
		$s .= '</select> : ';
		$s .= '<select name="' . $this->name . '_m">';
		for ( $i = 0; $i < 60; $i++ ) {
            $h = ( $i < 10 ) ? '0' . $i : $i;
            $selected = '';
            if ( $this->value ) {
                if ( $this->value->format( 'H' ) == $h ) {
                    $selected = ' selected="selected"';
                }
            }
            $s .= '<option value="' . $h . '"' . $selected . '>' . $h . '</option>';
        }
		$s .= '</select>';
		return $s;
	}
}