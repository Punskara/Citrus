<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0.2                                                            |
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
 * @subpackage Citrus\db\Query
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\db;
use \core\Citrus\Citrus;

/**
 * This class builds an SQL Query
 */
abstract class Query {
    /**
	 * Main table
	 * @var string
	 */
	public $table;
	/**
	 * Alias => table hash
	 * @var array
	 */
	public $Aliases = array();
	/**
	 * Columns to return
	 * @var array
	 */
	public $columns = array();
	/**
	 * Last executed SQL
	 * @var string
	 */
	public $Sql = "";
	/**
	 * database connection
	 * @var \core\Citrus\db\Connection
	 */
	public $database;


    /**
     * Constructor.
     *
     * @param \core\Citrus\db\Connection  $db  an Citrus connection to the database
     */
	public function __construct( $db = null ) {
		$this->database = $db ? $db : Citrus::getInstance()->getDatabase();
	}
	public function __toString() {
		return $this->RenderSql();
	}

	/**
	 * Render SQL
	 * @return string
	 */
	abstract public function RenderSql();
	/**
	 * Executes the query
	 * @return mixed
	 */
	public function Query() {
		$this->Sql = $this->RenderSql();
		return $this->database->query( $this->Sql );
	}
	/**
	 * Prepares / Executes the query
	 * @return mixed
	 */
	public function Execute( $params = array(), $driverOptions = array() ) {
		$rec = false;
		try {
		    $rec = $this->database->Execute( (string)$this, $params, $driverOptions );
		} catch ( \PDOException $e ) {
           \core\Citrus\sys_Debug::handleException( $e, true, 'SQL: ' . $this->RenderSql() );
        }
		return $rec;
	}
	/**
	 * Adds a table to this query
	 *
	 * @param string $table
	 * @param string $alias
	 * @return string
	 */
	public function AddAlias( $table, $alias ) {
		$this->Aliases[$alias] = $table;
		return "`$table` AS `$alias`";
	}
	/**
	 * Adds a column to this query
	 *
	 * @param string $column
	 * @param string $table
	 * @return string
	 */
	public function AddColumn( $column, $table = null ) {
		return $thistable[] = $table ? "`$table`.`$column`" : "`$column`";
	}
	/**
	 * Adds several columns to this query
	 *
	 * @param mixed $columns
	 * @param string $table
	 * @return void
	 */
	public function AddColumns( $columns, $table = null ) {
		foreach ( $columns as $column ) {
			$thistable[] = $table ? "`$table`.`$column`" : "`$column`";
		}
	}
}