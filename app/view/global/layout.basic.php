<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="ie ie6"> <![endif]-->
<!--[if IE 7]>         <html class="ie ie7"> <![endif]-->
<!--[if IE 8]>         <html class="ie ie8"> <![endif]-->
<!--[if IE 9]>         <html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!--> <html> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title><?php if ( isset($layout['metaTitle']) ) echo $layout['metaTitle']; ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="author" content="powered by METASEIT (info@metaseit.com)" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]><script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
	<!-- style -->
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" />
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" />
<!--<link href="//cdn.bootcss.com/jstree/3.0.9/themes/default/style.min.css" rel="stylesheet" />-->
	<link href="//static.jstree.com/3.1.1/assets/dist/themes/default/style.min.css" rel="stylesheet" />
	<link href="<?php echo F::config('baseUrl'); ?>lib/bootstrap-extend/3.0/bootstrap.extend.css" rel="stylesheet" />
<!--<link href="<?php echo F::config('baseUrl'); ?>css/style.cms.css" rel="stylesheet" />-->
<!--<link href="<?php echo F::config('baseUrl'); ?>css/style.webform.css" rel="stylesheet" />-->
	<!-- script -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script> <!-- scaffold & webform -->
	<script src="//cdn.ckeditor.com/4.4.4/standard/ckeditor.js"></script>  <!-- scaffold -->
<!--<script src="//cdn.bootcss.com/jstree/3.0.9/jstree.min.js"></script>-->
	<script src="//static.jstree.com/3.1.1/assets/dist/jstree.js"></script>
	<script src="<?php echo F::config('baseUrl'); ?>lib/bootstrap-extend/3.0/bootstrap.extend.js"></script>
	<script src="<?php echo F::config('baseUrl'); ?>lib/simple-ajax-uploader/1.10.1/SimpleAjaxUploader.min.js"></script> <!-- scaffold & webform -->
<!--<script src="<?php echo F::config('baseUrl'); ?>js/script.cms.js"></script>-->
<!--<script src="<?php echo F::config('baseUrl'); ?>js/script.webform.js"></script>-->
</head>
<body data-controller="<?php echo $fusebox->controller; ?>" data-action="<?php echo $fusebox->action; ?>" data-ajax-error="modal">
<?php echo $layout['content']; ?>
</body>
</html>