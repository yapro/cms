<?php
$GLOBALS['SYSTEM']['notSaveUrl'] = true;// говорим системе, чтобы не запоминала просмотр данной страницы (левого фрейма)
include_once('../../libraries/access.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/pages.php');
//---------------------------------------------------------------------------------------------------------------------------------------
$GLOBALS['SYSTEM']['communications']-> permissions();
//---------------------------------------------------------------------------------------------------------------------------------------
$GLOBALS['SYSTEM']['sort_pages'] = $_COOKIE['system_sort_pages_left'];// указывем выбор сортировки
//---------------------------------------------------------------------------------------------------------------------------------------
if($_POST['ajax'] && $_POST['id']){
	$GLOBALS['pages']-> treeview($GLOBALS['pages']->page_id__parent_id[ $_POST['id'] ]);
	echo $GLOBALS['pages']->treeview; exit;
}
//print_r($GLOBALS['SYSTEM']['base_pages']);
//print_r($GLOBALS['pages']->access_base_id);
/*foreach($GLOBALS['pages']->access_base_id as $k=>$v){
	echo $GLOBALS['pages']->base[$k]['name']."\n";
}*/
$GLOBALS['pages']-> treeview();

echo $_SERVER['SITE_HEADER'].'
<style>
BODY { overflow: auto; }
</style>
<div style="padding: 3px 0px 3px 3px; background-color: #ECECEC;"><select onchange="Cookie(\'system_sort_pages_left\', this.value, function(){ window.location.reload(); });" style="width: 100%;">
'.str_replace('="'.$_COOKIE['system_sort_pages_left'].'"', '="'.$_COOKIE['system_sort_pages_left'].'" selected', '<option value="0">без сортировки</option>'.$GLOBALS['pages']-> fields_sort_options().'</select>').'</div>
<ul id="treeview20090524">'.$GLOBALS['pages']->treeview.'</ul>
<div style="padding:5px 0; background-color:#ECECEC; text-align: center; cursor:pointer; margin-top:5px" id="sh_20111125">свернуть / развернуть</div>
<link href="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>
<script type="text/javascript">
var access_color = ["", "E1E1E1", "99FF99", "CCFFCC", "9999FF", "CCCCFF", "FF99FF", "FFCCFF", "FFB7FF", "ED8181"];
function TreeviewPagesLinks(ul){// ф-я запускается 1 раз после построения дерева и каждый последующий раз при Ajax-подгрузке
	
	$("li", ul).each(function(){
		
		var li = this;
		
		var page_id = $(this).attr("id");
		
		var a = $("a:first", li);
		
		if(!$(a).attr("href")){// если первая ссылка не имеет атрибута href (меньше HTML для карты сайта)
			
			var alt = $(li).attr("alt");
			var ex = alt.split(".");
			var access = ex[0];
			var position = ex[1];
			// ex[2] - номер по порядку, для поддержки функции подгрузки Ajax
			
			if(access!=0){
				
				$(a).css("border-bottom", "1px solid #"+access_color[ access ]);
				
			}
			
			$(a).attr("href", "'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'right.php?moduleID='.$GLOBALS['SYSTEM']['module_id'].'&page_id="+page_id).click(function(){
				
				window.parent.parent.right_frame.location.href = this.href;
				
				$("#treeview20090524 A").css("background-color","transparent");
				
				$(a).css("background-color","#E7F3FF");
				
				var parents_id = $(this).parents("LI").map(function(){ 
				    return $(this).attr("id");
				}).get().join(",");
				
				Cookie("system_pages_left_selected_parents_id", parents_id);
				
				return false;
			}).attr("title", position+". "+$(a).text());
		}
	});
}
$(document).ready(function(){
	
	$("#treeview20090524").yaproTreeview(true,"pagesLeft","TreeviewPagesLinks",'.(int)$GLOBALS['SYSTEM']['config']['yapro_admin_pages_max'].',"'.$_SERVER['REQUEST_URI'].'");
	
	var system_pages_left_selected_parents_id = Cookie("system_pages_left_selected_parents_id");
	if(system_pages_left_selected_parents_id && system_pages_left_selected_parents_id != "" && system_pages_left_selected_parents_id != "0"){
		var selected_page_id = system_pages_left_selected_parents_id.split(".");
		var li = $("#"+selected_page_id[0])[0];
		var position = PosElement(li);
		window.scrollTo(0, (position.Top - (screenHeight()/2) ) );
		$("a:first", li).css("background-color","#E7F3FF");
	}
	
	$("#sh_20111125").click(function(){
		$(".explorer").trigger("click");
	});
});
</script>
</body>
</html>';
?>
