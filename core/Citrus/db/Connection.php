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
 * @subpackage Citrus\db\Connection
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\db;
use \core\Citrus\Citrus;
use \core\Citrus\sys;

class Connection extends \PDO {
    private $dispMsg = true;
    
    public function __construct( $dsn, $user = null, $pwd = null ) {
        parent::__construct( $dsn, $user, $pwd );
        $this->exec( "SET NAMES 'utf8'" );
        $this->exec( "SET CHARACTER SET 'utf8'" );
    }
    
    public function execute( $sql, $params = null ) {
        $cos = \core\Citrus\Citrus::getInstance();
        $stmt = $this->prepare( $sql );
        if ( $cos->debug ) { 
            $cos->debug->queries[] = $stmt->queryString; #$stmt->errorInfo(); 
        }
        try {
            $stmt->execute( $params );
        } catch ( \PDOException $e ) {
            sys\Debug::handleException( $e, true, 'SQL: ' . $stmt->queryString );
        }
        return $stmt;
    }
    /**
     * Prepare an insertion query
     *
     * @deprecated
     *
     * @param string $table Name of the table
     * @param array $columns Name of the columns
     * @return PDOStatement
     */
    public function prepareInsert( $table, $columns ) {
        $sql = "INSERT INTO $table (" . implode( ",", $columns ) .
            ") VALUES (" . implode( ',', array_fill( 0, count( $columns ), '?' ) ) . ")";
        return $this->prepare( $sql );
    }
    
    /**
     * Prepare an update query
     *
     * @deprecated No longer used, will be removed.
     *
     * @param string $table Name of the table
     * @param string $table Name of the key column (for the WHERE)
     * @param array $columns Name of the columns
     *
     * @return PDOStatement
     */
    public function prepareUpdate( $table, $keyName, $columns ) {
        $sql = "UPDATE $table SET " . implode( "=?,", $columns ) . "=? WHERE $keyName=?";
        return $this->prepare( $sql );
    }
    
    
    /**
     * Inserts values contained in $columns in $table
     *
     * @deprecated No longer used, will be removed.
     *
     * @param string  $table  table name
     * @param array  $columns  array of columns to set.
     *
     * @throws \core\Citrus\sys\Exception if query fails.
     * @return \PDOStatement  $s
     */
     
    public function execInsert( $table, $columns ) {
        $cos = Citrus::getInstance();
        $stmt = $this->prepareInsert( $table, array_keys( $columns ) );
        $s = $stmt->execute( array_values( $columns ) );
        
        if ( $cos->debug ) { 
            $cos->debug->queries[] = $stmt->queryString; #$stmt->errorInfo(); 
        }
        if ( !$s ) {
            $err = $stmt->errorInfo();
            throw new sys\Exception( 'SQL Error (' . $err[0] . ') (' . $err[1] . ') : ' . $err[2] );
        }
        return $s;
    }
    
    
    /**
     * Updates a row identified by $keyName 
     * with values contained in $columns in $table
     *
     * @deprecated No longer used, will be removed.
     *
     * @param string  $table  table name
     * @param string  $keyName  identifier
     * @param array  $columns  array of columns to update.
     *
     * @throws \core\Citrus\sys\Exception if query fails.
     * @return \PDOStatement  $s
     */
    public function execUpdate( $table, $keyName, $columns ) {
        $cos = Citrus::getInstance();
        $stmt = $this->prepareUpdate( $table, $keyName, array_keys( $columns ) );
        $values = array_values( $columns );
        array_push( $values, $columns[$keyName] );
        $s = $stmt->execute( $values );
        if ( $cos->debug ) { 
            $cos->debug->queries[] = $stmt->queryString; #$stmt->errorInfo(); 
            #vexp($stmt->errorInfo());
        }
        if ( !$s ) {
            $err = $stmt->errorInfo();
            throw new sys\Exception( 'SQL Error (' . $err[0] . ') (' . $err[1] . ') : ' . $err[2] );
        }
        return $s;
    }
    
    public function dispDebug( $value ) {
        $this->dispMsg = $value;
    }
    
    
    /**
     * Parses a MySQL DSN.
     */
    static public function parseMySQLDsn( $dsn ) {
        $pattern = "#mysql:dbname=([a-z0-9_]+);host=([a-z0-9_.]+)#";
        if (preg_match( $pattern, $dsn, $matches )) {
            return $matches;
        } else return false;
    }
}