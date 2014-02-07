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
 * @package Citrus\xml
 * @subpackage Citrus\xml\Writer
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\xml;

class Writer extends \XMLWriter {
    
    public $file;
    public $content;
    
    public function __construct( $file ) {
        //parent::__construct();
        $this->openMemory();
        $this->setIndent( true );
        $this->file = $file;
    }
    
    public function write() {
        if ( is_writable( $this->file ) ) {
            try {
                $handle = fopen( $this->file, "w+" );
            } catch ( \Exception $e ) {
                echo $e->getMessage();
            }
        	ob_start();
    		echo $this->flush();
    		$content = ob_get_clean();
		    if ( fwrite( $handle, $content ) === false ) {
		       throw new \Exception( "Unable to write file $this->file" );
		    }
		    fclose( $handle );
		}
    }
    public function getContent() {
        ob_start();
		echo $this->flush();
		$content = ob_get_clean();
		return $content;
    }
    
    public function setXMLHeaders() {
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Content-Transfer-Encoding: binary" );
        header( "Content-Description: File Transfer" );
        header( "Content-Type: text/xml" );
    }
}