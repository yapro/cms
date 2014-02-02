var COMMENTS_LINE = COMMENTS_PARENT = null;
var comments_notBot = false;// устанавливается скриптами в true - если посетитель не бот

// функция которую я вызываю, если возвращается пустой ajax-результат
function ajax_return_empty(a,d){
	$.post("/outer/ajax_return_empty.php", {"action":a,"data":d}, function(msg){
		if(!msg){ msg = "Возникла ошибка программного характера, мы уже знаем об этом и решаем проблему, извините за доставленное неудобство"; }
		if($.isFunction($.jGrowl)){// если подключен плагин scrollTo
			$.jGrowl(msg, { speed: 0, life:7000, theme: "red" });
		}else if($.isFunction(jAlert)){
			jAlert(msg);
		}else{
			alert(msg);
		}
	});
}
