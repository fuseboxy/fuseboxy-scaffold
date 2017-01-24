$(function(){


	// overriding simple-ajax-uploader function to show ajax error in modal
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


});