<?php
exit;// нужно переделать эту страницу

include_once('../../libraries/access.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$domain_id = (int)$domains['domain_id'];

// удаляю
if($domain_id && $_GET['config_id'] && access('d')){
	
	$where = '';
	
	if(is_numeric($_GET['config_id'])){
		$where = "(from_domain_id='".$domain_id."' OR to_domain_id='".$domain_id."') AND config_id = '".$_GET['config_id']."'";
	}
	
	if($_GET['config_id']=='refer'){
		$where = "to_domain_id = '".$domain_id."' AND from_domain_id NOT IN (0,".$domain_id.")";
	}
	
	if($_GET['config_id']=='from_search_system'){
		$where = "(from_domain_id='".$domain_id."' OR to_domain_id='".$domain_id."') AND from_search_system != '0'";
	}
	
	if($where || $_GET['config_id']=='all'){
		
		if($where){
			mysql_query("DELETE FROM ".P."statistics WHERE ".$where);
		}else{
			mysql_query("TRUNCATE TABLE ".P."statistics");
		}
		
		if($_GET['config_id']=='all'){
			
			mysql_query("DELETE FROM ".P."statistics_paths_ignored WHERE domain_id='".$domain_id."'");
			
			mysql_query("DELETE FROM ".P."statistics_visitors WHERE domain_id='".$domain_id."'");
		}
	}
	
	echo 'ajax_delete_ok'; exit;
}

$url = $_SERVER['REQUEST_URI'];// общий урл

echo $_SERVER['SITE_HEADER'].Head('Возможность удаления любого вида данных собранной статистики').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>Вид статистики</b></td>
			<td width="70"><b>Действие</b></td>
		</tr>
		<tr>
			<td><p>Статистика посещаемости пользователями</p></td>
			<td><p><a href="'.$url.'&config_id=12">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по неправильным 404 URL</p></td>
			<td><p><a href="'.$url.'&config_id=13">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по выходам и закрытиям страниц</p></td>
			<td><p><a href="'.$url.'&config_id=14">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по афилатам</p></td>
			<td><p><a href="'.$url.'&config_id=15">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по файлам</p></td>
			<td><p><a href="'.$url.'&config_id=16">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по индексированию ботами</p></td>
			<td><p><a href="'.$url.'&config_id=17">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по приходам с реффереров</p></td>
			<td><p><a href="'.$url.'&config_id=refer">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p>Статистика по приходам с поисковых систем по ключевым фразам</p></td>
			<td><p><a href="'.$url.'&config_id=from_search_system">Удалить</a></p></td>
		</tr>
		<tr>
			<td><p style="color: #FF0000;">Статистика по всем данным</p></td>
			<td><p><a href="'.$url.'&config_id=all">Удалить</a></p></td>
		</tr>
</table>';
?>
<style type="text/css">
.overflowTable A { color: #FF0000; }
</style>

<script type="text/javascript">
$(document).ready(function(){
	
	mouseMovementsClass("overflowTable");
	
	$(".overflowTable A").click(function(){
		var href = $(this).attr("href");
		jConfirm("Подтверждаете удаление?", function(r){
			if(r==true){
				$.fancybox.showActivity();
				$.get(href, function(r){
					$.fancybox.hideActivity();
					if(r=="ajax_delete_ok"){
						jAlert("Удаление выполнено");
					}else{
						jAlert("Недостаточно прав доступа");
					}
				});
			}
		}, null, "Да", "Нет");
		return false;
	});
});
</script>
</body>
</html>
