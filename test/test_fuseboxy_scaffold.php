<?php
class TestFuseboxyScaffold extends UnitTestCase {


	function __construct() {
		global $fusebox;
		// load library
		if ( !class_exists('Framework') ) {
			include __DIR__.'/utility-scaffold/framework/1.0.2/fuseboxy.php';
		}
		if ( !class_exists('F') ) {
			include __DIR__.'/utility-scaffold/framework/1.0.2/F.php';
		}
		// unit test mode
		Framework::$mode = Framework::FUSEBOX_UNIT_TEST;
		// run essential process
		Framework::createAPIObject();
		Framework::loadConfig();
		$fusebox->config['appPath'] = dirname(__DIR__).'/app/';
		$fusebox->controller = 'unitTest';
		Framework::setMyself();
		// load library
		include __DIR__.'/utility-scaffold/phpquery/0.9.5/phpQuery.php';
		include dirname(__DIR__).'/lib/redbeanphp/4.3.3/rb.php';
		R::setup('sqlite:'.__DIR__.'/unit_test.db');
		R::freeze(false);
	}


	function resetScaffoldConfig() {
		global $scaffold;
		$scaffold = array(
			'beanType' => 'unittestbean',
			'layoutPath' => __DIR__.'/utility-scaffold/layout.php',
		);
	}


