function l(x){
	if(window.console && console.log){console.log(x);}
}
// функция открывает новое виртуальное окно
var openwindow_ = undefined;
function OpenWindow(w, h, path, window_name, no_scrollbars){
	var valLeft = (screen.width) ? (screen.width-w)/2 : 0;// отступ слева
	var valTop = (screen.height) ? (screen.height-h)/2 : 0;// отступ сверху
	var features = 'width='+w+',height='+h+',left='+valLeft+',top='+valTop+',';
	if (typeof(openwindow_)!="undefined"){ openwindow_.close(); /*openwindow.focus();*/ }
	var window_name = (typeof(window_name)=="undefined")? 'name_up' : window_name;
	var no_scrollbars = (typeof(no_scrollbars)=="undefined")? 1 : 0;
	openwindow_ = window.open(path, window_name.replace(".",""), features+'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars='+no_scrollbars+',resizable=1');
	return openwindow_;
}
jQuery.fn.allPaddingsLR = function(){
	var padding = parseInt($(this).css("padding-left"),10) + parseInt($(this).css("padding-right"),10);
	$("*",this).each(function(){
		if(this.tagName=="P" || this.tagName=="DIV"){
			padding += parseInt($(this).css("padding-left"),10) + parseInt($(this).css("padding-right"),10);
		}
	});
	return padding;
};
function scrollbarWidth(){// возвращает ширину скроллбара для текущего браузера
	var e = $('<div style="overflow:scroll" />');
	$(document.body).append( e );
	var w = e[0].offsetWidth - e[0].clientWidth;
	$(e).remove();
	return w;
}

// переменные и функция под tiny_mce
var openBrowserPath = null;
var openBrowserSite = null;
var system_target_form_element = null;
var system_openBrowserFiles = null;
function openBrowser(id){//'backgroundimagebrowser','backgroundimage', 'image','table_image_browser_callback'
	system_target_form_element = id;
	if(system_openBrowserFiles && system_openBrowserFiles.document){
		system_openBrowserFiles.focus();
	}else{
		system_openBrowserFiles = OpenWindow("770","570",openBrowserPath+"?insert=1");
	}
}

// удаляет из массива дублирующиеся значения
function array_unique(arr){
	var vic = new Object();
	for(i=0; i < arr.length; i++){ vic[arr[i]] = ""; }
	arr = new Array();
	for(i in vic){ arr[arr.length] = i; }
	return arr;
}

// функция определения значения определенной куки
function GetCookie(name){
    var cookie_pair;
    var cookie_name;
    var cookie_value;
    //--Разбиваем куку в массив
    var cookie_array = document.cookie.split('; ')
    //--Пробегаем по массиву кук
    for (var counter = 0; counter < cookie_array.length; counter++) {
            //--Разбиваю куку на имя/значение
        cookie_pair = cookie_array[counter].split('=');
        cookie_name = cookie_pair[0];
        cookie_value = cookie_pair[1];
        //--Сравниваем имя куки с тем именем, которое нужно нам
        if (cookie_name == name) {
                 //--Если нашли нужную нам куку, то возвращаем её значение
            return cookie_value;
        }
    }
    //--Если куку не нашли, возвращаем null
    return "";
}

// функция определения значения определенной куки
function SetCookie(name, value){
    document.cookie = name+'='+value+'; path=/; expires=Friday, 15-May-2015 10:45:30 GMT';
}

// проверка на удаление
var confirm_message = 'Подтверждаете удаление?';
function checkDel(a,msg){
	jConfirm( (msg? msg : confirm_message), function(r) {
		if(r==true){
			document.location.href = a.href;
		}
	});
	return false;
}

var url = null;
var path = null;
// удаление директории или файла
function onDelete(e, name){
	if(url && e && name && checkDel()){
		$.ajax({
			type: "POST",
			url: url,
			data: "delete="+name,
			success: function(msg){
				if(msg=="ok"){ e.parentNode.parentNode.style.display = "none"; }else{ alert(msg); }
		   }
		});
	}
}
// Инсерт ссылки
function onInsert(name){
	if(url && path && name && window.opener && window.opener.openBrowserSite && window.opener.system_target_form_element){
		var temp_path = window.opener.openBrowserSite + path + name;
		var temp_e = window.opener.document.getElementById(window.opener.system_target_form_element);
		temp_e.value = temp_path;
		if(temp_e.onchange){ temp_e.onchange(); }
		window.opener.focus();
		window.close();
	}
}


