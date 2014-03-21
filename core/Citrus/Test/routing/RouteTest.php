<?php
namespace test\routing;
use \core\Citrus\routing\Router;
use \core\Citrus\routing\Route;

class RouterTest extends \PHPUnit_Framework_TestCase {
    public function testMatch() {
        $uri    = "/foo/bar/test";
        $rule   = "/:app/:controller/:action";
        $target = Array(
            "app"        => "test_app",
            "controller" => "test_controller",
            "action"     => "test_action",
        );
        $conditions = Array();

        $a = new Route( $rule, $uri, $target, $conditions );

        $this->assertEquals( $a->is_matched, true );
    }

    public function testRegexURL() {
        $condition = "[0-9]+";
        $rule      = "/:app/:controller/:id/action";
        $uri       = "/foo/bar/2/edit";

        
    }
}