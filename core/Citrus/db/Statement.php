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
 * @subpackage Citrus\db\Statement
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\db;

class Statement extends \PDOStatement {

	protected $db;

	protected function __construct( $db = null ) {
		if ( $db ) $this->db = $db;
	}

	public function execute( $params = null ) {
		global $kos;
		if ( $kos->Logger ) {
			$kos->Logger->logEvent( $this->queryString, \core\kos\kos_sys_Logger::INFO, 'sql' );
		}
		return parent::execute( $params );
	}

}