// скрытие или показ объекта
function SH(id){
	if($("#"+id).css("display")=="none"){
		$("#"+id).fadeIn("slow");
	}else{
		$("#"+id).fadeOut("slow");
	}
}

// раскраска по строкам
function mouseMovements(id) {
	var bgn=0;
	color_onmouseover = "ClassOnMouseOver";
	color_onclick = "ClassOnClick";
	if(typeof(id)=="string" && document.getElementById(id)){
		var tr = document.getElementById(id).getElementsByTagName('TR');
	}else if(typeof(id)=="object"){
		var tr = id.getElementsByTagName('TR');
	}else{
		return false;
	}
	for(i=0;i<tr.length;i++){
		if(tr[i].className==''){
			class_color = (bgn%2)?'LightBlue':'LightGrey'; bgn++;
			tr[i].className = class_color;
			
			(function(o_color) { 
				tr[i].onmouseover = function(){
					if(this.className != color_onclick){this.className = color_onmouseover;}
				} 
			})(class_color);
			
			(function(o_color) { 
				tr[i].onmouseout = function(){
					   if(this.className != color_onclick){this.className = o_color;}
				} 
			})(class_color);
			
			(function(o_color) { // передаем значение переменной class_color, как значение переменной o_color
				tr[i].onmousedown = function(){
					if(this.className!=color_onclick){this.className = color_onclick;}
					else{this.className = o_color;}
					} 
			})(class_color);
		}
	}
}

// применение mouseMovements ко всем дочерним таблицам класса classname
function mouseMovementsClass(classname){
	var t = document.getElementsByTagName("TABLE");
	for(var i=0; i<t.length; i++){
		if(t[i].className==classname){
			mouseMovements(t[i]);
		}
	}
}

// Специальная функция-хак под IE 7 и выше, которая помогает снять выделение с flash - объектов
function SystemWrite(str){
	document.write(str);
}

// обрезает пробелы в начале и в конце строки
function trim(s){
	while (s.substring(0,1) == ' ') {
		s = s.substring(1,s.length);
	}
	while (s.substring(s.length-1,s.length) == ' ') {
		s = s.substring(0,s.length-1);
	}
	return s;
}

// Проверка полей
function CheckField(form, type_check, field_name, message){// Примечание: регулярки отдают true если срабатывает условие, и null в обратном случае
	
	// регулярка на мыло
	var e = /^([\w\.\-]+)@([a-z0-9\-]+)\.([a-z0-9\-\.]+)$/i;// условие: обязательное сопостовление указанной регулярке
	
	// регулярка на цирфру
	var i = /[0-9\-\(\)\s]+/i;// условие: обязательное присутствие хотябы 1 цифры
	
	// регулярка на букву
	var t = /[a-zа-я\-\.\s]+/i;//--условие: присутствие хотябы 1 буквы
	
	// регулярка на пробел
	var b = /\s+/;//--условие: присутсвие хотябы 1 пробела, включая space, tab, form feed, line feed. Эквивалентно [ \f\n\r\t\v].
	
	obj = form[field_name];
	
	if(obj!=null && typeof(obj)!='undefined'){
		
		obj_value = trim(obj.value);
		
		if(obj_value.match(eval(type_check))==null || obj_value==''){
			
			obj.value = obj_value;
			obj.style.border="#FF0000 1px solid";
			obj.focus();
			return "- Вы не указали "+message+". Исправьте, пожалуйста!\n";
			
		}else{
			
			obj.value = obj_value;
			obj.style.border='#cccccc 1px solid';
			return "";
			
		}
		
	}else{
		alert("- Поле с name: "+field_name+" не найдено\n");
		return "?";
	}
}

