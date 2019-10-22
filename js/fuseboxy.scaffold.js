$(function(){


	// init ajax-uploader when page ready
	// ===> also when inline-edit & modal shows up
	fuseboxyScaffold__initAjaxUploader();
	$(document).on('ajaxLoad.bsx shown.bs.modal', function(evt){
		window.setTimeout(function(){
			fuseboxyScaffold__initAjaxUploader();
		}, 1000);
	});


	// init html-editor when page ready
	// ===> also when inline-edit & modal shows up
	fuseboxyScaffold__initHtmlEditor();
	$(document).on('ajaxLoad.bsx shown.bs.modal', function(evt){
		window.setTimeout(function(){
			fuseboxyScaffold__initHtmlEditor();
		}, 500);
	});


	// init datetime-picker when page ready
	// ===> also when inline-edit & modal shows up
	fuseboxyScaffold__initDatetimePicker();
	$(document).on('ready ajaxLoad.bsx shown.bs.modal', function(evt){
		window.setTimeout(function(){
			fuseboxyScaffold__initDatetimePicker();
		}, 500);
	});


	// sticky header
	$(window).on('scroll', function(evt){
		$('.scaffold-header').each(function(){
			var header = this;
			var windowScrollTop = $(window).scrollTop();
			var headerScrollTop = $(header).offset().top;
			if ( !$(header).data('original-state') && headerScrollTop < windowScrollTop ) {
				$(header)
					// mark flag
					.addClass('sticky-header')
					// retain header current state for rollback
					.data('original-state', {
						'left'      : $(header).css('left'),
						'position'  : $(header).css('position'),
						'top'       : $(header).css('top'),
						'width'     : $(header).width(),
						'scrollTop' : headerScrollTop
					})
					// make header sticky to top
					.css({
						'left'     : $(header).offset().left,
						'position' : 'fixed',
						'top'      : 0,
						'width'    : $(header).width()
					});
			} else if ( $(header).data('original-state') && headerScrollTop <= $(header).data('original-state').scrollTop ) {
				$(header)
					// clear flag
					.removeClass('sticky-header')
					// rollback to original state
					.css({
						'left'     : $(header).data('original-state').left,
						'position' : $(header).data('original-state').position,
						'top'      : $(header).data('original-state').top,
						'width'    : $(header).data('original-state').width
					})
					// clear retained state
					.removeData('original-state')
			}
		}); // scaffold-header-each
	}); // window-scroll


	// one click to create multiple new items
	$('.scaffold-btn-quick-multiple,.scaffold-btn-new-multiple').each(function(){
		var $container = $(this).closest('.btn-group');
		var $btn = $container.find('.scaffold-btn-quick,.scaffold-btn-new');
		$container.find('.dropdown-item').on('click', function(evt){
			var count = parseInt( $(this).text() );
			for (var i=0; i<count; i++ ) {
				$btn.trigger('click');
			}
		});
	});


}); // document-ready




function fuseboxyScaffold__initDatetimePicker(){
	$('.scaffold-input-datetime,.scaffold-input-date,.scaffold-input-time').not('.datetimepicker-ready').each(function(){
		// config for different type
		var config = {};
		if ( $(this).is('.scaffold-input-datetime') ) {
			config = { formatDate : 'Y-m-d', formatTime: 'H:i', step : 30 };
		} else if ( $(this).is('.scaffold-input-date') ) {
			config = { timepicker : false, formatDate : 'Y-m-d' };
		} else if ( $(this).is('.scaffold-input-time') ) {
			config = { datepicker : false, formatTime : 'H:i', step: 30 };
		}
		// transform
		$(this).datetimepicker(config);
		// mark complete
		$(this).addClass('datetimepicker-ready');
	});
}




function fuseboxyScaffold__initHtmlEditor(){
	$('.scaffold-input-wysiwyg:not(.summernote-ready').each(function(){
		// transform
		$(this).summernote({ 'height' : $(this).height() });
		// mark complete
		$(this).addClass('summernote-ready');
	});
}




