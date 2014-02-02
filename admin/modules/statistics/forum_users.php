<?php
include_once('../../libraries/access.php');
$times_max_minus_day = $times_min_minus_day = 1;
include_once('../../libraries/times.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$str = $xml_series = $xml_graph_1 = $xml_graph_2 = '';
if($q = mysql_('SELECT user_id, user_regdate, user_type FROM '.F.'users 
WHERE user_type IN (0,3) AND user_regdate BETWEEN '._.$min._.' AND '._.$max._.' ORDER BY user_id')){
	
	$visitor_id = $visits = $visitors = $paths = $all = $inactive = array();
	
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
			
			$xml_series  .= $day.'/';
			$xml_graph_1 .= $visitors.'/';
			$xml_graph_2 .= $inactive_count.'/';
			
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

echo $_SERVER['SITE_HEADER'].($xml_series?'<div align="center"><img src="/outer/graphic.php?array='.$xml_graph_1.'!'.$xml_graph_2.'!'.$xml_series.'"></div>':'').Head('Динамика регистрации пользователей на сайте','',$right).'
'.($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Дата</b></td>
			<td><b>Пользователей</b></td>
			<td><b>Неподтвердивших регистрацию</b></td>
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