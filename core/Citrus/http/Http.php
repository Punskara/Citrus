<?php
/*
.---------------------------------------------------------------------------.
|  Software: Citrus PHP Framework                                           |
|   Version: 1.0.2                                                            |
|   Contact: devs@citrus-project.net                                        |
|      Info: http://citrus-project.net                                      |
|   Support: http://citrus-project.net/documentation/                       |
| ------------------------------------------------------------------------- |
|   Authors: Rémi Cazalet                                                   |
|          : Nicolas Mouret                                                 |
|   Founder: Studio Caramia                                                 |
|  Copyright (c) 2008-2014, Studio Caramia. All Rights Reserved.            |
| ------------------------------------------------------------------------- |
|   For the full copyright and license information, please view the LICENSE |
|   file that was distributed with this source code.                        |
'---------------------------------------------------------------------------'
*/

/**
 * @package Citrus\http
 * @subpackage Citrus\http\Http
 * @author Rémi Cazalet <remi@caramia.fr>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */



namespace core\Citrus\http;
use core\Citrus\Citrus;

/**
 * This class provides a redirection function
 */
class Http {
   
    /**
     * Redirects browser using the header() function
     *
     * @param string  $url  Target url
     * @param string  $message  A message to memorize in session
     * @param array  $args  Various parameters to pass to the url.
     * 
     * @todo Improve the use of $args, and get rid of this old 'extraParams' array to
     * get something cleaner. 
     */
    static public function redirect( $url, $message = null, $args = null ) {
        $extraParams = '';
        if ( isset( $args['extraParams'] ) ) {
            $extraParams .= '?' . $args['extraParams'];
        }
        
        if ( substr( $url, 0, 1 ) == '/' && substr( CITRUS_PROJECT_URL, strlen( CITRUS_PROJECT_URL ) - 1, 1 ) ) {
            $location = substr( CITRUS_PROJECT_URL, 0, strlen( CITRUS_PROJECT_URL ) - 1 );
            $location .= $url . $extraParams;
        } else {
            $location = CITRUS_PROJECT_URL . $url . $extraParams;
        }
        header( "location:$location" );
        exit;
    }
}