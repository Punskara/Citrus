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
 * @package Citrus
 * @subpackage Citrus\Date
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus;

class Date extends \DateTime {
    const SQL_FORMAT = 'Y-m-d H:i:s';
    const FR_FORMAT = 'd/m/Y';
    
    function __construct( $date = 'now' ) {
        if ( $date == 'now' ) {
            $date = date( self::SQL_FORMAT );
        }
        parent::__construct( $date, new \DateTimeZone( 'Europe/Paris' ) );
    }
    
    /**
     * Parse the specified string
     *
     * @param string $date
     * @return \core\Citrus\Date
     */
    static function parse( $date = null, $defaultFormat = null ) {
        $obj = new Date( null, $defaultFormat );
        if ( !empty($date) ) {
            if ( strpos( $date, '/' ) !== false ) {
                $comp = explode( '/', $date );
                $date = implode( '-', array_reverse( $comp ) );
            }
            $parts = array();
            $index = 0;
            $c = 0;
            $digit = 0;
            $indigit = true;
            $len = strlen( $date );
            while ( $c < $len ) {
                $ch = substr( $date, $c, 1 );
                $isdigit = ( $ch === '0' || ( $ch >= 1 && $ch <= 9 ) );
                if ( $isdigit && !$indigit ) {
                    $indigit = true;
                    $digit = (int)$ch;
                } elseif ( $isdigit && $indigit ) {
                    $digit = 10*$digit + (int)$ch;
                } elseif ( !$isdigit && $indigit ) {
                    $indigit = false;
                    $parts[] = $digit;
                    $digit = 0;
                }
                $c++;
            }
            if ( $indigit ) $parts[] = $digit;
            
            $obj->setDate( (int)$parts[0], (int)$parts[1], (int)$parts[2] );

            if ( isset( $parts[3], $parts[4], $parts[5] ) ) 
                $obj->setTime( (int)$parts[3], (int)$parts[4], (int)$parts[5] );
        }
        return $obj;
    }
    
    public function __toString() {
        return $this->format( self::FR_FORMAT );
    }
    
    
    public function isBetween( $date1, $date2 ) {
        if ( !( $date1 instanceof Date ) ) {
            $date1 = self::parse( $date1 )->format( self::SQL_FORMAT );
        }
        if ( !( $date2 instanceof Date ) ) {
            $date2 = self::parse( $date2 )->format( self::SQL_FORMAT );
        }
        if ( $date1 >= $date2 ) return false;
        if ( $date1 == '' || $date2 == '' ) return false;
        
        return $this->format( self::SQL_FORMAT ) >= $date1 && $this->format( self::SQL_FORMAT ) <= $date2;
    }
    
    public function isBefore( $date, $include = false ) {
        if ( !( $date instanceof Date ) ) {
            $date = self::parse( $date )->format( self::SQL_FORMAT );
        }
        if ( $date instanceof Date ) {
            if ( $include ) return $this->format( self::SQL_FORMAT ) <= $date->format( self::SQL_FORMAT );
            else            return $this->format( self::SQL_FORMAT ) < $date->format( self::SQL_FORMAT );
        }
        return false;
    }
    
    public function isAfter( $date, $include = false ) {
        if ( !( $date instanceof Date ) ) {
            $date = self::parse( $date )->format( self::SQL_FORMAT );
        }
        if ( $date instanceof Date ) {
            if ( $include ) return $this->format( self::SQL_FORMAT ) >= $date->format( self::SQL_FORMAT );
            else            return $this->format( self::SQL_FORMAT ) > $date->format( self::SQL_FORMAT );
        }
        return false;
    }
}