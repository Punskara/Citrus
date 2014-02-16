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
 * @subpackage Citrus\db\SelectQuery
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\db;

/**
 * Generates as SQL selecting query
 */
class SelectQuery extends Query {
    /**
	 * Query left joins
	 * @var array
	 */
	public $leftJoins = array();
	/**
	 * Query inner joins
	 * @var array
	 */
	public $innerJoins = array();
	/**
	 * Query criterias
	 * @var array
	 */
	public $where = array();
	/**
	 * Query order
	 * @var array
	 */
	public $order = array();
	/**
	 * Query group by
	 * @var array
	 */
	public $group = array();
	/**
	 * Limit start
	 * @var int
	 */
	public $limitStart = 0;
	/**
	 * Limit count
	 * @var int
	 */
	public $limitCount = 0;


    /**
     * Renders query in SQL.
     * @return  string  SQL for the query.
     */
	public function renderSql() {
		$columns = implode( ",", $this->columns );
		$ljoins = $this->leftJoins ? "\n  LEFT JOIN " . implode( "\n  LEFT JOIN ", $this->leftJoins ) : "";
		$ijoins = $this->innerJoins ? "\n  INNER JOIN " . implode( "\n  INNER JOIN ", $this->innerJoins ) : "";
		$where = $this->where ? "\n  WHERE\n    " . implode( "\n    AND ", $this->where ) : "";
		$order = $this->order ? "\n  ORDER BY " . implode( ",", $this->order ) : "";
		$group = $this->group ? "\n  GROUP BY " . implode( ",", $this->group ) : "";
		$limit = $this->limitStart || $this->limitCount ? "\n LIMIT $this->limitStart,$this->limitCount" : "";
		return $this->Sql = "SELECT $columns\n  FROM $this->table$ljoins$ijoins$where$group$order$limit";
	}

	/**
	 * Add a left join
	 *
	 * @param string $table Join source
	 * @param string $join Join key
	 * @return string
	 */
	public function addLeftJoin( $table, $join ) {
		return $this->leftJoins[] = "$table ON $join";
	}
	/**
	 * Add an inner join
	 *
	 * @param string $table Join source
	 * @param string $join Join key
	 * @return string
	 */
	public function addInnerJoin( $table, $join ) {
		return $this->innerJoins[] = "$table ON $join";
	}
	/**
	 * Adds an AND criteria
	 *
	 * @param string $where Criteria to add
	 * @return \core\Citrus\db\SelectQuery
	 */
	public function addWhere( $where ) {
		$this->where[] = $where;
		return $this;
	}
	
	/**
	 * Adds an OR criteria
	 *
	 * @param string $where Criteria to add
	 * @return \core\Citrus\db\SelectQuery
	 */
	public function addORWhere( $where ) {
	    $this->where[] = '(' . implode( "\n OR ", $where ) . ')';
	}
	
	/**
	 * Adds an order criteria
	 *
	 * @param string $order order
	 * @return \core\Citrus\db\SelectQuery
	 */
	public function addOrder( $order ) {
		$this->order[] = $order;
		return $this;
	}
	/**
	 * Set the resultset limit
	 *
	 * @param int $offset
	 * @param int $count
	 * @return \core\Citrus\db\SelectQuery
	 */
	public function setLimit( $offset = null, $count = null ) {
		if ( isset( $offset ) ) $this->limitStart = $offset;
		if ( isset( $count  ) ) $this->limitCount = $count;
		return $this;
	}
}