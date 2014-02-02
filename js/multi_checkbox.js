/*
яндекс почта выбор нескольких checkbox 
jquery выбор нескольких checkbox с помощью мыши как в яндекс почта
jquery mousedown mouseup select many checkbox
jquery mousedown mousemove many checkbox
jquery выбор курсором множества checkbox
jquery выбор курсором множества checkbox
jquery выбор множества checkbox
juery mouse multiselect checkbox 

Info:
	скрипт дает возможность выбирать множество чекбоксов с помощью мыши
CopyRight by:
	Lebedenko Nikolay Nikolayevich (Lebnik)
How use:
	$("TABLE").lebnik_multi_checkbox();
Contact with me:
	http://yapro.ru/
	http://vkontakte.ru/club23074103
	http://www.facebook.com/pages/YaPro-CMS/184609554891388
	http://twitter.com/lebnik
	http://yalebnik.livejournal.com/
	http://odnoklassniki.ru/group/50396187918481
	http://totx.ya.ru/
	http://lebnik.habrahabr.ru/
*/
;(function($){
	
	$.fn.lebnik_multi_checkbox = function(){
		
		var lebnik_multi_checkbox_allow_check = false;
		var lebnik_multi_checkbox_y = 0;
		var lebnik_multi_checkbox_trand = "";
		var lebnik_multi_checkbox_course = "down";
		var lebnik_multi_checkbox_start_tr = null;
		var lebnik_multi_checkbox_checked = false;// информация о первом действии над первым checkbox-элементом 
		$(this).mouseenter(function(e){
			lebnik_multi_checkbox_allow_check = false;
			lebnik_multi_checkbox_trand = "";
			$(":checkbox").removeClass("lebnik_multi_checkbox_start_tr");
		});
		function checked_tr(tr, checked){
			var start = false;
			var e_checkbox = $(":checkbox", tr)[0];
			$(tr).closest("TABLE").find(":checkbox").each(function(){
				if(lebnik_multi_checkbox_course == "down"){
					if($(this).hasClass("lebnik_multi_checkbox_start_tr")){
						start = true;
					}
					if(start){
						$(this).attr("checked", checked);
					}
					if(this == e_checkbox){
						start = false;
					}
				}else{
					if(this == e_checkbox){
						start = true;
					}
					if(start){
						$(this).attr("checked", checked);
					}
					if($(this).hasClass("lebnik_multi_checkbox_start_tr")){
						start = false;
					}
				}
			});
		}
		$("TR", this).mouseenter(function(e){
			lebnik_multi_checkbox_course = (e.pageY < lebnik_multi_checkbox_y)? "up" : "down";
			if(lebnik_multi_checkbox_allow_check){
				if(lebnik_multi_checkbox_trand == ""){
					lebnik_multi_checkbox_trand = (e.pageY < lebnik_multi_checkbox_y)? "up" : "down";
				}
				var c = $(":checkbox", this);
				if(lebnik_multi_checkbox_trand == lebnik_multi_checkbox_course){
					if(lebnik_multi_checkbox_checked == true){
						checked_tr(this, true);
					}else{
						checked_tr(this, false);
					}
				}else{
					if(lebnik_multi_checkbox_checked == true){
						checked_tr(this, false);
					}else{
						checked_tr(this, true);
					}
				}
			}
			lebnik_multi_checkbox_y = e.pageY;
		});
		$(":checkbox", this).mousedown(function(){
			lebnik_multi_checkbox_allow_check = true;
			if( $(this).attr("checked") == true ){
				$(this).attr("checked", false);
				lebnik_multi_checkbox_checked = false;
			}else{
				$(this).attr("checked", true);
				lebnik_multi_checkbox_checked = true;
			}
			start_checkbox = $(this).attr("class", "lebnik_multi_checkbox_start_tr");
		}).mouseup(function(){
			if($(this).hasClass("lebnik_multi_checkbox_start_tr")){
				if( $(this).attr("checked") == true ){
					$(this).attr("checked", false);
				}else{
					$(this).attr("checked", true);
				}
			}
		});
		$(document).mouseup(function(){
			lebnik_multi_checkbox_allow_check = false;
			lebnik_multi_checkbox_trand = "";
			$(":checkbox").removeClass("lebnik_multi_checkbox_start_tr");
		});
	};
})(jQuery);