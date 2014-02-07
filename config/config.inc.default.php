<?php

return $config = array(
    # project configuration
    'site_name' => 'Citrus',
    
    # hosts
    'hosts' => array(
        'dev' => array(
            'domain'    => $_SERVER['HTTP_HOST'],
            'root_path' => dirname( $_SERVER['SCRIPT_NAME'] ),
            'services'  => array(
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
    'default_timezone' => 'Europe/Paris',
);