<?php
class TestFuseboxyScaffold extends UnitTestCase {


	function __construct() {
		global $fusebox;
		// load library
		if ( !class_exists('Framework') ) {
			require_once __DIR__.'/utility-scaffold/framework/1.0.6/fuseboxy.php';
		}
		if ( !class_exists('F') ) {
			require_once __DIR__.'/utility-scaffold/framework/1.0.6/F.php';
		}
		// unit test mode
		Framework::$mode = Framework::FUSEBOX_UNIT_TEST;
		// run essential process
		Framework::createAPIObject();
		Framework::loadConfig();
		$fusebox->config['appPath'] = dirname(__DIR__).'/app/';
		$fusebox->controller = 'unitTest';
		Framework::setMyself();
		// load scaffold library
		include dirname(__DIR__).'/app/model/Scaffold.php';
		include dirname(__DIR__).'/app/model/UUID.php';
		// load database library
		include __DIR__.'/utility-scaffold/phpquery/0.9.5/phpQuery.php';
		include dirname(__DIR__).'/lib/redbeanphp/5.3.1/rb.php';
		include __DIR__.'/utility-scaffold/config/rb_config.php';
	}


	function test__Scaffold__createFolder() {

	}


	function test__Scaffold__createFolder__FTP() {

	}


	function test__Scaffold__createFolder__Local() {

	}


	function test__Scaffold__createFolder__S3() {

	}


	function test__Scaffold__deleteBean() {
		Scaffold::$config = array('beanType' => 'unittest');
		Scaffold::setParamDefault();
		// create dummy record
		$dummy = R::dispense(Scaffold::$config['beanType']);
		$dummy->name = 'foobar';
		$id = R::store($dummy);
		$this->assertTrue($id);
		// proceed to delete
		$result = Scaffold::deleteBean($id);
		$this->assertTrue($result);
		// check record existence
		$bean = R::load(Scaffold::$config['beanType'], $id);
		$this->assertFalse($bean->id);
		// clean-up
		R::wipe(Scaffold::$config['beanType']);
		Scaffold::$config = null;
	}


	function test__Scaffold__fixParam() {
		global $fusebox;
		$fusebox->controller = 'unit';
		$fusebox->action = 'test';
		Scaffold::$config = array(
			'editMode' => 'foobar',
			'fieldConfig' => array(
				'field_Byte' => array('format' => 'file', 'filesize' => '100'),
				'field_KB'   => array('format' => 'file', 'filesize' => '1kb'),
				'field_MB'   => array('format' => 'file', 'filesize' => '2Mb'),
				'field_GB'   => array('format' => 'file', 'filesize' => '3GB'),
				'field_TB'   => array('format' => 'file', 'filesize' => '4tB'),
			),
		);
		Scaffold::fixParam();
		// edit mode
		$this->assertFalse(Scaffold::$config['editMode'] == 'foobar');
		$this->assertTrue(Scaffold::$config['editMode'] == 'inline');
		// file size
		$this->assertTrue(Scaffold::$config['fieldConfig']['field_Byte']['filesize_numeric'] == 100);
		$this->assertTrue(Scaffold::$config['fieldConfig']['field_KB']['filesize_numeric'] == 1024);
		$this->assertTrue(Scaffold::$config['fieldConfig']['field_MB']['filesize_numeric'] == 2*1024*1024);
		$this->assertTrue(Scaffold::$config['fieldConfig']['field_GB']['filesize_numeric'] == 3*1024*1024*1024);
		$this->assertTrue(Scaffold::$config['fieldConfig']['field_TB']['filesize_numeric'] == 4*1024*1024*1024*1024);
		// clean-up
		Scaffold::$config = null;
	}


