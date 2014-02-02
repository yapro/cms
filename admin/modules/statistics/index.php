<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------

if($q=mysql_query("SELECT s.bot_id, b.bot_name, COUNT(DISTINCT s.to_path_id) AS paths, MIN(s.date_time) AS start, MAX(s.date_time) AS end 
	FROM ".P."statistics AS s 
	LEFT JOIN ".F."bots AS b ON b.bot_id = s.bot_id
	WHERE s.to_domain_id = '".$domains['domain_id']."' AND s.config_id = '17' AND s.date_time BETWEEN '".$min."' AND '".$max."'
	GROUP BY s.bot_id ORDER BY b.bot_name")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td><p>'.$r['bot_name'].'</p></td>
			<td width="90"><p>'.$r['paths'].'</p></td>
			<td width="150"><p>'.date ("d.m.Y H:i:s", $r['start']).'</p></td>
			<td width="165"><p>'.date ("d.m.Y H:i:s", $r['end']).'</p></td>
			<td width="90"><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'visitor.php?bot_id='.$r['bot_id'].$date.'">Подробнее»</a></p></td>
		</tr>';
		$all['a1']++;
		$all['a2'] += $r['paths'];
	}
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Индексация поисковыми ботами','','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>Имя бота</b></td>
			<td width="90"><b>Cтраниц</b></td>
			<td width="150"><b>Первое посещение</b></td>
			<td width="165"><b>Последнее посещение</b></td>
			<td width="90"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="6" align="center"><p><b>Итог:</b></p></td>
		</tr>
		<tr>
			<td><p>'.$all['a1'].'</p></td>
			<td><p>'.$all['a2'].'</p></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
mouseMovementsClass("overflowTable");
</script>
</body>
</html>
