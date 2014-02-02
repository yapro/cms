<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю
include_once('../../libraries/pages_permissions.php');
//---------------------------------------------------------------------------------------------------------------------------------------
if(access('d') && $_GET['delete_permission_id'] && $GLOBALS['pages_permissions']-> delete($_GET['delete_permission_id']) ){// удаляю
	echo 'ajax_delete_ok'; exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
// нахожу существующие данные
if($q = mysql_query("SELECT permission_id, name FROM ".P."pages_permissions ORDER BY permission_id DESC")){
	while($r=mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td><p>'.htmlspecialchars($r['name']).'</p></td>
			<td width="65"><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'permissions.php?permission_id='.$r['permission_id'].'">Настроить</a></p></td>
			<td width="20"><p><img class="delete cursor" alt="'.$r['permission_id'].'" src="images/elements/del.gif"></a></p></td>
		</tr>';
	}
}

$b[] = $_SERVER['SYSTEM']['URL_MODULE_DIR'].'permissions.php'._.'images/elements/plus.gif'._.'Добавить настройку доступа';

echo $_SERVER['SITE_HEADER'].Head('Управление настройками доступа к страницам',$b).'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>Имя настройки доступа</b></td>
		<td width="85" colspan=2><b>Действия</b></td>
	</tr>'.$str.'
</table>';
?>
<style type="text/css">
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }
.delete { cursor:  }
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	mouseMovementsClass("overflowTable");
	
	// обрабатываем удаление
	$(".delete").attr("title","Удалить").click(function(){
		var element = this;
		var permission_id = $(this).attr("alt");
		jConfirm("Подтверждаете удаление?", function(r) {
			if(r==true){
				$.get("<?php echo $_SERVER['REQUEST_URI'].'&delete_permission_id='; ?>"+permission_id, function(msg){
					if(msg=="ajax_delete_ok"){
						$(element).closest("TR").remove();
					}else{
						jAlert("Недостаточно прав");
					}
				});
			}
		});
	});
});
</script>
</body>
</html>
