<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
if($q = mysql_query("SELECT s.to_domain_id, d.domain, COUNT(DISTINCT s.visitor_id) AS visitors, COUNT(*) AS visits, 
COUNT(DISTINCT s.from_path_id) AS from_paths, COUNT(DISTINCT s.to_path_id) AS to_paths, s.visitor_id 
FROM ".P."statistics AS s
LEFT JOIN ".P."statistics_domains AS d ON d.domain_id = s.to_domain_id 
WHERE s.from_domain_id = '".$domains['domain_id']."' AND s.config_id = '15' AND s.date_time BETWEEN '".$min."' AND '".$max."'
GROUP BY s.to_domain_id ORDER BY visitors DESC, visits DESC")){
	
	while($r = mysql_fetch_assoc($q)){
		
		$str .= '<tr>
			<td><p><input type="text" value="'.htmlspecialchars(unescape($r['domain'])).'" readonly></p></td>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p>'.$r['from_paths'].'</p></td>
			<td><p>'.$r['to_paths'].'</p></td>
			<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].(($r['visitors']=='1')?'visitor.php?visitor_id='.$r['visitor_id'] : 'visitors.php?affiliate_id='.$r['to_domain_id']).$date.'" target="_blank">Подробнее»</a></p></td>
		</tr>';
		
		$all['a1']++;
		$all['a2'] += $r['visitors'];
		$all['a3'] += $r['visits'];
		$all['a4'] += $r['from_paths'];
		$all['a5'] += $r['to_paths'];
		
	}
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Популярность переходов на другие сайты','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').'
'.($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>На аффилат</b></td>
			<td width="100"><b>Ушедших</b></td>
			<td width="100"><b>Уходов</b></td>
			<td width="100"><b>С страниц</b></td>
			<td width="100"><b>На страниц</b></td>
			<td width="90"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="6" align="center"><p><b>Итог:</b></p></td>
		</tr>
		<tr>
			<td><p>'.$all['a1'].'</p></td>
			<td><p>'.$all['a2'].'</p></td>
			<td><p>'.$all['a3'].'</p></td>
			<td><p>'.$all['a4'].'</p></td>
			<td><p>'.$all['a5'].'</p></td>
			<td>&nbsp;</td>
		</tr>
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>

<script type="text/javascript">

$(":text").addClass("input_text").attr("title","Двойной клик откроет сайт").css("cursor","pointer").click(function(){
	this.select();
}).dblclick(function(){
	window.open("http://" + this.value);
});

mouseMovementsClass("overflowTable");

</script>
</body>
</html>
