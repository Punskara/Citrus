<?php


$this->module               = new core\Citrus\mvc\Module( 'main', $this->path, 'index' );
$this->isSecure 			= true;
$this->titleTag 			= "";
$this->layout               = 'main';
$this->templates 		    = array();
$this->metaTags             = "";