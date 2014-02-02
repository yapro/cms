<?php
// плагин собирает данные о перенаправлениях на сайте

include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/browser_name.php');// узнаем имя браузера - переменная $browser_name

if($GLOBALS['SYSTEM']['config']['yapro_log_301'] && $error_message){
	
	log_('header301: выполняется перенаправление с страницы '.$_SERVER['REQUEST_URI']."\n".
	'с страницы urldecode: '.urldecode($_SERVER['REQUEST_URI'])."\n".
	'на страницу http://'.$_SERVER['HTTP_HOST'].$path."\n".
	'реферер: '.$_SERVER['HTTP_REFERER']."\n".
	'реферер urldecode: '.urldecode($_SERVER['HTTP_REFERER'])."\n".
	'Пояснение: '.$error_message."\n".
	'IP: '.$_SERVER['REMOTE_ADDR']."\n".
	'браузер: '.$browser_name.' '.(int)$GLOBALS['browser']-> getVersion()."\n".
	'полностью: '.$_SERVER['HTTP_USER_AGENT'],
	$_SERVER['DOCUMENT_ROOT'].'/uploads/'.md5($_SERVER['DOCUMENT_ROOT']).'_301.log');
	
}
if($pages_301 && $_SERVER['HTTP_REFERER']){
	
	// проверка игнорирования данной страницы:
	$ignored = @mysql_fetch_assoc(mysql_("SELECT id FROM ".P."pages_301 
	WHERE path_ignored = 1 AND error_link = "._.$_SERVER['REQUEST_URI']._));
	
	if(!$ignored['id']){
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/address_path.php');// функция address_path
		
		$referer = parse_url_($_SERVER['HTTP_REFERER']);// находим данные о реферере
		
		$from_domain = host_clear($referer['host']);
		
		// автоисправление ссылок в содержании страниц
		/*$error_link = $_SERVER['REQUEST_URI'].'"';
		$that_host = host_clear($_SERVER['HTTP_HOST']);
		$article = $GLOBALS['system']-> data('article');
		log_($error_link.' && '.$path.' && '.$article);
		if($error_link && $path && $article && $from_domain == $that_host && mb_strstr($article, $error_link)){
			mysql_('UPDATE '.P.'pages 
			SET article = '._.str_replace($error_link, $path.'"', $article)._.'
			'._.'WHERE'._.' page_id = '.$GLOBALS['system']->id);
		}*/
		
		mysql_("INSERT INTO ".P."pages_301 SET 
		date_time = "._.time()._.",
		from_domain = "._.$from_domain._.",
		from_path = "._.address_path($_SERVER['HTTP_REFERER'])._.",
		error_link = "._.$_SERVER['REQUEST_URI']._.",
		to_path = "._.$path._.",
		ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._."),
		browser_version = "._.(int)$GLOBALS['browser']-> getVersion()._.",
		browser_version_full = "._.( ($GLOBALS['browser']-> getVersion()=='unknown')? '' : $GLOBALS['browser']-> getVersion() )._.",
		browser_name = "._.$browser_name._.",
		browser_name_full = "._.$_SERVER['HTTP_USER_AGENT']._
		);
	}
}
header("location: http://".$_SERVER['HTTP_HOST'].$path, true, 301);// 301 - Ресурс окончательно перенесен
exit;
?>
