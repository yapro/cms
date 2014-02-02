<?php
// скрипт расширяющий возможности работы с cookies
if(!$_SERVER['DOCUMENT_ROOT'] || !is_dir($_SERVER['DOCUMENT_ROOT'])){ echo 'config_2 not found : '.__FILE__; exit; }
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
//----------------------------------------------------------------------------------------------------------------------------------
$GLOBALS['WITHOUT_CONFIG'] = true;// отключаем т.к. 1) они не нужны для этого скрипта, 2) в index.php он подключается после config.php
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// подгрузка необходимых функций
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/Browser.php');// подгрузка необходимых функций
$GLOBALS['SYSTEM']['user']['access'] = 0;// права доступа пользователя по-умолчанию (временно)
if(!$GLOBALS['system_include']){
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');// интеграция с форумом
}
//----------------------------------------------------------------------------------------------------------------------------------
$GLOBALS['SYSTEM_SCRIPT'] = __FILE__;
/*
законы скрипта:
1. ориентируемся только на переменную $_COOKIE['visitor_hash']
2. таким образом данные посетителя могут быть записаны только после посещения страницы/скрипта сайта повторно (во 2-й раз или последующие разы)
3. если $_COOKIE['visitor_hash'] нет - значит посетитель не поддерживающий куки (такая вот жесткая проверка включения cookie)
*/

$GLOBALS['SYSTEM']['user']['user_id'] = (int)$GLOBALS['SYSTEM']['user']['user_id'];// чтобы SQL-запросы выполнялись правильно

function visitor_id_20111221(){
	
	// проверяем есть ли такой visitor_id в нашей Б.Д.
	if($GLOBALS['SYSTEM']['user']['user_id']){// если пользователь авторизован
		
		//log_('это пользователь');
		$visitor_id = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors WHERE user_id='.$GLOBALS['SYSTEM']['user']['user_id']));
		
	}else{
		
		$host = host_clear($_SERVER['HTTP_HOST']);
		
		if(!strstr($host,'.')){ $host = ''; }// на случай тестировани в доменах вида http://localhost/ или http://test/
		
		// если visitor_hash не ставилась или браузер без поддержки cookie - ставим посетителю уникальный visitor_hash
		if(!$_COOKIE['visitor_hash']){
			
			setcookie('visitor_hash', uniq(), (time()+5555555), '/', $host);// устанавливаем на 2 месяца
			
		}else{// т.к. cookie поддерживаются в браузере:
			//log_('visitor_hash = '.$_COOKIE['visitor_hash'].' == '.$_SESSION['SYSTEM']['VISITOR_ID'].' -- '.$GLOBALS['SYSTEM']['user']['user_id']);
			// в связи с тем, что к значению куки в некоторых случаях браузеры или пхп добавляет другие значения, делаю фикс:
			$ex = explode(',', $_COOKIE['visitor_hash']);// разбиваем значение вида 12866119814cb0240d03061, UserHashdfed8d5719b=1, authorization=ok
			$_COOKIE['visitor_hash'] = $ex['0'];// берем его первую часть
			
			setcookie('visitor_hash', addslashes($_COOKIE['visitor_hash']), (time()+5555555), '/', $host);// продливаем куку (еще на 2 месяца)
			
			$visitor_id = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors WHERE hash = '._.$_COOKIE['visitor_hash']._));
		}
	}
	
	return $visitor_id['0'];
}

$visitor_id = visitor_id_20111221();

