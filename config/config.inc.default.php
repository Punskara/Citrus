<?php

return $config = array(
    # project configuration
    'siteName' => 'Citrus',
    'projectName' => 'citrus',
    
    # routing defaults
    'defaultApp' => 'install',
    
    # hosts
    'hosts' => array(
        'dev' => array(
            'httpHost'          => $_SERVER['HTTP_HOST'],
            'baseUrl'           => dirname( $_SERVER['SCRIPT_NAME'] ),
            'services'          => array(
                'hasRewriteEngine' => array( 'active' => true ),
                'logger' => array( 'active' => true ),
                'debug'  => array( 'active' => false ),
                'db'     => array( 'active' => false,
                    'connection' => array( 
                        "",
                        "", 
                        "" 
                    ),
                ),
            ),
        ),
    ),
    
    # Citrus locale Configuration
    'cos_Timezone' => 'Europe/Paris',
);