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
 * @package Citrus\http
 * @subpackage Citrus\http\Upload
 * @author Rémi Cazalet <remi@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;

class Upload {
	public static function uploadGraphic( $file, $fileName, $destDir, $isFlash = false ) {
		$max_size 	= 400000000; 
		$dirname 	= $destDir;
		$size 		= $file['size'];
		$name 		= $fileName;
		$type 		= $file['type'];
		$tmp_name 	= $file['tmp_name'];

		if ( !empty( $file ) ) {
			// tout d'abord s'il s'agit d'une image on recupere un tableau de caracteristiques
			// de l'image pour les tests.
			
			if ( $file != "none" && !stristr( $type, "plain" ) && !stristr( $type, "flash" ) ) {
				$tab = getimagesize( $tmp_name );
			} else {
				$tab[0] = false;
			}
			// test une erreur de taille de fichier,
			if ( $size > $max_size || $size == 0 ) {
				$ok =  'false_size';
			}
			// test si erreur dans la variable $Fichier
			elseif ( strpos( $name,'\\' ) || strpos( $name, '/' ) ) {
				$ok = 'false_format';
			}
			
			// si ce n'est pas un fichier txt je test si l'image est bien une image, si
			// une personne renomme un .doc en .jpg par exemple ce test suffit a annuler l'upload
			
			elseif ( !$tab[0] && !stristr( $type, "image" ) && !stristr( $type, "flash" ) ) {
				$ok = 'false_datatype';
			}    
			
			// test le type d'encodage du fichier pour etre bien sur de sa nature
			
			elseif ( 
				!stristr( $type, "gif" ) && 
				!stristr( $type, "jpeg" ) && 
				!stristr( $type, "png" ) &&
				!stristr( $type, 'flash' )
			) {
				$ok = 'false_enctype';
			}           
			// test si le fichier n'est pas déjà uploadé
			
			elseif ( file_exists( "$dirname/$name" ) ) {
				$ok = 'false_exists';
			}	
			else {
				// tout s'est déroulé ok, ouf enfin on peut uploader le fichier avec copy
				// apres avoir supprimer les espaces avec str_replace.
				$ok = 'true';
				$file_name = str_replace( " ", "_", $name );
				#if ( copy( $tmp_name, $dirname . "/" . $file_name ) ) {
				if ( move_uploaded_file( $tmp_name, $dirname . "/" . $file_name ) ) {
					if ( !stristr( $type, "flash" ) ) {
						$image = $file_name;
						$largeur_max = 150;
						$hauteur_max = 110;
						$source = $dirname . "/";
						$destination = $source;
						$prefixe = "s_";
					}
				}
				
			}
		}
		return $ok;
	}
	
	public static function uploadFile( $file, $fileName, $destDir, $isFlash = false ) {
		$max_size 	= 400000000; 
		#$dirname  	= dirname( __FILE__ ) . '/../../images/services'; // chemin de destination des fichiers depuis la racine du script
		$dirname 	= $destDir;
		$size 		= $file['size'];
		$name 		= $fileName;
		$type 		= $file['type'];
		$tmp_name 	= $file['tmp_name'];

		$types = array(
			'application/x-msword',
			'application/msword',
			'application/x-pdf', 
			'application/pdf',
			'application/octet-stream', 
			'text/html', 
			'application/x-vnd.oasis.opendocument.text'
		);

		if ( !empty( $file ) ) {

			if ( $size > $max_size || (int)$size == 0 ) {
				$ok =  'false_size';
			}
			elseif ( strpos( $name,'\\' ) || strpos( $name, '/' ) ) {
				$ok = 'false_format';
			}
		
			#var_export($type); exit();
			/*if (!in_array($type, $types)) {
				$ok = 'false_enctype';
			}*/           
			
			elseif ( file_exists( "$dirname/$name" ) ) {
				$ok = 'false_exists';
			}	
			else {
				$ok = true;
				$file_name = str_replace( " ", "_", $name );
				if ( move_uploaded_file( $tmp_name, $dirname . "/" . $file_name ) ) {
					if ( !stristr( $type, "flash" ) ) {
						$image = $file_name;
						$largeur_max = 150;
						$hauteur_max = 110;
						$source = $dirname . "/";
						$destination = $source;
					}
				}
				
			}
		}
		return $ok;
	}
	
	public static function renameFile( $src, $dest ) {
	    return rename( $src, $dest );
	}
}

?>