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
 * @subpackage Citrus\String
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus;

class String {
	public static function slug2( $string ) {
	    $str = strtolower( trim( $string ) );
		$char = explode(',', "à,â,ä,â,è,é,ë,ê,î,ï,ô,ö,ù,ü,û,ç" );
		$rep = explode( ',', "a,a,a,a,e,e,e,e,i,i,o,o,u,u,u,c" );
        
        $str = self::accent( $str );
        $str = preg_replace( '/[^a-z0-9-]/', '-', $str );
        $str = preg_replace( '/-+/', "-", $str );
        return $str;
	}
	
	public static function slug( $string ) {
		$string = strtolower( $string );
		$repl = array( 
			"à" => "a", "ä" => "a", "â" => "a",
			"é" => "e", "è" => "e", "ë" => "e", "ê" => "e",
			"î" => "i", "ï" => "i",
			"ô" => "o", "ö" => "o",
			"ù" => "u", "û" => "u", "ü" => "u",
			"ç" => 'c',
			"'" => "-", "’" => "-",
			"\"" => "", "\(" => "-", "\)" =>"-",
			"&" => "-", ":" => "-", ":" => "-",
			"\?" => "", "!" => "", "," => "",
			"\." => "", "\/" => "-", " " => "-",
			"--" => "-", "'" => "",
			"%C9" => 'e', 
			"#39;" => '', "#34;" => '',
		);
		foreach ( $repl as $find => $rep ) {
			$string = preg_replace( "/" . $find . "/", $rep, $string );
		}
		
		return $string;
	}
	
	static public function accent( $search ) {
        $search = preg_replace( '/[àâä]/iu', 'a', $search );
        $search = preg_replace( '/[éèëê]/iu', 'e', $search );
        $search = preg_replace( '/[îï]/iu', 'i', $search );
        $search = preg_replace( '/[ôö]/iu', 'o', $search );
        $search = preg_replace( '/[ùûü]/iu', 'u', $search );  
        return $search;
    }
}