	function test__defaultConfig() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'emptyRow';
		// check default permission
		self::resetScaffoldConfig();
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( $scaffold['allowNew'] );
		$this->assertTrue( $scaffold['allowEdit'] );
		$this->assertTrue( $scaffold['allowToggle'] );
		$this->assertFalse( $scaffold['allowDelete'] );
		$this->assertFalse( $scaffold['allowSort'] );
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__index() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'index';
		// create dummy records
		self::resetScaffoldConfig();
		for ($i=0; $i<10; $i++) {
			$bean = R::dispense($scaffold['beanType']);
			$bean->import(array(
				'name' => "FooBar #{$i}",
				'disabled' => ($i%2),
				'seq' => ($i*10)
			));
			$this->assertTrue( R::store($bean) );
		}
		// default breadcrumb
		self::resetScaffoldConfig();
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and strtolower($arguments['breadcrumb'][0]) == $scaffold['beanType'] );
		unset($output, $arguments);
		// custom breadcrumb
		self::resetScaffoldConfig();
		$arguments['breadcrumb'] = array('Unit Test', 'Listing', 'All');
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and $arguments['breadcrumb'][0] == 'Unit Test' );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and $arguments['breadcrumb'][1] == 'Listing' );
		$this->assertTrue( isset($arguments['breadcrumb'][2]) and $arguments['breadcrumb'][2] == 'All' );
		unset($output, $arguments);
		// check number of rows
		self::resetScaffoldConfig();
		$scaffold['allowToggle'] = true;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-header')->length == 1 );
		$this->assertTrue( pq('.scaffold-row')->length == 10 );
		$this->assertTrue( pq('.scaffold-btn-enable')->length == 5 );
		$this->assertTrue( pq('.scaffold-btn-disable')->length == 5 );
		// non-existing table
		// ===> should pass and nothing happen
		self::resetScaffoldConfig();
		$scaffold['beanType'] = 'unknown';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-row')->length );
		// config {listFilter} in string
		self::resetScaffoldConfig();
		$scaffold['allowToggle'] = true;
		$scaffold['listFilter'] = 'disabled = 0';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-row')->length == 5 );
		$this->assertTrue( pq('.scaffold-btn-disable')->length == 5 );
		$this->assertFalse( pq('.scaffold-btn-enable')->length );
		// config {listFilter} in array
		self::resetScaffoldConfig();
		$scaffold['allowToggle'] = true;
		$scaffold['listFilter'] = array('disabled = ?', array(true));
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-row')->length == 5 );
		$this->assertTrue( pq('.scaffold-btn-enable')->length == 5 );
		$this->assertFalse( pq('.scaffold-btn-disable')->length );
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case to avoid it
	function test__index__enableAllFeatures() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'index';
		// create dummy records
		self::resetScaffoldConfig();
		for ($i=0; $i<10; $i++) {
			$bean = R::dispense($scaffold['beanType']);
			$bean->import(array(
				'name' => "FooBar #{$i}",
				'disabled' => ($i%2),
				'seq' => ($i*10)
			));
			$this->assertTrue( R::store($bean) );
		}
		// enable all features
		self::resetScaffoldConfig();
		$scaffold['editMode'] = 'inline';
		$scaffold['allowNew'] = true;
		$scaffold['allowEdit'] = true;
		$scaffold['allowDelete'] = true;
		$scaffold['allowToggle'] = true;
		$scaffold['allowSort'] = true;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-btn-new')->length );
		$this->assertTrue( pq('.scaffold-btn-edit')->length );
		$this->assertTrue( pq('.scaffold-btn-delete')->length );
		$this->assertTrue( pq('.scaffold-btn-enable')->length );
		$this->assertTrue( pq('.scaffold-btn-disable')->length );
		$this->assertTrue( pq('.scaffold-btn-sort')->length );
		$this->assertFalse( pq('.scaffold-btn-quick-new')->length );
		// quick new button
		self::resetScaffoldConfig();
		$scaffold['editMode'] = 'modal';
		$scaffold['allowNew'] = true;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-btn-new')->length );
		$this->assertTrue( pq('.scaffold-btn-quick-new')->length );
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case to avoid it
	function test__index__disableAllFeatures() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'index';
		// create dummy records
		self::resetScaffoldConfig();
		for ($i=0; $i<10; $i++) {
			$bean = R::dispense($scaffold['beanType']);
			$bean->import(array(
				'name' => "FooBar #{$i}",
				'disabled' => ($i%2),
				'seq' => ($i*10)
			));
			$this->assertTrue( R::store($bean) );
		}
		// disable all features
		self::resetScaffoldConfig();
		$scaffold['allowNew'] = false;
		$scaffold['allowEdit'] = false;
		$scaffold['allowDelete'] = false;
		$scaffold['allowToggle'] = false;
		$scaffold['allowSort'] = false;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-new')->length );
		$this->assertFalse( pq('.scaffold-btn-edit')->length );
		$this->assertFalse( pq('.scaffold-btn-delete')->length );
		$this->assertFalse( pq('.scaffold-btn-enable')->length );
		$this->assertFalse( pq('.scaffold-btn-disable')->length );
		$this->assertFalse( pq('.scaffold-btn-sort')->length );
		$this->assertFalse( pq('.scaffold-btn-quick-new')->length );
		// clean-up
		R::wipe($scaffold['beanType']);
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
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/id was not specified/i', $output);
		unset($output, $arguments);
		// existing record
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-row')->length == 1 );
		unset($output, $doc, $arguments);
		// non-existing record
		self::resetScaffoldConfig();
		$arguments['id'] = -1;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-row')->length );
		unset($output, $doc, $arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case to avoid it
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
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-btn-edit')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-delete')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-disable')->length == 1 );
		unset($output, $doc, $arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case to avoid it
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
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-edit')->length );
		$this->assertFalse( pq('.scaffold-btn-delete')->length );
		$this->assertFalse( pq('.scaffold-btn-disable')->length );
		unset($output, $doc, $arguments);
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
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/id was not specified/i', $output);
		unset($output, $arguments);
		// default breadcrumb
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and strtolower($arguments['breadcrumb'][0]) == $scaffold['beanType'] );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and strtolower($arguments['breadcrumb'][1]) == 'edit' );
		unset($output, $arguments);
		// custom breadcrumb
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		$arguments['breadcrumb'] = array('UNIT TEST', 'EDIT', 'FOO BAR');
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and $arguments['breadcrumb'][0] == 'UNIT TEST' );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and $arguments['breadcrumb'][1] == 'EDIT' );
		$this->assertTrue( isset($arguments['breadcrumb'][2]) and $arguments['breadcrumb'][2] == 'FOO BAR' );
		unset($output, $arguments);
		// inline edit
		// ===> must be ajax-request
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'inline';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
		unset($output, $doc, $arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal edit
		// ===> must be ajax-request
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'modal';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
		unset($output, $doc, $arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// classic edit (in separate page)
		// ===> non-ajax-request
		self::resetScaffoldConfig();
		$scaffold['editMode'] = 'classic';
		$arguments['id'] = $id;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
		unset($output, $doc, $arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case to avoid it
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
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq("[name='data[id]']")->val() == $id );
		unset($output, $doc, $arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal edit : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'modal';
		$arguments['id'] = $id;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq("[name='data[id]']")->val() == $id );
		unset($output, $doc, $arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// classic edit : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'classic';
		$arguments['id'] = $id;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq("[name='data[id]']")->val() == $id );
		unset($output, $doc, $arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__new() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'new';
		// default breadcrumb
		self::resetScaffoldConfig();
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and strtolower($arguments['breadcrumb'][0]) == $scaffold['beanType'] );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and strtolower($arguments['breadcrumb'][1]) == 'new' );
		unset($output, $arguments);
		// custom breadcrumb
		self::resetScaffoldConfig();
		$arguments['breadcrumb'] = array('Unit Test', 'New', '*');
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and $arguments['breadcrumb'][0] == 'Unit Test' );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and $arguments['breadcrumb'][1] == 'New' );
		$this->assertTrue( isset($arguments['breadcrumb'][2]) and $arguments['breadcrumb'][2] == '*' );
		unset($output, $arguments);
		// classic : allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-inline-edit")->length == 1 );
		$this->assertTrue( pq("form[action]")->length == 1 );
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($output, $doc, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal : allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'modal';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-edit")->length == 1 );
		$this->assertTrue( pq("form[action]")->length == 1 );
		$this->assertTrue( pq("form[data-toggle='ajax-submit']")->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-save')->length == 1 );
		$this->assertTrue( pq('.scaffold-btn-close')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($output, $doc, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// check input field type
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['editMode'] = 'classic';
		$scaffold['fieldConfig'] = array(
			'myOutput' => array('format' => 'output'),
			'myText' => array('format' => 'text', 'placeholder' => 'Please enter here', 'required' => true),
			'myTextArea' => array('format' => 'textarea'),
			'myDropDown' => array('options' => array('abc'=>'ABC', 'xyz'=>'XYZ'), 'default' => 'xyz'),
			'myRadio' => array('format' => 'radio', 'options' => array('a'=>'A','b'=>'B','c'=>'C')),
			'myCheckBox' => array('format' => 'checkbox', 'options' => array('x'=>'X','y'=>'Y','z'=>'Z')),
			'myOneToMany' => array('format' => 'one-to-many', 'options' => array('A','B','C','D','E')),
			'myManyToMany' => array('format' => 'many-to-many', 'options' => array('X','Y','Z')),
			'myHtmlReadonly' => array('format' => 'wysiwyg', 'readonly' => true),
			'myHtmlEditor' => array('format' => 'wysiwyg'),
			'myDefault' => array('default' => '999', 'readonly' => true),
			'myValue' => array('default' => 'abc', 'value' => 'xyz'),
		);
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq("[name='data[myOutput]']")->length );
		$this->assertTrue( pq("[name='data[myText]']")->length == 1 );
		$this->assertTrue( pq("[name='data[myText]']")->is('input[type=text]') );
		$this->assertTrue( pq("[name='data[myText]']")->attr('placeholder') == 'Please enter here' );
		$this->assertTrue( pq("[name='data[myText]']")->is('[required]') );
		$this->assertTrue( pq("[name='data[myText]']")->not('[readonly]') );
		$this->assertTrue( pq("[name='data[myTextArea]']")->length == 1 );
		$this->assertTrue( pq("[name='data[myTextArea]']")->is('textarea') );
		$this->assertTrue( pq("[name='data[myDropDown]']")->length == 1 );
		$this->assertTrue( pq("[name='data[myDropDown]']")->is('select') );
		$this->assertTrue( pq("[name='data[myDropDown]']")->val() == 'xyz' );
		$this->assertTrue( pq("[name='data[myCheckBox][]']")->length == 4 );
		$this->assertTrue( pq("[name='data[myCheckBox][]']:eq(0)")->val() == '' );
		$this->assertTrue( pq("[name='data[myCheckBox][]']")->is('input[type=checkbox]') );
		$this->assertTrue( pq("[name='data[myRadio]']")->length == 3 );
		$this->assertTrue( pq("[name='data[myRadio]']")->is('input[type=radio]') );
		$this->assertTrue( pq("[name='data[myOneToMany][]']")->length == 6 );
		$this->assertTrue( pq("[name='data[myOneToMany][]']:eq(0)")->val() == '' );
		$this->assertTrue( pq("[name='data[myOneToMany][]']")->is('input[type=checkbox]') );
		$this->assertTrue( pq("[name='data[myManyToMany][]']")->length == 4 );
		$this->assertTrue( pq("[name='data[myManyToMany][]']:eq(0)")->val() == '' );
		$this->assertTrue( pq("[name='data[myManyToMany][]']")->is('input[type=checkbox]') );
		$this->assertTrue( pq("[id*=input-myHtmlReadonly]")->length == 1 );
		$this->assertTrue( pq("[id*=input-myHtmlReadonly]")->not('[contenteditable]') );
		$this->assertFalse( pq("[name='data[myHtmlReadonly]']")->length );
		$this->assertTrue( pq("[id*=input-myHtmlEditor]")->length == 1 );
		$this->assertTrue( pq("[id*=input-myHtmlEditor]")->is('[contenteditable]') );
		$this->assertTrue( pq("[name='data[myHtmlEditor]']")->length == 1 );
		$this->assertTrue( pq("[name='data[myDefault]']")->length == 1 );
		$this->assertTrue( pq("[name='data[myDefault]']")->is('input[type=text]') );
		$this->assertTrue( pq("[name='data[myDefault]']")->val() == 999 );
		$this->assertTrue( pq("[name='data[myDefault]']")->is('[readonly]') );
		$this->assertTrue( pq("[name='data[myValue]']")->length == 1 );
		$this->assertTrue( pq("[name='data[myValue]']")->val() == 'xyz' );
		// with detail {fieldConfig} specified
		self::resetScaffoldConfig();
		$scaffold['editMode'] = 'classic';
		$scaffold['fieldConfig'] = array(
			'name' => array('format' => 'textarea'),
			'disabled' => array('format' => 'hidden'),
			'seq' => array('format' => 'date'),
		);
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq("[name='data[name]']")->length == 1 );
		$this->assertTrue( pq("[name='data[disabled]']")->length == 1 );
		$this->assertTrue( pq("[name='data[seq]']")->length == 1 );
		$this->assertFalse( pq("[name='data[1]'],[name='data[2]'],[name='data[3]']")->length );  // should not have any dummy field
		$this->assertTrue( pq("[name='data[name]']")->is('textarea') );
		$this->assertTrue( pq("[name='data[disabled]']")->is('[type=hidden]') );
		$this->assertFalse( pq("[name='data[seq]']")->is('[type=date]') );
		$this->assertTrue( pq("[name='data[seq]']")->is('[type=number]') );  // field {seq} must be corrected into [type=number]
		$this->assertTrue( isset($scaffold['fieldConfig']['id']) );  // field-config of {id} was auto-created
		$this->assertTrue( $scaffold['fieldConfig']['id']['readonly'] );  // field {id} must be read-only
		// non-exist table & no {fieldConfig} specified
		// ===> only specify field name
		self::resetScaffoldConfig();
		$scaffold['beanType'] = 'unknown';
		$scaffold['editMode'] = 'classic';
		$scaffold['fieldConfig'] = array(
			'title',
			'speaker',
			'remark',
			'photo',
		);
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq("[name='data[title]']")->length == 1 );
		$this->assertTrue( pq("[name='data[speaker]']")->length == 1 );
		$this->assertTrue( pq("[name='data[remark]']")->length == 1 );
		$this->assertTrue( pq("[name='data[photo]']")->length == 1 );
		$this->assertFalse( pq("[name='data[1]'],[name='data[2]'],[name='data[3]']")->length );  // should not have any dummy field
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	// php bug : scaffold config cannot reset clearly
	// ===> create another test case to avoid it
	function test__new__notAllowSave() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'new';
		// classic : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-inline-edit")->length == 1 );
		$this->assertFalse( pq("form[action]")->length );
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq('.scaffold-btn-cancel')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($output, $doc, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// modal : not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		$scaffold['editMode'] = 'modal';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq(".scaffold-edit")->length == 1 );
		$this->assertFalse( pq("form[action]")->length );
		$this->assertFalse( pq('.scaffold-btn-save')->length );
		$this->assertTrue( pq('.scaffold-btn-close')->length == 1 );
		$this->assertTrue( empty(pq("[name='data[id]']")->val()) );
		unset($output, $doc, $_SERVER['HTTP_X_REQUESTED_WITH']);
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
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
	// ===> create another test case to avoid it
	function test__quickNew__notAllowSave() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'quick_new';
		// not allow save
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = false;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
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
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/toggle is not allowed/i', $output);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse($bean->disabled);
		// missing parameter : no [id] specified
		self::resetScaffoldConfig();
		$scaffold['allowToggle'] = true;
		$arguments['id'] = null;
		$arguments['disabled'] = null;
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/id was not specified/i', $output);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse($bean->disabled);
		unset($output, $arguments);
		// missing parameter : no [disabled] specified
		self::resetScaffoldConfig();
		$arguments['id'] = $id;
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/argument \[disabled\] is required/i', $output);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse($bean->disabled);
		unset($output, $arguments);
		// successfully disable
		self::resetScaffoldConfig();
		$scaffold['allowToggle'] = true;
		$arguments['id'] = $id;
		$arguments['disabled'] = 1;
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertTrue($hasRedirect);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue($bean->disabled);
		unset($output, $arguments);
		// successfully enable
		self::resetScaffoldConfig();
		$scaffold['allowToggle'] = true;
		$arguments['id'] = $id;
		$arguments['disabled'] = 0;
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertTrue($hasRedirect);
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertFalse($bean->disabled);
		unset($output, $arguments);
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
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/data were not submitted/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );  // no record created
		unset($output, $arguments);
		// check create record
		self::resetScaffoldConfig();
		$scaffold['allowNew'] = true;
		$arguments['data'] = array(
			'alias' => 'foobar',
			'name' => 'Foo BAR',
			'seq' => 999,
		);
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertTrue($hasRedirect);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );  // new record created
		$bean = R::findOne($scaffold['beanType']);
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->alias == 'foobar' and $bean->name == 'Foo BAR' and $bean->seq == 999 );
		unset($output, $arguments);
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
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertTrue($hasRedirect);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );  // no new record
		$bean = R::load($scaffold['beanType'], $arguments['data']['id']);
		$this->assertTrue( $arguments['data']['id'] == $bean->id );
		$this->assertTrue( $bean->alias == 'XYZ' and $bean->name == 'Ab Cd, Efg' );
		$this->assertTrue( empty($bean->seq) );
		unset($output, $arguments);
		// check not allow create
		self::resetScaffoldConfig();
		$scaffold['allowNew'] = false;
		$arguments['data'] = array(
			'alias' => 'abc',
			'name' => 'xyz',
			'seq' => 111,
		);
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/create record not allowed/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		unset($output, $arguments);
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
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/update record not allowed/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		$this->assertTrue( $bean->alias != 'aaa-bbb-ccc' and $bean->name != 'XXX YYY ZZZ' and $bean->seq != 222 );
		unset($output, $arguments);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__save__checkboxField() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'save';
		// create dummy record
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'alias' => 'unit-test',
			'name' => 'Unit Test',
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// save record
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['fieldConfig'] = array(
			'alias' => array('format' => 'text'),
			'name' => array('format' => 'text'),
			"multiple" => array('format' => 'checkbox'),
		);
		$arguments['data'] = array(
			'alias' => 'foo-bar',
			'name' => 'Foo Bar',
			"multiple" => array('A','B','C','x','y','z'),
		);
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		// check page response
		$this->assertTrue($hasRedirect);
		// check base record
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( $bean->alias == 'foo-bar' );
		$this->assertTrue( $bean->name == 'Foo Bar' );
		$this->assertTrue( $bean->multiple = 'A|B|C|x|y|z');
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__save__oneToMany() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'save';
		// create dummy record
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'alias' => 'unit-test',
			'name' => 'Unit Test',
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// create dummy records at associated table (one-to-many)
		$childBeanType = $scaffold['beanType'].'one2many';
		$childBeanIDs = array();
		for ($i=0; $i<10; $i++) {
			$childBean = R::dispense($childBeanType);
			$childBean->import(array(
				$scaffold['beanType'].'_id' => null,
				'name' => "Child Bean #{$i}",
			));
			$childBeanIDs[] = R::store($childBean);
			$this->assertTrue( $childBeanIDs[count($childBeanIDs)-1] );
		}
		// check associates before save
		$tmp = R::findAll($childBeanType, 'ORDER BY NAME');
		$bIndex = 0;
		foreach ( $tmp as $b ) {
			$this->assertFalse( $b["{$scaffold['beanType']}_id"] );
			$this->assertPattern("/Child Bean #{$bIndex}/", $b->name);
			$bIndex++;
		}
		// save children beans
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['fieldConfig'] = array(
			'alias' => array('format' => 'text'),
			'name' => array('format' => 'text'),
			"{$childBeanType}_id" => array('format' => 'one-to-many'),
		);
		$arguments['data'] = array(
			'alias' => 'foo-bar',
			'name' => 'Foo Bar',
			"{$childBeanType}_id" => $childBeanIDs,
		);
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		// check page response
		$this->assertTrue($hasRedirect);
		// check base record
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( $bean->alias == 'foo-bar' );
		$this->assertTrue( $bean->name == 'Foo Bar' );
		$propertyName = 'own'.ucfirst($childBeanType);
		$this->assertTrue( count($bean->{$propertyName}) == count($childBeanIDs) );
		foreach ( $bean->{$propertyName} as $b ) {
			$this->assertTrue( $b["{$scaffold['beanType']}_id"] == $bean->id );
			$this->assertPattern("/Child Bean #/", $b->name);
		}
		// check associates after save
		$tmp = R::findAll($childBeanType, 'ORDER BY NAME');
		$bIndex = 0;
		foreach ( $tmp as $b ) {
			$this->assertTrue( $b["{$scaffold['beanType']}_id"] == $bean->id );
			$this->assertPattern("/Child Bean #{$bIndex}/", $b->name);
			$bIndex++;
		}
		// clean-up
		R::wipe($scaffold['beanType']);
		R::wipe($childBeanType);
	}


	function test__save__manyToMany() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'save';
		// create dummy record
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'alias' => 'unit-test',
			'name' => 'Unit Test',
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// create dummy records at associated table
		$anotherBeanType = $scaffold['beanType'].'many2many';
		$anotherBeanIDs = array();
		for ($i=0; $i<10; $i++) {
			$anotherBean = R::dispense($anotherBeanType);
			$anotherBean->import(array(
				'name' => "Another Bean #{$i}",
			));
			$anotherBeanIDs[] = R::store($anotherBean);
			$this->assertTrue( $anotherBeanIDs[count($anotherBeanIDs)-1] );
		}
		// save related beans
		self::resetScaffoldConfig();
		$scaffold['allowEdit'] = true;
		$scaffold['fieldConfig'] = array(
			'alias' => array('format' => 'text'),
			'name' => array('format' => 'text'),
			"{$anotherBeanType}_id" => array('format' => 'many-to-many'),
		);
		$arguments['data'] = array(
			'alias' => 'foo-bar',
			'name' => 'Foo Bar',
			"{$anotherBeanType}_id" => $anotherBeanIDs,
		);
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		// check page response
		$this->assertTrue($hasRedirect);
		// check base record
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( $bean->alias == 'foo-bar' );
		$this->assertTrue( $bean->name == 'Foo Bar' );
		// check base record
		$bean = R::load($scaffold['beanType'], $id);
		$this->assertTrue( $bean->alias == 'foo-bar' );
		$this->assertTrue( $bean->name == 'Foo Bar' );
		$propertyName = 'shared'.ucfirst($anotherBeanType);
		$this->assertTrue( count($bean->{$propertyName}) == count($anotherBeanIDs) );
		foreach ( $bean->{$propertyName} as $b ) {
			$this->assertPattern("/Another Bean #/", $b->name);
		}
		// check associates after save
		$tmp = R::findAll($scaffold['beanType'].'_'.$anotherBeanType, "ORDER BY {$anotherBeanType}_id");
		$bIndex = 0;
		foreach ( $tmp as $b ) {
			$this->assertTrue( $b["{$scaffold['beanType']}_id"] == $bean->id );
			$this->assertTrue( $b["{$anotherBeanType}_id"] == $anotherBeanIDs[$bIndex] );
			$bIndex++;
		}
		// clean-up
		R::wipe($scaffold['beanType']);
		R::wipe($anotherBeanType);
		R::wipe($scaffold['beanType'].'_'.$anotherBeanType);
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
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/delete is not allowed/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		// no id specified
		self::resetScaffoldConfig();
		$scaffold['allowDelete'] = true;
		$arguments['id'] = null;
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
			$output = $e->getMessage();
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/id was not specified/i', $output);
		$this->assertTrue( R::count($scaffold['beanType']) == 1 );
		unset($output, $arguments);
		// successfully delete
		self::resetScaffoldConfig();
		$scaffold['allowDelete'] = true;
		$arguments['id'] = $id;
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertTrue($hasRedirect);
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );
		unset($output, $arguments);
		// delete non-existing record
		// ===> nothing happen (no error)
		// ===> redirect to index page (when normal request)
		self::resetScaffoldConfig();
		$scaffold['allowDelete'] = true;
		$arguments['id'] = -1;
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertTrue($hasRedirect);
		$this->assertTrue( R::count($scaffold['beanType']) == 0 );
		unset($output, $arguments);
		// delete in ajax-request
		// ===> no redirect & show nothing
		self::resetScaffoldConfig();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$scaffold['allowDelete'] = true;
		$arguments['id'] = 999;
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
		}
		$this->assertFalse($hasRedirect);
		$this->assertTrue( trim($output) == '' );
		unset($output, $arguments, $_SERVER['HTTP_X_REQUESTED_WITH']);
		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__uploadFile() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'upload_file';
		// missing config : fusebox-config (uploadDir)
		self::resetScaffoldConfig();
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$scaffold['fieldConfig'] = array( 'foobar' => array('format' => 'file') );
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/configuration \$fusebox->config\[\"uploadDir\"\] is required/i', $output);
		unset($output, $arguments);
		// missing config : fusebox-config (uploadBaseUrl)
		self::resetScaffoldConfig();
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$scaffold['fieldConfig'] = array( 'foobar' => array('format' => 'file') );
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/configuration \$fusebox->config\[\"uploadBaseUrl\"\] is required/i', $output);
		unset($output, $arguments, $fusebox->config['uploadDir']);
		// missing parameter : uploaderID & fieldName
		self::resetScaffoldConfig();
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$json = json_decode($output);
		$this->assertTrue($json);
		$this->assertFalse($json->success);
		$this->assertPattern('/argument \[uploaderID\] is required/i', $json->msg);
		$this->assertPattern('/argument \[fieldName\] is required/i', $json->msg);
		unset($output, $json);
		// missing data : file name passed by {uploaderID}
		self::resetScaffoldConfig();
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		$arguments['uploaderID'] = 'foobar_uploader_123456789';
		$arguments['fieldName'] = 'foobar';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$json = json_decode($output);
		$this->assertTrue($json);
		$this->assertFalse($json->success);
		$this->assertPattern("/data of \[{$arguments['uploaderID']}\] was not submitted/i", $json->msg);
		unset($output, $json);
		// missing field-config
		self::resetScaffoldConfig();
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		$arguments['uploaderID'] = 'foobar_uploader_123456789';
		$arguments['fieldName'] = 'foobar';
		$arguments[$arguments['uploaderID']] = 'unit_test_photo.jpg';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$json = json_decode($output);
		$this->assertTrue($json);
		$this->assertFalse($json->success);
		$this->assertPattern('/field config for \[foobar\] is required/i', $json->msg);
		unset($output, $json, $arguments);
		// invalid field-config
		self::resetScaffoldConfig();
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		$arguments['uploaderID'] = 'foobar_uploader_123456789';
		$arguments['fieldName'] = 'foobar';
		$arguments[$arguments['uploaderID']] = 'unit_test_photo.jpg';
		$scaffold['fieldConfig'] = array( 'foobar' => array('format' => 'checkbox') );
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$json = json_decode($output);
		$this->assertTrue($json);
		$this->assertFalse($json->success);
		$this->assertPattern('/field \[foobar\] must be \[format=file\]/i', $json->msg);
		unset($output, $json, $arguments);
		// upload file successfully
		// ===> should have directory created
		// ===> response should have uploaded file
		self::resetScaffoldConfig();
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		$arguments['uploaderID'] = 'foobar_uploader_123456789';
		$arguments['fieldName'] = 'foobar';
		$arguments[$arguments['uploaderID']] = 'unit_test_photo.jpg';
		$scaffold['libPath'] = dirname($fusebox->config['appPath']).'/lib/';
		$scaffold['fieldConfig'] = array( 'foobar' => array('format' => 'file') );
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$json = json_decode($output);
		$this->assertTrue($json);
		$this->assertTrue($json->success);
		$this->assertTrue( isset($json->baseUrl) );
		$this->assertTrue( isset($json->fileUrl) );
		$this->assertPattern('/'.preg_quote("/{$scaffold['beanType']}/{$arguments['fieldName']}/", '/').'/', $json->baseUrl);
		$this->assertPattern('/'.preg_quote("/{$scaffold['beanType']}/{$arguments['fieldName']}/unit_test_photo", '/').'/', $json->fileUrl);
		$this->assertTrue( is_dir("{$fusebox->config['uploadDir']}/{$scaffold['beanType']}/") );
		$this->assertTrue( is_dir("{$fusebox->config['uploadDir']}/{$scaffold['beanType']}/{$arguments['fieldName']}/") );
		unset($output, $arguments, $fusebox->config['uploadDir'], $fusebox->config['uploadBaseUrl']);
		// file extension check (UNDER CONSTRUCTION)

		// file size check (UNDER CONSTRUCTION)

		// clean-up
		R::wipe($scaffold['beanType']);
	}


	function test__removeExpiredFile() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'remove_expired_file';
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		// missing parameter
		self::resetScaffoldConfig();
		try {
			$hasError = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
		}
		$this->assertTrue($hasError);
		$this->assertPattern('/argument \[fieldName\] is required/i', $output);
		unset($output);
		// define essential param
		$arguments['fieldName'] = 'poster';
		$arguments['uploadDir'] = "{$fusebox->config['uploadDir']}/{$scaffold['beanType']}/{$arguments['fieldName']}/";
		// create dummy records
		self::resetScaffoldConfig();
		for ($i=0; $i<5; $i++) {
			$bean = R::dispense($scaffold['beanType']);
			$bean->import(array(
				'name' => "Foo Bar #{$i}",
				'poster' => "{$fusebox->config['uploadBaseUrl']}/{$scaffold['beanType']}/{$arguments['fieldName']}/poster_{$i}.png",
			));
			$this->assertTrue( R::store($bean) );
		}
		// remove expired file successfully
		self::resetScaffoldConfig();
		try {
			$hasError = false;
			include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasError = ( $e->getCode() == Framework::FUSEBOX_ERROR );
		}
		$this->assertFalse($hasError);
		$hasFile = false;
		foreach ( glob($arguments['uploadDir']."*.*" ) as $filePath ) {
			$hasFile = true;
			$this->assertTrue( pathinfo($filePath, PATHINFO_EXTENSION) == 'DELETED' );
		}
		$this->assertTrue($hasFile);
		unset($output);
		// clean-up
		foreach ( glob($arguments['uploadDir']."*.*" ) as $filePath ) {
			rename($filePath, substr($filePath, 0, strlen($filePath)-8));
		}
		R::wipe($scaffold['beanType']);
	}


}