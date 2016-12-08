<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" />
				<string name="ajaxUploadProgress" />
			</structure>
			<structure name="$field">
				<string name="name" />
				<string name="format" comments="file" />
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<string name="style" comments="apply to preview image" />
				<string name="filesize" optional="yes" comments="max file size in bytes" />
				<number name="filesize_numeric" optional="yes" comments="use this for comparison" />
				<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
				<boolean name="preview" optional="yes" />
			</structure>
			<object name="$bean" comments="for field value" />
		</in>
		<out />
	</io>
</fusedoc>
*/
$uniqid = "{$scaffold['beanType']}-input-file-{$field['name']}-".uniqid();
?>
<div
	id="<?php echo $uniqid; ?>"
	class="scaffold-input-file"
	<?php if ( isset($xfa['ajaxUpload']) ) : ?>data-upload-url="<?php echo F::url("{$xfa['ajaxUpload']}&uploaderID={$uniqid}&fieldName={$field['name']}"); ?>"<?php endif; ?>
	<?php if ( isset($xfa['ajaxUploadProgress']) ) : ?>data-progress-url="<?php echo F::url($xfa['ajaxUploadProgress']); ?>"<?php endif; ?>
	<?php if ( isset($field['filetype']) ) : ?>data-file-type="<?php echo $field['filetype']; ?>"<?php endif; ?>
	<?php if ( isset($field['filesize']) ) : ?>data-file-size="<?php echo $field['filesize']; ?>" data-file-size-numeric="<?php echo $field['filesize_numeric']; ?>"<?php endif; ?>
>
	<div class="input-group">
		<?php if ( empty($field['readonly']) ) : ?>
			<span class="input-group-btn">
				<button
					type="button"
					class="btn btn-sm btn-default btn-upload"
				>Choose</button>
				<button
					type="button"
					class="btn btn-sm btn-default btn-remove"
					style="border-right: 0; <?php if ( empty($field['_value_']) ) : ?>display: none;<?php endif; ?>"
				><i class="fa fa-times"></i></button>
			</span>
		<?php endif; ?>
		<input
			type="text"
			class="form-control input-sm"
			name="data[<?php echo $field['name']; ?>]"
			value="<?php echo $field['_value_']; ?>"
			placeholder="No file chosen"
			readonly
			<?php if ( !empty($field['required']) ) echo 'required'; ?>
		 />
	</div>
	<div class="alert alert-danger" style="display: none; margin-bottom: 0; margin-top: 5px;"></div>
	<div class="row" style="display: none; margin-top: 5px;">
		<div class="col-xs-9 progress-wrap"></div>
		<div class="col-xs-3 progress-abort"></div>
	</div>
	<?php if ( !empty($field['preview']) and !empty($field['_value_']) ) : ?>
		<a
			href="<?php echo $field['_value_']; ?>"
			class="thumbnail"
			target="_blank"
			style="margin: 5px 0 0 0; width: auto; <?php if ( !empty($field['style']) ) echo $field['style']; ?>"
			title="<?php echo basename($field['_value_']); ?>"
		><img
			alt=""
			src="<?php echo $field['_value_']; ?>"
		/></a>
	<?php endif; ?>
</div>


<script>
$(function(){

	// apply ajax-upload to this single field
	$('#<?php echo $uniqid; ?>').each(function(){
		// elements
		var $fieldWrap = $(this);
		var $field = $fieldWrap.find('input[type=text]');
		var $uploadBtn = $fieldWrap.find('.btn-upload');
		var $removeBtn = $fieldWrap.find('.btn-remove');
		var $progressWrap = $fieldWrap.find('.progress-wrap');
		var $preview = $fieldWrap.find('.thumbnail');
		var $alert = $fieldWrap.find('.alert');
		// validation
		if ( !$fieldWrap.attr('data-upload-url') ) {
			alert('attribute [data-upload-url] is required for file upload');
			$uploadBtn.prop('disabled', true);
			return false;
		}
		// param from controller
		var _uploadUrl = $fieldWrap.attr('data-upload-url');
		var _progressUrl = $fieldWrap.is('[data-progress-url]') ? $fieldWrap.attr('data-progress-url') : false;
		var _maxSize = $fieldWrap.is('[data-file-size]') ? (parseFloat($fieldWrap.attr('data-file-size-numeric'))/1024) : false;
		var _allowedExtensions = $fieldWrap.is('[data-file-type]') ? $fieldWrap.attr('data-file-type').split(',') : false;
		// init ajax uploader
		var uploader = new ss.SimpleUpload({
			//----- essential config -----
			button: $uploadBtn,
			url: _uploadUrl,
			name: $fieldWrap.attr('id'),
			//----- optional config -----
			progressUrl: _progressUrl,
			multiple: false,
			maxUploads: 1,
			debug: true,
			// number of KB (false for default)
			// ===> javascript use KB for validation
			// ===> server-side use byte for validation
			maxSize: _maxSize,
			// server-upload will block file upload other than below items
			allowedExtensions: _allowedExtensions,
			// control what file to show when choosing files
			//accept: 'image/*',
			hoverClass: 'btn-hover',
			focusClass: 'active',
			disabledClass: 'disabled',
			responseType: 'json',
			// validate allowed extension
			onExtError: function(filename, extension) {
				$alert.show().html(filename + ' is not a permitted file type.'+"\n\n"+'Only '+$fieldWrap.attr('data-file-type').toUpperCase()+' are allowed.');
			},
			// validate file size
			onSizeError: function(filename, fileSize) {
				$alert.show().html(filename + ' is too big. ('+$fieldWrap.attr('data-file-size')+' max file size)');
			},
			// show progress bar
			onSubmit: function(filename, ext, btn) {
				$alert.hide().html('');
				$preview.hide().html('');
				if ( $fieldWrap.attr('data-progress-url') ) {
					$progressWrap.append('<div class="progress progress-striped active"><div class="progress-bar" style="width: 0%;"></div></div>');
					this.setProgressBar( $progressWrap.find('.progress-bar') );
					this.setProgressContainer( $progressWrap.find('.progress') );
					$progressWrap.closest('.row').show();
				}
				// browser bug : don't know why must log 'btn' to show progress-bar
				//console.log(btn);
			},
			// start upload
			startXHR: function() {
				// Dynamically add a "Cancel" button to be displayed when upload begins
				// By doing it here ensures that it will only be added in browsers which 
				// support cancelling uploads
				var $cancelBtn = $('<button class="btn btn-xs btn-block btn-info btn-cancel-upload">Cancel</button>');
				$fieldWrap.find('.progress-abort').append( $cancelBtn );
				// Adds click event listener that will cancel the upload
				// The second argument is whether the button should be removed after the upload
				// true = yes, remove abort button after upload
				// false/default = do not remove
				this.setAbortBtn($cancelBtn, true);
			},
			// show upload preview (and show remove button)
			// ===> hide alert, hide progress bar
			onComplete: function(filename, response) {
				if ( response != null && response.success ) {
					$field.val(response.fileUrl);
					$preview.show().html('<a href="'+response.fileUrl+'" target="_blank"><img src="'+response.fileUrl+'" alt="" /></a>');
					$removeBtn.show();
					$progressWrap.closest('.row').hide();
				} else {
					var msg = ( response != null && response.msg != null ) ? response.msg : 'Unable to upload file';
					$alert.html(msg).show();
				}
			}
		});
		// clear selected image
		$fieldWrap.on('click', '.btn-remove', function(evt){
			evt.preventDefault();
			$field.val('');
			$preview.html('').hide();
			$removeBtn.hide();
		});
	});

});
</script>