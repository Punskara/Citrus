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
use \core\Citrus\html\form\Form;

/**
 * Extends Controller to provide model-related actions
 * (e.g. list, edit, save, delete, etc…)
 */
class ObjectController extends Controller {
    public function do_index( $request ) {
        $schema = data\Model::getSchema( $this->className );
        $cos = Citrus::getInstance();
        
        if ( $schema ) {
            $pager_min = 5;
            $pager = new \core\Citrus\data\Pager( $schema->className, $pager_min );

            if ( $request->isXHR ) $this->view->layout = false;

            $origin     = $request->param( 'origin', 'string' );
            $search     = $request->param( 'search', 'string' );
            $order      = $request->param( 'order', 'string' );
            $orderType  = $request->param( 'orderType', 'string' );
            
            $where = array();

            if ( $search !== false ) {
                if ( $origin == "search-form" ) {
                    $this->view = new View( '_index-list' );
                }
                foreach ( $schema->linkColumns as $cols ) {
                    $where[] = "$cols LIKE '%$search%'"; 
                }
            }
            $list = $pager->getCollection( 
                $where,
                isset( $order ) ? $order : false, 
                isset( $orderType ) ? $orderType : false
            );

            $this->view->assign( 'search', $search );
            $this->view->assign( 'order', $order );
            $this->view->assign( 'orderType', $orderType );

            $this->view->assign( 'schema', $schema );
            $this->view->assign( 'list', $list );
            $this->view->assign( 'pager', $pager );
        }
        else Citrus::pageForbidden();
    }

    public function do_edit( $request ) {
        $schema = data\Model::getSchema( $this->className );
        $this->view->layout = !$request->isXHR;
        $id = $request->param( 'id', 'int' );
        if ( $id ) {
            $res = \core\Citrus\data\Model::selectOne( $this->className, $id );
        } else {
            $res = new $this->className();
        }
        $form = Form::generateForm( $res );

        $this->view->assign( 'res', $res );
        $this->view->assign( 'form', $form );
        $this->view->assign( 'schema', $schema );
        $this->view->assign( 'layout', $this->view->layout );
    }


    /**
     * Generic function to save an object in database. Only accessible in POST method.
     * This function saves the object, upload the files if needed ans redirects to the
     * index of the module of the app. If used with AJAX, this will only display "ok".
     */
    public function do_save( $request ) {
        $this->view->layout = !$request->isXHR;
        $report = Array();
        $cos = Citrus::getInstance();
        if ( $request->method != 'POST' ) {
            if ( $cos->debug ) {
                throw new sys\Exception( 'Bad method request' );
            } else {
                Citrus::pageNotFound();
            }
        } else {
            $type = $this->className;

            if ( class_exists( $type ) ) {
                $inst = new $type();
                if ( isset( $_FILES ) ) {
                    foreach ( $_FILES as $name => $file ) {
                        if ( !empty( $file['name'] ) ) {
                            if ( $inst->$name ) {
                                unlink( CITRUS_PATH . 'www/upload' . $inst->$name );
                            }
                            $upld = new kos_http_Uploader( $file );
                            $upld->readFile();
                            $up = $upld->moveFile( CITRUS_PATH . '/www/upload/' );
                            $inst->args[$name] = $inst->$name = '/www/upload/' . $upld->fileName;
                        }
                    }
                }
                $inst->hydrateByFilters();
                $rec = $inst->save();
                $inst->hydrateManyByFilters();
                
                if ( $request->isXHR ) {
                    $this->view = new mvc\View( 'json-response' );
                    $this->view->layout = false;
                    if ( $rec ) {
                        $this->view->assign('status', "success");
                        $response['status'] = "success";
                        $response['return_url'] = $cos->app->getControllerUrl();
                    } else {
                        $response['status'] = "error";
                    }
                    $cos->response->contentType = "application/json";
                    $this->view->assign( 'response', $response );
                } else {
                    $loc = $cos->app->getControllerUrl();
                    http\Http::redirect( $loc );
                }
            } else throw new sys\Exception( "Unknown class '$type'" );
        }

        return;
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
    public function do_delete( $request ) {
        $resourceType = $this->className;
        $cos = Citrus::getInstance();
        $module = $this->module->name;
        $id = $request->param( 'id', 'int' );
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
    public function do_deleteSeveral( $request ) {
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
        if ( !$request->isXHR ) {
            $this->view = null;
            $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$this->name}/";
            http\Http::redirect( $loc );
        } else {
            $this->view = new mvc\View( 'json-response' );
            $this->view->layout = false;
            $this->view->assign( 'response', Array( 
                "return_url" => $cos->app->getControllerUrl() 
            ) );
        }
    }

}