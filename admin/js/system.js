var wysiwygOffCookieName = '';
var wysiwygOnLoaded = false;
var MceWidth = 0;
var screenWidthForMce = 0;

function wysiwygOnload(){// ф-я применения визуального редактора (запускается в конце страницы)
	
	screenWidthForMce = screenWidth();// узнаем ширину экрана, чтобы затем выставить правильную ширину визивику
	var t = $("TEXTAREA");
	if(t.length < 1){
		return ;
	}
	MceWidth = t[0].offsetWidth - 6;
	
	wysiwygOffCookieName = $("#wysiwyg").attr("alt")? $("#wysiwyg").attr("alt") : 'wysiwyg_off';
	Cookie(wysiwygOffCookieName, function(r){
		if(r=="1"){// если выключен редактор
			$("#wysiwyg").css("opacity", "0.3");
		}else{
			$("#wysiwyg").css("opacity", "1");
			wysiwyg();
		}
	});
}
$(document).ready(function(){
	
	$("#wysiwyg").click(function(){// обрабатываем клик по вкл/выкл визивик
		var opacity = $(this).css("opacity");
		if(opacity=="1"){// если включен редактор - выключаем его
			$(this).css("opacity","0.3");
			Cookie(wysiwygOffCookieName, "1");
		}else{// включаем редактор
			$(this).css("opacity","1");
			Cookie(wysiwygOffCookieName, "0");
		}
		wysiwyg();
	}).css("margin-left","10px").css("cursor","pointer");
});
function wysiwyg(){
	if(!wysiwygOnLoaded){
		var src = "http://"+document.location.host+"/js/tiny_mce/tiny_mce.js";
		tinyMCEPreInit = { base : "http://"+document.location.host+"/js/tiny_mce", suffix : ""};
		$.getScript(src, function() {
			wysiwygOnLoaded = true;// делаю пометку о том, что визуальный редактор загружен
			tinymce.dom.Event.domLoaded = true;//tinymce.dom.Event._pageInit();
			wysiwyg();
		});
		return false;
	}
	Cookie(wysiwygOffCookieName, function(r){
		if(r=="1"){// если сказано отключить - отключаем
			if(tinyMCE.activeEditor!=null){
				$('.wysiwyg').each(function(){
					tinyMCE.execCommand('mceRemoveControl', false, this.name);
				});
			}
		}else{// если сказано включить - включаем
			
			if(tinyMCE.activeEditor==null){
				tinyMCE.init({
					language : "ru",
					mode : "textareas",
					elements : "nourlconvert",
					convert_urls : false,
					theme : "advanced",
					editor_selector : "wysiwyg",
					plugins : "safari,pagebreak,style,table,advhr,advimage,advlink,iespell,media,contextmenu,paste,visualchars,nonbreaking,xhtmlxtras,fullscreen,yaproFastImage,yaproHideMe,yaproInsertHTML,yaproWatermark,inlinepopups,lebnikDisableParse",
					// Theme options
					theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,strikethrough,sub,sup,charmap,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,fontselect,|,forecolor,backcolor,removeformat,|,hr,fullscreen",
					theme_advanced_buttons2 : "yaproFastImage,image,media,yaproInsertHTML,pasteword,pastetext,yaproHideMe,|,link,unlink,anchor,|,outdent,indent,|,tablecontrols,yaproWatermark,code,lebnikDisableParse,newdocument,pagebreak",
					theme_advanced_buttons3 : false,
					theme_advanced_buttons4 : false,
					theme_advanced_font_sizes: "9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px,25px,26px,27px,28px,29px,30px,31px,32px,33px,34px,35px,36px,37px",
					font_size_style_values : "9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px,25px,26px,27px,28px,29px,30px,31px,32px,33px,34px,35px,36px,37px",
					file_browser_callback : "tinyMCEFileBrowserByLebnik",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					/*theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,*/
					accessibility_warnings : false,
					dialog_type : "modal",
					media_strict : false,
					extended_valid_elements : "div[align|class|style|id|title],iframe[name|src|framespacing|border|frameborder|scrolling|title|height|width|hspace|vspace|marginwidth|marginheight|allowfullscreen],object[declare|classid|codebase|data|type|codetype|archive|standby|height|width|usemap|name|tabindex|align|border|hspace|vspace],link[rel|type|href],video[tabindex|controls|preload|poster|width|height],source[src|type],form[id|class|style|title|lang|dir|action|method|enctype|accept|accept-charset|name|target|onsubmit|onreset]",
					content_css : "http://" + location.hostname + "/css/tiny_mce.css?v1",
					table_styles : "cellpadding5=cellpadding5;styleTable=styleTable;cellpadding5 styleTable=cellpadding5 styleTable",
					onchange_callback : "wysiwygOnChange",
					oninit : "wysiwygOninit"// выставляем ширину
				});
			}else{
				$('.wysiwyg').each(function(){
					tinyMCE.execCommand('mceAddControl', false, this.name);
				});
			}
		}
		ajaxUploadFiles();
	});
}
function tinyMCEFileBrowserByLebnik(field_name, url, type, win){
       if(field_name){
          if(!openBrowserPuth || openBrowserPuth==""){
             alert("Не указан путь к окну файлов! Javascript переменная openBrowserPuth не существует или пуста!");
          }else{
             var window_name = Math.round(Math.random() * 1000000000);
             var w = "770";
             var h = "570";
             var valLeft = (screen.width) ? (screen.width-w)/2 : 0;// отступ слева
             var valTop = (screen.height) ? (screen.height-h)/2 : 0;// отступ сверху
             var features = "width="+w+",height="+h+",left="+valLeft+",top="+valTop+",";
             window.open(openBrowserPuth, window_name, features+'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1');
          }
       }
}
// плагин ajaxUploadFiles
function ajaxUploadFiles(){
	
	if(typeof(ajaxUploadFileAddress)=="undefined"){
		
		ajaxUploadFileA = null;// активный элемент А, который определяется в момент нахождения курсора над элементом А
		yaproMouseenterElement = null;// активный элемент (альтернативный текущему)
		yaproAllowFileTypes = null;// активный элемент
		ajaxUploadFileTimeLabel = 'ajaxUploadFile';
		ajaxUploadFileAddress = window.location.href;
		var address = ajaxUploadFileAddress.split("#");
		var address_split = address[0].split("?");
		var separate = address_split[1]? "&" : "?";
		ajaxUploadFileAddress = address[0] + separate;
		ajaxUploadFile = $('<input type="file" name="ajax" style="cursor:pointer">');
		ajaxUploadFileForm = $('<form method="post" enctype="multipart/form-data" action="/" style="position: absolute; left: -555px; z-index: 1234567" target="ajaxiframe"></form>');
		ajaxUploadFileiFrame = $('<iframe name="ajaxiframe" src="'+ajaxUploadFileAddress+'ajaxUploadFile=1" style="display:none"></iframe>');
		$(document.body).prepend( ajaxUploadFileForm.prepend(ajaxUploadFile) ).prepend(ajaxUploadFileiFrame);
		
		ajaxUploadFileTEXTAREA = null;// имя активной TEXTAREA
		ajaxUploadFileMinus = ajaxUploadFile[0].offsetWidth - 20;
		
		$(ajaxUploadFile).css("opacity","0").change(function(){// обрабатываем файловое поле
			var file_name = checkFileType(this.value, yaproAllowFileTypes);// проверяю тип файла и исправляю имя файла
			if(file_name){// если тип файл и имя в порядке продолжаю:
				
				AjaxWindowLoad();
				
				if(!yaproMouseenterElement){
					$(">img",ajaxUploadFileA).css("visibility","hidden");
					ajaxUploadFileTEXTAREA = $(ajaxUploadFileA).firstParent(".mceEditor").prev().attr("name");// определяю текущий wisiwyg
				}
				$(ajaxUploadFileForm).attr("action", ajaxUploadFileAddress + "ajaxUploadFile=" + Math.round(Math.random() * 1000000000) );
				
				ajaxUploadFileForm.submit();
			}
		}).mouseover(function(){
			if(!yaproMouseenterElement){ $(ajaxUploadFileA).css("background-color","#B2BBB0").css("border","1px solid #0A246A"); }
		}).mouseout(function(){
			if(!yaproMouseenterElement){ $(ajaxUploadFileA).css("background-color","#F0F0EE").css("border","1px solid #F0F0EE"); }
		}).bind("mouseleave", function(){
			$(ajaxUploadFileForm).css("display","none");
		});
		
		ajaxUploadFileiFrame.unbind().load(function(){// обрабатываем iframe-загрузку
			
			var response = $(this.contentWindow.document.body).html();// информация о завершении загрузки файла
			
			if(response.substr(0,1)=='/'){// если файл загружен
				if(yaproMouseenterElement){
					setInputTypeText(response);
					yaproMouseenterElement = null;
				}else if(ajaxUploadFileA){
					$(">img", ajaxUploadFileA).css("visibility","visible");
					tinyMCE.get( ajaxUploadFileTEXTAREA ).execCommand("mceInsertContent", false, '<img src="'+openBrowserSite+response+'" border=0>');
					ajaxUploadFileA = null;
				}
			}else if(response){
				jAlert(response);
			}
			AjaxWindowLoad('hide');
		});
	}
}
function wysiwygOninit(){
	
	wysiwygWidth();
	
	to_mce_yaproFastImage();
	
	$(".mce_fullscreen SPAN").mouseup(function(e){
		var nothing = setTimeout(function(){
			to_mce_yaproFastImage("#mce_fullscreen_container ");
		},150);
	});
}
function to_mce_yaproFastImage(mce_fullscreen_container){// плагин ajaxUploadFile - обработка событий кнопки загрузки файлов
	
	var mce_fullscreen_container = (typeof(mce_fullscreen_container)=="undefined")? "" : mce_fullscreen_container;
	
	$(mce_fullscreen_container+".mce_yaproFastImage").each(function(){
		$(this).bind("mouseenter",function(){
			yaproMouseenterElement = null;
			yaproAllowFileTypes = 'jpg.jpeg.png.gif';
			ajaxUploadFileA = this;
			var position = PosElement(this);
			ajaxUploadFileForm.css({
				display: 'block',
				top: position.Top+'px',
				left: (position.Left - ajaxUploadFileMinus)+'px'
			});
		});
	});
}

