<?php
namespace core\Citrus;

class Media extends data\Model {
    public $type;
    public $name;
    public $url;
    public $blockThumbnail;
    public $newsThumbnail;
    
    public static $ImageExtensions = array( "png", "jpg", "jpeg", "gif" );
	public static $VideoExtensions = array( "swf", "flv" );
	public static $DocumentExtensions = array( "pdf", "doc", "xls", "ppt" );
    
    public function __toString() {
        return $this->name;
    }
    
    public static function Sorted( $inst ) {
		return array(
			"images" =>		self::GetImages( $inst ),
			"videos" =>		self::GetVideos( $inst ),
			"documents" =>	self::GetDocuments( $inst ),
		);
	}
	public static function GetImages( $inst ) {
		return self::FilterExtensions( $inst, self::$ImageExtensions );
	}
	public static function GetVideos( $inst ) {
		return self::FilterExtensions( $inst, self::$VideoExtensions );
	}
	public static function GetDocuments( $inst ) {
		return self::FilterExtensions( $inst, self::$DocumentExtensions );
	}
	protected static function FilterExtensions( $items, $values ) {
		$out = array();
		foreach ( $items as $item ) {
		    if ( is_object( $item ) ) {
    			$ext = substr( $item->url, strrpos( $item->url, "." ) + 1 );
    			if ( in_array( $ext, $values ) ) $out[] = $item;
    		}
		}
		return $out;
	}
	
	public function IsImage() {
		return in_array( substr( $this->Url, strrpos( $this->Url, "." ) + 1 ), self::$ImageExtensions );
	}
	public function IsVideo() {
		return in_array( substr( $this->Url, strrpos( $this->Url, "." ) + 1 ), self::$VideoExtensions );
	}
	public function IsDocument() {
		return in_array( substr( $this->Url, strrpos( $this->Url, "." ) + 1 ), self::$DocumentExtensions );
	}
	
	public function DeleteFile() {
		return unlink( KOSWEBPATH . $this->Url );
	}
	
	public function DeleteThumb() {
		return unlink( KOSWEBPATH . $this->Thumbnail );
	}
	
	public function deleteBlockThumb() {
		return unlink( CITRUS_PATH . $this->BlockThumbnail );
	}
	
	public function deleteNewsThumb() {
		return unlink( CITRUS_PATH . $this->newsThumbnail );
	}
	
	public function delete( $id ) {
	    parent::delete( $id );
	    if ( is_file( CITRUS_PATH . $this->blockThumbnail ) ) {
            unlink( CITRUS_PATH . $this->blockThumbnail );
        }
        if ( is_file( CITRUS_PATH . $this->newsThumbnail ) ) {
            unlink( CITRUS_PATH . $this->newsThumbnail );
        }
	    return unlink( CITRUS_PATH . $this->url );
	    
	}
	
	public function createVideoThumb() {
	    $this->thumbnail = $this->args['thumbnail'] = '/web/upload/' . basename( $this->thumbnail );
        $thumbnail = PhpThumbFactory::create( CITRUS_PATH . $this->thumbnail );
        $thumbnail->adaptiveResize( 380, 214 );
        $thumbnail->save( CITRUS_PATH . $this->thumbnail );
	}
	
	public function createThumbs() {
	    $this->blockThumbnail = $this->args['blockThumbnail'] = '/web/upload/block_thumb_' . basename( $this->url );
        $this->newsThumbnail = $this->args['newsThumbnail'] = '/web/upload/news_thumb_' . basename( $this->url );
	    
        $thumb = PhpThumbFactory::create( CITRUS_PATH . $this->url );
        
        $thumb->resize( 208 );
        $thumb->crop( 0, 0, 208, 128 );
        $thumb->save( CITRUS_PATH . $this->blockThumbnail );
	}
	
	public static function getPhotosExcept( $ids = array( ) ) {
        $cos = Citrus::getInstance();
        if ( count( $ids ) > 0 ) {
            $list = $cos->db->execute(
                "SELECT *
                FROM `nmm_media`
                WHERE `id` NOT IN (" . implode( ',', $ids ) . ")
                AND `type` = 'image'
                ORDER BY `id`"
            )->fetchAll( /PDO::FETCH_CLASS, 'nmm_Media' );
        } else {
            $list = false;
        }
        return $list;
    }
}