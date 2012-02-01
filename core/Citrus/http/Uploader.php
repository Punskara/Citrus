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
 * @subpackage Citrus\http\Uploader
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;

class Uploader {
	/**
	 * Maximal size of the uploaded file
	 * @var int
	 */
	public $maxFileSize = 0;
	/**
	 * Characters allowed in the file name (in a Regular Expression format)
	 * @var string
	 */
	public $validChars = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';
	/**
	 * List of forbidden extensions
	 * @var array
	 */
	public $invalidExtensions = array( 'php', 'phtml', 'cgi', 'fcgi', 'pl', 'py', 'rb', 'asp' );
	/**
	 * List of allowed extensions
	 * @var array
	 */
	public $validExtensions = array();
	/**
	 * Uploaded file name
	 * @var string
	 */
	public $fileName = '';
	/**
	 * Uploaded file extension
	 * @var string
	 */
	public $fileExt = '';
	/**
	 * Uploaded file libelle (filename without extension)
	 * @var string
	 */
	public $fileLibelle = '';
    
    /**
     * the file
     * @var $mixed;
     */
	protected $file;

    /**
     * Constructor
     *
     * @param  mixed  $file  The file
     *
     * @throws \Exception if the file is not found or if an upload error has occured. 
     */
	public function __construct( $file ) {
		if ( !isset( $file ) )
			throw new \Exception( "No upload found" );
		if ( isset( $file['error'] ) && $file['error'] )
			throw new \Exception( "Error " . $file['error'] );
		if ( !isset( $file['tmp_name'] ) || !@is_uploaded_file( $file['tmp_name'] ) )
			throw new \Exception( "Upload failed is_uploaded_file test" );
		if ( !isset( $file['name'] ) )
			throw new \Exception( "File has no name" );
		$this->file = $file;
	}
	
	
	/**
	 * Reads the file to determine its name and extention
	 *
	 * @throws \Exception if the file is too big, the extension is not allowed.
	 *
	 */
	public function readFile() {
        $fileName = preg_replace( '/[^' . $this->validChars . ']|\.+$/i', '', basename( $this->file['name'] ) );
		$ext = strtolower( ( $pos = strrpos( $fileName, '.' ) ) ? substr( $fileName, $pos+1 ) : '' );
		$libelle = strtolower( ( $pos = strrpos( $fileName, '.' ) ) ? substr( $fileName, 0, $pos ) : $fileName );
		if ( $this->maxFileSize && $this->maxFileSize < filesize( $this->file['tmp_name'] ) ) 
			throw new \Exception( "File exceeds maximum allowed size" );
		if ( in_array( $ext, $this->invalidExtensions ) ) 
			throw new \Exception( "File extension rejected." );
		if ( $this->validExtensions && !in_array( $ext, $this->validExtensions ) ) 
			throw new \Exception( "File extension rejected: $ext (" . implode( ',', $this->validExtensions ) . ')' );
		$this->fileName = $fileName;
		$this->fileLibelle = $libelle;
		$this->fileExt = $ext;
	}
	
	public function testFile( $dest ) {
        $cpt = 0;
		while ( is_file ( $dest . '/' . $this->fileName ) && $cpt++ ) 
			$this->fileName = $this->fileLibelle . '.' . sprintf ( '%04d', $cpt ). '.' . $this->fileExt;
	}
	
	/**
	 * Moves the file from the temp dir to the target destination
	 *
	 * @param string  $dest  Path of target destination
	 *
	 * @throws \Exception if the file could'nt be moved
	 * 
	 * @return boolean
	 */
	public function moveFile( $dest ) {
		if ( file_exists( $this->file['tmp_name'] ) ) {
			$this->testFile( $dest );
			if ( !@move_uploaded_file( $this->file["tmp_name"], $dest . '/' . $this->fileName ) ) {
			    throw new Exception( "File could not be moved" );
		    }
		}
		return true;
	}
}