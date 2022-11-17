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
	'retainParam' => "category={$arguments['category']}",
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
		'sql' => 'disabled = 0 AND category = ? ',
		'param' => array('FOOBAR'),
	),
	'listOrder' => 'ORDER BY category ASC, seq ASC ',
	'listField' => array(
		'id|parent_id' => 60,
		'category|bar' => '10%',
		'title|body' => '20%',
		'datetime|alt_date|alt_time' => '10%',
		'url|flyer|photo' => '30%',
		'created_on|created_by' => '10%',
		'seq|visible',
	),
	'modalField' => array(
		'id|parent_id',
		'## Header',
		'--',
		'title',
		'intro',
		'body',
		'~<br>',
		'### Genres',
		'--',
		'category|tags' => '4|8',
		'~<br>',
		'#### Date & Time',
		'--',
		'datetime|alt_date|alt_time',
		'~<br>',
		'##### History',
		'--',
		'created_on|created_by',
		'~<br>',
		'###### Settings',
		'--',
		'seq|visible',
		'~<br>',
		'--',
		'###### Permissions',
		'bar',
		'~<p><em>direct output on screen</em></p>',
	),
	'fieldConfig' => array(
		'id',
		'parent_id' => array('label' => false, 'readonly' => true),
		'category' => array('label' => true, format' => 'dropdown', 'icon' => 'far fa-star', 'default' => $arguments['category'], 'options' => Enum::array('FOO_CATEGORY')),
		'tags' => array('format' => 'checkbox', 'options' => [ 'linux' => 'Linux', 'apache' => 'Apache', 'mysql' => 'MySQL', 'php' => 'PHP' ]),
		'title' => array('format' => 'text', 'required' => true, 'placeholder' => true),
		'intro' => array('format' => 'textarea', 'style' => 'height: 5rem', 'placeholder' => true),
		'body' => array('format' => 'wsyiwyg', 'help' => 'page content in html'),
		'datetime' => array('format' => 'datetime'),
		'alt_date' => array('format' => 'date'),
		'alt_time' => array('format' => 'time'),
		'url' => array('format' => 'url', 'icon' => 'fa fa-link'),
		'photo' => array('format' => 'image', 'filetype' => 'gif,jpg,jpeg,png', 'filesize' => '500KB'),
		'flyer' => array('format' => 'file', 'filetype' => 'doc,docx,pdf', 'filesize' => '5MB'),
		'created_on' => array('default' => date('Y-m-d H:i:s')),
		'created_by' => Auth::user('username'),
		'seq' => array('format' => 'number'),
		'visible' => array('format' => 'radio', 'options' => [ 'Y' => 'Yes', 'N' => 'No' ]),
		// require [foo_bar] table with [foo_id & bar_id] columns
		'bar' => array('label' => 'Permissions', 'format' => 'many-to-many', 'options' => array_map(fn($item) => $item->name, ORM::all('bar'))),
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



<<<<<<< HEAD

## Third-party Components
The global layout module includes CDN of following JS and CSS libraries to provide a faster development environment:
* jQuery (1.9 or above)
* Bootstrap (5.x)

Please be noted that the Fuseboxy framework core does **NOT** depend on any one of these.

Therefore, developer could feel free to keep/remove any of these at `app/view/global/layout.basic.php` whenever applicable.




## Configuration & Installation

1. Modify **Fusebox Config**
	* Enable `formUrl2arguments`
	*

2. Add **ReadBeanPHP** ORM config

3. Load **javascript library** at global layout

--

1. Enable **output_buffering** of PHP settings:
	* e.g. `output_buffering = 4096`

2. Add following config into **app/config/fusebox_config.php** if not already exists:
	* `'baseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) )`

3. Copy files from the package into your application:
	* `app/view/global/*`
	* `css/main.css` *- optional*
	* `js/main.js` *- optional*
	* `test/test_fuseboxy_global_layout.php` *- optional (Only if you want to perform a unit test)*

4. Open and edit following variables at **app/view/global/layout.php**:
	* `$layout['metaTitle']`
	* `$layout['brand']`

5. Done.




[dependencies]

formUrl2arguments (enabled)
* app/config/fusebox_config.php
redbeanphp (included)
* lib/redbeanphp/5.x/
simple-ajax-uploader (included)
* lib/simple-ajax-uploader/2.x/
bootstrap-extend (cdn)
* https://cdn.statically.io/bb/henrygotmojo/bootstrap-extend/4.0.3/bootstrap.extend.css
* https://cdn.statically.io/bb/henrygotmojo/bootstrap-extend/4.0.3/bootstrap.extend.js
summernote (cdn)
* https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.css
* https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.min.js
jquery-datetimepicker (cdn)
* https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css
* https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js
fancybox (cdn)
* https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css
* https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js




[optional]
Log
Bean




[Config]

fusebox->config['autoLoad']

	dirname(dirname(dirname(__FILE__))).'/lib/redbeanphp/{VERSION}/rb.php',
	dirname(dirname(__FILE__)).'/config/rb_config.php',




[validation]

RedBean_SimpleModel
===> validate when update