function fuseboxyScaffold__initAjaxUploader(){
	$('.scaffold-input-file:not(.simple-ajax-uploader-ready)').each(function(){
		var elementID = $(this).attr('id');
		// apply ajax-upload to this single field
		$('#'+elementID).each(function(){
			// elements
			var $fieldContainer = $(this);
			var $field = $fieldContainer.find('input[type=text]');
			var $uploadBtn = $fieldContainer.find('.btn-upload');
			var $removeBtn = $fieldContainer.find('.btn-remove');
			var $undoBtn = $fieldContainer.find('.btn-undo');
			var $progress = $fieldContainer.find('.progress-row');
			var $previewImg = $fieldContainer.find('.img-thumbnail');
			var $errMsg = $fieldContainer.find('.form-text');
			// use jquery for show & hide
			$fieldContainer.find('.d-none').removeClass('d-none').hide();
			// click button to clear selected image
			$removeBtn.on('click', function(evt){
				evt.preventDefault();
				$field.val('');
				$previewImg.parent().hide();
				$undoBtn.show();
				$removeBtn.hide();
			}).removeClass('text-white');
			// click button to restore to original image
			$undoBtn.on('click', function(evt){
				evt.preventDefault();
				$field.val( $undoBtn.attr('data-original-image') );
				$previewImg.parent().show().attr('href', $undoBtn.attr('data-original-image'));
				$previewImg.attr('src', $undoBtn.attr('data-original-image'));
				$undoBtn.hide();
				$removeBtn.show();
			}).removeClass('text-white');
			// validation
			if ( !$fieldContainer.attr('data-upload-url') ) {
				alert('attribute [data-upload-url] is required for file upload');
				return false;
			// add behavior to upload button
			// ===> it will enable the upload button automatically
			} else {
				// param from controller
				var _uploadUrl   = $fieldContainer.attr('data-upload-url');
				var _progressUrl = $fieldContainer.is('[data-progress-url]') ? $fieldContainer.attr('data-progress-url') : false;
				var _maxSize     = $fieldContainer.is('[data-file-size]') ? (parseFloat($fieldContainer.attr('data-file-size-numeric'))/1024) : false;
				var _allowedExt  = $fieldContainer.is('[data-file-type]') ? $fieldContainer.attr('data-file-type').split(',') : false;
				// init ajax uploader
				var uploader = new ss.SimpleUpload({
					//----- essential config -----
					button: $uploadBtn.removeClass('text-white'),
					name: $fieldContainer.attr('id'),
					url: _uploadUrl,
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
					allowedExtensions: _allowedExt,
					// control what file to show when choosing files
					//accept: 'image/*',
					hoverClass: 'btn-hover',
					focusClass: 'active',
					responseType: 'json',
					// validate allowed extension
					onExtError: function(filename, extension) {
						var msg = filename + ' is not in a permitted file type. ('+$fieldContainer.attr('data-file-type').toUpperCase()+' only)';
						$errMsg.show().html(msg);
					},
					// validate file size
					onSizeError: function(filename, fileSize) {
						var msg = filename + ' is too big. ('+$fieldContainer.attr('data-file-size')+' max file size)';
						$errMsg.show().html(msg);
					},
					// show progress bar
					onSubmit: function(filename, extension, uploadBtn, fileSize) {
						// send original filename as additional data
						uploader._opts.data['originalName'] = encodeURI(filename);
						// clear image & show progress
						$errMsg.hide().html('');
						$previewImg.parent().hide();
						$progress.show();
						// hook progress
						this.setProgressBar( $progress.find('.progress-bar') );
						this.setProgressContainer( $progress.find('.progress') );
					},
					// start upload
					startXHR: function() {
						// Adds click event listener that will cancel the upload
						// The second argument is whether the button should be removed after the upload
						// true = yes, remove abort button after upload
						// false/default = do not remove
						var $abortBtn = $progress.find('.btn-abort');
						this.setAbortBtn($abortBtn, false);
					},
					// show upload preview (and show remove button)
					// ===> hide alert, hide progress bar
					onComplete: function(filename, response, uploadBtn, fileSize) {
						// upload succeed!
						if ( response.success ) {
							// update file path
							$field.val(response.fileUrl);
							// refresh preview image
							$previewImg.parent().show().attr('href', response.fileUrl);
							$previewImg.attr('src', response.fileUrl);
							// toggle buttons
							if ( $undoBtn.attr('data-original-image').length ) {
								$undoBtn.show();
								$removeBtn.hide();
							} else {
								$undoBtn.attr('data-original-image', response.fileUrl);
								$removeBtn.show();
							}
						// upload failed...
						} else {
							// simply show message
							$errMsg.html( response.msg ? response.msg : response ).show();
						}
						// hide progress bar
						$progress.hide();
					},					// any error
					onError: function(filename, errorType, status, statusText, response, uploadBtn, fileSize) {
						// show error in modal when not valid response
						var $errModal = $('#ss-error-modal');
						if ( !$errModal.length ) {
							$errModal = $(`
								<div id="ss-error-modal" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<div class="modal-content bg-danger">
											<div class="modal-body"></div>
										</div>
									</div>
								</div>
							`);
							$errModal.appendTo('body');
						}
						$errModal.find('.modal-body').html(response).end().modal('show');
					}
				}); // new-simple-upload
			} // if-data-upload-url
			// mark complete
			$(this).addClass('simple-ajax-uploader-ready');
		}); // each-element-id
	}); // each-scaffold-input-file
} // function