if($visitor_id){// т.к. посетитель найден - обновляем его данные
	//	ip = INET_ATON('._.$_SERVER['REMOTE_ADDR']._.'),
	mysql_('UPDATE '.C.'cookies_visitors SET time_modified = '.time().' WHERE visitor_id = '.$visitor_id);
	
}else if($_COOKIE['visitor_hash'] || $GLOBALS['SYSTEM']['user']['user_id']){// т.к. посетитель не найден добавляем его в базу кук
	
	mysql_('INSERT INTO '.C.'cookies_visitors SET 
	user_id = '.$GLOBALS['SYSTEM']['user']['user_id'].',
	hash = '._.($GLOBALS['SYSTEM']['user']['user_id']? 'user_'.$GLOBALS['SYSTEM']['user']['user_id'] : $_COOKIE['visitor_hash'])._.',
	ip = INET_ATON('._.$_SERVER['REMOTE_ADDR']._.'),
	time_created = '.time().',
	time_modified = '.time().',
	cookies_count = 0', 0, 1062);// 1062 - игнорируем логирование информировании о ошибке запроса вида : Duplicate entry
	
	$mysql_errno = mysql_errno();
	
	if($mysql_errno == 1062){// если такой постетиль уже есть (успел добавиться другим скриптом)
		
		$visitor_id = visitor_id_20111221();
		
	}else if(!$mysql_errno){
		
		$visitor_id = @mysql_insert_id();
		
	}
	
	if(!$visitor_id){
		
		// хакер попытался провести манипуляции с куками, запускаю: error(__FILE__);
		// например случай, когда visitor_hash был равен ../.../.././../.../.././../.../.././../.../.././../.../.././../.../.././etc/passwd
	}
}
//log_('1 : '.$_SERVER['REMOTE_ADDR'].' : '.print_r($_COOKIE,1));
$_SESSION['SYSTEM']['VISITOR_ID'] = (int)$visitor_id;
//log_('detect : '.$_SESSION['SYSTEM']['VISITOR_ID'].' : '.$GLOBALS['SYSTEM']['user']['user_id']);

if($_SESSION['SYSTEM']['VISITOR_ID']!=0){
	if(isset($_SESSION['SYSTEM']['cookies'])){
		if($_SESSION['SYSTEM']['cookies']){
			foreach($_SESSION['SYSTEM']['cookies'] as $k => $v){
				if(!isset($_COOKIE[ $k ])){// если такой куки переменной не существует, НО она есть в сессии
					$_COOKIE[ $k ] = $v;
				}
			}
		}
	}else if($q = mysql_query('SELECT name, value FROM '.P.'cookies WHERE visitor_id = '.$_SESSION['SYSTEM']['VISITOR_ID'])){
		while($r = mysql_fetch_assoc($q)){
			if(!isset($_COOKIE[ $r['name'] ])){// если такой переменной не существует, НО она есть в б.д.
				$_COOKIE[ $r['name'] ] = $_SESSION['SYSTEM']['cookies'][ $r['name'] ] = $r['value'];
			}
		}
	}
}
//log_('2 : '.print_r($_COOKIE,1));

// возвращает все данные зарегистрированного пользователя по его ID | при установке возвращает 0 - если ошибка, 1 - в случае успеха
function cookie($name='', $value=null, $page='/', $now=true){
	//-log_('$name='.$name.' $value='.$value.' VISITOR_ID='.$_SESSION['SYSTEM']['VISITOR_ID'].'__'.print_r($_COOKIE,1));
	if(!$name || $_SESSION['SYSTEM']['VISITOR_ID']==0){ return ; }
	
	if(isset($value)){// устанавливаем куку (добавляем или обновляем значение)
		//-log_('set');
		$id = @mysql_fetch_row(mysql_("SELECT id FROM ".P."cookies 
		WHERE visitor_id = ".$_SESSION['SYSTEM']['VISITOR_ID']." AND name = "._.$name._.' LIMIT 1'));
		
		if($id['0']){
			
			mysql_("UPDATE ".P."cookies SET value = "._.$value._." "._."WHERE"._." id = ".$id['0']);
			
		}else{
			
			if(mysql_("INSERT INTO ".P."cookies SET 
			visitor_id = ".$_SESSION['SYSTEM']['VISITOR_ID'].", 
			name = "._.$name._.", 
			value = "._.$value._)){
				
				mysql_query("UPDATE ".C."cookies_visitors SET cookies_count = cookies_count + 1 WHERE visitor_id = ".$_SESSION['SYSTEM']['VISITOR_ID']);
				
			}
			
		}
		
		$_SESSION['SYSTEM']['cookies'][ $name ] = $value;
		
		if($now){ $_COOKIE[ $name ] = $_SESSION['SYSTEM']['cookies'][ $name ]; }
		
		return mysql_error()? 0 : 1;
		
	}else{// находим данные пользователя
		//-log_(123);
		if(isset($_COOKIE[ $name ])){
			//-log_(1);
			return $_COOKIE[ $name ];
			
		}else if(isset($_SESSION['SYSTEM']['cookies'][ $name ])){
			
			if($now){ $_COOKIE[ $name ] = $_SESSION['SYSTEM']['cookies'][ $name ]; }
			//-log_(2);
			return $_SESSION['SYSTEM']['cookies'][ $name ];
			
		}else{
			
			$value = '';
			
			if($q = mysql_("SELECT value FROM ".P."cookies WHERE visitor_id = ".$_SESSION['SYSTEM']['VISITOR_ID']." AND name = "._.$name._)){
				while($r = @mysql_fetch_row($q)){
					$value .= $r['0'];
				}
			}
			
			$_SESSION['SYSTEM']['cookies'][ $name ] = $value;
			
			if($now){ $_COOKIE[ $name ] = $_SESSION['SYSTEM']['cookies'][ $name ]; }
			//-log_(3);
			return $_SESSION['SYSTEM']['cookies'][ $name ];
		}
	}
}
// удаляем куки-идентификации (например чтобы завести демо-счет на др. емэйл-адрес)
function cookie_delete($name='',$page='/', $now=true){
	
	if(!$name || $_SESSION['SYSTEM']['VISITOR_ID']==0){ return ; }
	
	if(mysql_('DELETE FROM '.P.'cookies WHERE visitor_id = '.$_SESSION['SYSTEM']['VISITOR_ID'].' AND name = '._.$name._)){
		
		mysql_query('UPDATE '.C.'cookies_visitors SET cookies_count = cookies_count - 1 WHERE visitor_id = '.$_SESSION['SYSTEM']['VISITOR_ID']);
		
		@setcookie(addslashes($name), 'delete_cookie', (time()-86400), '/');
		
		unset($_SESSION['SYSTEM']['cookies'][ $name ]);
		
		if($now){ unset($_COOKIE[ $name ]); }
		
		return 1;
	}
}
// удаляем куки-идентификации (например чтобы завести демо-счет на др. емэйл-адрес)
function cookie_sync_visitors($visitor_hash=''){
	return ;
	if($_SESSION['SYSTEM']['VISITOR_ID']!=0 && $visitor_hash){
		setcookie('visitor_hash', $visitor_hash, (time()+5555555), '/', '.'.host_clear($_SERVER['HTTP_HOST']) );
	}
	// делее будет реализовывание связывания данных одного хэш-пользователя с другим
}
//---------------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_POST['cookie_name']){
	header("content-type:application/x-javascript; charset=UTF-8");// кодировка страницы
	if(isset($_POST['del'])){
		echo cookie_delete($_POST['cookie_name']);
	}else if(isset($_POST['value'])){
		echo cookie($_POST['cookie_name'],$_POST['value']);
	}else{
		echo cookie($_POST['cookie_name']);
	}
	exit;
}
// если запрошена вся информация по посетителю в виде объекта - выводим ее:
if(isset($_GET['cookies_javascript_object'])){
	header("content-type:application/x-javascript; charset=UTF-8");// кодировка страницы
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// дата в прошлом
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");// текущая дата
	// всегда модифицируется
	header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");// HTTP/1.0
	$echo = '';
	if($_COOKIE){
		foreach($_COOKIE as $name => $value){
			$echo .= ajaxHTML($name).':'.ajaxHTML($value).',';
		}
	}
	echo 'var cookies_javascript_object = {'.($echo? substr($echo,0,-1) : '').'}';
	exit;
}
?>
