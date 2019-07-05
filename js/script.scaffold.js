$(function(){


	// overriding simple-ajax-uploader function to show ajax error in modal
	if ( typeof ss !== 'undefined' ) {
		ss.SimpleUpload.prototype._finish = function( status, statusText, response, filename, sizeBox, progBox, pctBox, abortBtn, removeAbort, uploadBtn ) {
			"use strict";
			// show server response
			this.log( 'Server response: ' + response );
			// check if any error
			if ( this._opts.responseType.toLowerCase() == 'json' ) {
				var jsonResponse = ss.parseJSON( response );
				if ( jsonResponse === false ) {
					// show error in modal when not valid response
					var errModal = $('#ss-error-modal');
					if ( !$(errModal).length ) {
						errModal = $('<div id="ss-error-modal" class="modal fade" data-nocache role="dialog"><div class="modal-dialog"><div class="modal-content panel panel-danger"><div class="modal-body panel-heading" style="border-radius: 6px;"></div></div></div></div>');
						$(errModal).appendTo('body');
					}
					$(errModal).find('.modal-body').html(response).end().modal('show');
					// show error in console when not valid response
					this._errorFinish( status, statusText, false, 'parseerror', filename, sizeBox, progBox, abortBtn, removeAbort, uploadBtn );
					// do not go further
					return;
				}
			}
			// go on if no error
			this._opts.onComplete.call( this, filename, response, uploadBtn );
			this._last( sizeBox, progBox, pctBox, abortBtn, removeAbort );
			// Null to avoid leaks in IE
			status = statusText = response = filename = sizeBox = progBox = pctBox = abortBtn = removeAbort = uploadBtn = null;
		};
	}


	// init simple-ajax-uploader when document/row/modal show
	$(document).on('ready ajaxLoad.bsx shown.bs.modal', function(evt){
		window.setTimeout(function(){
			$('.scaffold-input-file').not('.simple-ajax-uploader-ready').each(function(){
				var elementID = $(this).attr('id');
				// apply ajax-upload to this single field
				$('#'+elementID).each(function(){
					// elements
					var $fieldWrap = $(this);
					var $field = $fieldWrap.find('input[type=text]');
					var $uploadBtn = $fieldWrap.find('.btn-upload');
					var $removeBtn = $fieldWrap.find('.btn-remove');
					var $undoBtn = $fieldWrap.find('.btn-undo');
					var $progressWrap = $fieldWrap.find('.progress-wrap');
					var $preview = $fieldWrap.find('.thumbnail');
					var $alert = $fieldWrap.find('.alert');
					// click button to clear selected image
					$removeBtn.on('click', function(evt){
						evt.preventDefault();
						$field.val('');
						$preview.html('').hide();
						$undoBtn.show();
						$removeBtn.hide();
					});
					// click button to restore to original image
					$undoBtn.on('click', function(evt){
						evt.preventDefault();
						$field.val( $undoBtn.attr('data-original-image') );
						$preview.show().html('<a href="'+$undoBtn.attr('data-original-image')+'" target="_blank"><img src="'+$undoBtn.attr('data-original-image')+'" alt="" /></a>');
						$undoBtn.hide();
						$removeBtn.show();
					});
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
							var $cancelBtn = $('<span class="btn-cancel-upload"><button class="btn btn-xs btn-block btn-info">Cancel</button></span>');
							$fieldWrap.find('.progress-abort').append( $cancelBtn );
							// Adds click event listener that will cancel the upload
							// The second argument is whether the button should be removed after the upload
							// true = yes, remove abort button after upload
							// false/default = do not remove
							this.setAbortBtn($cancelBtn, true);
						},
						// show upload preview (and show remove button)
						// ===> hide alert, hide progress bar
						onComplete: function(filename, responseText) {
							var response = $.parseJSON(responseText);
							if ( response.success ) {
								$field.val(response.fileUrl);
								$preview.show().html('<a href="'+response.fileUrl+'" target="_blank"><img src="'+response.fileUrl+'" alt="" /></a>');
								if ( $undoBtn.length ) {
									$undoBtn.show();
									$removeBtn.hide();
								} else {
									$removeBtn.show();
								}
								$progressWrap.closest('.row').hide();
							} else {
								$alert.html( response.msg ? response.msg : responseText ).show();
							}
						}
					});
					// mark flag
					$(this).addClass('simple-ajax-uploader-ready');
				});
			});
		}, 500); // window-setTimeout
	}); // document-on


	// init ckeditor when row/modal show
	// ===> (auto-init by CKeditor core when document show)
	$(document).on('ajaxLoad.bsx shown.bs.modal', function(evt){
		window.setTimeout(function(){
			$('.scaffold-input-wysiwyg').not('.ckeditor-ready').each(function(){
				var elementID = $(this).attr('id');
				CKEDITOR.inline(elementID);
				// mark flag
				$(this).addClass('ckeditor-ready');
			});
		}, 500);
	});


	// init datetime picker when document/row/modal show
	$(document).on('ready ajaxLoad.bsx shown.bs.modal', function(evt){
		window.setTimeout(function(){
			$('.scaffold-input-date,.scaffold-input-time,.scaffold-input-datetime').not('.datetimepicker-ready').each(function(){
				if ( $(this).hasClass('scaffold-input-date') ) {
					var format = 'YYYY-MM-DD';
				} else if ( $(this).hasClass('scaffold-input-time') ) {
					var format = 'HH:mm';
				} else {
					var format = 'YYYY-MM-DD HH:mm';
				}
				// transform
				$(this).datetimepicker({
					'format' : format,
					'sideBySide' : true
				});
				// mark flag
				$(this).addClass('datetimepicker-ready');
			});
		}, 500);
	});


	// sticky header
	$(window).scroll(function(){
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
		});
	});


});