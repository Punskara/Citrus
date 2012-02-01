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
 * @package Citrus\db
 * @subpackage Citrus\db\Orm
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\db;

use \Doctrine\ORM\EntityManager;
use \Doctrine\DBAL\DriverManager;
use \Doctrine\ORM\Tools\Setup;

/**
 * Manages the link between Dotrine and Citrus.
 */

class Orm {
    
    public $entityMgr;
    public $conn;
    static $instance = false;
    
    
    /**
     * Constructor
     * 
     * @param \Doctrine\ORM\EntityManager  $entityMgr 
     * @param \Doctrine\DBAL\Connection  $connection
     */
    private function __construct( $entityMgr, $connection ) {
        $this->entityMgr = $entityMgr;
        $this->conn = $connection;
    }
    
    /**
     * Accessor.
     */
    static public function getInstance( $config, $paths, $isDevMode = false ) {
        if (self::$instance === false ) {
            list( $dsn, $user, $password ) = $config;
            if ( $dsn && $user && $password ) {
                $result = Connection::parseMySQLDsn( $dsn );
                if ( $result ) {
                    $connectionParams = array(
                        'dbname'    => $result[1],
                        'user'      => $user,
                        'password'  => $password,
                        'host'      => $result[2],
                        'driver'    => 'pdo_mysql',
                        'charset' => 'utf8',
                        'driverOptions' => array(
                                1002=>'SET NAMES utf8'
                        )
                    );
                    $config = new \Doctrine\DBAL\Configuration();

                    $conn = DriverManager::getConnection( $connectionParams, $config );
                    $config = Setup::createAnnotationMetadataConfiguration( $paths, $isDevMode );        
                    $em = EntityManager::create( $conn, $config );
                    self::$instance = new Orm( $em, $conn );
                }
            }
        }
        return self::$instance;
    }
    
    public function find( $class, $id = null ) {
        return $this->entityMgr->find( $class, $id );
    }
}