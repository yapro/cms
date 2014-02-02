<?php
$page_id = (int)$_GET['page_id'];
$parent_id = (int)($data['parent_id']? $data['parent_id'] : $_GET['parent_id']);
$field_name = $f['name']? $f['name'] : 'parent_id';
$uniq = md5($field_name);
$root_access = $GLOBALS['SYSTEM']['access_page_id']? false : true;// изначально если имеются настройки доступа - доступ запрещен

// чтобы определить доступ к Кореню сайта - проверяем parent_id у ID к которым разрешен доступ
if(!$root_access && $GLOBALS['SYSTEM']['access_page_id'] && $GLOBALS['pages']->access_page_id){
	foreach($GLOBALS['pages']->access_page_id as $id => $true){
		// если у страниц, к которым разрешен доступ выбран родительский раздел - Корень сайта
		if($GLOBALS['pages']->page_id__parent_id[ $id ]==0){
			$root_access = true;
			break;
		}
	}
}
// разрешаю доступ выбора разделов у которых есть на это доступ==2 (специально не вынесено в файл pages.php!)
if($GLOBALS['SYSTEM']['access_page_id']){
	foreach($GLOBALS['SYSTEM']['access_page_id'] as $id => $access){
		if($access==2){
			$GLOBALS['pages']-> access_page_id[ $id ] = true;
			
			// если доступа к Корню сайта нет И у страницы не задан родительский раздел - назначаем первый попавшийся ID за главного
			if(!$root_access && !$parent_id){ $parent_id = $id; }
		}
	}
}

if($_POST['ajax'] && $_POST['loadTreeview']){
	
	if(!$_POST['id'] && $root_access){// если это не продолжение построения списка и есть доступ к Корню сайта
		$GLOBALS['pages']->treeview = '<li id="0" alt="0.0.0"><a>Корень сайта</a></li>';
	}
	
	if($_POST['id']){
		
		$GLOBALS['pages']-> treeview($GLOBALS['pages']->page_id__parent_id[ $_POST['id'] ]);// создаем массив ID родительских страниц и запускаем treeview()
		
	}else{
		
		$GLOBALS['pages']->treeview();
		
	}
	echo $GLOBALS['pages']->treeview; exit;
}

if($parent_id){// находим имя родительского раздела
	
	$r = @mysql_fetch_assoc(mysql_query("SELECT name FROM ".P."pages WHERE page_id='".$parent_id."'"));
	
	$parent_name = $r['name']? htmlspecialchars($r['name']) : 'Не имеет названия или название не определено';
	
}

$document = '<tr class="no">
	<td><p><b>'.$f['title'].'</b></p></td>
	<td>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" onclick="treeviewToogle'.$uniq.'(this);" class="treeviewToogle'.$uniq.'">
		    <tr class="no">
		        <td>
		            <p id="selected_parent'.$field_name.'"><input type=hidden name="'.$field_name.'" value="'.$parent_id.'">'.($parent_id? $parent_name : ($root_access?'Корень сайта':'Доступ к разделам не задан') ).'</p>
		        </td>
		        <td width="15" height="16">
		            <div class="treeviewSelectPointer">&nbsp;</div>
		        </td>
		    </tr>
		</table>
	</td>
</tr>
<style>
.treeviewToogle'.$uniq.' { cursor: pointer }
.treeviewToogle'.$uniq.' TD { border: 0px !important; padding: 0px !important; }
.treeviewSelectPointer { background: transparent url(/images/elements/select_pointer.png) no-repeat; height: 14px }
.treeviewSelectLoading { background: transparent url(/images/elements/apple-loading-16-16.gif) no-repeat; height: 16px }
.treeviewSelectSelected {}
</style>
<link href="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>
<script type="text/javascript">
var selectAccess = true;
function treeviewToogle'.$uniq.'(table){
	
	if(!selectAccess){ return ; }
	
	var treeview = $("#treeview'.$uniq.'")[0];
	
	if(typeof(treeview)=="undefined"){
		selectAccess = false;
		$("div", table).removeClass("treeviewSelectPointer").addClass("treeviewSelectLoading");// изменение значка справа
		AjaxForm("'.$_SERVER['REQUEST_URI'].'", "&loadTreeview=true", "loadTreeview'.$uniq.'(msg)");//&start_id='.$page_id.'
		return false;
	}
	
	if($(treeview).css("display")=="none"){
		
		$(treeview).css("width", ($(table)[0].offsetWidth - 3)+"px").show();
		$("div", table).css("background-position", "0 -14px");
		
	}else{
		
		$(treeview).hide();
		$("div", table).css("background-position", "0px 0px");
		
	}

}
function insertParent'.$uniq.'(id, max_position){
	
	$("#selected_parent'.$field_name.'").html(\'<input type=hidden name="'.$field_name.'" value="\'+id+\'">\'+$("#"+id+" a:first").text());
	
	$("#treeview'.$uniq.'").hide();
	
	$(".treeviewToogle'.$uniq.' div").css("background-position", "0px 0px");
	
	if( id != $(\'[name="reload_frame['.$field_name.']"]\').val() ){
		if(!max_position){ var max_position = 0; }
		$("input[name=position]").val(eval(max_position)+10);
	}
}
var access_color = ["", "E1E1E1", "99FF99", "CCFFCC", "9999FF", "CCCCFF", "FF99FF", "FFCCFF", "FFB7FF", "ED8181"];
function selectParent'.$uniq.'(ul){
	
	$("li", ul).each(function(){
		
		var a = $("a:first", this);
		
		var id = $(this).attr("id");
		
		if(!$(a).attr("href")){
			
			var alt = $(this).attr("alt");
			if(alt){
				var ex = alt.split(".");
				var access = ex[0];
				var position = ex[1];
				var max_position = ex[2];
				
				if(access!=0 && access_color[ access ]){ $("a", this).css("border-bottom", "1px solid #"+access_color[ access ]); }
				$(a).data("position", position);
			}
			$(a).click(function(){ insertParent'.$uniq.'(id, max_position); $("input[name=name]").focus(); return false; });
		}
		//$(">ul", this).each(function(){ selectParent'.$uniq.'(this); });
	});
}

function loadTreeview'.$uniq.'(html){
	
	var table = $(".treeviewToogle'.$uniq.'");
	
	$(table).after(\'<ul id="treeview'.$uniq.'" class="filetree treeview" style="background-color: #FFFFFF; overflow: auto; height: 445px; display: none; position: absolute; z-index: 55555; border: 3px solid #CCCCCC">\'+html+\'</ul>\');
	
	$("#treeview'.$uniq.'").yaproTreeview(true,"x'.$uniq.'", "selectParent'.$uniq.'", '.(int)$GLOBALS['SYSTEM']['config']['yapro_admin_pages_max'].', "'.$_SERVER['REQUEST_URI'].'");
	
	$("div", table).removeClass("treeviewSelectLoading").addClass("treeviewSelectPointer");
	$("#treeview'.$uniq.'").hide();
	selectAccess = true;
	treeviewToogle'.$uniq.'(table);
}
</script>';
?>