// detect браузера
function browsers(){
	this.ver   = navigator.appVersion.toLowerCase();
	this.agent = ''+navigator.userAgent.toLowerCase();
	this.dom   = document.getElementById ? 1 : 0;
	this.opera = (this.agent.indexOf("opera")>-1 && this.dom) ? 1 : 0;
	this.ie    = (this.ver.indexOf("msie") > -1 && this.dom && !this.opera) ? 1 : 0;
	this.macOS = this.agent.indexOf("mac") > -1;
	this.mac   = (this.macOS && parseInt(this.ver) >= 7) ? 1 : 0;
	this.moz   = (this.agent.indexOf("gecko")>-1)
	this.ns6   = (this.dom && this.agent.indexOf("netscape")>-1 && parseInt(this.ver) >= 5) ? 1 : 0;
	//this.safari = (hnav.search(/macintosh/gmi)!=-1&&hnav.search(/safari/gmi)!=-1);
	//this.opera7 = (this.opera && parseInt(navigator.appVersion) >= 7);
	return this;
}
browser = new browsers();
// функция сортировки строк таблицы - onclick="sortTableTR(document.getElementById('sortTableTR'));"
function sortTableTR(el){
	var col_sort = el.innerHTML;
	var tr = el.parentNode;
	var table = tr.parentNode;
	var td, arrow, col_sort_num;
	
	for (var i=0; (td = tr.getElementsByTagName("td").item(i)); i++){
   		if(td.innerHTML == col_sort){
            col_sort_num = i; 
            if (td.prevsort == "y"){
                //arrow = td.firstChild;
                el.up = Number(!el.up);
            }else{
                td.prevsort = "y";
                //arrow = td.insertBefore(document.createElement("span"),td.firstChild);
                el.up = 0;
            }
            //arrow.innerHTML = el.up?"↑ ":"↓ ";
        }else{
            if (td.prevsort == "y"){
                td.prevsort = "n";
                if (td.firstChild){ td.removeChild(td.firstChild); }
            }
        }
    }
	
	var a = new Array();
	
    for(i=1; i < table.rows.length; i++){
	 	a[i-1] = new Array();
	 	a[i-1][0]=table.rows[i].getElementsByTagName("td").item(col_sort_num).innerText;
		a[i-1][1]=table.rows[i];
	}
	
	a.sort();
	if(el.up) a.reverse();	 
	
	for(i=0; i < a.length; i++)
	table.appendChild(a[i][1]);
}
// Функции вывода описания ввода, пример: onFocus="foc(this)" onBlur="blu(this)" value="описание"
function foc(o){
	if(o.alt=='' || typeof(o.alt)=='undefined'){ o.alt=o.value; }
	if(o.value==o.alt){ o.value=''; }
}
function blu(o){
	if(o.value==''){o.value=o.alt;}
}
// делает все формы Ajax-формами
function AllFormsAjax(id){
	if(typeof(id)=="string" && document.getElementById(id)){
		var all_forms = document.getElementById(id).getElementsByTagName('FORM');
	}else if(typeof(id)=="object"){
		var tr = id.getElementsByTagName('FORM');
	}else{
		var all_forms = document.getElementsByTagName('FORM');
	}
	$(all_forms).submit(function(){
		AjaxForm(this);
		return false;
	});
}
// сделать стартовой для браузера IE. Пример: <a href="/sethomepage.php" class="SetStartPage">Сделать стартовой</a>
if (window.attachEvent){
	$(document).ready(function(){
		$(".SetStartPage").each(function(){
			$(this).click(function(){
				$(this).css('behavior', 'url(#default#homepage)');
				this.setHomePage('http://' +window.location.host+'/');
				return false;
			});
		});
	});
}
// выводит FLV-плееер и предлагает проиграть файл. Пример: <script type="text/javascript">PlayerFLV(file_url,w,h,autostart);</script>
function PlayerFLV(file_url,w,h,autostart){
	if(typeof(w)!="string" || typeof(h)!="string"){	var w = 700; var h = 550; }
	if(typeof(autostart)!='undefined'){ var autostart = 'true'; }else{ var autostart = 'false'; }
	SystemWrite('<embed width="'+w+'" height="'+h+'" flashvars="file=http://urokiflash.alfamoon.com/flash8-urok02-01mono.flv&amp;searchbar=false&amp;showdownload=true&amp;autostart='+autostart+'" allowfullscreen="true" allowscriptaccess="always" src="http://' +window.location.host+'/images/swf/mediaplayer.swf"/>');
	/*
	<embed height="333" width="410" flashvars="height=333&amp;width=410&amp;file=http://media.tehpron.com/tehpron/doggama_fekzcuch.flv&amp;displayheight=333&amp;displaywidth=410&amp;recommendations=http://www.tehpron.com/view-content/recommendations.xml&amp;link=http://www.tehpron.com&amp;searchbar=false&amp;showdownload=true&amp;autostart=true" allowfullscreen="true" allowscriptaccess="always" src="http://www.tehpron.com/view-content/mediaplayer.swf"/>
	*/
}

