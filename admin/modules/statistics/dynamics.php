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
$_GET['to_path_id'] = (int)$_GET['to_path_id'];

if($_GET['to_path_id']){
	
	$_POST['search'] = $_POST['search']? $_POST['search'] : id_path($_GET['to_path_id']);
	
}else if($_POST['search']){
	
	$_GET['to_path_id'] = path_id($_POST['search']);
	
}

if(!$_GET['to_path_id']){ $_GET['to_path_id'] = -1; }
//---------------------------------------------------------------------------------------------------
$where = "WHERE to_domain_id = ".$domains['domain_id']." AND to_path_id = ".$_GET['to_path_id']." AND date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
$str = '';

$xml = $GLOBALS['system_cache']-> file_('', $min.".".$max.'.xml');// кэш-файл хмл-данных
$html = $GLOBALS['system_cache']-> file_('', $min.".".$max.'.html');// кэш-файл ХТМЛ данных

$_SERVER['SYSTEM']['amcharts_settings'] = 'count.xml';// файл настроек отображения графика
$_SERVER['SYSTEM']['amcharts_xml'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $xml);// путь к кэш-файлу хмл-данных от корня сайта
//---------------------------------------------------------------------------------------------------
if(is_file($xml) && is_file($html) && $max < time() && $zzz){
	
	$str = @file_get_contents($html);
	
}else if($q = mysql_query("SELECT date_time, visitor_id FROM ".P."statistics ".$where." ORDER BY id")){
	
	$visitor_id = $visits = $visitors = $paths = $all = array();
	
	$xml_series = $xml_graph_1 = $xml_graph_2 = '';
	
	while($r = mysql_fetch_assoc($q)){
		
		$day = date("d.m.Y", $r['date_time']);
		
		$visitor_id[ $day ][ $r['visitor_id'] ]++;
		$visits[ $day ]++;
		
	}
	//print_r($visits); exit;
	if($visits){
		
		$i = 0;
		
		foreach($visits as $day => $count){
			
			$visitors = count($visitor_id[ $day ]);
			
			$str .= '<tr>
				<td><p>'.$day.'</p></td>
				<td><p>'.$visitors.'</p></td>
				<td><p>'.$count.'</p></td>
				<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'visitors.php?to_path_id='.$_GET['to_path_id'].$date.'">Подробнее»</a></p></td>
			</tr>';
			
			$all['visitors'] += $visitors;
			$all['visits'] += $count;
			$all['days'][ $day ]++;
			
			$xml_series  .= '<value xid="'.$i.'">'.$day.'</value>';
			$xml_graph_1 .= '<value xid="'.$i.'">'.$visitors.'</value>';
			$xml_graph_2 .= '<value xid="'.$i.'">'.$count.'</value>';
			
			$i++;
		}
	}
	
	$visitors_uniq = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT visitor_id) FROM ".P."statistics ".$where));
	
	$str .= '<tr class="yaproSortTR">
		<td colspan="5" align="center"><p><b>Суммы:</b></p></td>
	</tr>
	<tr>
		<td><p>дней: '.count($all['days']).'</p></td>
		<td><p>'.$all['visitors'].' ('.$visitors_uniq['0'].' уникал.)</p></td>
		<td><p>'.$all['visits'].'</p></td>
		<td width="100">&nbsp;</td>
	</tr>';
	
	if($max < time()){ write_($str, $html); }
	
	write_('<?xml version="1.0" encoding="UTF-8"?><chart><series>'.$xml_series.'</series><graphs><graph gid="1">'.$xml_graph_1.'</graph><graph gid="2">'.$xml_graph_2.'</graph></graphs></chart>', $xml);
	
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].($all['visitors']? amcharts($str) : '').Head('Динамика посещаемости URL с ID: '.$_GET['to_path_id']).'
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="HeadPluse">
		<tr class="yaproSortTR">
			<td width="1" style="white-space: nowrap;">&nbsp;'.$time_select.'</td>
			<td width="1">&nbsp;&nbsp;&nbsp;<b>URL:</b></td>
			<td>
				<input type="text" value="'.htmlspecialchars($_POST['search']).'" name="search" class="input_text" style="width: 100%; _margin-bottom: 3px;">
			</td>
			<td width="1"><input type="submit" value="Найти"></td>
		</tr>
	</table>
</form>
'.($all['visitors']?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>Дата</b></td>
			<td><b>Посетителей</b></td>
			<td><b>Посещений</b></td>
			<td width="100"><b>Действия</b></td>
		</tr>
		'.$str.'
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
.HeadPluse TD { padding: 3px; }
</style>

<script type="text/javascript">
mouseMovementsClass("overflowTable");
</script>
</body>
</html>
