<?php /*
<fusedoc>
	<history version="1.4.2">
		- make {$scaffold} config be global
		- rename {F::fuseaction} to {F::command}
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
				<structure name="fieldConfig|editField" optional="yes" comments="options of each input field in edit form; also define sequence of field in modal edit form">
					<string name="+" comments="when no key specified, value is column name" />
					<structure name="~column~" comments="when key was specified, key is column name and value is field options">
						<string name="format" comments="normal|output|textarea|checkbox|radio|file" default="normal" />
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
						<!-- below are for [format=checkbox] only -->
						<boolean name="many-to-many" optional="yes" />
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
				<string name="uploadBaseUrl" optional="yes" comments="for [format=file] field" />
				<!-- settings for log -->
				<boolean name="writeLog" optional="yes" comments="simply true to log all actions" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// validation
F::error('configuration $scaffold["beanType"] is required', empty($scaffold['beanType']));
F::error('configuration $scaffold["beanType"] cannot contain underscore', strpos($scaffold['beanType'], '_') !== false);
F::error('configuration $scaffold["layoutPath"] is required', empty($scaffold['layoutPath']));
F::error('Log component is required', !empty($scaffold['writeLog']) and !class_exists('Log'));

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
$scaffold['_columns_'] = R::getColumns( $scaffold['beanType'] );
$scaffold['listField'] = isset($scaffold['listField']) ? $scaffold['listField'] : array_keys($scaffold['_columns_']);

// param default : list filter & order
$scaffold['listFilter'] = isset($scaffold['listFilter']) ? $scaffold['listFilter'] : '1 = 1 ';
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

// param default : edit field
if ( isset($scaffold['fieldConfig']) ) {
	$scaffold['editField'] = $scaffold['fieldConfig'];
}
$scaffold['editField'] = isset($scaffold['editField']) ? $scaffold['editField'] : array();
$_scaffoldEditField = $scaffold['editField'];
$scaffold['editField'] = array();
foreach ( $_scaffoldEditField as $_key => $_val ) {
	if ( is_numeric($_key) ) {
		$scaffold['editField'][$_val] = array();
	} else {
		$scaffold['editField'][$_key] = $_val;
	}
}
unset($_scaffoldEditField);
foreach ( $scaffold['_columns_'] as $_col => $_colType ) {
	if ( !isset($scaffold['editField'][$_col]) ) {
		$scaffold['editField'][$_col] = array();
	}
}
if ( !isset($scaffold['editField']['id']) ) {
	$scaffold['editField']['id'] = array();
}

// param default : edit field (field {id} must be readonly)
$scaffold['editField']['id']['readonly'] = true;

// param default : edit field (field {seq} must be number)
if ( isset($scaffold['editField']['seq']) ) {
	$scaffold['editField']['seq']['format'] = 'number';
}

// param default : edit field (field {disabled} is dropdown by default)
if ( isset($scaffold['editField']['disabled']) and empty($scaffold['editField']['disabled']) ) {
	$scaffold['editField']['disabled'] = array('options' => array('0' => 'enable', '1' => 'disable'));
}

// param default : modal field
$scaffold['modalField'] = isset($scaffold['modalField']) ? $scaffold['modalField'] : array_keys($scaffold['editField']);
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

// validation & param fix : upload-base-url
if ( isset($scaffold['previewBaseUrl']) ) {
	$scaffold['uploadBaseUrl'] = $scaffold['previewBaseUrl'];  // for backward compatibility
}
foreach ( $scaffold['editField'] as $item ) {
	F::error('configuration $scaffold["uploadBaseUrl"] is required for [format=file] field', !isset($scaffold['uploadBaseUrl']) and isset($item['format']) and $item['format'] == 'file');
}
if ( isset($scaffold['uploadBaseUrl']) and !in_array(substr($scaffold['uploadBaseUrl'], -1), array('/','\\')) ) {
	$scaffold['uploadBaseUrl'] .= '/';
}

// param fix : file size (string to number)
foreach ( $scaffold['editField'] as $itemName => $item ) {
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
		$scaffold['editField'][$itemName]['filesize_numeric'] = $item['filesize'];
	}
}




// run action...
switch ( $fusebox->action ) :


	// default show index
	case 'index':
		// get all records
		$beanList = R::find($scaffold['beanType'], "{$scaffold['listFilter']} {$scaffold['listOrder']}");
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
		$arguments['breadcrumb'] = array( ucfirst($scaffold['beanType']) );
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
		// display
		include $scaffold['scriptPath']['row'];
		break;
	case 'emptyRow':
		break;


	// get selected record
	case 'edit':
		F::error('id was not specified', empty($arguments['id']));
		$bean = R::load($scaffold['beanType'], $arguments['id']);
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
		if ( $scaffold['allowDelete'] and F::command('action') == 'edit' ) {
			$xfa['delete'] = "{$fusebox->controller}.delete";
		}
		$xfa['ajaxUpload'] = "{$fusebox->controller}.upload_file";
		$xfa['ajaxUploadProgress'] = "{$fusebox->controller}.upload_file_progress";
		// display form
		ob_start();
		if ( ( $scaffold['editMode'] == 'modal' or !F::ajaxRequest() ) and !F::is('*.quick_new') ) {
			include $scaffold['scriptPath']['edit'];
		} else {
			include $scaffold['scriptPath']['inline_edit'];
		}
		$layout['content'] = ob_get_clean();
		// show with layout (when necessary)
		if ( F::ajaxRequest() ) {
			echo $layout['content'];
		} else {
			$arguments['breadcrumb'] = array($scaffold['beanType']);
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
			// default value of one-to-many & many-to-many
			foreach ( $scaffold['editField'] as $fieldName => $field ) {
				if ( !empty($field['format']) and $field['format'] == 'checkbox' ) {
					$arguments['data'][$fieldName] = isset($arguments['data'][$fieldName]) ? $arguments['data'][$fieldName] : array();
				}
			}
			// extract one-to-many & many-to-many from submitted data before saving
			foreach ( $arguments['data'] as $key => $val ) {
				if ( is_array($val) ) {
					$associateName = str_replace('_id', '', $key);
					$propertyName = ( !empty($scaffold['editField'][$key]['many-to-many']) ? 'shared' : 'own' ) . ucfirst($associateName);
					$bean->{$propertyName} = array();
					foreach ( $val as $associateID ) {
						$associateBean = R::load($associateName, $associateID);
						$bean->{$propertyName}[] = $associateBean;
					}
					unset($arguments['data'][$key]);
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
		require $scaffold['libPath'].'simple-ajax-uploader/1.10.1/extras/Uploader.php';
		// validation
		if ( empty($arguments['uploaderID']) ) {
			$err = 'argument [uploaderID] is required';
		} elseif ( empty($arguments['fieldName']) ) {
			$err = 'argument [fieldName] is required';
		}
		// only proceed when ok...
		if ( !empty($err) ) {
			$result = array('success' => false, 'msg' => $err);
		} else {
			// init object (specify [uploaderID] to know which DOM to update)
			$fileUpload = new FileUpload($arguments['uploaderID']);
			// config : file upload directory (include trailing slash)
			$fileUpload->uploadDir = "{$fusebox->config['uploadDir']}/{$scaffold['beanType']}/";
			// config : array of permitted file extensions (only allow image & doc by default)
			if ( isset($scaffold['editField'][$arguments['fieldName']]['filetype']) ) {
				$fileUpload->allowedExtensions = explode(',', $scaffold['editField'][$arguments['fieldName']]['filetype']);
			} else {
				$fileUpload->allowedExtensions = explode(',', 'jpg,jpeg,png,gif,bmp,txt,doc,docx,pdf,ppt,pptx,xls,xlsx');
			}
			// config : max file upload size in bytes (default 10MB in library)
			// ===> scaffold-controller turns human-readable-filesize into numeric
			if ( isset($scaffold['editField'][$arguments['fieldName']]['filesize']) ) {
				$fileUpload->sizeLimit = $scaffold['editField'][$arguments['fieldName']]['filesize_numeric'];
			}
			// config : assign unique name to avoid overwrite
			$originalName = urldecode($arguments[$arguments['uploaderID']]);
			$uniqueName = pathinfo($originalName, PATHINFO_FILENAME).'_'.uniqid().'.'.pathinfo($originalName, PATHINFO_EXTENSION);
			$fileUpload->newFileName = $uniqueName;
			// create directory (when necessary)
			if ( !file_exists( $fileUpload->uploadDir ) ) {
				mkdir($fileUpload->uploadDir, 0766, true);
			}
			// start upload
			$uploadResult = $fileUpload->handleUpload();
			// result
			$result = array(
				'success' => $uploadResult,
				'msg' => $uploadResult ? 'File uploaded successfully' : $fileUpload->getErrorMsg(),
				'baseUrl' => $scaffold['uploadBaseUrl'],
				'fileUrl' => "{$scaffold['uploadBaseUrl']}{$scaffold['beanType']}/".$fileUpload->getFileName()
			);
		}
		// return to browser as json response
		echo json_encode($result);
		break;


	case 'upload_file_progress':
		require $scaffold['libPath'].'simple-ajax-uploader/1.10.1/extras/uploadProgress.php';
		break;


	default:
		F::pageNotFound();


endswitch;