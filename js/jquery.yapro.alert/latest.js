/*
// пример алерта с кэллбэк-функцией и указанием ширины в пикселах
jAlert("Вы не указали Название", function(){
	$("[name=name]").focus();
}, 150);

// с шириной в др. величине
jAlert("Вы не указали Название", function(){
	$("[name=name]").focus();
}, "150pt");

// пример работы с внешним объектом
var e = $("[name=name]");
jAlert("Вы не указали Название", function(){
	$(e).focus();
});

jConfirm("Подтверждаете удаление?", function(r) {
	if(r==true){
		alert("Согласие получено");
	}
});

// пример промпта
jPrompt("Введите имя новой директории:", "значение по умолчанию", callback, width, ok, cancel)

// передаем данные объектом
jPrompt({'msg':'Type something:', 'val':'Prefilled value','width':'300px','ok':'OKKs','cancel':'xxax', 'callback': function(r) {
    alert('You entered: ' + r);
}});

// подгрузка изображений в pop-ап окне
$("A.miniPhoto").click(function(){
	AjaxWindowLoad();
	jImage(this, function(){
		AjaxWindowLoad("hide");
	});
	return false;
});
*/
(function($) {
	
	$.alerts = {
		
		verticalOffset: -75,                // vertical offset of the dialog from center screen, in pixels
		horizontalOffset: 0,                // horizontal offset of the dialog from center screen, in pixels/
		repositionOnResize: true,           // re-centers the dialog on window resize
		overlayOpacity: .09,                // transparency level of overlay
		overlayColor: '#000',               // base color of overlay
		draggable: true,                    // make the dialogs draggable (requires UI Draggables plugin)
		okButton: '&nbsp;OK&nbsp;',         // text for the OK button
		cancelButton: '&nbsp;Отмена&nbsp;', // text for the Cancel button
		dialogClass: null,                  // if specified, this class will be applied to all dialogs
		
		// Public methods
		
		alert: function(message, callback, width, ok) {
			if(typeof(message)=="object"){ var callback = message.callback; }
			$.alerts._show(message, null, 'alert', function(result) {
				if( callback ) callback(result);
			}, width, ok);
		},
		
		confirm: function(message, callback, width, ok, cancel) {
			if(typeof(message)=="object"){ var callback = message.callback; }
			$.alerts._show(message, null, 'confirm', function(result) {
				if( callback ) callback(result);
			}, width, ok);
		},
			
		prompt: function(message, value, callback, width, ok, cancel) {
			if(typeof(message)=="object"){ var callback = message.callback; }
			$.alerts._show(message, value, 'prompt', function(result) {
				if( callback ) callback(result);
			}, width, ok, cancel);
		},
		
		// Private methods
		
		_show: function(msg, value, type, callback, width, ok, cancel) {
			
			if(typeof(msg)=="object"){
				var ok = msg.ok;
				var cancel = msg.cancel;
				var width = msg.width;
				var callback = msg.callback;
				var value = msg.val;
				var msg = msg.msg;
			}
			
			$.alerts._hide();
			$.alerts._overlay('show');
			
			if(!isNaN(width) && width!=0){ width = width+"px"; }// если указано число, значит по дефолту оно указано в пикселах
			
			$("BODY").append(
			  '<div id="popup_container"'+(width? ' style="width:'+width+'; min-width:'+width+'"':'')+'>' +
			    '<div id="popup_content">' +
			      '<div id="popup_message"></div>' +
				'</div>' +
			  '</div>');
			
			if( $.alerts.dialogClass ) $("#popup_container").addClass($.alerts.dialogClass);
			
			// IE6 Fix
			var pos = ($.browser.msie && parseInt($.browser.version) <= 6 ) ? 'absolute' : 'fixed'; 
			
			$("#popup_container").css({
				position: pos,
				zIndex: 99999,
				padding: 0,
				margin: 0
			});
			
			$("#popup_content").addClass(type);
			$("#popup_message").text(msg);
			$("#popup_message").html( $("#popup_message").text() );//.text().replace(/\n/g, '<br />')
			
			$("#popup_container").css({
				minWidth: $("#popup_container").outerWidth(),
				maxWidth: $("#popup_container").outerWidth()
			});
			
			$.alerts._reposition();
			$.alerts._maintainPosition(true);
			
			var timeStart = new Date().valueOf();
			
			switch( type ) {
				case 'alert':
					$("#popup_message").after('<div id="popup_panel"><input type="button" value="' + (ok? ok : $.alerts.okButton) + '" id="popup_ok" /></div>');
					$("#popup_ok").focus().click( function() {
						var timeEnd = new Date().valueOf();
						if( timeEnd > (timeStart + 123) ){
							$.alerts._hide();
							callback(true);
						}
					});
					$(document).keypress( function(e) {
						if( e.keyCode == 13 || e.keyCode == 27 ){
							$("#popup_ok").trigger('click');
						}
					});
				break;
				case 'confirm':
					$("#popup_message").after('<div id="popup_panel"><input type="button" value="' + (ok? ok : $.alerts.okButton) + '" id="popup_ok" /> <input type="button" value="' + (cancel? cancel : $.alerts.cancelButton) + '" id="popup_cancel" /></div>');
					$("#popup_ok").click( function() {
						$.alerts._hide();
						if( callback ) callback(true);
					});
					$("#popup_cancel").click( function() {
						$.alerts._hide();
						if( callback ) callback(false);
					});
					$("#popup_ok").focus();
					$(document).keypress( function(e) {
						if(e.keyCode == 27){
							$("#popup_cancel").trigger('click');
						}
					});
					$("#popup_ok").keypress( function(e) {
						if( e.keyCode == 13 ){
							$("#popup_ok").trigger('click');
						}
					});
					$("#popup_cancel").keypress( function(e) {
						if(e.keyCode == 13){ $("#popup_cancel").trigger('click'); }
					});
				break;
				case 'prompt':
					$("#popup_message").append('<br /><input type="text" size="30" id="popup_prompt" />').after('<div id="popup_panel"><input type="button" value="' + (ok? ok : $.alerts.okButton) + '" id="popup_ok" /> <input type="button" value="' + (cancel? cancel : $.alerts.cancelButton) + '" id="popup_cancel" /></div>');
					$("#popup_prompt").width( $("#popup_message").width() );
					$("#popup_ok").click( function(e) {
						var timeEnd = new Date().valueOf();
						if( timeEnd> (timeStart + 123) ){
							var val = $("#popup_prompt").val();
							$.alerts._hide();
							if( callback ) callback( val );
						}
					});
					$("#popup_cancel").click( function() {
						$.alerts._hide();
						if( callback ) callback( null );
					});
					$("#popup_prompt, #popup_ok").keypress( function(e) {
						if( e.keyCode == 13 ){
							$("#popup_ok").trigger('click');
						}
					});
					$("#popup_cancel").keypress( function(e) {
						if( e.keyCode == 13 ||  e.keyCode == 27 ){
							$("#popup_cancel").trigger('click');
						}
					});
					$(document).keypress( function(e) {
						if(e.keyCode == 27){
							$("#popup_cancel").trigger('click');
						}
					});
					if( value ) $("#popup_prompt").val(value);
					$("#popup_prompt").focus().select();
				break;
			}
			
			// Make draggable
			if( $.alerts.draggable ) {
				try {
					$("#popup_container").draggable({ handle: $("#popup_container") });
				} catch(e) { /* requires jQuery UI draggables */ }
			}
		},
		
		_hide: function() {
			$("#popup_container").remove();
			$.alerts._overlay('hide');
			$.alerts._maintainPosition(false);
		},
		
		_overlay: function(status) {
			switch( status ) {
				case 'show':
					$.alerts._overlay('hide');
					$("BODY").append('<div id="popup_overlay"></div>');
					$("#popup_overlay").css({
						position: 'absolute',
						zIndex: 99998,
						top: '0px',
						left: '0px',
						width: '100%',
						height: $(document).height(),
						background: $.alerts.overlayColor,
						opacity: $.alerts.overlayOpacity
					});
				break;
				case 'hide':
					$("#popup_overlay").remove();
				break;
			}
		},
		
		_reposition: function() {
			var top = (($(window).height() / 2) - ($("#popup_container").outerHeight() / 2)) + $.alerts.verticalOffset;
			var left = (($(window).width() / 2) - ($("#popup_container").outerWidth() / 2)) + $.alerts.horizontalOffset;
			if( top < 0 ) top = 0;
			if( left < 0 ) left = 0;
			
			// IE6 fix
			if( $.browser.msie && parseInt($.browser.version) <= 6 ) top = top + $(window).scrollTop();
			
			$("#popup_container").css({
				top: top + 'px',
				left: left + 'px'
			});
			$("#popup_overlay").height( $(document).height() );
		},
		
		_maintainPosition: function(status) {
			if( $.alerts.repositionOnResize ) {
				switch(status) {
					case true:
						$(window).bind('resize', $.alerts._reposition);
					break;
					case false:
						$(window).unbind('resize', $.alerts._reposition);
					break;
				}
			}
		}
		
	}
	
	// Shortuct functions
	jAlert = function(message, callback, width, ok){
		$.alerts.alert(message, callback, width, ok);
	}
	
	jConfirm = function(message, callback, width, ok, cancel) {
		$.alerts.confirm(message, callback, width, ok, cancel);
	};
	
	jPrompt = function(message, value, callback, width, ok, cancel) {
		$.alerts.prompt(message, value, callback, width, ok, cancel);
	};
	
	jImage = function(a, callback){
		var img = $('<img src="'+a.href+'" class="Hidden">');
		$(document.body).append(img);
		$(img).load(function(){
			
			var w = screenWidth();
			var h = screenHeight();
			//console.log("screenWidth / screenHeight = "+w+" / "+h);
			var w_max = w - 67;
			var h_max = h - 75;//($.browser.msie? 85 : 75)
			//console.log("w_max screen / h_max screen = "+w_max+" / "+h_max);
			var w_set = h_set = null;
			//console.log("this.width / this.height = "+this.width+" / "+this.height);
			if(this.width > w_max && this.height > h_max){
				
				var w_proportion = w_max;
				var h_proportion = ( (w_max * this.height) / this.width ).toFixed();
				//console.log("w_proportion / h_proportion = "+w_proportion+" / "+h_proportion);
				
				if(h_proportion > h_max){
					h_set = h_max;
					//console.log("h_set = "+h_set);
				}else{
					w_set = w_max;
					//console.log("w_set = "+w_set);
				}
				
			}else if(this.width > w_max){
				
				w_set = w_max;
				//console.log("w_set = "+w_set);
			}else if(this.height > h_max){
				
				h_set = h_max;
				//console.log("h_set = "+h_set);
			}
			
			$.alerts.alert('<img src="'+this.src+'"'+(w_set? ' width='+w_set : '') + (h_set? ' height='+h_set : '')+'>', null, null, "закрыть | Esc");
			
			if(callback){ callback(); }
		});
	};
	
})(jQuery);