// возвращает массив с элементами в обратном порядке (сохраняя ключи, если preserve_keys имеет значение TRUE)
function array_reverse(array, preserve_keys){
	
    var arr_len=array.length, newkey=0, tmp_ar = {}
	
    for(var key in array){
        newkey=arr_len-key-1;
        tmp_ar[(!!preserve_keys)?newkey:key]=array[newkey];
    }
    return tmp_ar;
}

// собирает в строку все данные определенной формы, чтобы затем передать их в Ajax-запросе
function AjaxData(form){
	var form_data = "";
	if(form){
		// [ специальных хак под JQuery
		var id = "";
		id = $(form).attr("id");
		if(id==""){ 
			id = "id"+Math.round(Math.random() * 1000000000);
			$(form).attr("id", id);
		}
		// специальных хак под JQuery ]
		var Elements = $("#"+id+" * ");
		for(var i=0; i<Elements.length; i++){
			var name = $(Elements[i]).attr("name");
			if(name){
				var value = $(Elements[i]).val();
				if(value && name!="" && value!="" && (Elements[i].tagName=="INPUT" || Elements[i].tagName=="TEXTAREA" || Elements[i].tagName=="SELECT") ){
					var type = $(Elements[i]).attr("type");
					if(Elements[i].tagName=="INPUT" && (type=="radio" || type=="checkbox") && !$(Elements[i]).attr("checked")){
						form_data = form_data+"&"+ name +"=";// такого в jquery нет
					}else if(Elements[i].tagName=="SELECT" && $(Elements[i]).attr("multiple")){
						$("option", Elements[i]).each(function(){
							if($(this).attr("selected")){
								form_data = form_data+"&"+ name +"="+ $(this).val();
							}
						});
					}else{
						form_data = form_data+"&"+ name +"="+ value.replace(/%/g,"%25").replace(/&/g,"%26").replace(/\+/g,'%2B');
					}
				}
			}
		}
	}
	return form_data;
}

// отправляем все данные определенной формы в Ajax-запросе. Пример: onsubmit="return AjaxForm('/inner.php', '&x=1', 'my_function(form)');"
function AjaxForm(e, form_data, eval_data){
	if(typeof(e) == "object"){ action = e.action; }else if(typeof(e) == "string"){ action = e; }else{ return false; }
	if(action && form_data!=""){
		$.ajax({
			type: "POST",
			url: action,
			data: "ajax=1"+form_data,
			success: function(msg){
				if(typeof(eval_data)=="string"){
					eval(eval_data);
				}else{
					if(msg){ alert(msg); }
				}
		 	}
		});
	}
	return false;
}

// определение позиции элемента
function PosElement(e){
	if(typeof(e) != "object") { e = document.getElementById(e); }
	if(e==null || typeof(e) != "object") { return false; }
	var ww = e.offsetWidth, hh = e.offsetHeight;
	for (var xx = 0,yy = 0; e != null; xx += e.offsetLeft,yy += e.offsetTop,e = e.offsetParent);
	return {Left:xx, Top:yy, Right:xx + ww, Bottom:yy + hh}// левый, верхний, правый, нижний
}
function pos(e){ return PosElement(e); }

// запоминаем CSS-правила используемые по-умолчанию для элементов INPUT
function fieldsValidCSS(form, set_default){
	if(set_default && $(form).data("setdefault")){ set_default = false; }// проверка на многократное использование setdefault для всех полей формы
	$("input:text", form).each(function(){
		fieldValidCSS(this, set_default);
	});
}
// запоминаем/устанавливаем CSS-правила используемые по-умолчанию для заданного элемента
function fieldValidCSS(e, set_default){
	var position = ["Top","Right","Bottom","Left"];
	for(var i=0; i<position.length; i++){
		var name = "border"+position[i];
		var s = $(e).data(name+"Style");
		var c = $(e).data(name+"Color");
		var w = $(e).data(name+"Width");
		if(set_default && (s || c || w)){
			$(e).css(name+"Style", s);// solid
			if(c){ $(e).css(name+"Color", c); }// rgb(192, 186, 186)
			if(w){ $(e).css(name+"Width", w); }// 1px
		}else{
			$(e).data(name+"Style", $(e).css(name+"Style"));// solid
			$(e).data(name+"Color", $(e).css(name+"Color"));// rgb(192, 186, 186)
			$(e).data(name+"Width", $(e).css(name+"Width"));// 1px
		}
	}
}

