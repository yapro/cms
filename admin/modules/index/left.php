<?php
$GLOBALS['SYSTEM']['notSaveUrl'] = true;
include_once('../../libraries/access.php');
include_once('../../libraries/modules.php');
$modules-> treeview();
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].'
<table id="siteSelect" border="0" cellpadding="0" cellspacing="0" width="100%" class="NameTable" style="position: static">
    <tr>
        <td style="padding-left: 5px;" width="1"><a href="'.$_SERVER['SYSTEM']['URL'].'index.php?exit" title="Выйти из Системы Администрирования" style="background: url(images/elements/logout.gif) no-repeat scroll 0 0 transparent; padding: 0 5px 1px 20px; font-weight: bold; color: #000000;">Выйти</a></td>
        <td style="text-align:right; padding-right: 5px; padding-bottom: 1px;"><a href="http://'.$GLOBALS['SYSTEM']['config']['yapro_http_host'].'" title="Открыть сайт" target="_blank" style="background: url(images/logotypes/m-domains.png) no-repeat scroll right center transparent; padding: 0 23px 1px 0; font-weight: bold; color: #000000; outline:none;">Открыть сайт</a></td>
    </tr>
</table>

<table id="backToMenu" border="0" cellpadding="0" cellspacing="0" width="100%" class="NameTable cursor" style="position: static; display: none;">
    <tr>
        <td onclick="ShowHideIframe()" class="Image"><img src="images/elements/lastpost.gif"></td>
        <td onclick="ShowHideIframe()" class="Name" width="100%">Вернуться в меню</td>
        <td id="Refresh" width="100%" style="padding: 0 10px;" valign="top" title="Обновить карту"><img src="images/elements/reload.png" style="margin-top:6px;"></td>
    </tr>
</table>

<ul id="treeview20090525">'.$modules->treeview.'</ul>
<link href="/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>';
?>
<script language="JavaScript" type="text/javascript">

// создаем переменную которую можно проверять на клик на странице
var document_onclick = false;
document.onclick = function(){
	document_onclick = true;
};

$(document).ready(function(){
	
	$("#treeview20090525").yaproTreeview(true,"indexLeft");
	
	$("#treeview20090525 li").each(function(){
		
		var a = $("a:first", this);
		var href = $(a).attr("href");
		
		if(href.substr(-2,1)=="#"){
			$(a).click(function(){ $(this).parents("li").filter(":first").find("div:first").trigger("click"); return false; });
		}else{
			$(a).attr("target", "right_frame");
			if($(this).attr("alt")=="2"){
				$(a).click(function(){
					if(!$(this).data("result_loading")){
						return ShowHideIframe(this);
					}
				});
			}
		}
		
	});
	
	$("#Refresh").click(function(){
		var iframe = $("[name=system_iframe]")[0];
		iframe.src = iframe.src + 1;
		return false;
	});
	/*
	$("#siteSelect SELECT").dblclick(function(){
		alert(1);
	});
	*/
});

function ShowHideIframe(a){
	var iframe = $("[name=system_iframe]")[0];
	if(iframe){
		$(iframe).remove();// удаляем iframe
		$("#backToMenu").hide();// скрываем назад в меню
		//if($.browser.msie){ document.getElementsByTagName("BODY")[0].style.overflow = "auto"; }
		$("BODY").css("overflow","auto");
		$("#siteSelect").show();// показываем выбор сайтов
		$("#treeview20090525").show();// показываем меню
		$(".treeviewPointer DIV").removeClass("treeviewBack").addClass("treeviewLoading");
	}else{
		AjaxWindowLoad();
		var iframe_h = $(window).height() - $("#siteSelect")[0].offsetHeight;
		//if($.browser.msie){ document.getElementsByTagName("BODY")[0].style.overflow = "hidden"; }
		$("BODY").css("overflow","hidden");
		var href_split = a.href.split("?");
		var src = a.href+((href_split.length>1)? "&" : "?")+"nocache="+Math.round(Math.random() * 1000000000);// перезагружаем iframe
		var e = $('<iframe vspace="0" hspace="0" marginwidth="0" marginheight="0" frameborder="0" scrolling="auto" width="100%" height="" name="system_iframe" style="display:none;" src="'+src+'"></iframe>');
		$(document.body).append( e );
		$("#siteSelect").hide("fast");// скрываем выбор сайтов
		$("#treeview20090525").hide("fast");// скрываем меню
		$("#backToMenu").show(function(){ if($.browser.msie){ this.style.removeAttribute("filter"); } });// показываем назад в меню
		$(e).load(function(){
			$(e).show(function(){ if($.browser.msie){ this.style.removeAttribute("filter"); } });// показываем iframe
			$(e).css("display","block").attr("height", iframe_h);// выставляем высоту iframe
			AjaxWindowLoad("hide");
		});
	}
	return false;
}

// TEST
$(document).ready(function(){
	$("#161 A:first").data("result_loading",true).click(function(){
		
		var module_id = $(this).parent().attr("id");
		
		var a = this;
		
		if($(a).data("load_trigger_click")){ return false; }
		
		document_onclick = false;
		
		var module_adress = a.href;
		
		var result_adress = $("#ADMIN_URL").val()+"results/"+module_id+"."+Cookie("visitor_hash")+".html";
		
		// меняем фон элемента на загрузку
		var div = $(a).parent().find("DIV:first");
		var bg = $(div).css("background");
		var bgp = $(div).css("background-position");
		$(div).css("background", "url(/images/elements/apple-loading-16-16.gif) no-repeat 0 0");
		//log(div);
		//log( $(div).css("background") );
		
		//log(result_adress);
		$.get(module_adress,{"ajax_result_left_frame":"true"});
		
		var timer = setInterval(function(){
			$.get(result_adress, function(r){
				if(r && r!="" && r!=" "){
					
					$(a).click(function(){// при клике по ссылке - меняем фон элементов на стандарт
						if(!$(a).data("result_loading")){
							$(div).css("background",bg).css("background-position",bgp);
							$(a).data("result_loading",true).data("load_trigger_click",false).attr("href",module_adress);
						}
					});
					
					$(a).attr("href",result_adress).data("result_loading",false).data("load_trigger_click",true);
					
					if(document_onclick){
						
						// меняем фон элемента на ОК
						$(div).css("background","url(images/elements/ok.png) no-repeat scroll 0 0 transparent").click(function(){
							$(a).trigger("click");
						});
						
					}else{
						$(a).trigger("click");
					}
					
					//background-position
					//log(r);
					clearTimeout(timer);
				}
			});
		},5000);
		return false;
	});
});
</script>
<style>
.NameTable TD { text-shadow: 2px 2px 3px #FFFFFF; }
</style>
</body>
</html>
