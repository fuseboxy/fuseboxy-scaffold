<?php
class Scaffold {


	// properties : scaffold config
	public static $config;
	// properties : library for corresponding methods
	public static $libPath = array(
		'uploadFile' => __DIR__.'/../../lib/simple-ajax-uploader/2.6.7/extras/Uploader.php',
		'uploadFileProgress' => __DIR__.'/../../lib/simple-ajax-uploader/2.6.7/extras/uploadProgress.php',
	);




	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			create folder at upload directory according to protocol
		</description>
		<io>
			<in>
				<path name="$newFolder" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function createFolder($newFolder) {
		$protocol = self::parseConnectionString(null, 'protocol');
		if ( $protocol === false ) return false;
		// done!
		if ( $protocol == 'ftp' or $protocol == 'ftps' ) return self::createFolder__FTP($newFolder);
		if ( $protocol == 's3' ) return self::createFolder__S3($newFolder);
		return self::createFolder__LocalServer($newFolder);
	}




	// create directory at remote FTP server (when not exists)
	// ===> append directory with folder in connection string (if any)
	public static function createFolder__FTP($newFolder, $connString=null) {
		// connect to server
		$ftpConn = self::getConnection__FTP($connString);
		if ( $ftpConn === false ) return false;
		// parse connection string
		$cs = self::parseConnectionString__FTP($connString);
		if ( $cs === false ) return false;
		// check through each directory
		$dirStack = $cs['folder'];
		$newFolder = explode('/', trim($newFolder, '/'));
		foreach ( $newFolder as $dir ) {
			$dirStack .= !empty($dirStack) ? "/{$dir}" : $dir;
			// create folder remotely (when necessary)
			if ( !file_exists("{$cs['protocol']}://{$cs['username']}:{$cs['password']}@{$cs['hostname']}/{$dirStack}") ) {
				// create folder
				$mkdirResult = ftp_mkdir($ftpConn, $dirStack);
				if ( $mkdirResult === false ) {
					self::$error = "Error occurred while creating folder at FTP server (folder={$dirStack})";
					return false;
				}
				// change folder permission
				// ===> suppress error for Windows server
				// ===> dirty temporary solution...
				$chmodResult = @ftp_chmod($ftpConn, 0766, $dirStack);
			}
		}
		// disconnect...
		ftp_close($ftpConn);
		// done!
		return true;
	}


	// create directory at local server (when not exists)
	public static function createFolder__LocalServer($newFolder) {
		$tmpNewFolder  = F::config('uploadDir');
		$tmpNewFolder .= ( substr($tmpNewFolder, -1) == '/' ) ? '' : '/';
		$tmpNewFolder .= $newFolder;
		if ( !file_exists($tmpNewFolder) ) {
			mkdir($tmpNewFolder, 0766, true);
		}
		return true;
	}


