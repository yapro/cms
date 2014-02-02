<?php
include_once('../../../libraries/access.php');
include_once('../../../libraries/modules.php');
$modules-> options();
//---------------------------------------------------------------------------------------------------------------------------------------
// сохраняю
if(access('w') && $_GET['module_id'] && is_numeric($_GET['module_id']) && $_POST['name'] && $reload_left_frame = $modules-> update()){
	$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись сохранена<!>';
}

// добавляю
if(access('w') && !$_GET['module_id'] && $_POST['name'] && $_GET['module_id'] = $modules-> insert($_POST)){
	$reload_left_frame = true;
	$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись добавлена<!>';
}

// удаляю
if(access('d') && $_GET['delete_module_id'] && $reload_left_frame = $modules-> delete($_GET['delete_module_id'])){
	$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись удалена!<!>';
}
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл

$data = $modules-> select($_GET['module_id']);
//---------------------------------------------------------------------------------------------------------------------------------------
if($_GET['parent_id']){ $selected_parent_id = $_GET['parent_id']; }else{ $selected_parent_id = $data['parent_id']; }

$buttons[] = $url._.'images/elements/plus.gif'._.'Создать новый';
$buttons[] = $url.'?parent_id='.$data['parent_id']._.'images/elements/plus_sister.gif'._.'Добавить сестринский';
$buttons[] = $url.'?parent_id='.$data['module_id']._.'images/elements/plus_kid.gif'._.'Добавить дочерний';

echo $_SERVER['SITE_HEADER'].Head($data['module_id']?'Редактирование модуля '.$data['module_id']:'Создание модуля', $buttons).'
<form action="'.$url.($data['module_id']?'?module_id='.$data['module_id']:'').'" method="post" id="myForm">
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
<tr>
	<td width="170"><p><b>Название:</b></p></td>
	<td><p><input name="name" type="text" value="'.htmlspecialchars($data['name']).'"></p></td>
</tr>
<tr>
	<td><p><b>Путь к модулю:</b></p></td>
	<td><p><input name="path" type="text" value="'.htmlspecialchars($data['path']).'"></p></td>
</tr>
<tr>
	<td><p><b>Родительский раздел:</b></p></td>
	<td><p>
		<select name="parent_id" size="1" onchange="SetNewRealPosition()">
			<option value="0" style="background-color: #E7F6FF; font-weight: bold;">Новый главный раздел</option>'.str_replace('="'.$selected_parent_id.'"', '="'.$selected_parent_id.'" selected class="Selected"', $modules->options).'
		</select></p>
	</td>
</tr>
<tr>
	<td><p><b>Порядковый номер:</b></p></td>
	<td><p><input name="position" type="text" value="'.(int)$data['position'].'"></p></td>
</tr>
<tr>
	<td><p><b>Тип модуля:</b></p></td>
	<td><p>
		<select name="type" size="1">
			'.str_replace('="'.$data['type'].'"', '="'.$data['type'].'" selected class="Selected"', '<option value="0">модуль</option><option value="1" style="background-color: #e5e5e5">модуль скрытый</option><option value="2" style="background-color: #99FFFF">модуль в фрейме</option><option value="3" style="background-color: #FFD557">раздел страниц</option>').'
		</select></p>
	</td>
</tr>
<tr class="no">
	<td>&nbsp;</td>
	<td><input class="submit" type="submit" value="'.($data['module_id']?'Сохранить">&nbsp;&nbsp;<input onclick="copyMe();" class="submit" type="submit" value="Копировать">&nbsp;&nbsp;<a href="'.$url.'?delete_module_id='.$data['module_id'].'" onclick="return checkDel(this);" title="Удалить" style="text-decoration: none; color: #FF0000;">Удалить</a' : 'Создать"').'></td>
</tr>
</table></form>';
?>
<style type="text/css">
.DivTableEdite { padding: 28px 0px 30px 0px; }

.NameTable TD.Image { padding-left: 7px; }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }
.NameTable TD.Name { padding-left: 10px; }
</style>
<script type="text/javascript">
// проверка заполнения полей
$("#myForm").submit(function(){
	if($("[name=name]").val()==""){
		jAlert("Вы не указали Название модуля.", function(){
			$("[name=name]").focus();
		}, 250);
		return false;
	}
})



var default_parent_id = <?php echo (int)$data['parent_id']; ?>;
var default_position = <?php echo (int)$data['position']; ?>;
var js_option_max_position = new Array;
<?php echo $modules->js_option_max_position; ?>
function SetNewRealPosition(){
	var select_id = $("select[name=parent_id]").val();
	if(select_id==default_parent_id){
		new_position = default_position;
	}else{
		if(js_option_max_position[select_id]){ var new_position = js_option_max_position[select_id]+10; }else{ var new_position = 10; }
	}
	$("input[name=position]").val(new_position);
}
<?php echo $data['module_id']?'':'SetNewRealPosition();'; ?>

$(document).ready(function(){
	
	$("#TableEdite tr").each(function(){ $("td:first", this).css("white-space", "nowrap"); });
	$("#TableEdite tr:eq(0) td:eq(1)").css("width", "100%");
	
	$("input:text").addClass("input_text");
	
	mouseMovements("TableEdite");
	
	<?php if($reload_left_frame){ echo 'if(window.parent.left_frame && window.parent.left_frame.system_iframe){
		window.parent.left_frame.document.getElementById("backToMenu").onclick = function(){ window.parent.left_frame.location.reload(); }
		window.parent.left_frame.system_iframe.location.reload();
	}'; } ?>
	
	$("[name=name]").focus();
});

var kids = <?php echo count($modules->parent_id__module_id[ $data['module_id'] ]); ?>;
var confirm_message = "Уверены что хотите удалить данный модуль?<br>Так же будут удалены все "+(kids?"дочернии модули и ":"")+" права<br>касающиеся этого "+(kids?"и дочерних модулей":"модуля")+"!";

function copyMe(){
	var form = $("#myForm")[0];
	form.action = form.action.replace("module_id","copy");
}
</script>
</body>
</html>
