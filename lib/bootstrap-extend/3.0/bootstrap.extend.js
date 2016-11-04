/**
 *  ==================== BOOTSTRAP-EXTEND ====================
 *
 *  [Description]
 *  - Extend javascript features of Twitter Bootstrap
 *  - Version 3.0 (indicates that it works for Bootstrap v3.x)
 *
 *  [Dependencies]
 *  - Bootstrap 3.x
 *  - jQuery 1.7+
 *  - jQuery-blockUI plugin (included in this file)
 *
 *  [Features]
 *  - AUTO AJAX ERROR
 *  - MODAL NO-CACHE
 *  - DATA-TOGGLE : AJAX-LOAD / AJAX-SUBMIT  ===>  popstate under construction...
 *  - DATA-TOGGLE : INPUT-VALUE
 *
 */


$(function(){


/**
 *  ---------------
 *  AUTO AJAX ERROR
 *  ---------------
 *
 *  I show error modal dialog whenever there is server error
 *  ===> body[data-ajax-error] = {modal*|alert}
 *  ===> simply die() in server-script and error message will auto-show in modal
 *  ===> default open dialog when ajax-error
 *  ===> applicable to whole site
 *
 *
 *  [Example]
 *
 *  <body data-ajax-error="modal"> ... </body>
 *
 **/

$(document).ajaxError(function(evt, jqXHR, ajaxSettings, thrownError){
	if ( $('body').is('[data-ajax-error]') ) {
		var mode = $('body').attr('data-ajax-error').length ? $('body').attr('data-ajax-error') : 'modal';
		// show message in modal, or...
		if ( mode == 'modal' ) {
			var errMsg = '<h3 style="margin-top: 0;" title="'+ajaxSettings.url+'">Error</h3><pre>'+jqXHR.responseText+'</pre>';
			// show message in already opened modal, or...
			var visibleModal = $('.modal:visible .modal-body');
			if ( $(visibleModal).length ) {
				var errAlert = $('#bs-extend-error-alert');
				// create new alert box (when necessary)
				if ( !$(errAlert).length ) {
					errAlert = $('<div id="bs-extend-error-alert" class="alert alert-danger"></div>');
					$(errAlert).prependTo(visibleModal).hide();
				}
				// show alert box in modal
				$(errAlert)
					.html(errMsg)
					.filter(':visible').hide().fadeIn().end()
					.filter(':hidden').slideDown().end()
					.off('click')
					.on('click', function(){ $(errAlert).slideUp(); });
			// create new modal window and show message
			} else {
				var errModal = $('#bs-extend-error-modal');
				// create hidden dialog (when necessary)
				if ( !$(errModal).length ) {
					errModal = $('<div id="bs-extend-error-modal" class="modal fade" data-nocache role="dialog"><div class="modal-dialog"><div class="modal-content panel panel-danger"><div class="modal-body panel-heading" style="border-radius: 6px;"></div></div></div></div>');
					$(errModal).appendTo('body');
				}
				// show message in modal
				$(errModal).find('.modal-body').html(errMsg).end().modal('show');
			}
		// show message in alert
		} else {
			alert('[Error]\n'+ajaxSettings.url+'\n'+jqXHR.responseText);
		}
	}
});




/**
 *  --------------
 *  MODAL NO-CACHE
 *  --------------
 *
 *  I allow clearing modal cache on-close by specifying [data-nocache] at .modal element
 *  ===> avoid bootstrap default ajax-modal cache behavior
 *  ===> otherwise, modal content will stay the same...
 *  ===> http://stackoverflow.com/questions/12286332/twitter-bootstrap-remote-modal-shows-same-content-everytime
 *
 *
 *  [Example]
 *
 *  <div id="my-modal" class="modal" data-nocache> ... </div>
 *
 **/

$(document).on('hidden.bs.modal', '[data-nocache]', function(evt){
	$(this).removeData('bs.modal').find('.modal-body').html('<h1 class="text-muted text-center"><i class="fa fa-spinner fa-spin"></i></h1>');
});




/**
 *  -------------------------------------
 *  DATA-TOGGLE : AJAX-LOAD / AJAX-SUBMIT
 *  -------------------------------------
 *
 *  I allow ajax-load/ajax-submit content to specific element by defining data attributes
 *  ===> data-target = ~selector~
 *  ===> data-toggle = {ajax-load|ajax-submit}
 *  ===> data-toggle-mode = {replace*|prepend|append|before|after}
 *  ===> data-toggle-loading = {progress*|spinner|spinner-large|overlay|none}
 *  ===> data-toggle-transition = {slide*|fade|none}
 *  ===> data-toggle-callback = ~function|function-name~
 *  ===> data-toggle-pushstate
 *
 *  I use jquery-blockui plugin (if available)
 *  ===> when ajax-load or ajax-submit
 *
 *
 *  [Example : ajax-load]
 *
 *  <a href="/url/to/go" class="btn btn-default" data-toggle="ajax-load" data-target="#element"> ... </a>
 *
 *
 *  [Example : ajax-submit]
 *
 *  <form method="post" action="/url/to/go" data-toggle="ajax-submit" data-target="#element"> ... </form>
 *
 **/

$(document)
	// remote load
	.on('click', '[data-toggle=ajax-load]', function(evt){
		evt.preventDefault();
		var btn = this;
		$(document).trigger('ajaxLoad.bsx', btn);
	})
	// remote submit
	.on('submit', '[data-toggle=ajax-submit]', function(evt){
		evt.preventDefault();
		// BUG : when pass to custom event
		// ===> element becomes BUTTON (instead of FORM)
		// ===> use closest() as dirty fix
		var f = $(this).closest('form');
		$(document).trigger('ajaxSubmit.bsx', f);
	})
	// remote load/submit
	.on('ajaxLoad.bsx ajaxSubmit.bsx', function(evt, triggerElement){
		// confirmation
		if ( $(triggerElement).is('[data-confirm]') ) {
			var msg = $(triggerElement).attr('data-confirm').length ? $(triggerElement).attr('data-confirm') : 'Are you sure?';
			if ( !confirm(msg) ) return false;
		}
		// options
		var targetSelector   = $(triggerElement).attr('data-target');
		var toggleMode       = $(triggerElement).is('[data-toggle-mode]')       ? $(triggerElement).attr('data-toggle-mode') : 'replace';
		var toggleTransition = $(triggerElement).is('[data-toggle-transition]') ? $(triggerElement).attr('data-toggle-transition') : 'slide';
		var toggleCallback   = $(triggerElement).is('[data-toggle-callback]')   ? $(triggerElement).attr('data-toggle-callback') : '';
		var toggleLoading    = $(triggerElement).is('[data-toggle-loading]')    ? $(triggerElement).attr('data-toggle-loading') : 'progress';
		var togglePushState  = $(triggerElement).is('[data-toggle-pushstate]')  ? true : false;
		// apply block-ui when ajax load (if any)
		var configBlockUI;
		if ( $.fn.block ) {
			// default loading style (progress)
			configBlockUI = {
				'message' : false,
				'css' : {
					'backgroundColor' : 'none',
					'border' : 'none'
				},
				'fadeIn' : 0,
				'showOverlay' : true
			};
			// loading style : none
			if ( toggleLoading == 'none' ) {
				configBlockUI['overlayCSS'] = { 'background-color' : 'white', 'opacity' : 0 };
			// loading style : spinner
			} else if ( toggleLoading == 'spinner' || toggleLoading == 'spinner-large' ) {
				configBlockUI['message'] = ( toggleLoading == 'spinner-large' ) ? '<i class="fa fa-spin fa-spinner fa-4x text-muted"></i>' : '<i class="fa fa-spin fa-spinner text-muted"></i>';
				configBlockUI['overlayCSS'] = { 'background-color'  : 'gray', 'opacity' : .1 };
			// loading style : overlay
			} else if ( toggleLoading == 'overlay' ) {
				configBlockUI['overlayCSS'] = { 'background-color'  : 'gray', 'opacity' : .1 };
			// loading style : progress (default)
			} else {
				configBlockUI['overlayCSS'] = {
					'-webkit-animation' : 'progress-bar-stripes 2s linear infinite',
					'animation'         : 'progress-bar-stripes 2s linear infinite',
					'background-color'  : '#bbb',
					'background-image'  : '-webkit-linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent)',
					'background-image'  : 'linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent)',
					'background-size'   : '40px 40px',
				};
			}
		}
		// callback can be either function or event name
		// ===> trigger custom event so that {this} scope is available in callback function
		var toggleCallback = $(triggerElement).is('[data-toggle-callback]') ? $(triggerElement).attr('data-toggle-callback') : '';
		var toggleCallbackFunc = function(){};
		if ( toggleCallback.length ) {
			eval('toggleCallbackFunc = '+toggleCallback+';');
		}
		// when callback event was fired
		// ===> run the callback function
		// ===> there is no [this] variable available in callback function, because the original trigger element was already been replaced...
		$(document).on(evt.type+'Callback.bsx', toggleCallbackFunc);
		// check target
		if ( !targetSelector ) {
			console.log('[Error] '+evt.type+'.'+evt.namespace+' - attribute [data-target] was not specified');
		} else if ( !$(targetSelector).length ) {
			console.log('[Error] '+evt.type+'.'+evt.namespace+' - target not found ('+targetSelector+')');
		}
		// normal redirect or submit when target element was not properly specified
		if ( !targetSelector || !$(targetSelector).length ) {
			$(triggerElement).removeAttr('data-toggle');
			if ( evt.type == 'ajaxSubmit' ) {
				$(triggerElement).submit();
			} else {
				document.location.href = $(triggerElement).is('[href]') ? $(triggerElement).attr('href') : $(triggerElement).attr('data-href');
			}
		// proceed...
		} else {
			var targetElement = $(targetSelector);
			var url;
			if ( $(triggerElement).is('form') ) {
				url = $(triggerElement).attr('action');
			} else if ( $(triggerElement).is('[type=button]') ) {
				url = $(triggerElement).is('[href]') ? $(triggerElement).attr('href') : $(triggerElement).attr('data-href');
			} else if ( $(triggerElement).is('a') ) {
				url = $(triggerElement).attr('href');
			} else {
				console.log('[Error] '+evt.type+'.'+evt.namespace+' - type of trigger element not support');
			}
			var param = $(triggerElement).is('form') ? $(triggerElement).serialize() : {};
			// block
			if ( $(triggerElement).is('form') ) {
				if ( configBlockUI ) {
					$(triggerElement).block( configBlockUI );
				}
				$(triggerElement).find('[type=submit]').attr('disabled', true);
			} else {
				$(triggerElement).attr('disabled', true);
			}
			if ( configBlockUI ) {
				$(targetElement).block( configBlockUI );
			}
			// load result remotely
			$.ajax({
				'url' : url,
				'data' : param,
				'cache' : false,
				'method' : $(triggerElement).is('form[method]') ? $(triggerElement).attr('method') : 'get',
				'success' : function(data, textStatus, jqXHR){
					var newElement;
					// wrap by something if response text is not html
					if ( !$(data).length ) {
						data = '<div>'+data+'</div>';
					}
					// determine position of new element
					if ( toggleMode == 'prepend' ) {
						newElement = $(data).prependTo(targetElement);
					} else if ( toggleMode == 'append' ) {
						newElement = $(data).appendTo(targetElement);
					} else if ( toggleMode == 'before' ) {
						newElement = $(data).insertBefore(targetElement);
					} else {
						newElement = $(data).insertAfter(targetElement);
					}
					// show new element with effect
					// ===> callback after new element shown
					if ( toggleTransition == 'fade' ) {
						$(newElement).hide().fadeIn('normal', function(){
							$(document).trigger(evt.type+'Callback.bsx').off(evt.type+'Callback.bsx');
						});
					} else if ( toggleTransition == 'slide' ) {
						$(newElement).hide().slideDown('normal', function(){
							$(document).trigger(evt.type+'Callback.bsx').off(evt.type+'Callback.bsx');
						});
					} else {
						$(newElement).hide().show();
						$(document).trigger(evt.type+'Callback.bsx').off(evt.type+'Callback.bsx');
					}
					// hide current element (when necessary)
					if ( toggleMode == 'replace' ) {
						if ( toggleTransition == 'slide' ) {
							$(targetElement).slideUp('normal', function(){
								$(targetElement).remove();
							});
						} else {
							$(targetElement).hide().remove();
						}
					}
					// push state (when necessary)
					if ( history.pushState && togglePushState ) {
						history.pushState({
							'pushState.bsx' : {
								'targetSelector'   : targetSelector,
								'toggleMode'       : toggleMode,
								'toggleTransition' : toggleTransition,
								'toggleCallback'   : toggleCallback,
								'toggleLoading'    : toggleLoading
							}
						}, '', url);
					}
				},
				'complete' : function(){
					// unblock trigger element
					if ( $(triggerElement).is('form') ) {
						if ( configBlockUI ) $(triggerElement).unblock();
						$(triggerElement).find('[type=submit]').attr('disabled', false);
					} else {
						$(triggerElement).attr('disabled', false);
					}
					// unblock old element
					if ( configBlockUI ) $(targetElement).unblock();
				}
			});
		}
	});


// listen popstate event
// ===> manipulate page by pushstate object created by ajax-load/ajax-submit
$(window).on('popstate', function(evt){
/*
	// ========== UNDER CONSTRUCTION ==========
	// state data available
	// ===> update the page with state data
	if ( evt.originalEvent.state !== null && evt.originalEvent.state.hasOwnProperty('pushState.bsx') ) {
		console.log('UNDER CONSTRUCTION', evt.originalEvent.state['pushState.bsx']);
	// init page has no state data available
	// ===> reload browser to update the page
	} else {
		document.location.reload();
	}
*/
});




/**
 *  -------------------------
 *  DATA-TOGGLE : INPUT-VALUE
 *  -------------------------
 *
 *  I put value of trigger element to target element (useful when synchronize value of bootstrap dropdown and hidden field)
 *  ===> data-target = ~selector~  (allow multiple)
 *  ===> data-toggle = {input-value}
 *  ===> data-toggle-attr = ~list of attributes~  (update list of elements presented by [data-target])
 *  ===> data-toggle-callback = ~function|function-name~
 *
 *
 *  [Example : single]
 *
 *  <select data-toggle="input-value" data-target="#element"> ... </select>
 *
 *
 *  [Example : multiple]
 *
 *  <select data-toggle="input-value" data-target="#element-1,#element-2,#element-3" data-toggle-attr="value,data-attr-a,data-attr-b"> ... </select>
 *
 **/

$(document)
	// change : listbox
	.on('change', 'select[data-toggle=input-value]', function(evt){
		evt.preventDefault();
		var src = $(this).closest('select');
		$(document).trigger('inputValue.bsx', src);
	})
	// click : non-listbox
	.on('click', '[data-toggle=input-value]:not(select)', function(evt){
		evt.preventDefault();
		var src = this;
		$(document).trigger('inputValue.bsx', src);
	})
	// update target value
	.on('inputValue.bsx', function(evt, triggerElement){
		var targetElement = $(triggerElement).attr('data-target');
		// validation
		if ( !targetElement ) {
			console.log('[Error] '+evt.type+'.'+evt.namespace+' - attribute [data-target] was not specified');
		} else if ( !$(targetElement).length ) {
			console.log('[Error] '+evt.type+'.'+evt.namespace+' - target not found ('+targetElement+')');
		} else if ( !$(targetElement).is('input,select,textarea') ) {
			console.log('[Error] '+evt.type+'.'+evt.namespace+' - target is not a valid input field');
		// update value of target : multiple source value
		// ===> when [data-toggle-attr] is list
		} else if ( $(triggerElement).is('[data-toggle-attr]') && $(triggerElement).attr('data-toggle-attr').split(',').length ) {
			var attrList = $(triggerElement).attr('data-toggle-attr').split(',');
			$(targetElement).each(function(i){
				if ( i < attrList.length ) {
					var attrName = $.trim( attrList[i] );
					var newValue = $(triggerElement).is('select') ? $(triggerElement).find(':selected').attr(attrName) : $(triggerElement).attr(attrName);
					$(this).val( newValue );
				}
			});
		// update value of target : single source value
		// ===> get attribute value if [data-toggle-attr] is defined
		// ===> otherwise, simply get value (or text)
		} else if ( $(triggerElement).is('[data-toggle-attr]') ) {
			var attrName = $(triggerElement).attr('data-toggle-attr');
			var newValue = $(triggerElement).is('select') ? $(triggerElement).find(':selected').attr(attrName) : $(triggerElement).attr(attrName);
			$(targetElement).val( newValue );
		} else {
			var newValue = $(triggerElement).is('input,select,textarea') ? $(triggerElement).val() : $.trim($(triggerElement).html());
			$(targetElement).val( newValue );
		}
		// callback (if any)
		// ===> callback can be either function or event name
		// ===> trigger custom event so that {this} scope is available in callback function
		var toggleCallback = $(triggerElement).is('[data-toggle-callback]') ? $(triggerElement).attr('data-toggle-callback') : '';
		if ( toggleCallback.length ) {
			eval('var toggleCallbackFunc = '+toggleCallback+';');
			// when callback event was fired
			// ===> run the callback function
			$(triggerElement).on('inputValueCallback.bsx', toggleCallbackFunc(evt));
			$(document).on('inputValueCallback.bsx', function(evt){
				triggerElement.callback = toggleCallbackFunc;
				triggerElement.callback(evt);
			});
			// trigger callback event now!
			$(document).trigger('inputValueCallback.bsx').off('inputValueCallback.bsx');
		}
	});


});





