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
 * @subpackage Citrus\html\Document
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\html;
/**
 * This class handles an html document : it allows to generate an HTMl document (doctype, children, etc…)
 */
class Document {
    /**
     * @var string
     */
    public $dtd;
    
    /**
     * @var array
     */
    public $elements = array();
    
    /**
     * @var boolean
     */
    public $closeTags = true;

    /**
     * Constructor.
     *
     * @param string  $html  html version (see $htmlAllowed)
     * @param string  $restriction  (strict, transitionnal, etc…)
     */
    public function __construct( $html, $restriction = 'strict' ) {
        $htmlAllowed = array(
            'html4.01', 
            'html5', 
            'xhtml1.0',
            'xhtml1.1',
        );
        $restrictAllowed = array(
            'strict', 'transitionnal', 'frameset'
        );
        if ( $html == 'html4.01' ) {
            $this->closeTags = false;
            if ( $restriction == 'strict' ) {
                $this->dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
            } elseif ( $restriction == 'transitionnal' ) {
                $this->dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
            } elseif ( $restriction == 'frameset' ) {
                $this->dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
            }
        } elseif ( $html == 'html5' ) {
            $this->dtd = '<!DOCTYPE HTML>';
            $this->closeTags = false;
        } elseif ( $html == 'xhtml1.0' ) {
            $this->closeTags = false;
            if ( $restriction == 'strict' ) {
                $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            } elseif ( $restriction == 'transitionnal' ) {
                $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
            } elseif ( $restriction == 'frameset' ) {
                $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
            }
        } elseif ( $html == 'xhtml1.1' ) {
            $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
        }
        $this->dtd .= "\r\n";
    }
    
    
    /**
     * Adds an element to the document
     * 
     * @param string  $tagName  name of the html tag
     * @param array  $params  parameters for the tag : attributes, inline
     * 
     * @see \core\Citrus\html\Element
     * 
     * @return \core\Citrus\html\Element
     */
    public function addElement( $tagName, $params = array() ) {
        $elt = new Element( $tagName, $params );
        $elt->closeTag = $this->closeTags;
        $this->elements[] = $elt;
        return $elt;
    }
    

}