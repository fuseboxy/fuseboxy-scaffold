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
		switch ( self::parseConnectionString(null, 'protocol') ) {
			case 's3':
				return self::createFolder__S3($newFolder);
				break;
			case 'ftp':
			case 'ftps':
				return self::createFolder__FTP($newFolder);
				break;
			default:
				return self::createFolder__LocalServer($newFolder);
		}
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
		if ( self::$config['writeLog'] ) {
			$beanBeforeDelete = $bean->export();
		}
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
				'remark' => method_exists('Bean', 'toString') ? Bean::toString($beanBeforeDelete) : null,
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
			adjust parameters to meet the needs of controller
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<string name="editMode" />
					<array name="fieldConfig" />
						<structure name="+">
							<string name="fileSize" optional="yes" />
						</structure>
					</array>
					<string name="listOrder" />
					<structure name="pagination" optional="yes">
						<number name="recordCount" />
						<number name="recordPerPage" />
						<number name="pageVisible" />
					</structure>
					<number name="page" scope="$_GET" optional="yes" />
					<boolean name="showAll" scope="$_GET" optional="yes" />
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fixParam() {
		// param fix : edit mode
		// ===> enforce normal edit form when not ajax
		if ( F::is('*.edit,*.new') and !F::ajaxRequest() ) {
			self::$config['editMode'] = 'basic';
		}
		// param fix : file size
		// ===> turn human-readable string to number
		foreach ( self::$config['fieldConfig'] as $itemName => $item ) {
			if ( !empty($item['filesize']) ) {
				$kb = 1024;
				$mb = $kb * 1024;
				$gb = $mb * 1024;
				$tb = $gb * 1024;
				// turn human-readable file size to number
				$item['filesize'] = strtoupper(str_replace(' ', '', $item['filesize']));
				$lastOneDigit = substr($item['filesize'], -1);
				$lastTwoDigit = substr($item['filesize'], -2);
				if ( $lastOneDigit == 'T' or $lastTwoDigit == 'TB' ) {
					$item['filesize'] = floatval($item['filesize']) * $tb;
				} elseif ( $lastOneDigit == 'G' or $lastTwoDigit == 'GB' ) {
					$item['filesize'] = floatval($item['filesize']) * $gb;
				} elseif ( $lastOneDigit == 'M' or $lastTwoDigit == 'MB' ) {
					$item['filesize'] = floatval($item['filesize']) * $mb;
				} elseif ( $lastOneDigit == 'K' or $lastTwoDigit == 'KB' ) {
					$item['filesize'] = floatval($item['filesize']) * $kb;
				} else {
					$item['filesize'] = floatval($item['filesize']);
				}
				// put into result
				self::$config['fieldConfig'][$itemName]['filesize_numeric'] = $item['filesize'];
			}
		}
		// param fix : list order
		// ===> add limit and offset to statement
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
					<array name="listFilter" optional="yes">
						<string name="0" comments="statement" />
						<array  name="1" comments="parameters" />
					</array>
					<string name="listFilter" optional="yes" />
				</structure>
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function getBeanCount() {
		if ( is_array(self::$config['listFilter']) ) {
			return ORM::count(self::$config['beanType'], self::$config['listFilter'][0], self::$config['listFilter'][1]);
		} else {
			return ORM::count(self::$config['beanType'], self::$config['listFilter']);
		}
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
					<array name="listFilter" optional="yes">
						<string name="0" comments="statement" />
						<array name="1" comments="parameters" />
					</array>
					<string name="listFilter" optional="yes" />
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
		if ( is_array(self::$config['listFilter']) ) {
			return ORM::get(self::$config['beanType'], self::$config['listFilter'][0].' '.self::$config['listOrder'], self::$config['listFilter'][1]);
		} else {
			return ORM::get(self::$config['beanType'], self::$config['listFilter'].' '.self::$config['listOrder']);
		}
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
		switch ( self::parseConnectionString(null, 'protocol') ) {
			case 's3':
				return self::getFileList__S3($dir);
				break;
			case 'ftp':
			case 'ftps':
				return self::getFileList__FTP($dir);
				break;
			default:
				return self::getFileList__LocalServer($dir);
		}
	}




	// get list of files in specific directory at FTP server
	// ===> append directory with the folder specified in connection string (if any)
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




	// get list of files in specific directory at local server
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




	// get list of files in specific directory at S3 bucket
	// ===> append directory with the folder specified in connection string (if any)
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
			parse upload directory config as connection string (when necessary)
		</description>
		<io>
			<in>
				<string name="$connString" optional="yes" default="~uploadDir~" />
				<string name="$key" optional="yes" comments="return value of specific key" />
			</in>
			<out>
				<structure name="~return~" optional="yes" oncondition="when success (key not specified)">
					<!-- S3 -->
					<string name="protocol" />
					<string name="accessKeyID" optional="yes" oncondition="s3" />
					<string name="secretAccessKey" optional="yes" oncondition="s3" />
					<string name="bucket" optional="yes" oncondition="s3" />
					<string name="folder" />
					<!-- FTP/FTPS -->
					<string name="protocol" />
					<string name="username" optional="yes" oncondition="ftp|ftps" />
					<string name="password" optional="yes" oncondition="ftp|ftps" />
					<string name="hostname" optional="yes" oncondition="ftp|ftps" />
					<string name="folder" />
					<!-- Local Server -->
					<string name="protocol" />
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
			===> {ftp|ftps}://{username}:{password}@{hostname}/{folder}
		</description>
		<io>
			<in>
				<string name="$connString" optional="yes" />
			</in>
			<out>
				<structure name="~return~" optional="yes" oncondition="when success">
					<string name="protocol" />
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
					<string name="protocol" />
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


	// rename file at server according to protocol
	public static function renameFile($source, $destination) {
		switch ( self::parseConnectionString(null, 'protocol') ) {
			case 's3':
				return self::renameFile__S3($source, $destination);
				break;
			case 'ftp':
			case 'ftps':
				return self::renameFile__FTP($source, $destination);
				break;
			default:
				return self::renameFile__LocalServer($source, $destination);
		}
	}


	// rename file at FTP server
	// ===> append source and destination with folder in connection string (if any)
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




	// rename file at S3 bucket
	// ===> append source and destination with folder in connection string (if any)
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
		if ( self::$config['writeLog'] ) $beanBeforeSave = $bean->export();
		// fix submitted multi-selection value
		foreach ( self::$config['fieldConfig'] as $fieldName => $field ) {
			// remove empty item from submitted checkboxes
			if ( isset($field['format']) and in_array($field['format'], array('checkbox','one-to-many','many-to-many')) ) {
				$data[$fieldName] = array_filter($data[$fieldName], 'strlen');
			}
			// extract {one-to-many|many-to-many} from submitted data before saving
			if ( isset($field['format']) and in_array($field['format'], array('one-to-many','many-to-many')) ) {
				$associateName = str_replace('_id', '', $fieldName);
				$propertyName = ( ( $field['format'] == 'one-to-many' ) ? 'own' : 'shared' ) . ucfirst($associateName);
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
			} elseif ( isset($field['format']) and $field['format'] == 'checkbox' ) {
				$data[$fieldName] = implode('|', $data[$fieldName]);
			}
		}
		// put submitted data into bean
		$bean->import($data);
		foreach ( $bean as $key => $val ) if ( $val === '' ) $bean[$key] = null;
		// default value
		// ===> allow no <seq> field, but <disabled> field is compulsory
		if ( !isset($bean->disabled) or $bean->disabled == '' ) {
			$bean->disabled = 0;
		}
		if ( isset($bean->seq) and $bean->seq == '' ) {
			$bean->seq = 0;
		}
		// save bean
		$id = ORM::save($bean);
		if ( $id === false ) {
			self::$error = ORM::error();
			return false;
		}
		// write log (when necessary)
		if ( self::$config['writeLog'] ) {
			if ( !empty($data['id']) and method_exists('Bean', 'diff') ) {
				$logRemark = Bean::diff($beanBeforeSave, $bean);
			} elseif ( empty($data['id']) and method_exists('Bean', 'toString') ) {
				$logRemark = Bean::toString($bean);
			} else {
				$logRemark = null;
			}
			$logResult = Log::write(array(
				'action' => ( empty($data['id']) ? 'CREATE' : 'UPDATE' ).'_'.self::$config['beanType'],
				'entity_type' => self::$config['beanType'],
				'entity_id' => $id,
				'remark' => $logRemark,
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
			assign default value to parameters
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<array name="fieldConfig">
						<string name="+" value="~fieldName~" />
					</array>
				</structure>
				<structure name="$tableColumns" comments="from {ORM::columns} method">
					<string name="~columnName~" value="~columnType~" example="varchar(255)" />
				</structure>
			</in>
			<out>
				<structure name="$config" scope="self">
					<boolean name="allowNew" default="true" />
					<boolean name="allowEdit" default="true" />
					<boolean name="allowToggle" default="true" />
					<boolean name="allowDelete" default="false" />
					<boolean name="allowSort" default="true" />
					<string name="editMode" default="inline" />
					<string name="modalSize" deafult="lg" />
					<string name="listFilter" default="1 = 1" />
					<string name="listOrder" default="ORDER BY (seq,) id" />
					<structure name="fieldConfig">
						<structure name="id">
							<boolean name="readonly" value="true" comments="force {ID} field exists; force readonly" />
						</structure>
						<structure name="~fieldName~">
							<string name="label" comments="derived from field name when not specified or true" />
							<string name="placeholder" comments="derived from field name when true" />
						</structure>
					</structure>
					<structure name="listField" default="~fieldConfig|tableColumns~">
						<string name="~columnList~" value="~columnWidth~" />
					</structure>
					<structure name="modalField">
						<list name="+" value="~columnList~" optional="yes" delim="|" comments="when no key specified, value is field list" />
						<list name="~columnList~" value="~columnWidthList~" optional="yes" delim="|" comments="when key was specified, key is column list and value is column width list" />
						<string name="~line~" optional="yes" example="---" comments="any number of dash(-) or equal(=)" />
						<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
					</structure>
				</structure>
				<string name="sortField" scope="$arguments" optional="yes" comments="indicate which label in table header to show the arrow" />
				<string name="sortRule" scope="$arguments" optional="yes" comments="indicate the direction of arrow shown at table header" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function setParamDefault() {
		global $arguments;
		// obtain all columns of specific table
		// ===> allow proceed further if table not exists (simply treated as no column)
		$tableColumns = ORM::columns(self::$config['beanType']);
		if ( $tableColumns === false and !preg_match('/Base table or view not found/i', ORM::error()) ) {
			self::$error = ORM::error();
			return false;
		}
		$tableColumns = empty($tableColumns) ? $tableColumns : array();
		$tableColumns = array_map(function(){ return array(); }, $tableColumns);
		// param default : field config
		// ===> merge table columns to field config
		if ( !isset(self::$config['fieldConfig']) ) self::$config['fieldConfig'] = array();
		self::$config['fieldConfig'] = array_merge(self::$config['fieldConfig'], $tableColumns);
		// fix param : field config
		// ===> convert numeric key to field name
		$arr = self::$config['fieldConfig'];
		self::$config['fieldConfig'] = array();
		foreach ( $arr as $key => $val ) self::$config['fieldConfig'] += is_numeric($key) ? array($val=>[]) : array($key=>$val);
		// fix param : field config (id)
		// ===> compulsory
		// ===> must be readonly
		if ( !isset(self::$config['fieldConfig']['id']) ) self::$config['fieldConfig']['id'] = array();
		self::$config['fieldConfig']['id']['readonly'] = true;
		// fix param : field config (seq)
		// ===> optional
		// ===> must be number
		if ( isset(self::$config['fieldConfig']['seq']) ) self::$config['fieldConfig']['seq']['format'] = 'number';
		// param default : field config (disabled)
		// ===> optional
		// ===> default as boolean dropdown (when not specified)
		if ( isset(self::$config['fieldConfig']['disabled']) and empty(self::$config['fieldConfig']['disabled']) ) {
			self::$config['fieldConfig']['disabled'] = array('options' => array('0' => 'Enable', '1' => 'Disable'));
		}
		// param default : field config (label)
		// ===> derived from field name
		foreach ( self::$config['fieldConfig'] as $_key => $_val ) {
			if ( !isset($_val['label']) or $_val['label'] === true ) {
				self::$config['fieldConfig'][$_key]['label'] = implode(' ', array_map(function($word){
					return in_array($word, array('id','url')) ? strtoupper($word) : ucfirst($word);
				}, explode('_', $_key)));
			}
		}
		// param default : field config (placeholder)
		// ===> drived from field name
		foreach ( self::$config['fieldConfig'] as $_key => $_val ) {
			if ( isset($_val['placeholder']) and $_val['placeholder'] === true ) {
				self::$config['fieldConfig'][$_key]['placeholder'] = implode(' ', array_map(function($word){
					return in_array($word, array('id','url')) ? strtoupper($word) : ucfirst($word);
				}, explode('_', $_key)));
			}
		}
		// param default : modal field
		if ( !isset(self::$config['modalField']) ) self::$config['modalField'] = array_keys(self::$config['fieldConfig']);
		// fix param : modal field (line)
		// ===> unify to use dash (simplify display logic)
		// ===> make line unique in length (avoid override after convert key)
		$i = 3;
		foreach ( self::$config['modalField'] as $key => $val ) {
			$isLine = ( !empty($val) and ( trim($val, '-') == '' or trim($val, '=') == '' ) );
			if ( $isLine ) self::$config['modalField'][$key] = str_repeat('-', $i);
			$i++;
		}
		// fix param : modal field (key)
		// ===> convert numeric key to field name
		$arr = self::$config['modalField'];
		self::$config['modalField'] = array();
		foreach ( $arr as $key => $val ) self::$config['modalField'] += is_numeric($key) ? array($val=>'') : array($key=>$val);
		// fix param : modal field (id)
		// ===> compulsory
		$hasID = false;
		foreach ( self::$config['modalField'] as $key => $val ) if ( in_array('id', explode('|', $key)) ) $hasID = true;
		if ( !$hasID ) self::$config['modalField'] = array('id' => '') + self::$config['modalField'];
		// fix param : modal field (width)
		// ===> assign default column width
		foreach ( self::$config['modalField'] as $key => $val ) {
			if ( empty($val) ) {
				$count = count( explode('|', $key) );
				self::$config['modalField'][$key] = implode('|', array_fill(0, $count, ( 12 % $count == 0 ) ? (12/$count) : 1));
			}
		}
		// param default : list field
		if ( !isset(self::$config['listField']) ) self::$config['listField'] = array_keys(self::$config['fieldConfig']);
		// fix param : list field (key)
		// ===> convert numeric key to field name
		$arr = self::$config['listField'];
		self::$config['listField'] = array();
		foreach ( $arr as $key => $val ) self::$config['listField'] += is_numeric($key) ? array($val=>'') : array($key=>$val);
		// param default : permission
		if ( !isset(self::$config['allowNew']) ) self::$config['allowNew'] = true;
		if ( !isset(self::$config['allowEdit']) ) self::$config['allowEdit'] = true;
		if ( !isset(self::$config['allowSort']) ) self::$config['allowSort'] = true;
		if ( !isset(self::$config['allowToggle']) ) self::$config['allowToggle'] = true;
		if ( !isset(self::$config['allowDelete']) ) self::$config['allowDelete'] = false;
		// param default : edit mode
		if ( empty(self::$config['editMode']) ) self::$config['editMode'] = 'inline';
		// param default : modal size
		if ( empty(self::$config['modalSize']) ) self::$config['modalSize'] = 'lg';
		// param default : list filter & order
		if ( empty(self::$config['listFilter']) ) self::$config['listFilter'] = ' 1 = 1 ';
		if ( self::$config['allowSort'] and isset($arguments['sortField']) ) {
			// use sort-field specified (when necessary)
			self::$config['listOrder'] = "ORDER BY `{$arguments['sortField']}` ";
			if ( isset($arguments['sortRule']) ) self::$config['listOrder'] .= $arguments['sortRule'];
		} elseif ( !isset(self::$config['listOrder']) ) {
			// otherwise, use specify a default list order (when necessary)
			self::$config['listOrder'] = isset(self::$config['_columns_']['seq']) ? 'ORDER BY IFNULL(seq, 9999), id ' : 'ORDER BY id ';
		}
		// param default : sort field (when necessary)
		// ===> extract from list order
		if ( !isset($arguments['sortField']) ) {
			$tmp = trim(str_replace('ORDER BY ', '', self::$config['listOrder']));
			$tmp = explode(',', $tmp);  // turn {column-direction} list into array
			$tmp = $tmp[0];  // extract first {column-direction}
			$tmp = explode(' ', $tmp);
			$arguments['sortField'] = $tmp[0];  // extract {column}
			if ( isset($tmp[1]) ) $arguments['sortRule'] = $tmp[1];
		}
		// param default : script path
		foreach ( ['edit','header','inline_edit','list','row','modal'] as $item ) {
			if ( !isset(self::$config['scriptPath']) ) self::$config['scriptPath'] = array();
			if ( !isset(self::$config['scriptPath'][$item]) ) self::$config['scriptPath'][$item] = F::appPath("view/scaffold/{$item}.php");
		}
		// param default : write log
		if ( !isset(self::$config['writeLog']) ) self::$config['writeLog'] = false;
		// param default : pagination
		if ( !isset(self::$config['pagination']) ) self::$config['pagination'] = false;
		if ( !empty(self::$config['pagination']) ) {
			if ( !is_array(self::$config['pagination']) ) self::$config['pagination'] = array();
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
		// take action according to protocol
		switch ( self::parseConnectionString(null, 'protocol') ) {
			case 's3':
				$result = self::startUpload__S3($uploadResult, $uploadDir);
				break;
			case 'ftp':
			case 'ftps':
				$result = self::startUpload__FTP($uploadResult, $uploadDir);
				break;
			default:
				$result = self::startUpload__LocalServer($uploadResult, $uploadDir);
		}
		// done!
		return $result;
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
		} elseif ( self::$config['fieldConfig'][$arguments['fieldName']]['format'] != 'file' ) {
			$err[] = "Field [{$arguments['fieldName']}] must be [format=file]";
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
		if ( isset(self::$config['fieldConfig'][$arguments['fieldName']]['filetype']) ) {
			$uploader->allowedExtensions = explode(',', self::$config['fieldConfig'][$arguments['fieldName']]['filetype']);
		} else {
			$uploader->allowedExtensions = explode(',', 'jpg,jpeg,png,gif,bmp,txt,doc,docx,pdf,ppt,pptx,xls,xlsx');
		}
		// config : max file upload size in bytes (default 10MB in library)
		// ===> scaffold-controller turns human-readable-filesize into numeric
		if ( isset(self::$config['fieldConfig'][$arguments['fieldName']]['filesize']) ) {
			$uploader->sizeLimit = self::$config['fieldConfig'][$arguments['fieldName']]['filesize_numeric'];
		}
		// config : assign unique name to avoid overwrite
		$arguments['originalName'] = urldecode($arguments['originalName']);
		$uniqueName = pathinfo($arguments['originalName'], PATHINFO_FILENAME).'_'.uuid().'.'.pathinfo($arguments['originalName'], PATHINFO_EXTENSION);
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
			foreach ( self::$config['fieldConfig'] as $_key => $_field ) {
				if ( isset($_field['format']) and $_field['format'] == 'file' ) {
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
		} elseif ( !in_array(self::$config['editMode'], ['inline','modal','inline-modal','basic']) ) {
			self::$error = 'Scaffold config [editMode] is invalid ('.self::$config['editMode'].')';
			return false;
		// check uploader directory (when has file field)
		} elseif ( empty(F::config('uploadDir')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadDir] is required';
			return false;
		} elseif ( empty(F::config('uploadUrl')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadUrl] is required';
			return false;
		// check log component
		} elseif ( !empty(self::$config['writeLog']) and !class_exists('Log') ) {
			self::$error = 'Log component is required';
			return false;
		}
		// done!
		return true;
	}


} // class