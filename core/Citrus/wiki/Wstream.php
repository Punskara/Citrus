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
 * @package Citrus\wiki
 * @subpackage Citrus\wiki\Wstream
 * @author Nicolas Mouret <nicolas@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\Wiki;

abstract class Wstream {

	private $stream = array(
		'lnk' => null,
		'method' => null,
		'action' => null,
		'vars' => array(),
		'files' => array(),
		'cookies' => array()
	);
	
	private $wiki = array (
		'cookieprefix' => null,
	);
	
	abstract protected function w_url();
	
	
	protected function str_init() {
		$url = $this->w_url() .'?';
		if ( !is_null( $this->stream['action'] ) ) $url .= 'action=' . $this->stream['action'] . '&';
		if ( $this->stream['method'] != HttpRequest::METH_POST )
			foreach( $this->stream['vars'] as $k => $v ) $url .= $k . '=' . $v . '&';
		$this->stream['lnk'] = new HttpRequest();
		$this->stream['lnk']->setUrl ( $url . 'format=json' );
		$this->stream['lnk']->setMethod ( $this->stream['method'] );
	}
	
	protected function str_build( $param, $method = HttpRequest::METH_GET ) {
		$this->stream['vars'] = array();
		$this->stream['files'] = array();
		foreach ( $param['options'] as $key => $value ) $this->str_param( $key, $value );
		if ( isset( $param['action'] ) ) $this->stream['action'] = $param['action'];
		$this->stream['method'] = $method;
		$this->str_init();
		return $this;
	}
	
	protected function str_send() {
		$this->stream['vars']['format'] = 'json';
		if ( $this->stream['method'] == HttpRequest::METH_POST )
			$this->stream['lnk']->addPostFields( $this->stream['vars'] );
		foreach ( $this->stream['files'] as $key => $value ) {
		    $this->stream['lnk']->addPostFile( $key, $value, mime_content_type( $value ) );
		}
		$this->stream['lnk']->addCookies( $this->stream['cookies'] );
		
		try {
			$res = $this->stream['lnk']->send()->getBody();
		    return json_decode( $res );
		} catch ( HttpException $ex ) {
		    echo $ex;
		}
	}
	
	protected function str_param( $key, $value ) {
		if ( is_file( $value ) ) $this->stream['files'][ $key ] = $value;
		else $this->stream['vars'][ $key ] = $value;
	}
	
	protected function str_cookie( $key, $value ) {
		$this->stream['cookies'][ $key ] = $value;
	}
	
}