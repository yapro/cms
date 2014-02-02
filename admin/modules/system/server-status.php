<?php
include_once('../../libraries/access.php');

$doc = $GLOBALS['SYSTEM']['config']['yapro_server_status']? file_get_contents($GLOBALS['SYSTEM']['config']['yapro_server_status']) : '';

$standart = spliti('</head><body>', $doc);
$standart = str_replace('</body></html>', '', $standart['1']);
//echo $doc; exit;

$ScoreboardKeys['_'] = 'Ожидание подключения';// Waiting for Connection - Ожидание подключения
$ScoreboardKeys['S'] = 'Начинаю работу с данными';//  Starting up - Начинаю работу с данными
$ScoreboardKeys['R'] = 'Выполняю чтение запроса';//  Reading Request - Выполняю чтение запроса
$ScoreboardKeys['W'] = 'Отправляю ответ';//  Sending Reply - Отправляю ответ
$ScoreboardKeys['K'] = 'Поддерживаю чтение';//  Keepalive (read) - Поддерживаю чтение
$ScoreboardKeys['D'] = 'Поиск доменной системы имен';//  DNS Lookup - Поиск доменной системы имен
$ScoreboardKeys['C'] = 'Закрываю подключение';//  Closing connection - Закрываю подключение
$ScoreboardKeys['L'] = 'Регистрирую подключения';//  Logging - Регистрирую подключения
$ScoreboardKeys['G'] = 'Успешно закрываю подключение';//  Gracefully finishing - Успешно закрываю подключение
$ScoreboardKeys['I'] = 'Очищаю данные успешо выполненного запроса';//  Idle cleanup of worker - Очищаю данные успешо выполненного запроса
$ScoreboardKeys['.'] = 'Подключился без процесса';//  Open slot with no current process - Выполнил подключение без процесса

$docX = preg_replace('/<dt>Parent Server Generation(.*)<\/dt>\n/sUi', '~SPLIT~', $doc);

$ParentServerGeneration = explode('~SPLIT~', $docX);

$ServerBuilt = spliti('<dt>Server Built: ', $ParentServerGeneration['0']);
$ServerBuilt = spliti('</dt></dl><hr /><dl>', $ServerBuilt['1']);

$RestartTime = spliti('<dt>Restart Time: ', $ParentServerGeneration['0']);
$RestartTime = spliti('</dt>', $RestartTime['1']);

$Serveruptime = spliti('<dt>Server uptime: ', $ParentServerGeneration['1']);
$Serveruptime = spliti('</dt>', $Serveruptime['1']);

$ScoreboardKey = spliti('<p>Scoreboard Key:<br />', $ParentServerGeneration['1']);

