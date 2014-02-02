<?php
include_once('../../libraries/access.php');
$times_max_minus_day = $times_min_minus_day = 1;
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------

//$_SERVER['SYSTEM']['amcharts_height'] = 855;

$xml = $GLOBALS['system_cache']-> file_().'.xml';// кэш-файл хмл-данных
$e = explode($_SERVER['DOCUMENT_ROOT'], $xml);
$_SERVER['SYSTEM']['amcharts_xml'] = $e['1'];// путь к кэш-файлу хмл-данных от корня сайта

$amcharts_settings_siteroot = $GLOBALS['system_cache']-> file_().'.settings.xml';// файл настроек отображения графика
$e = explode($_SERVER['DOCUMENT_ROOT'], $amcharts_settings_siteroot);
$_SERVER['SYSTEM']['amcharts_settings_siteroot'] = $e['1'];

write_('<settings><grid><x><approx_count>9</approx_count></x></grid><graphs><graph gid="1"><title>Пользователей</title><selected>true</selected></graph><graph gid="2"><title>Активных</title><selected>true</selected></graph></graphs></settings>', $amcharts_settings_siteroot);

//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_('SELECT user_id, user_regdate, user_type FROM '.F.'users 
WHERE user_type IN (0,3) AND user_regdate BETWEEN '._.$min._.' AND '._.$max._.' ORDER BY user_id')){
	
	$visitor_id = $visits = $visitors = $paths = $all = $inactive = array();
	
	$xml_series = $xml_graph_1 = $xml_graph_2 = '';
	
	while($r = mysql_fetch_assoc($q)){
		
		$day = date("d.m.Y", $r['user_regdate']);
		
		$visits[ $day ]++;
		
		$visitor_id[ $day ][ $r['user_id'] ]++;
		
		if($r['user_type']){// 1 - бездействующий
			$inactive[ $day ]++;
		}
	}
	
	if($visits){
		
		$i = 0;
		
		foreach($visits as $day => $count){
			
			$visitors = count($visitor_id[ $day ]);
			
			$inactive_count = (int)$inactive[ $day ];
			
			$str .= '<tr>
				<td><p>'.$day.'</p></td>
				<td><p>'.$visitors.'</p></td>
				<td><p>'.$inactive_count.'</p></td>
			</tr>';
			
			$all['visitors'] += $visitors;
			$all['visits'] += $inactive_count;
			$all['days'][ $day ]++;
			
			$xml_series  .= '<value xid="'.$i.'">'.$day.'</value>';
			$xml_graph_1 .= '<value xid="'.$i.'">'.$visitors.'</value>';
			$xml_graph_2 .= '<value xid="'.$i.'">'.$inactive_count.'</value>';
			
			$i++;
		}
		
		$str .= '<tr class="yaproSortTR">
			<td colspan="3" align="center"><p><b>Суммы:</b></p></td>
		</tr>
		<tr>
			<td><p>дней: '.count($all['days']).'</p></td>
			<td><p>'.$all['visitors'].'</p></td>
			<td><p>'.$all['visits'].'</p></td>
		</tr>';
		
		write_('<?xml version="1.0" encoding="UTF-8"?><chart><series>'.$xml_series.'</series><graphs><graph gid="1">'.$xml_graph_1.'</graph><graph gid="2">'.$xml_graph_2.'</graph></graphs></chart>', $xml);
	}
	
}
//---------------------------------------------------------------------------------------------------

$right = '<td style="white-space:nowrap"><form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td width="1" style="white-space: nowrap;">&nbsp;'.$time_select.'</td>
			<td width="1"><input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>';

echo $_SERVER['SITE_HEADER'].amcharts($str).Head('Динамика регистрации пользователей на сайте','',$right).'
'.($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Дата</b></td>
			<td><b>Пользователей</b></td>
			<td title="Подтвердивших регистрацию"><b>Активных</b></td>
		</tr>
		'.$str.'
	</table>':'<p style="padding:10px">За выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
.HeadPluse TD { padding: 3px; }
</style>

<script type="text/javascript">
mouseMovementsClass("overflowTable");
</script>
</body>
</html>
