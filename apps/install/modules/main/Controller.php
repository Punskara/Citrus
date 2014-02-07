<?php
/*
.---------------------------------------------------------------------------.
|   Software: Citrus PHP Framework                                          |
|   Version: 1.0.2                                                            |
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
use \core\Citrus\mvc\Controller as AController;
use \core\Citrus\http\Http;
use \core\Citrus\db\InsertQuery;
use \apps\install\Installer;  

class Controller extends AController {
    
    public $pageTitle = 'Installation';
    
    public function do_index( $request ) {
        $generated = (bool) $request->param( 'generated', 'string' );
        $this->view->assign( 'generated', $generated || is_file( CITRUS_PATH . '/config/config.inc.php' ) );
        $this->view->assign( 'just_generated', $generated );
    }

    public function do_installation() { $this->view->layout = false; }

    public function do_configuration() { $this->view->layout = false; }

    public function do_apps() { 
        $this->view->layout = false; 
        $installer = new Installer();
        foreach ( $installer->getAppsList() as $app ) $res[ $app ] =  $installer->getModulesList( $app );
        $this->view->assign( 'appsJson', json_encode($res) );
    }
    
    public function do_getconfig( $request ) {
        $this->view->layout = false; 
        
        if ( is_file( CITRUS_PATH . '/config/config.inc.php' ) ) {
            $config = include CITRUS_PATH . '/config/config.inc.php';
            $json['site_name'] = $config['site_name'];
            
            foreach ( $config['hosts'] as $k=>$h ) {
                $host = Array();
                
                $bdd_lnk = explode(';', $h['services']['db']['connection'][0]);
                
                $host['hostname'] = $h['domain'];
                $host['path'] = $h['root_path'];
                $host['log'] = $h['services']['logger']['active'] ? 1 : 0;
                $host['debug'] = $h['services']['debug']['active'] ? 1 : 0;
                $host['bdd'] = $h['services']['db']['active'] ? 1 : 0;
                $host['database'] = substr($bdd_lnk[0],13) ? substr($bdd_lnk[0],13) : '';
                $host['bddhost'] = substr($bdd_lnk[1],5) ? substr($bdd_lnk[1],5) : '' ;
                $host['login'] = $h['services']['db']['connection'][1];
                $host['password'] = $h['services']['db']['connection'][2];
                $json['hosts'][] = (object) $host;
            }
            
            $installer = new Installer();
            foreach ( $installer->getAppsList() as $app ) $res[ $app ] =  $installer->getModulesList( $app );
            
            $json['apps'] = $res;
            $out = json_encode( (object) $json ).';';
        }
        else $out ='{};';
        $this->view->assign( "json", $out );
    }

    public function do_generate( $request ) {
        $this->view->layout = false;
        $config = json_decode( html_entity_decode ( $request->param('config', 'string' ), ENT_QUOTES ) );
        // création du fichier config

        foreach ( $config->hosts as $k=>$host ) {
            $hosts_generate[] = array(
                'domain'    => $host->hostname,
                'root_path' => $host->path,
                'services'  => array(
                    'logger' => array( 'active' => $host->log ? true : false ),
                    'debug'  => array( 'active' => $host->debug ? true : false ),
                    'db'     => array( 'active' => $host->bdd ? true : false ,
                        'connection' => array( 
                            "mysql:dbname=" . $host->database . ";host=" . $host->bddhost , 
                            $host->login, 
                            $host->password 
                        ),
                    ),
                ),
            );
        }
        
        $config_generate = array(
            'site_name'         => $config->site_name,
            'hosts'             => $hosts_generate ,
            'default_timezone'  => 'Europe/Paris',
        );
        
        
        $config_str = preg_replace(
            "/[0-9]+ => ([^\n])/", "$1", 
            var_export( $config_generate, true ) 
        );
        
        $config_file = '<?php' . chr(10) . 'return $config = ' . $config_str .';';

        // saving old configuration settings
        if ( is_file( CITRUS_PATH . '/config/config.inc.php' ) )
            rename ( CITRUS_PATH . '/config/config.inc.php', CITRUS_PATH . '/config/config.' . date("Ymdhis") . '.php' );

        // writing new configuration settings
        $fp = fopen( CITRUS_PATH . '/config/config.inc.php' , 'w');
        fwrite($fp, $config_file);
        fclose($fp);
        
        // création des apps et modules
        $installer = new Installer();
        $exist = $installer->getAppsList();
        
        foreach ( $installer->getAppsList() as $app ) 
            $exist[ $app ] =  $installer->getModulesList( $app );

        foreach ( $config->apps as $app => $modules ) {
            if ( !isset($exist[ $app ]) ) $installer->generateApp( $app );
            foreach ( $modules as $k => $module ) 
                if ( !isset( $exist[ $app ] ) || ( isset( $exist[ $app ] ) && !in_array( $module, $exist[ $app ]) ) ) 
                    $installer->generateModule( $module, $app );
        }
        
        Http::redirect( CITRUS_PROJECT_URL );
        
    }
}