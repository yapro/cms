/* Copyright (c) Lebnik http://yapro.ru/ $(".yaproTable").yaproFieldsSize(); */
;(function($) {
	$.fn.extend({
		yaproFieldsSize: function(options){
			var allPaddingsLR = function(e){
				var padding = parseInt($(e).css("padding-left"),10) + parseInt($(e).css("padding-right"),10);
				$("*",e).each(function(){
					if(this.tagName=="P" || this.tagName=="DIV"){
						padding += parseInt($(this).css("padding-left"),10) + parseInt($(this).css("padding-right"),10);
					}
				});
				return padding;
			};
			$(this).each(function(i){// просматриваем таблицу
				
				var input = select = padding = e = null;
				var innerWidthTDs = new Array();
				var firstTR = $("tr:first", this);//каждый элемент
				
				$("tr:first", this).each(function(tr){
					
					$("td", this).each(function(td){
						
						if(tr==0){
							innerWidthTDs[td] = (this.offsetWidth - allPaddingsLR(this) )+"px";
						}
						alert(innerWidthTDs[td]);
						e = $(this).find("input")[0];
						if(e && e.tagName=="INPUT"){ $(e).css("width", innerWidthTDs[td]); }
						
						if(!e){
							e = $(this).find("select")[0];
							if(e && e.tagName=="INPUT"){ $(e).css("width", innerWidthTDs[td]); }
						}
						if(e){ $("#zzz").text( $("#zzz").text() +" | "+ e.tagName +" - "+ this.offsetWidth +" - "+ allPaddingsLR(this) +" = "+ innerWidthTDs[td]); }
					});
					
				});
			});
		}
	});
})(jQuery);
