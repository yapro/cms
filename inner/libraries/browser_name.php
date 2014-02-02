<?
// скрипт создает переменную $browser_name в которую помещает сокращенное имя браузера или поискового бота

if(!class_exists('Browser')){
	include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/Browser.php');// подгрузка необходимых cookies функций
}

$browser_name = ($GLOBALS['browser']-> getBrowser()=='unknown')? '' : $GLOBALS['browser']-> getBrowser();

$file = $_SERVER['DOCUMENT_ROOT'].'/cache/bots.php';// формируем имя кэш-файла

if(is_file($file)){
	
	include_once($file);
	
}else if($q = mysql_query("SELECT bot_agent FROM ".F."bots WHERE bot_active=1")){
	$bots = array();
	while($r=mysql_fetch_assoc($q)){
		$bots[] = $r['bot_agent'];
	}
	if($bots){
		write_('<?php '.$GLOBALS["phpToStr"]->toStr($bots, 'bots').' ?>', $file);
	}
}

if($bots){
	foreach($bots as $bot_agent){
		if(mb_stristr($_SERVER['HTTP_USER_AGENT'], $bot_agent)){
			$browser_name = $bot_agent;
			break;// если бот определен - прекращаем итерацию
		}
	}
}
?>
