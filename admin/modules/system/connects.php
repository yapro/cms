<?php
include_once('../../libraries/access.php');

$doc = '';
ob_start();
@passthru('netstat -an | less');
$command_str = ob_get_contents();
ob_end_clean();
if($command_str){
	
	$exA = explode("Active UNIX", $command_str);
	if($exA['0']){
		$exN = explode("\n", $exA['0']);
		foreach($exN as $str){
			if(mb_substr($str, 0, 3)=='tcp'){
				$str = preg_replace("/[\s]{2,}/", " ", $str);
				$exS = explode(" ", $str);
				$exC = explode(":", $exS['4']);
				
				// иногда порт от IP разделяют точкой
				if($exC['0'] && !$exC['1']){ $exD = explode(".", $exC['0']); $exC['0']=$exD['0'].'.'.$exD['1'].'.'.$exD['2'].'.'.$exD['3']; }
				
				if($exC['0']){ $ip_connects[$exC['0']]++; $ip_actions[$exC['0']][$exS['5']]++; }
			}
		}
		if($ip_connects){
			arsort($ip_connects);
			foreach($ip_connects as $ip=>$connects){
				
				$all['0'] += $connects;
				$all['1']++;
				$all['2'] += $ip_actions[$ip]['LISTEN'];
				$all['3'] += $ip_actions[$ip]['SYN_SENT'];
				$all['4'] += $ip_actions[$ip]['SYN_RECEIVED'];
				$all['5'] += $ip_actions[$ip]['ESTABLISHED'];
				$all['6'] += $ip_actions[$ip]['TIME_WAIT'];
				$other = ($connects - $ip_actions[$ip]['LISTEN'] - $ip_actions[$ip]['SYN_SENT'] - $ip_actions[$ip]['SYN_RECEIVED'] - $ip_actions[$ip]['ESTABLISHED'] - $ip_actions[$ip]['TIME_WAIT']);
				$all['7'] += $other;
				
				if($ip==$_SERVER['REMOTE_ADDR']){
					$ip_info = '<span style="color: #FF0000">Вы: '.$_SERVER['REMOTE_ADDR'].'<span>';
				}else if($ip==$_SERVER["SERVER_ADDR"]){
					$ip_info = '<span style="color: #FF0000">Данный сервер: '.$_SERVER['REMOTE_ADDR'].'<span>';
				}else{
					$ip_info = $ip;
				}
				
				$doc .= '<tr>
					<td><a href="https://www.nic.ru/whois/?query='.$ip.'" target="_blank">'.$ip_info.'</a></td>
					<td>'.$ip_actions[$ip]['LISTEN'].'</td>
					<td>'.$ip_actions[$ip]['SYN_SENT'].'</td>
					<td>'.$ip_actions[$ip]['SYN_RECEIVED'].'</td>
					<td>'.$ip_actions[$ip]['ESTABLISHED'].'</td>
					<td>'.$ip_actions[$ip]['TIME_WAIT'].'</td>
					<td>'.$other.'</td>
				</tr>';
			}
		}
	}
}
if(!$doc){
	$doc = '<tr><td>На Вашем хостнге запрещено определение данной информации</td></tr>';
}else{
	$doc .= '<tr>
			<td colspan="7" align="center"><b>Статусов действия: '.(int)$all['0'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Суммарные итоги:</b></td>
		</tr>
		<tr>
			<td>'.$all['1'].'</td>
			<td>'.$all['2'].'</td>
			<td>'.$all['3'].'</td>
			<td>'.$all['4'].'</td>
			<td>'.$all['5'].'</td>
			<td>'.$all['6'].'</td>
			<td>'.$all['7'].'</td>
		</tr>';
}

$name = 'Активные IP подключения к вашему серверу и их статусы на данный момент';
$buttons[] = _.'images/logotypes/m-www.png'._;

echo $_SERVER['SITE_HEADER'].Head($name, $buttons).'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr>
		<td>IP-адрес</td>
		<td width="90" title="LISTEN-Ожидает входящих соединений">В ожидании</td>
		<td width="70" title="SYN_SENT-Активно пытается установить соединение">Попыток</td>
		<td width="110" title="SYN_RECEIVED-Идет начальная синхронизация соединения">Синхронизации</td>
		<td width="110" title="ESTABLISHED-Соединение установлено">Установленных</td>
		<td width="110" title="TIME_WAIT-Ожидание закрытия после передачи данных">Завершающихся</td>
		<td width="100" title="Другие статусы соединения">Другие</td>
	</tr>'.$doc.'
</table>
<script type="text/javascript" src="http://'.$_SERVER['HTTP_HOST'].'/js/jquery.yaproTableHeadSort.js" charset="utf-8"></script>';
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
	$(".overflowTable").yaproTableHeadSort({HeadRows:1, BottomRows:2, SortTD:"5D"});
});
</script>
</body>
</html>
