<?php
// скрипт подсчета закачек файлов
header("content-type:application/x-javascript; charset=UTF-8");// кодировка страницы
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// дата в прошлом
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");// текущая дата
// всегда модифицируется
header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");// HTTP/1.0

if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации

if(!$GLOBALS['SYSTEM']['config']['yapro_statistics_downloading_files'] || !$_GET['path']){ exit; }

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/address_path.php');// функция address_path

$_GET['path'] = unescape($_GET['path']);

// находим данные о странице, на которой сделан клик по ссылке "скачать"
$to = parse_url_($_GET['path']);// находим адрес сайта
$to_path = address_path($_GET['path']);// находим путь страницы

$ignored_hash = md5($to['host'].$to_path);// проверка игнорирования данной страницы
$ignored = @mysql_fetch_assoc(mysql_query("SELECT id FROM ".P."files_downloads WHERE ignored_hash = '".$ignored_hash."'"));

if(!$ignored['id']){
	
	include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/Browser.php');// подгрузка необходимых функций
	
	$referer = parse_url_($_SERVER['HTTP_REFERER']);// находим данные о реферере
	
	mysql_("INSERT INTO ".P."files_downloads SET 
	date_time = "._.time()._.",
	from_domain = "._.host_clear($referer['host'])._.",
	from_path = "._.address_path($_SERVER['HTTP_REFERER'])._.",
	to_domain = "._.$to['host']._.",
	to_path = "._.$to_path._.",
	ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._."),
	browser_version = "._.(int)$browser-> getVersion()._.",
	browser_version_full = "._.( ($browser-> getVersion()=='unknown')? '' : $browser-> getVersion() )._.",
	browser_name = "._.( ($browser-> getBrowser()=='unknown')? '' : $browser-> getBrowser() )._.",
	browser_name_full = "._.$_SERVER['HTTP_USER_AGENT']._.",
	java_on = ".(int)$_GET['java_on'].",
	operation_system = "._.$_GET['operation_system']._.",
	screen_width = ".(int)$_GET['screen_width'].",
	screen_height = ".(int)$_GET['screen_height'].",
	flash_version = ".(int)$_GET['flash_version']
	);
}
?>