<?php
include_once('../../libraries/access.php');
include_once('../../libraries/pages_permissions.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/pages.php');// класс работы с таблицей страниц
//+include_once($_SERVER['DOCUMENT_ROOT'].'/inner/Functions/system_cache.php');// класс работы с кэшем
//---------------------------------------------------------------------------------------------------------------------------------------
if($_POST['ajax'] && $_POST['id']){
	$GLOBALS['pages']-> treeview($GLOBALS['pages']->page_id__parent_id[ $_POST['id'] ]);
	echo $GLOBALS['pages']->treeview; exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
// сохраняю
if(access('w') && $_POST && $_GET['permission_id'] && $GLOBALS['pages_permissions']-> update($_GET['permission_id']) ){
	
	$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись сохранена<!>';
}
// добавляю
if(access('w') && $_POST && !$_GET['permission_id'] && $_GET['permission_id'] = $GLOBALS['pages_permissions']-> insert() ){
	
	$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись добавлена<!>';
}
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл
//---------------------------------------------------------------------------------------------------------------------------------------
$GLOBALS['pages']-> treeview();
//---------------------------------------------------------------------------------------------------------------------------------------
$access_hidden = '';
$data = $GLOBALS['pages_permissions']-> select($_GET['permission_id']);
if($data['permissions']){// прописываю существующие данные доступа в HTML
	foreach($data['permissions'] as $page_id => $allow){
		$access_hidden .= '<input type="hidden" name="permissions['.$page_id.']" value="'.$allow.'">';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
$left[] = $url._.'images/elements/plus.gif'._.'Добавить новую настройку доступа';
$right = '<td><input type="submit" value="Сохранить"></td>';
// onsubmit="return CheckSubmitForm(this)"
echo $_SERVER['SITE_HEADER'].'<form action="'.$url.'?permission_id='.(int)$_GET['permission_id'].'" method="post">
'.Head('Настройки доступа к страницам',$left,$right).'
'.$access_hidden.'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr>
		<td width="95"><p><b>Имя настройки:</b></p></td>
		<td><p><input type="text" name="name" value="'.htmlspecialchars($data['name']).'"></p></td>
	</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td>
			<ul id="treeview20090615"  class="filetree treeview">'.$GLOBALS['pages']->treeview.'</ul></form>
		</td>
	</tr>
</table></form>
<link href="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>';
?>
<style type="text/css">
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }

.AcessButton {
	background: transparent url(images/elements/bg_access_pages.png) no-repeat;
	cursor: pointer;
	float: left;
	height: 16px;
	width: 16px;
	margin-right: 3px
}
.state0 {  }
.state1{ background-position: 0 -16px; }
.state2{ background-position: 0 -32px; }
.state3{ background-position: 0 -48px; }
.state4{ background-position: 0 -64px; }
.state5{ background-position: 0 -80px; }
.state6{ background-position: 0 -96px; }
.state7{ background-position: 0 -112px; }
</style>
<script type="text/javascript">
var access_color = ["", "E1E1E1", "99FF99", "CCFFCC", "9999FF", "CCCCFF", "FF99FF", "FFCCFF", "FFB7FF", "ED8181"];
var title = Array();
title[0] = 'Настройка доступа отключена';
title[1] = 'Доступ к странице';
title[2] = 'Доступ к дочерним страницам';
title[3] = 'Доступ к разделу целиком';
title[4] = 'Отключен доступ к странице';
title[5] = 'Отключен доступ к дочерним страницам';
title[6] = 'Отключен доступ к разделу целиком';

function hiddenAllow(id,allow){
	var hidden = $('[name="permissions['+id+']"]');
	if(allow){
		if(hidden.length<1){
			hidden = $('<input type="hidden" name="permissions['+ id +']">');
			$("form").prepend( hidden );
		}
		hidden.val(allow);
	}else{
		if(hidden.length>0 && allow==0){
			hidden.remove();
		}
	}
}

function TreeviewAcessButton(ul){
	
	$("li", ul).each(function(){
		
		var div = $("div.mark:first", this)[0];
		
		if(div){
			var id = this.id;
			var state = $('[name="permissions['+id+']"]').val();
			if(!state){ state = 0; }
			
			$(div).removeClass("mark").addClass("AcessButton state"+state).click(function(){
				
				if($(this).hasClass("state0")){
					$(this).removeClass("state0").addClass("state1").attr('title',title[1]);
					hiddenAllow(id,1);
				}else if($(this).hasClass("state1")){
					$(this).removeClass("state1").addClass("state2").attr('title',title[2]);
					hiddenAllow(id,2);
				}else if($(this).hasClass("state2")){
					$(this).removeClass("state2").addClass("state3").attr('title',title[3]);
					hiddenAllow(id,3);
				}else if($(this).hasClass("state3")){
					$(this).removeClass("state3").addClass("state4").attr('title',title[4]);
					hiddenAllow(id,4);
				}else if($(this).hasClass("state4")){
					$(this).removeClass("state4").addClass("state5").attr('title',title[5]);
					hiddenAllow(id,5);
				}else if($(this).hasClass("state5")){
					$(this).removeClass("state5").addClass("state6").attr('title',title[6]);
					hiddenAllow(id,6);
				}else{
					$(this).removeClass("state6").addClass("state0").attr('title',title[0]);
					hiddenAllow(id,0);
				}
			});
			
			var access = $(this).attr("alt");
			if(access!=0){ $("a", this).css("border-bottom", "2px solid #"+access_color[ access ]); }
		}
	});
}
$("[name=name]").addClass("input_text").focus();

$("#treeview20090615").yaproTreeview(true,"pagesAcess","TreeviewAcessButton",<?php echo (int)$GLOBALS['SYSTEM']['config']['yapro_admin_pages_max']; ?>,"<?php echo $_SERVER['REQUEST_URI']; ?>");

$(".state1").attr('title',title[1]);
$(".state2").attr('title',title[2]);
$(".state3").attr('title',title[3]);
$(".state4").attr('title',title[4]);
$(".state5").attr('title',title[5]);
$(".state6").attr('title',title[6]);
$(".state0").attr('title',title[0]);

</script>
</body>
</html>
