<?php
/*
 * This file is part of Citrus. 
 *
 * (c) Rémi Cazalet <remi@caramia.fr>
 * Nicolas Mouret <nicolas@caramia.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package Citrus\html
 * @subpackage Citrus\html\Element
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\html;

/**
 * This class manages an html element
 */
class Element {
    /**
     * @var string
     */
    public $tagName = '';
    
    /**
     * @var string
     */
    public $id = '';
    
    /**
     * @var array
     */
    public $classes = array();
    
    /**
     * @var array
     */
    public $attributes = array();
    
    /**
     * @var string
     */
    public $innerHtml = '';
    
    /**
     * @var boolean
     */
    public $inline = false;
    
    /**
     * @var array
     */
    public $children = array();
    
    /**
     * @var boolean
     */
    public $closeTag = false;
    
    /**
     * Constructor
     * Adds an element to the document
     * 
     * @param string  $tagName  name of the html tag
     * @param array  $params  parameters for the tag : attributes, etc…
     */
    public function __construct( $tagName, $params = array() ) {
        $this->tagName = $tagName;
        $this->id = isset( $params['id'] ) ? $params['id'] : '';
        $this->classes = isset( $params['classes'] ) ? $params['classes'] : array();
        $this->attributes = isset( $params['attributes'] ) ? $params['attributes'] : array();
        $this->inline = isset( $params['inline'] ) ? $params['inline'] : false;
        $this->closeTag = isset( $params['closeTag'] ) ? $params['closeTag'] : false;
    }
    
    /**
     * Render the html code for the tag.
     *
     * @return string  $html  the html code
     */
    public function renderHtml() {
        $html = '<' . $this->tagName;
        if ( count( $this->classes ) ) {
            $html .= ' ' . implode( ' ', $this->classes );
        }
        if ( $this->id ) $html .= ' id="' . $this->id . '"';
        if ( count( $this->attributes ) ) {
            foreach ( $this->attributes as $name => $value ) {
                $html .= ' ' . $name . '="' . $value . '"';
            }
        }
        $closeTag = $this->closeTag && $this->inline ? ' /' : '';
        
        $innerHtml = '';
        if ( $this->innerHtml != '' ) $innerHtml .= $this->innerHtml;
        if ( count( $this->children ) ) foreach ( $this->children as $child ) {
            $innerHtml .= $child->renderHtml();
        }
        
        $html .= $closeTag . ">";
        if ( $innerHtml != '' ) $html .= "\r\n";
        $html .= $innerHtml;
        
        if ( !$this->inline ) {
            $html .= '</' . $this->tagName . '>' . "\r\n";
        } else {
            $html .= "\r\n";
        }
        return $html;
    }
    
    /**
     * Adds a class to the tag
     *
     * @param string  $class  the name of the class
     * 
     * @return  \core\Citrus\html\Element  $this  this object.
     */
    public function addClass( $className ) {
        if ( !in_array( $className, $this->classes ) ) {
            $this->classes[] = $className;
        }
        return $this; 
    }
    
    /**
     * Removes one of the classes of the tag
     *
     * @param string  $class  the name of the class
     * 
     * @return  \core\Citrus\html\Element  $this  this object.
     */
    public function removeClass( $className ) {
        $key = array_search( $className, $this->Class );
        if ( $key !== false ) {
            unset( $this->Class[$key] );
        }
        return $this;
    }
    
    /**
     * Adds HTML code into the tag
     *
     * @param string  $html  the html code
     * 
     * @return  \core\Citrus\html\Element  $this  this object.
     */
    public function addHtml( $html ) {
        if ( $html != '' ) {
            $this->innerHtml .= $html . "\r\n";
        }
        return $this;
    }
    
    /**
     * Adds a child to the tag
     *
     * @param string  $tagName  the name of the child tag
     * @param array  $params  parameters for the tag : attributes, inline
     * 
     * @return  \core\Citrus\html\Element  $this  this object.
     */
    public function addChild( $tagName, $params = array() ) {
        
        $elt = new Element( $tagName, $params );
        $this->children[] = $elt;
        // $elt->closeTag = $this->closeTag;
        //$this->addHtml( $elt->renderHtml() );
        return $elt;
    }
    
    /**
     * Adds children to the tag
     *
     * @param array  $children  An array of \core\Citrus\html\Elements
     * 
     * @return  \core\Citrus\html\Element  $this  this object.
     */
    public function addChildren( $children = array() ) {
        $this->children = array_merge( $this->children, $children );
    }
}