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
 * @package Citrus\data
 * @subpackage Citrus\data\Pager
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\Citrus;
use \core\Citrus\db;

/**
 * This class works with an HydratableQuery object. It generates a pager
 * to use with big lists.
 */

class Pager {
    /**
     * @var string
     */
    public $targetClass;
    
    /**
     * @var integer
     */
    public $startPage = 0;
    
    /**
     * @var integer
     */
    public $limitStart = 0;
    
    /**
     * @var integer
     */
    public $rowsPerPage = 3;
    
    /**
     * @var integer
     */
    public $currentPage = 1;
    
    /**
     * @var integer
     */
    public $nbPages;
    
    /**
     * @var integer
     */
    public $rows;
    
    public $nbRows;
    
    /**
     * @var integer
     */
    public $pagesBeforeCurrent = 3;
    
    /**
     * @var integer
     */
    public $pagesAfterCurrent = 3;
    
    
    /**
     * @var integer
     */
    public $pagesBeforeStart = 3;
    
    /**
     * @var integer
     */
    public $pagesAfterEnd = 3;
    
    /**
     * @var mixed
     */
    protected $obj = null;
    
    /**
     * @var string
     */
    protected $cond = null;

    /**
     * @var string
     */
    protected $location = null;
    
    public $coll;
    /**
     * Contructor.
     *
     * @param string  $targetClass  type of the objects in the list.
     * @param integer  $rowsPerPage  number of rows per page.
     *
     * @throws \Exception if the $targetClass is not a referenced class.
     */
    public function __construct( $targetClass, $rowsPerPage = null, $location = null ) {
        $cos = Citrus::getInstance();
        if ( class_exists( $targetClass ) ) {
            $this->targetClass = $targetClass;
            $this->obj = new $targetClass();
            if ( is_int( $rowsPerPage ) ) {
                $this->rowsPerPage = $rowsPerPage;
            }
            if ( $location ) $this->location = $location;
            $currentPage = $cos->app->controller->request->param( 'page', 'int' );
            if ( $currentPage ) {
                $this->currentPage = $currentPage;
                $this->limitStart = ( $this->currentPage - 1 ) * $this->rowsPerPage;
            } else {
                $this->limitStart = 0;
            }
            $this->nbRows = $this->countRows();
            
        } else throw new \Exception( "Target class $targetClass doesn't exists." );
    }
    
    
    /**
     * Makes the SQL query
     * 
     * @return array $rec the objects fetched by the query;
     */
    public function getResultSet() {
        $rec = Model::selectAll( $this->targetClass, $this );
        return $rec;
    }
    
    /**
     * Makes the SQL query using the $where conditions
     * 
     * @param string  $where  SQL conditions
     * @return array $rec the objects fetched by the query;
     */
    public function getResultSetWhere( $where, $or_where = false, $order = false, $orderType = false ) {
        $this->cond = $where;
        $rec = Model::selectAllWhere( $this->targetClass, $where, $this, $or_where, $order, $orderType );       
        return $rec;
    }
    
