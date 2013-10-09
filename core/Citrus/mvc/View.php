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
    public $layout;

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

    /**
     * Constructor
     *
     * @param \core\Citrus\mvc\App $app The app which uses the view.
     */
    public function __construct( $name ) {
        $this->name = $name;
        $this->layout_file = "main";
    }
    
    /** 
     * Sets the stylesheets to be used in the html file
     *
     * @param array $stylesheets The stylesheets to add
     */
    public function setStyleSheets( $styleSheets ) {
        $this->styleSheets = $styleSheets;
    }
    
    /** 
     * Add an extenal stylesheet
     *
     * @param array $stylesheets The stylesheets to add
     */
    public function addStyleSheet( $path ) {
        $this->addedStyleSheets[] = $path;
    }
    
    
    /**
     * Generates the <link> html tags to call the stylesheets in the html document
     *
     * @return string $cssFiles The html tags.
     * @deprecated To be replaced by getStylesheetsAsElements
     */
    public function displayStyleSheets() {
        $cos = Citrus::getInstance();
        
        $sheets = array();
        if ( $this->styleSheets ) {
            $sheets = array();
            foreach ( $this->styleSheets as $s ) {
                $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                if ( $media == 'print' ) $s = substr( $s, 1 );                
                if ( substr( $s, 0, 7 ) == "http://" ) $href = $s;             
                else $href = CITRUS_PROJECT_URL . "css/$s";
                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';
                $sheets[] = '<link rel="stylesheet' . $rel_less . '" media="screen" type="text/css" href="' . $href . '" />';
            }
            if ( count( $this->addedStyleSheets ) ) {
                foreach ( $this->addedStyleSheets as $s ) {
                    $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                    if ( $media == 'print' ) $s = substr( $s, 1 );
                    if ( substr( $s, 0, 7 ) == "http://" ) $href = $s;             
                    elseif ( substr( $s, 0, 1 ) === '/' )  $href = $s;
                    else $href = CITRUS_PROJECT_URL . "css/$s";
                    $css_type = strpos( $s, '.less' ) !== false ? 'text/less' : 'text/css';
                    $sheets[] = '<link rel="stylesheet" media="screen" type="' . $css_type . '" href="' . $href . '" />';
                }
            }
        }
        if ( $cos->app && $cos->app->controller ) {
            $src = 'css/' . $cos->app->name . '/modules/' . $cos->app->controller->name ;
            if ( is_file( CITRUS_WWW_PATH . $src . '.css') ) 
                $sheets[] = '<link rel="stylesheet" media="screen" type="text/css" href="' . 
                            CITRUS_PROJECT_URL . $src . 
                            '.css" />';
            if (is_file(CITRUS_WWW_PATH . $src . '.less')) 
                $sheets[] = '<link rel="stylesheet/less" media="screen" type="text/css" href="' . 
                            CITRUS_PROJECT_URL . $src . 
                            '.less" />';
        }
        
        return implode( "\n\t", $sheets ) . "\n";
    }
    
    /**
     * Generates \core\Citrus\html\Element objects in order to display html <link> tags 
     *
     * @return array $sheets An array containing the Element objects.
     */
    public function getStylesheetsAsElements() {
        $cos = Citrus::getInstance();
        
        $cssFiles = '';
        $sheets = array();
        if ( $this->styleSheets ) {
            foreach ( $this->styleSheets as $s ) {
                $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                if ( $media == 'print' ) $s = substr( $s, 1 );                
                if ( substr( $s, 0, 7 ) == "http://" ) $href = $s;             
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
                if ( substr( $s, 0, 7 ) == "http://" ) $href = $s;             
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
    public function setJavascriptFiles( $files ) {
        $this->javascriptFiles = $files;
    }
    
    /** 
     * Add an extenal javascript file
     *
     * @param string  $path  Path to the file.
     */
    public function addJavascript( $path ) {
        $this->addedJavascripts[] = $path;
    }
    
    
    /**
     * Generates \core\Citrus\html\Element objects in order to display html <script> tags 
     *
     * @return array $elements An array containing the Element objects.
     */
    public function getJavascriptAsElements() {
        $cos = Citrus::getInstance();
        $elements = array();
        if ( $this->javascriptFiles ) {
            $files = array();
            foreach ( $this->javascriptFiles as $s ) {
                if ( substr( $s, 0, 7 ) == "http://" ) $src = $s;             
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
                if ( substr( $s, 0, 7 ) == "http://" ) $src = $s;             
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
     * Generates the <script> html tags to call the stylesheets in the html document
     *
     * @return string $jsFiles The html tags.
     * @deprecated To be replaced by getJavascriptAsElements
     */
    public function displayJavascriptFiles() {
        $cos = Citrus::getInstance();
        $files = array();
        if ( $this->javascriptFiles ) {
            $files = array();
            foreach ( $this->javascriptFiles as $s ) {
                if ( substr( $s, 0, 7 ) == "http://" ) $src = $s;             
                else $src = CITRUS_PROJECT_URL . "js/$s";
                $files[] = '<script type="text/javascript" src="' . $src . '"></script>';
            }
            if ( count( $this->addedJavascripts ) ) {
                foreach ( $this->addedJavascripts as $s ) {
                    if ( substr( $s, 0, 7 ) == "http://" ) $src = $s;             
                    elseif ( substr( $s, 0, 1 ) === '/' )  $src = $s;
                    else $src = CITRUS_PROJECT_URL . "js/$s";
                    $files[] = '<script type="text/javascript" src="' . $src . '"></script>';
                }
            }
        }
        if ( $cos->app && $cos->app->controller ) {
            $src = 'js/' . $cos->app->name . '/modules/' . $cos->app->controller->name . '.js' ;
            if ( is_file( CITRUS_WWW_PATH . $src ) ) 
                $files[] = '<script type="text/javascript" src="' . CITRUS_PROJECT_URL . $src . '"></script>';
        }
        $jsFiles = implode( "\n\t", $files ) . "\n";
        return $jsFiles;
    }
    
    /**
     * Displays the main layout of the template
     *
     * @throws \Exception if the main template file is not found.
     */
    public function display() {
        $cos = Citrus::getInstance();
        $content = "";
        $layout_path = CITRUS_APPS_PATH . $cos->app->name . '/templates/' . $this->layout_file . '.tpl.php';

        extract( $this->vars, EXTR_OVERWRITE );
        if ( $this->layout && is_file( $layout_path ) ) {
            $config_path = CITRUS_APPS_PATH . $cos->app->name . '/config/view.php';
            if ( file_exists( $config_path ) )
                require_once $config_path;

            ob_start();
            include_once $layout_path;
            $content = ob_get_contents();
            ob_get_clean();
        } else $content = $this->getSubview();
        return $content;
    }

    public function getSubview() {
        $cos = Citrus::getInstance();
        $tplContent = false;
        extract( $this->vars, EXTR_OVERWRITE );
        $default_template = CITRUS_APPS_PATH . $cos->app->name . '/templates/' . $this->name . '.tpl.php';
        $action_template = $cos->getController()->path . '/templates/' . $this->name . '.tpl.php';

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
}
