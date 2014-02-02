<?php
header("HTTP/1.0 404 Not Found");

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/address_path.php');// функция address_path

$path_404 = address_path($_SERVER['REQUEST_URI']);

// проверка игнорирования данной страницы:
$ignored = @mysql_fetch_assoc(mysql_("SELECT id FROM ".P."pages_404 WHERE path_ignored = 1 AND path_404 = "._.$path_404._));

if(!$ignored['id']){
	
	include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/browser_name.php');// узнаем имя браузера - переменная $browser_name
	
	if($error_message && ($GLOBALS['SYSTEM']['config']['yapro_log_404'] || $important)){
		log_('выполняется перенаправление с страницы '.$_SERVER['REQUEST_URI']."\n".
		'с страницы urldecode: '.urldecode($_SERVER['REQUEST_URI'])."\n".
		(($_SERVER['HTTP_HOST'] == $GLOBALS['SYSTEM']['config']['yapro_http_host'])?'':'домена: '.$_SERVER['HTTP_HOST']."\n").
		'Рефферер: '.$_SERVER['HTTP_REFERER']."\n".
		'реферер urldecode: '.urldecode($_SERVER['HTTP_REFERER'])."\n".
		'Пояснение: '.$error_message."\n".
		'IP: '.$_SERVER['REMOTE_ADDR']."\n".
		'браузер: '.$browser_name.' '.(int)$GLOBALS['browser']-> getVersion()."\n".
		'полностью: '.$_SERVER['HTTP_USER_AGENT'],
		$_SERVER['DOCUMENT_ROOT'].'/uploads/'.md5($_SERVER['DOCUMENT_ROOT']).'_404.log');
	}
	
	//statistics_search_text();// находим from_search_system - $GLOBALS['SYSTEM']['statistics']['search_system']
	
	$referer = parse_url_($_SERVER['HTTP_REFERER']);// находим данные о реферере
	
	mysql_("INSERT INTO ".P."pages_404 SET 
	date_time = "._.time()._.",
	from_domain = "._.host_clear($referer['host'])._.",
	from_path = "._.address_path($_SERVER['HTTP_REFERER'])._.",
	from_search_system = "._.$GLOBALS['SYSTEM']['statistics']['search_system']._.",
	path_404 = "._.$path_404._.",
	ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._."),
	browser_version = "._.(int)$GLOBALS['browser']-> getVersion()._.",
	browser_version_full = "._.( ($GLOBALS['browser']-> getVersion()=='unknown')? '' : $GLOBALS['browser']-> getVersion() )._.",
	browser_name = "._.$browser_name._.",
	browser_name_full = "._.$_SERVER['HTTP_USER_AGENT']._
	);
}

// раньше была задержка в 1 сек. которая была нужна для правильного сбора JS статистики
echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=http://'.$GLOBALS['SYSTEM']['config']['yapro_http_host'].'/404">';

exit;
?>
