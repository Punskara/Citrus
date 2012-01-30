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
 * @subpackage Citrus\wiki\Wiki
 * @author Nicolas Mouret <nicolas@caramia.fr>
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Wiki;

class Wiki extends Wstream {

	private $config = array(
		'protocole' => 'http',
		'url'       => 'muffin-local',
		'path'      => '/mediawiki/api.php'
	);
	
	/* configuration et initialisation */
	protected function w_url() {
		return $this->config['protocole'] . '://' . $this->config['url'] . $this->config['path'] ;
	}
	
	/* fonctionnalitées */
	public function w_login( $login, $pass ) {
		// identification
		
		$param = array( 
			'action' => 'login',
			'options' => array(
				'lgname' => $login,
				'lgpassword' => $pass
			)
		);

		
		$res = $this->str_build( $param, \HttpRequest::METH_POST )->str_send();

		$this->wiki['cookieprefix'] = $res->login->cookieprefix;
		$this->str_cookie( $this->wiki['cookieprefix'] . "_session", $res->login->sessionid );

		if ( $res->login->result == 'NeedToken' ) {
			$this->str_param( 'lgtoken', $res->login->token );
			$res = $this->str_send();
		}
		
		if ( $res->login->result == 'Success' ) {
			$this->str_cookie( $this->wiki['cookieprefix'] . "UserID", 		$res->login->lguserid );
			$this->str_cookie( $this->wiki['cookieprefix'] . "UserName", 	$res->login->lgusername );
			$this->str_cookie( $this->wiki['cookieprefix'] . "Token", 		$res->login->lgtoken );
			return true;
		}
		else return false;
	}
	
	public function w_logout() {
		// deconnexion
		$param = array( 
			'action' => 'logout',
			'options' => array()
		);
		return $this->str_build( $param )->str_send();
	}
	
	public function w_watchlist() {
		// liste de suivi
		$param = array( 
			'action' => 'query',
			'options' => array(
				'list' => 'watchlist',
				'wlprop' => 'ids|title|flags|user|comment|timestamp|patrol|sizes'
			)
		);
		return $this->str_build( $param )->str_send();
	}
	
	public function w_watchlistraw() {
		// liste de suivi2 ?
		$param = array( 
			'action' => 'query',
			'options' => array(
				'list' => 'watchlistraw'
			)
		);
		return $this->str_build( $param )->str_send();
	}
	
	public function w_pagecreate( $title, $content ) {
		// création d'une page
		
		$param = array( 
			'action' => 'query',
			'options' => array(
				'prop' => 'info',
				'titles'=> $title,
				'intoken' => 'edit'
			)
		);
		$res = $this->str_build( $param )->str_send();
		list( $k, $v ) = each( $res->query->pages );
		if ( $k < 0 ) { // fichier inexistant
			$editToken = $v->edittoken;
		
			$param = array( 
				'action' => 'edit',
				'options' => array(
					'title'=>$title,
					'text'=>$content,
					'createonly' => true,
					'token' => $editToken
				)
			);
			return $this->str_build( $param, \HttpRequest::METH_POST )->str_send();
		}
		else return "fichier deja existant";
	}
	
	public function w_pagemodif( $title, $content ) {
		// Modification d'une page existante
		
		$param = array( 
			'action' => 'query',
			'options' => array(
				'prop' => 'info',
				'titles'=> $title,
				'intoken' => 'edit'
			)
		);
		$res = $this->str_build( $param )->str_send();
		list( $k, $v ) = each( $res->query->pages );
		if ($k>0) { // fichier existant
			$editToken = $v->edittoken;
		
			$param = array( 
				'action' => 'edit',
				'options' => array(
					'title' => $title,
					'text' => $content,
					'nocreate' => true,
					'token' => $editToken
				)
			);
			return $this->str_build( $param, \HttpRequest::METH_POST )->str_send();
		}
		else return "fichier inexistant";
	}
	
	public function w_pagerevision( $title ) {
		$param = array( 
			'action' => 'query',
			'options' => array(
				'prop' => 'revisions',
				'titles'=> $title,
				'rvlimit' => '500',
				'rvprop' => 'timestamp|user|comment|content'
			)
		);
		return $this->str_build( $param, \HttpRequest::METH_POST )->str_send();
	}
	
	public function w_pagecontent( $title ) {
		$param = array( 
			'action' => 'query',
			'options' => array(
				'prop' => 'revisions',
				'titles' => $title,
				'rvprop' => 'timestamp|user|comment|content'
			)
		);
		return $this->str_build( $param, \HttpRequest::METH_POST )->str_send();
	}
	
}