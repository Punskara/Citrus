<?php

return array(
    'default' => array(
        'module' => 'main',
        'action' => 'index',
    ),
    
    'routes' => array(
		array(
			'url'    => '/getconfig.json', 
			'target' => array( 'app'=>'install', 'controller' => 'main', 'action'=>'getconfig' )
		),
		array(
			'url'    => '/install/main/getconfig.json', 
			'target' => array( 'app'=>'install', 'controller' => 'main', 'action'=>'getconfig' )
		)
    ),
);