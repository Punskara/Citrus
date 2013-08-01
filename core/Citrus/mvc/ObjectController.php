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
 * @package Citrus\mvc
 * @subpackage Citrus\mvc\ObjectController
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\mvc;
use \core\Citrus\Citrus;
use core\Citrus\http;
use core\Citrus\sys;
use core\Citrus\data;

/**
 * Extends Controller to provide model-related actions
 * (e.g. list, edit, save, delete, etc…)
 */
class ObjectController extends Controller {
    public function do_index() {
        $schema = data\Model::getSchema( $this->className );
        $cos = Citrus::getInstance();

        if ( $schema ) {
            $pager_min = 15;
            $pager = new \core\Citrus\data\Pager( $schema->className, $pager_min );

            if ( $this->request->isXHR ) {
                $this->layout = false;
                $search     = $this->request->param( 'search', 'string' );
                $order      = $this->request->param( 'order', 'string' );
                $orderType  = $this->request->param( 'orderType', 'string' );
                
                $where = array();
                if ( $search ) {
                    foreach ( $schema->linkColumns as $cols ) {
                        $where[] = "$cols LIKE '%$search%'"; 
                    }
                }
                $list = $pager->getCollection( 
                    $where,
                    isset( $order ) ? $order : false, 
                    isset( $orderType ) ? $orderType : false
                );
                
                $this->template = new Template( '_index-list' );
                $this->template->assign( 'search', $search );
                $this->template->assign( 'order', $order );
                $this->template->assign( 'orderType', $orderType );
            } else {
                $list = $pager->getCollection( array(), $schema->orderColumn );
                $cos->app->view->addJavascript( 'backend/listing.js' );
                $this->loadActionTemplate( 'index' );
            }
            $this->template->assign( 'schema', $schema );
            $this->template->assign( 'list', $list );
            $this->template->assign( 'pager', $pager );
        }
        else Citrus\Citrus::pageForbidden();
    }

    public function do_edit() {
        $schema = data\Model::getSchema( $this->className );
        $this->layout = !$this->request->isXHR;
        $id = $this->request->param( 'id', 'int' );
        if ( $id ) {
            $res = \core\Citrus\data\Model::selectOne( $this->className, $id );
        } else {
            $res = new $this->className();
        }
        $res->generateForm();
        $this->template->assign( 'res', $res );
        $this->template->assign( 'schema', $schema );
        $this->template->assign( 'layout', $this->layout );
    }


    /**
     * Generic function to save an object in database. Only accessible in POST method.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app. If used with AJAX, this will only display "ok".
     */
    public function do_save() {
        $cos = Citrus::getInstance();
        if ( $this->request->method != 'POST' ) {
            if ( $cos->debug ) {
                throw new Exception( 'Bad method request' );
            } else {
                Citrus::pageNotFound();
            }
        } else {
            #vexp($_POST);
            $type = $this->request->param( 'modelType', 'string' );
            if ( class_exists( $type ) ) {
                $inst = new $type();
                if ( isset( $_FILES ) ) {
                    foreach ( $_FILES as $name => $file ) {
                        if ( !empty( $file['name'] ) ) {
                            if ( $inst->$name ) {
                                unlink( CITRUS_PATH . 'web/upload' . $inst->$name );
                            }
                            $upld = new kos_http_Uploader( $file );
                            $upld->readFile();
                            $up = $upld->moveFile( CITRUS_PATH . '/web/upload/' );
                            $inst->args[$name] = $inst->$name = '/web/upload/' . $upld->fileName;
                        }
                    }
                }
                $inst->hydrateByFilters();
                $rec = $inst->save();
                $inst->hydrateManyByFilters();
                
                #vexp($rec);exit();
                if ( $this->request->isXHR ) {
                    if ( $rec ) {
                        echo "ok";
                        exit;
                    }
                } else {
                    $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$this->name}/";
                    http\Http::redirect( $loc );
                }
            } else throw new sys\Exception( "Unknown class '$type'" );
        }
    }
    
    /**
     * Generic function to delete an object from database.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app.
     * 
     * @param string  $resourceType  Name of the class of the object we want to delete.
     * 
     * @todo Throw an exception if class $resourceType doesn't exist
     * @todo Prevent this function to be executed if method is not POST
     * @todo Handle the case that the request is sent with AJAX.
     */
    public function do_delete() {
        $resourceType = $this->className;
        $cos = Citrus::getInstance();
        $module = $this->module->name;
        $id = $this->request->param( 'id', 'int' );
        if ( $id ) {
            if ( class_exists( $resourceType ) ) {
                data\Model::deleteOne( $resourceType, $id );
            }
            $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$this->name}/";
            http\Http::redirect( $loc );
        }
    }

    /**
     * Generic function to delete several objects from database.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app.
     * 
     * @param string  $resourceType  Name of the class of the object we want to delete.
     * 
     * @todo Throw an exception if class $resourceType doesn't exist
     * @todo Prevent this function to be executed if method is not POST
     * @todo Handle the case that the request is sent with AJAX.
     */
    public function do_deleteSeveral() {
        $resourceType = $this->className;
        $cos = Citrus::getInstance();
        if ( isset($_POST['delete'] ) ) {
            $ids = $_POST['delete'];
            if ( is_array( $ids ) ) {
                if ( class_exists( $resourceType ) ) {
                    \core\Citrus\data\Model::deleteSeveral( $resourceType, $ids );
                }
            }
        }
        $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$this->name}/";
        http\Http::redirect( $loc );
    }

}