// проверка валидации данных введенных в заданное поле
function fieldValid(form, field_name, type_check, message){// регулярки отдают true если срабатывает условие, и null в обратном случае
	
	if(form==null || typeof(form)=='undefined'){ return "- Форма не задана!\n"; }
	if(field_name==null || field_name=="" || typeof(field_name)=='undefined'){ return "- Не указано имя поля!\n"; }
	if(type_check==null || type_check=="" || typeof(type_check)=='undefined'){ return "- Для "+field_name+" не указан тип проверяемых данных!\n"; }
	
	var obj = $("[name="+field_name+"]", form).get(0);// находим объект
	if(typeof(obj)!='object'){ return "- Поле с именем "+obj+" не найдено\n"; }
	
	if(message==null || typeof(message)=='undefined' || message==""){
		message = $(obj).parent().parent().find("td").eq(0).text().replace("* ","").replace(":","");
		if(message==""){ return "- Для "+field_name+" не указано отладочное сообщение!\n"; }
	}
	
	var obj_value = trim(obj.value);// удаляю пробелы по сторонам
	obj.value = obj_value;
	
	if(obj_value!="" && type_check){// если поле заполнено И указан type_check
		
		var x = null;
		
		var w = /^[a-zа-я]+$/i;// слово
		
		var n = /^[0-9]+$/i;// число
		
		var d = /^[0-9\.\:\s]+$/i;// дата времени
		
		var p = /^[0-9\-\(\)\s\+]+$/i;// номер телефона
		
		var e = /^([\w\.\-]+)@([a-z0-9\-]+)\.([a-z0-9\-\.]+)$/i;// электронная почта
		
		var t = /^[^0-9]+$/i;// отсутствие цифр
		
		var i = /^[^a-zа-я]+$/i;// отсутствие букв
		
		var t1 = /[a-zа-я]+/i;// хотя бы одна буква
		
		var i1 = /[0-9]+/i;// хотя бы одна цифра
		
		var b = /\s+/;// пробел - условие true: присутсвие хотябы 1 пробела, включая space, tab, form feed, line feed. Эквивалентно [ \f\n\r\t\v].
		
		var valid = obj_value.match(eval(type_check));// в случае успеха - возвращает текущее значение, если совпадение не найдено - null
		
		if(valid==null || valid==" "){
			obj.style.border='#FF0000 1px solid';
			return "- Поле "+message+" заполнено неправильно! Исправьте, пожалуйста.\n";
		}
	}
	fieldValidCSS(obj, true);
	return "";
}
function fieldMust(form, field_name, message){
	
	if(form==null || typeof(form)=='undefined'){ return "- Форма не задана!\n"; }
	if(field_name==null || field_name=="" || typeof(field_name)=='undefined'){ return "- Не указано имя поля!\n"; }
	
	var obj = $("[name="+field_name+"]", form).get(0);// находим объект
	if(typeof(obj)!='object'){ return "- Поле с именем "+obj+" не найдено\n"; }
	if(obj.tagName!="INPUT" && obj.tagName!="TEXTAREA" && obj.tagName!="SELECT"){
		return "- Поле "+field_name+" является элементом "+obj.tagName+" и не поддается проверке!\n";
	}
	
	if(message==null || message=="" || typeof(message)=='undefined'){
		message = $(obj).parent().parent().find("td").eq(0).text().replace("* ","").replace(":","");
		if(message==""){ return "- Для "+field_name+" не указано отладочное сообщение!\n"; }
	}
	
	var obj_value = trim(obj.value);// удаляю пробелы по сторонам
	obj.value = obj_value;
	
	var error = "";
	
	var type = $(obj).attr("type");
	
	if(obj.tagName=="INPUT" && (type=="radio" || type=="checkbox") ){
		
		if(!$(obj).attr("checked")){ error = "- Поле "+message+" не выбрано. Исправьте, пожалуйста!"; }
		
	}else{//INPUT:text || TEXTAREA || SELECT
		
		if(obj_value=="" || obj_value=="0"){ error = "- Поле "+message+" не заполнено. Исправьте, пожалуйста!"; }
		
	}
	
	if(error!=""){ obj.style.border='#FF0000 1px solid'; return error+"\n"; }else{ fieldValidCSS(obj, true); return ""; } 
}
// проверка валидации номера телефона введенного в заданное поле
function phoneValid(e, event_, endfocus){
	
	if(typeof(e.value)!="string" || event_.ctrlKey // Ctrl
	|| event_.keyCode=="13" // Enter
	|| event_.keyCode=="37" // вверх
	|| event_.keyCode=="38" // вправо
	|| event_.keyCode=="39" // вниз
	|| event_.keyCode=="40" // влево
	|| event_.keyCode=="8"){// tab
		return false;
	}
	
	var str = e.value;// введенный пользователем номер
	var newstr = "";// исправленный номер
	var number_badge = 0;// цифровая позиция цифры номера
	var symbol = "";// переменная под символ
	var clear_number = "";// номер исключительно из цифр (без всяких других знаков)
	
	if(str.substr(0,1)==" "){ str = str.substr(1,100); }// удаление пробела в самом начале // нуля в самом начале ИЛИ str.substr(0,1)=="0" || 
	if(str.substr(str.length,1)==" "){ str = str.substr(0,(str.length-1)); }
	
	for(var i=0; i<str.length; i++){
		
		symbol = str.substr(i,1);
		
		if(symbol!=" " && isNaN(symbol)==false){// если цифра
			
			clear_number += symbol;
			
		}
	}
	
	for(var i=0; i<clear_number.length; i++){
		
		symbol = clear_number.substr(i,1);
		
		newstr += symbol;
		
		if((str.substr(0,1)=="8" || str.substr(0,2)=="+7") && clear_number.length<12){// исправление номеров с 8 или +7
			
			if(i==0){ newstr += " ("; }
			if(i==3){ newstr += ") "; }
			if(i==6){ newstr += "-"; }
			
		}else if(clear_number.substr(0,6)=="810380"){// исправление украинских номеров вида: 810380626743377 на 8-10-380 (6267) 43-377
			
			if(i==0 || i==2 || i==12){ newstr += "-"; }// тире
			if(i==9){ newstr += ")"; } // скобка )
			if(i==5 || i==9){ newstr += " "; }// пробелы
			if(i==5){ newstr += "("; } // скобка (
			
		}else if(clear_number.substr(0,3)=="380"){// исправление украинских номеров вида: +380930595377 на +380 (93) 059-5377
			
			if(i==7){ newstr += "-"; }// тире
			if(i==4){ newstr += ")"; } // скобка )
			if(i==2 || i==4){ newstr += " "; } // пробелы
			if(i==2){ newstr += "("; } // скобка (
			
		}else if(clear_number.substr(0,1)=="9" && clear_number.length=="10"){// исправление российских номеров вида: 9264215497 на +7 (926) 421-5497
			
			if(i==0){ newstr = "+7 (9"; }// тире
			if(i==2){ newstr += ") "; } // скобка ) с пробелом
			if(i==5){ newstr += "-"; }// тире
			
		}else if(clear_number.substr(0,1)=="7" && clear_number.length=="11"){// исправление российских номеров вида: 74957887706 на +7 (495) 788-7706
			
			if(i==0){ newstr = "+7 ("; }// тире
			if(i==3){ newstr += ") "; } // скобка ) с пробелом
			if(i==6){ newstr += "-"; }// тире
			
		}else{// неизвестный вид номера разделяем промежуточными тире
			if(i==2 || i==5 || i==10 || i==14){ newstr += "-"; }
		}
	}
	// если в начале номера знак +, при этом следующая цифра не задана или являеся цифрой 7 или 3 (правльно записываемый номер или Украина)
	if(str.substr(0,1)=="+" && (str.substr(1,1)=="" || str.substr(1,1)=="7" || str.substr(1,1)=="3")){ newstr = "+"+newstr; }
	
	// если фокус покидает поле, и на конце сформировавшегося номера знак - тире
	if(endfocus && newstr.substr((newstr.length-1),1)=="-"){ newstr = newstr.substr(0,(newstr.length-1)); }
	
	e.value = newstr;
	
	return false;
}

