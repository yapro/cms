/*
Copyright (c) Lebnik http://yapro.ru/ 
$(".sortable").yaproTableHeadSort({HeightMinus:10, HeadRows:2, BottomRows:2, SortTD:"3A", syncWidthElements:true});, SortTD:"OFF"
*/
;(function($) {
	$.fn.extend({
		yaproTableHeadSort: function(options) {
			$(document.body).css("overflow","hidden");// скрываем скроллбар для окна
			if($.browser.msie && $.browser.version==7){ $("html").css("overflow","hidden"); }
			if(options && options.HeadRows){ var HeadRows = options.HeadRows; }else{ var HeadRows = 1; }// кол-во строк заголовка
			if(options && options.HeightTable){ var HeightTable = options.HeightTable; }// высота таблиц
			if(options && options.HeightMinus){ var HeightMinus = options.HeightMinus; }// высота таблиц - HeightMinus
			var allPaddingsLR = function(e){
				var padding = parseInt($(e).css("padding-left"),10) + parseInt($(e).css("padding-right"),10);
				$("*",e).each(function(){
					if(this.tagName=="P" || this.tagName=="DIV"){
						padding += parseInt($(this).css("padding-left"),10) + parseInt($(this).css("padding-right"),10);
					}
				});
				return padding;
			};
			var syncWidth = function(div, table, head, eBottom){// подстраиваем по ширине все ячейки таблицы
				
				var nothing = setTimeout(function(){
					
					var headTR = $("tr:last", head);// выбираем последнюю строку шапки
					var nameTD = $(headTR).children()[0].tagName;// нахожу имя тега шапки
					
					var tableTD = $("tr:first td", table);// выбираем первую строку таблицы
					
					var cellspacing = parseInt($(table).attr("cellspacing"),10);
					var cellspacingTable = ($.browser.msie && $.browser.version==8)? 0 : (cellspacing * 2);// если cellspacing=1, то у TABLE cellspacing=2px | в браузере IE8 cellspacing для таблицы не создает ширины
					
					var cellspacingTD = cellspacing * (tableTD.length - 1);// сумма: cellspacing * TD-1
					var border = parseInt($(table).attr("border"),10);
					var borderTable = border * 2;// если border=1, то у TABLE border=2px
					var borderTD = border * 2;// каждый border - 2px у каждого TD
					var borderTDSum = borderTD * tableTD.length;// сумма: количество border * 2px * TD
					
					var divScrollWidth = div[0].offsetWidth - div[0].clientWidth;
					
					var headTDLast = $(nameTD, headTR).length - 1;
					$(nameTD, headTR).each(function(i){
						var c = parseInt( $(this).attr("colSpan"), 10);// находим значение colSpan
						if(c){ $(this).data("colSpan", c).attr("colSpan", 1); }
						
						
						var w = 0;
						var width = $(this).attr("width");
						if(width && width.substr(width.length-1, 1)!="%"){
							w = parseInt(width, 10);
							if(i==headTDLast){ w += divScrollWidth; }
							$(this).removeAttr("width").css("width",w+"px");
						}
					});
					
					$(table).removeAttr("width").css("width", "");
					
					// Внимание: на элементы TBODY, TR ширина cellspacing и border не влияют
					
					var tableTDLast = tableTD.length - 1;
					var nextTD = w = cT = oW = wT = nextTDtemp = x = wX = c = wUsed = 0;// nextTD - номер элемента
					
					$(nameTD, headTR).each(function(i){// меняем ширину ячейкам основной таблицы по подобию ширины ячеек шапки
						
						w = this.offsetWidth;// находим ширину текущей ячейки - allPaddingsLR(this)
						
						c = $(this).data("colSpan");// находим значение colSpan
						
						if(c && c>1){
							
							nextTDtemp = nextTD;// создаем временный N элемента ячейки
							wUsed = 0;// используемое ячейками пространство
							for(var x=0;x<c;){
								
								cT = $(tableTD[ nextTDtemp ]).attr("colSpan");
								
								cT = cT? eval(cT) : 1;
								
								x += cT;
								
								wUsed += tableTD[ nextTDtemp ].offsetWidth;
								
								wUsed += cellspacing;
								
								nextTDtemp += cT;
								
							}
							
							if(i==headTDLast){ w -= divScrollWidth; }
							
							oW = w;
							
							// получаю незанимаемый размер - размер который можно добавить к любой ячейке ()
							wT = parseInt( ( (w - wUsed) / x), 10);// т.к. бывает запятая после целого числа, поэтому округляем в сторону уменьшения
							
							var widthSum = 0;
							for(var x=0;x<c;){
								
								cT = $(tableTD[nextTD]).attr("colSpan");
								
								cT = cT? eval(cT) : 1;
								
								x += cT;
								
								if(x>=c){
									w = oW;
								}else{
									w = tableTD[ nextTD ].offsetWidth + wT;// добавляем свободную ширину
									
								}
								
								$(tableTD[nextTD]).css("width", (w - allPaddingsLR(tableTD[nextTD]) )+"px");// - cellspacingMinus
								
								oW -= cellspacing;
								
								oW -= w;
								
								nextTD += cT;
							}
						}else{
							
							w -= allPaddingsLR(tableTD[nextTD]);
							
							if(nextTD == tableTDLast){ w -= divScrollWidth; }
							
							$(tableTD[nextTD]).css("width", w+"px");
							
							nextTD += 1;
						}
					});
					if(options && options.syncWidthElements){
						// выравниваем ширину элементов INPUT и SELECT
						var input = select = padding = e = null;
						var innerWidthTDs = new Array();
						$("tr", table).each(function(tr){
							if(tr==0){
								$("td", this).each(function(td){
									innerWidthTDs[td] = (parseInt( $(this).css("width"),10) - allPaddingsLR(this) + 7)+"px";// 7 - эксперементально
								});
							}
							syncWidthElements(innerWidthTDs, this);
						});
					}
					// выравниваем ячейки нижней таблицы
					if(eBottom){
						
						var eBottomTR = $("tr:last", eBottom);// находим последнюю строку
						nextTD = w = 0;// задаем начальную переменную
						
						var eBottomTDLast = $("TD", eBottomTR).length - 1;
						$("TD", eBottomTR).each(function(i){
							
							w = 0
							
							var c = parseInt( $(this).attr("colSpan"), 10);// находим значение colSpan
							
							if(c && c>1){
								
								// т.к. блочный элемент 1-н, то паддинг у него будет как у одного из colSpan блоков таблицы
								w -= allPaddingsLR( tableTD[nextTD] );
								
								for(var x=0;x<c;){
									
									w += tableTD[nextTD].offsetWidth + cellspacing;// находим ширину блочной ячейки
									
									cT = $(tableTD[nextTD]).attr("colSpan");
									
									cT = cT? eval(cT) : 1;
									
									x += cT;
									
									nextTD += cT;
								}
								
								w -= cellspacing;// ячеек всегда на 1 меньше
								
							}else{
								w = tableTD[nextTD].offsetWidth - allPaddingsLR( tableTD[nextTD] );// находим ширину текущей ячейки
								nextTD += 1;
							}
							
							if(i==eBottomTDLast){ w += divScrollWidth; }
							
							$(this).css("width", w+"px");// выставляем ширину ячейке
						});
					}
					if(options && options.syncWidthElements && eBottom){ syncWidthElements(innerWidthTDs, eBottomTR); }
				}, 10);
			};
			var syncWidthElements = function(innerWidthTDs, tr){// подстраиваем по ширине все ячейки таблицы
				$("td", tr).each(function(td){
					e = $(this).find("input")[0];
					if(e && e.tagName=="INPUT" && e.type=="text"){ $(e).css("width", innerWidthTDs[td]); }//
					if(!e){
						e = $(this).find("select")[0];
						if(e && e.tagName=="SELECT"){ $(e).css("width", innerWidthTDs[td]); }
					}
				});
			};
			var syncHeight = function(div_clone, HeightTable, HeightMinus, eBottom){// подстраиваем по ширине все ячейки таблицы
				
				var hDiv = $(div_clone).children()[0].offsetHeight;
				var offset = $(div_clone).offset();//
				var hBottom = eBottom? $(eBottom)[0].offsetHeight : 0;
				var hMinus = HeightMinus? HeightMinus : 0;
				var hw = $.browser.opera? window.innerHeight : $(window).height();
				
				if((offset.top + hDiv + hBottom + hMinus)>hw){
					$(div_clone).height(hw - offset.top - hBottom - hMinus);
				}else{
					$(div_clone).css("height", $(div_clone).children()[0].offsetHeight);
				}
				
			};
			var sortRows = function(e, trS, n, sortAsc){// сортируем строки таблицы
				trS.sort(function (trA,trB){
					var d = null;
					var rTime = /^([\d]{2}).([\d]{2}).([\d]{4}) ([\d]{2}):([\d]{2}):([\d]{2})$/i;// 30.09.2009 17:05:35  r = ["30.09.2009 17:05:35", "30", "09", "2009", "17", "05", "35"]
					var rByte = /(.+)Байт$/i;// 941 Байт 	113,34 КБайт 	113,34 MБайт 	113,34 ГБайт	["941 КБайт", "941 К"]
					var realByte = function(zzz){
						var m = 1;
					    var x = zzz[1].split(' ');
					    if(x[1]=='К'){ 
					        m = 1000;
					    }else if(x[1]=='М'){
					        m = 1000000;
					    }else if(x[1]=='Г'){
					        m = 1000000000;
					    }
					    return eval(x[0].replace(',', ".") * m);
					};
					
					var a = $("td", trA).eq(n);
					var at = $.trim( $(a).text() );// нахожу значение поля
					if(!at || at==""){ at = $.trim( $(a).find("input").val() ); }
					if(!at || at==""){ at = $.trim( $(a).find("select").val() ); }
					if(!at || at==""){ at = $("IMG", a).attr("src"); }
					if(at){
						at = at.toUpperCase();
						if(d = rTime.exec(at)){ at = eval( d[3]+d[2]+d[1]+d[4]+d[5]+d[6] ); }
						else if(d = rByte.exec(at)){ at = realByte(d); }
					}
					if(!at){ at=0; }
					
					var b = $("td", trB).eq(n);
					var bt = $.trim( $(b).text() );// нахожу значение следующего поля
					if(!bt || bt==""){ bt = $.trim( $(b).find("input").val() ); }
					if(!bt || bt==""){ bt = $.trim( $(b).find("select").val() ); }
					if(!bt || bt==""){ bt = $("IMG", b).attr("src"); }
					if(bt){
						bt = bt.toUpperCase();
						if(d = rTime.exec(bt)){ bt = eval( d[3]+d[2]+d[1]+d[4]+d[5]+d[6] ); }
						else if(d = rByte.exec(bt)){ bt = realByte(d);  }
					}
					if(!bt){ bt=0; }
					
					//$("#ZXC").html($("#ZXC").html() +"<br>"+ at +"___"+bt);
					var r = /^[\d]+$/i;// число
					if(at && at.toString().match(r)!=null && bt && bt.toString().match(r)!=null){// если оба значения числовые строки - делаем их числами
						at = parseInt(eval(at),10);
						bt = parseInt(eval(bt),10);
						if(sortAsc){
							return bt - at;
						}else{
							return at - bt;
						}
					}else{
						if(at==bt){ return 0; }// выполняю сравнение строк
						if((at<bt && !sortAsc)||(at>bt && sortAsc)){return -1;}
						if((at>bt && !sortAsc)||(at<bt && sortAsc)){return 1;}
					}
				});
				$.each(trS, function(index, row) {
					$(e).append(row);
				});
			};
			var cookieName = "yaproTableHeadSort"+MD5(location.href);
			var div = document.createElement("div");// создаю див
			var a = document.createElement("a");// создаю A
			var d = document.createElement("div");// создаю див
			$(document.body).append(div).append(a).append(d);// добавляю элементы
			$(div).css("overflow","auto");// добавляю ему свойство
			$(this).each(function(i){// просматриваем каждый элемент
				var e = this; var eBottom = null;// назначаю переменные
				var eHead = $(e).clone(true).addClass("yaproHead").addClass("yaproHead"+i);// делаю копию таблицы
				$("tr:gt("+(HeadRows-1)+")", eHead).remove();// удаляю все строки начиная с HeadRows
				$("tr:lt("+HeadRows+")", e).remove();// удаляю все строки до HeadRows
				$(e).wrap( $(div).clone(true) );// клонирую див и обромляю им таблицу
				var div_clone = $(e).parent();
				$(div_clone).before(eHead);// вставляю усеченную копию таблицы выше дива
				if(options && options.BottomRows){
					var eBottom = $(e).clone().addClass("yaproBottom").addClass("yaproBottom"+i);// делаю копию таблицы
					var numberTR = $("tr",e).length - options.BottomRows;
					$("tr:lt("+numberTR+")", eBottom).remove();// удаляю все строки до options.BottomRows
					$(div_clone).after(eBottom);// вставляю новую таблицу eBottom после дива
					$("tr:gt("+( numberTR - 1 )+")", e).remove();// удаляю все строки BottomRows с основной таблицы
				}
				// добавляю синхронизацию ширины полей шапки 1-ой строки и высоты таблиц текущей таблицы
				var nothing = setTimeout(function(){
					syncHeight(div_clone, HeightTable, HeightMinus, eBottom);
					var nothing = setTimeout(function(){ syncWidth(div_clone, e, eHead, eBottom); },100);
				},200);
				// выставляю сортировку при клике на шапку таблицы
				var trS = $("tr",e).get();// делаю все строки основной таблицы - массивом
				var sortTR = $("tr:last", eHead);
				var nameTD = $(sortTR).children()[0].tagName;// нахожу элемент ячейки
				$(sortTR).addClass("yaproSortTR");// добавляю класс
				$(a).css("display", "block");// добавляю ему свойства
				var colSpan = n = 0;
				$(nameTD, sortTR).each(function(){
					$(this).data("n", n);
					colSpan = $(this).attr("colSpan");
					n += colSpan? eval(colSpan) : 1;
				});
				$(nameTD, sortTR).click(function(){
					if(options && options.SortTD=="OFF"){return;}
					var n = $(this).data("n");// номер столбца
					var sortAsc = $(this).hasClass("sortAsc");// вид сортировки
					$(nameTD, sortTR).removeClass("sortAsc").removeClass("sortDesc");
					if(sortAsc){
						sortDesc = true;
						var cookie = "D";
						$(this).removeClass("sortAsc").addClass("sortDesc");
					}else{
						sortDesc = false;
						var cookie = "A";
						$(this).addClass("sortAsc").removeClass("sortDesc");
					}
					sortRows(e, trS, n, sortAsc);
					SetCookie(cookieName, n + cookie);
				}).each(function(){
					$(this).html( $(this).text() );
					$(this).wrapInner( $(d).clone() ).wrapInner( $(a).clone() );
					var c = $(this).children().children();
					$(c).css("padding",c[0].offsetTop+"px "+c[0].offsetLeft+"px");
					$(this).css("padding","0px");
				});
				if(options && options.SortTD && options.SortTD!="OFF"){// сортировка по-умолчанию
					var cookie = GetCookie(cookieName);
					if(cookie){ options.SortTD = cookie; }
					var n = parseInt(options.SortTD.substr(0, 1),10);
					if(options.SortTD.substr(1, 1)=="A"){
						var sortDesc = false;
						var sortClass = "Asc";
					}else{
						var sortDesc = true;
						var sortClass = "Desc";
					}
					$(nameTD+":eq("+n+")", sortTR).addClass("sort"+sortClass);
					sortRows(e, trS, n, sortDesc);
				}
				$(this).addClass("yaproTable").addClass("yaproTable"+i);
			});
			$(div).remove();
			$(a).remove();
			$(d).remove();
		}
	});
})(jQuery);
