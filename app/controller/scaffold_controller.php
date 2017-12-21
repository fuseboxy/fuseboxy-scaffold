<?php /*
<fusedoc>
	<history version="1.2">
		- apply [pre-help] on [fieldConfig]
		- apply horizontal line on [listField]
		- apply style [break-word] to link displayed in row
	</history>
	<history version="1.1.3">
		- minor adjustment on font size of checkbox/radio
		- fix bug in showing checkbox value
	</history>
	<history version="1.1.2">
		- fix serious bug of write log in toggleBean method
	</history>
	<history version="1.1.1">
		- split header/inline-edit/row buttons into seperate files
		- minor change on style of disabled row
	</history>
	<history version="1.1">
		- apply {Scaffold} component in order to simplify controller logic
		- apply {script.scaffold.js} to consolidate javascript logic
		- apply placeholder to input of [format=file]
		- apply input of [format=date|time|datetime]
		- allow file upload to {AWS S3} and {FTP}
	</history>
	<history version="1.0.1">
		- apply UUID to make sure event row/input are having unique id (PHP function uniqid() does not guarantee uniqueness...)
		- fix bug of scaffold file upload preview
	</history>
	<history version="1.0">
		- apply custom breadcrumb
		- apply {format=wysiwyg} input field
		- apply {format=one-to-many|many-to-many} instead of using {format=checkbox} in order to make things more clear
		- apply {listFilter} of array for sql parameter binding
		- deprecate {F::fuseaction} and rename method to {F::command}
		- deprecate {editField} and only accept {fieldConfig} to avoid any confusion
		- deprecate {previewBaseUrl} and only accept {uploadBaseUrl} to avoid any confusion
		- deprecate {paramNew} and {paramEdit} because it can be easily replaced by session
		- deprecate {displayName} and replace by {fieldConfig.label} to make the config structure more simple
		- fix bug of throwing error when table not exists (usually at MySQL)
		- fix bug of {editMode=classic} when not ajax-request
		- fix bug of {editMode=inline} when invalid mode was specified
		- define {uploadBaseUrl} at fusebox-config scope instead of scaffold scope
		- no delete button in edit form (delete button only available in listing)
		- remove expired files when uploading file
	</history>
	<history version="0.9.1">
		- accept {filesize} in string format (e.g. 1MB, 2k)
		- rename {previewBaseUrl} to {uploadBaseUrl} for better understanding
		- force {uploadBaseUrl} compulsory if there is [format=file] field
		- fix bug in {scriptPath} setting
	</history>
	<history version="0.9">apply {scriptPath} for further customization on user interface</history>
	<history version="0.8.2">debug : deselect all checkbox and remove all one-to-many/many-to-many relations</history>
	<history version="0.8.1">debug : ajax upload file-with-space-in-name will cause filename url-encoded</history>
	<history version="0.8">apply {writeLog} argument and write CRUD operation log</history>
	<history version="0.7.1">debug : retain url params when sorting</history>
	<history version="0.7">apply {allowSort} argument and allow click header to sort</history>
	<history version="0.6.2">debug : default {listFilter} to {1 = 1} to make scaffold compatible to both RedBean 3.x and 4.x</history>
	<history version="0.6.1">debug : allow no seq field</history>
	<history version="0.6">
		- accept <fieldConfig> as <editField>
		- apply <editMode=classic>
		- separate <modal.php> from <list.php>
	</history>
	<history version="0.5">
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
				<string name="editMode" optional="yes" comments="inline|modal|classic" />
				<string name="modalSize" optional="yes" comments="normal|large|max" />
				<array_or_string name="listFilter" optional="yes">
					<string name="0" optional="yes" comments="sql statement" oncondition="when {listFilter} is array" />
					<array  name="1" optional="yes" comments="sql parameter" oncondition="when {listFilter} is array" />
				</array_or_string>
				<string name="listOrder" optional="yes" default="order by {seq} (if any), then by {id}" />
				<array name="listField" optional="yes" comments="determine fields to display in listing">
					<string name="+" comments="when no key specified, value is column-list" />
					<string name="~column-list~" comments="when key was specified, key is column list and value is column width" />
				</array>
				<array name="modalField" optional="yes" comments="determine fields to show in modal form">
					<list name="+" comments="when no key specified, value is column list" />
					<list name="~column-list~" comments="when key was specified, key is column list and value is column width list" />
				</array>
				<structure name="fieldConfig" optional="yes" comments="options of each input field in edit form; also define sequence of field in modal edit form">
					<string name="+" comments="when no key specified, value is column name" />
					<structure name="~column~" comments="when key was specified, key is column name and value is field options">
						<string name="label" optional="yes" comments="display name at table/form header">
						<string name="format" optional="yes" comments="text|hidden|output|textarea|checkbox|radio|file|one-to-many|many-to-many|wysiwyg|url" default="text" />
						<array name="options" optional="yes" comments="show dropdown when specified">
							<string name="~key is option-value~" comments="value is option-text" />
						</array>
						<boolean name="required" optional="yes" />
						<boolean name="readonly" optional="yes" comments="output does not pass value; readonly does" />
						<string name="placeholder" optional="yes" default="column display name" />
						<string name="value" optional="yes" comments="force filling with this value even if field has value" />
						<string name="default" optional="yes" comments="filling with this value if field has no value" />
						<string name="style" optional="yes" />
						<string name="pre-help" optional="yes" comments="help text show before input field" />
						<string name="help" optional="yes" comments="help text show after input field" />
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
// allow component to access and update the config variable
Scaffold::$config = &$scaffold;


// validation
$validateConfig = Scaffold::validateConfig();
F::error(Scaffold::error(), $validateConfig === false);

// set parameter default value
$setParamDefault = Scaffold::setParamDefault();
F::error(Scaffold::error(), $setParamDefault === false);


// adjust parameter
$fixParam = Scaffold::fixParam();
F::error(Scaffold::error(), $fixParam === false);



// start!
switch ( $fusebox->action ) :


	// default show index
	case 'index':
		// get all records
		$beanList = Scaffold::getBeanList();
		// define exit point
		if ( $scaffold['allowNew'] ) {
			if ( $scaffold['editMode'] != 'inline' ) {
				$xfa['quick'] = "{$fusebox->controller}.quick_new";
			}
			$xfa['new'] = "{$fusebox->controller}.new";
		}
		if ( $scaffold['allowEdit'] ) {
			$xfa['edit'] = "{$fusebox->controller}.edit&nocache=".time();
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
			$xfa['edit'] = "{$fusebox->controller}.edit&nocache=".time();
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


	// edit record (or create new record)
	case 'edit':
		F::error('id was not specified', empty($arguments['id']));
	case 'new':
	case 'quick_new':
		$bean = Scaffold::getBean( F::is('*.edit') ? $arguments['id'] : null );
		F::error(Scaffold::error(), $bean === false);
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
		$toggleBean = Scaffold::toggleBean($arguments['id'], !$arguments['disabled']);
		F::error(Scaffold::error(), $toggleBean === false);
		// back to list
		F::redirect("{$fusebox->controller}.row&id={$arguments['id']}", F::ajaxRequest());
		F::redirect($fusebox->controller, !F::ajaxRequest());
		break;


	// save record (and go to default page)
	case 'save':
		F::error('data were not submitted', empty($arguments['data']));
		F::error('create record not allowed', !$scaffold['allowNew'] and empty($arguments['data']['id']));
		F::error('update record not allowed', !$scaffold['allowEdit'] and !empty($arguments['data']['id']));
		// save record
		$id = Scaffold::saveBean($arguments['data']);
		F::error(Scaffold::error(), $id === false);
		// finish
		F::redirect("{$fusebox->controller}.row&id={$id}", F::ajaxRequest());
		F::redirect($fusebox->controller, !F::ajaxRequest());
		break;


	// delete record (and go to default page)
	case 'delete':
		F::error('delete is not allowed', !$scaffold['allowDelete']);
		F::error('id was not specified', empty($arguments['id']));
		// delete record
		$deleteBean = Scaffold::deleteBean($arguments['id']);
		F::error(Scaffold::error(), $deleteBean === false);
		// return to index page if not ajax
		// ===> otherwise, simply show nothing (in order to hide row)
		F::redirect($fusebox->controller, !F::ajaxRequest());
		break;


	// ajax file upload
	case 'upload_file':
		$result = Scaffold::uploadFile($arguments);
		$result = ( $result !== false ) ? $result : array(
			'success' => false,
			'msg' => Scaffold::error(),
		);
		echo json_encode($result);
		break;
	// ajax upload progress
	case 'upload_file_progress':
		require $scaffold['libPath'].'simple-ajax-uploader/1.10.1/extras/uploadProgress.php';
		break;


	default:
		F::pageNotFound();


endswitch;