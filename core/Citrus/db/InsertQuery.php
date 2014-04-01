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
 * @subpackage Citrus\db\HydratableQuery
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\db;

/**
 * This class generates an SQL insertion query.
 */
class InsertQuery extends Query {
	/**
	 * values => Table hash
	 * @var array
	 */
    public $values = array();

	/**
	 * Sets value for given column
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
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
		foreach ( $this->values as $col => $val ) {
			if ( is_bool( $val ) ) $val = $val ? 1 : 0;
			$values[ $col ] = isset( $val ) ? $this->database->quote( $val ) : "NULL";
		}
		$columns = "`" . implode( "`,`", array_keys( $values ) ) . "`";
		$values  = implode( ",", array_values( $values ) );
		return $this->sql = "INSERT INTO $this->table ($columns) VALUES ($values)";
	}

	/**
	 * Prepare / Execute the query
	 * @return mixed
	 */
	public function execute( $params = array(), $driverOptions = array() ) {
		$stmt = parent::execute( $params, $driverOptions );
		return $this->database->lastInsertId();
	}
}