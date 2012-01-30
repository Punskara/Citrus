<?php

return array(
    'default' => array(
        'app' => 'install',
        'module' => 'main',
        'action' => 'index',
    ),
    'routes' => array( 
		array(
			'url'    => '/', 
        	'target' => array( 'controller' => 'main' ) 
		)
    ),
    
);