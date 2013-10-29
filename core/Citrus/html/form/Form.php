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
        /*foreach ( $classes as $class ) {
            $classes[] = $class;
        }*/
        $classes = implode( ' ', $classes ) . '';
        if ( $elt instanceof InputHidden ) {
            $str = (string)$elt;
        } elseif ( $elt instanceof RichText ) {
            $str = "<div class=\"form-group row clearfix $classes\">\n";
            if ( $elt->label ) 
                $str .= "<label class=\"col-lg-2 col-xs-12 control-label\" for=\"$elt->id\">" . 
                            $elt->label . 
                        ":</label>\n";
            $str .= '<div class="col-lg-10">' . "\n\t";
            $str .= (string)$elt;
            $str .= "\n</div>\n";
            $str .= "\n</div>\n";
        } else {
            $str = "<div class=\"form-group row clearfix $classes\">\n";
            if ( $elt->label ) {
                $str .= "<label class=\"col-lg-2 col-xs-12 control-label\" for=\"$elt->id\">" . 
                            $elt->label . 
                        ":</label>\n";
            }
            $str .= '<div class="col-lg-10 col-xs-12">' . "\n\t";
            $str .= (string)$elt;
            $str .= "\n</div>\n";
            $str .= "\n</div>\n";
        }
        return $str;
    }
    
    public function renderElements( $elements = Array() ) {
        $str = '';
        if ( count( $elements ) > 0 ) {
            foreach ( $elements as $e ) {
                if ( is_array( $e ) ) {
                    $str .= $this->renderElement( $e );
                } else {
                    $str .= $this->renderElement( $e );
                }
            }
        }
        return $str;
    }

    /**
     * Generates an HTML form from the schema of the object
     */
    static public function generateForm( $res ) {
        $form = new Form();
        $props = $res->schema->properties;
        $form->elements['modelType'] = new InputHidden( 
            'modelType', '', 'modelType', array(), $value = $res->schema->className
        );
        foreach ( $props as $propName => $propAttr ) {
            if ( isset( $propAttr['inputType'] ) ) {
                if ( class_exists( '\core\Citrus\html\form\\' . $propAttr['inputType'] ) ) {
                    $element = '\core\Citrus\html\form\\' . $propAttr['inputType'];
                    $classes = ( isset( $propAttr['null'] ) && $propAttr['null'] == false ) ? array( 'required' ) : array();
                    if ( isset( $propAttr['inputFilter'] ) ) $classes[] = $propAttr['inputFilter'];
                    $form->elements[$propName] = new $element( 
                        $propName, 
                        isset( $propAttr['formLabel'] ) ? $propAttr['formLabel'] : '', 
                        $propName,
                        $classes,
                        $res->$propName
                    );
                    if ( $element == '\core\Citrus\html\form\SelectOne' ) {
                        if ( isset( $propAttr['modelType'] ) ) {
                            $form->elements[$propName]->makeOptions( 
                                $propAttr['modelType'],
                                isset( $propAttr['firstBlank'] )
                            );
                        } elseif ( isset( $propAttr['options'] ) ) {
                            $form->elements[$propName]->options = $propAttr['options'];
                        }
                    }
                }
            }
        }
        $manyProps = $res->schema->manyProperties;
        foreach ( $manyProps as $propName => $propAttr ) {
            if ( isset( $propAttr['inputType'] ) ) {
                $inputType = $propAttr['inputType'];
                $element = '\core\Citrus\html\form\\' . $inputType;
            } else {
                $element = '\core\Citrus\html\form\SelectMany';
            }
            if ( isset( $propAttr['inputFilter'] ) ) $classes[] = $propAttr['inputFilter'];
            $form->elements[$propName] = new $element( 
                $propName, 
                isset( $propAttr['formLabel'] ) ? $propAttr['formLabel'] : '', 
                $propName,
                $classes,
                $res->$propName
            );
            $where = false;
            if ( isset( $propAttr['conditions'] ) && is_array( $propAttr['conditions'] ) ) {
                $where = $propAttr['conditions'];
            }
            $form->elements[$propName]->makeOptions( $propAttr['modelType'], true, $where );
        }
        
        return $form;
    }
    
    /**
     * Displays generated HTML form
     */
    public function render() {
        $render = '';
        foreach ( $this->elements as $elt ) {
            $render .= $this->renderElement( $elt->name );
        }
        return $render;
    }
}