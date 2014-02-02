<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
$visitor_id = $_POST['visitor_id']? (int)$_POST['visitor_id'] : ($_GET['visitor_id']? (int)$_GET['visitor_id'] : false);
//---------------------------------------------------------------------------------------------------
if($_GET['bot_id'] && is_numeric($_GET['bot_id'])){ $bot_id = $_GET['bot_id']; }
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2.($bot_id? '&bot_id='.$bot_id : '&visitor_id='.$visitor_id );
//---------------------------------------------------------------------------------------------------
$where = "WHERE ".($bot_id? "s.bot_id = '".$bot_id."'" : "s.visitor_id='".(int)$visitor_id."'")." AND s.date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
if($q = mysql_query("SELECT s.id, s.from_domain_id, df.domain AS from_domain, pf.path AS from_path, s.from_search_system, k.keyword, 
	s.date_time, s.to_domain_id, dt.domain AS to_domain, pt.path AS to_path, s.config_id, s.id
	FROM ".P."statistics AS s
	LEFT JOIN ".P."statistics_domains AS df ON df.domain_id = s.from_domain_id
	LEFT JOIN ".P."statistics_paths AS pf ON pf.path_id = s.from_path_id
	LEFT JOIN ".P."statistics_paths AS pt ON pt.path_id = s.to_path_id 
	LEFT JOIN ".P."statistics_domains AS dt ON dt.domain_id = s.to_domain_id
	LEFT JOIN ".P."statistics_keywords AS k ON k.keyword_id = s.keyword_id
	".$where." ORDER BY date_time")){
	
	$search_system['1'] = 'Yandex';
	$search_system['2'] = 'Google';
	$search_system['3'] = 'Rambler';
	$search_system['4'] = 'Aport';
	$search_system['5'] = 'Mail';
	$search_system['6'] = 'Yahoo';
	$search_system['7'] = 'Msn / Live';
	$search_system['8'] = 'Bing';
	
	while($r = mysql_fetch_assoc($q)){
		
		$from_my_site = ($r['from_domain_id']==$domains['domain_id'])? true : false;
		
		$from_site_name = $from_my_site? 'страницы текущего сайта' : $r['from_domain'];
		
		if($r['from_path'] && substr($r['from_path'],0,7)=='http://' || substr($r['from_path'],0,8)=='https://'){
			
			preg_match_all('/http(s|):\/\/([-a-z0-9\.]+\.[a-z0-9\.\:]+)\/(.*)/i', $r['from_path'], $found);
			if($found['2']['0']){// если путь имеет вид - protocol://site.ru/путь.html
				$r['from_path'] = '/'.$found['3']['0'];
			}else{
				// проверяем на тестовые домены вида localhost, например http://test/page.php
				preg_match_all('/http(s|):\/\/([-a-z0-9\.]+)\/(.*)/i', $r['from_path'], $found);
				if($found['2']['0']){
					$r['from_path'] = '/'.$found['3']['0'];
				}
			}
		}
		
		$from_a = '<a href="http://'. $r['from_domain'].str_replace('"', '&quot;', $r['from_path']).'" target="_blank">';
		
		$from = $from_a.$from_site_name.'</a>';
		
		$to_site_name = ($r['to_domain_id']==$domains['domain_id'])? 'страницы текущего сайта' : $r['to_domain'];
		
		$to = '<a href="http://'. $r['to_domain'].$r['to_path'].'" target="_blank">'.$to_site_name.'</a>';
		
		if($r['config_id']=='12'){// посещаемость страниц
			
			if($r['from_search_system']){// c поисковой системы
				
				$search_text = !$r['keyword']? '' : 'по ключевой фразе <span style="color: #FF0000">'.htmlspecialchars($r['keyword']).'</span>';
				
				$action = 'пришел с поисковой системы '.$from_a.$search_system[ $r['from_search_system'] ].'</a> '.$search_text.' на страницу:';
				
			}else if($r['from_domain_id'] != $domains['domain_id'] && $from_site_name){// с какого-то сайта
				
				$action = 'пришел с сайта '.$from.' на страницу:';
				
			}else if($r['from_domain_id']){// с текущего сайта
				
				$action = 'посещение страницы:';
				
			}else{// с закладки или избранного
				
				$action = 'пришел с закладки (избранного) на страницу:';
			}
			
		}else if($r['config_id']=='13'){// переходы на несуществующие страницы
			
			if($r['from_domain_id']){
				
				$action = 'неправильное обращение с сайта '.$from.' на страницу:';
				
			}else{
				
				$action = 'неправильное обращение с закладки или избранного';
				
			}
			
		}else if($r['config_id']=='14'){// выходы (покидания и закрытия страниц)
			
			$action = 'закрыл или ушел с страницы:';
			
		}else if($r['config_id']=='15'){// переходы на аффилаты (уходы на чужие сайты)
			
			$action = 'ушел на сайт:';
			
		}else if($r['config_id']=='16'){// скачивание файлов
			
			$action = 'скачал файл:';
			
		}else if($r['config_id']=='17'){// индексирование поисковыми ботами
			
			$action = 'индексирование ботом страницы:';
			
		}else{
			continue;
		}
		
		if($from_my_site){
			
			if($r['to_domain_id']==$domains['domain_id']){
				
				if($r['config_id']=='14'){// выходы (покидания и закрытия страниц)
					
					$input = $r['from_path'];
					
				}else{
					
					$input = $r['to_path'];
					
				}
				
			}else{
				$input = "http://".$r['to_domain'].$r['to_path'];
			}
			
		}else{
			$input = ( ($r['to_domain_id']==$domains['domain_id'])? '' : "http://".$r['to_domain']).$r['to_path'];
		}
		
		$str .= '<tr>
			<td class="nowrap" title="ID:'.$r['id'].'"><p>'.date("d.m.Y H:i:s", $r['date_time']).'</p></td>
			<td><p>'.$action.'</p></td>
			<td><p><input type="text" value="'.htmlspecialchars($input).'"></p></td>
		</tr>';
		
		$all['days'][ date("d.m.Y", $r['date_time']) ] = true;
		$all['configs'][ $r['config_id'] ] = true;
		$all['to_paths'][ $r['to_path'] ] = true;
		$all['actions']++;
		
	}
	
	if($bot_id){
		
		$user_info = '';
		
	}else{
		
		$r = mysql_fetch_assoc(mysql_query("SELECT v.browser_id, v.flash_version, v.java_on, v.ip, sy.system, sc.screen 
		FROM ".P."statistics_visitors AS v
		LEFT JOIN ".P."statistics_systems AS sy ON sy.system_id = v.system_id
		LEFT JOIN ".P."statistics_screens AS sc ON sc.screen_id = v.screen_id
		WHERE v.visitor_id='".$visitor_id."'"));
		
		$browser['0'] = 'Не определен';
		$browser['1'] = 'Opera';
		$browser['2'] = 'Safari';
		$browser['3'] = 'Firefox';
		$browser['4'] = 'Internet Explorer';
		$browser['5'] = 'Chrome';
		
		$ip = long2ip($r['ip']);
		
		$user_info = '<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td align="center" style="padding:5px">
		<b>Браузер:</b> '.$browser[ $r['browser_id'] ].'
		&nbsp;&nbsp;&nbsp;&nbsp;<b>Flash:</b> '.($r['flash_version']? $r['flash_version'] : 'отключен').'
		&nbsp;&nbsp;&nbsp;&nbsp;<b>Java:</b> '.($r['java_on']? 'включена' : 'отключена').'
		&nbsp;&nbsp;&nbsp;&nbsp;<b>Операционная система:</b> '.($r['system']? $r['system'] : '<font color="#FF0000">не определена</font>').'
		&nbsp;&nbsp;&nbsp;&nbsp;<b>Разрешение экрана:</b> '.($r['screen']? $r['screen'] : '<font color="#FF0000">не определено</font>').'
		&nbsp;&nbsp;&nbsp;&nbsp;<b>IP адрес:</b> <a href="https://www.nic.ru/whois/?query='.$ip.'" target="_blank">'.$ip.'</a>
				</td>
			</tr>
		</table>';
		
	}
}
//---------------------------------------------------------------------------------------------------
if($bot_id){
	$r = mysql_fetch_assoc(mysql_query("SELECT bot_agent FROM ".F."bots WHERE bot_id = '".$bot_id."'"));
	$head = 'Индексирование страниц ботом: '.$r['bot_agent'];
}else{
	$head = 'Действия посетителя под номером: '.$visitor_id.($r['screen']? '' : ' / <font color="#FF0000">возможно робот</font>');
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head($head,'','<td style="white-space:nowrap">
<form action="'.$url.'" method="post">
	<input type="hidden" value="'.$visitor_id.'" name="visitor_id">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center" style="padding:3px">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').($str? $user_info.'
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Дата</b></td>
			<td><b>Действие</b></td>
			<td width="50%"><b>По отношению к чему выполнено действие</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="3" align="center"><p><b>Итог: '.$all['actions'].' действий</b></p></td>
		</tr>
		<tr>
			<td><p>за дней: '.count($all['days']).'</p></td>
			<td><p>вариантов действий: '.count($all['configs']).'</p></td>
			<td><p>вариантов url: '.count($all['to_paths']).'</p></td>
		</tr>
	</table>
<style type="text/css">
.nowrap {
	white-space:nowrap
}
</style>
<script type="text/javascript">
mouseMovementsClass("overflowTable");
$("input:text").addClass("input_text").click(function(){
	
	this.select();
	
}).dblclick(function(){
	
	if(this.value.substr(0,7)=="http://" || this.value.substr(0,8)=="https://"){
		
		window.open(this.value)
		
	}else{
		window.open("http://'.$GLOBALS['SYSTEM']['config']['yapro_http_host'].'"+this.value);
	}
});
</script>':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
</body>
</html>
