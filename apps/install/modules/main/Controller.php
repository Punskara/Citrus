<?php
/*
.---------------------------------------------------------------------------.
|   Software: Citrus PHP Framework                                          |
|   Version: 1.0                                                            |
|   Contact: contact@citrus-project.net                                     |
|      Info: http://citrus-project.net                                      |
|   Support: http://citrus-project.net/documentation/                       |
| ------------------------------------------------------------------------- |
|   Authors: Rémi Cazalet                                                   |
|          : Nicolas Mouret                                                 |
|   Founder: Studio Caramia                                                 |
| Copyright (c) 2008-2012, Studio Caramia. All Rights Reserved.             |
| ------------------------------------------------------------------------- |
|   For the full copyright and license information, please view the LICENSE |
|   file that was distributed with this source code.                        |
'---------------------------------------------------------------------------'
*/

/**
 * @package apps\install\modules\main
 * @subpackage Controller
 * @author Nicolas Mouret <nicolas@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace apps\install\modules\main;
use \core\Citrus\Citrus;
use \core\Citrus\mvc;
use \core\Citrus\http;
use \core\Citrus\db\InsertQuery;

class Controller extends mvc\Controller {
	
	public $pageTitle = 'Installation';
	
    public function do_index() {
	    $generated = (bool) $this->request->param( 'generated', FILTER_SANITIZE_STRING );
        $this->template->assign( 'generated', $generated || is_file( CITRUS_PATH . '/config/config.inc.php' ) );
        $this->template->assign( 'just_generated', $generated );
	}

    public function do_installation() { $this->layout = false; }

    public function do_configuration() { $this->layout = false; }

    public function do_apps() { 
		$this->layout = false; 
        $cos = Citrus::getInstance();
		foreach ( $cos->getAppsList() as $app ) $res[ $app ] =  $cos->getModulesList( $app );
        $this->template->assign( 'appsJson', json_encode($res) );
	}
	
	public function do_getconfig() {
		$this->layout = false; 
		
		if ( is_file( CITRUS_PATH . '/config/config.inc.php' ) ) {
			$config = include CITRUS_PATH . '/config/config.inc.php';
			$json['projectname'] = $config['projectName'];
			$json['sitename'] = $config['siteName'];
			$json['defaultApp'] = $config['defaultApp'];
			$json['bdd'] = $config['db'] ? 1 : 0;
			$json['doctrine'] = $config['db_doctrine'] ? 1 : 0;
			
			foreach ( $config['hosts'] as $k=>$h ) {
				$host = Array();
				
				$bdd_lnk = explode(';', $h['services']['db']['connection'][0]);
				
				$host['hostname'] = $h['httpHost'];
				$host['path'] = $h['baseUrl'];
				$host['log'] = $h['services']['logger']['active'] ? 1 : 0;
				$host['debug'] = $h['services']['debug']['active'] ? 1 : 0;
				$host['database'] = substr($bdd_lnk[0],13) ? substr($bdd_lnk[0],13) : '';
				$host['bddhost'] = substr($bdd_lnk[1],5) ? substr($bdd_lnk[1],5) : '' ;
				$host['login'] = $h['services']['db']['connection'][1];
				$host['password'] = $h['services']['db']['connection'][2];
				$host['type'] = $h['type'];
				$json['hosts'][] = (object) $host;
			}
			
	        $cos = Citrus::getInstance();
			foreach ( $cos->getAppsList() as $app ) $res[ $app ] =  $cos->getModulesList( $app );
			
			$json['apps'] = $res;
			
			echo 'config = '.json_encode( (object) $json ).';';
		}
		else echo 'config = {};';
	}

	public function do_generate() {
		$this->layout = false;
		$config = json_decode(html_entity_decode ($this->request->param('config', FILTER_SANITIZE_STRING ), ENT_QUOTES));
		// création du fichier config
	
		foreach ($config->hosts as $k=>$host) {
			$hosts_generate[] = array(
				'httpHost'          => $host->hostname,
				'baseUrl'           => $host->path,
				'services'          => array(
					'hasRewriteEngine' => array( 'active' => true ),
					'logger' => array( 'active' => $host->log ? true : false ),
					'debug'  => array( 'active' => $host->debug ? true : false ),
					'db'     => array( 'active' => $config->bdd ? true : false ,
						'connection' => array( 
							"mysql:dbname=" . $host->database . ";host=" . $host->bddhost , 
							$host->login, 
							$host->password 
						),
					),
				),
				'type'           	=> $host->type,
			);
		}
		
		$config_generate = array(
			'siteName' 		=> $config->sitename,
			'projectName' 	=> $config->projectname,
			'defaultApp' 	=> $config->defaultApp,
			'db'			=> $config->bdd ? true : false,
			'db_doctrine'	=> $config->doctrine ? true : false,
			'hosts' => $hosts_generate ,
			'cos_Timezone' => 'Europe/Paris',
		);
		
		
		$config_str = preg_replace("/[0-9]+ => ([^\n])/", "$1", var_export($config_generate, true));
		
		$config_file = '<?php' . chr(10) . 'return $config = ' . $config_str .';';
		// sauvegarde de l'ancien fichier de configuration
		if ( is_file( CITRUS_PATH . '/config/config.inc.php' ) )
			rename ( CITRUS_PATH . '/config/config.inc.php', CITRUS_PATH . '/config/config.' . date("Ymdhis") . '.php' );
		// ecriture du fichier de configuration
		$fp = fopen( CITRUS_PATH . '/config/config.inc.php' , 'w');
		fwrite($fp, $config_file);
		fclose($fp);
		
		// création des apps et modules
		$cos = Citrus::getInstance();
        		
		$exist = $cos->getAppsList();
		foreach ( $cos->getAppsList() as $app ) $exist[ $app ] =  $cos->getModulesList( $app );

		foreach ( $config->apps as $app => $modules ) {
			if ( !isset($exist[ $app ]) ) $cos->generateApp( $app );
			foreach ( $modules as $k => $module ) 
				if ( !isset( $exist[ $app ] ) || ( isset( $exist[ $app ] ) && !in_array( $module, $exist[ $app ]) ) ) 
					$cos->generateModule( $module, $app );
		}
		
        http\Http::redirect( 'index.php?cos_app=install&cos_module=main&cos_action=index' );
        
	}

    public function do_model() { 
		$this->layout = false; 
		$this->template->assign( 'shema', is_file( CITRUS_PATH . '/include/schema.sql' ));
	}
    
    public function do_buildSchema() {
        $this->layout = false;
        $cos = Citrus::getInstance();
        if ( $cos->buildSQLSchema() ) {
            echo 'ok';
        }
    }

    public function do_execSchema() {
        $this->layout = false;
        $cos = Citrus::getInstance();
		$cos->db->execute( file_get_contents( CITRUS_PATH . '/include/schema.sql' ) );
    }
    
    public function do_dlSchema() {
        $this->layout = false;
        if ( is_file( CITRUS_PATH . '/include/schema.sql' ) ) {
            header( "Pragma: public" );
            header( "Expires: 0" );
            header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header( "Content-Transfer-Encoding: binary" );
            header( "Content-Description: File Transfer" );
            header( "Content-Type: text/sql" );
            header( "Content-Disposition: attachment; filename=\"schema.sql\"" );
            include CITRUS_PATH . '/include/schema.sql';
        }
    }
    
}