    public function setCondition( $where ) {
        $this->cond = $where;
    }
    
    
    /**
     * Displays pagination links
     *
     * @return  string  $html  HTML for links
     */
    public function displayPager( $clean_query = false ) {
        $this->nbPages = ceil( $this->nbRows / $this->rowsPerPage );
        $html = '';
        $url = parse_url( $_SERVER['REQUEST_URI'] );
        
        if ( isset( $url['query'] ) ) {
            $extParam = preg_replace( '/(\&?page=[0-9]+)/', '', $url['query'] );// . '&';
        } else if ( $clean_query ) {
            $extParam = preg_replace( '/(page\/[0-9]+)/', '', $url['path'] );
            // $extParam = '';
        } else $extParam='';
        
        if ( $this->nbPages > 1 ) {
            $html = '<div class="CitrusPager">';
            $html .= ' ';
            $start = 1;
            
            # first we display the back to start and previous buttons
            $html .= $this->renderStartButtons( $extParam );
            
            # then we display the first pages (pagesBeforeStart)
            if ( $this->nbPages > $this->pagesBeforeStart ) {
                $html .= $this->renderPagesLinks( 1, $this->pagesBeforeStart, $extParam );
            } else {
                $html .= $this->renderPagesLinks( 1, $this->nbPages, $extParam );
            }
            
            
            # if the current page is close enough to the start, we just shift the start
            if ( $this->currentPage <= $this->pagesBeforeCurrent + $this->pagesBeforeStart ) {
                $start = $this->pagesBeforeStart + 1;
            } else {
                $start = $this->currentPage - $this->pagesBeforeCurrent;
            }

            if ( $start > $this->pagesBeforeStart + 1 ) {
                 $html .= ' … ';
            }

            # the end of the list : 
            $end = $this->currentPage + $this->pagesAfterCurrent;
            
            if ( $end > $this->nbPages ) {
                $end = $this->nbPages;
            }
            
            $html .= $this->renderPagesLinks( $start, $end, $extParam );


            if ( $end < $this->nbPages - $this->pagesAfterEnd ) {
                 $html .= ' … ';
            }
            
            # last pages
            if ( $end < $this->nbPages ) {
                if ( $this->nbPages - $this->currentPage > $this->pagesAfterEnd + $this->pagesAfterCurrent ) {
                    $html .= $this->renderPagesLinks( $this->nbPages - $this->pagesAfterEnd + 1, $this->nbPages, $extParam );
                } elseif ( $this->nbPages - $this->currentPage > $this->pagesAfterEnd ) {
                    $html .= $this->renderPagesLinks( $this->currentPage + $this->pagesAfterCurrent + 1, $this->nbPages, $extParam );
                }
            }
            
            $html .= $this->renderEndButtons( $extParam );
            
            $html .= '</div>';
        }
        return $html;
    }
    
    
    /**
     * Displays pages links
     *
     * @return  string  $html  HTML for links
     */
    public function renderPagesLinks( $start, $end, $extParam ) {
        $html = '';
        $cos = Citrus::getInstance();
        $search = $cos->app->controller->request->param( 'search', 'string' );
        $order = $cos->app->controller->request->param( 'order', 'string' );
        $orderType = $cos->app->controller->request->param( 'orderType', 'string' );
        if ( !$this->location ) $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$cos->app->controller->name}/{$cos->app->controller->action}.html";
        else $loc = $this->location;
        for ( $i = $start; $i <= $end; $i++ ) {
            if ( $i == $this->currentPage ) {
                $html .= '<span class="currentPage">' . $i . '</span>';
            } else {
                $html .= link_to( 
                    $loc, 
                    $i, array( 'extraParams' => 'page=' . $i . ( $search ? '&search='.$search : '') . ( $order ? '&order='.$order : '') . ( $orderType ? '&orderType='.$orderType : '') )
                 );
            }
            $html .= ' ';
        }
        return $html;
    }
    
    
    /**
     * Displays start button links (first, previous)
     *
     * @return  string  $html  HTML for links
     */
    public function renderStartButtons( $extParam ) {
        $html = '';
        $cos = Citrus::getInstance();
        $search = $cos->app->controller->request->param( 'search', 'string' );
        $order = $cos->app->controller->request->param( 'order', 'string' );
        $orderType = $cos->app->controller->request->param( 'orderType', 'string' );
        $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$cos->app->controller->name}/{$cos->app->controller->action}.html";
        
        if ( $this->currentPage != 1 ) {
            $html .= link_to( 
                $loc, 
                '‹‹', 
                array( 'extraParams' => $extParam . 'page=1'. ( $search ? '&search='.$search : '') . ( $order ? '&order='.$order : '') . ( $orderType ? '&orderType='.$orderType : '') ) 
            );
            $prev = $this->currentPage - 1;
            $html .= link_to( 
                $loc, 
                '‹', 
                array( 'extraParams' => $extParam . 'page=' . $prev. ( $search ? '&search='.$search : '') . ( $order ? '&order='.$order : '') . ( $orderType ? '&orderType='.$orderType : '') ) 
            );
        }
        return $html;
    }
    
    
    /**
     * Displays end of pagination links (next, last)
     *
     * @return  string  $html  HTML for links
     */
    public function renderEndButtons( $extParam ) {
        $html = '';
        $cos = Citrus::getInstance();
        $search = $cos->app->controller->request->param( 'search', 'string' );
        $order = $cos->app->controller->request->param( 'order', 'string' );
        $orderType = $cos->app->controller->request->param( 'orderType', 'string' );
        $loc = CITRUS_PROJECT_URL . "{$cos->app->name}/{$cos->app->controller->name}/{$cos->app->controller->action}.html";
        
        if ( $this->currentPage != $this->nbPages ) {
            $next = $this->currentPage + 1;
            $html .= link_to( 
                $loc, 
                '›', 
                array( 'extraParams' => $extParam . 'page=' . $next. ( $search ? '&search='.$search : '') . ( $order ? '&order='.$order : '') . ( $orderType ? '&orderType='.$orderType : '') ) 
            );
    
            $html .= link_to( 
                $loc, 
                '››', 
                array( 'extraParams' => $extParam . 'page=' . $this->nbPages. ( $search ? '&search='.$search : '') . ( $order ? '&order='.$order : '') . ( $orderType ? '&orderType='.$orderType : '') ) 
            );
        }
        return $html;
    }
    
    
    /**
     * Counts the number of rows of the resultset
     *
     * @return  integer  number of rows.
     */
    public function countRows() {
        $q = new db\SelectQuery();
        $q->columns = array( 'COUNT(*)' );
        $q->table = $this->obj->schema->tableName;
        if ( $this->cond !== null && !is_array( $this->cond ) ) $q->addWhere( $this->cond );
        else if (is_array( $this->cond )) $q->AddORWhere( $this->cond );
        return $q->Execute()->fetchColumn( 0 );
    }
    
    public function prepareCollection() {
        $this->coll = new \core\Citrus\data\ModelCollection( $this->targetClass );
    }

    public function getCollection( $where = Array(), $order = false, $orderType = false ) {
        if ( $this->coll == null ) $this->prepareCollection();

        if ( count( $where ) ) $this->coll->query->addWhere( implode("\nAND ", $where ) );
        if ( $order !== false ) {
            if ( isset( $schema->properties[$order] ) && isset( $schema->properties[$order]['modelType'] ) ) {
                $sch2 = self::getSchema( $schema->properties[$order]['modelType'] );
                $order = explode(' ', $sch2->orderColumn );
                $order = array_shift( $order );
                
                if ( $orderType !== false ) $order .= ' ' . $orderType;
                $this->coll->query->AddOrder( $sch2->tableName . '.' . $order );
            } else {
                if ( $orderType !== false ) {
                    $order .= ' ' . $orderType;
                }
                $this->coll->query->AddOrder( $this->coll->query->table . '.' . $order );
            }
        } else if ( isset( $schema->orderColumn ) ) {
            $order = $schema->orderColumn;
            if ( isset( $schema->orderSort ) ) {
                $order .= ' ' . $schema->orderSort;
            }
            $this->coll->query->AddOrder( $this->coll->query->table . '.' . $order );
        }
        
        $this->nbRows = $this->coll->query->count();
        $this->coll->query->setLimit( $this->limitStart, $this->rowsPerPage );
        $this->coll->fetch();
        // echo $this->coll->query;exit;
        return $this->coll->items;
    }
    
    public function getLocalizableCollection( $search, $columns = Array(), $order = false, $orderType = false ) {
        if ( $this->coll == null ) 
            $this->coll = new LocalizableModelCollection( $this->targetClass );
        
        
        $loc_table = $this->coll->schema->localizable_table;
        $table = $this->coll->schema->tableName;
        $loc_where = $where = Array();
        
        if ( $search != '' ) {
            if ( count( $columns ) > 0 ) foreach ( $columns as $c ) {
                if ( strpos( $c, 'loc:' ) !== false ) {
                    $col_name = str_replace( "loc:", "", $c );
                    $loc_where[] = "`$loc_table`.`$col_name` LIKE '%$search%'";
                }
                else $where[] = "`$table`.`$c` LIKE '%$search%'";
            }
            
            $all_where = Array();
            if ( count( $loc_where ) ) {
                $all_where = array_merge( $all_where, $loc_where );
                // $this->coll->query->addWhere( implode("\nOR ", $loc_where ) );
            }
            
            if ( count( $where ) ) {
                $all_where = array_merge( $all_where, $where );
                // $this->coll->query->addWhere( implode("\nOR ", $where ) );
            }
            if ( count( $all_where ) > 0 ) {
                $this->coll->query->addWhere( implode("\nOR ", $all_where ) );
            }
        }
        $this->coll->query->addLeftJoin( 
            $this->coll->schema->localizable_table, 
            "`$loc_table`.`localizable_id` = `$table`.`id`" 
        );
        
        if ( $order !== false ) {
            if ( isset( $schema->properties[$order] ) && isset( $schema->properties[$order]['modelType'] ) ) {
                $sch2 = self::getSchema( $schema->properties[$order]['modelType'] );
                $order = explode( ' ', $sch2->orderColumn );
                $order = array_shift( $order );
                
                if ( $orderType !== false ) $order .= ' ' . $orderType;
                $this->coll->query->AddOrder( $sch2->tableName . '.' . $order );
            } else {
                if ( $orderType !== false ) {
                    $order .= ' ' . $orderType;
                }
                $this->coll->query->AddOrder( $this->coll->query->table . '.' . $order );
            }
        } else if ( isset( $schema->orderColumn ) ) {
            $order = $schema->orderColumn;
            if ( isset( $schema->orderSort ) ) {
                $order .= ' ' . $schema->orderSort;
            }
            $this->coll->query->AddOrder( $this->coll->query->table . '.' . $order );
        }
        
        $this->nbRows = $this->coll->query->count();
        $this->coll->query->addDistinct();
        $this->coll->query->setLimit( $this->limitStart, $this->rowsPerPage );
        $this->coll->fetch();
        // echo $this->coll->query;exit;
        return $this->coll->items;
    }
}