<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold" comments="config">
				<!-- essential config -->
				<string name="beanType" />
				<string name="layoutPath" />
				<string_or_structure name="retainParam" optional="yes" comments="retain additional parameter (e.g. fuseaction=product.list&category=foo)" format="query-string or associated-array" />
				<!-- below config are all optional -->
				<string name="editMode" optional="yes" comments="inline|modal|inline-modal|basic" />
				<string name="modalSize" optional="yes" comments="max|xxl|xl|lg|md|sm|xs" />
				<boolean name="stickyHeader" optional="yes" default="false" />
				<boolean name="allowNew" optional="yes" default="true" />
				<boolean name="allowQuick" optional="yes" default="true" />
				<boolean name="allowEdit" optional="yes" default="true" />
				<boolean name="allowToggle" optional="yes" default="true" comments="applicable only when there is [disabled] field" />
				<boolean name="allowDelete" optional="yes" default="false" />
				<boolean_or_structure name="allowSort" optional="yes" default="true">
					<string name="~column~" comments="sort by column or sub-query" />
				</boolean_or_structure>
				<string name="listFilter" optional="yes" comments="sql statement" />
				<structure name="listFilter" optional="yes">
					<string name="sql" comments="sql statement" />
					<array  name="param" comments="parameters" />
				</structure>
				<string name="listOrder" optional="yes" default="order by {seq} (if any), then by {id}" />
				<array name="listField" optional="yes" comments="determine fields to display in listing">
					<string name="+" comments="when no key specified, value is column list" />
					<string name="~columnList~" comments="when key was specified, key is column list and value is column width" />
				</array>
				<array name="modalField" optional="yes" comments="determine fields to show in modal form">
					<list name="+" value="~columnList~" optional="yes" delim="|" comments="when no key specified, value is column list" />
					<list name="~columnList~" value="~columnWidthList~" optional="yes" delim="|" comments="when key was specified, key is column list and value is column width list" />
					<string name="~line~" optional="yes" example="---" comments="any number of dash(-) or equal(=)" />
					<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
				</array>
				<structure name="fieldConfig" optional="yes" comments="options of each input field in edit form; also define sequence of field in modal edit form">
					<string name="+" comments="when no key specified, value is column name" />
					<structure name="~column~" comments="when key was specified, key is column name and value is field options">
						<string name="label" optional="yes" comments="display name at table/form header">
						<string name="placeholder" optional="yes" default="display name in field" />
						<string name="inline-label" optional="yes" default="display name at beginning of field" />
						<string name="format" optional="yes" comments="text|hidden|output|textarea|dropdown|checkbox|radio|date|time|datetime|file|image|one-to-many|many-to-many|wysiwyg|custom" default="text" />
						<structure name="options" optional="yes" comments="show dropdown when specified">
							<string name="~optionValue~" value="~optionText~" optional="yes" />
							<structure name="~optGroup~" optional="yes">
								<structure name="~optionValue~" value="~optionText~" />
							</structure>
						</structure>
						<string name="label" optional="yes" comments="display name at table/form header">
						<string name="placeholder" optional="yes" default="display name in field" />
						<string name="inline-label" optional="yes" default="display name at beginning of field" />
						<boolean name="required" optional="yes" />
						<boolean name="readonly" optional="yes" comments="output does not pass value; readonly does" />
						<boolean name="disabled" optional="yes" comments="show field but no value passed" />
						<string name="value" optional="yes" comments="force filling with this value even if field has value" />
						<string name="default" optional="yes" comments="filling with this value if field has no value" />
						<string name="class" optional="yes" />
						<string name="style" optional="yes" />
						<string name="pre-help" optional="yes" comments="help text show before input field" />
						<string name="help" optional="yes" comments="help text show after input field" />
						<!-- below are for [format=file|image] only -->
						<string name="filesize" optional="yes" comments="max file size in bytes" example="2MB|500KB" />
						<list name="filetype" optional="yes" delim="," example="pdf,doc,docx" />
						<!-- for [format=image] only -->
						<string name="resize" optional="yes" example="800x600|1024w|100h" />
						<!-- for [format=custom] only -->
						<string name="scriptPath" optional="yes" example="/server/path/to/custom/input.php" />
					</structure>
				</structure>
				<!-- advanced settings for UI customization -->
				<structure name="scriptPath" optional="yes">
					<string name="list" optional="yes" />
					<string name="header" optional="yes" />
					<string name="row" optional="yes" />
					<string name="edit" optional="yes" />
					<string name="inline_edit" optional="yes" />
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