// устанавливает обработчики событий на элемент(ы) для проверки валидации телефонного номера
function phoneCheck(e){
	$(e).keyup(function(event){// когда пользователь отпускает клавишу клавиатуры
	    phoneValid(this, event);
	}).keypress(function(event){// когда пользователь нажимает клавишу клавиатуры и удерживает её в нажатом состоянии
	    phoneValid(this, event);
	}).change(function(event){// когда поле теряет фокус
	    phoneValid(this, event, true);
	});
}

// получает позицию фокуса (курсора) в заданном поле
function getFocusPos(e){
	var pos = 0;
	if(browser.ie){
		var r = e.document.selection.createRange();
		pos = r.text.length;
	}else{
		pos = e.selectionEnd;// находим порядковый номер окончания выделения
	}
	return pos;
}

// устанавливает позицию фокуса (курсора) в заданном поле
function setFocusPos(e, pos){
	if(browser.ie){
		var r = e.document.selection.createRange();// создаем на основе выделенного объект TextRange
		r.moveStart("character", pos)// начальная позиция равна длинне нового текста
		r.moveEnd("character", pos); //конечная позиция равна минусовой длинне вставляемого текста
		r.select();
	}else{
		e.setSelectionRange(pos,pos);
	}
}
// накрывает словем загрузки текущую страницу
function AjaxWindowLoad(hide){
	if(hide){
		$("#AjaxWindowLoad").hide();
	}else{
		var e = $("#AjaxWindowLoad").get(0);
		if(e){
			$("#AjaxWindowLoad").show();
		}else{
			$(document.body).prepend('<table id="AjaxWindowLoad" align="center" border="0" width="100%" height="100%" style="position: fixed; *position: absolute; z-index:7777777; left: 0%; top: 0%; background-color:#CCCCCC; filter: Alpha(Opacity=50, Style=0); opacity:0.50;"><tr><td style="text-align: center"><img src="/js/tiny_mce/themes/advanced/skins/default/img/progress.gif" border="0"></td></tr></table>');
		}
	}
}