//==================== JQUERY-BLOCKUI (v2.66) ====================

/*
* jQuery BlockUI; v20131009
* http://jquery.malsup.com/block/
* Copyright (c) 2013 M. Alsup; Dual licensed: MIT/GPL
*/
(function(){"use strict";function e(e){function o(o,i){var s,h,k=o==window,v=i&&void 0!==i.message?i.message:void 0;if(i=e.extend({},e.blockUI.defaults,i||{}),!i.ignoreIfBlocked||!e(o).data("blockUI.isBlocked")){if(i.overlayCSS=e.extend({},e.blockUI.defaults.overlayCSS,i.overlayCSS||{}),s=e.extend({},e.blockUI.defaults.css,i.css||{}),i.onOverlayClick&&(i.overlayCSS.cursor="pointer"),h=e.extend({},e.blockUI.defaults.themedCSS,i.themedCSS||{}),v=void 0===v?i.message:v,k&&b&&t(window,{fadeOut:0}),v&&"string"!=typeof v&&(v.parentNode||v.jquery)){var y=v.jquery?v[0]:v,m={};e(o).data("blockUI.history",m),m.el=y,m.parent=y.parentNode,m.display=y.style.display,m.position=y.style.position,m.parent&&m.parent.removeChild(y)}e(o).data("blockUI.onUnblock",i.onUnblock);var g,I,w,U,x=i.baseZ;g=r||i.forceIframe?e('<iframe class="blockUI" style="z-index:'+x++ +';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+i.iframeSrc+'"></iframe>'):e('<div class="blockUI" style="display:none"></div>'),I=i.theme?e('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:'+x++ +';display:none"></div>'):e('<div class="blockUI blockOverlay" style="z-index:'+x++ +';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>'),i.theme&&k?(U='<div class="blockUI '+i.blockMsgClass+' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:'+(x+10)+';display:none;position:fixed">',i.title&&(U+='<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(i.title||"&nbsp;")+"</div>"),U+='<div class="ui-widget-content ui-dialog-content"></div>',U+="</div>"):i.theme?(U='<div class="blockUI '+i.blockMsgClass+' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:'+(x+10)+';display:none;position:absolute">',i.title&&(U+='<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(i.title||"&nbsp;")+"</div>"),U+='<div class="ui-widget-content ui-dialog-content"></div>',U+="</div>"):U=k?'<div class="blockUI '+i.blockMsgClass+' blockPage" style="z-index:'+(x+10)+';display:none;position:fixed"></div>':'<div class="blockUI '+i.blockMsgClass+' blockElement" style="z-index:'+(x+10)+';display:none;position:absolute"></div>',w=e(U),v&&(i.theme?(w.css(h),w.addClass("ui-widget-content")):w.css(s)),i.theme||I.css(i.overlayCSS),I.css("position",k?"fixed":"absolute"),(r||i.forceIframe)&&g.css("opacity",0);var C=[g,I,w],S=k?e("body"):e(o);e.each(C,function(){this.appendTo(S)}),i.theme&&i.draggable&&e.fn.draggable&&w.draggable({handle:".ui-dialog-titlebar",cancel:"li"});var O=f&&(!e.support.boxModel||e("object,embed",k?null:o).length>0);if(u||O){if(k&&i.allowBodyStretch&&e.support.boxModel&&e("html,body").css("height","100%"),(u||!e.support.boxModel)&&!k)var E=d(o,"borderTopWidth"),T=d(o,"borderLeftWidth"),M=E?"(0 - "+E+")":0,B=T?"(0 - "+T+")":0;e.each(C,function(e,o){var t=o[0].style;if(t.position="absolute",2>e)k?t.setExpression("height","Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:"+i.quirksmodeOffsetHack+') + "px"'):t.setExpression("height",'this.parentNode.offsetHeight + "px"'),k?t.setExpression("width",'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"'):t.setExpression("width",'this.parentNode.offsetWidth + "px"'),B&&t.setExpression("left",B),M&&t.setExpression("top",M);else if(i.centerY)k&&t.setExpression("top",'(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"'),t.marginTop=0;else if(!i.centerY&&k){var n=i.css&&i.css.top?parseInt(i.css.top,10):0,s="((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "+n+') + "px"';t.setExpression("top",s)}})}if(v&&(i.theme?w.find(".ui-widget-content").append(v):w.append(v),(v.jquery||v.nodeType)&&e(v).show()),(r||i.forceIframe)&&i.showOverlay&&g.show(),i.fadeIn){var j=i.onBlock?i.onBlock:c,H=i.showOverlay&&!v?j:c,z=v?j:c;i.showOverlay&&I._fadeIn(i.fadeIn,H),v&&w._fadeIn(i.fadeIn,z)}else i.showOverlay&&I.show(),v&&w.show(),i.onBlock&&i.onBlock();if(n(1,o,i),k?(b=w[0],p=e(i.focusableElements,b),i.focusInput&&setTimeout(l,20)):a(w[0],i.centerX,i.centerY),i.timeout){var W=setTimeout(function(){k?e.unblockUI(i):e(o).unblock(i)},i.timeout);e(o).data("blockUI.timeout",W)}}}function t(o,t){var s,l=o==window,a=e(o),d=a.data("blockUI.history"),c=a.data("blockUI.timeout");c&&(clearTimeout(c),a.removeData("blockUI.timeout")),t=e.extend({},e.blockUI.defaults,t||{}),n(0,o,t),null===t.onUnblock&&(t.onUnblock=a.data("blockUI.onUnblock"),a.removeData("blockUI.onUnblock"));var r;r=l?e("body").children().filter(".blockUI").add("body > .blockUI"):a.find(">.blockUI"),t.cursorReset&&(r.length>1&&(r[1].style.cursor=t.cursorReset),r.length>2&&(r[2].style.cursor=t.cursorReset)),l&&(b=p=null),t.fadeOut?(s=r.length,r.stop().fadeOut(t.fadeOut,function(){0===--s&&i(r,d,t,o)})):i(r,d,t,o)}function i(o,t,i,n){var s=e(n);if(!s.data("blockUI.isBlocked")){o.each(function(){this.parentNode&&this.parentNode.removeChild(this)}),t&&t.el&&(t.el.style.display=t.display,t.el.style.position=t.position,t.parent&&t.parent.appendChild(t.el),s.removeData("blockUI.history")),s.data("blockUI.static")&&s.css("position","static"),"function"==typeof i.onUnblock&&i.onUnblock(n,i);var l=e(document.body),a=l.width(),d=l[0].style.width;l.width(a-1).width(a),l[0].style.width=d}}function n(o,t,i){var n=t==window,l=e(t);if((o||(!n||b)&&(n||l.data("blockUI.isBlocked")))&&(l.data("blockUI.isBlocked",o),n&&i.bindEvents&&(!o||i.showOverlay))){var a="mousedown mouseup keydown keypress keyup touchstart touchend touchmove";o?e(document).bind(a,i,s):e(document).unbind(a,s)}}function s(o){if("keydown"===o.type&&o.keyCode&&9==o.keyCode&&b&&o.data.constrainTabKey){var t=p,i=!o.shiftKey&&o.target===t[t.length-1],n=o.shiftKey&&o.target===t[0];if(i||n)return setTimeout(function(){l(n)},10),!1}var s=o.data,a=e(o.target);return a.hasClass("blockOverlay")&&s.onOverlayClick&&s.onOverlayClick(o),a.parents("div."+s.blockMsgClass).length>0?!0:0===a.parents().children().filter("div.blockUI").length}function l(e){if(p){var o=p[e===!0?p.length-1:0];o&&o.focus()}}function a(e,o,t){var i=e.parentNode,n=e.style,s=(i.offsetWidth-e.offsetWidth)/2-d(i,"borderLeftWidth"),l=(i.offsetHeight-e.offsetHeight)/2-d(i,"borderTopWidth");o&&(n.left=s>0?s+"px":"0"),t&&(n.top=l>0?l+"px":"0")}function d(o,t){return parseInt(e.css(o,t),10)||0}e.fn._fadeIn=e.fn.fadeIn;var c=e.noop||function(){},r=/MSIE/.test(navigator.userAgent),u=/MSIE 6.0/.test(navigator.userAgent)&&!/MSIE 8.0/.test(navigator.userAgent);document.documentMode||0;var f=e.isFunction(document.createElement("div").style.setExpression);e.blockUI=function(e){o(window,e)},e.unblockUI=function(e){t(window,e)},e.growlUI=function(o,t,i,n){var s=e('<div class="growlUI"></div>');o&&s.append("<h1>"+o+"</h1>"),t&&s.append("<h2>"+t+"</h2>"),void 0===i&&(i=3e3);var l=function(o){o=o||{},e.blockUI({message:s,fadeIn:o.fadeIn!==void 0?o.fadeIn:700,fadeOut:o.fadeOut!==void 0?o.fadeOut:1e3,timeout:o.timeout!==void 0?o.timeout:i,centerY:!1,showOverlay:!1,onUnblock:n,css:e.blockUI.defaults.growlCSS})};l(),s.css("opacity"),s.mouseover(function(){l({fadeIn:0,timeout:3e4});var o=e(".blockMsg");o.stop(),o.fadeTo(300,1)}).mouseout(function(){e(".blockMsg").fadeOut(1e3)})},e.fn.block=function(t){if(this[0]===window)return e.blockUI(t),this;var i=e.extend({},e.blockUI.defaults,t||{});return this.each(function(){var o=e(this);i.ignoreIfBlocked&&o.data("blockUI.isBlocked")||o.unblock({fadeOut:0})}),this.each(function(){"static"==e.css(this,"position")&&(this.style.position="relative",e(this).data("blockUI.static",!0)),this.style.zoom=1,o(this,t)})},e.fn.unblock=function(o){return this[0]===window?(e.unblockUI(o),this):this.each(function(){t(this,o)})},e.blockUI.version=2.66,e.blockUI.defaults={message:"<h1>Please wait...</h1>",title:null,draggable:!0,theme:!1,css:{padding:0,margin:0,width:"30%",top:"40%",left:"35%",textAlign:"center",color:"#000",border:"3px solid #aaa",backgroundColor:"#fff",cursor:"wait"},themedCSS:{width:"30%",top:"40%",left:"35%"},overlayCSS:{backgroundColor:"#000",opacity:.6,cursor:"wait"},cursorReset:"default",growlCSS:{width:"350px",top:"10px",left:"",right:"10px",border:"none",padding:"5px",opacity:.6,cursor:"default",color:"#fff",backgroundColor:"#000","-webkit-border-radius":"10px","-moz-border-radius":"10px","border-radius":"10px"},iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank",forceIframe:!1,baseZ:1e3,centerX:!0,centerY:!0,allowBodyStretch:!0,bindEvents:!0,constrainTabKey:!0,fadeIn:200,fadeOut:400,timeout:0,showOverlay:!0,focusInput:!0,focusableElements:":input:enabled:visible",onBlock:null,onUnblock:null,onOverlayClick:null,quirksmodeOffsetHack:4,blockMsgClass:"blockMsg",ignoreIfBlocked:!1};var b=null,p=[]}"function"==typeof define&&define.amd&&define.amd.jQuery?define(["jquery"],e):e(jQuery)})();