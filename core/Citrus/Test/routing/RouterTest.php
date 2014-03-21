<?php
namespace test\routing;
use \core\Citrus\routing\Router;
use \core\Citrus\routing\Route;

class RouterTest extends \PHPUnit_Framework_TestCase {
	public function testMap( $value = '' ) {
		$uri 	= "/foo/bar/test";
		$rule 	= "/:app/:controller/:action";
		$target = Array(
			"app" 		 => "test_app",
			"controller" => "test_controller",
			"action" 	 => "test_action",
		);
		$conditions = Array();

		$a = new Router( "/", "" );

		$a->map( $rule, $target, $conditions );

		$this->assertContainsOnlyInstancesOf( 
			'\core\Citrus\routing\Route',
			$a->routes
		);
	}

	public function testExecute() {
		$uri 	= "/foo/bar/test";
		$rule 	= "/:app/:controller/:action";
		$target = Array(
			"app" 		 => "test_app",
			"controller" => "test_controller",
			"action" 	 => "test_action",
		);
		$conditions = Array();

		$route = new Route( $rule, $uri, $target, $conditions );

		$a = new Router( "/", $uri );

		$a->map( $rule, $target, $conditions );
		$a->execute();

		$this->assertEquals( $a->route, $route );
	}

	public function testGetRouteURL() {
		$uri 	= "/foo/bar/test";
		$rule 	= "/:app/:controller/:action";
		$target = Array(
			"app" 		 => "test_app",
			"controller" => "test_controller",
			"action" 	 => "test_action",
		);
		$conditions = Array();

		$route = new Route( $rule, $uri, $target, $conditions );

		$a = new Router( "/", $uri );

		$a->map( $rule, $target, $conditions );
		$a->execute();

		$this->assertEquals( $a->getRouteURL(), $rule );
	}
}