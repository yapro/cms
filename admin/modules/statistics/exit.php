<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$where = "WHERE to_domain_id = '".$domains['domain_id']."' AND config_id = '12' AND date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_query("SELECT to_path_id, visitor_id FROM ".P."statistics ".$where." ORDER BY id DESC")){
	
	$in = '';
	
	$visitors_check = $paths = array();
	
	while($r = mysql_fetch_assoc($q)){
		
		if($visitors_check[ $r['visitor_id'] ]){ continue; }
		
		$visitors_check[ $r['visitor_id'] ]++;
		
		$paths[ $r['to_path_id'] ][ $r['visitor_id'] ]++;
		
		$in .= $r['to_path_id'].',';
	}
	
	if($in && $q = mysql_query("SELECT * FROM ".P."statistics_paths WHERE path_id IN (".substr($in,0,-1).")")){
		
		$path = array();
		
		while($r = mysql_fetch_assoc($q)){
			
			$path[ $r['path_id'] ] = $r['path'];
			
		}
		
		arsort($paths);
		
		foreach($paths as $path_id => $arr_visitors){
			
			$count = count($arr_visitors);
			
			$str .= '<tr>
				<td><p>'.$count.'</p></td>
				<td><p><input type="text" value="'.htmlspecialchars($path[ $path_id ]).'" readonly></p></td>
				<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].(($count=='1')?'visitor.php?visitor_id='.key($arr_visitors) : 'visitors.php?to_path_id='.$path_id).$date.'">Подробнее»</a></p></td>
			</tr>';
			
		}
	}
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Выходы с страниц сайта','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Вышедших</b></td>
			<td><b>с URL</b></td>
			<td width="1"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="3" align="center"><p><b>Итог:</b></p></td>
		</tr>
		<tr>
			<td width="1"><p>&nbsp;</p></td>
			<td><p>'.count($paths).'</p></td>
			<td width="1">&nbsp;</td>
		</tr>
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
	$(":text").addClass("input_text").click(function(){
		this.select();
	}).dblclick(function(){
		window.open('http://<?php echo $GLOBALS['SYSTEM']['config']['yapro_http_host']; ?>'+this.value);
	});
});
</script>
</body>
</html>
