[prerequisite]

redbeanphp (included)
bootstrap-extend
simple-ajax-uploader (included)
ckeditor (cdn)
jquery.validate (cdn)




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