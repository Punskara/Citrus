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
 * @package Citrus\mvc
 * @subpackage Citrus\mvc\View
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use \core\Citrus\html;
use \core\Citrus\sys;


class View {
    
    /**
     * @var array
     */
    public $styleSheets = array();
    
    /**
     * @var array
     */
    public $javascriptFiles = array();
    
    /**
     * @var string
     */
    public $layout = true;

    /**
     * @var string
     */
    public $layout_file;

    /**
     * @var array
     */
    public $addedStyleSheets = array();
    
    /**
     * @var array
     */
    public $addedJavascripts = array();
    
    
    /**
     * @var String
     */
    public $name;
    
    /**
     * @var array
     */
    public $vars = array();

    public $path;

    /**
     * Constructor
     *
     * @param \core\Citrus\mvc\App $app The app which uses the view.
     */
    public function __construct( $name, $path = false ) {
        $this->name = $name;
        $this->path = $path;

        // no layout if XMLHTTPRequest
        $this->layout = !Citrus::getInstance()->request->isXHR;
    }
    
    /** 
     * Sets the stylesheets to be used in the html file
     *
     * @param array $stylesheets The stylesheets to add
     */
    public function setCSS( $styleSheets ) {
        $this->styleSheets = $styleSheets;
    }
    
    /** 
     * Add an extenal stylesheet
     *
     * @param array $stylesheets The stylesheets to add
     */
    public function addCSS( $path ) {
        $this->addedStyleSheets[] = $path;
    }
    

    /**
     * Generates the <link> html tags to add the CSS
     *
     * @return string $html The html tags.
     */
    public function renderCSS( $close_tags = false ) {
        $html = '';
        foreach ( $this->getCSSAsElements() as $e ) {
            $e->closeTag = $close_tags;
            $html .= $e->renderHTML();
        }
        return $html;
    }
    
    /**
     * Generates \core\Citrus\html\Element objects in order to display html <link> tags 
     *
     * @return array $sheets An array containing the Element objects.
     */
    public function getCSSAsElements() {
        $cos = Citrus::getInstance();
        
        $cssFiles = '';
        $sheets = array();
        if ( $this->styleSheets ) {
            foreach ( $this->styleSheets as $s ) {
                $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                if ( $media == 'print' ) $s = substr( $s, 1 );                
                if ( substr( $s, 0, 4 ) == "http" ) $href = $s;             
                else $href = CITRUS_PROJECT_URL . "css/$s";

                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';

                $st = new html\Element( 'link', array(
                    'attributes' => array(
                        'rel' => 'stylesheet' . $rel_less,
                        'media' => $media,
                        'type' => 'text/css',
                        'href' => $href,
                    ), 
                    'inline' => true,
                    'closeTag' => false,
                ) );
                $sheets[] = $st;
            }
        }
        if ( count( $this->addedStyleSheets ) ) {
            foreach ( $this->addedStyleSheets as $s ) {
                $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                if ( $media == 'print' ) $s = substr( $s, 1 );
                if ( substr( $s, 0, 4 ) == "http" ) $href = $s;             
                elseif ( substr( $s, 0, 1 ) === '/' )  $href = $s;
                else $href = CITRUS_PROJECT_URL . "css/$s";
                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';
                $st = new html\Element( 'link', array(
                    'attributes' => array(
                        'rel' => 'stylesheet' . $rel_less,
                        'media' => $media,
                        'type' => 'text/css',
                        'href' => $href,
                    ), 
                    'inline' => true,
                    'closeTag' => false
                ) ); 
                $sheets[] = $st;                
            }
        }
        
        if ( $cos->app && $cos->app->controller ) {
            $src = 'css/' . $cos->app->name . '/modules/' . $cos->app->controller->name . '.css' ;
            if (is_file(CITRUS_WWW_PATH . $src)) {
                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';
                $st = new html\Element( 'link', array(
                    'attributes' => array(
                        'rel' => 'stylesheet' . $rel_less,
                        'media' => $media,
                        'type' => 'text/css',
                        'href' => CITRUS_PROJECT_URL . $src,
                    ), 
                    'inline' => true,
                    'closeTag' => false
                ) );
                $sheets[] = $st;                
            }
        }
        
        return $sheets;
    }
    /** 
     * Sets the javascript files to be used in the html file
     *
     * @param array $javascriptFiles The files to add
     */
    public function setJS( $files ) {
        $this->javascriptFiles = $files;
    }
    
