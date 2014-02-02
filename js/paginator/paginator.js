/*
Copyright (c) Lebnik Пример выводить по 15 страниц: $(document).ready(function(){ pag = new Paginator('.Listing', 16); });
*/
function getPageX( oElement ) {
	var iPosX = oElement.offsetLeft;
	while ( oElement.offsetParent != null ) {
		oElement = oElement.offsetParent;
		iPosX += oElement.offsetLeft;
		if (oElement.tagName == 'BODY') break;
	}
	return iPosX;
}

function getMousePosition(e) {
	if (e.pageX || e.pageY){
		var posX = e.pageX;
		var posY = e.pageY;
	}else if (e.clientX || e.clientY) 	{
		var posX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
		var posY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	}
	return {x:posX, y:posY}	
}

Paginator = function(e, pagesSpan){
	
	if(!e){ return false; }
	var alt = $(e).attr("alt");
	if(!alt){ return false; }
	var ex = alt.split("~K~");
	this.page = ex[3];// урл-страницы
	this.folio = ~~ex[2];// выбранная страница
	this.index = ~~ex[1];// 1 значит индексная страница
	this.pages = pagesSpan;// указанное кол-во страниц
	this.htmlBox = e;
	
	if(!this.htmlBox || !pagesSpan) return;
	
	$('.inThePresent',this.htmlBox).remove();
	$('.inThePast',this.htmlBox).remove();
	
	$(this.htmlBox).addClass("paginator");
	
	this.pagesTable;
	this.pagesTr; 
	this.sliderTr;
	this.pagesCells;
	this.slider;
	
	// создаем атрибут клонируя объекты, т.к. обращаясь к данному атрибуту ф-ии, значение атрибута снова проверяется и обновляется
	this.span = $('span',this.htmlBox).clone();
	
	this.pagesTotal = pagesTotal = ~~ex[0];// всего страниц
	
	if(pagesSpan < pagesTotal){
		this.pagesSpan = pagesSpan;
	} else {
		this.pagesSpan = pagesTotal;
		$(this.htmlBox).addClass('fullsize');
	}
	this.currentPage = $('em',this.htmlBox).text();
	this.firstCellValue;
	this.initPages();
	this.initSlider();
	this.drawPages();
	this.initEvents();

	this.scrollToCurrentPage();
	this.setCurrentPagePoint();
};

Paginator.prototype.initPages = function(){
	var html = "<table align=center><tr>";
	for (var i=1; i<=this.pagesSpan; i++){
		html += "<td></td>"
	}
	
	html += "</tr>" +
	"<tr><td colspan='" + this.pagesSpan + "'>" +
	"<div class='scrollbar'>" + 
		"<div class='line'></div>" + 
		"<div class='current_page_point'></div>" + 
		"<div class='slider'>" + 
			"<div class='slider_point'></div>" + 
		"</div>" + 
	"</div>" +
	"</td></tr></table>";

	$(this.htmlBox).html(html);// заменяем стандартный HTML на таблицу Pagеr-а
	
	this.pagesTable = $('table',this.htmlBox)[0];// находим таблицу Pagеr-а
	this.pagesTr = $('tr',this.pagesTable)[0];// находим 1-ю строку Pagеr-а
	this.sliderTr = $('tr',this.pagesTable)[1];// находим 2-ю строку Pagеr-а
	this.pagesCells = $('td',this.pagesTr);// находим все TD 1-й строки Pagеr-а
	
	this.scrollbar = $('div.scrollbar',this.pagesTable)[0];// находим DIV scrollbar-a
	this.slider = $('div.slider',this.pagesTable)[0];// находим DIV управления scrollbar-ом
	this.currentPagePoint = $('div.current_page_point',this.pagesTable)[0];// находим DIV задающий позицию в scrollbar-е
};

Paginator.prototype.initSlider = function(){
	this.slider.xPos = 0;// задаем нанчальную позицию DIV-а управления scrollbar-ом
	this.slider.style.width = this.pagesSpan/this.pagesTotal * 100 + "%";// выставляем ширину DIV-у управления scrollbar-ом
};

