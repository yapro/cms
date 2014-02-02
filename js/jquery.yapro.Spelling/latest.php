<?php
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

// скрипт работает только в случае включения в браузере javascript
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции

if($_POST['text']){// шпион сообщений - отправляет отчет при размещеннии нового комментария на JID-модератора
	
	$message = $_SERVER['HTTP_REFERER']."\n".
		$_SERVER['REMOTE_ADDR']." :\n".
		"описание ошибки: ".htmlspecialchars(trim($_POST['info']))."\n".
		"ошибка в тексте: ".htmlspecialchars(trim($_POST['pre'].' → '.$_POST['text'].' ← '.$_POST['suf']));
	
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
		if($email && !mail_('texterror@'.$_SERVER['HTTP_HOST'], 'Spelling', $email, 'Ошибка в тексте', $message)){
			error(__FILE__);
		}else{
			write_('---------Дата: '.date('H:i:s d.m.Y')."\n".$message,$_SERVER['DOCUMENT_ROOT'].'/uploads/spelling.log','a');
		}
	}
}
?>
