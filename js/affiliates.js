// если активна настройка "ѕрисваивать redirect к URL чужих сайтов", то данный код нужно вставить перед счетчикам сайта
$(".redirect").each(function(){
	var redirect = $(this).attr("href").split("/system/redirect?to=")[1];
	if(redirect){
		$(this).attr("href", redirect);
	}
});