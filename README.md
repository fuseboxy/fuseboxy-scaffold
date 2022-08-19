Fuseboxy Scaffold (v2.x)
========================

Low-code CRUD UI Builder


--------------------------------------------------


## Installation

#### By Composer

* 


#### Manually

*  


--------------------------------------------------


## Configuration

```
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
			<string name="format" optional="yes" comments="text|hidden|output|textarea|checkbox|radio|file|image|one-to-many|many-to-many|wysiwyg|url" default="text" />
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
```


--------------------------------------------------


## Examples

Miniumum Settings

```
<?php

$scaffold = array(
	'beanType' => 'foo',
	'layout' => F::appPath('view/global/layout.php'),
);

include F::appPath('controller/scaffold_controller.php');

```


Full Settings

```
<?php

$scaffold = array(
	'beanType' => 'foo',
	'layoutPath' => F::appPath('view/foo/layout.php'),
	'editMode' => 'modal',
	'modalSize' => 'xl',
	'stickyHeader' => true,
	'allowNew' => true,
	'allowQuick' => true,
	'allowEdit' => true,
	'allowToggle' => true,
	'allowDelete' => Auth::user('SUPER'),
	'allowSort' => array('alias', 'title'),
	'listFilter' => array(
		'sql' => 'disabled = 0 AND type = ? ',
		'param' => array('FOOBAR'),
	),
	'listOrder' => 'ORDER BY type ASC, seq ASC ',
	'listField' => array(
	),
	'modalField' => array(
	),
	'fieldConfig' => array(
	),
	'scriptPath' => array(
		'header' => F::appPath('view/foo/header.php'),
		'row' => F::appPath('view/foo/row.php'),
		'list' => F::appPath('view/foo/list.php'),
		'edit' => F::appPath('view/foo/edit.php'),
		'inline_edit' => F::appPath('view/foo/inline_edit.php'),
	),
	'pagination' => array(
		'recordPerPage' => 100,
		'pageVisible' => 20,
	),
	'writeLog' => class_exists('Log'),
);

include F::appPath('controller/scaffold_controller.php');

```


--------------------------------------------------


## Validation


--------------------------------------------------


## Customization



