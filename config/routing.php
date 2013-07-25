<?php

return array(
    'default' => array(
        'app' => 'install',
        'controller' => 'main',
        'action' => 'index',
    ),
    'routes' => array( 
		array(
			'url'    => '/', 
        	'target' => array( 'controller' => 'main' ) 
		)
    ),
    
);