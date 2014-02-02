/* плагин: путь к странице - Copyright (c) Lebnik http://yapro.ru/ */
var SHPath = {};
;(function($){
	$(document).ready(function(){
		
		var SHPathTimer = null;
		var SHPathSubMenuID = null;
		
		$('#SHPath A').mouseover(function(){
			
			clearInterval(SHPathTimer);// говорим: не скрывать подменю
			
			var id = $(this).attr("rel");// элемент SubMenu
			
			if(SHPathSubMenuID && SHPathSubMenuID!=id){// скрываем открытое под-меню, чтобы сразу далее показать новое меню
				SHPathSubMenuHide( SHPathSubMenuID );//'SHPathSubMenu'+
			}
			
			var xy = PosElement(this);// координаты элемента A
			
			var table = SHPath[id];
			
			if(typeof(table)=="undefined"){
				return false;
			}
			
			var div = $("DIV:first", table)[0];// внутренняя часть выпадающего меню
			
			var screenHeight = $.browser.opera? window.innerHeight : $(window).height();
			
			var maxTopHeight = xy.Top - $(document).scrollTop() - $(this).height();// высота от хлебн.крош. до области видимости сверху
			
			var maxBottomHeight = screenHeight + $(document).scrollTop() - xy.Bottom - 30;// 30 = 12(сверху)+0(снизу)+18(возможный скролл снизу)
			
			if(maxTopHeight > maxBottomHeight){// если сверху места больше чем снизу
				
				var topPosition = $(document).scrollTop();
				
				if(div.offsetHeight > maxTopHeight){
					
					div.style.height = maxTopHeight+"px";
					
				}else{
					
					topPosition = xy.Top - div.offsetHeight - $(this).height();
					
				}
				$(table).css("top", topPosition+"px");// выставляем координату Y
				
			}else{// если снизу больше места чем сверху
				
				if(div.offsetHeight > maxBottomHeight){
					div.style.height = maxBottomHeight+"px";
				}
				$(table).css("top", xy.Bottom+"px");// выставляем координату Y
				
			}
			
			$(table).css("left", (xy.Left-5)+"px");// выставляем координату Х-5
			
			div.style.width = "auto";
			var w = (div.offsetWidth + 40);
			var screenWidth = $.browser.opera? window.innerWidth : $(window).width();
			if( (xy.Left + w) > screenWidth){
				w = screenWidth - xy.Left - 40;
			}
			div.style.width = w+"px";
			
			SHPathSubMenuID = id;// назначаем временной переменной ссылку на объект
			
		}).mouseout(function(){// скрываем через 0,1 секунды
			var id = $(this).attr("rel");
			SHPathTimer = setTimeout(function(){
				SHPathSubMenuHide(id);
			}, 100);
		});
		function SHPathSubMenuHide(id){// скрываем подменю
			var table = SHPath[id];
			$(table).css("top","-100000px").css("left","-10000px");
			$("DIV:first", table).css("width","auto").css("height","auto");
		}
		$(".SHPathSubMenu").mouseover(function(){
			clearInterval(SHPathTimer);// останавливаем скрытие меню
		}).mouseout(function(){// скрываем через 0,1 секунды
			var id = $(this).attr("alt");
			SHPathTimer = setTimeout(function(){
				SHPathSubMenuHide(id);
			}, 100);
		});
	});
})(jQuery);