	function test__Scaffold__getBean() {
		Scaffold::$config = array('beanType' => 'unittest');
		Scaffold::setParamDefault();
		// create dummy record
		$dummy = R::dispense(Scaffold::$config['beanType']);
		$dummy->name = 'foobar';
		$id = R::store($dummy);
		$this->assertTrue($id);
		// empty bean (when nothing specified)
		$emptyBean = Scaffold::getBean();
		$this->assertFalse($emptyBean->id);
		$this->assertTrue($emptyBean->getMeta('type') == 'unittest');
		// existing bean (when id specified)
		$bean = Scaffold::getBean($id);
		$this->assertTrue($bean->id);
		$this->assertTrue($bean->id == $id);
		$this->assertTrue($emptyBean->getMeta('type') == 'unittest');
		// non-existing record (wrong id)
		$wrongBean = Scaffold::getBean(-999);
		$this->assertFalse($wrongBean);
		$this->assertPattern('/record not found/i', Scaffold::error());
		// clean-up
		R::wipe(Scaffold::$config['beanType']);
		Scaffold::$config = null;
	}


	function test__Scaffold__getBeanList() {
		Scaffold::$config = array('beanType' => 'foobar');
		Scaffold::setParamDefault();
		// create dummy records
		for ($i=0; $i<10; $i++) {
			$dummy = R::dispense(Scaffold::$config['beanType']);
			$dummy->name = "foobar ({$i})";
			$dummy->disabled = ( $i%2 == 0 );
			R::store($dummy);
		}
		// no filter specified (rely on default)
		$beans = Scaffold::getBeanList();
		$this->assertTrue(count($beans) == 10);
		// filter as string
		Scaffold::$config['listFilter'] = 'disabled = 0';
		$beans = Scaffold::getBeanList();
		$this->assertTrue(count($beans) == 5);
		Scaffold::$config['listFilter'] = null;
		// filter as array
		Scaffold::$config['listFilter'] = array('name <> ?', array('foobar (0)'));
		$beans = Scaffold::getBeanList();
		$this->assertTrue(count($beans) == 9);
		Scaffold::$config['listFilter'] = null;
		// order ascending
		Scaffold::$config['listOrder'] = 'ORDER BY name ASC';
		$beans = Scaffold::getBeanList();
		$firstBean = array_shift($beans);
		$lastBean  = array_pop($beans);
		$this->assertTrue($firstBean->name == 'foobar (0)');
		$this->assertTrue($lastBean->name  == 'foobar (9)');
		Scaffold::$config['listOrder'] = null;
		// order descending
		Scaffold::$config['listOrder'] = 'ORDER BY name DESC';
		$beans = Scaffold::getBeanList();
		$firstBean = array_shift($beans);
		$lastBean  = array_pop($beans);
		$this->assertTrue($firstBean->name == 'foobar (9)');
		$this->assertTrue($lastBean->name  == 'foobar (0)');
		Scaffold::$config['listOrder'] = null;
		// clean-up
		R::wipe(Scaffold::$config['beanType']);
		Scaffold::$config = null;
	}


	function test__Scaffold__getConnection__FTP() {

	}


	function test__Scaffold__getConnection__S3() {

	}


	function test__Scaffold__getFileList() {

	}


	function test__Scaffold__getFileList__FTP() {

	}


	function test__Scaffold__getFileList__Local() {

	}


	function test__Scaffold__getFileList__S3() {

	}


	function test__Scaffold__parseConnectionString() {


		// clean-up
		$fusebox->config['uploadDir'] = null;
	}