	// create folder at S3 bucket (when not exists)
	// ===> append directory with folder in connection string (if any)
	public static function createFolder__S3($newFolder, $connString=null) {
		// get client for S3 operation
		$s3 = self::getConnection__S3($connString);
		if ( $s3 === false ) return false;
		// parse connection string
		$cs = self::parseConnectionString__S3($connString);
		if ( $cs === false ) return false;
		// fix parameter (remove leading slash & append trailing slash)
		if ( substr($newFolder, 0, 1) == '/' ) $newFolder = substr($newFolder, 1);
		if ( substr($newFolder, -1) != '/' ) $newFolder .= '/';
		// create folder remotely (when necessary)
		if ( !$s3->doesObjectExist($cs['bucket'], $cs['folder'].$newFolder) ) {
			try {
				$putObjectResult = $s3->putObject(array(
					'Bucket' => $cs['bucket'],
					'Key' => $cs['folder'].$newFolder,
					'Body' => '',
					'ACL' => 'public-read',
				));
			} catch (S3Exception $e) {
				self::$error = $e->getMessage();
				return false;
			}
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			remove specific bean
		</description>
		<io>
			<in>
				<number name="$id" />
				<structure name="$config" scope="self">
					<boolean name="writeLog" />
					<string name="beanType" />
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function deleteBean($id) {
		$bean = self::getBean($id);
		// get record value for log (when necessary)
		if ( self::$config['writeLog'] ) $beanBeforeDelete = Bean::export($bean);
		// commit to delete record
		$deleteResult = ORM::delete($bean);
		if ( $deleteResult === false ) {
			self::$error = ORM::error();
			return false;
		}
		// write log (when necessary)
		if ( self::$config['writeLog'] ) {
			$logResult = Log::write(array(
				'action' => 'DELETE_'.self::$config['beanType'],
				'entity_type' => self::$config['beanType'],
				'entity_id' => $id,
				'remark' => Bean::toString($beanBeforeDelete),
			));
			if ( $logResult === false ) {
				self::$error = Log::error();
				return false;
			}
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			obtain field config of specific field
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="~fieldName~" optional="yes" />
				</structure>
				<!-- parameter -->
				<string name="$fieldName" />
			</in>
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldConfig($fieldName) {
		// validation
		if ( empty(self::$config['fieldConfig'][$fieldName]) ) {
			self::$error = "Field config for [{$fieldName}] not found";
			return false;
		}
		// done!
		return self::$config['fieldConfig'][$fieldName];
	}




	/**
	<fusedoc>
		<description>
			convert field name to [name] attribute for <input>
		</description>
		<io>
			<in>
				<string name="$fieldName" example="student_name|student.name" />
			</in>
			<out>
				<structure name="~return~" example="data[student_name]|data[student][name]" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldName2dataFieldName($fieldName) {
		return 'data['.str_replace('.', '][', $fieldName).']';
	}




	/**
	<fusedoc>
		<description>
			convert human-readable file-size string to number
		</description>
		<io>
			<in>
				<string name="$input" example="2MB|110KB" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fileSizeNumeric($input) {
		$kb = 1024;
		$mb = $kb * 1024;
		$gb = $mb * 1024;
		$tb = $gb * 1024;
		// extra unit
		$input = strtoupper(str_replace(' ', '', $input));
		$lastOneDigit = substr($input, -1);
		$lastTwoDigit = substr($input, -2);
		// calculation
		if ( $lastOneDigit == 'T' or $lastTwoDigit == 'TB' ) {
			$result = floatval($input) * $tb;
		} elseif ( $lastOneDigit == 'G' or $lastTwoDigit == 'GB' ) {
			$result = floatval($input) * $gb;
		} elseif ( $lastOneDigit == 'M' or $lastTwoDigit == 'MB' ) {
			$result = floatval($input) * $mb;
		} elseif ( $lastOneDigit == 'K' or $lastTwoDigit == 'KB' ) {
			$result = floatval($input) * $kb;
		} else {
			$result = floatval($input);
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get specific bean (or empty bean)
		</description>
		<io>
			<in>
				<number name="id" optional="yes" comments="create empty object when not specified" />
				<structure name="$config" scope="self">
					<string name="beanType" />
				</structure>
			</in>
			<out>
				<object name="~return~" optional="yes" oncondition="succeed" />
				<boolean name="~return~" value="false" optional="yes" oncondition="fail" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function getBean($id=null) {
		// get empty record when no argument
		if ( empty($id) ) {
			$result = ORM::new(self::$config['beanType']);
		// get specific record with id was specified
		} else {
			$result = ORM::get(self::$config['beanType'], $id);
		}
		// validation
		if ( $result === false ) {
			self::$error = ORM::error();
			return false;
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get number of records matching the filter criteria
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<string name="beanType" />
					<structure name="listFilter">
						<string name="sql" />
						<array name="param" />
					</structure>
				</structure>
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function getBeanCount() {
		$result = ORM::count(self::$config['beanType'], self::$config['listFilter']['sql'], self::$config['listFilter']['param']);
		if ( $result === false ) {
			self::$error = ORM::error();
			return false;
		}
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get all corresponding records
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<string name="beanType" />
					<structure name="listFilter">
						<string name="sql" />
						<array name="param" />
					</structure>
					<string name="listOrder" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getBeanList() {
		$result = ORM::get(self::$config['beanType'], self::$config['listFilter']['sql'].' '.self::$config['listOrder'], self::$config['listFilter']['param']);
		if ( $result === false ) {
			self::$error = ORM::error();
			return false;
		}
		return $result;
	}




	// get ftp connection
	public static function getConnection__FTP($connString=null) {
		$cs = self::parseConnectionString($connString);
		if ( $cs === false ) return false;
		// create connection
		$conn = ftp_connect($cs['hostname']);
		if ( $conn == false ) {
			self::$error = "Cannot connect to FTP server ({$cs['hostname']})";
			return false;
		}
		// login to server
		$loginResult = ftp_login($conn, $cs['username'], $cs['password']);
		if ( $loginResult === false ) {
			self::$error = "Error occurred while logging in FTP server";
			return false;
		}
		// done!
		return $conn;
	}




	/**
	<fusedoc>
		<description>
			get upload client for S3
		</description>
		<io>
			<in>
				<string name="$connString" />
				<structure name="config" scope="$fusebox">
					<string name="httpProxy" optional="yes" />
				</structure>
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function getConnection__S3($connString=null) {
		$cs = self::parseConnectionString($connString);
		if ( $cs === false ) return false;
		// config for factory
		$config = array(
			'credentials' => array('key' => $cs['accessKeyID'], 'secret' => $cs['secretAccessKey']),
			'region' => 'us-east-1',
			'version' => '2006-03-01',
			'http' => array( 'proxy' => F::config('httpProxy') ),
		);
		// create object to retrieve bucket location
		$client = Aws\S3\S3Client::factory($config);
		$bucketLocation = $client->getBucketLocation(array('Bucket' => $cs['bucket']));
		// re-create object with correct region specified
		$config['region'] = $bucketLocation->get('LocationConstraint');
		$client = Aws\S3\S3Client::factory($config);
		// done!
		return $client;
	}




	/**
	<fusedoc>
		<description>
			get list of files in specific directory according to protocol
		</description>
		<io>
			<in>
				<string name="$dir" />
			</in>
			<out>
				<array name="~return~">
					<structure name="+">
						<string name="path" />
						<string name="name" />
						<string name="ext" />
						<datetime name="mtime" />
					</structure>
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getFileList($dir) {
		$protocol = self::parseConnectionString(null, 'protocol');
		if ( $protocol === false ) return false;
		// done!
		if ( $protocol == 'ftp' or $protocol == 'ftps' ) return self::getFileList__FTP($dir);
		if ( $protocol == 's3' ) return self::getFileList__S3($dir);
		return self::getFileList__LocalServer($dir);
	}




	/**
	<fusedoc>
		<description>
			get list of files in specific directory at FTP server
			===> append directory with the folder specified in connection string (if any)
		</description>
		<io>
			<in>
				<string name="$dir" />
				<string name="$connString" optional="yes" />
			</in>
			<out>
				<array name="~return~">
					<structure name="+">
						<string name="path" />
						<string name="name" />
						<string name="ext" />
						<datetime name="mtime" />
					</structure>
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getFileList__FTP($dir, $connString=null) {
		$result = array();
		// connect to server
		$ftpConn = self::getConnection__FTP($connString);
		if ( $ftpConn === false ) return false;
		// parse connection string
		$cs = self::parseConnectionString__FTP($connString);
		if ( $cs === false ) return false;
		// get file list
		$fileList = ftp_nlist($ftpConn, $cs['folder'].$dir);
		$rawFileList = ftp_rawlist($ftpConn, $cs['folder'].$dir);
		// only put file into result container
		foreach ( $fileList as $key => $filePath ) {
			$rawFile = $rawFileList[$key];
			$isDir  = ( substr($rawFile, 0, 1) == 'd' or strpos($rawFile, '<DIR>') !== false );
			$isLink = ( substr($rawFile, 0, 1) == 'l' or strpos($rawFile, '<SYMLINKD>') !== false or strpos($rawFile, '<JUNCTION>') !== false );
			$isFile = !( $isDir or $isLink );
			if ( !$isDir and !$isLink ) {
				$result[] = array(
					'path'  => $filePath,
					'name'  => basename($filePath),
					'ext'   => pathinfo($filePath, PATHINFO_EXTENSION),
					'mtime' => ftp_mdtm($ftpConn, $filePath),
				);
			}
		}
		// disconnect...
		ftp_close($ftpConn);
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get list of files in specific directory at local server
		</description>
		<io>
			<in>
				<string name="$dir" />
			</in>
			<out>
				<array name="~return~">
					<structure name="+">
						<string name="path" />
						<string name="name" />
						<string name="ext" />
						<datetime name="mtime" />
					</structure>
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getFileList__LocalServer($dir) {
		$result = array();
		// go through each file in directory
		foreach ( glob($dir."*.*") as $filePath ) {
			$result[] = array(
				'path'  => $filePath,
				'name'  => basename($filePath),
				'ext'   => pathinfo($filePath, PATHINFO_EXTENSION),
				'mtime' => filemtime($filePath),
			);
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get list of files in specific directory at S3 bucket
			===> append directory with the folder specified in connection string (if any)
		</description>
		<io>
			<in>
				<string name="$dir" />
				<string name="$connString" optional="yes" />
			</in>
			<out>
				<array name="~return~">
					<structure name="+">
						<string name="path" />
						<string name="name" />
						<string name="ext" />
						<datetime name="mtime" />
					</structure>
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getFileList__S3($dir, $connString=null) {
		$result = array();
		// get S3 client
		$s3 = self::getConnection__S3($connString);
		if ( $s3 === false ) return false;
		// parse connection string
		$cs = self::parseConnectionString($connString);
		if ( $cs === false ) return false;
		// fix parameter (remove leading slash & append trailing slash)
		if ( substr($dir, 0, 1) == '/' ) $dir = substr($dir, 1);
		if ( substr($dir, -1) != '/' ) $dir .= '/';
		// obtain objects from bucket
		$iterator = $s3->getIterator('ListObjects', array(
			'Bucket' => $cs['bucket'],
			'Prefix' => $cs['folder'].$dir,
		));
		// only put file into result container (skip directory)
		foreach ( $iterator as $object ) {
			if ( substr($object['Key'], -1) != '/' ) {
				$result[] = array(
					'path' => $object['Key'],
					'name' => basename($object['Key']),
					'ext' => pathinfo($object['Key'], PATHINFO_EXTENSION),
					'mtime' => strtotime($object['LastModified']->jsonSerialize()),
				);
			}
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			obtain all columns of specific table
			===> allow proceed further if table not exists (simply treated as no column)
		</description>
		<io>
			<in>
				<string name="$beanType" />
			</in>
			<out>
				<array name="~return~">
					<string name="+" value="~fieldName~" />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getTableColumns($beanType) {
		$result = ORM::columns($beanType);
		// check any database error
		if ( $result === false and !preg_match('/Base table or view not found/i', ORM::error()) ) {
			self::$error = ORM::error();
			return false;
 		// simply treat as no column (when table not exists)
		} elseif ( empty($result) ) {
			$result = array();
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			assign default and fix value of parameters
		</description>
		<io>
			<in>
				<structure name="$config" scope="self" />
			</in>
			<out>
				<!-- return -->
				<boolean name="~return~" />
				<!-- modified -->
				<structure name="$config" scope="self">
					<string name="retainParam" optional="yes" default="" format="query-string" />
					<boolean name="allowNew" default="true" />
					<boolean name="allowQuick" default="~allowNew~" />
					<boolean name="allowEdit" default="true" />
					<boolean name="allowToggle" default="true" />
					<boolean name="allowDelete" default="false" />
					<structure name="allowSort" default="~allFields~">
						<string name="~fieldName~" value="~fieldNameOrSubQuery~" />
					</structure>
					<boolean name="stickyHeader" default="false" />
					<string name="editMode" default="inline" />
					<string name="modalSize" deafult="lg" />
					<structure name="listFilter">
						<string name="sql" default=" 1 = 1 " />
						<array name="param" default="~emptyArray~" />
					</structure>
					<string name="listOrder" default="ORDER BY (seq,) id" />
					<structure name="fieldConfig">
						<structure name="id">
							<boolean name="readonly" value="true" comments="force {ID} field exists; force readonly" />
						</structure>
						<structure name="~fieldName~">
							<string name="label" comments="derived from field name when not specified or true" />
							<string name="placeholder" comments="derived from field name when true" />
							<string name="inline-label" comments="derived from field name when true" />
							<list name="filetype" comments="for [format=image] field" />
						</structure>
					</structure>
					<structure name="listField" default="~fieldConfig|tableColumns~">
						<string name="~columnList~" value="~columnWidth~" />
					</structure>
					<structure name="modalField">
						<list name="~columnList~" value="~columnWidthList~" optional="yes" delim="|" comments="when key was specified, key is column list and value is column width list" />
						<string name="~line~" optional="yes" example="---" comments="any number of dash(-) or equal(=)" />
						<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
						<string name="~output~" optional="yes" example="~<br />" comments="output content/html directly" />
					</structure>
					<structure name="scriptPath">
						<string name="edit|header|inline_edit|list|row|modal" value="~filePath~" />
					</structure>
					<structure_or_boolean name="pagination" default="false">
						<number name="recordCount" />
						<number name="recordPerPage" />
						<number name="pageVisible" />
					</structure_or_boolean>
				</structure>
				<string name="sortField" scope="$_GET" optional="yes" comments="indicate which label in table header to show the arrow" />
				<string name="sortRule" scope="$_GET" optional="yes" comments="indicate the direction of arrow shown at table header" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig() {
		$tableColumns = self::getTableColumns(self::$config['beanType']);
		if ( $tableColumns === false ) return false;
		// field config : fix & default
		self::$config['fieldConfig'] = self::$config['fieldConfig'] ?? array();
		self::$config['fieldConfig'] = self::initConfig__fixFieldConfig(self::$config['fieldConfig'], $tableColumns);
		if ( self::$config['fieldConfig'] === false ) return false;
		// modal field : fix & default
		self::$config['modalField'] = self::$config['modalField'] ?? array_keys(self::$config['fieldConfig']);
		self::$config['modalField'] = self::initConfig__fixModalField(self::$config['modalField'], [ 'enforceHasID' => true ]);
		if ( self::$config['modalField'] === false ) return false;
		// list field : fix & default
		self::$config['listField'] = self::$config['listField'] ?? array_keys(self::$config['fieldConfig']);
		self::$config['listField'] = self::initConfig__fixListField(self::$config['listField'], [ 'enforceHasID' => true ]);
		if ( self::$config['listField'] === false ) return false;
		// allow-xxx : default
		if ( self::initConfig__defaultPermission() === false ) return false;
		// allow sort : fix
		if ( self::initConfig__fixAllowSort() === false ) return false;
		// edit mode : fix & default
		if ( self::initConfig__fixEditMode() === false ) return false;
		// modal size : default
		if ( empty(self::$config['modalSize']) ) self::$config['modalSize'] = 'lg';
		// sticky header : default
		self::$config['stickyHeader'] = self::$config['stickyHeader'] ?? false;
		// list filter : fix & default
		if ( self::initConfig__fixListFilter() === false ) return false;
		// list order : fix & default
		if ( self::initConfig__fixListOrder() === false ) return false;
		// script path : fix & default
		if ( self::initConfig__fixScriptPath() === false ) return false;
		// write log : default
		self::$config['writeLog'] = self::$config['writeLog'] ?? false;
		// pagination : fix & default
		if ( self::initConfig__fixPagination() === false ) return false;
		// list order : add limit and offset to statement (when pagination)
		if ( self::initConfig__fixListOrderWhenPagination() === false ) return false;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			set default [allowXXX] settings
		</description>
		<io>
			<in />
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<boolean name="allowNew"    default="true" />
					<boolean name="allowQuick"  default="~allowNew~" />
					<boolean name="allowEdit"   default="true" />
					<boolean name="allowSort"   default="true" />
					<boolean name="allowToggle" default="true" />
					<boolean name="allowDelete" default="false" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultPermission() {
		if ( !isset(self::$config['allowNew'])    ) self::$config['allowNew']    = true;
		if ( !isset(self::$config['allowQuick'])  ) self::$config['allowQuick']  = self::$config['allowNew'];
		if ( !isset(self::$config['allowEdit'])   ) self::$config['allowEdit']   = true;
		if ( !isset(self::$config['allowSort'])   ) self::$config['allowSort']   = true;
		if ( !isset(self::$config['allowToggle']) ) self::$config['allowToggle'] = true;
		if ( !isset(self::$config['allowDelete']) ) self::$config['allowDelete'] = false;
		// done!
		return true;
	}



	/**
	<fusedoc>
		<description>
			fix [allowSort] settings
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<boolean name="allowSort" optional="yes" />
					<list name="allowSort" optional="yes" delim="|," />
					<array name="allowSort" optional="yes">
						<string name="+" value="~fieldName~" />
					</array>
				</structure>
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="allowSort" default="~allFields~">
						<string name="~fieldName~" value="~fieldNameOrSubQuery~" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixAllowSort() {
		// param fix : permission (allowSort)
		// ===> convert boolean to all fields
		// ===> convert list to array
		if ( self::$config['allowSort'] === true ) {
			self::$config['allowSort'] = array_keys(self::$config['fieldConfig']);
		} elseif ( self::$config['allowSort'] === false ) {
			self::$config['allowSort'] = array();
		} elseif ( is_string(self::$config['allowSort']) ) {
			self::$config['allowSort'] = str_replace('|', ',', self::$config['allowSort']);
			self::$config['allowSort'] = array_filter(explode(',', self::$config['allowSort']));
		}
		// param fix : allowSort
		// ===> use [fieldName] as key
		// ===> with [fieldName] or [subQuery] as value
		$arr = self::$config['allowSort'];
		self::$config['allowSort'] = array();
		foreach ( $arr as $key => $val ) {
			if ( is_numeric($key) ) self::$config['allowSort'][$val] = "`{$val}`";
			else self::$config['allowSort'][$key] = $val;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			fix [editMode] settings
		</description>
		<io>
			<in />
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<string name="editMode" default="inline" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixEditMode() {
		// param default : edit mode
		if ( empty(self::$config['editMode']) ) self::$config['editMode'] = 'inline';
		// param fix : edit mode
		// ===> enforce normal edit form when not ajax
		if ( F::is('*.edit,*.new') and !F::ajaxRequest() ) self::$config['editMode'] = 'basic';
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			fix [fieldConfig] settings
			===> taking parameters instead of refer to scaffold config
			===> reuse [renderXXX] more easily (without having to specify whole scaffold config)
		</description>
		<io>
			<in>
				<structure name="$fieldConfig" />
					<string name="+" value="~fieldName~" optional="yes" />
				</structure>
				</structure>
				<array name="$tableColumns" optional="yes">
					<string name="+" value="~fieldName~" />
				</array>
			</in>
			<out>
				<structure name="~return~">
					<structure name="id">
						<boolean name="readonly" value="true" comments="force {ID} field exists; force readonly" />
					</structure>
					<structure name="~fieldName~">
						<string name="label" comments="derived from field name when not specified or true" />
						<string name="placeholder" comments="derived from field name when true" />
						<string name="inline-label" comments="derived from field name when true" />
						<list name="filetype" comments="for [format=image] field" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixFieldConfig($fieldConfig, $tableColumns=[]) {
		// param default : field config
		// ===> merge table columns to field config
		// ===> make sure to include all fields of the table
		foreach ( $fieldConfig as $key => $val ) {
			if ( isset($tableColumns[$key]) and !isset($fieldConfig[$key]) ) {
				$fieldConfig[$key] = array();
			}
		}
		// fix param : field config
		// ===> convert numeric key to field name
		$arr = $fieldConfig;
		$fieldConfig = array();
		foreach ( $arr as $key => $val ) $fieldConfig += is_numeric($key) ? array($val=>[]) : array($key=>$val);
		// fix param : field config (id)
		// ===> compulsory
		// ===> must be readonly
		if ( !isset($fieldConfig['id']) ) $fieldConfig['id'] = array();
		$fieldConfig['id']['readonly'] = true;
		// fix param : field config (seq)
		// ===> optional
		// ===> must be number
		if ( isset($fieldConfig['seq']) ) $fieldConfig['seq']['format'] = 'number';
		// param default : field config (disabled)
		// ===> optional
		// ===> default as boolean dropdown (when not specified)
		if ( isset($fieldConfig['disabled']) and empty($fieldConfig['disabled']) ) {
			$fieldConfig['disabled'] = array('options' => array('0' => 'Enable', '1' => 'Disable'));
		}
		// param default : field config (label)
		// param default : field config (placeholder)
		// param default : field config (filetype)
		// param default : field config (filesize)
		foreach ( $fieldConfig as $fieldName => $cfg ) {
			// label : derived from field name
			if ( !isset($cfg['label']) or $cfg['label'] === true ) {
				$fieldConfig[$fieldName]['label'] = implode(' ', array_map(function($word){
					return in_array($word, array('id','url')) ? strtoupper($word) : ucfirst($word);
				}, explode('_', $fieldName)));
			}
			// placeholder : derived from field name
			if ( isset($cfg['placeholder']) and $cfg['placeholder'] === true ) {
				$fieldConfig[$fieldName]['placeholder'] = implode(' ', array_map(function($word){
					return in_array($word, array('id','url')) ? strtoupper($word) : ucfirst($word);
				}, explode('_', $fieldName)));
			}
			// inline-label : derived from field name
			if ( isset($cfg['inline-label']) and $cfg['inline-label'] === true ) {
				$fieldConfig[$fieldName]['inline-label'] = implode(' ', array_map(function($word){
					return in_array($word, array('id','url')) ? strtoupper($word) : ucfirst($word);
				}, explode('_', $fieldName)));
			}
			// filetype : image
			if ( empty($cfg['filetype']) and isset($cfg['format']) and $cfg['format'] == 'image' ) {
				$fieldConfig[$fieldName]['filetype'] = 'gif,jpg,jpeg,png';
			}
			// filetype : file
			if ( empty($cfg['filetype']) and isset($cfg['format']) and $cfg['format'] == 'file' ) {
				$fieldConfig[$fieldName]['filetype'] = 'jpg,jpeg,png,gif,bmp,txt,doc,docx,pdf,ppt,pptx,xls,xlsx';
			}
			// filesize
			if ( empty($cfg['filesize']) and isset($cfg['format']) and in_array($cfg['format'], ['file','image']) ) {
				$fieldConfig[$fieldName]['filesize'] = '10MB';
			}
		}
		// done!
		return $fieldConfig;
	}




	/**
	<fusedoc>
		<description>
			fix [listField] settings
			===> taking parameters instead of refer to scaffold config
			===> reuse [renderXXX] more easily (without having to specify whole scaffold config)
		</description>
		<io>
			<in>
				<structure name="$listField">
					<string name="+" value="~fieldNameList~" />
				</structure>
				<structure name="$options">
					<boolean name="enforceHasID" optional="yes" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<string name="~fieldNameList~" value="~columnWidth~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixListField($listField, $options=[]) {
		// fix param : list field (key)
		// ===> convert numeric key to field name
		$arr = $listField;
		$listField = array();
		foreach ( $arr as $fieldNameList => $columnWidth ) {
			if ( is_numeric($fieldNameList) ) $listField[$columnWidth] = '';
			else $listField[$fieldNameList] = $columnWidth;
		}
		// fix param : enforce ID field
		if ( !empty($options['enforceHasID']) ) {
			$hasID = false;
			foreach ( $listField as $fieldNameList => $val ) if ( in_array('id', explode('|', $fieldNameList)) ) $hasID = true;
			if ( !$hasID ) $listField = array('id' => 60) + $listField;
		}
		// done!
		return $listField;
	}




	/**
	<fusedoc>
		<description>
			fix [listFilter] settings
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="listFilter" optional="yes" />
					<array name="listFilter" optional="yes">
						<string name="0" value="~sql~" />
						<array name="1" value="~param~" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="listFilter">
						<string name="sql" />
						<array name="param" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixListFilter() {
		// default sql statement (when necessary)
		if ( empty(self::$config['listFilter']) ) self::$config['listFilter'] = ' 1 = 1 ';
		// convert string to structure (when necessary)
		if ( is_string(self::$config['listFilter']) ) {
			self::$config['listFilter'] = array('sql' => self::$config['listFilter']);
		}
		// convert array to structure (when necessary)
		if ( is_array(self::$config['listFilter']) and isset(self::$config['listFilter'][0]) ) {
			self::$config['listFilter']['sql'] = self::$config['listFilter'][0];
			unset(self::$config['listFilter'][0]);
		}
		if ( is_array(self::$config['listFilter']) and isset(self::$config['listFilter'][1]) ) {
			self::$config['listFilter']['param'] = self::$config['listFilter'][1];
			unset(self::$config['listFilter'][1]);
		}
		// default empty param (when necessary)
		if ( !isset(self::$config['listFilter']['param']) ) self::$config['listFilter']['param'] = array();
		// validation
		if ( !is_string(self::$config['listFilter']['sql']) ) {
			self::$error = 'SQL statement of [listFilter] is not string';
			return false;
		}
		// fix sql statement (when necessary)
		$firstWord = strtoupper(explode(' ', trim(self::$config['listFilter']['sql']))[0]);
		if ( $firstWord == 'AND' ) self::$config['listFilter']['sql'] = ' 1 = 1 '.self::$config['listFilter']['sql'];
		// fix string param (when necessary)
		if ( is_string(self::$config['listFilter']['param']) or is_numeric(self::$config['listFilter']['param']) ) {
			self::$config['listFilter']['param'] = array(self::$config['listFilter']['param']);
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			fix [listOrder] settings
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="allowSort" />
					<string name="listOrder" />
				</structure>
				<!-- url variable -->
				<string name="sortField" scope="$_GET" optional="yes" />
				<string name="sortRule" scope="$_GET" optional="yes" />
			</in>
			<out>
				<!-- return -->
				<boolean name="~return~" />
				<!-- modified -->
				<structure name="$config" scope="self">
					<string name="listOrder" default="ORDER BY (seq,) id" />
				</structure>
				<string name="sortField" scope="$_GET" optional="yes" comments="indicate which label in table header to show the arrow" />
				<string name="sortRule" scope="$_GET" optional="yes" comments="indicate the direction of arrow shown at table header" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixListOrder() {
		// order by [sortField] in URL (when specified)
		if ( !empty(self::$config['allowSort']) and isset($_GET['sortField']) and isset(self::$config['allowSort'][$_GET['sortField']]) ) {
			self::$config['listOrder'] = 'ORDER BY ';
			// order by [options] sequence (when necessary)
			if ( !empty(self::$config['fieldConfig'][$_GET['sortField']]['options']) ) {
				self::$config['listOrder'] .= "CASE `{$_GET['sortField']}` ";
				foreach ( self::$config['fieldConfig'][$_GET['sortField']]['options'] as $optValue => $optText ) {
					$optValue = str_replace("'", "''", $optValue);
					$optText  = str_replace("'", "''", $optText);
					self::$config['listOrder'] .= "WHEN '{$optValue}' THEN '{$optText}' ";
				}
				self::$config['listOrder'] .= 'END ';
				if ( isset($_GET['sortRule']) ) self::$config['listOrder'] .= $_GET['sortRule'];
				self::$config['listOrder'] .= ', ';
			}
			// sort by column/sub-query
			self::$config['listOrder'] .= self::$config['allowSort'][$_GET['sortField']].' ';
			if ( isset($_GET['sortRule']) ) self::$config['listOrder'] .= $_GET['sortRule'];
		// otherwise, use default [listOrder] in config
		} elseif ( !isset(self::$config['listOrder']) ) {
			self::$config['listOrder'] = 'ORDER BY ';
			if ( isset(self::$config['_columns_']['seq']) ) self::$config['listOrder'] .= 'IFNULL(seq, 9999), ';
			self::$config['listOrder'] .= 'id ';
		}
		// param default : sort field (when necessary)
		// ===> extract from list order
		if ( !isset($_GET['sortField']) ) {
			$tmp = trim(str_replace('ORDER BY ', '', self::$config['listOrder']));
			$tmp = explode(',', $tmp);  // turn {column-direction} list into array
			$tmp = $tmp[0];  // extract first {column-direction}
			$tmp = explode(' ', $tmp);
			$_GET['sortField'] = $tmp[0];  // extract {column}
			if ( isset($tmp[1]) ) $_GET['sortRule'] = $tmp[1];
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			adjust [listOrder] statement when pagination
			// ===> add limit and offset to statement
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="listOrder" />
				</structure>
				<!-- url variable -->
				<boolean name="showAll" scope="$_GET" optional="yes" />
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~' />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<string name="listOrder" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixListOrderWhenPagination() {
		// param fix : list order
		if ( !empty(self::$config['pagination']) and empty($_GET['showAll']) ) {
			$offset = ( !empty($_GET['page']) and $_GET['page'] > 0 ) ? ( ($_GET['page']-1) * self::$config['pagination']['recordPerPage'] ) : 0;
			$limit = self::$config['pagination']['recordPerPage'];
			self::$config['listOrder'] .= " LIMIT {$limit} OFFSET {$offset}";
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			fix [modalField] settings
			===> taking parameters instead of refer to scaffold config
			===> reuse [renderXXX] more easily (without having to specify whole scaffold config)
		</description>
		<io>
			<in>
				<array name="$modalField">
					<string name="+" />
				</array>
				<structure name="$options">
					<boolean name="enforceHasID" optional="yes" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<list name="+" value="~columnList~" optional="yes" delim="|" comments="when no key specified, value is field list" />
					<list name="~columnList~" value="~columnWidthList~" optional="yes" delim="|" comments="when key was specified, key is column list and value is column width list" />
					<string name="~line~" optional="yes" example="---" comments="any number of dash(-) or equal(=)" />
					<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
					<string name="~output~" optional="yes" example="~<br />" comments="output content/html directly" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixModalField($modalField, $options=[]) {
		// fix param : modal field (heading & line & output)
		// ===> append space to make sure it is unique
		// ===> avoid being overridden after convert to key
		foreach ( $modalField as $key => $val ) {
			// only consider numeric key field
			// ===> which means the config is before fixed
			if ( is_numeric($key) and self::parseFieldRow($val, true) != 'fields' ) {
				$modalField[$key] = $val.str_repeat(' ', $key);
			}
		}
		// fix param : modal field (key)
		// ===> convert numeric key to field name
		$arr = $modalField;
		$modalField = array();
		foreach ( $arr as $fieldNameList => $fieldWidthList ) {
			if ( is_numeric($fieldNameList) ) $modalField[$fieldWidthList] = '';
			else $modalField[$fieldNameList] = $fieldWidthList;
		}
		// fix param : enforce ID field
		if ( !empty($options['enforceHasID']) ) {
			$hasID = false;
			foreach ( $modalField as $fieldNameList => $fieldWidthList ) if ( in_array('id', explode('|', $fieldNameList)) ) $hasID = true;
			if ( !$hasID ) $modalField = array('id' => '') + $modalField;
		}
		// done!
		return $modalField;
	}




	/**
	<fusedoc>
		<description>
			fix [pagination] settings
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<boolean name="pagination" optional="yes" />
				</structure>
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure_or_boolean name="pagination" default="false">
						<number name="recordCount" />
						<number name="recordPerPage" />
						<number name="pageVisible" />
					</structure_or_boolean>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixPagination() {
		// default no pagination
		if ( !isset(self::$config['pagination']) ) self::$config['pagination'] = false;
		// fix format
		if ( !empty(self::$config['pagination']) ) {
			self::$config['pagination'] = is_array(self::$config['pagination']) ? self::$config['pagination'] : array();
			self::$config['pagination']['recordCount']   = self::getBeanCount();
			self::$config['pagination']['pageVisible']   = isset(self::$config['pagination']['pageVisible']  ) ? self::$config['pagination']['pageVisible']   : 10;
			self::$config['pagination']['recordPerPage'] = isset(self::$config['pagination']['recordPerPage']) ? self::$config['pagination']['recordPerPage'] : 20;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			fix [scriptPath] settings
		</description>
		<io>
			<in />
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="scriptPath">
						<string name="edit|header|inline_edit|list|row|modal" value="~filePath~" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixScriptPath() {
		foreach ( ['edit','header','inline_edit','list','row','modal'] as $item ) {
			if ( !isset(self::$config['scriptPath']) ) self::$config['scriptPath'] = array();
			if ( !isset(self::$config['scriptPath'][$item]) ) self::$config['scriptPath'][$item] = F::appPath("view/scaffold/{$item}.php");
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			access nested-array value (e.g. data[student][name]) by period-delimited-list (e.g. student.name)
		</description>
		<io>
			<in>
				<list name="$nestedKey" delim="." />
				<array name="$nestedArray" />
			</in>
			<out>
				<mixed name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function nestedArrayGet($nestedKey, $nestedArray) {
		$nestedKey = explode('.', $nestedKey);
		$result = $nestedArray;
		foreach ( $nestedKey as $key ) {
			if ( is_object($result) and isset($result->{$key}) ) $result = $result->{$key};
			elseif ( is_array($result) and isset($result[$key]) ) $result = $result[$key];
			else $result = null;
		}
		return $result;
	}




	/**
	<fusedoc>
		<description>
			parse upload directory config as connection string (when necessary)
			===> s3://xxxxxxxxxx0
		</description>
		<io>
			<in>
				<string name="$connString" optional="yes" default="~uploadDir~" />
				<string name="$key" optional="yes" comments="return value of specific key" />
			</in>
			<out>
				<structure name="~return~" optional="yes" oncondition="when success (key not specified)">
					<!-- S3 -->
					<string name="protocol" value="s3" />
					<string name="accessKeyID" optional="yes" oncondition="s3" />
					<string name="secretAccessKey" optional="yes" oncondition="s3" />
					<string name="bucket" optional="yes" oncondition="s3" />
					<string name="folder" />
					<!-- FTP/FTPS -->
					<string name="protocol" value="ftp|ftps" />
					<string name="username" optional="yes" oncondition="ftp|ftps" />
					<string name="password" optional="yes" oncondition="ftp|ftps" />
					<string name="hostname" optional="yes" oncondition="ftp|ftps" />
					<string name="folder" />
					<!-- Local Server -->
					<string name="protocol" value="local" />
					<string name="folder" />
				</structure>
				<string name="~return~" optional="yes" oncondition="when success (key specified)" />
				<boolean name="~return~" value="false" optional="yes" oncondition="when failure" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseConnectionString($connString=null, $key=null) {
		// check against framework config or passed parameter
		$connString = !empty($connString) ? $connString : F::config('uploadDir');
		// parse according to protocol
		if ( substr($connString, 0, 5) == 's3://' ) {
			$result = self::parseConnectionString__S3($connString);
		} elseif ( substr($connString, 0, 6) == 'ftp://' or substr($connString, 0, 7) == 'ftps://' ) {
			$result = self::parseConnectionString__FTP($connString);
		// no parse for local server
		} else {
			$result = array('protocol' => 'local', 'folder' => $connString);
		}
		// failure...
		if ( $result === false ) {
			return false;
		// success!
		} elseif ( $key === null ) {
			return $result;
		} elseif ( isset($result[$key]) ) {
			return $result[$key];
		// key not found...
		} else {
			$keyList = implode(',', array_keys($result));
			self::$error = "Key [{$key}] is invalid (valid={$keyList})";
			return false;
		}
	}




	/**
	<fusedoc>
		<description>
			parse FTP connection string
		</description>
		<io>
			<in>
				<string name="$connString" optional="yes" />
			</in>
			<out>
				<structure name="~return~" optional="yes" oncondition="when success">
					<string name="protocol" value="ftp|ftps" />
					<string name="username" />
					<string name="password" />
					<string name="hostname" />
					<string name="folder" />
				</structure>
				<boolean name="~return~" value="false" optional="yes" oncondition="when failure" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseConnectionString__FTP($connString=null) {
		// parse framework config or passed parameter
		$connString = !empty($connString) ? $connString : F::config('uploadDir');
		// unify path-delim
		$conn = str_replace('\\', '/', $connString);
		// extract protocol
		$token = '://';
		if ( strpos($conn, $token) !== false ) {
			$protocol = strtolower(substr($conn, 0, strpos($conn, $token)));
			$conn = substr($conn, strlen("{$protocol}{$token}"));
		}
		// dedupe path-delim
		$conn = trim($conn, '/');
		do {
			$conn = str_replace('//', '/', $conn);
		} while ( strpos($conn, '//') !== false );
		// extract credential
		$token = '@';
		if ( strpos($conn, $token) !== false ) {
			$conn = explode($token, $conn, 2);
			$credential = $conn[0];
			$conn = isset($conn[1]) ? $conn[1] : '';
		} else {
			$credential = '';
		}
		$credential = explode(':', $credential, 2);
		$username = isset($credential[0]) ? $credential[0] : '';
		$password = isset($credential[1]) ? $credential[1] : '';
		// extract hostname & folder (if any)
		$conn = explode('/', $conn, 2);
		$hostname = $conn[0];
		$folder = isset($conn[1]) ? $conn[1] : '';
		// validate protocol
		if ( empty($protocol) ) {
			self::$error = "[Protocol] is missing from connection string ({$connString})";
			return false;
		} elseif ( !in_array($protocol, array('ftp','ftps')) ) {
			self::$error = "[Protocol] is invalid ({$protocol})";
			return false;
		// validate credential
		} elseif ( empty($username) ) {
			self::$error = "[Username] is missing from connection string ({$connString})";
			return false;
		} elseif ( empty($password) ) {
			self::$error = "[Password] is missing from connection string ({$connString})";
			return false;
		// validate hostname
		} elseif ( empty($hostname) ) {
			self::$error = "[Hostname] is missing from connection string ({$connString})";
			return false;
		}
		// add trailing slash to folder (when necessary)
		if ( !empty($folder) and substr($folder, -1) != '/' ) {
			$folder .= '/';
		}
		// done!
		return array(
			'protocol' => $protocol,
			'username' => $username,
			'password' => $password,
			'hostname' => $hostname,
			'folder'   => $folder,
		);
	}




	/**
	<fusedoc>
		<description>
			parse S3 connection string
			===> s3://{accessKeyID}:{secretAccessKey}@{bucket}/{folder}
		</description>
		<io>
			<in>
				<string name="$connString" optional="yes" />
			</in>
			<out>
				<structure name="~return~" optional="yes" oncondition="when success">
					<string name="protocol" value="s3" />
					<string name="accessKeyID" />
					<string name="secretAccessKey" />
					<string name="bucket" />
					<string name="folder" />
				</structure>
				<boolean name="~return~" value="false" optional="yes" oncondition="when failure" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseConnectionString__S3($connString=null) {
		// parse framework config or passed parameter
		$connString = !empty($connString) ? $connString : F::config('uploadDir');
		// unify path-delim
		$conn = str_replace('\\', '/', $connString);
		// extract protocol
		$token = '://';
		if ( strpos($conn, $token) !== false ) {
			$protocol = strtolower(substr($conn, 0, strpos($conn, $token)));
			$conn = substr($conn, strlen("{$protocol}{$token}"));
		}
		// dedupe path-delim
		$conn = trim($conn, '/');
		do {
			$conn = str_replace('//', '/', $conn);
		} while ( strpos($conn, '//') !== false );
		// extract credential
		$token = '@';
		if ( strpos($conn, $token) !== false ) {
			$conn = explode($token, $conn, 2);
			$credential = $conn[0];
			$conn = isset($conn[1]) ? $conn[1] : '';
		} else {
			$credential = '';
		}
		$credential = explode(':', $credential, 2);
		$username = isset($credential[0]) ? $credential[0] : '';
		$password = isset($credential[1]) ? $credential[1] : '';
		// extract hostname & folder (if any)
		$conn = explode('/', $conn, 2);
		$hostname = $conn[0];
		$folder = isset($conn[1]) ? $conn[1] : '';
		// validate protocol
		if ( empty($protocol) ) {
			self::$error = "[Protocol] is missing from connection string ({$connString})";
			return false;
		} elseif ( $protocol != 's3' ) {
			self::$error = "[Protocol] is invalid ({$protocol})";
			return false;
		// validate credential
		} elseif ( empty($username) ) {
			self::$error = "[Access Key ID] is missing from connection string ({$connString})";
			return false;
		} elseif ( empty($password) ) {
			self::$error = "[Secret Access Key] is missing from connection string ({$connString})";
			return false;
		// validate hostname
		} elseif ( empty($hostname) ) {
			self::$error = "[Bucket] is missing from connection string ({$connString})";
			return false;
		}
		// add trailing slash to folder (when necessary)
		if ( !empty($folder) and substr($folder, -1) != '/' ) {
			$folder .= '/';
		}
		// done!
		return array(
			'protocol' => $protocol,
			'accessKeyID' => $username,
			'secretAccessKey' => $password,
			'bucket' => $hostname,
			'folder'   => $folder,
		);
	}




	/**
	<fusedoc>
		<description>
			parse row of field-layout (usually field-name-list) and determine its type
		</description>
		<io>
			<in>
				<string name="$fieldRow" />
				<boolean name="$getType" default="false" />
			</in>
			<out>
				<string name="~return~" value="heading|line|output|fields" oncondition="when [getType] is true" />
				<string name="~return~" comments="display row in corresponding format" oncondition="when [getType] is false" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseFieldRow($fieldRow, $getType=false) {
		$fieldRow = trim($fieldRow);
		// heading
		if ( strlen($fieldRow) != strlen(ltrim($fieldRow, '#')) ) {
			$size = 'h'.( strlen($fieldRow) - strlen(ltrim($fieldRow, '#')) );
			$text = trim(ltrim($fieldRow, '#'));
			return $getType ? 'heading' : "<div class='{$size}'>{$text}</div>";
		// direct output
		} elseif ( strlen($fieldRow) and $fieldRow[0] === '~' ) {
			$output = trim(substr($fieldRow, 1));
			return $getType ? 'output' : ( strlen($output) ? "<div>{$output}</div>" : '' );
		// line
		} elseif ( trim($fieldRow, '=-') === '' ) {
			return $getType ? 'line' : '<hr />';
		}
		// fields (render nothing)
		return $getType ? 'fields' : '';
	}




	// remove expired file according to protocol
	public static function removeExpiredFile($fieldName, $uploadDir) {
		// get all records of specific field
		// ===> only required file name
		$nonOrphanFiles = array();
		$arr = ORM::get(self::$config['beanType'], "{$fieldName} IS NOT NULL");
		foreach ( $arr as $item ) if ( !empty($item->{$fieldName}) ) $nonOrphanFiles[] = basename($item->{$fieldName});
		// go through every file in upload directory
		if ( !empty($nonOrphanFiles) ) {
			$fileList = self::getFileList($uploadDir);
			foreach ( $fileList as $file ) {
				// only remove orphan file older than 1 day
				// ===> avoid remove file which ajax-upload by user but not save record yet
				// ===> (do not check lifespan when unit-test)
				$isOrphan = !in_array($file['name'], $nonOrphanFiles);
				$isExpired = ( $file['mtime'] < strtotime(date("-1 day")) or Framework::$mode == Framework::FUSEBOX_UNIT_TEST );
				$isDeleted = ( $file['ext'] == 'DELETED' );
				// archive expired file by appending {.DELETED} extension
				// ===> avoid accidentally removing any precious data
				// ===> (rely on server administrator to remove the {*.DELETE} files explicitly)
				if ( $isOrphan and $isExpired and !$isDeleted ) {
					$renameResult = self::renameFile($file['path'], "{$file['path']}.DELETED");
					if ( !$renameResult ) return false;
				} // if-orphan-expired-deleted
			} // foreach-uploadDir
		} // if-not-empty-nonOrphanFiles
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			rename file at server according to protocol
		</description>
		<io>
			<in>
				<path name="$source" />
				<path name="$destination" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renameFile($source, $destination) {
		$protocol = self::parseConnectionString(null, 'protocol');
		if ( $protocol === false ) return false;
		// done!
		if ( $protocol == 'ftp' or $protocol == 'ftps' ) return self::renameFile__FTP($source, $destination);
		if ( $protocol == 's3' ) return self::renameFile__S3($source, $destination);
		return self::renameFile__LocalServer($source, $destination);
	}




	/**
	<fusedoc>
		<description>
			rename file at FTP server
			===> append source and destination with folder in connection string (if any)
		</description>
		<io>
			<in>
				<path name="$source" />
				<path name="$destination" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renameFile__FTP($source, $destination, $connString=null) {
		$result = array();
		// connect to server
		$ftpConn = self::getConnection__FTP($connString);
		if ( $ftpConn === false ) return false;
		// parse connection string
		$cs = self::parseConectionString__FTP($connString);
		if ( $cs === false ) return false;
		// get file list
		$renameResult = ftp_rename($ftpConn, $cs['folder'].$source, $cs['folder'].$destination);
		if ( $renameResult === false ) {
			self::$error = "Error occurred while renaming expired file on FTP server (source={$source}, destination={$destination}, folder={$cs['folder']})";
		}
		// disconnect...
		ftp_close($ftpConn);
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			rename file at local server
		</description>
		<io>
			<in>
				<string name="$source" />
				<string name="$destination" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renameFile__LocalServer($source, $destination) {
		if ( !rename($filePath, "{$filePath}.DELETED") ) {
			self::$error = "Error occurred while renaming expired file (source={$source}, destination={$destination})";
			return false;
		}
		return true;
	}




	/**
	<fusedoc>
		<description>
			rename file at S3 bucket
			===> append source and destination with folder in connection string (if any)
		</description>
		<io>
			<in>
				<path name="$source" />
				<path name="$destination" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renameFile__S3($source, $destination, $connString=null) {
		// connect to server
		$s3 = self::getConnection__S3($connString);
		if ( $s3 === false ) return false;
		// parse connection string
		$cs = self::parseConnectionString($connString);
		if ( $cs === false ) return false;
		// rename file at remote bucket
		try {
			$s3->copyObject(array(
				'Bucket' => $cs['bucket'],
				'Key'    => $cs['folder'].$destination,
				'CopySource' => $cs['bucket'].'/'.$cs['folder'].$source,
			));
			$s3->deleteObject(array(
				'Bucket' => $cs['bucket'],
				'Key'    => $cs['folder'].$source,
			));
		} catch (S3Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			render edit form according to [modalField & fieldConfig] scaffold config
			===> for [editMode=modal|inline-modal|basic]
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self" optional="yes">
					<string name="editMode" value="modal|inline-modal|basic" comments="for [formType] option" />
					<boolean name="allowEdit" />
					<structure name="scriptPath">
						<string name="inline_edit" />
					</structure>
				</structure>
				<!-- parameters -->
				<array name="$fieldLayout">
					<list name="~columnNameList~" value="~columnWidthList~" optional="yes" delim="|" />
					<string name="~line~" optional="yes" example="---" comments="any number of dash(-) or equal(=)" />
					<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
				</array>
				<structure name="$fieldConfigAll">
					<structure name="~fieldName~" />
				</structure>
				<object name="$bean" />
				<structure name="$options" optional="yes">
					<string name="formType" default="modal" comments="modal|inline-modal|basic" />
					<number name="labelColumn" default="2" comments="column width" />
				</structure>
				<!-- additional exit points (e.g. when custom button specified) -->
				<structure name="$xfa" optional="yes" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderForm($fieldLayout, $fieldConfigAll, $bean, $options=[], $xfa=[]) {
		// default option
		$options['formType'] = $options['formType'] ?? self::$config['editMode'] ?? 'modal';
		// exit point
		if ( !empty(self::$config['allowEdit']) ) $xfa['submit'] = F::command('controller').'.save';
		if ( empty($bean->id) ) $xfa['cancel'] = F::command('controller').'.empty';
		else $xfa['cancel'] = F::command('controller').'.row&id='.$bean->id;
		// display
		ob_start();
		$formBody = self::renderFormBody($fieldLayout, $fieldConfigAll, $bean, $options);
		include self::$config['scriptPath']['edit'] ?? F::appPath('view/scaffold/edit.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			only render form fields in body (no <form> wrapper)
		</description>
		<io>
			<in>
				<array name="$fieldLayout" />
				<structure name="$fieldConfigAll" />
				<object name="$bean" optional="yes" />
				<structure name="$options" optional="yes" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderFormBody($fieldLayout, $fieldConfigAll, $bean=[], $options=[]) {
		$fieldLayout = self::initConfig__fixModalField($fieldLayout);
		if ( $fieldLayout === false ) return false;
		$fieldConfigAll = self::initConfig__fixFieldConfig($fieldConfigAll);
		if ( $fieldConfigAll === false ) return false;
		// default option
		$options['labelColumn'] = $options['labelColumn'] ?? 2;
		// display
		ob_start();
		include F::appPath('view/scaffold/edit.body.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			render edit form according to [listField & fieldConfig] of scaffold config
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self" optional="yes">
					<boolean name="allowEdit" />
					<structure name="scriptPath">
						<string name="inline_edit" />
					</structure>
				</structure>
				<!-- parameters -->
				<array name="$fieldLayout">
					<list name="~columnNameList~" value="~columnWidthList~" delim="|" />
				</array>
				<structure name="$fieldConfigAll">
					<structure name="~fieldName~" />
				</structure>
				<object name="$bean" />
				<!-- placeholder -->
				<structure name="$options" optional="yes" />
				<!-- additional exit points (e.g. when custom button specified) -->
				<structure name="$xfa" optional="yes" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderInlineForm($fieldLayout, $fieldConfigAll, $bean, $options=[], $xfa=[]) {
		$fieldLayout = self::initConfig__fixListField($fieldLayout);
		if ( $fieldLayout === false ) return false;
		$fieldConfigAll = self::initConfig__fixFieldConfig($fieldConfigAll);
		if ( $fieldConfigAll === false ) return false;
		// exit point
		if ( !empty(self::$config['allowEdit']) ) $xfa['submit'] = F::command('controller').'.save';
		if ( empty($bean->id) ) $xfa['cancel'] = F::command('controller').'.empty';
		else $xfa['cancel'] = F::command('controller').'.row&id='.$bean->id;
		// display
		ob_start();
		include self::$config['scriptPath']['inline_edit'] ?? F::appPath('view/scaffold/inline_edit.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			render specific field
		</description>
		<io>
			<in>
				<string name="$fieldName" />
				<structure name="$fieldConfig" />
				<object name="$bean" />
			</in>
			<out>
				<string name="~return~" comments="output" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderInput($fieldName, $fieldConfig, $bean) {
		// simply display nothing (when empty field name)
		if ( empty($fieldName) ) return '';
		// essential variable
		$dataFieldName = self::fieldName2dataFieldName($fieldName);
		if ( $dataFieldName === false ) return F::alertOutput([ 'type' => 'warning', 'message' => self::error() ]);
		// exit point : ajax upload
		if ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], ['file','image']) ) {
			$xfa['ajaxUpload'] = F::command('controller').'.upload_file';
			$xfa['ajaxUploadProgress'] = F::command('controller').'.upload_file_progress';
		}
		// determine value to show in field
		// ===> precedence: defined-value > one-to-many|many-to-many > bean-value > default-value > empty
		if ( isset($fieldConfig['value']) ) {
			$fieldValue = $fieldConfig['value'];
		// checkbox (one-to-many|many-to-many)
		// ===> one-to-many  : get value from own-list
		// ===> many-to-many : get value from shared-list
		} elseif ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], ['one-to-many','many-to-many']) ) {
			$fieldValue = array();
			$associateName = str_replace('_id', '', $fieldName);
			$propertyName = ( ( $fieldConfig['format'] == 'one-to-many' ) ? 'own' : 'shared' ) . ucfirst($associateName);
			foreach ( $bean->{$propertyName} as $tmp ) $fieldValue[] = $tmp->id;
		// bean-value > default-value > empty
		} else {
			$fieldValue = self::nestedArrayGet($fieldName, $bean) ?? $fieldConfig['default'] ?? '';
		}
		// fix options (when necessary)
		// ===> when options was not specified
		// ===> use field value as options
		if ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], ['radio','checkbox','one-to-many','many-to-many']) and !isset($fieldConfig['options']) ) {
			$fieldConfig['options'] = array();
			if ( $fieldConfig['format'] == 'radio' ) $fieldConfig['options'][$fieldValue] = $fieldValue;
			else foreach ( $fieldValue as $val ) $fieldConfig['options'][$val] = $val;
		}
		// fix checkbox value (when necessary)
		// ===> turn pipe-delimited list into array
		if ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'checkbox' and !is_array($fieldValue) ) {
			$fieldValue = explode('|', $fieldValue);
		}
		// done!
		ob_start();
		include F::appPath('view/scaffold/input.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			resize image (BMP,GIF,JPG,PNG) to specific width & height
		</description>
		<io>
			<in>
				<path name="$filePath" />
				<string name="$dimension" example="800x600|1024w|100h" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function resizeImage($filePath, $dimension) {
		$original = $target = array('width' => 0, 'height' => 0);
		// validate dimension
		if ( preg_match('/^([0-9]+)(x)([0-9]+)$/i', $dimension, $matches) ) {
			$target['width'] = $matches[1];
			$target['height'] = $matches[3];
		} elseif ( preg_match('/^([0-9]+)(w)$/i', $dimension, $matches) ) {
			$target['width'] = $matches[1];
		} elseif ( preg_match('/^([0-9]+)(h)$/i', $dimension, $matches) ) {
			$target['height'] = $matches[1];
		} else {
			self::$error = "Invalid file resize dimension ({$dimension})";
			return false;
		}
		// get image size of original file
		$size = getimagesize($filePath);
		if ( $size === false ) {
			self::$error = 'Unable to get image size';
			return false;
		}
		$original['width'] = $size[0];
		$original['height'] = $size[1];
		$imageType = $size[2];
		$mimeType = $size['mime'];
		// calculate percentage
		if ( !empty($target['width']) ) {
			$percentage = $target['width'] / $original['width'];
		} else {
			$percentage = $target['height'] / $original['height'];
		}
		// calculate missing dimension (when necessary)
		if ( empty($target['width']) ) {
			$target['width'] = round( $original['width'] * $percentage );
		} elseif ( empty($target['height']) ) {
			$target['height'] = round ( $original['height'] * $percentage );
		}
		// validate calculated dimension
		if ( $target['width'] == 0 ) {
			self::$error = "Target [width] cannot be zero ({$dimension})";
			return false;
		} elseif ( $target['height'] == 0 ) {
			self::$error = "Target [height] cannot be zero ({$dimension})";
			return false;
		}
		// load original image
		if ( $imageType == IMAGETYPE_BMP ) {
			$srcImage = imagecreatefrombmp($filePath);
		} elseif ( $imageType == IMAGETYPE_GIF ) {
			$srcImage = imagecreatefromgif($filePath);
		} elseif ( $imageType == IMAGETYPE_JPEG ) {
			$srcImage = imagecreatefromjpeg($filePath);
		} elseif ( $imageType == IMAGETYPE_PNG ) {
			$srcImage = imagecreatefrompng($filePath);
		} else {
			self::$error = "Resizing of [{$mimeType}] is not supported";
			return false;
		}
		// create resized new image
		$newImage = imagecreatetruecolor($target['width'], $target['height']);
		if ( $newImage === false ) {
			self::$error = "Unable to create new image";
			return false;
		}
		$resizeResult = imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, $target['width'], $target['height'], $original['width'], $original['height']);
		if ( $resizeResult === false ) {
			self::$error = "Unable to resize image";
			return false;
		}
		// override original image with new image
		if ( $imageType == IMAGETYPE_BMP ) {
			$saveResult = imagebmp($newImage, $filePath);
		} elseif ( $imageType == IMAGETYPE_GIF ) {
			$saveResult = imagegif($newImage, $filePath);
		} elseif ( $imageType == IMAGETYPE_JPEG ) {
			$saveResult = imagejpeg($newImage, $filePath, 80);
		} elseif ( $imageType == IMAGETYPE_PNG ) {
			$saveResult = imagepng($newImage, $filePath);
		}
		if ( $saveResult === false ) {
			self::$error = "Unable to save resized image";
			return false;
		}
		// allow read & execute to all
		chmod($filePath, 0755);
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			save bean with submitted data
		</description>
		<io>
			<in>
				<structure name="$data" />
				<structure name="$config" scope="self">
					<boolean name="writeLog" />
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<number name="~return~" comments="bean id" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function saveBean($data) {
		// get current bean or create new bean
		$bean = self::getBean( !empty($data['id']) ? $data['id'] : null );
		if ( $bean === false ) return false;
		if ( self::$config['writeLog'] ) $beanBeforeSave = Bean::export($bean);
		// fix submitted multi-selection value
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// remove empty item from submitted checkboxes
			if ( !empty($cfg['format']) and in_array($cfg['format'], ['checkbox','one-to-many','many-to-many']) ) {
				$data[$fieldName] = array_filter($data[$fieldName], 'strlen');
			}
			// extract {one-to-many|many-to-many} from submitted data before saving
			if ( !empty($cfg['format']) and in_array($cfg['format'], ['one-to-many','many-to-many']) ) {
				$associateName = str_replace('_id', '', $fieldName);
				$propertyName = ( ( $cfg['format'] == 'one-to-many' ) ? 'own' : 'shared' ) . ucfirst($associateName);
				$bean->{$propertyName} = array();
				foreach ( $data[$fieldName] as $associateID ) {
					$associateBean = ORM::get($associateName, $associateID);
					if ( $associateBean === false ) {
						self::$error = ORM::error();
						return false;
					}
					$bean->{$propertyName}[] = $associateBean;
				}
				unset($data[$fieldName]);
			// turn checkbox into pipe-delimited list
			} elseif ( !empty($cfg['format']) and $cfg['format'] == 'checkbox' ) {
				$data[$fieldName] = implode('|', $data[$fieldName]);
			}
		}
		// put submitted data into bean
		foreach ( $data as $key => $val ) $bean->{$key} = $val;
		foreach ( $bean as $key => $val ) if ( $val === '' ) $bean->{$key} = null;
		// default value for <disabled> and <seq>
		// ===> field <disabled> is compulsory
		// ===> field <seq> is optional
		if ( !isset($bean->disabled) or $bean->disabled == '' ) $bean->disabled = 0;
		if ( isset($bean->seq) and $bean->seq == '' ) $bean->seq = 0;
		// save bean
		$id = ORM::save($bean);
		if ( $id === false ) {
			self::$error = ORM::error();
			return false;
		}
		// write log (when necessary)
		if ( self::$config['writeLog'] ) {
			$logResult = Log::write(array(
				'action' => ( empty($data['id']) ? 'CREATE' : 'UPDATE' ).'_'.self::$config['beanType'],
				'entity_type' => self::$config['beanType'],
				'entity_id' => $id,
				'remark' => !empty($data['id']) ? Bean::diff($beanBeforeSave, $bean) : Bean::toString($bean),
			));
			if ( $logResult === false ) {
				self::$error = Log::error();
				return false;
			}
		}
		// done!
		return $id;
	}




	/**
	<fusedoc>
		<description>
			proceed file upload according to different protocol
		</description>
		<io>
			<in>
				<object name="&$handler" comments="simple-ajax-uploader handler" />
				<string name="$uploadDir" comments="target directory" />
				<string name="$resize" optional="yes" example="800x600|1024w|100h" />
			</in>
			<out>
				<string name="~return~" comments="filename" />
			</out>
		</io>
	</fusedoc>
	*/
	// 
	public static function startUpload(&$handler, $uploadDir, $resize=null) {
		$protocol = self::parseConnectionString(null, 'protocol');
		if ( $protocol === false ) return false;
		// skip when unit-test
		if ( Framework::$mode == Framework::FUSEBOX_UNIT_TEST ) {
			return $handler->getNewFileName();
		}
		// fix parameter (remove leading slash & append trailing slash)
		if ( substr($uploadDir, 0, 1) == '/' ) $uploadDir = substr($uploadDir, 1);
		if ( substr($uploadDir, -1) != '/' ) $uploadDir .= '/';
		// upload to temp directory first
		$uploadResult = self::startUpload__TempDir($handler);
		if ( $uploadResult === false ) return false;
		// resize uploaded file (when necessary)
		if ( !empty($resize) ) {
			$resizeResult = self::resizeImage($uploadResult['filePath'], $resize);
			if ( $resizeResult === false ) return false;
		}
		// done!
		if ( $protocol == 'ftp' or $protocol == 'ftps' ) return self::startUpload__FTP($uploadResult, $uploadDir);
		if ( $protocol == 's3' ) return self::startUpload__S3($uploadResult, $uploadDir);
		return self::startUpload__LocalServer($uploadResult, $uploadDir);
	}




	/**
	<fusedoc>
		<description>
			proceed to upload to remote FTP server
			===> append upload directory with folder in connection string (if any)
		</description>
		<io>
			<in>
				<structure name="$tempUpload">
					<string name="directory" />
					<string name="fileName" />
					<path name="filePath" />
				</structure>
				<string name="$uploadDir" />
			</in>
			<out>
				<string name="~return~" comments="filename" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function startUpload__FTP($tempUpload, $uploadDir) {
		$cs = self::parseConnectionString();
		if ( $cs === false ) return false;
		// connect to server
		$ftpConn = self::getConnection__FTP();
		if ( $ftpConn === false ) return false;
		// upload to target directory at remote server
		$destination = $cs['folder'].$uploadDir.$tempUpload['fileName'];
		$source = $tempUpload['filePath'];
		$uploadResult = ftp_put($ftpConn, $destination, $source, FTP_BINARY);
		if ( $uploadResult === false ) {
			self::$error = "Error occurred while uploading file to FTP server";
			return false;
		}
		// disconnect...
		ftp_close($ftpConn);
		// done!
		return $tempUpload['fileName'];
	}




	/**
	<fusedoc>
		<description>
			move uploaded file from temp directory to correct local directory
		</description>
		<io>
			<in>
				<structure name="$tempUpload">
					<string name="directory" />
					<string name="fileName" />
					<path name="filePath" />
				</structure>
				<string name="$uploadDir" />
			</in>
			<out>
				<string name="~return~" comments="filename" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function startUpload__LocalServer($tempUpload, $uploadDir) {
		$source = $tempUpload['filePath'];
		$destination  = F::config('uploadDir');
		$destination .= ( substr($destination, -1) == '/' ) ? '' : '/';
		$destination .= $uploadDir . $tempUpload['fileName'];
		// move temp file to target directory in local server
		$moveResult = rename($source, $destination);
		if ( $moveResult === false ) {
			self::$error = "Error occurred while moving temp file in local server";
			return false;
		}
		// done!
		return $tempUpload['fileName'];
	}




	/**
	<fusedoc>
		<description>
			proceed to upload to S3 bucket
			===> append upload directory with folder in connection string (if any)
		</description>
		<io>
			<in>
				<structure name="$tempUpload">
					<string name="directory" />
					<string name="fileName" />
					<path name="filePath" />
				</structure>
				<string name="$uploadDir" />
			</in>
			<out>
				<string name="~return~" comments="filename" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function startUpload__S3($tempUpload, $uploadDir) {
		$cs = self::parseConnectionString();
		if ( $cs === false ) return false;
		// get S3 client for upload operation
		$s3 = self::getConnection__S3();
		if ( $s3 === false ) return false;
		// upload to target folder at remote bucket
		try {
			$destination = $cs['folder'].$uploadDir.$tempUpload['fileName'];
			$source = $tempUpload['filePath'];
			$newFile = $s3->putObject(array(
				'Bucket' => $cs['bucket'],
				'Key' => $destination,
				'SourceFile' => $source,
				'ACL' => 'public-read'
			));
		} catch (S3Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return basename($newFile['ObjectURL']);
	}




	/**
	<fusedoc>
		<description>
			upload to temp directory at local server
		</description>
		<io>
			<in>
				<object name="&$handler" comments="simple-ajax-uploader handler" />
			</in>
			<out>
				<structure name="~return~" comments="info of file uploaded to temp dir">
					<string name="directory" comments="with trailing slash" />
					<string name="fileName" />
					<path name="filePath" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function startUpload__TempDir(&$handler) {
		// upload to system temp directory
		$tmpUploadDir  = str_replace('\\', '/', sys_get_temp_dir());
		$tmpUploadDir .= ( substr($tmpUploadDir, -1) == '/' ) ? '' : '/';
		$handler->uploadDir = $tmpUploadDir;
		$uploadResult = $handler->handleUpload();
		// validate upload result
		if ( !$uploadResult ) {
			self::$error = '[startUpload__TempDir] '.$handler->getErrorMsg();
			return false;
		}
		// done!
		return array(
			'directory' => $handler->uploadDir,
			'fileName'  => $handler->getFileName(),
			'filePath'  => $handler->uploadDir.$handler->getFileName(),
		);
	}




	/**
	<fusedoc>
		<description>
			enable or disable specific record
		</description>
		<io>
			<in>
				<number name="$id" />
				<boolean name="$active" comments="enable when true; disable when false" />
				<structure name="$config" scope="self">
					<boolean name="writeLog" />
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function toggleBean($id, $active) {
		$bean = self::getBean($id);
		if ( $bean === false ) return false;
		$bean->disabled = !$active;
		$saveResult = ORM::save($bean);
		// check result
		if ( $saveResult === false ) {
			self::$error = "Error occurred while toggling record (id={$id}, active={$active})";
			return false;
		}
		// write log (when necessary)
		if ( self::$config['writeLog'] ) {
			$logResult = Log::write(array(
				'action' => ( !empty($active) ? 'ENABLE' : 'DISABLE' ).'_'.self::$config['beanType'],
				'entity_type' => self::$config['beanType'],
				'entity_id' => $id,
			));
			if ( $logResult === false ) {
				self::$error = Log::error();
				return false;
			}
		}
		// done!
		return true;
	}




	// ajax upload file
	public static function uploadFile($arguments) {
		// load library
		$lib = self::$libPath['uploadFile'];
		if ( !file_exists($lib) ) {
			self::$error = "Could not load [SimpleAjaxUploader] library (path={$lib})";
			return false;
		}
		require_once $lib;
		// validation
		$err = array();
		if ( empty($arguments['uploaderID']) ) {
			$err[] = 'Argument [uploaderID] is required';
		}
		if ( empty($arguments['originalName']) ) {
			$err[] = 'Argument [originalName] is required';
		}
		if ( empty($arguments['fieldName']) ) {
			$err[] = 'Argument [fieldName] is required';
		} elseif ( !isset(self::$config['fieldConfig'][$arguments['fieldName']]) ) {
			$err[] = "Field config for [{$arguments['fieldName']}] is required";
		} elseif ( !in_array(self::$config['fieldConfig'][$arguments['fieldName']]['format'], ['file','image']) ) {
			$err[] = "Field [{$arguments['fieldName']}] must be [format=file|image]";
		}
		// validation error (if any)
		if ( !empty($err) ) {
			self::$error = implode("\n", $err);
			return false;
		}
		// get connection info
		$cs = self::parseConnectionString();
		if ( $cs === false ) return false;
		// fix config
		$uploadDir = self::$config['beanType'].'/'.$arguments['fieldName'].'/';
		$uploadBaseUrl  = str_replace('\\', '/', F::config('uploadUrl'));
		$uploadBaseUrl .= ( substr($uploadBaseUrl, -1) == '/' ) ? '' : '/';
		$uploadBaseUrl .= self::$config['beanType'].'/'.$arguments['fieldName'].'/';
		// create folder (when necessary)
		$createFolderResult = self::createFolder($uploadDir);
		if ( $createFolderResult === false ) return false;
		// remove uploaded file which parent record was deleted
		$removeExpiredFile = self::removeExpiredFile($arguments['fieldName'], $uploadDir);
		if ( $removeExpiredFile === false ) return false;
		// init object (specify [uploaderID] to know which DOM to update)
		$uploader = new FileUpload($arguments['uploaderID']);
		// config : array of permitted file extensions (only allow image & doc by default)
		$uploader->allowedExtensions = explode(',', self::$config['fieldConfig'][$arguments['fieldName']]['filetype']);
		// config : max file upload size in bytes (default 10MB in library)
		// ===> scaffold-controller turns human-readable-filesize into numeric
		if ( !empty(self::$config['fieldConfig'][$arguments['fieldName']]['filesize']) ) {
			$uploader->sizeLimit = self::fileSizeNumeric( self::$config['fieldConfig'][$arguments['fieldName']]['filesize'] );
		}
		// config : assign unique name to avoid overwrite
		$arguments['originalName'] = urldecode($arguments['originalName']);
		$uniqueName = pathinfo($arguments['originalName'], PATHINFO_FILENAME).'_'.Util::uuid().'.'.pathinfo($arguments['originalName'], PATHINFO_EXTENSION);
		$uploader->newFileName = $uniqueName;
		// check resize config (when necessary)
		if ( !empty(self::$config['fieldConfig'][$arguments['fieldName']]['resize']) ) {
			$resize = self::$config['fieldConfig'][$arguments['fieldName']]['resize'];
		} else {
			$resize = null;
		}
		// start upload
		$uploadFileName = self::startUpload($uploader, $uploadDir, $resize);
		if ( $uploadFileName === false ) return false;
		// success!
		return array(
			'success' => true,
			'msg'     => 'File uploaded successfully',
			'baseUrl' => $uploadBaseUrl,
			'fileUrl' => $uploadBaseUrl.$uploadFileName,
		);
	}




	/**
	<fusedoc>
		<description>
			perform validation on scaffold config (which specified in controller)
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<string name="beanType" />
					<string name="layoutPath" comments="can be false but cannot be null" />
					<string name="editMode" comments="inline|modal|inline-modal|basic" />
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
					<boolean name="writeLog" />
				</structure>
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" />
					<string name="uploadUrl" />
				</structure>
				<class name="Log" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function validateConfig() {
		// has any file field?
		$hasFileField = false;
		if ( isset(self::$config['fieldConfig']) ) {
			foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
				if ( isset($cfg['format']) and in_array($cfg['format'], ['file','image']) ) {
					$hasFileField = true;
					break;
				}
			}
		}
		// check bean type
		if ( empty(self::$config['beanType']) ) {
			self::$error = 'Scaffold config [beanType] is required';
			return false;
		} elseif ( strpos(self::$config['beanType'], '_') !== false ) {
			self::$error = 'Scaffold config [beanType] cannot contain underscore';
			return false;
		// check layout path
		} elseif ( !isset(self::$config['layoutPath']) ) {
			self::$error = 'Scaffold config [layoutPath] is required';
			return false;
		// check edit mode
		} elseif ( !empty(self::$config['editMode']) and !in_array(self::$config['editMode'], ['inline','modal','inline-modal','basic']) ) {
			self::$error = 'Scaffold config [editMode] is invalid ('.self::$config['editMode'].')';
			return false;
		// check uploader directory (when has file field)
		} elseif ( empty(F::config('uploadDir')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadDir] is required';
			return false;
		} elseif ( empty(F::config('uploadUrl')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadUrl] is required';
			return false;
		// check util component (for uuid)
		} elseif ( !class_exists('Util') ) {
			self::$error = 'Util component is required';
			return false;
		// check log component
		} elseif ( !empty(self::$config['writeLog']) and !class_exists('Log') ) {
			self::$error = 'Log component is required';
			return false;
		}
		// check field config : any missing @ listField
		foreach ( self::$config['listField'] as $fieldNameList => $columnWidth ) {
			$fieldNameList = explode('|', $fieldNameList);
			foreach ( $fieldNameList as $fieldName ) {
				if ( !empty($fieldName) and !isset(self::$config['fieldConfig'][$fieldName]) ) {
					self::$error = "Field config for [{$fieldName}] is required";
					return false;
				}
			} // foreach-fieldName
		} // foreach-listField
		// check field config : any missing
		foreach ( self::$config['modalField'] as $fieldNameList => $fieldWidthList ) {
			if ( self::parseFieldRow($fieldNameList, true) == 'fields' ) {
				$fieldNameList = explode('|', str_replace(',', '|', $fieldNameList));
				foreach ( $fieldNameList as $fieldName ) {
					if ( !empty($fieldName) and !isset(self::$config['fieldConfig'][$fieldName]) ) {
						self::$error = "Field config for [{$fieldName}] is required";
						return false;
					}
				} // foreach-fieldName
			} // if-parseFieldRow-fields
		} // foreach-modalField
		// check field config : options
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			if ( isset($cfg['format']) and in_array($cfg['format'], ['checkbox','radio']) and !isset($cfg['options']) ) {
				self::$error = "Options for [{$fieldName}] is required";
				return false;
			} elseif ( isset($cfg['options']) and $cfg['options'] !== false and !is_array($cfg['options']) ) {
				self::$error = "Options for [{$fieldName}] must be array";
				return false;
			}
		}
		// done!
		return true;
	}


} // class