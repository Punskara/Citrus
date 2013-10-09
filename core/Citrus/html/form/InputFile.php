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
        
        $hidden = '<input type="hidden" value="' . $this->value . '" name="' . $this->name . '" ' . $this->classesString() . ' />';
        $view = '';
        if ($this->value) $view = '<img src="' . CITRUS_PROJECT_URL . 'upload/' . $this->value . '" title="'.$this->value .'"/>';
        
        return "$hidden<div class=\"televersement\"></div>";
    }
}