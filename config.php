<?php
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_USER_NOTICE);
$dbms = 'mysql';
$dbhost = 'you_host';
$dbport = '';
$dbname = 'you_db';
$dbuser = 'you_user';
$dbpasswd = 'you_pass';
define('P', '');// префикс таблиц сайта в базе данных
define('F', 'f_');// префикс таблиц форума
define('W', '');// префикс таблицы IP-предупреждений
define('N', '');// префикс таблицы никнэймов посетителей
define('C', '');// префикс таблицы кукиес-идентификаторов посетителей и пользователей
define('_', '~'.md5(uniqid(time())).'~');// спец. апостроф
define('S_', '~S'.md5(uniqid(time())).'S~');// спец. апостроф

/*if(substr($_SERVER['REQUEST_URI'],0,7)=='/admin/'){
	$dbuser = 'admin_user';
}*/

function config_20101117($str=''){
	
	@header("content-type:text/html; charset=UTF-8");
	@header("Expires: ".gmdate("D, d M Y H:i:s", (time()-777) )." GMT");// отменяем всяческое кеширование
	@header("Last-Modified: ".gmdate("D, d M Y H:i:s", time() )." GMT");// делаем дату модификации новой
	header("HTTP/1.0 503 Service Unavailable");
	echo '<H2 style="text-align: center; padding-top: 150px;">'.($str? $str : 'Проблемы в настройках').'</H2>';
	exit;
	
}

$dbcnx = @mysql_connect($dbhost.($dbport?$dbport:''),$dbuser,$dbpasswd);
if(!$dbcnx){ $error_connect_mysql_server++; }
if(!@mysql_select_db($dbname,$dbcnx)){ $error_connect_mysql_db++; }

if($error_connect_mysql_server || $error_connect_mysql_db){
	
	$info = '<br>Информацию о хостинге можно <a href="https://www.nic.ru/whois/?query='.$_SERVER["SERVER_ADDR"].'">узнать здесь &#8250;</a>';
	
	if(mysql_errno()=='1040'){
		
		config_20101117('База данных временно недоступна. Слишком много подключений или нехватка места на жестком диске'.$info);
		
	}else if($error_connect_mysql_server){
		
		config_20101117('В настоящий момент сервер базы данных недоступен'.$info);
		
	}else{
		
		config_20101117('В настоящий момент база данных недоступна'.$info);
		
	}
}

if(!defined('F') || F==''){
	config_20101117('Не указан префикс таблиц пользователей');
}

mysql_query("SET NAMES UTF8");
setlocale(LC_ALL, 'ru_RU.UTF-8');
mb_internal_encoding("UTF-8");
//mb_regex_encoding('UTF-8');

// необходимые константы для подключения phpBB3
define('IN_PHPBB', true);
define('PHPBB_INSTALLED', true);
//define('PHPBB_MSG_HANDLER', 'errorLog');// может быть в будущем разработчики phpBB исправят баги и не будут вызывать trigger_error где попало :)

$GLOBALS['SYSTEM']['config'] = array();
if($q = mysql_query('SELECT config_name, config_value FROM '.F.'config')){
	while($r=mysql_fetch_assoc($q)){
		$GLOBALS['SYSTEM']['config'][ $r['config_name'] ] = $r['config_value'];
	}
}

if($q = mysql_query("SELECT name, value FROM ".P."settings")){
	while($r=mysql_fetch_assoc($q)){
		$GLOBALS['SYSTEM']['config'][ $r['name'] ] = $r['value'];
	}
}

if(!$GLOBALS['WITHOUT_CONFIG']){
	
	if(!$GLOBALS['SYSTEM']['config']['yapro_index_id']){ config_20101117('Не заданы настройки сайта'); }
	
	$x = $_SERVER['HTTP_HOST'];
	if($GLOBALS['SYSTEM']['config']['yapro_subdomains']){
		$x = mb_substr($_SERVER['HTTP_HOST'], -mb_strlen($GLOBALS['SYSTEM']['config']['yapro_http_host']));
	}
	
	if($x != $GLOBALS['SYSTEM']['config']['yapro_http_host']){
		header("location: http://".$GLOBALS['SYSTEM']['config']['yapro_http_host'].$_SERVER['REQUEST_URI'], true, 301);
		exit;
	}
}

// Находим реальный IP посетителя, даже если на сервере запущен прокси-сервер (например nginx)
if($_SERVER["HTTP_X_FORWARDED_FOR"]) {
	$a = array_reverse(explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]));
	$_SERVER['REMOTE_ADDR'] = trim($a['0']);
}else if($_SERVER["HTTP_CLIENT_IP"]){
	$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CLIENT_IP"];
}else{
	$_SERVER['REMOTE_ADDR'] = $_SERVER["REMOTE_ADDR"];
}
$ip = explode('.',$_SERVER['REMOTE_ADDR']);// бывает IP адрес определяется как строка unknown
if($_SERVER['REMOTE_ADDR']=='unknown' || !$ip || !$ip['3'] || !is_numeric($ip['0']) || !is_numeric($ip['1']) || !is_numeric($ip['2']) || !is_numeric($ip['3'])){
    $_SERVER['REMOTE_ADDR'] = 0;
}
@session_start();// заводим сессию

$GLOBALS['SYSTEM']['user_links'] = array(// применяемые ссылки
	'start' => '/s/user'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'],// адрес текущей страницы
	'registration' => '/s/user'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?a=registration',// форма регистрации и инфа после сабмита
	'restore' => '/s/user'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?a=restore',// форма восстановления пароля и инфа после сабмита
	'out' => '/s/user'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?a=out',// форма восстановления пароля и инфа после сабмита
	'edit' => '/i',// форма редактирования профиля
	'vkontakte' => '/s/user'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?a=vkontakte',// авторизация через vkontakte
);
?>
