<?php


$this->module               = new core\Citrus\mvc\Module( 'main', $this->path, 'index' );
$this->isSecure 			= false;
$this->titleTag 			= "";
$this->layout               = 'main';
$this->templates 		    = array();
$this->metaTags             = "";