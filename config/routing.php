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
		),
        array(
            'url'    => '/static/:app/:type/:file:ext', 
            'target' => array( 
                'action' => 'static',
            ),
            'conditions' => Array(
                'type' => "css|js|img|fonts",
                'file' => ".*",
                'ext'  => "\.[A-Za-z0-9]+"
            ),
        )
    ),
    
);