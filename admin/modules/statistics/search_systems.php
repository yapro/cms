<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$search_systems = array(
'1' => 'Yandex',
'2' => 'Google',
'3' => 'Rambler',
'4' => 'Aport',
'5' => 'Mail',
'6' => 'Yahoo',
'7' => 'Live',
'8' => 'Bing');
//---------------------------------------------------------------------------------------------------
$where = "WHERE to_domain_id = '".$domains['domain_id']."' AND from_search_system != '0' AND date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
if($q = mysql_query("SELECT from_search_system, COUNT(DISTINCT from_domain_id) AS from_domains, COUNT(DISTINCT from_path_id) AS from_paths, 
	COUNT(DISTINCT visitor_id) AS visitors, COUNT(*) AS visits, COUNT(DISTINCT keyword_id) AS keywords, COUNT(DISTINCT to_path_id) AS to_paths
	FROM ".P."statistics ".$where." GROUP BY from_search_system ORDER BY visitors DESC, visits DESC, keywords DESC")){
	
	while($r = mysql_fetch_assoc($q)){
		
		$str .= '<tr>
			<td><p>'.$search_systems[ $r['from_search_system'] ].'</p></td>
			<td><p>'.$r['from_domains'].'</p></td>
			<td><p>'.$r['from_paths'].'</p></td>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p>'.$r['keywords'].'</p></td>
			<td><p>'.$r['to_paths'].'</p></td>
		</tr>';
		
		$all['search_systems']++;
		$all['from_domains'] += $r['from_domains'];
		$all['from_paths'] += $r['from_paths'];
		$all['visitors'] += $r['visitors'];
		$all['visits'] += $r['visits'];
		$all['keywords'] += $r['keywords'];
		$all['to_paths'] += $r['to_paths'];
	}
	
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT visitor_id), COUNT(DISTINCT from_path_id), COUNT(DISTINCT to_path_id), COUNT(DISTINCT keyword_id) FROM ".P."statistics ".$where));
	$all['visitors_uniq'] = $spec['0'];
	$all['from_paths_uniq'] = $spec['1'];
	$all['to_paths_uniq'] = $spec['2'];
	$all['keywords_uniq'] = $spec['3'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Популярность переходов с поисковых систем','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center" style="padding:3px">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>С систем</b></td>
			<td><b>С подсистем</b></td>
			<td><b>С cтраниц</b></td>
			<td><b>Посетителей</b></td>
			<td><b>Посещений</b></td>
			<td><b>По фразам</b></td>
			<td><b>На страниц</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="7" align="center"><p><b>Суммы:</b></p></td>
		</tr>
		<tr>
			<td><p>'.$all['search_systems'].'</p></td>
			<td><p>'.$all['from_domains'].'</p></td>
			<td><p>'.$all['from_paths'].' ('.$all['from_paths_uniq'].' уник.)</p></td>
			<td><p>'.$all['visitors'].' ('.$all['visitors_uniq'].' уник.)</p></td>
			<td><p>'.$all['visits'].'</p></td>
			<td><p>'.$all['keywords'].' ('.$all['keywords_uniq'].' уник.)</p></td>
			<td><p>'.$all['to_paths'].' ('.$all['to_paths_uniq'].' уник.)</p></td>
		</tr>
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
});
</script>
</body>
</html>
