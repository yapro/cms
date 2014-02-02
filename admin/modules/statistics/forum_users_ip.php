<?php
include_once('../../libraries/access.php');
$times_max_minus_day = $times_min_minus_day = 1;
include_once('../../libraries/times.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_('SELECT user_ip, COUNT(*) as c FROM '.F.'users 
WHERE user_type IN (0,3) AND user_regdate BETWEEN '._.$min._.' AND '._.$max._.' GROUP BY user_ip ORDER BY c DESC')){
	
	$ip = $id = 0;
	
	while($r = mysql_fetch_assoc($q)){
		
		$str .= '<tr>
			<td><p><a href="https://www.nic.ru/whois/?query='. htmlspecialchars($r['user_ip']).'" target="_blank">'.htmlspecialchars($r['user_ip']).'</p></td>
			<td><p>'.$r['c'].'</p></td>
		</tr>';
		
		$ip++;
		
		$id += $r['c'];
	}
	
	if($str){
		
		$str .= '<tr class="yaproSortTR">
			<td colspan="2" align="center"><p><b>Суммы:</b></p></td>
		</tr>
		<tr>
			<td><p>'.$ip.'</p></td>
			<td><p>'.$id.' чел.</p></td>
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

echo $_SERVER['SITE_HEADER'].Head('Популярность IP-адресов при регистрации пользователей','',$right).'
'.($str?'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>IP-адрес</b></td>
			<td><b>Количество зарегистрированных</b></td>
		</tr>
		'.$str.'
	</table>':'<p style="padding:10px">За выбранный период времени данные не найдены.</p>');
?>
<!--<p style="padding:10px"><b>Подсказка:</b> если какие-то боты регистрируют липовых пользователей на определенные домены, то регистрацию с этих доменов можно заблокировать, если внести домены в список файла /inner/user_reg_bad_domains.php.</p> -->
<style type="text/css">
.HeadPluse TD { padding: 3px; }
</style>

<script type="text/javascript">
mouseMovementsClass("overflowTable");
</script>
</body>
</html>