// визуальный эффект: печатная машинка
jQuery.fn.typewriter = function(text) {
	$(this).css("color","#FF0000").each(function() {
		var e = $(this), str = $(e).text(), progress = 0;
		$(e).text('');
		var timer = setInterval(function() {
			$(e).text(str.substring(0, progress++));
			if (progress > str.length){
				clearInterval(timer);
				setTimeout(function(){ $(e).css("color","#000000").text(text); }, 1000);
			}
		}, 100);
	});
	return this;
};
function parentName(e, name){
	var x = null;
	$(e).parents().each(function(){ if(this.tagName==name){ x = this; } });
	return x;
}
// находит и возвращает первый родительский элемент по заданному имени
function firstParent(e, name){
	if(e && typeof(e[0])=="object"){ e = e[0]; }
	var finded = null;
	if(name.substr(0,1)=="."){ var findClass = name.substr(1); }else{ var findClass = null; }
	while(e){
		e = e.parentNode;
		if(e && typeof(e)=="object"){
			if(findClass){
				if( $(e).hasClass( findClass ) ){
					finded = e;
					e = null;
				}
			}else{
				if(e.tagName==name){
					finded = e;
					e = null;
				}
			}
		}else{
			e = null;
		}
	}
	return finded;
}
jQuery.fn.firstParent = function(name){
	var e = firstParent(this, name);
	return $(e);
};
/* Copyright (c) 2006 Brandon Aaron (http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * http://plugins.jquery.com/project/copyEvents
 * $LastChangedDate: 2007-12-20 09:26:01 -0600 (Thu, 20 Dec 2007) $
 * $Rev: 4271 $
 *
 * Version: 1.1
 */
(function($) {

$.fn.extend({
	copyEvents: function(from) {
		$.event.copy( $(from), this );
		return this;
	},
	
	copyEventsTo: function(to) {
		$.event.copy( this, $(to) );
		return this;
	}
});

$.event.copy = function(from, to) {
	var events = $.data(from[0], "events");
	if ( !from.size() || !events || !to.size() ) return;

	to.each(function() {
		for (var type in events)
			for (var handler in events[type])
				$.event.add(this, type, events[type][handler], events[type][handler].data);
	});
};

})(jQuery);

// создание массива кэшированных изображений, пример var myimages = preloadimages("/img0.gif","/img1.gif",...); после чего применять this.style.backgroundImage = "url("+myimages["0"].src+")";
function preloadimages(){
	var myimg=new Array();
	for (i=0;i<preloadimages.arguments.length;i++){
		myimg[i]=new Image();
		myimg[i].src=preloadimages.arguments[i];
	}
	return myimg;
}
var MD5 = function (string) {

	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}

	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
 	}

 	function F(x,y,z) { return (x & y) | ((~x) & z); }
 	function G(x,y,z) { return (x & z) | (y & (~z)); }
 	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }

	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};

	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};

	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	};

	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;

	string = Utf8Encode(string);

	x = ConvertToWordArray(string);

	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}

	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);

	return temp.toLowerCase();
}
function screenWidth(){
	return $.browser.opera? window.innerWidth : $(window).width();
}
function screenHeight(){
	return $.browser.opera? window.innerHeight : $(window).height();
}
function log(x){ if(x && window.console && console.log){ console.log(x); } }

