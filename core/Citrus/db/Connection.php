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
 * @subpackage Citrus\db\Connection
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\db;
use \core\Citrus\Citrus;
use \core\Citrus\sys\Debug;
use \core\Citrus\sys\Exception;

class Connection extends \PDO {
    private $dispMsg = true;
    
    public function __construct( $dsn, $user = null, $pwd = null ) {
        parent::__construct( $dsn, $user, $pwd );
        $this->exec( "SET NAMES 'utf8'" );
        $this->exec( "SET CHARACTER SET 'utf8'" );
    }
    
    public function execute( $sql, $params = null ) {
        $cos = Citrus::getInstance();
        $stmt = $this->prepare( $sql );
        if ( $cos->debug ) { 
            $cos->debug->queries[] = $stmt->queryString; #$stmt->errorInfo(); 
        }
        try {
            $stmt->execute( $params );
        } catch ( \PDOException $e ) {
            Debug::handleException( $e, true, 'SQL: ' . $stmt->queryString );
        }
        return $stmt;
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