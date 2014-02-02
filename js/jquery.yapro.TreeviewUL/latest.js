/* Copyright (c) Lebnik http://yapro.ru/ $(".yaproTable").yaproFieldsSize(); */
;(function($) {
	$.fn.extend({
		yaproTreeview: function(Mark,CookieName,CallbackFunction,MaxElements,AjaxPath,AjaxHTML){
			var Cookies = function(arr){
				this.CookieArray = arr;
			};
			var CookiesEdit = function(id, action){// в куках храним id которые нужно скрывать
				if(typeof(CookieName)!="undefined" && id){
					Cookie(CookieName, function(cookie_content){
						if(action=="hide"){// добавляем в куку
							cookie_content = cookie_content + id + ",";
						}else{// удаляем с куки
							var cookie_array = cookie_content.split(",");
							cookie_array = array_unique(cookie_array);
							cookie_content = "";
							for(var i=0, l=cookie_array.length; i<l; i++){
								if(cookie_array[i] && cookie_array[i]!=id){
									cookie_content += cookie_array[i]+",";
								}
							}
						}
						Cookie(CookieName, cookie_content);
					});
				}
			};
			var Hidden = function(li){// проверяем скрыт ли был элемент при прошлом просмотре данной страницы
				return this.CookieArray[ $(li).attr("id") ] ? true : false;
			};
			var Toggle = function(li, div, ul){
				var id = $(li).attr("id");
				var last = $(li).hasClass("last");
				
				if($(li).hasClass("pluse")){// если скрыт
					CookiesEdit(id, "show");
					$(li).removeClass("pluse").addClass("minus");
					$(ul).css("dsplay", "block");
				}else{
					CookiesEdit(id, "hide");
					$(li).removeClass("minus").addClass("pluse");
					$(ul).css("dsplay", "none");
				}
			};
			var UL = function(ul){
				
				var l = $(">li", ul).length-1;
						
				
				$(">li", ul).each(function(i){
					
					var classLI = $(this).attr("class");
					
					// если не имеет атрибута class или имеет class - latest который говорит о том, что это самый последний элемент
					if(typeof(classLI)=="undefined" || classLI=="" || classLI=="latest"){
						
						if(Mark){// если показывать ярлычок возле пункта
							var div = $('<div class="mark" />');
							$(this).prepend(div);
						}
						
						var li = this;// объявляем для использование внутри итераций each
						var ul = null;// объявляем для проверки на дочерние UL
						var ul_li = $(">ul li", li).length;// проверяем наличие дочерних UL LI
						
						$(li).addClass( Hidden(li)? "pluse" : "minus" );
						
						if(ul_li){
							var explorer = $('<div class="explorer">').click(function(){ Toggle(li, this, ul); });
							$(li).prepend(explorer);
						}
						if(i==l){
							$(this).addClass("last");// последний элемент LI
						}else if(!ul_li){
							$(this).addClass("noKids");
						}
						
						if(Mark){// если показывать ярлычок возле пункта
							
							// добавляем пробел впереди названия элемента
							if(ul_li){ var a = $("a:first", this); $(a).html("&nbsp;"+ $(a).html() ); }else{ $(this).addClass("file"); }
							
							// если Ajax И номер элемента больше заданного кол-ва И последний, но не самый последний
							if(AjaxPath && MaxElements && i>=(MaxElements-1) && $(this).hasClass("last") && !$(this).hasClass("latest")){
								
								//minus last file
								var nextElements = $('<li alt="'+$(li).attr("alt")+'" class="last"><div class="nextElements"></div><a href="#">&nbsp;Следующие '+MaxElements+'</a></li>').click(function(){
									
									var id = $(li).attr("id")
									var alt = $(li).attr("alt").split(".");
									
									if(id){
										
										var next = this;
										$("DIV:first", next).removeClass("nextElements").addClass("ajaxElements");
										
										$.post(AjaxPath, {ajax:true, loadTreeview:true, id:id, start:alt[2]}, function(msg){
											$(next).remove();// Удаляем Следующие Х
											$("#"+id).yaproTreeview(Mark, CookieName, CallbackFunction, MaxElements, AjaxPath, msg);
										});
									}
									return false;
								});
								
								$(li).removeClass("last").after( nextElements );
								if(!ul_li){
									$(li).addClass("noKids");
								}
							}
						}
						/* cпец. хак который используется если не применять .treeview li a { display:block; margin-top: 1px; margin-bottom:-1px } */
						if(i==0 && $.browser.msie && $.browser.version<8){// если первый элемент
							var parents = 1;
							var parents_li = 0;
							$(this).parents().each(function(){
								if(this.tagName=="LI"){
									parents_li += 1;
									if( $(this).data("fixIEmargin") ){ parents += 1; }
								}
							});
							var d = $("div:first", this);
							if(d[0] && parents_li>0){
								$(li).data("fixIEmargin",1);
								$(d).attr("title", parents_li).css("margin-left", ( parseInt( $(d).css("margin-left"), 10) - (parents * 2) ) +"px");
							}
						}
						$(">ul", this).each(function(){ UL(this); });
					}
				});
			};
			var addAjax = function(li, a){// проверяем скрыт ли был элемент при прошлом просмотре данной страницы
				if(li && a){
					$(a).attr("href","#").unbind("click").click(function(){
						Ajax(li); return false;
					});
				}
			};
			var Callback = function(ul){// проверяем скрыт ли был элемент при прошлом просмотре данной страницы
				if(ul && CallbackFunction){
					eval(CallbackFunction+"(ul)");
				}
			};
			var Ajax = function(li){// проверяем скрыт ли был элемент при прошлом просмотре данной страницы
				var id = $(li).attr("id");
				var alt = $(li).attr("alt").split(".");
				if(id){
					$("div:first", li).removeClass("nextElements").addClass("ajaxElements");
					var eval_str = '$("#'+id+'").yaproTreeview('+Mark+',"'+CookieName+'","'+CallbackFunction+'",'+MaxElements+',"'+AjaxPath+'",msg)';
					AjaxForm(AjaxPath, '&loadTreeview=true&id='+id+'&start='+alt[2], eval_str);
				}
			};
			var Start = function(ul, CookieArray){// проверяем скрыт ли был элемент при прошлом просмотре данной страницы
				Cookies(CookieArray);
				UL(ul);
				Callback(ul);
				$(ul).show();
			};
			if(AjaxHTML){
				var ul = firstParent(this, "UL");
				$(this).after(AjaxHTML);
				UL(ul);
				Callback(ul);
			}else{
				var ul = this;
				$(ul).hide();
				$(ul).addClass("treeview");
				if(Mark){ $(ul).addClass("Mark"); }
				CookieArray = new Array();
				if(typeof(CookieName)!="undefined"){
					Cookie(CookieName, function(cookie_content){
						if(cookie_content && cookie_content!=""){
							var cookie_array = cookie_content.split(",");
							cookie_array = array_unique(cookie_array);
							if(cookie_array.length<1){ return false; }
							for(var i=0, l=cookie_array.length; i<l; i++){
								CookieArray[ cookie_array[i] ] = true;
							}
						}
						Start(ul, CookieArray);
					});
				}else{
					Start(ul, CookieArray);
				}
			}
		}
	});
})(jQuery);
