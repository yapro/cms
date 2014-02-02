<?php
// скрипт добавляет в левый верхний угол сайта 2 активные кнопки для обновления CSS и JavaScript файлов
$GLOBALS['SYSTEM']['buttons'] .= '<a href="/outer/css_js.php?a=css">CSS</a><a href="/outer/css_js.php?a=js">JavaScript</a>
<script type="text/javascript">

var allow_ajax_system_moderation_buttons = true;

$("#system_moderation_buttons A").click(function(){
	
	if( allow_ajax_system_moderation_buttons ){
		
		allow_ajax_system_moderation_buttons = false;
		
		$.fancybox.showActivity();
		
		var href = $(this).attr("href");
		
		$.get(href, function(r){
			
			allow_ajax_system_moderation_buttons = true;
			
			$.fancybox.hideActivity();
			
			if(r.msg=="ok"){
				document.location.href = document.location.href;
			}else{
				jAlert(r.msg);
			}
		},"json");
	}
	return false;
});
</script>';
?>