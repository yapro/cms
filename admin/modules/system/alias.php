<?php
include_once('../../libraries/access.php');

if(access('w') && $_POST['k'] && $_POST['v']){
	// сохраняю
	if($_GET['alias_id']){
		if(mysql_("UPDATE ".P."alias SET k="._.$_POST['k']._.", v="._.$_POST['v']._." "._."WHERE"._." alias_id="._.$_GET['alias_id']._."")){
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись сохранена';
		}
	// добавляю
	}else if(mysql_("INSERT INTO ".P."alias VALUES("._._.", "._.$_POST['k']._.", "._.$_POST['v']._.")")){
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	}
}

// удаляю
if(access('d') && $_GET['delete_alias_id'] && mysql_("DELETE FROM ".P."alias WHERE alias_id="._.$_GET['delete_alias_id']._."")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
}

$url = $_SERVER['PHP_SELF'];// общий урл

$str = '';

if($q = mysql_query("SELECT alias_id, k, v FROM ".P."alias ORDER BY v")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<form action="'.$url.'?alias_id='.$r['alias_id'].'" method="post"><tr>
			<td><input name="k" type="text" value="'.htmlspecialchars($r['k']).'"></td>
			<td><input name="v" type="text" value="'.htmlspecialchars($r['v']).'"></td>
			<td><input class="button" type="submit" value="Сохранить"></td>
			<td><a href="'.$url.'?delete_alias_id='.$r['alias_id'].'" onclick="return checkDel(this);" title="Удалить"><img src="images/elements/del.gif"></a></td>
		</tr></form>';
	}
}

echo $_SERVER['SITE_HEADER'].Head('Псевдонимы шаблонов').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="50%"><b>Имя файла</b></td>
		<td width="50%"><b>Псевдоним</b></td>
		<td width="1" colspan=2><b>Действия</b></td>
	</tr>
	<form action="'.$url.'" method="post"><tr>
		<td><input name="k" type="text" value=""></td>
		<td><input name="v" type="text" value=""></td>
		<td colspan=2><input class="submit" type="submit" value="Добавить"></td>
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
});
</script>
</body>
</html>