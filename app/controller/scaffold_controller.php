<?php /*
<fusedoc>
	<history version="1.5">
		- allow custom breadcrumb
		- rename {F::fuseaction} to {F::command}
		- do not throw error when table not exists (usually at MySQL)
		- define {uploadBaseUrl} at fusebox-config scope instead of scaffold scope
		- deprecate {editField} and only accept {fieldConfig} to avoid any confusion
		- deprecate {previewBaseUrl} and only accept {uploadBaseUrl} to avoid any confusion
		- fix {editMode=classic} when not ajax-request
		- fix {editMode=inline} when invalid mode was specified
		- no delete button in edit form (only available in listing)
		- apply {format=one-to-many|many-to-many} instead of using {format=checkbox} in order to make things more clear
		- allow {listFilter} as array for sql parameter binding
		- remove expired files when uploading file
	</history>
	<history version="1.4.1">
		- accept {filesize} in string format (e.g. 1MB, 2k)
		- rename {previewBaseUrl} to {uploadBaseUrl} for better understanding
		- force {uploadBaseUrl} compulsory if there is [format=file] field
		- fix bug in {scriptPath} setting
	</history>
	<history version="1.4">apply {scriptPath} for further customization on user interface</history>
	<history version="1.3.2">debug : deselect all checkbox and remove all one-to-many/many-to-many relations</history>
	<history version="1.3.1">debug : ajax upload file-with-space-in-name will cause filename url-encoded</history>
	<history version="1.3">apply {writeLog} argument and write CRUD operation log</history>
	<history version="1.2.1">debug : retain url params when sorting</history>
	<history version="1.2">apply {allowSort} argument and allow click header to sort</history>
	<history version="1.1.2">debug : default {listFilter} to {1 = 1} to make scaffold compatible to both RedBean 3.x and 4.x</history>
	<history version="1.1.1">debug : allow no seq field</history>
	<history version="1.1">
		- accept <fieldConfig> as <editField>
		- apply <editMode=classic>
		- separate <modal.php> from <list.php>
	</history>
	<history version="1.0">
		- scaffold config
		- can define layout
		- can choose columns in listing
		- can choose edit mode (inline/modal)
		- auto implement enable/disable
		- explicit define create/edit/delete permission
		- file upload (ajax)
		- etc.
	</history>
	<io>
		<in>
			<structure name="$scaffold" comments="config">
				<!-- essential config -->
				<string name="beanType" />
				<string name="layoutPath" />
				<!-- below config are all optional -->
				<boolean name="allowNew" optional="yes" default="true" />
				<boolean name="allowEdit" optional="yes" default="true" />
				<boolean name="allowToggle" optional="yes" default="true" comments="applicable only when there is [disabled] field" />
				<boolean name="allowDelete" optional="yes" default="false" />
				<boolean name="allowSort" optional="yes" default="false" />
				<string name="paramNew" optional="yes" comments="extra url-param for [new] button" />
				<string name="paramEdit" optional="yes" comments="extra url-param for [edit] button" />
				<string name="editMode" optional="yes" comments="inline|modal|classic" />
				<string name="modalSize" optional="yes" comments="normal|large|max" />
				<array_or_string name="listFilter" optional="yes">
					<string name="0" optional="yes" comments="sql statement" oncondition="when {listFilter} is array" />
					<array name="1" optional="yes" comments="parameter" oncondition="when {listFilter} is array" />
				</array_or_string>
				<string name="listFilter" optional="yes" />
				<string name="listOrder" optional="yes" default="order by {seq} (if any), then by {id}" />
				<array name="listField" optional="yes" comments="determine fields to display in listing">
					<string name="+" comments="when no key specified, value is column-list" />
					<string name="~column-list~" comments="when key was specified, key is column list and value is column width" />
				</array>
				<structure name="displayName" optional="yes" comments="display name at table header">
					<string name="~column~" />
				</structure>
				<array name="modalField" optional="yes" comments="determine fields to show in modal form">
					<list name="+" comments="when no key specified, value is column list" />
					<list name="~column-list~" comments="when key was specified, key is column list and value is column width list" />
				</array>
				<structure name="fieldConfig" optional="yes" comments="options of each input field in edit form; also define sequence of field in modal edit form">
					<string name="+" comments="when no key specified, value is column name" />
					<structure name="~column~" comments="when key was specified, key is column name and value is field options">
						<string name="format" comments="normal|output|textarea|checkbox|radio|file|one-to-many|many-to-many" default="normal" />
						<array name="options" comments="show dropdown when specified">
							<string name="~key is option-value~" comments="value is option-text" />
						</array>
						<boolean name="required" />
						<boolean name="readonly" comments="output does not pass value; readonly does" />
						<string name="placeholder" default="column display name" />
						<string name="default" />
						<string name="style" />
						<string name="help" />
						<!-- below are for [format=file] only -->
						<string name="filesize" optional="yes" comments="max file size in bytes" />
						<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
						<boolean name="preview" optional="yes" />
					</structure>
				</structure>
				<!-- advanced settings for UI customization -->
				<structure name="scriptPath" optional="yes">
					<string name="list" optional="yes" />
					<string name="header" optional="yes" />
					<string name="row" optional="yes" />
					<string name="edit" optional="yes" />
					<string name="inline_edit" optional="yes" />
					<string name="modal" optional="yes" />
				</structure>
				<!-- advanced settings for file upload -->
				<string name="libPath" optional="yes" default="~fusebox.config.appPath~/../lib" comments="for simple-ajax-uploader library" />
				<!-- settings for log -->
				<boolean name="writeLog" optional="yes" comments="simply true to log all actions" />
			</structure>
			<structure name="config" scope="$fusebox" comments="for file field">
				<string name="uploadDir" optional="yes" comments="server path for saving file" />
				<string name="uploadBaseUrl" optional="yes" comments="web path for image source" />
			</structure>
			<array name="breadcrumb" scope="$arguments" optional="yes" comments="custom breadcrumb" />
		</in>
		<out />
	</io>
</fusedoc>
*/
// validation
F::error('configuration $scaffold["beanType"] is required', empty($scaffold['beanType']));
F::error('configuration $scaffold["beanType"] cannot contain underscore', strpos($scaffold['beanType'], '_') !== false);
F::error('configuration $scaffold["layoutPath"] is required', empty($scaffold['layoutPath']));
F::error('configuration $fusebox->config["uploadDir"] is required', empty($fusebox->config['uploadDir']) and F::is('*.upload_file'));
F::error('configuration $fusebox->config["uploadBaseUrl"] is required', empty($fusebox->config['uploadBaseUrl']) and F::is('*.upload_file'));
F::error('Log component is required', !empty($scaffold['writeLog']) and !class_exists('Log'));

