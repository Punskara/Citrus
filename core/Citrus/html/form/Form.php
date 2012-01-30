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
 * @subpackage Citrus\html\form\Form
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus\html\form;

class Form {

    public $action;
    public $method;
    
    public $elements = array();
    public $html;
    
    public function addElements( $array ) {
        foreach ( $array as $name => $elt ) {
            if ( $elt ) {
                $elt->form = $this;
                $this->elements[ $name ] = $elt;
            }
        }
    }

    public function renderElement( $name, $classes = array() ) {
        if ( !isset( $this->elements[$name] ) ) return '';
        $elt = $this->elements[$name];
        
        foreach ( $classes as $class ) {
            $classes[] = $class;
        }
        $classes = implode( ' ', $classes ) . '';
        if ( $elt instanceof InputHidden ) {
            $str = (string)$elt;
        } elseif ( $elt instanceof RichText ) {
            $str = "<div class=\"formRow $classes\">\n";
            if ( $elt->label ) $str .= "<label for=\"$elt->id\">" . tr( $elt->label ) . ":</label>\n";
            $str .= (string)$elt . "\n</div>\n";
        } else {
            $str = "<div class=\"formRow\">\n";
            if ( $elt->label ) $str .= "<label class=\"inline\" for=\"$elt->id\">" . tr( $elt->label ) . ":</label>\n";
            $str .= (string)$elt . "\n</div>\n";
        }
        return $str;
    }
}