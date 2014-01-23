<?php

return array(
    'routes' => array( 
        array(
            'url'    => '/:app/:type/:file:ext', 
            'target' => array( 
                'action' => 'static',
            ),
            'conditions' => Array(
                'type' => "css|js|img|fonts",
                'file' => ".*",
                'ext'  => "\.[A-Za-z0-9]+"
            ),
        ),
    ),
    
);