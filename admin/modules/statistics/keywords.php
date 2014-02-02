<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$search_id = array(
'1' => 'Yandex',
'2' => 'Google',
'3' => 'Rambler',
'4' => 'Aport',
'5' => 'Mail',
'6' => 'Yahoo',
'7' => 'Live');
//---------------------------------------------------------------------------------------------------
$where = "WHERE s.to_domain_id = '".$domains['domain_id']."' AND s.keyword_id != '0' AND s.date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
if($q = mysql_query("SELECT COUNT(DISTINCT s.visitor_id) AS visitors, COUNT(*) AS visits, k.keyword, s.visitor_id, s.keyword_id 
	FROM ".P."statistics AS s
	LEFT JOIN ".P."statistics_keywords AS k ON k.keyword_id = s.keyword_id
	".$where." GROUP BY s.keyword_id ORDER BY visitors DESC, visits DESC")){
	
	while($r = mysql_fetch_assoc($q)){
		
		$str .= '<tr>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p><input type="text" value="'.htmlspecialchars($r['keyword']).'" readonly></p></td>
			<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].(($r['visitors']=='1')?'visitor.php?visitor_id='.$r['visitor_id'] : 'visitors.php?keyword_id='.$r['keyword_id']).$date.'">Подробнее»</a></p></td>
		</tr>';
		
		$all['visitors_sum'] += $r['visitors'];
		$all['visits'] += $r['visits'];
		$all['keywords']++;
	}
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT visitor_id) FROM ".P."statistics AS s ".$where.""));
	$all['visitors'] = $spec['0'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Популярность входа на сайт по поисковым фразам','','<td style="white-space:nowrap">
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
			<td width="100"><b>Переходов</b></td>
			<td><b>По фразе</b></td>
			<td width="90"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="5" align="center"><p><b>Сумма:</b> '.$all['visitors'].' чел.</p></td>
		</tr>
		<tr>
			<td><p>'.$all['visitors_sum'].'</p></td>
			<td><p>'.$all['visits'].'</p></td>
			<td><p>'.$all['keywords'].'</p></td>
			<td>&nbsp;</td>
		</tr>
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
	$(":text").addClass("input_text").dblclick(function(){ this.select(); });
});
</script>
</body>
</html>
