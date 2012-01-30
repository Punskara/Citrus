<?php

return array(
	'modelType' => '\\core\\Citrus\\User',
    'tableName' => 'citrus_user',
	'description' => 'utilisateur',
	'pluralDescription' => 'utilisateurs',
    'properties' => array(
		'id' => array(
            'type'              => 'int',
            'autoincrement'     => true,
            'primaryKey'        => true,
            'inputType'         => 'InputHidden',
        ),
		'login' => array(   
            'type'              => 'string',
            'length'            => 255,
            'null'              => false,
            'formLabel'         => 'Nom',
            'inputType'         => 'InputText',
        ),
		'password' => array(   
            'type'              => 'string',
            'length'            => 255,
            'null'              => false,
            'formLabel'         => 'Nom',
            'inputType'         => 'InputPassword',
        ),
		'name' => array(   
            'type'              => 'string',
            'length'            => 255,
            'null'              => false,
            'formLabel'         => 'Nom',
            'inputType'         => 'InputText',
        ),
		'email' => array(   
            'type'              => 'string',
            'length'            => 255,
            'null'              => false,
            'formLabel'         => 'E-mail',
            'inputType'         => 'InputText',
        ),
        'datecreated' => array(
            'type'              => 'datetime',
            'null'              => false,
        ),
        'datemodified' => array(
            'type'              => 'datetime',
            'null'              => false,
        ),
	),
	'adminColumns' => array( 'login' => 'Login', 'email' => 'e-mail' ),
    'orderColumn' => 'login ASC',
);