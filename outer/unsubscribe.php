<?php
// скрипт отписки E-mail от всех рассылок сайта
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции

//----------------------------------------------------------------------------------

function unsubscribe20110130($email=''){
	if($email){
		$r = @mysql_fetch_assoc(mysql_('SELECT unsubscribe FROM '.P.'unsubscribe WHERE email = '._.$email._));
		if($r['unsubscribe']){
			return true;
		}
	}
}
//----------------------------------------------------------------------------------

$unsubscribe = false;

if($_GET['email']){// проверяем, может быть E-mail уже был отписан
	$unsubscribe = unsubscribe20110130( rawurldecode($_GET['email']) );
}
//----------------------------------------------------------------------------------
if(!$unsubscribe && $_GET['hash']){
	
	$ex = explode('.', $_GET['hash']);
	
	$r = @mysql_fetch_assoc(mysql_query('SELECT user_email FROM '.F.'users WHERE user_id = '.(int)$ex['0'].' AND user_type = 0 AND user_regdate = '.(int)$ex['1']));
	
	// если хэш подтверждения правилен
	if($r['user_email']){
		
		$unsubscribe = unsubscribe20110130($r['user_email']);// проверяем, может быть E-mail уже был отписан
		
		$set = 'unsubscribe = 1, uniq_hash = '._.$_GET['hash']._.', time_unsubscribe = '._.time()._.'';
		
		if(!$unsubscribe && mysql_('INSERT INTO '.P.'unsubscribe SET email = '._.$r['user_email']._.', '.$set.' ON DUPLICATE KEY UPDATE '.$set)){
			
			$unsubscribe = true;
			
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