	function test__Scaffold__parseConnectionString__FTP() {
		global $fusebox;
		// failure : missing protocol
		$conn = Scaffold::parseConnectionString__FTP('unit:test@ftp.foobar.net');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Protocol\] is missing from connection string/i', Scaffold::error());
		$conn = Scaffold::parseConnectionString__FTP('://unit:test@ftp.foobar.net');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Protocol\] is missing from connection string/i', Scaffold::error());
		// failure : invalid protocol
		$conn = Scaffold::parseConnectionString__FTP('http://unit:test@ftp.foobar.net');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Protocol\] is invalid/i', Scaffold::error());
		// failure : missing username
		$conn = Scaffold::parseConnectionString__FTP('ftp://ftp.foobar.net');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Username\] is missing from connection string/i', Scaffold::error());
		$conn = Scaffold::parseConnectionString__FTP('ftp://@ftp.foobar.net');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Username\] is missing from connection string/i', Scaffold::error());
		// failure : missing password
		$conn = Scaffold::parseConnectionString__FTP('ftp://unit@ftp.foobar.net');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Password\] is missing from connection string/i', Scaffold::error());
		// failure : missing hostname
		$conn = Scaffold::parseConnectionString__FTP('ftp://unit:test@');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Hostname\] is missing from connection string/i', Scaffold::error());
		// success : no folder
		$conn = Scaffold::parseConnectionString__FTP('ftp://foo:bar@ftp.unit-test.com/');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 'ftp');
		$this->assertTrue($conn['username'] == 'foo');
		$this->assertTrue($conn['password'] == 'bar');
		$this->assertTrue($conn['hostname'] == 'ftp.unit-test.com');
		$this->assertTrue($conn['folder'] == '');
		// success : has folder
		$conn = Scaffold::parseConnectionString__FTP('FTPS://unit:test@ftp.foobar.net/path/to/folder');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 'ftps');
		$this->assertTrue($conn['username'] == 'unit');
		$this->assertTrue($conn['password'] == 'test');
		$this->assertTrue($conn['hostname'] == 'ftp.foobar.net');
		$this->assertTrue($conn['folder'] == 'path/to/folder/');  // has trailing slash
		// success : excessive slash
		$conn = Scaffold::parseConnectionString__FTP('ftp://////username:password@hostname///path/to///folder//////');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 'ftp');
		$this->assertTrue($conn['username'] == 'username');
		$this->assertTrue($conn['password'] == 'password');
		$this->assertTrue($conn['hostname'] == 'hostname');
		$this->assertTrue($conn['folder'] == 'path/to/folder/');
		// parse framework config
		$fusebox->config['uploadDir'] = 'ftp://uid:pwd@svr/dir';
		$conn = Scaffold::parseConnectionString__FTP();
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 'ftp');
		$this->assertTrue($conn['username'] == 'uid');
		$this->assertTrue($conn['password'] == 'pwd');
		$this->assertTrue($conn['hostname'] == 'svr');
		$this->assertTrue($conn['folder'] == 'dir/');
		// parse passed argument instead of config
		$conn = Scaffold::parseConnectionString__FTP('ftps://foo:bar@unit.test.org/UPLOAD/');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 'ftps');
		$this->assertTrue($conn['username'] == 'foo');
		$this->assertTrue($conn['password'] == 'bar');
		$this->assertTrue($conn['hostname'] == 'unit.test.org');
		$this->assertTrue($conn['folder'] == 'UPLOAD/');
		// clean-up
		$fusebox->config['uploadDir'] = null;
	}


	function test__Scaffold__parseConnectionString__S3() {
		global $fusebox;
		// failure : missing protocol
		$conn = Scaffold::parseConnectionString__S3('foo:bar@unit-test-bucket');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Protocol\] is missing from connection string/i', Scaffold::error());
		$conn = Scaffold::parseConnectionString__S3('://foo:bar@unit-test-bucket');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Protocol\] is missing from connection string/i', Scaffold::error());
		// failure : invalid protocol
		$conn = Scaffold::parseConnectionString__S3('ftp://foo:bar@unit-test-bucket');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Protocol\] is invalid/i', Scaffold::error());
		// failure : missing access-key-id
		$conn = Scaffold::parseConnectionString__S3('s3://unit-test-bucket');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Access Key ID\] is missing from connection string/i', Scaffold::error());
		$conn = Scaffold::parseConnectionString__S3('s3://@unit-test-bucket');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Access Key ID\] is missing from connection string/i', Scaffold::error());
		// failure : missing secret-access-key
		$conn = Scaffold::parseConnectionString__S3('s3://foo@unit-test-bucket');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Secret Access Key\] is missing from connection string/i', Scaffold::error());
		// failure : missing bucket
		$conn = Scaffold::parseConnectionString__S3('s3://foo:bar@');
		$this->assertFalse($conn);
		$this->assertPattern('/\[Bucket\] is missing from connection string/i', Scaffold::error());
		// success : no folder
		$conn = Scaffold::parseConnectionString__S3('s3://abcde:12345@unit-test-bucket/');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 's3');
		$this->assertTrue($conn['accessKeyID'] == 'abcde');
		$this->assertTrue($conn['secretAccessKey'] == '12345');
		$this->assertTrue($conn['bucket'] == 'unit-test-bucket');
		$this->assertTrue($conn['folder'] == '');
		// success : has folder
		$conn = Scaffold::parseConnectionString__S3('S3://abcde:12345@unit-test-bucket/path/to/folder');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 's3');
		$this->assertTrue($conn['accessKeyID'] == 'abcde');
		$this->assertTrue($conn['secretAccessKey'] == '12345');
		$this->assertTrue($conn['bucket'] == 'unit-test-bucket');
		$this->assertTrue($conn['folder'] == 'path/to/folder/');  // has trailing slash
		// success : excessive slash
		$conn = Scaffold::parseConnectionString__S3('s3://////abcde:12345@unit-test-bucket///path/to///folder//////');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 's3');
		$this->assertTrue($conn['accessKeyID'] == 'abcde');
		$this->assertTrue($conn['secretAccessKey'] == '12345');
		$this->assertTrue($conn['bucket'] == 'unit-test-bucket');
		$this->assertTrue($conn['folder'] == 'path/to/folder/');
		// parse framework config
		$fusebox->config['uploadDir'] = 's3://uid:pwd@bucket/dir';
		$conn = Scaffold::parseConnectionString__S3();
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 's3');
		$this->assertTrue($conn['accessKeyID'] == 'uid');
		$this->assertTrue($conn['secretAccessKey'] == 'pwd');
		$this->assertTrue($conn['bucket'] == 'bucket');
		$this->assertTrue($conn['folder'] == 'dir/');
		// parse passed argument instead of config
		$conn = Scaffold::parseConnectionString__S3('s3://unit.test:1234567890@foo-bar-bucket/UPLOAD/');
		$this->assertTrue($conn);
		$this->assertTrue($conn['protocol'] == 's3');
		$this->assertTrue($conn['accessKeyID'] == 'unit.test');
		$this->assertTrue($conn['secretAccessKey'] == '1234567890');
		$this->assertTrue($conn['bucket'] == 'foo-bar-bucket');
		$this->assertTrue($conn['folder'] == 'UPLOAD/');
		// clean-up
		$fusebox->config['uploadDir'] = null;
	}


	function test__Scaffold__removeExpiredFile() {
/*
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'remove_expired_file';
		$fusebox->config['uploadDir'] = __DIR__.'/utility-scaffold/upload';
		$fusebox->config['uploadBaseUrl'] = dirname($_SERVER['SCRIPT_NAME']).'/utility-scaffold/upload';
		// missing parameter
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		for ($i=0; $i<5; $i++) {
			$bean = R::dispense($scaffold['beanType']);
			$bean->import(array(
				'name' => "Foo Bar #{$i}",
				'poster' => "{$fusebox->config['uploadBaseUrl']}/{$scaffold['beanType']}/{$arguments['fieldName']}/poster_{$i}.png",
			));
			$this->assertTrue( R::store($bean) );
		}
		// remove expired file successfully
		self::__resetScaffoldConfig();
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
*/
	}


	function test__Scaffold__renameFile() {

	}


	function test__Scaffold__renameFile__FTP() {

	}


	function test__Scaffold__renameFile__Local() {

	}


	function test__Scaffold__renameFile__S3() {

	}


	function test__Scaffold__saveBean() {
		Scaffold::$config = array('beanType' => 'unittest');
		Scaffold::setParamDefault();
		// create new record
		$newID = Scaffold::saveBean(array(
			'alias' => 'foobar',
			'name'  => 'Foo Bar',
		));
		$this->assertTrue($newID);
		$bean = Scaffold::getBean($newID);
		$this->assertTrue($bean->alias == 'foobar');
		// update existing record
		$id = Scaffold::saveBean(array(
			'id'    => $newID,
			'alias' => 'unittest',
			'name'  => 'Unit Test',
		));
		$this->assertTrue($id);
		$bean = Scaffold::getBean($newID);
		$this->assertTrue($bean->alias == 'unittest');
		// update non-existing record
		$wrongID = Scaffold::saveBean(array(
			'id'    => -999,
			'alias' => 'nobody',
			'name'  => 'Nobody',
		));
		$this->assertFalse($wrongID);
		$this->assertPattern('/record not found/i', Scaffold::error());
		// clean-up
		R::wipe(Scaffold::$config['beanType']);
		Scaffold::$config = null;
	}


	function test__Scaffold__setParamDefault() {
		Scaffold::$config = array('beanType' => 'unittest');
		Scaffold::setParamDefault();
		// check param existence
		$this->assertTrue( isset(Scaffold::$config['allowNew']) );
		$this->assertTrue( isset(Scaffold::$config['allowEdit']) );
		$this->assertTrue( isset(Scaffold::$config['allowToggle']) );
		$this->assertTrue( isset(Scaffold::$config['allowDelete']) );
		$this->assertTrue( isset(Scaffold::$config['allowSort']) );
		$this->assertTrue( isset(Scaffold::$config['listFilter']) );
		$this->assertTrue( isset(Scaffold::$config['listOrder']) );
		$this->assertTrue( isset(Scaffold::$config['writeLog']) );
		// check param value
		$this->assertTrue(Scaffold::$config['allowNew']);
		$this->assertTrue(Scaffold::$config['allowEdit']);
		$this->assertTrue(Scaffold::$config['allowToggle']);
		$this->assertFalse(Scaffold::$config['allowDelete']);
		$this->assertFalse(Scaffold::$config['allowSort']);
		$this->assertFalse(Scaffold::$config['writeLog']);
		// clean-up
		R::wipe(Scaffold::$config['beanType']);
		Scaffold::$config = null;
	}


	function test__Scaffold__startUpload() {

	}


	function test__Scaffold__startUpload__FTP() {

	}


	function test__Scaffold__startUpload__Local() {

	}


	function test__Scaffold__startUpload__S3() {

	}


	function test__Scaffold__toggleBean() {
		Scaffold::$config = array('beanType' => 'unittest');
		Scaffold::setParamDefault();
		// create dummy record
		$dummy = R::dispense(Scaffold::$config['beanType']);
		$dummy->name = 'foobar';
		$id = R::store($dummy);
		$this->assertTrue($id);
		// enable record
		$result = Scaffold::toggleBean($id, 1);
		$this->assertTrue($result);
		$bean = Scaffold::getBean($id);
		$this->assertFalse($bean->disabled);
		// disable record
		$result = Scaffold::toggleBean($id, 0);
		$this->assertTrue($result);
		$bean = Scaffold::getBean($id);
		$this->assertTrue($bean->disabled);
		// toggle non-existing record
		$result = Scaffold::toggleBean(-999, 1);
		$this->assertFalse($result);
		$this->assertPattern('/record not found/i', Scaffold::error());
		// clean-up
		R::wipe(Scaffold::$config['beanType']);
		Scaffold::$config = null;
	}


	function test__Scaffold__uploadFile() {

	}


	function test__Scaffold__validateConfig() {
		global $fusebox;
		// missing bean type
		$result = Scaffold::validateConfig();
		$this->assertFalse($result);
		$this->assertPattern('/scaffold config \[beanType\] is required/i', Scaffold::error());
		// invalid bean type
		Scaffold::$config['beanType'] = 'unit_test';
		$result = Scaffold::validateConfig();
		$this->assertFalse($result);
		$this->assertPattern('/cannot contain underscore/i', Scaffold::error());
		// missing layout path
		Scaffold::$config['beanType'] = 'unittest';
		$result = Scaffold::validateConfig();
		$this->assertFalse($result);
		$this->assertPattern('/scaffold config \[layoutPath\] is required/i', Scaffold::error());
		// no need for upload directory (when no file field)
		Scaffold::$config['layoutPath'] = '/foo/bar/layout.php';
		$result = Scaffold::validateConfig();
		$this->assertTrue($result);
		// missing upload directory (when has file field)
		Scaffold::$config['fieldConfig'] = array('myField' => array('format' => 'file'));
		$result = Scaffold::validateConfig();
		$this->assertFalse($result);
		$this->assertPattern('/fusebox config \[uploadDir\] is required/i', Scaffold::error());
		// missing upload base-url (when has file field)
		$fusebox->config['uploadDir'] = '/unit/test/upload/';
		$result = Scaffold::validateConfig();
		$this->assertFalse($result);
		$this->assertPattern('/fusebox config \[uploadBaseUrl\] is required/i', Scaffold::error());
		// all pass!
		$fusebox->config['uploadBaseUrl'] = 'http://www.foobar.com/unit/test/upload/';
		$result = Scaffold::validateConfig();
		$this->assertTrue($result);
		// clean-up
		Scaffold::$config = null;
		$fusebox->config['uploadDir'] = $fusebox->config['uploadBaseUrl'] = null;
	}


	/*function __resetScaffoldConfig() {
		global $scaffold;
		$scaffold = array(
			'beanType' => 'unittestbean',
			'layoutPath' => __DIR__.'/utility-scaffold/layout.php',
		);
	}


	function test__index() {
		global $fusebox;
		global $scaffold;
		$fusebox->action = 'index';
		// create dummy records
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and strtolower($arguments['breadcrumb'][0]) == $scaffold['beanType'] );
		unset($output, $arguments);
		// custom breadcrumb
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$scaffold['beanType'] = 'unknown';
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertFalse( pq('.scaffold-row')->length );
		// config {listFilter} in string
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// missing parameter
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$arguments['id'] = $id;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$doc = phpQuery::newDocument($output);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( pq('.scaffold-row')->length == 1 );
		unset($output, $doc, $arguments);
		// non-existing record
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// allow {edit|delete|toggle}
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow {edit|delete|toggle}
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// missing parameter
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$arguments['id'] = $id;
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and strtolower($arguments['breadcrumb'][0]) == $scaffold['beanType'] );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and strtolower($arguments['breadcrumb'][1]) == 'edit' );
		unset($output, $arguments);
		// custom breadcrumb
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
			'seq' => 999
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// inline edit : not allow save
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		ob_start();
		include dirname(__DIR__).'/app/controller/scaffold_controller.php';
		$output = ob_get_clean();
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue( isset($arguments['breadcrumb'][0]) and strtolower($arguments['breadcrumb'][0]) == $scaffold['beanType'] );
		$this->assertTrue( isset($arguments['breadcrumb'][1]) and strtolower($arguments['breadcrumb'][1]) == 'new' );
		unset($output, $arguments);
		// custom breadcrumb
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean->import(array(
			'name' => 'foo bar',
			'disabled' => 0,
		));
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow toggle
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
		$bean = R::dispense($scaffold['beanType']);
		$bean['name'] = 'foo bar';
		$id = R::store($bean);
		$this->assertTrue($id);
		// not allow delete
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		$this->assertPattern('/Fusebox config \[uploadDir\] is required/i', $output);
		unset($output, $arguments);
		// missing config : fusebox-config (uploadBaseUrl)
		self::__resetScaffoldConfig();
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
		$this->assertPattern('/Fusebox config \[uploadBaseUrl\] is required/i', $output);
		unset($output, $arguments, $fusebox->config['uploadDir']);
		// missing parameter : uploaderID & fieldName
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
		self::__resetScaffoldConfig();
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
	}*/


}