// obtain all columns of specified table
// ===> if no column (or non-exist table)
// ===> rely on {fieldConfig} (if any)
try {
	$scaffold['_columns_'] = R::getColumns( $scaffold['beanType'] );
} catch (Exception $e) {
	if ( preg_match('/Base table or view not found/i', $e->getMessage()) ) {
		$scaffold['_columns_'] = array();
	} else {
		throw $e;
	}
}
if ( empty($scaffold['_columns_']) and isset($scaffold['fieldConfig']) ) {
	foreach ( $scaffold['fieldConfig'] as $_key => $_val ) {
		$_col = is_numeric($_key) ? $_val : $_key;
		$scaffold['_columns_'][$_col] = '~any~';
	}
}

// param default : permission
$scaffold['allowNew'] = isset($scaffold['allowNew']) ? $scaffold['allowNew'] : true;
$scaffold['allowEdit'] = isset($scaffold['allowEdit']) ? $scaffold['allowEdit'] : true;
$scaffold['allowToggle'] = isset($scaffold['allowToggle']) ? $scaffold['allowToggle'] : true;
$scaffold['allowDelete'] = isset($scaffold['allowDelete']) ? $scaffold['allowDelete'] : false;
$scaffold['allowSort'] = isset($scaffold['allowSort']) ? $scaffold['allowSort'] : false;

// param default : extra param
$scaffold['paramNew'] = isset($scaffold['paramNew']) ? $scaffold['paramNew'] : '';
$scaffold['paramEdit'] = isset($scaffold['paramEdit']) ? $scaffold['paramEdit'] : '';

// param default : edit mode
$scaffold['editMode'] = !empty($scaffold['editMode']) ? $scaffold['editMode'] : 'inline';

// param default : modal size
$scaffold['modalSize'] = !empty($scaffold['modalSize']) ? $scaffold['modalSize'] : 'normal';

// param default : list field
$scaffold['listField'] = isset($scaffold['listField']) ? $scaffold['listField'] : array_keys($scaffold['_columns_']);