    /** 
     * Add an extenal javascript file
     *
     * @param string  $path  Path to the file.
     */
    public function addJS( $path ) {
        $this->addedJavascripts[] = $path;
    }
    
    
    /**
     * Generates \core\Citrus\html\Element objects in order to display html <script> tags 
     *
     * @return array $elements An array containing the Element objects.
     */
    public function getJSAsElements( $show_type_attr = false ) {
        $cos = Citrus::getInstance();
        $elements = array();
        if ( $this->javascriptFiles ) {
            $files = array();
            foreach ( $this->javascriptFiles as $s ) {
                if ( substr( $s, 0, 4 ) == "http" ) $src = $s;             
                else $src = CITRUS_PROJECT_URL . "js/$s";
                $elt = new html\Element( 'script', array(
                    'attributes' => array(
                        'type' => 'text/javascript',
                        'src' => $src,
                    ), 
                    'inline' => false 
                ) );
                $elements[] = $elt;
            }
        }
        
        if ( count( $this->addedJavascripts ) ) {
            foreach ( $this->addedJavascripts as $s ) {
                if ( substr( $s, 0, 4 ) == "http" ) $src = $s;             
                elseif ( substr( $s, 0, 1 ) === '/' )  $src = $s;
                else $src = CITRUS_PROJECT_URL . "js/$s";
                $elt = new html\Element( 'script', array(
                    'attributes' => array(
                        'type' => 'text/javascript',
                        'src' => $src,
                    ), 
                    'inline' => false,
                ) );
                $elements[] = $elt;
            }
        }
        
        if ( $cos->app && $cos->app->controller ) {
            $src = 'js/' . $cos->app->name . '/modules/' . $cos->app->controller->name . '.js' ;
            if (is_file(CITRUS_WWW_PATH . $src)) {
                $elt = new html\Element( 'script', array(
                    'attributes' => array(
                        'type' => 'text/javascript',
                        'src' => CITRUS_PROJECT_URL . $src,
                    ), 
                    'inline' => false,
                ) );
                $elements[] = $elt;
            }
        }
        
        
        return $elements;
    }
    
    /**
     * Generates the <script> html tags to add JS files
     *
     * @return string $html The html tags.
     */
    public function renderJS( $show_type_attr = false ) {
        $html = '';
        foreach ( $this->getJSAsElements() as $e ) {
            unset( $e->attributes['type'] );
            $html .= $e->renderHTML();
        }
        return $html;
    }
    
    /**
     * Displays the main layout of the template
     *
     * @throws \Exception if the main template file is not found.
     */
    public function render() {
        $cos = Citrus::getInstance();
        $content = "";
        extract( $this->vars, EXTR_OVERWRITE );
        if ( $this->layout && is_file( $this->layout_file ) ) {
            ob_start();
            include_once $this->layout_file;
            $content = ob_get_contents();
            ob_get_clean();
        } else $content = $this->getContent();
        return $content;
    }

    public function getContent() {
        $cos = Citrus::getInstance();
        $tplContent = false;
        extract( $this->vars, EXTR_OVERWRITE );
        $default_template = CITRUS_APPS_PATH . $cos->app->name . '/templates/' . $this->name . '.tpl.php';
        $action_template = $cos->getController()->path . '/templates/' . $this->name . '.tpl.php';

        if ( $this->path )
            $action_template = $this->path . $this->name . '.tpl.php';
        
        if ( file_exists( $action_template ) )
            $template = $action_template;
        else if ( file_exists( $default_template ) ) 
            $template = $default_template;
        else {
            throw new sys\Exception( "Unable to find template « $this->name »." );
            return;
        }

        ob_start();
        include $template;
        $tplContent = ob_get_contents();
        ob_get_clean();
    
        return $tplContent;
    }
    
    public function assign( $var, $val = null ) {
        if ( is_array( $var ) || $var instanceof Traversable ) {
            foreach ( $var as $k => $v ) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[ $var ] = $val;
        }
        return $val;
    }

    public function getAppConfig( $app ) {
        $config_path = CITRUS_APPS_PATH . $app->name . '/config/view.php';
        if ( file_exists( $config_path ) ) require_once $config_path;
    }
}
