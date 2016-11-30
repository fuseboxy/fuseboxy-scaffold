<?php
class TestFuseboxyScaffold extends UnitTestCase {


	function __construct() {
		global $fusebox;
		// unit test mode
		$GLOBALS['FUSEBOX_UNIT_TEST'] = true;
		// load library
		if ( !class_exists('Framework') ) {
			include dirname(__FILE__).'/utility-scaffold/framework/1.0.1/fuseboxy.php';
		}
		if ( !class_exists('F') ) {
			include dirname(__FILE__).'/utility-scaffold/framework/1.0.1/F.php';
		}
		// run essential process
		Framework::createAPIObject();
		Framework::loadDefaultConfig();
		$fusebox->config['appPath'] = dirname(dirname(__FILE__)).'/app/';
		$fusebox->controller = 'unitTest';
		Framework::setMyself();
		// load library
		include dirname(__FILE__).'/utility-scaffold/phpquery/0.9.5/phpQuery.php';
		include dirname(dirname(__FILE__)).'/lib/redbeanphp/4.3.3/rb.php';
		R::setup('sqlite:'.dirname(dirname(dirname(__FILE__))).'/unit_test.db');
		R::freeze(false);
	}


	function resetScaffoldConfig() {
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
		self::resetScaffoldConfig();
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/php error/i', $output);
		$this->assertTrue( $scaffold['allowNew'] );
		$this->assertTrue( $scaffold['allowEdit'] );
		$this->assertTrue( $scaffold['allowToggle'] );
		$this->assertFalse( $scaffold['allowDelete'] );
		$this->assertFalse( $scaffold['allowSort'] );
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__index() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__row() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'row';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// missing parameter
		self::resetScaffoldConfig();
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
		unset($arguments);
		// existing record
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-row')->length == 1 );
		unset($arguments);
		// non-existing record
		self::resetScaffoldConfig();
		$arguments['id'] = -1;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-row')->length );
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case instead
	function test__row__allowEditDeleteToggle() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'row';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// allow {edit|delete|toggle}
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		$scaffold['allowEdit'] = true;
		$scaffold['allowDelete'] = true;
		$scaffold['allowToggle'] = true;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-btn-edit')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-delete')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-disable')->length == 1 );
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case instead
	function test__row__notAllowEditDeleteToggle() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'row';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow {edit|delete|toggle}
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		$scaffold['allowEdit'] = false;
		$scaffold['allowDelete'] = false;
		$scaffold['allowToggle'] = false;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-edit')->length );
		$this->assertFalse( pq('.scaffold-btn-delete')->length );
		$this->assertFalse( pq('.scaffold-btn-disable')->length );
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__edit() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'edit';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// missing parameter
		self::resetScaffoldConfig();
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
		unset($arguments);
		// inline edit
		// ===> must be ajax-request
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'inline';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertTrue( pq('.scaffold-inline-edit')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( pq("[name='data[id]']")->val() == $bean->id );
		$this->assertTrue( pq("[name='data[name]']")->val() == $bean->name );
		$this->assertTrue( pq("[name='data[seq]']")->val() == $bean->seq );
		$this->assertTrue( pq("[name='data[disabled]']")->val() == $bean->disabled );
		unset($arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal edit
		// ===> must be ajax-request
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'modal';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-close')->length == 1 );
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( pq("[name='data[id]']")->val() == $bean->id );
		$this->assertTrue( pq("[name='data[name]']")->val() == $bean->name );
		$this->assertTrue( pq("[name='data[seq]']")->val() == $bean->seq );
		$this->assertTrue( pq("[name='data[disabled]']")->val() == $bean->disabled );
		unset($arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// classic edit (in separate page)
		// ===> non-ajax-request
		self::resetScaffoldConfig();
		$scaffold['editMode'] = 'classic';
		$arguments['id'] = $id;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq("form[data-toggle='ajax-submit']")->length );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( pq("[name='data[id]']")->val() == $bean->id );
		$this->assertTrue( pq("[name='data[name]']")->val() == $bean->name );
		$this->assertTrue( pq("[name='data[seq]']")->val() == $bean->seq );
		$this->assertTrue( pq("[name='data[disabled]']")->val() == $bean->disabled );
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case instead
	function test__edit__notAllowSave() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'edit';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// inline edit : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'inline';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq("[name='data[id]']")->val() == $id );
		unset($arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal edit : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'modal';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq("[name='data[id]']")->val() == $id );
		unset($arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// classic edit : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'classic';
		$arguments['id'] = $id;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq("[name='data[id]']")->val() == $id );
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__new() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'new';
		// classic : allow save (no parameter is required)
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-edit")->length == 1 );
		$this->assertTrue( pq("form[action]")->length == 1 );
		$this->assertFalse( pq("form[data-toggle='ajax-submit']")->length );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		// inline : allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'inline';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-inline-edit")->length == 1 );
		$this->assertTrue( pq("form[action]")->length == 1 );
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal : allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'modal';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-edit")->length == 1 );
		$this->assertTrue( pq("form[action]")->length == 1 );
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-close')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case instead
	function test__new__notAllowSave() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'new';
		// classic : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-edit")->length == 1 );
		$this->assertFalse( pq("form[action]")->length );
		$this->assertFalse( pq("form[data-toggle='ajax-submit']")->length );
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		// inline : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'inline';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-inline-edit")->length == 1 );
		$this->assertFalse( pq("form[action]")->length );
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'modal';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-edit")->length == 1 );
		$this->assertFalse( pq("form[action]")->length );
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq('.scaffold-btn-close')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__quickNew() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'quick_new';
		// allow save (no parameter is required)
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq("form[action]")->length == 1 );
		$this->assertTrue( pq('.scaffold-inline-edit')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case instead
	function test__quickNew__notAllowSave() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'quick_new';
		// not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		ob_start();
		include dirname(dirname(__FILE__)).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq("form[action]")->length );
		$this->assertTrue( pq('.scaffold-inline-edit')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__toggle() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'toggle';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow toggle
		self::resetScaffoldConfig();
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
		// missing parameter : no [id] specified
		self::resetScaffoldConfig();
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
		unset($arguments);
		// missing parameter : no [disabled] specified
		self::resetScaffoldConfig();
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
		unset($arguments);
		// successfully disable
		self::resetScaffoldConfig();
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
		unset($arguments);
		// successfully enable
		self::resetScaffoldConfig();
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
		unset($arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__save() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'save';
		// check no data
		self::resetScaffoldConfig();
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
		unset($arguments);
		// check create record
		self::resetScaffoldConfig();
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
		unset($arguments);
		// check update record
		self::resetScaffoldConfig();
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
		unset($arguments);
		// check not allow create
		self::resetScaffoldConfig();
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
		unset($arguments);
		// check not allow update
		self::resetScaffoldConfig();
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
		unset($arguments);
		// check saving one-to-many
		/***** (UNDER CONSTRUCTION) *****/

		// check saving many-to-many
		/***** (UNDER CONSTRUCTION) *****/

		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__delete() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'delete';
		// create dummy record
		self::resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean['name'] = 'foo bar';
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow delete
		self::resetScaffoldConfig();
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
		// no id specified
		self::resetScaffoldConfig();
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
		unset($arguments);
		// successfully delete
		self::resetScaffoldConfig();
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
		unset($arguments);
		// delete non-existing record
		// ===> nothing happen (no error)
		// ===> redirect to index page (when normal request)
		self::resetScaffoldConfig();
		$scaffold['allowDelete'] = true;
		$arguments['id'] = -1;
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
		unset($arguments);
		// delete in ajax-request
		// ===> no redirect & show nothing
		self::resetScaffoldConfig();
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
		unset($arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__uploadFile() {
		/***** (UNDER CONSTRUCTION) *****/
	}


	function test__uploadFileProgress() {
		/***** (UNDER CONSTRUCTION) *****/
	}


}