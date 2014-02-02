/*
библиотека обрабатываем INPUT поля с объявленным CSS классом, например ajaxFile, под адреса Ajax-файлов (С) Лебеденко Н.Н. / Lebnik 2011
пример запуска:
$.getScript("http://"+window.location.host+"/js/ajaxFile/ajaxFile.js", function(){
	ajaxFile(".ajaxFile", "http://"+window.location.host+"/js/ajaxFile/files.php");
});
*/
function ajaxFile(arg, address){
	
	var address_split = address.split("?");
	var separate = address_split[1]? "&" : "?";
	var ajaxAddress = address + separate;
	
	// добавляем изображение символизирующее возможность Добавить
	$(arg).each(function(){
		
		var iframeName = 'id_'+Math.round(Math.random() * 1000000000);
		
		var mouseoverStopTD = mouseleaveStopTD = false;// переменные для правильной обработки подстановки невидимой формы выбра файла
		
		var input = $(this).css("width","100%").attr("title","Двойной клик - просмотр загруженного");
		
		var table = $('<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="20">&nbsp;</td><td></td></tr></table>');
		$(this).after(table);
		$("TD:last", table).append(this);
		
		var td = $("TD:first", table).css("cursor","pointer");
		
		var setTD = function(td,input,response){
			
			mouseoverStopTD = true;
			
			input[0].style.visibility = "visible";
			
			if(response){ $(input[0]).val( response ); }
			
			$(input).unbind("click").unbind("dblclick").click(function(){//
				this.select();
			}).dblclick(function(){
				window.open( "http://"+window.location.host + this.value );
			});
			
			$(td).css("background","url(/js/ajaxFile/del.gif) no-repeat 2px 2px").unbind("click").click(function(){
				mouseoverStopTD = false;
				input[0].value = "";
				input[0].style.visibility = "hidden";
				$(this).css("background","url(/js/ajaxFile/add.gif) no-repeat 5px 5px").trigger("mouseover");
				return false;
			});
		};
		
		var file = $('<input type="file" name="html5_multiple_file_upload[]" style="cursor:pointer" title="Выбрать файл для загрузки на сайт">');
		
		var form = $('<form method="post" enctype="multipart/form-data" action="'+ajaxAddress+'ajaxFile='+iframeName+'" style="position: absolute; left: -555px; z-index: 12345" target="'+iframeName+'"></form>');// гет-переменную ajaxFile назначаем чтобы: 1. не кешировался сабмит, 2. чтобы идентифицировать ajax загрузку файла
		
		var iframe = $('<iframe name="'+iframeName+'" src="'+ajaxAddress+'blank=1" style="display:none"></iframe>');
		
		$(document.body).prepend( form.prepend( file ) ).prepend( iframe );
		
		// обрабатываем td у которого поле с классом ajaxFile
		$(td).mouseover(function(){
			
			if(mouseoverStopTD){ return false; }
			
			$(form).css("display","block");// сначала показываем форму (чтобы правильно определить offsetWidth)
			
			var position = PosElement(this);
			
			$(form).css({
				top: position.Top+'px',
				left: (position.Left - file[0].offsetWidth + 20)+'px'
			});
		}).mouseleave(function(){
			
			if(mouseleaveStopTD){ return false; }
			
			//+$(form).css("display","none");//.css("left","-555px;");
			
		});
		// обрабатываем файловое поле
		$(file).css("opacity","0").change(function(){
			
			$(td).css("background","url(/js/ajaxFile/loading.gif) no-repeat 2px 2px");
			
			form.submit();
			
		}).mouseover(function(){
			
			mouseleaveStopTD = true;
			
		}).mouseleave(function(){
			
			$(form).css("left","-555px;");
			
			mouseoverStopTD = true;
			mouseleaveStopTD = false;
			
			setTimeout(function(){
				mouseoverStopTD = false;
			}, 123);
			
		});
		
		// обрабатываем iframe-загрузку
		iframe.unbind().load(function(){
			
			$(form).css("display","none");
			
			var response = $(this.contentWindow.document.body).html();// информация о завершении загрузки файла
			
			if(response!=""){
				
				mouseoverStopTD = false;
				$(td).css("background","").unbind("mouseenter");
				
				var ex = response.split('<p><img src="');
				
				if(ex[1] && ex[1].substr(0,1)=='/'){// если файл загружен
					
					var ex = ex[1].split('"');
					
					setTD(td,input,ex[0]);
					
				}else{
					jAlert("Возможно Вы попытались загрузить неразрешенный тип файла.<br>Если Вы считаете что это не так, то наверняка возникла<br>ошибка программного характера, пожалуйста сообщите нам об этом");
				}
				
			}else{// всегда срабатывает при создании iframe
				
			}
		});
		
		if($(input).val()!=""){// если изображение уже загружено в базу
			mouseoverStopTD = true;
			setTD(td,input);
		}else{
			$(td).css("background","url(/js/ajaxFile/add.gif) no-repeat 5px 5px")
		}
	});
}
