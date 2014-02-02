<?php
// скрипт отписки пользователя от рассылки новых комментариев со страниц сайта
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции

//----------------------------------------------------------------------------------

$unsubscribe = false;

if($_GET['hash']){
	
	$ex = explode('.', $_GET['hash']);
	
	$user_regdate = (int)$ex['1'];
	
	$user_id = (int)$ex['0'];
	
	$r = @mysql_fetch_assoc(mysql_query('SELECT user_email FROM '.F.'users WHERE user_id = '.$user_id.' AND user_type = 0 AND user_regdate = '.$user_regdate));
	
	// если хэш подтверждения правилен
	if($r['user_email']){
		
		$page_id = (int)$ex['2'];
		
		$unsubscribe_pages = array();
		
		if($q = mysql_query('SELECT page_id, unsubscribe FROM '.P.'subscribe_pages_comments WHERE user_id = '.$user_id)){
			while($r=mysql_fetch_assoc($q)){
				$unsubscribe_pages[ $r['page_id'] ] = $r['unsubscribe'];
			}
		}
		
		if($page_id){// отписка от отдельной страницы
			
			if(array_key_exists($page_id, $unsubscribe_pages)){// если E-mail был подписан
				if($unsubscribe_pages[ $page_id ] || mysql_query('UPDATE '.P.'subscribe_pages_comments SET unsubscribe = 1, time_unsubscribe = '.time().' WHERE page_id = '.$page_id.' AND user_id = '.$user_id)){
					$unsubscribe = true;
				}
			}else{// если E-mail не был подписан (на всякий случай)
				if(mysql_query('INSERT INTO '.P.'subscribe_pages_comments SET page_id = '.$page_id.', user_id = '.$user_id.', unsubscribe = 1, time_unsubscribe = '.time())){
					$unsubscribe = true;
				}
			}
		}else{// отписка от всех страниц
			
			if(mysql_query('UPDATE '.P.'subscribe_pages_comments SET unsubscribe = 1, time_unsubscribe = '.time().' WHERE user_id = '.$user_id)){
				$unsubscribe = true;
			}
			
		}
	}
}
//----------------------------------------------------------------------------------
@header("content-type:text/html; charset=UTF-8");
// хеадер отключаем, т.к. IE не показывает содержимое страницы @header("HTTP/1.0 500 Internal Server Error");
echo '<center style="padding-top: 150px;"><h2>'.($unsubscribe? 'Вы успешно отписаны.' : 'К сожалению, отписка не выполнена.<br>
Возникла проблема технического характера.<br>
Пожалуйста, сообщите нам об этом, на e-mail: <a href="mailto:'.$GLOBALS['SYSTEM']['config']['yapro_board_contact'].'">'.$GLOBALS['SYSTEM']['config']['yapro_board_contact'].'</a>').'</h2></center>';
?>
