<?php
include_once('../../libraries/access.php');
//-----------------------------------------------------------
// сохраняю
if(access('w') && $_POST['name_old'] && $_POST['name'] && $_POST['name']
&& mysql_("UPDATE ".P."settings SET name="._.$_POST['name']._.", value="._.$_POST['value']._.", 
title="._.$_POST['title']._." "._."WHERE"._." name="._.$_POST['name_old']._."")){
	
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись сохранена';
	
}

// добавляю
if(access('w') && !$_POST['name_old'] && $_POST['name'] && mysql_("INSERT INTO ".P."settings SET 
name="._.$_POST['name']._.", value="._.$_POST['value']._.", title="._.$_POST['title']._)){
	
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	
}

// удаляю
if(access('d') && $_GET['delete_name'] && mysql_("DELETE FROM ".P."settings WHERE name="._.$_GET['delete_name']._)){
	
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
	
}
//-----------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл
//-----------------------------------------------------------
if($q = mysql_query("SELECT * FROM ".P."settings ORDER BY title")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<form action="'.$url.'" method="post"><tr>
			<td><p><input name="title" type="text" value="'.htmlspecialchars($r['title']).'"></p></td>
			<td><p><input name="value" type="text" value="'.htmlspecialchars($r['value']).'"></p></td>
			<td><p>
				<input name="name" type="text" value="'.htmlspecialchars($r['name']).'">
				<input name="name_old" type="hidden" value="'.htmlspecialchars($r['name']).'">
			</p></td>
			<td width="1"><p><input class="button" type="submit" value="Сохранить"></p></td>
			<td width="1"><p><a href="'.$url.'?delete_name='.$r['name'].'" onclick="return checkDel(this);" title="Удалить"><img src="images/elements/del.gif"></a></p></td>
		</tr></form>';
	}
}
//-----------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head($_SERVER['SYSTEM']['MESSAGE']? $_SERVER['SYSTEM']['MESSAGE'] : 'Настройки').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>Описание</b></td>
		<td width="15%"><b>Значение</b></td>
		<td width="25%"><b>Имя</b></td>
		<td width="1" colspan=2><b>Действия</b></td>
	</tr>
	<form action="'.$url.'" method="post"><tr>
		<td><p><input name="title" type="text" value=""></p></td>
		<td><p><input name="value" type="text" value=""></p></td>
		<td><p><input name="name" type="text" value=""></p></td>
		<td colspan=2><p><input class="button" type="submit" value="Добавить"></p></td>
	</tr></form>
	'.$str.'
</table>';
?>
<style type="text/css">

</style>
<script type="text/javascript">
$("input:text").addClass("input_text").each(function(){
	$(this).attr("title", this.value);
});
mouseMovementsClass("overflowTable");

<?php if($_POST){ echo 'if(window.parent.left_frame){ window.parent.left_frame.location.reload(); }'; } ?>

</script>
</body>
</html>
