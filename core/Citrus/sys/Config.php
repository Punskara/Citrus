<?php

namespace core\Citrus\sys;

class Config {

    static public function appPath( $name ) {
        return CTS_APPS_PATH . $name;
    }

    static public function appViewPath( $name ) {
        return CTS_APPS_PATH . '/' . $name . CTS_VIEW_DIR . '/';
    }

    static public function appClassName( $app ) {
        return  str_replace( '/', '\\', 
                    CTS_APPS_DIR . '/' . 
                    $app . '/' . 
                    ucfirst( $app ) . 'App'
                );
    }

    static public function controllerClassName( $app, $controller ) {
        return  str_replace( '/', '\\', 
                    CTS_APPS_DIR . '\\' . 
                    $app . CTS_CTRL_DIR . '\\' . 
                    ucfirst( $controller ) . "Controller"
                );
    }
}