<?php
include_once('../../libraries/access.php');
$forum_prefix = F;
/*
group_type:
0 - По запросу
1 - Закрытая
2 - Скрытая
4 - Открытая
*/
//---------------------------------------------------------------------------------------------------------------------------------------
if(access('w') && $_POST['group_name']){
	// сохраняю
	if($_GET['group_id']){
		if(mysql_("UPDATE ".$forum_prefix."groups SET group_name="._.$_POST['group_name']._." "._."WHERE"._." group_id="._.$_GET['group_id']._."")){
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись сохранена';
		}
	// добавляю
	}else if(mysql_("INSERT INTO ".$forum_prefix."groups SET group_type=2, group_name="._.$_POST['group_name']._."")){
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	}
}

// удаляю
if(access('d') && $_GET['delete_group_id'] && is_numeric($_GET['delete_group_id']) && mysql_query("DELETE FROM ".$forum_prefix."groups WHERE group_id=".$_GET['delete_group_id']."")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
}

$url = $_SERVER['PHP_SELF'];// общий урл

$str = '';

if($q = mysql_query("SELECT group_id, group_name FROM ".$forum_prefix."groups WHERE group_type=2 ORDER BY group_name")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<form action="'.$url.'?group_id='.$r['group_id'].'" method="post"><tr>
			<td>'.$r['group_id'].'</td>
			<td><input name="group_name" type="text" value="'.htmlspecialchars($r['group_name']).'"></td>
			<td width="1"><input class="button" type="submit" value="Сохранить"></td>
			<td><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'users.php?group_id='.$r['group_id'].'">Участники»</a></td>
			<td width="1"><a href="'.$url.'?delete_group_id='.$r['group_id'].'" title="Удалить"><img src="images/elements/del.gif"></a></td>
		</tr></form>';
	}
}

echo $_SERVER['SITE_HEADER'].Head('Управление группами').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1"><b>ID</b></td>
		<td width="100%"><b>Название группы</b></td>
		<td colspan=3><b>Действия</b></td>
	</tr>
	<form action="'.$url.'" method="post"><tr>
		<td>&nbsp;</td>
		<td><input name="group_name" type="text" value=""></td>
		<td colspan=3><input class="submit" type="submit" value="Добавить"></td>
	</tr></form>
	'.$str.'
</table>';
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	$("input:text").addClass("input_text");
	mouseMovementsClass("overflowTable");
	$('[title="Удалить"]').click(function(){
		var a = this;
		jConfirm("Подтверждаете удаление?", function(r) {
		    if(r==true){
		    	location.href = a.href;
			}
		});
		return false;
	});
});
</script>
</body>
</html>