function wysiwygWidth(){
	var srollBarWidth = screenWidthForMce - screenWidth();// находим ширину скроллбара (если он появился после появления визивика)
	$(".mceLayout").css("width", (MceWidth - srollBarWidth)+"px");
}
// проверка типа файла
function checkFileType(name, types){
	var file_name = name;
	if(file_name && !$.browser.mozilla){// определяем имя выбранного файла
		var split_reverse = array_reverse( file_name.split("\\") );
		file_name = split_reverse[0];
	}
	var access = false;
	var arr_types = types.split(".");
	var file_type = array_reverse( file_name.split(".") );
		file_type = file_type[0].toLowerCase();
	for(var i=0; i<arr_types.length; i++){
		if(!access && arr_types[i] == file_type){
			access = true;
		}
	}
	if(access){ return file_name; }else{ jAlert("Тип файла "+file_type+" запрещен!\nРазрешены типы файлов: "+types); }
}
// добавляем активные кнопки
function setInputTypeText(path){
	$(yaproMouseenterElement).unbind("mouseenter").css("background","").html('<img src="images/elements/del.gif" class="cursor" onclick="setInputTypeFile($(this).parent())"> <input name="'+$(yaproMouseenterElement).data('name')+'" type="text" value="'+Url.encode(path).replace(/%25/g,"%")+'" class="input_text" style="width: 91%; position: relative; _top: -1px;" onclick="this.select()" ondblclick="window.open(openBrowserSite+this.value);" class="cursor" title="Двойной клик откроет файл в новом окне" readonly>');
}
// добавляем файловое поле в блок DIV
function setInputTypeFile(e){
	// добавляем изображение символизирующее возможность Добавить
	$(e).html('<input name="'+$(e).data("name")+'" type="hidden" value="">').css("background","url(images/elements/add.gif) no-repeat 5px 7px").bind("mouseenter",function(){
		yaproMouseenterElement = this;
		yaproAllowFileTypes = $(this).data("types");
		var position = PosElement(this);
		ajaxUploadFileForm.css({
			display: 'block',
			top: position.Top+'px',
			left: (position.Left - ajaxUploadFileMinus)+'px'
		});
	});
}
// добавляет возможность работы с файлами
function filesForm(){
	
	if($(".UploadFile").length){ ajaxUploadFiles(); }
	
	$(".UploadFile").each(function(){
		
		var settings = $(this).attr("alt").split("~~~");
		
		/*
		0 - field_name - имя поля
		1 - file_types - разрешенные типы файлов через точку
		2 - file_path - имя загруженного файла из базы данных
		*/
		if(settings[0] && settings[1]){
			
			$(this).data("name", settings[0]).data("types", settings[1]);// добавляем данные для последующей работы с ними
			
			yaproMouseenterElement = this;
			
			if(settings[2]){// если файл был загружен ранее
				
				setInputTypeText(settings[2]);// добавляем активные элементы
				
			}else{// добавляем возможность выбора файла
				
				setInputTypeFile(this);// добавляем поле выбора файла
			}
			yaproMouseenterElement = null;
		}
	});
}
// Url.encode(this.value)
var Url = {
	// public method for url encoding
	encode : function (string) {
		return escape(this._utf8_encode(string));
	},
	// public method for url decoding
	decode : function (string) {
		return this._utf8_decode(unescape(string));
	},
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}
// устанавливает данные по-умолчанию на выбранные элементы и ставит отслеживание изменений в заданных элементах
function onChangeToCookies(cookie_prefix, elements_names, set){
	
	for(var i = 0; i < elements_names.length; i++ ) {
		$("[name="+elements_names[i]+"]").change(function(){ Cookie(this.name+cookie_prefix, this.value); });
		if(set){ $("[name="+elements_names[i]+"]").val( Cookie(elements_names[i]+cookie_prefix) ); }
	}
}
// возвращает позицию scrollTop для IE или др. браузера
function sFixed(top){
	return (top + (ignoreMe=document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop))+"px"
}

// получаем высоту видимой области страницы
function screenHeight(){
	return $.browser.opera? window.innerHeight : $(window).height();
}

// узнаем, наличие скроллбара у текущей страницы
function isScrollBar(){
	return ( document.body.scrollHeight>screenHeight() )? true : false;
}
// перезагружает левый внутренний фрейм
function reload_left_frame(){
	var l = window.parent.left_frame;
	if(l && !l.closed){
		$(window.parent.left_frame.document.body.children).each(function(){
			if($(this).attr("name")=="system_iframe"){
				l.$.fancybox.showActivity();
				//this.src = this.src+"1"
				this.contentWindow.location.reload();
				$(this).load(function(){ l.$.fancybox.hideActivity(); });
			}
		});
	}
}
// показываем поле, в котором неправильно введены данные
jQuery.fn.Attention = function(e){ var position = PosElement(this[0]); scrollTo(0, position.Top-30); this[0].focus(); };

function wysiwygOnChange(){
	
}
