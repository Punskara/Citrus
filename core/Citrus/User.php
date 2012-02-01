<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0                                                            |
|   Contact: devs@citrus-project.net                                        |
|      Info: http://citrus-project.net                                      |
|   Support: http://citrus-project.net/documentation/                       |
| ------------------------------------------------------------------------- |
|   Authors: Rémi Cazalet                                                   |
|          : Nicolas Mouret                                                 |
|   Founder: Studio Caramia                                                 |
|  Copyright (c) 2008-2012, Studio Caramia. All Rights Reserved.            |
| ------------------------------------------------------------------------- |
|   For the full copyright and license information, please view the LICENSE |
|   file that was distributed with this source code.                        |
'---------------------------------------------------------------------------'
*/

/**
 * @package Citrus
 * @subpackage Citrus\User
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



/**
 * @Entity @Table(name="citrus_user")
 */
 
namespace core\Citrus;

class User extends data\Model {
    /**
    * @Id @Column(type="integer") @GeneratedValue
    */
    private $id;
    
    /**
     * @Column(type="string")
     */
    public $login;
    
    /**
     * @Column(type="string")
     */
    public $password;
    
    /**
     * @Column(type="string")
     */
    public $name;
    
    /**
     * @Column(type="string")
     */
    public $email;
    
    /**
     * @Column(type="boolean")
     */
    public $valid;
    
    /**
     * @Column(type="boolean")
     */
    public $isAdmin;
    
    const TABLENAME = 'citrus_user';

    public function __construct() {
        parent::__construct();
    }

    public function __toString()  {
        return (string) $this->name;
    }

	public function login( $login, $password ) {
	    $user = $this->fetchUser( $login, $password );
	    if ( $user ) {
		    $_SESSION['CitrusUserLogin'] 	= $this->login;
		    $_SESSION['CitrusUserId']       = $this->id;
		    $_SESSION['CitrusUser']         = $user;
		}
		return $user;
	}

	public function isLogged() {
		return ( 
			isset( $_SESSION['CitrusUserLogin'] )  && 
			isset( $_SESSION['CitrusUserId'] )
		);
	}
	
	private function fetchUser( $login, $password ) {
	    $cos = Citrus::getInstance();
	    
	    $password = md5( $password );
	    
	    $exists = $cos->db->execute( 
	        "SELECT * FROM " . self::TABLENAME . "
	        WHERE login = '$login' 
	        AND password = '$password'"
	    )->fetchObject( '\\core\\Citrus\\User' );
	    
	    if ( $exists ) {
    	    $this->login    = $exists->login;
            $this->name     = $exists->name;
            $this->email    = $exists->email;
            $this->id       = $exists->id;
        }
	    return $exists;
	}
	
	public static function createTable() {
	    $q = "CREATE TABLE `citrus_user` (
            `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `login` VARCHAR( 255 ) NOT NULL ,
            `password` VARCHAR( 255 ) NOT NULL ,
            `name` VARCHAR( 255 ) NOT NULL ,
            `email` VARCHAR( 255 ) NOT NULL ,
            `isadmin` TINYINT NOT NULL ,
            `valid` TINYINT NOT NULL
        ) ENGINE = MYISAM ;\n";
        return $q;
	}
	
	public static function insertFirstUser() {
	    $q = "INSERT INTO `citrus_user` (
            `id` ,
            `login` ,
            `password` ,
            `name` ,
            `email` ,
            `isadmin`,
            `valid`
        )
        VALUES (
            NULL , 'admin', '1a1dc91c907325c69271ddf0c944bc72', 'root', 'remi@caramia.fr', 1, 1
        );\n";
        return $q;
	}
	
	public function getIP() {
	    return $_SERVER['REMOTE_ADDR'];
	}
	
	public function getUserAgent() {
	    return $_SERVER['HTTP_USER_AGENT'];
	}


    public function __get( $name ) {
        if ( property_exists( $this, $name ) ) {
            return $this->$name;
        }
    }
    
    public function __set( $name, $value ) {
        if ( property_exists( $this, $name ) ) {
            $this->$name = $value;
        }
    }
}