// set parameter default value
$init = Scaffold::initConfig();
F::error(Scaffold::error(), $init === false);

// validation
$valid = Scaffold::validateConfig();
F::error(Scaffold::error(), $valid === false);


// start!
switch ( $fusebox->action ) :


	// default show index
	case 'index':
		// get all records
		$beanList = Scaffold::getBeanList();
		F::error(Scaffold::error(), $beanList === false);
		// define exit point
		if ( $scaffold['allowNew'] ) {
			$xfa['new'] = "{$fusebox->controller}.new".$scaffold['retainParam'];
		}
		if ( $scaffold['allowQuick'] and in_array($scaffold['editMode'], ['modal','basic']) ) {
			$xfa['quick'] = "{$fusebox->controller}.quick".$scaffold['retainParam'];
		}
		if ( $scaffold['allowEdit'] ) {
			$xfa['edit'] = "{$fusebox->controller}.edit&nocache=".time().$scaffold['retainParam'];
		}
		if ( $scaffold['allowDelete'] ) {
			$xfa['delete'] = "{$fusebox->controller}.delete".$scaffold['retainParam'];
		}
		if ( $scaffold['allowToggle'] ) {
			$xfa['enable'] = "{$fusebox->controller}.toggle&disabled=0".$scaffold['retainParam'];
			$xfa['disable'] = "{$fusebox->controller}.toggle&disabled=1".$scaffold['retainParam'];
		}
		// retain url params when change sorting
		if ( !empty($scaffold['allowSort']) ) {
			$xfa['sort'] = $fusebox->controller.$scaffold['retainParam'];
			foreach ( $_GET as $key => $val ) {
				if ( $key != F::config('commandVariable') and $key != 'sortField' and $key != 'sortRule' ) {
					// e.g. &search[sid]=999999
					if ( is_array($val) ) foreach ( $val as $subKey => $subVal ) $xfa['sort'] .= "&{$key}%5B{$subKey}%5D={$subVal}";
					// e.g. &sid=999999
					else $xfa['sort'] .= "&{$key}={$val}";
				}
			}
		}
		// display list
		ob_start();
		include $scaffold['scriptPath']['list'];
		$layout['content'] = ob_get_clean();
		// pagination
		if ( !empty($scaffold['pagination']) ) {
			$arguments['pagination'] = $scaffold['pagination'];
		}
		// breadcrumb
		if ( !isset($arguments['breadcrumb']) ) {
			$controllerDisplayName = ucwords(str_replace(['-','_'], ' ', F::command('controller')));
			$arguments['breadcrumb'] = array($controllerDisplayName);
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
		F::error('Argument [id] is required', empty($arguments['id']));
		// get record
		$bean = Scaffold::getBean($arguments['id']);
		F::error(Scaffold::error(), $bean === false);
		// define exit point
		// ===> refer to index
		if ( $scaffold['allowEdit'] ) {
			$xfa['edit'] = "{$fusebox->controller}.edit&nocache=".time().$scaffold['retainParam'];
		}
		if ( $scaffold['allowDelete'] ) {
			$xfa['delete'] = "{$fusebox->controller}.delete".$scaffold['retainParam'];
		}
		if ( $scaffold['allowToggle'] ) {
			$xfa['enable'] = "{$fusebox->controller}.toggle&disabled=0".$scaffold['retainParam'];
			$xfa['disable'] = "{$fusebox->controller}.toggle&disabled=1".$scaffold['retainParam'];
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
		F::error('Forbidden (allowEdit=false)', !$scaffold['allowEdit']);
		F::error('Argument [id] is required', empty($arguments['id']));
		// get record
		$bean = Scaffold::getBean($arguments['id']);
		F::error(Scaffold::error(), $bean === false);
	case 'new':
	case 'quick':
		F::error('Forbidden (allowNew=false)', !$scaffold['allowNew'] and !F::is('*.edit'));
		// default number of form
		if ( empty($arguments['count']) ) $arguments['count'] = 1;
		// get empty record (when necessary)
		if ( !isset($bean) ) $bean = Scaffold::getBean();
		F::error(Scaffold::error(), $bean === false);
		// display form
		// ===> use [count] to display multiple forms
		$layout['content'] = '';
		for ( $i=0; $i<$arguments['count']; $i++ ) {
			$method = ( F::is('*.quick') or $scaffold['editMode'] == 'inline' ) ? 'renderInlineForm' : 'renderForm';
			$fieldLayout = ( $method == 'renderInlineForm' ) ? $scaffold['listField'] : $scaffold['modalField'];
			$layout['content'] .= Scaffold::$method($fieldLayout, $scaffold['fieldConfig'], $bean, [], $xfa ?? []);
		}
		// show with layout (when necessary)
		if ( F::ajaxRequest() or $scaffold['layoutPath'] === false ) {
			echo $layout['content'];
		} else {
			// breadcrumb
			$controllerDisplayName = ucwords(str_replace(['-','_'], ' ', F::command('controller')));
			if ( !isset($arguments['breadcrumb']) ) $arguments['breadcrumb'] = array($controllerDisplayName, F::is('*.edit') ? 'Edit' : 'New');
			// layout
			include $scaffold['layoutPath'];
		}
		break;


	// show or hide record
	// ===> show record row after save
	case 'toggle':
		F::error('Forbidden (allowToggle=false)', !$scaffold['allowToggle']);
		F::error('Argument [id] is required', empty($arguments['id']));
		F::error('Argument [disabled] is required', !isset($arguments['disabled']));
		// save record
		$toggleBean = Scaffold::toggleBean($arguments['id'], !$arguments['disabled']);
		F::error(Scaffold::error(), $toggleBean === false);
		// back to list
		F::redirect("{$fusebox->controller}.row&id={$arguments['id']}".$scaffold['retainParam'], F::ajaxRequest());
		F::redirect($fusebox->controller.$scaffold['retainParam']);
		break;


	// save record (and go to default page)
	case 'save':
		F::error('No data submitted', empty($arguments['data']));
		F::error('Forbidden (allowNew=false)', !$scaffold['allowNew'] and empty($arguments['data']['id']));
		F::error('Forbidden (allowEdit=false)', !$scaffold['allowEdit'] and !empty($arguments['data']['id']));
		// save record
		$id = Scaffold::saveBean($arguments['data']);
		F::error(Scaffold::error(), $id === false);
		// finish
		F::redirect("{$fusebox->controller}.row&id={$id}".$scaffold['retainParam'], F::ajaxRequest() and empty($arguments['noRedirect']));
		F::redirect($fusebox->controller.$scaffold['retainParam'], empty($arguments['noRedirect']));
		break;


	// delete record (and go to default page)
	case 'delete':
		F::error('Forbidden (allowDelete=false)', !$scaffold['allowDelete']);
		F::error('Argument [id] is required', empty($arguments['id']));
		// delete record
		$deleteBean = Scaffold::deleteBean($arguments['id']);
		F::error(Scaffold::error(), $deleteBean === false);
		// return to index page if not ajax
		// ===> otherwise, simply show nothing (in order to hide row)
		F::redirect($fusebox->controller.$scaffold['retainParam'], !F::ajaxRequest());
		break;


	// ajax file upload
	case 'upload_file':
var_dump($arguments);
var_dump($_FILES);
/*
		$result = Scaffold::uploadFile($arguments);
		$result = ( $result !== false ) ? $result : array(
			'success' => false,
			'msg' => Scaffold::error(),
		);
		echo json_encode($result);
*/
		break;
	// ajax upload progress
	case 'upload_file_progress':
		require Scaffold::$libPath['uploadFileProgress'];
		break;


	default:
		F::pageNotFound();


endswitch;