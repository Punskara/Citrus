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
 * @subpackage Citrus\Date
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus;

class Date extends \DateTime {
    const SQL_FORMAT = 'Y-m-d H:i:s';
    const FR_FORMAT = 'd/m/Y';
    
    public $dayNames = array( 'dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi' );
    public $shortDayNames = array( 'dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam' );
    public $monthNames = array(
        'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
    );
    public $shortMonthNames = array( 'jan', 'fév', 'mar', 'avr', 'mai', 'jui', 'jul', 'aou', 'sep', 'oct', 'nov', 'dec' );
    
    function __construct( $date = 'now' ) {
        if ( $date == 'now' ) {
            $date = date( self::SQL_FORMAT );
        }
        parent::__construct( $date, new DateTimeZone( 'Europe/Paris' ) );
    }
    
    /**
     * Parse the specified string
     *
     * @param string $date
     * @return Citrus_Date
     */
    static function parse( $date = null, $defaultFormat = null ) {
        $obj = new Citrus_Date( null, $defaultFormat );
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
            if ( $indigit )        $parts[] = $digit;
            $old = error_reporting(0);
            $obj->setDate( (int)$parts[0], (int)$parts[1], (int)$parts[2] );
            $obj->setTime( (int)$parts[3], (int)$parts[4], (int)$parts[5] );
            error_reporting($old);
        }
        return $obj;
    }
    
    public function getFullPattern() {
        $pattern = "/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})-([a-zA-Z]{6,9})$/";
        if ( preg_match( $pattern, $this->format( 'Y-m-d-l'), $regs ) ) {
            $days = array( 
                'Sunday'    => 'Dimanche',
                'Monday'    => 'Lundi',
                'Tuesday'   => 'Mardi',
                'Wednesday' => 'Mercredi',
                'Thursday'  => 'Jeudi',
                'Friday'    => 'Vendredi',
                'Saturday'  => 'Samedi',
            );
            $months = array(
                '01' => 'Janvier',
                '02' => 'Février',
                '03' => 'Mars',
                '04' => 'Avril',
                '05' => 'Mai',
                '06' => 'Juin',
                '07' => 'Juillet',
                '08' => 'Août',
                '09' => 'Septembre',
                '10' => 'Octobre',
                '11' => 'Novembre',
                '12' => 'Décembre'
            );
            return $days[$regs[4]] . ' ' . $regs[3] . ' ' . $months[$regs[2]] . ' ' . $regs[1];
        }
    }
    
    public function __toString() {
        return $this->format( 'd/m/Y' );
    }
    
    public static function getMonth( $month, $lang ) {
        $months = array(
            'fr'    => array(
                '01' => 'Janvier',
                '02' => 'Février',
                '03' => 'Mars',
                '04' => 'Avril',
                '05' => 'Mai',
                '06' => 'Juin',
                '07' => 'Juillet',
                '08' => 'Août',
                '09' => 'Septembre',
                '10' => 'Octobre',
                '11' => 'Novembre',
                '12' => 'Décembre',
            ),
            'en'    => array(
                '01' => 'January',
                '02' => 'February',
                '03' => 'March',
                '04' => 'April',
                '05' => 'May',
                '06' => 'June',
                '07' => 'July',
                '08' => 'August',
                '09' => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December',
            ),
        );
        return $months[$lang][$month];
    }
    
    public function isBetween( $date1, $date2 ) {
        if ( !( $date1 instanceof Citrus_Date ) ) {
            $date1 = self::parse( $date1 )->format( self::SQL_FORMAT );
        }
        if ( !( $date2 instanceof Citrus_Date ) ) {
            $date2 = self::parse( $date2 )->format( self::SQL_FORMAT );
        }
        if ( $date1 >= $date2 ) return false;
        if ( $date1 == '' || $date2 == '' ) return false;
        
        return $this->format( self::SQL_FORMAT ) >= $date1 && $this->format( self::SQL_FORMAT ) <= $date2;
    }
    
    public function isBefore( $date, $include = false ) {
        if ( !( $date instanceof Citrus_Date ) ) {
            $date = self::parse( $date )->format( self::SQL_FORMAT );
        }
        if ( $date instanceof Citrus_Date ) {
            if ( $include ) return $this->format( self::SQL_FORMAT ) <= $date->format( self::SQL_FORMAT );
            else            return $this->format( self::SQL_FORMAT ) < $date->format( self::SQL_FORMAT );
        }
        return false;
    }
    
    public function isAfter( $date, $include = false ) {
        if ( !( $date instanceof Citrus_Date ) ) {
            $date = self::parse( $date )->format( self::SQL_FORMAT );
        }
        if ( $date instanceof Citrus_Date ) {
            if ( $include ) return $this->format( self::SQL_FORMAT ) >= $date->format( self::SQL_FORMAT );
            else            return $this->format( self::SQL_FORMAT ) > $date->format( self::SQL_FORMAT );
        }
        return false;
    }
}