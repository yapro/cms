<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$where = "WHERE date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
if(access('w') && $_POST['ignore']){
	foreach($_POST['ignore'] as $id => $checked){
		mysql_query("UPDATE ".P."files_downloads SET ignored_hash = '".$_POST['ignored_hash'][$id]."' WHERE id = ".(int)$id."");
	}
	$_SERVER['SYSTEM']['MESSAGE'] = 'Обновление списка игнорирования успешно выполнено!';
}
//---------------------------------------------------------------------------------------------------
$sql = "FROM ".P."files_downloads ".$where." GROUP BY to_domain, to_path";
//-------------------------------------------------------
$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_query("SELECT COUNT(*) ".$sql));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_query("SELECT id, COUNT(DISTINCT from_path) AS from_paths, to_domain, to_path, MAX(date_time) AS latest_time, COUNT(DISTINCT ip) AS visitors, COUNT(*) AS visits 
	".$sql." ORDER BY visitors DESC, visits DESC LIMIT ".$start.', '.$max)){
	
	// Игнорируемый страницы
	if($q2 = mysql_query("SELECT id FROM ".P."files_downloads WHERE path_ignored = 1")){
		while($r = mysql_fetch_assoc($q2)){
			$path_ignored[ $r['id'] ] = true;
		}
	}
	
	while($r = mysql_fetch_assoc($q)){
		
		$file = (($r['to_domain']==$GLOBALS['SYSTEM']['config']['yapro_http_host'])? '' : $r['to_domain']).$r['to_path'];
		
		$str .= '<tr>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p>'.$r['from_paths'].'</p></td>
			<td><p><input type="text" readonly value="'.htmlspecialchars($file).'"></p></td>
			<td><p>'.date("d.m.Y H:i:s", $r['latest_time']).'</p></td>
			<td><p>
				'.($path_ignored[ $r['id'] ]?'':'<input type="checkbox" name="ignore['.$r['id'].']">
				<input type="hidden" name="ignored_hash['.$r['id'].']" value="'.md5($r['to_domain'].$r['to_path']).'"> ').'
				<a href="#" alt="'.$r['id'].'">Подробнее»</a></p></td>
		</tr>';
		$all['a2'] += $r['visits'];
		$all['a4'][$file] = true;
		$all['a5'][date("d.m.Y", $r['latest_time'])] = true;
	}
	
	// находим кол-во уникальных посетителей
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT ip) FROM ".P."files_downloads ".$where));
	$all['a1'] = $spec['0'];
	
	// находим кол-во уникальных страниц
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT from_path) FROM ".P."files_downloads ".$where));
	$all['a3'] = $spec['0'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Скачивание файлов с сайта','','<td style="white-space:nowrap"><form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').'
'.($str? '<form action="'.$url.'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Посетителей</b></td>
			<td width="1"><b>Скачиваний</b></td>
			<td width="1"><b>С&nbsp;страниц</b></td>
			<td><b>Файл</b></td>
			<td width="125"><b>Последний раз</b></td>
			<td width="110"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="5" align="center"><p><b>Итог:</b></p></td><td align="center"><p><input type="submit" value="&#8593; Игнорировать"></p></td>
		</tr>
		<tr>
			<td><p>'.$all['a1'].'</p></td>
			<td><p>'.$all['a2'].'</p></td>
			<td><p>'.$all['a3'].'</p></td>
			<td><p>'.count($all['a4']).'</p></td>
			<td><p>'.count($all['a5']).'</p></td>
			<td>&nbsp;</td>
		</tr>
	</table></form>'.$list.'
<script type="text/javascript">
$(document).ready(function(){
});
</script>
':'<p style="padding:10px">За выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

$(":text").addClass("input_text").click(function(){this.select()}).dblclick(function(){
	window.open( "http://" + ( (this.value.substr(0, 1)=="/")? "<?=$GLOBALS['SYSTEM']['config']['yapro_http_host']?>" : "") + this.value);
}).css("cursor","pointer").each(function(){
	$(this).attr("title", $(this).val() );
});

$(":checkbox").css("vertical-align","middle");

mouseMovementsClass("overflowTable");

</script>
</body>
</html>
