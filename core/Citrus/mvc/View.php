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
    private $styleSheets = array();
    
    /**
     * @var array
     */
    private $javascriptFiles = array();
    
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
    private $addedStyleSheets = array();
    
    /**
     * @var array
     */
    private $addedJavascripts = array();
    
    /**
     * @var String
     */
    private $tpl_file;
    
    /**
     * @var array
     */
    private $vars = array();

    public $static_path = CTS_PROJECT_URL;

    const TPL_EXT = ".tpl.php";

    /**
     * Constructor
     *
     * @param \core\Citrus\mvc\App $app The app which uses the view.
     */
    public function __construct( $tpl_name ) {
        $cos = Citrus::getInstance();

        $this->tpl_file = $cos->app->tpl_dir . $tpl_name . self::TPL_EXT;

        // automaticly disabling layout if XMLHTTPRequest
        $this->layout = !$cos->request->isXHR;
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
        if ( $this->styleSheets ) {
            foreach ( $this->styleSheets as $s ) {
                $media = substr( $s, 0, 1 ) == '@' ? 'print' : 'screen';
                if ( $media == 'print' ) $s = substr( $s, 1 );

                $is_absolute = substr( $s, 0, 1 ) == "/";
                $is_remote = substr( $s, 0, 4 ) == "http";

                if ( $is_absolute || $is_remote ) $href = $s;
                else $href = $this->static_path . "css/$s";

                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';

                $st = new Element( 'link', array(
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
                else $href = CTS_PROJECT_URL . "css/$s";
                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';
                $st = new Element( 'link', array(
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
            if (is_file(CTS_WWW_PATH . $src)) {
                $rel_less = strpos( $s, '.less' ) !== false ? '/less' : '';
                $st = new Element( 'link', array(
                    'attributes' => array(
                        'rel' => 'stylesheet' . $rel_less,
                        'media' => $media,
                        'type' => 'text/css',
                        'href' => CTS_PROJECT_URL . $src,
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
                $is_absolute = substr( $s, 0, 1 ) == "/";
                $is_remote = substr( $s, 0, 4 ) == "http";
                if ( $is_absolute || $is_remote ) $src = $s;
                else $src = $this->static_path . "js/$s";
                $elt = new Element( 'script', array(
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
                $is_absolute = substr( $s, 0, 1 ) == "/";
                $is_remote = substr( $s, 0, 4 ) == "http";
                if ( $is_absolute || $is_remote ) $src = $s;
                else $src = $this->static_path . "js/$s";
                $elt = new Element( 'script', array(
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
            if (is_file(CTS_WWW_PATH . $src)) {
                $elt = new Element( 'script', array(
                    'attributes' => array(
                        'type' => 'text/javascript',
                        'src' => CTS_PROJECT_URL . $src,
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

        if ( !file_exists( $this->tpl_file ) )
            $this->tpl_file = CTS_APPS_PATH . $cos->app->name . 
                              '/templates/' . basename( $this->tpl_file );
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
}