Paginator.prototype.initEvents = function(){
	var _this = this;

	this.slider.onmousedown = function(e){
		if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();

		this.dx = getMousePosition(e).x - this.xPos;
		document.onmousemove = function(e){
			if (!e) var e = window.event;
			_this.slider.xPos = getMousePosition(e).x - _this.slider.dx;
			_this.setSlider();
			_this.drawPages();
		}
		document.onmouseup = function(){
			document.onmousemove = null;
			_this.enableSelection();
		}
		_this.disableSelection();
	}
//getMousePosition(e) {x:posX, y:posY}	

	this.scrollbar.onmousedown = function(e){
		if($(_this.paginatorBox).hasClass('fullsize')){ return; }
		
		if (!e) var e = window.event;
		_this.slider.xPos = getMousePosition(e).x - getPageX(_this.scrollbar) - _this.slider.offsetWidth/2;
		_this.setSlider();
		_this.drawPages();
	}
	$(window).resize(function(){
			if(typeof pag == 'undefined') return;
			pag.setCurrentPagePoint();
			pag.scrollToCurrentPage();
	});
};

Paginator.prototype.setSlider = function(){
	this.slider.style.left = this.slider.xPos + "px";
};

Paginator.prototype.drawPages = function(){
	
	var percentFromLeft = this.slider.xPos/(this.pagesTable.offsetWidth);// нахожу процентное положение DIV-а управления scrollbar-ом
	this.firstCellValue = Math.round(percentFromLeft * this.pagesTotal);// выставляю значение первому TD 1-й строки Pagеr-а
	var html = "";
	if(this.firstCellValue < 1){
		this.firstCellValue = 1;
		this.slider.xPos = 0;
		this.setSlider();
	} else if(this.firstCellValue >= this.pagesTotal - this.pagesSpan) {
		this.firstCellValue = this.pagesTotal - this.pagesSpan + 1;
		this.slider.xPos = this.pagesTable.offsetWidth - this.slider.offsetWidth;
		this.setSlider();
	}
	// прохожу по TD-элементам
	for(var i=0; i<this.pagesCells.length; i++){
		
		var currentCellValue = this.firstCellValue + i;
		
		var prefixLength = String(this.pagesTotal).length - String(currentCellValue).length;
		
		var prefix = this.makePrefix(prefixLength);
		
		if(currentCellValue>1){
			var repl = (currentCellValue-1);
		}else{
			var repl = "";
		}
		
		if(currentCellValue==(this.folio+1)){
			var l = '<em>';
			var r = '</em>';
		}else{
			if(repl){// если ссылка не на первую страницу
				var l = '<a href="'+this.page.replace("~PagerNumber~", repl)+'">';
			}else{// если ссылка на первую страницу
				var l = '<a href="'+(this.index? "/" : this.page.replace("&p=~PagerNumber~", "").replace("?p=~PagerNumber~", "") )+'">';
				l = l.replace("&folio=~PagerNumber~", "");// для страницы поиска
			}
			
			var r = '</a>';
		}
		
		this.pagesCells[i].innerHTML = '<span>' + l + currentCellValue + r + String(prefix) + '</span>';
		
	}
	$(".paginator table").css("margin","auto").attr("align","center");
	//-log(this.pagesTotal+' < '+this.pages);
	if(this.pagesTotal < this.pages){
		$(".paginator table").css("width","1px");//auto
	}
};

Paginator.prototype.scrollToCurrentPage = function(){
	this.slider.xPos = (this.currentPage - Math.round(this.pagesSpan/2))/this.pagesTotal * this.pagesTable.offsetWidth;
	this.setSlider();
	this.drawPages();
};

Paginator.prototype.setCurrentPagePoint = function(){
	if(this.currentPage == 1){
		this.currentPagePoint.style.left = 0;
	} else {
		this.currentPagePoint.style.left = this.currentPage/this.pagesTotal * this.pagesTable.offsetWidth + "px";
	}
};

Paginator.prototype.makePrefix = function(prefixLength){
	var prefix = "";
	for (var i=0; i<prefixLength; i++){
		prefix += "_";
	}
	return prefix;
};

Paginator.prototype.disableSelection = function(){
	document.onselectstart = function(){
		return false;
	}
	this.slider.focus();
};

Paginator.prototype.enableSelection = function(){
	document.onselectstart = function(){
		return true;
	}
	this.slider.blur();
};
