<?php
class TestFuseboxyScaffold extends UnitTestCase {


	function __construct() {
		$GLOBALS['FUSEBOX_UNIT_TEST'] = true;
		// load library
		if ( !class_exists('Framework') ) {
			include dirname(__FILE__).'/utility-scaffold/framework/1.0.1/fuseboxy.php';
		}
		if ( !class_exists('F') ) {
			include dirname(__FILE__).'/utility-scaffold/framework/1.0.1/F.php';
		}
		// run essential process
		global $fusebox;
		Framework::createAPIObject();
		Framework::loadDefaultConfig();
		$fusebox->config['appPath'] = dirname(dirname(__FILE__)).'/app/';
		$fusebox->controller = 'unitTest';
		Framework::setMyself();
		// load database library
		include dirname(dirname(__FILE__)).'/lib/redbeanphp/4.3.3/rb.php';
		R::setup('sqlite:'.dirname(dirname(dirname(__FILE__))).'/unit_test.db');
		R::freeze(false);
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
		unset($fusebox, $arguments);
		R::wipe($scaffold['beanType']);
	}


	function test__index() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__row() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__edit() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__new() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__quickNew() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__toggle() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'toggle';
		// create dummy record
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow toggle
		$scaffold['allowToggle'] = false;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/toggle is not allowed/i', $output);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse( $bean->disabled );
		unset($scaffold['allowToggle']);
		// missing parameter : no [id] specified
		$scaffold['allowToggle'] = true;
		$arguments['id'] = null;
		$arguments['disabled'] = null;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/id was not specified/i', $output);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse( $bean->disabled );
		// missing parameter : no [disabled] specified
		$arguments['id'] = $id;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/argument \[disabled\] is required/i', $output);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse( $bean->disabled );
		unset($scaffold['allowToggle'], $arguments['id'], $arguments['disabled']);
		// successfully disable
		$scaffold['allowToggle'] = true;
		$arguments['id'] = $id;
		$arguments['disabled'] = 1;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
			$hasRedirect = preg_match('/FUSEBOX-REDIRECT/i', $output);
		}
		$this->assertTrue( $hasRun );
		$this->assertTrue( $hasRedirect );
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( $bean->disabled );
		unset($scaffold['allowToggle'], $arguments['id'], $arguments['disabled']);
		// successfully enable
		$scaffold['allowToggle'] = true;
		$arguments['id'] = $id;
		$arguments['disabled'] = 0;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
			$hasRedirect = preg_match('/FUSEBOX-REDIRECT/i', $output);
		}
		$this->assertTrue( $hasRun );
		$this->assertTrue( $hasRedirect );
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse( $bean->disabled );
		unset($scaffold['allowToggle'], $arguments['id'], $arguments['disabled']);
		// clean-up
		unset($fusebox, $arguments);
		R::wipe($scaffold['beanType']);
	}


	function test__save() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'save';
		// check no data
		$arguments['data'] = array();
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/data were not submitted/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );  // no record created
		$arguments['data'] = null;
		// check create record
		$scaffold['allowNew'] = true;
		$arguments['data'] = array(
			'alias' => 'foobar',
			'name' => 'Foo BAR',
			'seq' => 999,
		);
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/FUSEBOX-REDIRECT/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );  // new record created
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
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/FUSEBOX-REDIRECT/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );  // no new record
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
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
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
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
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
		/***** (UNDER CONSTRUCTION) *****/

		// check saving many-to-many
		/***** (UNDER CONSTRUCTION) *****/

		// clean-up
		unset($fusebox, $arguments);
		R::wipe($scaffold['beanType']);
	}


	function test__delete() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'delete';
		// create dummy record
		$bean = R::dispense($scaffold['beanType']);
		$bean['name'] = 'foo bar';
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow delete
		$scaffold['allowDelete'] = false;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/delete is not allowed/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		unset($scaffold['allowDelete']);
		// no id specified
		$scaffold['allowDelete'] = true;
		$arguments['id'] = null;
		try {
			$hasRun = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
		}
		$this->assertTrue( $hasRun );
		$this->assertPattern('/id was not specified/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		unset($scaffold['allowDelete'], $arguments['id']);
		// successfully delete
		$scaffold['allowDelete'] = true;
		$arguments['id'] = $id;
		try {
			$hasRun = false;
			$hasRedirect = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
			$hasRedirect = preg_match('/FUSEBOX-REDIRECT/i', $output);
		}
		$this->assertTrue( $hasRun );
		$this->assertTrue( $hasRedirect );
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );
		unset($scaffold['allowDelete'], $arguments['id']);
		// delete non-existing record
		// ===> nothing happen (no error)
		// ===> redirect to index page (when normal request)
		$scaffold['allowDelete'] = true;
		$arguments['id'] = 999;
		try {
			$hasRun = false;
			$hasRedirect = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasRun = true;
			$output = $e->getMessage();
			$hasRedirect = preg_match('/FUSEBOX-REDIRECT/i', $output);
		}
		$this->assertTrue( $hasRun );
		$this->assertTrue( $hasRedirect );
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );
		unset($scaffold['allowDelete'], $arguments['id']);
		// delete in ajax-request
		// ===> no redirect & show nothing
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$scaffold['allowDelete'] = true;
		$arguments['id'] = 999;
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = preg_match('/FUSEBOX-REDIRECT/i', $output);
		}
		$this->assertFalse( $hasRedirect );
		$this->assertTrue( trim($output) == '' );
		unset($scaffold['allowDelete'], $arguments['id'], $_SERVER['HTTP_X_REQUESTED_WITH']);
		// clean-up
		unset($fusebox, $arguments);
		R::wipe($scaffold['beanType']);
	}


	function test__uploadFile() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__uploadFileProgress() {
		/***** (UNDER CONSTRUCTION) *****/
	}


}