$HeadTable = spliti('<table border="0"><tr><th>Srv</th><th>PID</th><th>Acc</th><th>M</th><th>CPU
</th><th>SS</th><th>Req</th><th>Conn</th><th>Child</th><th>Slot</th><th>Client</th><th>VHost</th><th>Request</th></tr>', $ScoreboardKey['1']);

$Symbols = spliti('<hr /> <table>', $HeadTable['1']);

$Bottom = spliti('</table>', $Symbols['1']);

preg_match_all('/<tr><td><b>(.*)<\/b><\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td>(.*)<\/td><td nowrap>(.*)<\/td><td nowrap>(.*)<\/td><\/tr>/sUi', $Symbols['0'], $tr);

$url_link = $_SERVER['PHP_SELF'].'?nothing';// общий урл

if($_GET['order']=='Asite'){
	$sorted_array = $tr['12'];
}else{// ip
	$sorted_array = $tr['11'];
}
asort($sorted_array);

foreach($sorted_array as $k=>$true){

	$type = str_replace("\n", '', strip_tags($tr['4'][$k]));
	
	$tr['13'][$k] = str_replace("&amp;", '&', $tr['13'][$k]);
	
	if(substr($tr['12'][$k],0,4)=='xn--'){// поддержка кирилических доменов
		$tr['12'][$k] = $IDN->decode($tr['12'][$k]);
	}

	$ip = $tr['11'][$k];
	
	$str .= '<tr>
		<td><p>'.'<a href="http://www.nic.ru/whois/?query='.$ip.'" target="_blank">'.(($ip==$_SERVER['REMOTE_ADDR'])?'<span style="color: #FF0000">Вы: </span>':'').$ip.'</a></p></td>
		<td><p><input type="text" value="'.htmlspecialchars($tr['12'][$k]).'"></p></td>
		<td><p><input type="text" value="'.htmlspecialchars($tr['13'][$k]).'"></p></td>
		<td><p><input type="text" value="'.htmlspecialchars($ScoreboardKeys[$type]).'"></p></td>
		<td><p>'.$tr['6'][$k].' сек.</p></td>
		<td><p>'.$tr['5'][$k].'</p></td>
		<td><p>'.$tr['2'][$k].'</p></td>
	</tr>';
	// 1. <td style="width: 175px"><p>'.$tr['10'][$k].'</p></td>
	$a['1'][$ip]++;
	$a['2'][$tr['12'][$k]]++;
	$a['3'] += $tr['5'][$k];
	$a['4'][$tr['12'][$k].' '.$tr['13'][$k]]++;
	$a['5'][$type]++;
	// 1. $a['7'][$ip] += $tr['10'][$k];
	$a['8'][$ip] += $tr['6'][$k];
	
	
	$url = explode(' ', $tr['13'][$k]);
	$url = explode('?', $url['1']);
	$a['9'][ $tr['12'][$k].' '.$url['0'] ]++;// кол-во подключений по урл с учетом доменного имени
	$a['10'][ $url['0'] ]++;// кол-во подключений по урл (без учетом доменного имени)
	
	$a['11'][ $tr['12'][$k].' '.$url['0'] ] += $tr['5'][$k];// загруженность процессора по урл с учетом доменного имени
	$a['12'][ $url['0'] ] += $tr['5'][$k];// загруженность процессора по урл (без учетом доменного имени)
	
	// виды страниц и кол-во IP по ним
	$a['6'][$tr['12'][$k].' '.$tr['13'][$k]] [$ip]++;
	$a['6'][ $tr['12'][$k].' '.$url['0'] ] [$ip]++;
	$a['6'][ $url['0'] ] [$ip]++;
}

if($a){
	arsort($a['1']);
	foreach($a['1'] as $ip=>$count){
		$s['1'] .= '<option>'.$count.' - '.(($ip==$_SERVER['REMOTE_ADDR'])?'Ваш IP':$ip).'</option>';
	}
	arsort($a['2']);
	foreach($a['2'] as $host=>$count){
		$s['2'] .= '<option>'.$count.' - '.$host.'</option>';
	}
	arsort($a['4']);
	foreach($a['4'] as $page=>$count){
		$s['4'] .= '<option>'.$count.' подкл. с '.count($a['6'][$page]).' IP : '.$page.'</option>';
	}
	$s['4'] .= '<option>------- без учета вида подключения и GET-данных -------</option>';
	arsort($a['9']);
	foreach($a['9'] as $page=>$count){
		$s['4'] .= '<option>'.$count.' подкл. с '.count($a['6'][$page]).' IP : '.$page.' (CPU: '.$a['11'][ $page ].')</option>';
	}
	$s['4'] .= '<option>------ без учета вида подключения, GET-данных и доменного имени --------</option>';
	arsort($a['10']);
	foreach($a['10'] as $page=>$count){
		$s['4'] .= '<option>'.$count.' подкл. с '.count($a['6'][$page]).' IP : '.$page.' (CPU: '.$a['12'][ $page ].')</option>';
	}

	arsort($a['5']);
	foreach($a['5'] as $type=>$count){
		$s['5'] .= '<option>'.$count.' - '.$ScoreboardKeys[$type].'</option>';
	}

	// 1. arsort($a['7']);
	// 1. foreach($a['7'] as $ip=>$count){
	// 1. 	$s['6'] .= '<option>'.$count.' - '.$ip.'</option>';
	// 1. }

	arsort($a['8']);
	foreach($a['8'] as $ip=>$count){
		$s['7'] .= '<option>'.$count.' - '.(($ip==$_SERVER['REMOTE_ADDR'])?'Ваш IP':$ip).'</option>';
	}
}

echo $_SERVER['SITE_HEADER'].Head('Активные IP подключения к сайтам Вашего сервера | сортировка : <a href="'.$url_link.getOrder('ip').'">ip</a> , <a href="'.$url_link.getOrder('site').'">сайт</a>',false,'','http://yapro.ru/web-master/apache/informaciya-o-servere-moduli-apache-mod_status.html').'
'.($str?'
<table border="0" cellspacing="1"  bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="135"><b>IP</b></td>
		<td width="125"><b>Сайт</b></td>
		<td><b>Адрес</b></td>
		<td width="145"><b>Вид действия</b></td>
		<td width="1" title="Время исполнение запроса"><b>Время исполнения</b></td>
		<td width="1 title="Центральный процессор" style="white-space: nowrap""><b>ЦП МГц</b></td>
		<td width="1 title="ID процесса" style="white-space: nowrap""><b>PID</b></td>
	</tr>
	'.$str.'
	<tr class="yaproSortTR">
		<td><p><select style="width: 140px;"><option>ip-адресов: '.count($a['1']).' шт.</option>'.$s['1'].'</select></p></td>
		<td><p><select style="width: 125px;"><option>сайтов: '.count($a['2']).' шт.</option>'.$s['2'].'</select></p></td>
		<td><p><select style="width: 300px;"><option>уникальных урл: '.count($a['4']).' шт.</option>'.$s['4'].'</select></p></td>
		<td><p><select style="width: 100px;"><option>видов: '.count($a['5']).' шт.</option>'.$s['5'].'</select></p></td>
		<td><p><select style="width: 115px;"><option>'.@array_sum($a['8']).' сек.</option>'.$s['7'].'</select></p></td>
		<td style="white-space: nowrap"><p>'.$a['3'].' МГц</p></td>
		<td style="white-space: nowrap"><p> </p></td>
	</tr>
	<tr><td colspan="7" align="center"><p><b>Итог:</b> '.count($tr['1']).' подключений</p></td></tr>
	<tr><td colspan="7"><p><b>Сервер запущен:</b> '.$ServerBuilt['0'].'</p></td></tr>
	<tr><td colspan="7"><p><b>Последний перезапуск:</b> '.$RestartTime['0'].'</p></td></tr>
	<tr><td colspan="7"><p><b>Время непрерывной работы:</b> '.$Serveruptime['0'].'</p></td></tr>
	<tr class="yaproSortTR"><td colspan="7"><p><b>Стандартный материал:</p></td></tr>
</table>
<div style="padding: 10px;">'.$standart.'</div>':'<p style="padding:10px">На Вашем сервере модуль mod_status отключен.</p>');
	// 1. <td><p>Мб передано</p></td>
	// 1. <td><p><select style="width: 165px"><option>'.array_sum($a['7']).' Мб</option>'.$s['6'].'</select></p></td>
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	$("input:text").addClass("input_text");
	mouseMovementsClass("overflowTable");
});
</script>
</body>
</html>
