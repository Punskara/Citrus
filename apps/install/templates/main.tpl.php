<?php 
use core\Citrus\html as html;

$doc = new html\Document( 'html5' );

// doctype
echo $doc->dtd;

// <html> tag
$html = $doc->addElement( 'html' );

// head
$head = $html->addChild( 'head' );

// meta data
$head->addChild( 'meta', array(
        'attributes' => array(
            'http-equiv' => 'Content-Type',
            'content' => 'text/html; charset=utf-8'
        ),
        'inline' => true,
    ) );

if ( $cos->app->ctrl->metadesc ) { 
    $head->addChild( 'meta', '', array(
        'attributes' => array(
            'name' => 'description',
            'content' => $cos->app->ctrl->metadesc,
        ),
        'inline' => true,
    ) );
} 
if ( $cos->app->ctrl->metakey ) {    
    $head->addChild( 'meta', array(
        'classes' => array(),
        'attributes' => array(
            'name' => 'keywords',
            'content' => $cos->app->ctrl->metakey,
        ),
        'inline' => true,
        'closeTag' => false,
    ) );
}

// page title
$title = $head->addChild( 'title' );
$siteName = '';
if ( $cos->app->ctrl->pageTitle ) $siteName .= $cos->app->ctrl->pageTitle . ' - ';
$siteName .= $cos->app->titleTag;
$title->addHtml( $siteName );


// CSS & JS
$head->addChildren( $this->getStylesheetsAsElements() );
$head->addChildren( $this->getJavascriptAsElements() );

$scpt1 = $head->addChild( 'script', array(
    'attributes' => array(
        'type' => 'text/javascript',
    ),
    'inline' => false,
) );
$scpt1->addHtml( "var rootUrl = '" . $cos->host->baseUrl . "';" );

// body
$body = $html->addChild( 'body' );

// main content
$body->addHtml( $this->displayTemplate() );

// debug
if ( $cos->debug ) {
    $body->addHtml( $cos->debug->debugBar() );
}


// rendering
echo $html->renderHtml();