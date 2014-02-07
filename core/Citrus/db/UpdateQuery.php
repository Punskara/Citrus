<?php
/*
 * This file is part of Citrus. 
 *
 * (c) Rémi Cazalet <remi@caramia.fr>
 * Nicolas Mouret <nicolas@caramia.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package Citrus\db
 * @subpackage Citrus\db\UpdateQuery
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\db;


/**
 * This class generates an SQL update query.
 */
class UpdateQuery extends Query {
    /**
	 * Query criterias
	 * @var  array  criterias
	 */
	public $where = array();
    
    /**
     * @var  array  new values
     */
	public $values = array();

	/**
	 * Adds a AND criteria
	 *
	 * @param string $where Criteria to add
	 * @return \core\Citrus\db\SelectQuery
	 */
	public function addWhere( $where ) {
		$this->where[] = $where;
		return $this;
	}
    
    /**
     * Adds a value to update
     *
     * @param  string  column name
     * @param  string  new value
     */
	public function setValue( $name, $value ) {
		$this->addColumn( $name );
		$this->values[ $name ] = $value;
	}


    /**
     * Renders query in SQL.
     * @return  string  SQL for the query.
     */
	public function renderSql() {
		$values = array();
		$id = 0;
		foreach ( $this->values as $col => $val ) {
			if ( is_bool( $val ) ) $val = $val ? 1 : 0;
			$values[] = "`$col`=" . ( isset( $val ) ? $this->database->quote( $val ) : "NULL" );
		}
		$values  = implode( ",", $values );
		$where = $this->where ? "\n  WHERE\n    " . implode( "\n    AND ", $this->where ) : "";
		return $this->sql = "UPDATE $this->table SET $values$where";
	}

	/**
	 * Prepare / Execute the query
	 * @return mixed
	 */
	public function execute( $params = array(), $driverOptions = array() ) {
		$stmt = parent::execute( $params, $driverOptions );
		return $stmt->rowCount();
	}
}