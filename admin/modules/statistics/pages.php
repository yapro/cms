<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$where = "WHERE s.to_domain_id = '".$domains['domain_id']."' AND s.config_id = '12' AND s.date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
$sql = "FROM ".P."statistics AS s 
LEFT JOIN ".P."statistics_paths AS p ON p.path_id = s.to_path_id 
".$where." GROUP BY s.to_path_id";
//-------------------------------------------------------
$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_query("SELECT COUNT(*) ".$sql));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_query("SELECT COUNT(DISTINCT s.visitor_id) AS visitors, COUNT(*) AS visits, p.path AS to_path, 
	MAX(s.date_time) AS time_latest, s.visitor_id, s.to_path_id ".$sql." ORDER BY visitors DESC, visits DESC LIMIT ".$start.', '.$max)){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p><input type="text" value="'.htmlspecialchars($r['to_path']).'" readonly></p></td>
			<td><p>'.date("d.m.Y H:i:s", $r['time_latest']).'</p></td>
			<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].(($r['visitors']=='1')?'visitor.php?visitor_id='.$r['visitor_id'] : 'visitors.php?to_path_id='.$r['to_path_id']).$date.'" target="_blank">Подробнее»</a></p></td>
		</tr>';
		$all['a1'] += $r['visitors'];
		$all['a2'] += $r['visits'];
		$all['a3']++;
		$all['a4'][ date("d.m.Y", $r['time_latest']) ] = true;
	}
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT visitor_id) FROM ".P."statistics AS s ".$where.""));
	$all['visitors_uniq'] = $spec['0'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Посещаемость страниц сайта','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="100"><b>Посетителей</b></td>
			<td width="100"><b>Посещений</b></td>
			<td><b>URL</b></td>
			<td width="125"><b>Последний раз</b></td>
			<td width="100"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="5" align="center"><p><b>Итог:</b> '.$all['visitors_uniq'].' чел. (за весь выбранный период)</p></td>
		</tr>
		<tr>
			<td width="100"><p>'.$all['a1'].'</p></td>
			<td width="100"><p>'.$all['a2'].'</p></td>
			<td><p><b>адресов:</b> '.$all['a3'].'</p></td>
			<td width="125"><p><b>дней:</b> '.count($all['a4']).'</p></td>
			<td width="100">&nbsp;</td>
		</tr>
	</table>'.$list:'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

mouseMovementsClass("overflowTable");

$(":text").addClass("input_text").attr("title","Двойной клик откроет страницу").css("cursor","pointer").click(function(){
	this.select();
}).dblclick(function(){
	window.open('http://<?php echo $GLOBALS['SYSTEM']['config']['yapro_http_host']; ?>'+this.value);
});

</script>
</body>
</html>
