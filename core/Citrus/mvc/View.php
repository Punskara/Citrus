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
 * @package Citrus\mvc
 * @subpackage Citrus\mvc\View
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use \core\Citrus\html\Element;
use \core\Citrus\sys\Exception;


class View {
    
    /**
     * @var array
     */
    private $stylesheets = array();
    
    /**
     * @var array
     */
    private $javascripts = array();
    
    /**
     * @var string
     */
    public $layout = true;

    /**
     * @var string
     */
    public $layout_file;
    
    /**
     * @var String
     */
    private $tpl_file;
    
    /**
     * @var array
     */
    private $vars = array();


    /**
     * @var strong
     */
    public $static_path = CTS_PROJECT_URL;

    const TPL_EXT = ".tpl.php";

    /**
     * Constructor
     *
     * @param String $tpl_name Absolute path to template file (without extension)
     */
    public function __construct( $tpl_name, $layout = true ) {
        $this->tpl_file = $tpl_name . self::TPL_EXT;
        $this->layout = $layout;
    }
    
    /** 
     * Sets the stylesheets to be used in the html file
     *
     * @param array $stylesheets The stylesheets to add
     */
    public function setCSS( $stylesheets ) {
        $this->stylesheets = $stylesheets;
    }
    
    /** 
     * Add an extenal stylesheet
     *
     * @param array $stylesheets The stylesheets to add
     */
    public function addCSS( $path ) {
        $this->stylesheets[] = $path;
    }
    

    /**
     * Generates the <link> html tags to add the CSS
     *
     * @return string $html The html tags.
     */
    public function renderCSS( $close_tags = false ) {
        $html = '';
        $files = $this->getCSSAsElements();
        foreach ( $files as $e ) {
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
        if ( $this->stylesheets ) {
            foreach ( $this->stylesheets as $s ) {
                $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                if ( $media == 'print' ) $s = substr( $s, 1 );

                $is_absolute = substr( $s, 0, 1 ) == "/";
                $is_remote = substr( $s, 0, 4 ) == "http";

                #if ( $is_absolute || $is_remote ) $href = $s;
                #else $href = $this->static_path . "css/$s";
                $href = $s;
                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';

                $st = new Element( 'link', array(
                    'attributes' => array(
                        'rel'   => 'stylesheet' . $rel_less,
                        'media' => $media,
                        'type'  => 'text/css',
                        'href'  => $href,
                    ), 
                    'inline' => true,
                    'closeTag' => false,
                ) );
                $sheets[] = $st;
            }
        }
        return $sheets;
    }
    /** 
     * Sets the javascript files to be used in the html file
     *
     * @param array $files The files to add
     */
    public function setJS( $files ) {
        $this->javascripts = $files;
    }
    
    /** 
     * Add an extenal javascript file
     *
     * @param string  $path  Path to the file.
     */
    public function addJS( $path ) {
        $this->javascripts[] = $path;
    }
    
    
    /**
     * Generates \core\Citrus\html\Element objects in order to display html <script> tags 
     *
     * @return array $elements An array containing the Element objects.
     */
    public function getJSAsElements( $show_type_attr = false ) {
        $cos = Citrus::getInstance();
        $elements = array();
        if ( $this->javascripts ) {
            $files = array();
            foreach ( $this->javascripts as $s ) {
                $is_absolute = substr( $s, 0, 1 ) == "/";
                $is_remote = substr( $s, 0, 4 ) == "http";
                if ( $is_absolute || $is_remote ) $src = $s;
                else $src = $this->static_path . "js/$s";
                $elt = new Element( 'script', array(
                    'attributes' => array(
                        'type'  => 'text/javascript',
                        'src'   => $src,
                    ), 
                    'inline' => false 
                ) );
                $elements[] = $elt;
            }
        }
        
        if ( $cos->app && $cos->app->getController() ) {
            $src =  'js/' . $cos->app->name . '/modules/' . 
                    $cos->app->getController()->name . '.js' ;
                    
            if ( is_file( CTS_WWW_PATH . $src ) ) {
                $elt = new Element( 'script', array(
                    'attributes' => array(
                        'type'  => 'text/javascript',
                        'src'   => CTS_PROJECT_URL . $src,
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
        $files = $this->getJSAsElements();
        foreach ( $files as $e ) {
            unset( $e->attributes['type'] );
            $html .= $e->renderHTML();
        }
        return $html;
    }
    
    /**
     * returns the template content
     *
     * @throws \Exception if the template file is not found.
     */
    public function render() {
        $cos = Citrus::getInstance();
        if ( !file_exists( $this->tpl_file ) ) {
            throw new Exception( "Template file not found: $this->tpl_file." );
            return;
        }
        $tplContent = false;
        extract( $this->vars, EXTR_OVERWRITE );
        ob_start();
        include $this->tpl_file;
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
        $config_path = CTS_APPS_PATH . $app->name . '/config/view.php';
        if ( file_exists( $config_path ) ) require_once $config_path;
    }

    static public function partial( $partial, $vars = null ) {
        $cos = Citrus::getInstance();
        if ( $cos->debug ) $cos->debug->startNewTimer( "partial " . $partial );

        if ( is_array( $vars ) ) extract( $vars, EXTR_OVERWRITE );
        $file = $cos->app->getTplDir() . $partial . self::TPL_EXT;
        if ( file_exists( $file ) ) include $file;

        if ( $cos->debug ) $cos->debug->stopLastTimer();
    }
}