// param default : list filter & order
$scaffold['listFilter'] = isset($scaffold['listFilter']) ? $scaffold['listFilter'] : '1 = 1 ';
if ( is_string($scaffold['listFilter']) ) {
	$scaffold['listFilter'] = array(
		$scaffold['listFilter'],
		array(),
	);
}
if ( $scaffold['allowSort'] and isset($arguments['sortField']) ) {
	// use sort-field specified (when necessary)
	$scaffold['listOrder'] = "ORDER BY {$arguments['sortField']} ";
	if ( isset($arguments['sortRule']) ) $scaffold['listOrder'] .= $arguments['sortRule'];
} elseif ( !isset($scaffold['listOrder']) ) {
	// otherwise, use specify a default list order (when necessary)
	$scaffold['listOrder'] = isset($scaffold['_columns_']['seq']) ? 'ORDER BY seq, id ' : 'ORDER BY id ';
}

// param default : sort field (extract from list order)
if ( !isset($arguments['sortField']) ) {
	$tmp = trim(str_replace('ORDER BY ', '', $scaffold['listOrder']));
	$tmp = explode(',', $tmp);  // turn {column-direction} list into array
	$tmp = $tmp[0];  // extract first {column-direction}
	$tmp = explode(' ', $tmp);
	$arguments['sortField'] = $tmp[0];  // extract {column}
	if ( isset($tmp[1]) ) $arguments['sortRule'] = $tmp[1];
}

// param default : field config
$scaffold['fieldConfig'] = isset($scaffold['fieldConfig']) ? $scaffold['fieldConfig'] : array();
$_arr = $scaffold['fieldConfig'];
$scaffold['fieldConfig'] = array();
foreach ( $_arr as $_key => $_val ) {
	if ( is_numeric($_key) ) {
		$scaffold['fieldConfig'][$_val] = array();
	} else {
		$scaffold['fieldConfig'][$_key] = $_val;
	}
}
unset($_arr);
foreach ( $scaffold['_columns_'] as $_col => $_colType ) {
	if ( !isset($scaffold['fieldConfig'][$_col]) ) {
		$scaffold['fieldConfig'][$_col] = array();
	}
}
if ( !isset($scaffold['fieldConfig']['id']) ) {
	$scaffold['fieldConfig']['id'] = array();
}

// param default : edit field (field {id} must be readonly)
$scaffold['fieldConfig']['id']['readonly'] = true;

// param default : edit field (field {seq} must be number)
if ( isset($scaffold['fieldConfig']['seq']) ) {
	$scaffold['fieldConfig']['seq']['format'] = 'number';
}

// param default : edit field (field {disabled} is dropdown by default)
if ( isset($scaffold['fieldConfig']['disabled']) and empty($scaffold['fieldConfig']['disabled']) ) {
	$scaffold['fieldConfig']['disabled'] = array('options' => array('0' => 'enable', '1' => 'disable'));
}

// param default : modal field
$scaffold['modalField'] = isset($scaffold['modalField']) ? $scaffold['modalField'] : array_keys($scaffold['fieldConfig']);
$_scaffoldModalField = $scaffold['modalField'];
$scaffold['modalField'] = array();
$_scaffoldModalFieldHasID = false;
foreach ( $_scaffoldModalField as $_key => $_val ) {
	if ( is_numeric($_key) ) {
		$scaffold['modalField'][$_val] = '';
	} else {
		$scaffold['modalField'][$_key] = $_val;
	}
	if ( ( is_numeric($_key) and strpos($_val.'|', 'id|') !== false ) or ( strpos($_key.'|', 'id|') !== false ) ) {
		$_scaffoldModalFieldHasID = true;
	}
}
if ( !$_scaffoldModalFieldHasID ) {
	$scaffold['modalField'] = array('id' => '') + $scaffold['modalField'];
}
unset($_scaffoldModalField);
foreach ( $scaffold['modalField'] as $_colList => $_colWidthList ) {
	$_cols = explode('|', $_colList);
	if ( !empty($_cols) and empty($_colWidthList) ) {
		if     ( count($_cols) == 1 ) $_colWidthList = '12';
		elseif ( count($_cols) == 2 ) $_colWidthList = '6|6';
		elseif ( count($_cols) == 3 ) $_colWidthList = '4|4|4';
		elseif ( count($_cols) == 4 ) $_colWidthList = '3|3|3|3';
		elseif ( count($_cols) == 5 ) $_colWidthList = '3|3|2|2|2';
		elseif ( count($_cols) == 6 ) $_colWidthList = '2|2|2|2|2|2';
		else $_colWidthList = implode('|', array_fill(0, 1, '1'));
		$scaffold['modalField'][$_colList] = $_colWidthList;
	}
}

