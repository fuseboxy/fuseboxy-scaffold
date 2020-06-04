<?php /*
<fusedoc>
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
				<boolean name="allowSort" optional="yes" default="true" />
				<string name="editMode" optional="yes" comments="inline|modal|basic" />
				<string name="modalSize" optional="yes" comments="sm|md|lg|xl|max" />
				<array_or_string name="listFilter" optional="yes">
					<string name="0" optional="yes" comments="sql statement" oncondition="when {listFilter} is array" />
					<array  name="1" optional="yes" comments="sql parameter" oncondition="when {listFilter} is array" />
				</array_or_string>
				<string name="listOrder" optional="yes" default="order by {seq} (if any), then by {id}" />
				<array name="listField" optional="yes" comments="determine fields to display in listing">
					<string name="+" comments="when no key specified, value is column list" />
					<string name="~columnList~" comments="when key was specified, key is column list and value is column width" />
				</array>
				<array name="modalField" optional="yes" comments="determine fields to show in modal form">
					<list name="+" comments="when no key specified, value is column list" />
					<list name="~columnList~" comments="when key was specified, key is column list and value is column width list" />
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
						<string name="class" optional="yes" />
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
				<!-- advanced settings for pagination -->
				<boolean name="pagination" optional="yes" comments="simply set true to enable pagination with default settings" />
				<structure name="pagination" optional="yes" comments="further specify pagination settings">
					<number name="recordPerPage" optional="yes" default="50" />
					<number name="pageVisible" optional="yes" default="10" />
				</structure>
				<!-- settings for log -->
				<boolean name="writeLog" optional="yes" comments="simply true to log all actions" />
			</structure>
			<structure name="Scaffold::$libPath">
				<string name="uploadFile" />
				<string name="uploadFileProgress" />
			</structure>
			<structure name="config" scope="$fusebox" comments="for file field">
				<string name="uploadDir" optional="yes" comments="server path for saving file" />
				<string name="uploadUrl" optional="yes" comments="web path for image source" />
			</structure>
			<array name="breadcrumb" scope="$arguments" optional="yes" comments="custom breadcrumb" />
		</in>
		<out />
	</io>
</fusedoc>
*/
// disallow accessing this controller directly
F::error('Forbidden', F::is('scaffold.*'));

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
		F::error(Scaffold::error(), $beanList === false);
		// define exit point
		if ( $scaffold['allowNew'] ) {
			if ( $scaffold['editMode'] != 'inline' ) {
				$xfa['quick'] = "{$fusebox->controller}.quick";
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
		// pagination
		if ( !empty($scaffold['pagination']) ) {
			$arguments['pagination'] = $scaffold['pagination'];
		}
		// breadcrumb
		if ( !isset($arguments['breadcrumb']) ) {
			$arguments['breadcrumb'] = array(ucfirst($scaffold['beanType']));
		}
		// layout
		if ( $scaffold['layoutPath'] === false ) {
			echo $layout['content'];
		} else {
			include $scaffold['layoutPath'];
		}
		break;


	// click cancel button to return to view mode
	// ===> or nothing when cancel of new record form
	case 'row':
		F::error("id was not specified", empty($arguments['id']));
		// get record
		$bean = Scaffold::getBean($arguments['id']);
		F::error(Scaffold::error(), $bean === false);
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
	case 'empty':
		break;


	// edit record (or create new record)
	case 'edit':
		F::error('id was not specified', empty($arguments['id']));
	case 'new':
	case 'quick':
		$bean = Scaffold::getBean( F::is('*.edit') ? $arguments['id'] : null );
		F::error(Scaffold::error(), $bean === false);
		// define exit point
		if ( $scaffold['allowEdit'] ) {
			$xfa['submit'] = "{$fusebox->controller}.save";
		}
		$xfa['cancel'] = empty($bean->id) ? "{$fusebox->controller}.empty" : "{$fusebox->controller}.row&id={$bean->id}";
		$xfa['ajaxUpload'] = "{$fusebox->controller}.upload_file";
		$xfa['ajaxUploadProgress'] = "{$fusebox->controller}.upload_file_progress";
		// display form
		ob_start();
		if ( F::is('*.quick') or $scaffold['editMode'] == 'inline' ) {
			include $scaffold['scriptPath']['inline_edit'];
		} else {
			include $scaffold['scriptPath']['edit'];
		}
		$layout['content'] = ob_get_clean();
		// show with layout (when necessary)
		if ( F::ajaxRequest() or $scaffold['layoutPath'] === false ) {
			echo $layout['content'];
		} else {
			// breadcrumb
			if ( !isset($arguments['breadcrumb']) ) $arguments['breadcrumb'] = array(ucfirst($scaffold['beanType']), F::is('*.edit') ? 'Edit' : 'New');
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
		require Scaffold::$libPath['uploadFileProgress'];
		break;


	default:
		F::pageNotFound();


endswitch;