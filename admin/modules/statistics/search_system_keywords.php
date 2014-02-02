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
$where = "WHERE s.to_domain_id = '".$domains['domain_id']."' AND s.keyword_id != '0' AND s.date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
if($q = mysql_query("SELECT s.from_search_system, d.domain, COUNT(DISTINCT s.from_path_id) AS from_paths, 
	COUNT(DISTINCT s.visitor_id) AS visitors, COUNT(*) AS visits, k.keyword, s.keyword_id
	FROM ".P."statistics AS s
	LEFT JOIN ".P."statistics_domains AS d ON d.domain_id = s.from_domain_id
	LEFT JOIN ".P."statistics_keywords AS k ON k.keyword_id = s.keyword_id
	".$where." GROUP BY s.from_search_system, s.from_domain_id, s.keyword_id ORDER BY visitors DESC, visits DESC")){
	
	while($r = mysql_fetch_assoc($q)){
		
		$str .= '<tr>
			<td><p>'.$search_systems[ $r['from_search_system'] ].'</p></td>
			<td><p>'.$r['domain'].'</p></td>
			<td><p>'.$r['from_paths'].'</p></td>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p><input type="text" value="'.htmlspecialchars($r['keyword']).'" readonly></p></td>
		</tr>';
		
		$all['search_systems'][ $r['from_search_system'] ]++;
		$all['from_domains'][ $r['domain'] ]++;
		$all['from_paths'] += $r['from_paths'];
		$all['visitors'] += $r['visitors'];
		$all['visits'] += $r['visits'];
		$all['keywords']++;
		$all['keywords_uniq'][ $r['keyword_id'] ]++;
	}
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT visitor_id) FROM ".P."statistics AS s ".$where.""));
	$all['visitors_uniq'] = $spec['0'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Популярность поисковых систем и ключевых фраз','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="75"><b>С системы</b></td>
			<td width="125"><b>С подсистемы</b></td>
			<td width="75"><b>С cтраниц</b></td>
			<td width="90"><b>Посетителей</b></td>
			<td width="85"><b>Посещений</b></td>
			<td><b>По фразае</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="6" align="center"><p><b>Сумма:</b></p></td>
		</tr>
		<tr>
			<td><p>'.count($all['search_systems']).'</p></td>
			<td><p>'.count($all['from_domains']).'</p></td>
			<td><p>'.$all['from_paths'].'</p></td>
			<td><p>'.$all['visitors'].' ('.$all['visitors_uniq'].' чел.)</p></td>
			<td><p>'.$all['visits'].'</p></td>
			<td><p>'.$all['keywords'].' (уникальных: '.count($all['keywords_uniq']).')</p></td>
		</tr>
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
mouseMovementsClass("overflowTable");
$(":text").addClass("input_text").dblclick(function(){ this.select(); });
</script>
</body>
</html>
