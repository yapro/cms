<?php
include_once('../../libraries/access.php');
$forum_prefix = F;
//---------------------------------------------------------------------------------------------------------------------------------------
if(access('w') && $_POST['rank_title']){
	// сохраняю
	if($_GET['rank_id']){
		if(mysql_("UPDATE ".$forum_prefix."ranks SET rank_title="._.$_POST['rank_title']._.", rank_min="._.$_POST['rank_min']._.", 
		rank_special="._.(($_POST['rank_special']=='on')?1:0)._." "._."WHERE"._." rank_id="._.$_GET['rank_id']._."")){
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись сохранена';
		}
	// добавляю
	}else if(mysql_("INSERT INTO ".$forum_prefix."ranks SET rank_title="._.$_POST['rank_title']._.", rank_min="._.$_POST['rank_min']._.", rank_special="._.(($_POST['rank_special']=='on')?1:0)._)){
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	}
}

// удаляю
if(access('d') && $_GET['delete_rank_id'] && is_numeric($_GET['delete_rank_id']) && mysql_query("DELETE FROM ".$forum_prefix."ranks WHERE rank_id=".$_GET['delete_rank_id']."")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
}
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл
$str = '';
if($q = mysql_query("SELECT rank_id, rank_title, rank_min, rank_special FROM ".$forum_prefix."ranks ORDER BY rank_title")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<form action="'.$url.'?rank_id='.$r['rank_id'].'" method="post"><tr>
			<td>'.$r['rank_id'].'</td>
			<td><input name="rank_title" type="text" value="'.htmlspecialchars($r['rank_title']).'"></td>
			<td><input name="rank_min" type="text" value="'.(int)$r['rank_min'].'"></td>
			<td class="center"><input name="rank_special" type="checkbox"'.($r['rank_special']?' checked':'').'></td>
			<td width="1"><input class="button" type="submit" value="Сохранить"></td>
			<td width="1"><a href="'.$url.'?delete_rank_id='.$r['rank_id'].'" title="Удалить"><img src="images/elements/del.gif"></a></td>
			<td width="1"><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'users.php?rank_id='.$r['rank_id'].'">Участники»</a></td>
		</tr></form>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Управление пользовательскими званиями (рангами)').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1"><b>ID</b></td>
		<td><b>Название группы</b></td>
		<td width="150"><b>Минимум сообщений</b></td>
		<td width="1"><b>Специальное</b></td>
		<td width="1" colspan=3><b>Действия</b></td>
	</tr>
	<form action="'.$url.'" method="post"><tr>
		<td>&nbsp;</td>
		<td><input name="rank_title" type="text" value=""></td>
		<td><input name="rank_min" type="text" value=""></td>
		<td class="center"><input name="rank_special" type="checkbox"></td>
		<td width="1" colspan=3><input class="submit" type="submit" value="Добавить"></td>
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
