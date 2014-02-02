<?php
include_once('../../libraries/access.php');
//-----------------------------------------------------------------------------------------------------------------------------
// добавляю
if(access('w') && !$_GET['variant_id'] && $_POST['name'] && mysql_("INSERT INTO ".P."subscribe_variants 
	VALUES("._._.", "._.(int)$_POST['position']._.", "._.$_POST['name']._.", "._.$_POST['comment']._.")")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Добавление выполнено';
}
// сохранение
if(access('w') && $_GET['variant_id'] && is_numeric($_GET['variant_id']) && $_POST['name'] && mysql_("UPDATE ".P."subscribe_variants SET name="._.$_POST['name']._.", comment="._.$_POST['comment']._.", position="._.$_POST['position']._." "._."WHERE"._." variant_id="._.$_GET['variant_id']._."")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Сохранение выполнено';
}
// удаление
if(access('d') && $_GET['delete_variant_id'] && is_numeric($_GET['delete_variant_id']) 
&& mysql_query("DELETE FROM ".P."subscribe_variants WHERE variant_id='".$_GET['delete_variant_id']."'")
&& mysql_query("DELETE FROM ".P."subscribe WHERE variant_id='".$_GET['delete_variant_id']."'")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Удаление выполнено';
}
//-----------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['REQUEST_URI'];// общий урл

if($q = mysql_query("SELECT * FROM ".P."subscribe_variants ORDER BY position")){
	while($r=mysql_fetch_assoc($q)){
		$str .= '<form action="'.$url.'&variant_id='.$r['variant_id'].'" method="post"><tr>
			<td><p>'.$r['variant_id'].'</p></td>
			<td><p><input name="name" type="text" value="'.htmlspecialchars($r['name']).'"></p></td>
			<td><p><input name="comment" type="text" value="'.htmlspecialchars($r['comment']).'"></p></td>
			<td width="80"><p><input name="position" type="text" value="'.$r['position'].'"></p></td>
			<td width="70"><p><input class="button" type="submit" value="Сохранить"></p></td>
			<td width="25"><p align="center"><a href="'.$url.'&delete_variant_id='.$r['variant_id'].'" onclick="return checkDel();" title="Удалить"><img src="images/elements/del.gif"></a></p></td>
		</tr></form>';
	}
}

echo $_SERVER['SITE_HEADER'].Head('Варианты подписок').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>ID</b></td>
		<td><b>Имя</b></td>
		<td><b>Пояснение</b></td>
		<td><b>Позиция</b></td>
		<td colspan=2><b>Действия</b></td>
	</tr><form action="'.$url.'" method="post"><tr>
		<td width="30"><p>Авто</p></td>
		<td><p><input name="name" type="text" value=""></p></td>
		<td><p><input name="comment" type="text" value=""></p></td>
		<td width="80"><p><input name="position" type="text" value=""></p></td>
		<td width="95" colspan=2><p><input class="submit" type="submit" value="Добавить"></p></td>
	</tr></form>
	'.$str.'
</table>';
?>
<style type="text/css">

</style>
<script type="text/javascript">
// изменяем вопрос подтверждения при удалении
confirm_message = "Так же будут удадлены все подписки пользователей на данный вариант рассылки, если таковые существуют. Подтверждаете удаление?";

$(document).ready(function(){
	$("input:text").addClass("input_text");
	mouseMovementsClass("overflowTable");
});
</script>
</body>
</html>
