<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------
// удаляю
if(access('d') && $_GET['delete_path_id'] && is_numeric($_GET['delete_path_id'])
	&& mysql_('UPDATE '.P.'pages_404 SET path_ignored = 0 WHERE id = '.(int)$_GET['delete_path_id'])){
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
}

$str = '';
if($q = mysql_('SELECT id, path_404 FROM '.P.'pages_404 WHERE path_ignored = 1')){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td><p><a href="'.$_SERVER['REQUEST_URI'].'&delete_path_id='.$r['id'].'" onclick="return checkDel(this);" title="Удалить"><img src="images/elements/del.gif"></a></p></td>
			<td><p><input name="v" type="text" value="'.htmlspecialchars($r['path_404']).'" readonly></p></td>
		</tr>';
	}
}

echo $_SERVER['SITE_HEADER'].Head('Неправильные URL, которые игнорируются').($str?'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="25"><p>&nbsp;</p></td>
		<td><b>Страница</b></td>
	</tr>
	'.$str.'
</table>':'<p style="padding:10px">Таковых страниц не существует.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
$("input:text").addClass("input_text");
mouseMovementsClass("overflowTable");
</script>
</body>
</html>
