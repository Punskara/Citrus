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
 * @version $Id$
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */


namespace core\Citrus\data;
use \core\Citrus\Citrus;

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
    private $obj = null;
    
    /**
     * @var string
     */
    private $cond = null;
    
    
    /**
     * Contructor.
     *
     * @param string  $targetClass  type of the objects in the list.
     * @param integer  $rowsPerPage  number of rows per page.
     *
     * @throws \Exception if the $targetClass is not a referenced class.
     */
    public function __construct( $targetClass, $rowsPerPage = null ) {
        $cos = Citrus::getInstance();
        if ( class_exists( $targetClass ) ) {
            $this->targetClass = $targetClass;
            $this->obj = new $targetClass();
            if ( is_int( $rowsPerPage ) ) {
                $this->rowsPerPage = $rowsPerPage;
            }
            $currentPage = $cos->app->module->ctrl->request->param( 'page', FILTER_VALIDATE_INT );
            if ( $currentPage ) {
                $this->currentPage = $currentPage;
                $this->limitStart = ( $this->currentPage - 1 ) * $this->rowsPerPage;
            } else {
                $this->limitStart = 0;
            }
            
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
    public function getResultSetWhere( $where ) {
    	$this->cond = $where;
        $rec = Model::selectAllWhere( $this->targetClass, $where, $this );
        return $rec;
    }
    
    
    /**
     * Displays pagination links
     *
     * @return  string  $html  HTML for links
     */
    public function displayPager() {
        $nbRows = $this->countRows();
        $this->nbPages = ceil( $nbRows / $this->rowsPerPage );
        $html = '';
        
        $url = parse_url( $_SERVER['REQUEST_URI'] );
        if ( isset( $url['query'] ) ) {
            $extParam = preg_replace( '/(\&?page=[0-9]+)/', '', $url['query'] ) . '&';
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
        for ( $i = $start; $i <= $end; $i++ ) {
            if ( $i == $this->currentPage ) {
                $html .= '<span class="currentPage">' . $i . '</span>';
            } else {
                $html .= link_to( 
                    'index.php?' . $_SERVER['QUERY_STRING'], 
                    $i, array( 'extraParams' => $extParam . 'page=' . $i )
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
        if ( $this->currentPage != 1 ) {
            $html .= link_to( 
                'index.php?' . $_SERVER['QUERY_STRING'], 
                image_tag( 'resultset_first.png', array( 'alt' => '|<', 'title' => 'Début', ) ), 
                array( 'extraParams' => $extParam . 'page=1' ) 
            );
            $prev = $this->currentPage - 1;
            $html .= link_to( 
                'index.php?' . $_SERVER['QUERY_STRING'], 
                image_tag( 'resultset_previous.png', array( 'alt' => '<', 'title' => 'Précédente', ) ), 
                array( 'extraParams' => $extParam . 'page=' . $prev ) 
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
        
        if ( $this->currentPage != $this->nbPages ) {
            $next = $this->currentPage + 1;
            $html .= link_to( 
                'index.php?' . $_SERVER['QUERY_STRING'], 
                image_tag( 'resultset_next.png', array( 'alt' => '>', 'title' => 'Suivante', ) ), 
                array( 'extraParams' => $extParam . 'page=' . $next ) 
            );
    
            $html .= link_to( 
                'index.php?' . $_SERVER['QUERY_STRING'], 
                image_tag( 'resultset_last.png', array( 'alt' => '>|', 'title' => 'Fin', ) ), 
                array( 'extraParams' => $extParam . 'page=' . $this->nbPages ) 
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
        if ( $this->cond !== null ) $q->addWhere( $this->cond );
        return $q->Execute()->fetchColumn( 0 );
    }
}