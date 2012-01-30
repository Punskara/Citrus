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
 * @subpackage Citrus\html\form\SelectOne
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus\html\form;

class SelectOne extends FormElement {
	public $options = array();
	
	public function __toString() {
		$attrs = array();
		if ( $this->id ) $attrs[] = 'id="' . $this->id . '"';
		if ( $this->name ) $attrs[] = 'name="' . $this->name . '"';
		$attrs = implode( ' ', $attrs );
		$elt = "<select " . $attrs . ">\n";
		if ( count( $this->options ) ) {
            foreach ( $this->options as $value => $text ) {
                $selected = (string)$this->value == (string)$value ? ' selected="selected"' : '';
                $elt .= "\t<option value=\"$value\"$selected>$text</option>\n";
            }
        } else {
            $elt .= "\t<option value=\"\">" . tr( 'Choose' ) . "</option>\n";
        }
        $elt .= "</select>\n";
        return $elt;
	}
	
	public function makeOptions( $class, $blank = false ) {
	    if ( class_exists( $class ) ) {
            $items = \core\Citrus\data\Model::selectAll( $class );
            $schema = \core\Citrus\data\Model::getSchema( $class );
            if ( $blank ) {
                $this->options[''] = '-- ' . $schema->pluralDescription . ' --';
            }
            foreach ( $items as $item ) {
                $this->options[$item->id] = $item;
            }
        }
	}
	
}

