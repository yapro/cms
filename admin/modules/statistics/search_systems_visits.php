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
$str = '';

$xml = $GLOBALS['system_cache']-> file_('', $min.".".$max.'.xml');
$html = $GLOBALS['system_cache']-> file_('', $min.".".$max.'.html');

$_SERVER['SYSTEM']['amcharts_settings'] = 'search_systems_visitors.xml';
$_SERVER['SYSTEM']['amcharts_xml'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $xml);
//---------------------------------------------------------------------------------------------------
if(is_file($xml) && is_file($html) && $max < time() && $XXX){
	
	$str = @file_get_contents($html);
	
}else if($q = mysql_query("SELECT date_time, from_search_system FROM ".P."statistics WHERE 
to_domain_id = '".$domains['domain_id']."' AND from_search_system != '0' AND date_time BETWEEN '".$min."' AND '".$max."'")){
	
	$search_system = $visits_count = $xml_graph = array();
	
	$fields = $footer = $xml_series = $xml_graphs = $visits_sum = '';
	
	while($r = mysql_fetch_assoc($q)){
		
		$day = date("d.m.Y", $r['date_time']);
		
		$search_system[ $r['from_search_system'] ] = true;
		
		$visits_count[ $day ][ $r['from_search_system'] ]++;
		
		$visits[ $r['from_search_system'] ]++;
		
	}
	if($search_system){
		
		$i = 0;
		
		ksort($visits_count);
		
		foreach($visits_count as $day => $from_search_system__visits){// данные по текущему дню с поисковых систем
			
			$str .= '<tr><td><p>'.$day.'</p></td>';
			
			foreach($search_system as $from_search_system => $true){// проверяем каждую поисковую систему
				
				$count = (int)$from_search_system__visits[ $from_search_system ];
				
				$str .= '<td><p>'.$count.'</p></td>';
				
				$xml_graph[ $from_search_system ] .= '<value xid="'.$i.'">'.$count.'</value>';
				
				$visits_sum += $count;
			}
			$str .= '</tr>';
			
			$xml_series  .= '<value xid="'.$i.'">'.$day.'</value>';
			
			$i++;
		}
		
		$fields = '<td><p><b>Дата:</b></p></td>';
		$footer = '<td><p>дней: '.$i.'</p></td>';
		$colspan = 1;
		
		foreach($search_system as $from_search_system => $true){// создаем поля всех задействованных поисковых систем
			
			$colspan++;
			
			$fields .= '<td><p><b>'.$search_systems[ $from_search_system ].'</b></p></td>';
			
			$footer .= '<td><p>'.$visits[ $from_search_system ].'</p></td>';
		}
		
		if($xml_graph){
			foreach($xml_graph as $from_search_system => $xml_str){
				$xml_graphs .= '<graph gid="'.$from_search_system.'">'.$xml_str.'</graph>';
			}
		}
		
		$str = '<tr class="yaproSortTR">'.$fields.'</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="'.$colspan.'" align="center"><p><b>Сумма: '.$visits_sum.'</b></p></td>
		</tr>
		<tr>'.$footer.'</tr>';
		
		if($max < time()){ write_($str, $html); }
		
		write_('<?xml version="1.0" encoding="UTF-8"?><chart><series>'.$xml_series.'</series><graphs>'.$xml_graphs.'</graphs></chart>', $xml);
		
	}else{
		$_SERVER['SYSTEM']['amcharts_xml'] = $_SERVER['SYSTEM']['MODULE_DIR_PATH'].'/blank.xml';
	}
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].amcharts($str).Head('Популярность посещений с поисковых систем','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		'.$str.'
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
mouseMovementsClass("overflowTable");
</script>
</body>
</html>
