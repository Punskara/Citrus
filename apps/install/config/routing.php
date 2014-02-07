<?php

return array(
    'routes' => array(
        array(
            'url'    => '/', 
            'target' => array( 
                'app' => 'install', 
                'controller' => 'main',
                'action' => 'index',
            ),
        ),
        array(
            'url'    => '/install/main/getconfig.json', 
            'target' => array( 'app'=>'install', 'controller' => 'main', 'action'=>'getconfig' )
        ),
    ),
);