//--------------------------------------
var lebnikHost = window.location.host;
var lebnikHostWithWWW = "www."+lebnikHost;
var lebnikHostWithOutWWW = window.location.host.replace(/www\./i, '');
function other_domain(href){
	if(!href || typeof(href)=="undefined"){
		return ;
	}
	var split_href = href.split('://');
	if(typeof(split_href['1'])!='undefined'){// если есть - ://site.ru...
		var check_host = split_href['1'].split('/');
		if(check_host['0']!=''){// если домен указан
			if(check_host['0']!=lebnikHost && check_host['0']!=lebnikHostWithWWW && check_host['0']!=lebnikHostWithOutWWW){
				return true;
			}
		}
	}
}
//--------------------------------------
function strip_tags (input, allowed) {
	   allowed = (((allowed || "") + "")
	      .toLowerCase()
	      .match(/<[a-z][a-z0-9]*>/g) || [])
	      .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	   var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	       commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	   return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1){
	      return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	   });
}
var callback_before_post = false;// если указать true, то callback будет выполняться до того, как значение будет сохранено в mysql базу cookies
function Cookie(name, value, callback, before){
	
	if(!name || name==""){ return ; }else{ name = ""+name; }// переводим в string
	
	if(value && typeof(value)=="function"){ callback = value; }
	
	var url = "/outer/cookies.php?nocache="+Math.round(Math.random() * 1000000000);
	
	if(typeof(value)!="undefined" && typeof(value)!="function"){
		
		if(typeof(cookies_javascript_object)=="object"){
			value = ""+value;// переводим в string
			eval("cookies_javascript_object."+name+" = '"+value.replace("'","\'")+"';");// если существует, то "string"
			if(callback && (callback_before_post || before)){
				callback(value);
				callback = null;// отменяем callback после $.post
			}
		}
	    $.post(url, {cookie_name:name, value:value}, function(r){
	    	if(r!="1"){
	    		jAlert("Системная ошибка при установке cookies");
	    	}else if(callback){
	    		callback(r);
			}
	    });
	}else{
		
		if(typeof(cookies_javascript_object)=="object"){
			var object_value = null;
			eval("object_value = cookies_javascript_object."+name+";");
			if(typeof(object_value)!="string"){// если существует, то "string"
				object_value = "";
			}
			if(callback){
				callback(object_value);
			}
			return object_value;
		}else{
			if(callback){
				$.post(url, {cookie_name:name}, function(r){
		    		callback(r);
				});
	    	}
    	}
	}
    return "";
}

// функция определения значения определенной куки
function CookieDelete(name, callback){
	
	if(!name || name==""){ return ; }
	
	var url = "/outer/cookies.php?nocache="+Math.round(Math.random() * 1000000000);
	
	$.post(url, {cookie_name:name, del:1}, function(r){
    	if(r!="1"){
    		jAlert("Системная ошибка при удалении cookies");
    	}else if(callback){
			callback(r);
		}
    });
    return "";
}
var cookiedelete = cookie_delete = CookieDelete;
// т.к. jQuery не обрабатывает отключенные чекбоксы, добавляем обработку элементов checkbox, пример применения: $.post(form.action, formdata(form), function(r){});
function formdata(form, without_empty){
	if(!form || typeof(form)!="object"){ alert("Форма данных не указана"); }
	var data = $(form).serialize();
	$("input:checkbox", form).each(function(){
		if(this.checked == false && !without_empty){
			data += "&"+this.name+"=";
		}
	});
	return data;
}
/*
// доп. функция под jQuery под определение offsetWidth

// метод дает возможность применять любые срандартные функции в функциях jQuery. Пример: alert($(this).lebnik("tagName"));
jQuery.fn.lebnik = function(name) { return eval("this[0]."+name); };
jQuery.fn.offsetWidth = function() {
	return this[0].offsetWidth;
};
jQuery.fn.YaPro = function() {
	return this.tagName;
};
*/
