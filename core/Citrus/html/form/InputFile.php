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
 * @subpackage Citrus\html\form\InputFile
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */




namespace core\Citrus\html\form;

class InputFile extends FormElement {
	public function __toString() {
	    #$this->classes[] = 'text';
	    
		$attrs = array();
		$attrs[] = 'type="file"';
		if ( $this->id ) $attrs[] = 'id="' . $this->id . '"';
		if ( $this->name ) $attrs[] = 'name="' . $this->name . '"';
		$attrs[] = 'value="' . $this->value . '"';
		
		# affiche le thumbnail si le media est une image
		$ext = substr( $this->value, strrpos( $this->value, '.' ) + 1, strlen( $this->value ) );
		$image = '';
		$hidden = '';

		if ( in_array( $ext, \core\Citrus\Media::$ImageExtensions ) ) {
		    try {
                /*$thumb = PhpThumbFactory::create( CITRUS_PATH . $this->value );
                $thumb->cropFromCenter( 200, 100 );*/
                $image = '<img src="' . CITRUS_PROJECT_URL . $this->value . '" style="width:120px;" alt="" />';
                $hidden = '<input type="hidden" value="' . $this->value . '" name="' . $this->name . '"';
                if ( in_array('required', $this->classes ) ) {
                    $hidden .= 'class="required"';
                }
                $hidden .= '/>';
            } catch( \Exception $e ) {
                echo $e->getMessage();
            }
		}
		$attrs = implode( ' ', $attrs );
        
		$classes = $this->classesString();
		return "$image$hidden<input " . $attrs . " " . $classes . " />";
	}
}