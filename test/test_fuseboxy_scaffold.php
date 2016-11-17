<?php
class TestFuseboxyScaffold extends UnitTestCase {


	function __construct() {
		$GLOBALS['FUSEBOX_UNIT_TEST'] = true;
		// load library
		include dirname(__FILE__).'/utility-scaffold/framework/1.0/fuseboxy.php';
		include dirname(__FILE__).'/utility-scaffold/framework/1.0/F.php';
		// run essential process
		global $fusebox;
		framework__setFuseboxAPI();
		framework__loadDefaultConfig();
		$fusebox->config['appPath'] = dirname(dirname(__FILE__)).'/app/';
		$fusebox->config['autoLoad'] = array(
			dirname(dirname(__FILE__)).'/lib/redbeanphp/4.3.3/rb.php',
			dirname(__FILE__).'/utility-scaffold/config/rb_config.php',
		);
		framework__autoLoad();
		$fusebox->controller = 'unitTest';
		framework__setMyself();
		// define scaffold default config
		global $scaffold;
		$scaffold = array(
			'beanType' => 'unittestbean',
			'layoutPath' => dirname(__FILE__).'/utility-scaffold/layout.php',
		);
	}


	function test__config() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'emptyRow';
		// check default permission
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/php error/i', $output);
		$this->assertTrue( $scaffold['allowNew'] );
		$this->assertTrue( $scaffold['allowEdit'] );
		$this->assertTrue( $scaffold['allowToggle'] );
		$this->assertFalse( $scaffold['allowDelete'] );
		$this->assertFalse( $scaffold['allowSort'] );
		// clean-up
		unset($fusebox->action, $arguments);
		R::exec('DROP TABLE '.$scaffold['beanType']);
	}


	function test__index() {
		$this->assertTrue(true);
	}


	function test__row() {
		$this->assertTrue(true);
	}


	function test__edit() {
		$this->assertTrue(true);
	}


	function test__new() {
		$this->assertTrue(true);
	}


	function test__quickNew() {
		$this->assertTrue(true);
	}


	function test__toggle() {
		$this->assertTrue(true);
	}


	function test__save() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'save';
		// check no data
		$arguments['data'] = array();
		try {
			$hasRun = false;
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/data were not submitted/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );
		$arguments['data'] = null;
		// check create record
		$scaffold['allowNew'] = true;
		$arguments['data'] = array(
			'alias' => 'foobar',
			'name' => 'Foo BAR',
			'seq' => 999,
		);
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		$bean = R::findOne($scaffold['beanType']);
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->alias == 'foobar' and $bean->name == 'Foo BAR' and $bean->seq == 999 );
		$arguments['data'] = null;
		// check update record
		$scaffold['allowEdit'] = true;
		$arguments['data'] = array(
			'id' => $bean->id,
			'alias' => 'XYZ',
			'name' => 'Ab Cd, Efg',
			'seq' => null,
		);
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		$bean = R::load($scaffold['beanType'], $arguments['data']['id']);
		$this->assertTrue( $arguments['data']['id'] == $bean->id );
		$this->assertTrue( $bean->alias == 'XYZ' and $bean->name == 'Ab Cd, Efg' );
		$this->assertTrue( empty($bean->seq) );
		$arguments['data'] = null;
		// check not allow create
		$scaffold['allowNew'] = false;
		$arguments['data'] = array(
			'alias' => 'abc',
			'name' => 'xyz',
			'seq' => 111,
		);
		try {
			$hasRun = false;
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/create record not allowed/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		$arguments['data'] = null;
		// check not allow update
		$scaffold['allowEdit'] = false;
		$bean = R::findOne($scaffold['beanType']);
		$arguments['data'] = array(
			'id' => $bean->id,
			'alias' => 'aaa-bbb-ccc',
			'name' => 'XXX YYY ZZZ',
			'seq' => 222,
		);
		try {
			$hasRun = false;
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/update record not allowed/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		$this->assertTrue( $bean->alias != 'aaa-bbb-ccc' and $bean->name != 'XXX YYY ZZZ' and $bean->seq != 222 );
		$arguments['data'] = null;
		// check saving one-to-many


		// check saving many-to-many


		// clean-up
		unset($fusebox->action, $arguments);
		R::exec('DROP TABLE '.$scaffold['beanType']);
	}


	function test__delete() {
		$this->assertTrue(true);
	}


	function test__uploadFile() {
		$this->assertTrue(true);
	}


	function test__uploadFileProgress() {
		$this->assertTrue(true);
	}


}