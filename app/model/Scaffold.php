<?php
class Scaffold {


	// config
	public static $config;


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	// create folder at upload directory according to protocol
	public static function createFolder($newFolder) {
		$protocol = self::getUploadProtocol();
		// take action according to protocol
		if ( $protocol == 's3' ) {
			$result = self::createFolder__S3($newFolder);
		} elseif ( $protocol == 'ftp' or $protocol == 'ftps' ) {
			$result = self::createFolder__FTP($newFolder);
		} else {
			$result = self::createFolder__LocalServer($newFolder);
		}
		// done!
		return $result;
	}


	// create directory at remote FTP server (when not exists)
	public static function createFolder__FTP($newFolder) {
		self::$error = 'Method [Scaffold::createFolder__FTP] is under construction';
		return false;
	}

	// create directory at local server (when not exists)
	public static function createFolder__LocalServer($newFolder) {
		if ( !file_exists($newFolder) ) {
			mkdir($newFolder, 0766, true);
		}
		return true;
	}


	// create folder at S3 bucket (when not exists)
	public static function createFolder__S3($newFolder) {
		$connString = self::parseConnectionString();
		$s3 = self::getS3Client();
		// fix parameter
		// ===> remove leading slash
		// ===> append trailing slash
		if ( substr($newFolder, 0, 1) == '/' ) $newFolder = substr($newFolder, 1);
		if ( substr($newFolder, -1) != '/' ) $newFolder .= '/';
		// create folder remotely (when necessary)
		if ( !$s3->doesObjectExist($connString['bucket'], $newFolder) ) {
			try {
				$putObjectResult = $s3->putObject(array(
					'Bucket' => $connString['bucket'],
					'Key' => $newFolder,
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


	// remove specific bean
	public static function deleteBean($id) {
		$bean = self::getBean($id);
		// get record value for log (when necessary)
		if ( self::$config['writeLog'] ) {
			$beanBeforeDelete = $bean->export();
		}
		// commit to delete record
		R::trash($bean);
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


	// adjust parameters to meet the needs of controller
	public static function fixParam() {
		// param fix : edit mode
		if ( F::is('*.edit,*.new') and !F::ajaxRequest() ) {
			self::$config['editMode'] = 'classic';
		}
		if ( !in_array(self::$config['editMode'], array('inline','modal','classic')) ) {
			self::$config['editMode'] = 'inline';
		}
		// param fix : file size (string to number)
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
				self::$config['fieldConfig'][$itemName]['filesize_numeric'] = $item['filesize'];
			}
		}
		// done!
		return true;
	}


	// get specific bean (or empty bean)
	public static function getBean($id=null) {
		// get empty record when no argument
		if ( $id === null ) {
			return R::dispense(self::$config['beanType']);
		// get specific record with id was specified
		} else {
			$result = R::load(self::$config['beanType'], $id);
			if ( empty($result->id) ) {
				self::$error = "Record not found (id={$id})";
				return false;
			}
			return $result;
		}
	}


	// get all records
	public static function getBeanList() {
		if ( is_array(self::$config['listFilter']) ) {
			return R::find(self::$config['beanType'], self::$config['listFilter'][0].' '.self::$config['listOrder'], self::$config['listFilter'][1]);
		} else {
			return R::find(self::$config['beanType'], self::$config['listFilter'].' '.self::$config['listOrder']);
		}
	}


	// get upload client for S3
	public static function getS3Client() {
		$connString = self::parseConnectionString();
		if ( $connString == false ) return false;
		// config for factory
		$config = array(
			'credentials' => array('key' => $connString['accessKeyID'], 'secret' => $connString['secretAccessKey']),
			'region' => 'us-east-1',
			'version' => '2006-03-01',
		);
		// create object to retrieve bucket location
		$client = Aws\S3\S3Client::factory($config);
		$bucketLocation = $client->getBucketLocation(array('Bucket' => $connString['bucket']));
		// re-create object with correct region specified
		$config['region'] = $bucketLocation->get('LocationConstraint');
		$client = Aws\S3\S3Client::factory($config);
		// done!
		return $client;
	}


	// get protocol from upload directory
	public static function getUploadProtocol() {
		global $fusebox;
		if ( substr($fusebox->config['uploadDir'], 0, 5) == 's3://' ) {
			return 's3';
		} elseif ( substr($fusebox->config['uploadDir'], 0, 6) == 'ftp://' ) {
			return 'ftp';
		} elseif ( substr($fusebox->config['uploadDir'], 0, 7) == 'ftps://' ) {
			return 'ftps';
		} else {
			return 'local-server';
		}
	}


	/**
	<fusedoc>
		<description>
			parse upload directory config as connection string (when necessary)
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" />
				</structure>
			</in>
			<out>
				<structure name="~return~" optional="yes" oncondition="when success">
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
				<boolean name="~return~" value="false" optional="yes" oncondition="when failure" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseConnectionString() {
		global $fusebox;
		// parse according to protocol
		$protocol = self::getUploadProtocol();
		if ( $protocol == 's3' ) {
			return self::parseConnectionString__S3();
		} elseif ( $protocol == 'ftp' or $protocol == 'ftps' ) {
			return self::parseConnectionString__FTP();
		// no parse for local server
		} else {
			return array(
				'protocol' => self::getUploadProtocol(),
				'folder' => $fusebox->config['uploadDir'],
			);
		}
	}


	// parse FTP connection string
	// ===> {ftp|ftps}://{username}:{password}@{hostname}/{folder}
	public static function parseConnectionString__FTP() {
		global $fusebox;
		// load connection string from config
		$conn = substr($fusebox->config['uploadDir'], $protocol == 'ftp' ? 6 : 7);
		$conn = str_replace('\\', '/', $conn);
		// extract username
		if ( strpos($conn, ':') !== false ) {
			$conn = explode(':', $conn, 2);
			$username = $conn[0];
			$conn = $conn[1];
		} else {
			self::$error = "[Username] is missing from the connection string ({$fusebox->config['uploadDir']})";
			return false;
		}
		// extract password
		if ( strpos($conn, '@') !== false ) {
			$conn = explode('@', $conn, 2);
			$password = $conn[0];
			$conn = $conn[1];
		} else {
			self::$error = "[Password] is missing from the connection string ({$fusebox->config['uploadDir']})";
			return false;
		}
		// extract hostname & folder (if any)
		$conn = explode('/', $conn, 2);
		$hostname = $conn[0];
		$folder = isset($conn[1]) ? $conn[1] : '';
		if ( empty($bucket) ) {
			self::$error = "[Hostname] is missing from the connection string ({$fusebox->config['uploadDir']})";
			return false;
		}
		// add trailing slash to folder (when necessary)
		if ( !empty($folder) and substr($folder, -1) != '/' ) {
			$folder .= '/';
		}
		// done!
		return array(
			'protocol' => self::getUploadProtocol(),
			'username' => $username,
			'password' => $password,
			'hostname' => $hostname,
			'folder'   => $folder,
		);
	}


	// parse S3 connection string
	// ===> s3://{accessKeyID}:{secretAccessKey}@{bucket}/{folder}
	public static function parseConnectionString__S3() {
		global $fusebox;
		// load connection string from config
		$conn = substr($fusebox->config['uploadDir'], 5);
		$conn = str_replace('\\', '/', $conn);
		// extract access-key-id
		if ( strpos($conn, ':') !== false ) {
			$conn = explode(':', $conn, 2);
			$accessKeyID = $conn[0];
			$conn = $conn[1];
		} else {
			self::$error = "[Access Key ID] is missing from the connection string ({$fusebox->config['uploadDir']})";
			return false;
		}
		// extract secret-access-key
		if ( strpos($conn, '@') !== false ) {
			$conn = explode('@', $conn, 2);
			$secretAccessKey = $conn[0];
			$conn = $conn[1];
		} else {
			self::$error = "[Secret Access Key] is missing from the connection string ({$fusebox->config['uploadDir']})";
			return false;
		}
		// extract bucket & folder (if any)
		$conn = explode('/', $conn, 2);
		$bucket = $conn[0];
		$folder = isset($conn[1]) ? $conn[1] : '';
		if ( empty($bucket) ) {
			self::$error = "[Bucket] is missing from the connection string ({$fusebox->config['uploadDir']})";
			return false;
		}
		// add trailing slash to folder (when necessary)
		if ( !empty($folder) and substr($folder, -1) != '/' ) {
			$folder .= '/';
		}
		// done!
		return array(
			'protocol' => self::getUploadProtocol(),
			'accessKeyID' => $accessKeyID,
			'secretAccessKey' => $secretAccessKey,
			'bucket' => $bucket,
			'folder' => $folder,
		);
	}


	// remove expired file according to protocol
	public static function removeExpiredFile($fieldName, $uploadDir) {
		$protocol = self::getUploadProtocol();
		// skip when unit-test
		if ( Framework::$mode != Framework::FUSEBOX_UNIT_TEST ) {
			return true;
		// take action according to protocol
		} elseif ( $protocol == 's3' ) {
			return self::removeExpiredFile__S3($fieldName, $uploadDir);
		} elseif ( $protocol == 'ftp' or $protocol == 'ftps' ) {
			return self::removeExpiredFile__FTP($fieldName, $uploadDir);
		} else {
			return self::removeExpiredFile__LocalServer($fieldName, $uploadDir);
		}
	}


	// archive expired file at S3 bucket
	public static function removeExpiredFile__S3($fieldName, $uploadDir) {
		return true;
	}


	// archive expired file at FTP server
	public static function removeExpiredFile__FTP($fieldName, $uploadDir) {
		self::$error = 'Method [Scaffold::removeExpiredFile__FTP] is under construction';
		return false;
	}


	// archive expired file at local server
	public static function removeExpiredFile__LocalServer($fieldName, $uploadDir) {
		// get all records of specific field
		// ===> only required file name
		$nonOrphanFiles = R::getCol("SELECT {$fieldName} FROM ".self::$config['beanType']." WHERE {$fieldName} IS NOT NULL");
		foreach ( $nonOrphanFiles as $i => $path ) {
			if ( !empty($path) ) {
				$nonOrphanFiles[$i] = basename($path);
			}
		}
		// go through every file in upload directory
		if ( !empty($nonOrphanFiles) ) {
			foreach ( glob($uploadDir."*.*" ) as $filePath ) {
				// only remove orphan file older than 1 day
				// ===> avoid remove file which ajax-upload by user but not save record yet
				// ===> (do not check lifespan when unit-test)
				$isOrphan = !in_array(basename($filePath), $nonOrphanFiles);
 				$isExpired = ( filemtime($filePath) < strtotime(date("-1 day")) or Framework::$mode == Framework::FUSEBOX_UNIT_TEST );
				$isDeleted = ( pathinfo($filePath, PATHINFO_EXTENSION) == 'DELETED' );
				// archive expired file by appending {.DELETED} extension
				// ===> avoid accidentally removing any precious data
				// ===> (rely on server administrator to remove the {*.DELETE} files explicitly)
				if ( $isOrphan and $isExpired and !$isDeleted ) {
					$renameResult = rename($filePath, "{$filePath}.DELETED");
					if ( !$renameResult ) {
						self::$error = 'Error occurred while archiving expired file';
						return false;
					}
				} // if-orphan-expired-deleted
			} // foreach-uploadDir
		} // if-not-empty-nonOrphanFiles
	}


	// save bean with submitted data
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
					$associateBean = R::load($associateName, $associateID);
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
		// default value
		// ===> allow no <seq> field, but <disabled> field is compulsory
		if ( !isset($bean->disabled) or $bean->disabled == '' ) {
			$bean->disabled = 0;
		}
		if ( isset($bean->seq) and $bean->seq == '' ) {
			$bean->seq = 0;
		}
		// save bean
		$id = R::store($bean);
		if ( empty($id) ) {
			self::$error = 'Error occurred while saving record';
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


	// assign default value to parameters
	public static function setParamDefault() {
		// obtain all columns of specified table
		// ===> if no column (or non-exist table)
		// ===> rely on {fieldConfig} (if any)
		try {
			self::$config['_columns_'] = R::getColumns( self::$config['beanType'] );
		} catch (Exception $e) {
			if ( preg_match('/Base table or view not found/i', $e->getMessage()) ) {
				self::$config['_columns_'] = array();
			} else {
				throw $e;
			}
		}
		if ( empty(self::$config['_columns_']) and isset(self::$config['fieldConfig']) ) {
			foreach ( self::$config['fieldConfig'] as $_key => $_val ) {
				$_col = is_numeric($_key) ? $_val : $_key;
				self::$config['_columns_'][$_col] = '~any~';
			}
		}
		// param default : permission
		self::$config['allowNew'] = isset(self::$config['allowNew']) ? self::$config['allowNew'] : true;
		self::$config['allowEdit'] = isset(self::$config['allowEdit']) ? self::$config['allowEdit'] : true;
		self::$config['allowToggle'] = isset(self::$config['allowToggle']) ? self::$config['allowToggle'] : true;
		self::$config['allowDelete'] = isset(self::$config['allowDelete']) ? self::$config['allowDelete'] : false;
		self::$config['allowSort'] = isset(self::$config['allowSort']) ? self::$config['allowSort'] : false;
		// param default : edit mode
		self::$config['editMode'] = !empty(self::$config['editMode']) ? self::$config['editMode'] : 'inline';
		// param default : modal size
		self::$config['modalSize'] = !empty(self::$config['modalSize']) ? self::$config['modalSize'] : 'normal';
		// param default : list field
		self::$config['listField'] = isset(self::$config['listField']) ? self::$config['listField'] : array_keys(self::$config['_columns_']);
		// param default : list filter & order
		self::$config['listFilter'] = isset(self::$config['listFilter']) ? self::$config['listFilter'] : '1 = 1 ';
		if ( self::$config['allowSort'] and isset($arguments['sortField']) ) {
			// use sort-field specified (when necessary)
			self::$config['listOrder'] = "ORDER BY {$arguments['sortField']} ";
			if ( isset($arguments['sortRule']) ) self::$config['listOrder'] .= $arguments['sortRule'];
		} elseif ( !isset(self::$config['listOrder']) ) {
			// otherwise, use specify a default list order (when necessary)
			self::$config['listOrder'] = isset(self::$config['_columns_']['seq']) ? 'ORDER BY seq, id ' : 'ORDER BY id ';
		}
		// param default : sort field (extract from list order)
		if ( !isset($arguments['sortField']) ) {
			$tmp = trim(str_replace('ORDER BY ', '', self::$config['listOrder']));
			$tmp = explode(',', $tmp);  // turn {column-direction} list into array
			$tmp = $tmp[0];  // extract first {column-direction}
			$tmp = explode(' ', $tmp);
			$arguments['sortField'] = $tmp[0];  // extract {column}
			if ( isset($tmp[1]) ) $arguments['sortRule'] = $tmp[1];
		}
		// param default : field config
		self::$config['fieldConfig'] = isset(self::$config['fieldConfig']) ? self::$config['fieldConfig'] : array();
		$_arr = self::$config['fieldConfig'];
		self::$config['fieldConfig'] = array();
		foreach ( $_arr as $_key => $_val ) {
			if ( is_numeric($_key) ) {
				self::$config['fieldConfig'][$_val] = array();
			} else {
				self::$config['fieldConfig'][$_key] = $_val;
			}
		}
		unset($_arr);
		foreach ( self::$config['_columns_'] as $_col => $_colType ) {
			if ( !isset(self::$config['fieldConfig'][$_col]) ) {
				self::$config['fieldConfig'][$_col] = array();
			}
		}
		if ( !isset(self::$config['fieldConfig']['id']) ) {
			self::$config['fieldConfig']['id'] = array();
		}
		// param default : label
		foreach ( self::$config['fieldConfig'] as $_key => $_val ) {
			if ( !isset($_val['label']) ) {
				self::$config['fieldConfig'][$_key]['label'] = ( $_key == 'id' ) ? strtoupper($_key) : ucwords(str_replace('_', ' ', $_key));
			}
		}
		// param default : field config (field {id} must be readonly)
		self::$config['fieldConfig']['id']['readonly'] = true;
		// param default : field config (field {seq} must be number)
		if ( isset(self::$config['fieldConfig']['seq']) ) {
			self::$config['fieldConfig']['seq']['format'] = 'number';
		}
		// param default : field config (field {disabled} is dropdown by default)
		if ( isset(self::$config['fieldConfig']['disabled']) and empty(self::$config['fieldConfig']['disabled']) ) {
			self::$config['fieldConfig']['disabled'] = array('options' => array('0' => 'enable', '1' => 'disable'));
		}
		// param default : modal field
		self::$config['modalField'] = isset(self::$config['modalField']) ? self::$config['modalField'] : array_keys(self::$config['fieldConfig']);
		$_scaffoldModalField = self::$config['modalField'];
		self::$config['modalField'] = array();
		$_scaffoldModalFieldHasID = false;
		foreach ( $_scaffoldModalField as $_key => $_val ) {
			if ( is_numeric($_key) ) {
				self::$config['modalField'][$_val] = '';
			} else {
				self::$config['modalField'][$_key] = $_val;
			}
			if ( ( is_numeric($_key) and strpos($_val.'|', 'id|') !== false ) or ( strpos($_key.'|', 'id|') !== false ) ) {
				$_scaffoldModalFieldHasID = true;
			}
		}
		if ( !$_scaffoldModalFieldHasID ) {
			self::$config['modalField'] = array('id' => '') + self::$config['modalField'];
		}
		unset($_scaffoldModalField);
		foreach ( self::$config['modalField'] as $_colList => $_colWidthList ) {
			$_cols = explode('|', $_colList);
			if ( !empty($_cols) and empty($_colWidthList) ) {
				if     ( count($_cols) == 1 ) $_colWidthList = '12';
				elseif ( count($_cols) == 2 ) $_colWidthList = '6|6';
				elseif ( count($_cols) == 3 ) $_colWidthList = '4|4|4';
				elseif ( count($_cols) == 4 ) $_colWidthList = '3|3|3|3';
				elseif ( count($_cols) == 5 ) $_colWidthList = '3|3|2|2|2';
				elseif ( count($_cols) == 6 ) $_colWidthList = '2|2|2|2|2|2';
				else $_colWidthList = implode('|', array_fill(0, 1, '1'));
				self::$config['modalField'][$_colList] = $_colWidthList;
			}
		}
		// param default : script path
		self::$config['scriptPath'] = isset(self::$config['scriptPath']) ? self::$config['scriptPath'] : array();
		$arr = array('edit','header','inline_edit','list','row','modal');
		foreach ( $arr as $i => $item ) {
			if ( !isset(self::$config['scriptPath'][$item]) ) {
				self::$config['scriptPath'][$item] = F::config('appPath')."view/scaffold/{$item}.php";
			}
		}
		// param default : library path
		self::$config['libPath'] = isset(self::$config['libPath']) ? self::$config['libPath'] : (dirname(F::config('appPath')).'/lib/');
		self::$config['libPath'] .= in_array(substr(self::$config['libPath'], -1), array('/','\\')) ? '' : '/';
		// param default : write log
		self::$config['writeLog'] = isset(self::$config['writeLog']) ? self::$config['writeLog'] : false;
		// done!
		return true;
	}


	// proceed upload according to protocol
	public static function startUpload(&$handler, $uploadDir) {
		$protocol = self::getUploadProtocol();
		// skip when unit-test
		if ( Framework::$mode == Framework::FUSEBOX_UNIT_TEST ) {
			return $handler->getNewFileName();
		// take action according to protocol
		} elseif ( $protocol == 's3' ) {
			return self::startUpload__S3($handler, $uploadDir);
		} elseif ( $protocol == 'ftp' or $protocol == 'ftps' ) {
			return self::startUpload__FTP($handler, $uploadDir);
		} else {
			return self::startUpload__LocalServer($handler, $uploadDir);
		}
	}


	// proceed upload to remote FTP server
	public static function startUpload__FTP(&$handler, $uploadDir) {
		self::$error = 'Method [Scaffold::startUpload__FTP] is under construction';
		return false;
	}


	// proceed upload to local server
	public static function startUpload__LocalServer(&$handler, $uploadDir) {
		$handler->uploadDir = $uploadDir;
		$uploadResult = $handler->handleUpload();
		// validate upload result
		if ( !$uploadResult ) {
			self::$error = $handler->getErrorMsg();
			return false;
		}
		// done!
		return $handler->getFileName();
	}


	// proceed upload to S3 bucket
	public static function startUpload__S3(&$handler, $uploadDir) {
		$connString = self::parseConnectionString();
		// fix parameter
		// ===> remove leading slash
		// ===> append trailing slash
		if ( substr($uploadDir, 0, 1) == '/' ) $uploadDir = substr($uploadDir, 1);
		if ( substr($uploadDir, -1) != '/' ) $uploadDir .= '/';
		// upload to temp directory at local server first
		$tmpUploadDir  = str_replace('\\', '/', sys_get_temp_dir());
		$tmpUploadDir .= ( substr($tmpUploadDir, -1) == '/' ) ? '' : '/';
		$handler->uploadDir = $tmpUploadDir;
		$uploadResult = $handler->handleUpload();
		// validate upload result to local server
		if ( !$uploadResult ) {
			self::$error = $handler->getErrorMsg();
			return false;
		}
		// upload to target folder at remote bucket
		$s3 = self::getS3Client();
		try {
			$newFile = $s3->putObject(array(
				'Bucket' => $connString['bucket'],
				'Key' => $uploadDir.$handler->getFileName(),
				'SourceFile' => $tmpUploadDir.$handler->getFileName(),
				'ACL' => 'public-read'
			));
		} catch (S3Exception $e) {
			self::$error = $e->getMessage();
			return false;
		}
		// done!
		return basename($newFile['ObjectURL']);
	}


	// enable/disable specific record
	public static function toggleBean($id, $active) {
		$bean = self::getBean($id);
		if ( $bean === false ) return false;
		$bean->disabled = !$active;
		$saveResult = R::store($bean);
		// check result
		if ( empty($saveResult) ) {
			self::$error = "Error occurred while toggling record (id={$id}, active={$active})";
			return false;
		}
		// write log (when necessary)
		if ( self::$config['writeLog'] ) {
			$logResult = Log::write(array(
				'action' => ( empty($active) ? 'ENABLE' : 'DISABLE' ).'_'.self::$config['beanType'],
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
		global $fusebox;
		// load library
		$libPath = self::$config['libPath'].'simple-ajax-uploader/1.10.1/extras/Uploader.php';
		if ( !file_exists($libPath) ) {
			self::$error = "Could not load [SimpleAjaxUploader] library (path={$libPath})";
			return false;
		}
		require_once $libPath;
		// validation
		$err = array();
		if ( empty($arguments['uploaderID']) ) {
			$err[] = 'Argument [uploaderID] is required';
		} elseif ( !isset($arguments[$arguments['uploaderID']]) ) {
			$err[] = "Data of [{$arguments['uploaderID']}] was not submitted";
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
		// fix config
		$connString = self::parseConnectionString();
		$uploadDir  = str_replace('\\', '/', $connString['folder']);
		$uploadDir .= ( substr($uploadDir, -1) == '/' ) ? '' : '/';
		$uploadDir .= self::$config['beanType'].'/'.$arguments['fieldName'].'/';
		$uploadBaseUrl  = str_replace('\\', '/', $fusebox->config['uploadBaseUrl']);
		$uploadBaseUrl .= ( substr($uploadBaseUrl, -1) == '/' ) ? '' : '/';
		$uploadBaseUrl .= self::$config['beanType'].'/'.$arguments['fieldName'].'/';
		// create folder (when necessary)
		$createFolderResult = self::createFolder($uploadDir);
		if ( $createFolderResult === false ) return false;
		// remove uploaded file which parent record was deleted
		$removeExpiredFile = self::removeExpiredFile($arguments['fieldName'], $uploadDir);
		if ( $removeExpiredFile === false ) return false;
		// init object (specify [uploaderID] to know which DOM to update)
		$fileUpload = new FileUpload($arguments['uploaderID']);
		// config : array of permitted file extensions (only allow image & doc by default)
		if ( isset(self::$config['fieldConfig'][$arguments['fieldName']]['filetype']) ) {
			$fileUpload->allowedExtensions = explode(',', self::$config['fieldConfig'][$arguments['fieldName']]['filetype']);
		} else {
			$fileUpload->allowedExtensions = explode(',', 'jpg,jpeg,png,gif,bmp,txt,doc,docx,pdf,ppt,pptx,xls,xlsx');
		}
		// config : max file upload size in bytes (default 10MB in library)
		// ===> scaffold-controller turns human-readable-filesize into numeric
		if ( isset(self::$config['fieldConfig'][$arguments['fieldName']]['filesize']) ) {
			$fileUpload->sizeLimit = self::$config['fieldConfig'][$arguments['fieldName']]['filesize_numeric'];
		}
		// config : assign unique name to avoid overwrite
		$originalName = urldecode($arguments[$arguments['uploaderID']]);
		$uniqueName = pathinfo($originalName, PATHINFO_FILENAME).'_'.uuid().'.'.pathinfo($originalName, PATHINFO_EXTENSION);
		$fileUpload->newFileName = $uniqueName;
		// start upload
		$uploadFileName = self::startUpload($fileUpload, $uploadDir);
		if ( $uploadFileName === false ) return false;
		// success!
		return array(
			'success' => true,
			'msg'     => 'File uploaded successfully',
			'baseUrl' => $uploadBaseUrl,
			'fileUrl' => $uploadBaseUrl.$uploadFileName,
		);
	}


	// perform validation on config
	public static function validateConfig() {
		global $fusebox;
		// check if any file-field
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
		} elseif ( empty(self::$config['layoutPath']) ) {
			self::$error = 'Scaffold config [layoutPath] is required';
			return false;
		// check uploader directory
		} elseif ( empty($fusebox->config['uploadDir']) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadDir] is required';
			return false;
		} elseif ( empty($fusebox->config['uploadBaseUrl']) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadBaseUrl] is required';
			return false;
		// check log component
		} elseif ( !empty(self::$config['writeLog']) and !class_exists('Log') ) {
			self::$error = 'Log component is required';
			return false;
		}
		// done!
		return true;
	}


} // Scaffold