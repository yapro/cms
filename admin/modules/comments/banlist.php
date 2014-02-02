<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю
//---------------------------------------------------------------------------------------------------------------------------------------
// удаляю
if(access('d') && F && $_GET['delete_ban_id'] 
	&& mysql_("DELETE FROM ".F."banlist WHERE ban_id="._.$_GET['delete_ban_id']._."")){
		$_SERVER['SYSTEM']['MESSAGE'] .= 'Удаление выполнено';
}

// модуль собирает в массив информацию о существующих пользователях
if(F && $q = mysql_("SELECT user_id, username FROM ".F."users WHERE user_inactive_time = "._._."")){
	while($r=mysql_fetch_assoc($q)){
		$user[$r['user_id']] = $r['username'];
	}
}
$str = '';
$strings = 0;
$users = $ips = $ids = array();
if($q = mysql_query("SELECT * FROM ".F."banlist ORDER BY ban_id DESC")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td><p>'.((F && $r['ban_userid'] && $user[$r['ban_userid']])?'<a href="http://'.$GLOBALS['SYSTEM']['config']['yapro_http_host'].'/forum/memberlist.php?mode=viewprofile&u='.$r['ban_userid'].'" target="_blank">':'').(($r['ban_userid'] && $user[$r['ban_userid']])?htmlspecialchars($user[$r['ban_userid']]) : 'Гость').'</a></p></td>
			<td><p><a href="https://www.nic.ru/whois/?query='.$r['ban_ip'].'" target="_blank">'.$r['ban_ip'].'</a></p></td>
			<td><p>'.$r['ban_id'].'</p></td>
			<td><p><a href="'.$_SERVER['REQUEST_URI'].'&delete_ban_id='.$r['ban_id'].'">разбанить</a></p></td>
		</tr>';
		$users[ $r['ban_userid'] ]++;
		$ips[ $r['ban_ip'] ]++;
		$ids[ $r['ban_id'] ]++;
		$strings++;
	}
}

echo $_SERVER['SITE_HEADER'].Head('Просмотр забанненных пользователей, IP-адресов и отмена бана', '', '', 'http://yapro.ru/documents/video/rabota-s-kommentariyami-banlistom-i-smaylami.html' ).($str?'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>Пользователь</b></td>
		<td width="125"><b>IP-адрес</b></td>
		<td width="100"><b>ID</b></td>
		<td width="100"><b>Действие</b></td>
	</tr>
	'.$str.'
	<tr>
		<td colspan=4 style="text-align:center"><b>Итог:</b></td>
	</tr>
	<tr>
		<td><b>'.count($users).'</b></td>
		<td><b>'.count($ips).'</b></td>
		<td><b>'.count($ids).'</b></td>
		<td><b>'.$strings.'</b></td>
	</tr>
</table>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
});
</script>':'<p style="padding:10px">Данные не найдены.</p>');
?>

<script type="text/javascript">
$(document).ready(function(){

});
</script>
</body>
</html>
