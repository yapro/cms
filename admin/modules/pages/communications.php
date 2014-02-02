<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------------------------------------------
// сохраняю
if(access('w') && $_GET['communication_id'] && $_POST['name'] && $_POST['module_id'] && $_POST['form_id'] && mysql_("UPDATE ".P."communications SET 
	module_id="._.(int)$_POST['module_id']._.", 
	name="._.$_POST['name']._.", 
	form_id="._.(int)$_POST['form_id']._.", 
	permission_id="._.(int)$_POST['permission_id']._." 
"._."WHERE"._." communication_id="._.(int)$_GET['communication_id']._."")){
		
		$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись сохранена<!>';
		
}

// добавляю
if(access('w') && !$_GET['communication_id'] && $_POST['name'] && $_POST['module_id'] && $_POST['form_id'] && mysql_("INSERT INTO ".P."communications SET 
	name = "._.$_POST['name']._.", 
	module_id = "._.(int)$_POST['module_id']._.", 
	form_id = "._.(int)$_POST['form_id']._.", 
	permission_id = "._.(int)$_POST['permission_id']._."")){
		
		$_GET['communication_id'] = mysql_insert_id();
		$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись добавлена<!>';
		
}

// удаляю
if(access('d') && $_GET['delete_communication_id'] && mysql_query("DELETE FROM ".P."communications 
WHERE communication_id='".(int)$_GET['delete_communication_id']."'")){
	echo 'ajax_delete_ok'; exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
// модули
include_once('../../libraries/modules.php');
$modules->pages_options();

// формы
include_once('../../libraries/fields.php');
$forms = $fields-> forms_options();

// нахожу существующие настройки доступа
$permissions = '';
if($q = mysql_query("SELECT permission_id, name FROM ".P."pages_permissions ORDER BY permission_id DESC")){
	while($r=mysql_fetch_assoc($q)){
		$permissions .= '<option value="'.$r['permission_id'].'">'.htmlspecialchars($r['name']).'</option>';
	}
}

function getCommunications($data=array()){
	
	global $modules, $forms, $permissions;
	
	return '<form action="'.$_SERVER['PHP_SELF'].($data['communication_id']?'?communication_id='.$data['communication_id']:'').'" method="post" onsubmit="return CheckSubmitForm(this)">
	<tr class="yaproSortTR">
		<td width="70"><p><b>Название:</b></p></td>
		<td><p><input name="name" type="text" value="'.htmlspecialchars($data['name']).'"></p></td>
	</tr>
	<tr>
		<td><p><b>Модуль:</b></p></td>
		<td><p><select name="module_id" size="1">'.str_replace('="'.$data['module_id'].'"', '="'.$data['module_id'].'" selected class="Selected"', $modules->pages_options).'</select></p></td>
	</tr>
	<tr>
		<td><p><b>Форма:</b></p></td>
		<td><p><select name="form_id" size="1">'.str_replace('="'.$data['form_id'].'"', '="'.$data['form_id'].'" selected class="Selected"', $forms).'</select></p></td>
	</tr>
	<tr>
		<td><p><b>Доступ:</b></p></td>
		<td><p><select name="permission_id" size="1"><option value="0">полный</option>'.str_replace('="'.$data['permission_id'].'"', '="'.$data['permission_id'].'" selected class="Selected"', $permissions).'</select></p></td>
	</tr>
	<tr class="no">
		<td>&nbsp;</td>
		<td><input class="submit" type="submit" value="'.($data['communication_id']?'Сохранить">&nbsp;&nbsp;<a href="#" alt="'.$data['communication_id'].'" class="delete">Удалить</a' : 'Создать"').'></td>
	</tr></form>';
	
}
//---------------------------------------------------------------------------------------------------------------------------------------
$str = '';// нахожу существующие связи
if($q = mysql_query("SELECT communication_id, module_id, form_id, permission_id, name FROM ".P."communications ORDER BY communication_id DESC")){
	while($r=mysql_fetch_assoc($q)){
		$str .= getCommunications($r);
	}
}
echo $_SERVER['SITE_HEADER'].Head('Связи расширяющие настройки модулей по работе с страницами').'<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">'.getCommunications().$str.'</table>';
?>
<style type="text/css">
.delete { text-decoration: none; color: #FF0000; }
</style>
<script type="text/javascript">
// проверка заполнения полей
function CheckSubmitForm(form){
	if(form.name.value == ""){
		jAlert("Вы не указали Название модуля.", function(){
			$(form.name).focus();
		}, 250);
		return false;
	}
}

$(document).ready(function(){
	
	$("input:text").addClass("input_text");
	
	
	// обрабатываем удаление
	$(".delete").attr("title","Удалить").click(function(){
		var element = this;
		var element_id = $(this).attr("alt");
		jConfirm("Подтверждаете удаление?", function(r) {
			if(r==true){
				$.get("<?php echo $_SERVER['PHP_SELF'].'?delete_communication_id='; ?>"+element_id, function(msg){
					if(msg=="ajax_delete_ok"){
						document.location.href = document.location.href;
					}else{
						jAlert("Недостаточно прав");
					}
				});
			}
		});
		return false;
	});
});
</script>
</body>
</html>