// param default : script path
$scaffold['scriptPath'] = isset($scaffold['scriptPath']) ? $scaffold['scriptPath'] : array();
$arr = array('edit','header','inline_edit','list','row','modal');
foreach ( $arr as $i => $item ) {
	if ( !isset($scaffold['scriptPath'][$item]) ) {
		$scaffold['scriptPath'][$item] = F::config('appPath')."view/scaffold/{$item}.php";
	}
}

// param default : library path
$scaffold['libPath'] = isset($scaffold['libPath']) ? $scaffold['libPath'] : (dirname(F::config('appPath')).'/lib/');

// param default : write log
$scaffold['writeLog'] = isset($scaffold['writeLog']) ? $scaffold['writeLog'] : false;

// param fix : edit mode
if ( F::is('*.edit,*.new') and !F::ajaxRequest() ) {
	$scaffold['editMode'] = 'classic';
}
if ( !in_array($scaffold['editMode'], array('inline','modal','classic')) ) {
	$scaffold['editMode'] = 'inline';
}

// param fix : file size (string to number)
foreach ( $scaffold['fieldConfig'] as $itemName => $item ) {
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
		$scaffold['fieldConfig'][$itemName]['filesize_numeric'] = $item['filesize'];
	}
}




// run action...
switch ( $fusebox->action ) :


	// default show index
	case 'index':
		// get all records
		$beanList = R::find($scaffold['beanType'], $scaffold['listFilter'][0].' '.$scaffold['listOrder'], $scaffold['listFilter'][1]);
		// define exit point
		if ( $scaffold['allowNew'] ) {
			if ( $scaffold['editMode'] != 'inline' ) {
				$xfa['quick'] = "{$fusebox->controller}.quick_new".$scaffold['paramNew'];
			}
			$xfa['new'] = "{$fusebox->controller}.new".$scaffold['paramNew'];
		}
		if ( $scaffold['allowEdit'] ) {
			$xfa['edit'] = "{$fusebox->controller}.edit&nocache=".time().$scaffold['paramEdit'];
		}
		if ( $scaffold['allowDelete'] ) {
			$xfa['delete'] = "{$fusebox->controller}.delete";
		}
		if ( $scaffold['allowToggle'] ) {
			$xfa['enable'] = "{$fusebox->controller}.toggle&disabled=0";
			$xfa['disable'] = "{$fusebox->controller}.toggle&disabled=1";
		}
		if ( $scaffold['allowSort'] ) {
			// retain url params when change sorting
			$xfa['sort'] = $fusebox->controller;
			foreach ( $_GET as $key => $val ) {
				if ( $key != F::config('commandVariable') and $key != 'sortField' and $key != 'sortRule' ) {
					$xfa['sort'] .= "&{$key}={$val}";
				}
			}
		}
		// display list
		ob_start();
		include $scaffold['scriptPath']['list'];
		if ( $scaffold['editMode'] == 'modal' ) {
			include $scaffold['scriptPath']['modal'];
		}
		$layout['content'] = ob_get_clean();
		// breadcrumb
		if ( !isset($arguments['breadcrumb']) ) {
			$arguments['breadcrumb'] = array(ucfirst($scaffold['beanType']));
		}
		// layout
		include $scaffold['layoutPath'];
		break;


	// click cancel button to return to view mode
	// ===> or nothing when cancel of new record form
	case 'row':
		F::error("id was not specified", empty($arguments['id']));
		// get record
		$bean = R::load($scaffold['beanType'], $arguments['id']);
		// define exit point
		// ===> refer to index
		if ( $scaffold['allowEdit'] ) {
			$xfa['edit'] = "{$fusebox->controller}.edit&nocache=".time().$scaffold['paramEdit'];
		}
		if ( $scaffold['allowDelete'] ) {
			$xfa['delete'] = "{$fusebox->controller}.delete";
		}
		if ( $scaffold['allowToggle'] ) {
			$xfa['enable'] = "{$fusebox->controller}.toggle&disabled=0";
			$xfa['disable'] = "{$fusebox->controller}.toggle&disabled=1";
		}
		// display (when necessary)
		if ( !empty($bean->id) ) {
			include $scaffold['scriptPath']['row'];
		}
		break;
	case 'emptyRow':
		break;


	// get selected record
	case 'edit':
		F::error('id was not specified', empty($arguments['id']));
		$bean = R::load($scaffold['beanType'], $arguments['id']);
		F::error("record not found (id={$arguments['id']})", empty($bean->id));
		/***** do not case-break to re-use 'new' action *****/
	// create empty record (when necessary)
	case 'new':
	case 'quick_new':
		$bean = isset($bean) ? $bean : R::dispense($scaffold['beanType']);
		// define exit point
		if ( $scaffold['allowEdit'] ) {
			$xfa['submit'] = "{$fusebox->controller}.save";
		}
		$xfa['cancel'] = empty($bean->id) ? "{$fusebox->controller}.emptyRow" : "{$fusebox->controller}.row&id={$bean->id}";
		$xfa['ajaxUpload'] = "{$fusebox->controller}.upload_file";
		$xfa['ajaxUploadProgress'] = "{$fusebox->controller}.upload_file_progress";
		// display form
		ob_start();
		if ( F::is('*.quick_new') or $scaffold['editMode'] == 'inline' ) {
			include $scaffold['scriptPath']['inline_edit'];
		} else {
			include $scaffold['scriptPath']['edit'];
		}
		$layout['content'] = ob_get_clean();
		// show with layout (when necessary)
		if ( F::ajaxRequest() ) {
			echo $layout['content'];
		} else {
			// breadcrumb
			if ( !isset($arguments['breadcrumb']) ) {
				$arguments['breadcrumb'] = array(ucfirst($scaffold['beanType']), F::is('*.edit') ? 'Edit' : 'New');
			}
			// layout
			include $scaffold['layoutPath'];
		}
		break;


	// show or hide record
	// ===> show record row after save
	case 'toggle':
		F::error('toggle is not allowed', !$scaffold['allowToggle']);
		F::error("id was not specified", empty($arguments['id']));
		F::error("argument [disabled] is required", !isset($arguments['disabled']));
		// save record
		try {
			$bean = R::load($scaffold['beanType'], $arguments['id']);
			$bean->disabled = $arguments['disabled'];
			$id = R::store($bean);
			// write log (when necessary)
			if ( $scaffold['writeLog'] ) {
				$logResult = Log::write(array(
					'action' => empty($arguments['disabled']) ? "ENABLE_{$scaffold['beanType']}" : "DISABLE_{$scaffold['beanType']}",
					'entity_type' => $scaffold['beanType'],
					'entity_id' => $arguments['id'],
				));
				F::error(Log::error(), !$logResult);
			}
			// back to list
			F::redirect("{$fusebox->controller}.row&id={$id}", F::ajaxRequest());
			F::redirect($fusebox->controller, !F::ajaxRequest());
		// catch any error
		} catch (Exception $e) {
			F::error($e->getMessage());
		}
		break;


	// save record (and go to default page)
	case 'save':
		F::error('data were not submitted', empty($arguments['data']));
		F::error('create record not allowed', !$scaffold['allowNew'] and empty($arguments['data']['id']));
		F::error('update record not allowed', !$scaffold['allowEdit'] and !empty($arguments['data']['id']));
		try {
			// get current bean or create new bean
			if ( !empty($arguments['data']['id']) ) {
				$bean = R::load($scaffold['beanType'], $arguments['data']['id']);
				if ( $scaffold['writeLog'] ) $beanBeforeSave = $bean->export();
			} else {
				$bean = R::dispense($scaffold['beanType']);
			}
			// fix submitted multi-selection value
			foreach ( $scaffold['fieldConfig'] as $fieldName => $field ) {
				// default value when select no item
				if ( isset($field['format']) and in_array($field['format'], array('checkbox','one-to-many','many-to-many')) ) {
					$arguments['data'][$fieldName] = isset($arguments['data'][$fieldName]) ? $arguments['data'][$fieldName] : array();
				}
				// extract {one-to-many|many-to-many} from submitted data before saving
				if ( isset($field['format']) and in_array($field['format'], array('one-to-many','many-to-many')) ) {
					$associateName = str_replace('_id', '', $fieldName);
					$propertyName = ( ( $field['format'] == 'one-to-many' ) ? 'own' : 'shared' ) . ucfirst($associateName);
					$bean->{$propertyName} = array();
					foreach ( $arguments['data'][$fieldName] as $associateID ) {
						$associateBean = R::load($associateName, $associateID);
						$bean->{$propertyName}[] = $associateBean;
					}
					unset($arguments['data'][$fieldName]);
				// turn checkbox into pipe-delimited list
				} elseif ( isset($field['format']) and $field['format'] == 'checkbox' ) {
					$arguments['data'][$fieldName] = implode('|', $arguments['data'][$fieldName]);
				}
			}
			// put submitted data into bean
			$bean->import($arguments['data']);
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
			// write log (when necessary)
			if ( $scaffold['writeLog'] ) {
				if ( !empty($arguments['data']['id']) and method_exists('Bean', 'diff') ) {
					$logRemark = Bean::diff($beanBeforeSave, $bean);
				} elseif ( empty($arguments['data']['id']) and method_exists('Bean', 'toString') ) {
					$logRemark = Bean::toString($bean);
				} else {
					$logRemark = null;
				}
				$logResult = Log::write(array(
					'action' => empty($arguments['data']['id']) ? "CREATE_{$scaffold['beanType']}" : "UPDATE_{$scaffold['beanType']}",
					'entity_type' => $scaffold['beanType'],
					'entity_id' => $id,
					'remark' => $logRemark,
				));
				F::error(Log::error(), !$logResult);
			}
		// catch any error...
		} catch (Exception $e) {
			F::error($e->getMessage());
		}
		// finish
		F::redirect("{$fusebox->controller}.row&id={$id}", F::ajaxRequest());
		F::redirect($fusebox->controller, !F::ajaxRequest());
		break;


	// delete record (and go to default page)
	case 'delete':
		F::error('delete is not allowed', !$scaffold['allowDelete']);
		F::error('id was not specified', empty($arguments['id']));
		// delete record
		try {
			$bean = R::load($scaffold['beanType'], $arguments['id']);
			if ( $scaffold['writeLog'] ) $beanBeforeDelete = $bean->export();
			R::trash($bean);
		// catch any error...
		} catch (Exception $e) {
			F::error($e->getMessage());
		}
		// write log (when necessary)
		if ( $scaffold['writeLog'] ) {
			$logResult = Log::write(array(
				'action' => "DELETE_{$scaffold['beanType']}",
				'entity_type' => $scaffold['beanType'],
				'entity_id' => $arguments['id'],
				'remark' => method_exists('Bean', 'toString') ? Bean::toString($beanBeforeDelete) : null,
			));
			F::error(Log::error(), !$logResult);
		}
		// return to index page if not ajax
		// ===> otherwise, simply show nothing (in order to hide row)
		F::redirect($fusebox->controller, !F::ajaxRequest());
		break;


	// ajax file upload
	case 'upload_file':
		// load library
		if ( !class_exists('FileUpload') ) {
			require $scaffold['libPath'].'simple-ajax-uploader/1.10.1/extras/Uploader.php';
		}
		// validation
		$err = array();
		if ( empty($arguments['uploaderID']) ) {
			$err[] = 'argument [uploaderID] is required';
		} elseif ( !isset($arguments[$arguments['uploaderID']]) ) {
			$err[] = "data of [{$arguments['uploaderID']}] was not submitted";
		}
		if ( empty($arguments['fieldName']) ) {
			$err[] = 'argument [fieldName] is required';
		} elseif ( !isset($scaffold['fieldConfig'][$arguments['fieldName']]) ) {
			$err[] = "field config for [{$arguments['fieldName']}] is required";
		} elseif ( $scaffold['fieldConfig'][$arguments['fieldName']]['format'] != 'file' ) {
			$err[] = "field [{$arguments['fieldName']}] must be [format=file]";
		}
		// only proceed when ok...
		if ( !empty($err) ) {
			$result = array('success' => false, 'msg' => implode("\n", $err));
		} else {
			// fix config
			$uploadDir  = $fusebox->config['uploadDir'];
			$uploadDir .= in_array(substr($uploadDir, -1), array('/','\\')) ? '' : '/';
			$uploadDir .= "{$scaffold['beanType']}/{$arguments['fieldName']}/";
			$uploadBaseUrl  = $fusebox->config['uploadBaseUrl'];
			$uploadBaseUrl .= in_array(substr($uploadBaseUrl, -1), array('/','\\')) ? '' : '/';
			$uploadBaseUrl .= "{$scaffold['beanType']}/{$arguments['fieldName']}/";
			// create directory (when necessary)
			if ( !file_exists( $uploadDir ) ) {
				mkdir($uploadDir, 0766, true);
			}
			// remove expired file
			if ( empty($GLOBALS['FUSEBOX_UNIT_TEST']) ) {
				F::invoke("{$fusebox->controller}.remove_expired_file", array(
					'fieldName' => $arguments['fieldName'],
					'uploadDir' => $uploadDir,
				));
			}
			// init object (specify [uploaderID] to know which DOM to update)
			$fileUpload = new FileUpload($arguments['uploaderID']);
			// config : file upload directory (include trailing slash)
			$fileUpload->uploadDir = $uploadDir;
			// config : array of permitted file extensions (only allow image & doc by default)
			if ( isset($scaffold['fieldConfig'][$arguments['fieldName']]['filetype']) ) {
				$fileUpload->allowedExtensions = explode(',', $scaffold['fieldConfig'][$arguments['fieldName']]['filetype']);
			} else {
				$fileUpload->allowedExtensions = explode(',', 'jpg,jpeg,png,gif,bmp,txt,doc,docx,pdf,ppt,pptx,xls,xlsx');
			}
			// config : max file upload size in bytes (default 10MB in library)
			// ===> scaffold-controller turns human-readable-filesize into numeric
			if ( isset($scaffold['fieldConfig'][$arguments['fieldName']]['filesize']) ) {
				$fileUpload->sizeLimit = $scaffold['fieldConfig'][$arguments['fieldName']]['filesize_numeric'];
			}
			// config : assign unique name to avoid overwrite
			$originalName = urldecode($arguments[$arguments['uploaderID']]);
			$uniqueName = pathinfo($originalName, PATHINFO_FILENAME).'_'.uniqid().'.'.pathinfo($originalName, PATHINFO_EXTENSION);
			$fileUpload->newFileName = $uniqueName;
			// start upload
			if ( empty($GLOBALS['FUSEBOX_UNIT_TEST']) ) {
				$uploadResult = $fileUpload->handleUpload();
				$uploadFileName = $fileUpload->getFileName();
			} else {
				$uploadResult = true;
				$uploadFileName = $uniqueName;
			}
			// result
			$result = array(
				'success' => $uploadResult,
				'msg' => $uploadResult ? 'File uploaded successfully' : $fileUpload->getErrorMsg(),
				'baseUrl' => $uploadBaseUrl,
				'fileUrl' => $uploadBaseUrl.$uploadFileName,
			);
		}
		// return to browser as json response
		echo json_encode($result);
		break;
	case 'upload_file_progress':
		require $scaffold['libPath'].'simple-ajax-uploader/1.10.1/extras/uploadProgress.php';
		break;


	// remove uploaded files which have parent record deleted
	case 'remove_expired_file':
		F::error('invalid access', !F::isInvoke() and empty($GLOBALS['FUSEBOX_UNIT_TEST']));
		F::error('argument [fieldName] is required', empty($arguments['fieldName']));
		F::error('argument [uploadDir] is required', empty($arguments['uploadDir']));
		// get all records of specific field
		// ===> only required file name
		$nonOrphanFiles = R::getCol("SELECT {$arguments['fieldName']} FROM {$scaffold['beanType']} WHERE {$arguments['fieldName']} IS NOT NULL");
		foreach ( $nonOrphanFiles as $i => $path ) {
			if ( !empty($path) ) {
				$nonOrphanFiles[$i] = basename($path);
			}
		}
		// go through every file in upload directory
		if ( !empty($nonOrphanFiles) ) {
			foreach (glob($arguments['uploadDir']."*.*") as $filename) {
				// only remove orphan file older than one day
				// ===> avoid remove file which ajax-upload by user but not save record yet
				$isOrphan = !in_array($filename, $nonOrphanFiles);
				$isDayOld = ( filemtime($arguments['uploadDir'].$filename) < strtotime(date('-1 day')) );
				if ( $isOrphan and $isDayOld ) {
					unlink($arguments['uploadDir'].$filename);
				}
			}
		}
		break;


	default:
		F::pageNotFound();


endswitch;