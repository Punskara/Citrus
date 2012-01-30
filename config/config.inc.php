<?php
return $config = array (
  'siteName' => 'Citrus',
  'projectName' => 'citrus',
  'defaultApp' => 'install',
  'db' => true,
  'db_doctrine' => false,
  'hosts' => 
  array (
    0 => 
    array (
      'httpHost' => 'dev.citrus.local',
      'baseUrl' => '/',
      'services' => 
      array (
        'hasRewriteEngine' => 
        array (
          'active' => true,
        ),
        'logger' => 
        array (
          'active' => true,
        ),
        'debug' => 
        array (
          'active' => true,
        ),
        'db' => 
        array (
          'active' => false,
          'connection' => 
          array (
            'mysql:dbname=toto;host=localhost',
            'root',
            'labaz2donnees',
          ),
        ),
      ),
      'type' => 'Développement',
    ),
  ),
  'cos_Timezone' => 'Europe/Paris',
);