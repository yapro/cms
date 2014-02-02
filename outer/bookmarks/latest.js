$(document).ready(function(){
	$("#lebnikZakladkiYandex").attr("title","Добавить в Яндекс.Закладки");
	$("#lebnikVkontakte").attr("title","Добавить в заметки Вконтакте");
	$("#lebnikFacebook").attr("title","Опубликовать в Фэйсбуке");
	$("#lebnikMailRu").attr("title","Опубликовать в Mail.Ru");
	$("#lebnikTwitter").attr("title","Опубликовать в Twitter");
	$("#lebnikOdnoklassniki").attr("title","Опубликовать в Одноклассниках");
	$("#lebnikGoogleBuzz").attr("title","Опубликовать в Google Buzz");
	$("#lebnikLivejournal").attr("title","Опубликовать в LiveJournal (ЖЖ)");
	// функция открывает новое виртуальное окно
	function ow20110204(w, h, path, window_name, no_scrollbars){
		var valLeft = (screen.width) ? (screen.width-w)/2 : 0;// отступ слева
		var valTop = (screen.height) ? (screen.height-h)/2 : 0;// отступ сверху
		var features = 'width='+w+',height='+h+',left='+valLeft+',top='+valTop+',';
		window.open(path, (window_name? window_name : 'name_up'), features+'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars='+(no_scrollbars?0:1)+',resizable=1');
	}
	$("#lebnikZakladkiYandex A, #lebnikTwitter A, #lebnikLivejournal A, #lebnikOdnoklassniki A").click(function(){
		var id = $(this).closest("LI").attr("id")
		ow20110204(800, 600, this.href, id);
		return false;
	});
});
