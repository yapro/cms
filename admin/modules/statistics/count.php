<?php
include_once('../../libraries/access.php');
$times_max_minus_day = $times_min_minus_day = 1;
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$where = "WHERE to_domain_id = '".$domains['domain_id']."' AND config_id = '12' AND date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
$str = '';

$get_day = 'week';
if(isset($_GET['day'])){ $get_day  = (int)$_GET['day']; }// делаем возможность просмотра данных только за определенный день

$xml = $GLOBALS['system_cache']-> file_('', $min.'.'.$max.'.'.$get_day.'.xml');

$html = $GLOBALS['system_cache']-> file_('', $min.'.'.$max.'.'.$get_day.'.html');

$_SERVER['SYSTEM']['amcharts_settings'] = 'count.xml';
$_SERVER['SYSTEM']['amcharts_xml'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $xml);

if(is_file($xml) && is_file($html) && $max < time()){
	
	$str = @file_get_contents($html);
	
}else if($q = mysql_query("SELECT date_time, visitor_id, to_path_id FROM ".P."statistics ".$where." ORDER BY id")){
	
	$visitor_id = $to_path_id = $visits = $visitors = $paths = $all = array();
	
	$xml_series = $xml_graph_1 = $xml_graph_2 = $xml_graph_3 = '';
	
	while($r = mysql_fetch_assoc($q)){
		
		$day = date("d.m.Y", $r['date_time']);
		
		$visitor_id[ $day ][ $r['visitor_id'] ]++;
		$visits[ $day ]++;
		$to_path_id[ $day ][ $r['to_path_id'] ]++;
		
	}
	if($visits){
		
		$i = 0;
		
		$date_w = array(1=>'Пн.',2=>'Вт.',3=>'Ср.',4=>'Чт.',5=>'Пт.',6=>'Сб.',0=>'Вс.');
		
		foreach($visits as $day => $count){
			
			$w = date("w", strtotime($day));
			
			if(isset($_GET['day']) && $w!=$get_day){ continue; }// делаем возможность просмотра данных только за определенный день
			
			$visitors = count($visitor_id[ $day ]);
			
			$paths = count($to_path_id[ $day ]);
			
			$str .= '<tr>
				<td><p'.(($w==6 || $w==0)? ' style="color:#FF0000"':'').'>'.$day.' <a href="'.$url.'&day='.$w.'">'.$date_w[ $w ].'</a></p></td>
				<td><p>'.$visitors.'</p></td>
				<td><p>'.$count.'</p></td>
				<td><p>'.$paths.'</p></td>
				<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'visitors.php?nothing='.$date.'">Подробнее»</a></p></td>
			</tr>';
			
			$all['visitors'] += $visitors;
			$all['visits'] += $count;
			$all['paths'] += $paths;
			$all['days'][ $day ]++;
			
			$xml_series  .= '<value xid="'.$i.'">'.$day.'</value>';
			$xml_graph_1 .= '<value xid="'.$i.'">'.$visitors.'</value>';
			$xml_graph_2 .= '<value xid="'.$i.'">'.$count.'</value>';
			$xml_graph_3 .= '<value xid="'.$i.'">'.$paths.'</value>';
			
			$i++;
		}
		
		
		$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT visitor_id), COUNT(DISTINCT to_path_id) FROM ".P."statistics ".$where));
		$all['visitors_uniq'] = $spec['0'];
		$all['paths_uniq'] = $spec['1'];
		
		if($str){
			
			$str .= '
			<tr class="yaproSortTR">
				<td colspan="5" align="center"><p><b>Суммы:</b></p></td>
			</tr>
			<tr>
				<td><p>дней: '.count($all['days']).'</p></td>
				<td><p>'.(int)$all['visitors'].' ('.(int)$all['visitors_uniq'].' уникал.)</p></td>
				<td><p>'.(int)$all['visits'].'</p></td>
				<td><p>'.(int)$all['paths'].' ('.(int)$all['paths_uniq'].' уникал.)</p></td>
				<td width="100">&nbsp;</td>
			</tr>';
			
		}
		if($max < time()){ write_($str, $html); }
		
		write_('<?xml version="1.0" encoding="UTF-8"?><chart><series>'.$xml_series.'</series><graphs><graph gid="1">'.$xml_graph_1.'</graph><graph gid="2">'.$xml_graph_2.'</graph><graph gid="3">'.$xml_graph_3.'</graph></graphs></chart>', $xml);
		
	}else{
		$_SERVER['SYSTEM']['amcharts_xml'] = $_SERVER['SYSTEM']['MODULE_DIR_PATH'].'/blank.xml';
	}
}
if(isset($_GET['day'])){ $url .= '&day='.(int)$_GET['day']; }
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].amcharts($str).Head('Посещаемость сайта','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>Дата</b></td>
			<td><b>Посетителей</b></td>
			<td><b>Посещений</b></td>
			<td><b>Страниц</b></td>
			<td width="100"><b>Действия</b></td>
		</tr>
		'.$str.'
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">

mouseMovementsClass("overflowTable");

$(".overflowTable tr").each(function(){
	var a = $("A:first", this);
	$(a).attr("title", "Посмотреть только " + $(a).text() );
});

</script>
</body>
</html>
