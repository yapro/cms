<?php
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

// скрипт работает только в случае включения в браузере javascript
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции

if($_POST['action']){// шпион Ajax ошибок - отправляет отчет при возникновении ошибки на JID-модератора
	
	$message = htmlspecialchars($_SERVER['HTTP_HOST'].' : '.$_POST['action']).' : see error log';
	
	$jabber = array();
	@include($_SERVER['DOCUMENT_ROOT'].'/jabber.php');
	if($jabber && $jabber['toJID']){
		include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/XMPPHP/XMPP.php');
		$conn = new XMPPHP_XMPP($jabber['server'], $jabber['port'], $jabber['nikname'], $jabber['password']);
		$conn->connect();
		$conn->processUntil('session_start');
		$conn->message($jabber['toJID'],$message);
	}else{
		@include(dirname(__FILE__).'/email.php');
		if($email && mail_('texterror@'.$_SERVER['HTTP_HOST'], 'Spelling', $email, 'Ошибка в тексте', $message)){
			error(__FILE__);
		}
	}
	
	$output = array();
	parse_str($_POST['data'], $output);
	
	echo error("action: ".htmlspecialchars(trim($_POST['action']))."\n"."data: ".print_r($output,1));
}
?>
