<?php
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
@session_start();// заводим сессию
@header("content-type:text/html; charset=UTF-8");
//--------------------------------------------------------------------------------------------
if(isset($_GET['check_enable_cookie']) && !$_SESSION['check_enable_cookie']){
	echo '<H2 style="text-align: center; padding-top: 150px;">В Вашем браузере отключена поддержка cookie, пожалуйста включите ее, 
	и только потом <a href="/install/index.php">продолжайте процесс установки »</a></H2>';
	exit;
}
if(!$_SESSION['check_enable_cookie']){
	$_SESSION['check_enable_cookie'] = 1;// устанавливаем cookie до конца сессии, чтобы проверить их включение в браузере
	header("location: http://".$_SERVER['HTTP_HOST'].'/install/index.php?check_enable_cookie', true, 301);// 301 - Ресурс окончательно перенесен
	exit;
}

//--------------------------------------------------------------------------------------------
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
//--------------------------------------------------------------------------------------------
$content = @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');
//--------------------------------------------------------------------------------------------
$top = '<html>
	<head>
	<title>Установщик системы</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Language" content="ru">
	<link href="/css/typography.css" type="text/css" rel="stylesheet">
	</head> 
	<body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
	<center>
	<table border="0" cellpadding="0" cellspacing="0" width="770"><tr><td style="border: 0px; padding-top: 15px">';

$bottom = '</td></tr></table></body></html>';

$start = '<p><b style="color: #FF0000;">Обязательно выполните данное действие</b> и только потом <a href="/install/index.php">продолжайте процесс установки »</a></p>';
//--------------------------------------------------------------------------------------------
function uninstall_yapro($content){
	
	$f = @fopen($_SERVER['DOCUMENT_ROOT'].'/config.php', "w");
	
	fwrite($f, str_replace('//installed', '', $content));// удаляем информацию об установке
	fclose($f);
	
	// перенаправляю, чтобы изменить данные подключения
	header("location: http://".$_SERVER['HTTP_HOST'].'/install/index.php');
	exit;
}
//--------------------------------------------------------------------------------------------
if(!$content){
	echo $top.'Недостаточно прав для чтения файла конфигурации (config.php). Или данный файл пуст!'.$bottom;
}else{
	
	$check_config_install = explode('//installed', $content);
	
	if($check_config_install['1']){// если файл конфигурации уже создан:
		
		$connect_php_code = explode('if($error_connect_mysql_server', $content);
		
		eval(' ?>'.$connect_php_code['0'].' ?><?php ');
		
		if($error_connect_mysql_server || $error_connect_mysql_db){
			
			$_SESSION['install_error'] = '<p style="color: #FF0000;">Не удалось подключиться к MySQL базе данных, проверьте правильность укзанных данных и доступ MySQL пользователя к указанной базе данных.</p>';
			
			uninstall_yapro($content);
			
		}
		
		// подключение к б.д. выполнено нормально, продолжаем установку
		mysql_query("SET NAMES UTF8");
		setlocale(LC_ALL, 'ru_RU.UTF-8');
		mb_internal_encoding("UTF-8");
		
		if(@mysql_num_rows(mysql_query("SHOW TABLES"))>0){
			
			$_SESSION['install_error'] = '<p style="color: #FF0000;"><b>Внимание!</b> В указанной базе данных обнаружены таблицы. Пожалуйста, удалите данные таблицы.</p>';
			
			uninstall_yapro($content);
			
		}
		
		$content = @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/install/my.sql');
		
		if($content){
			
			$GLOBALS['SYSTEM']['without_user_auto_authentication'] = true;
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');
			
			$db_prefix = $_SESSION['install_db_prefix'];
			$admin_pass = $_SESSION['install_admin_pass'];
			if(!$admin_pass){ $admin_pass = time(); }// если пароль не был указан, назначаем его самостоятельно
			$install_forum = (int)$_SESSION['install_forum'];
			
			$content = str_replace("YaPro.ru", $_SERVER['HTTP_HOST'], 
			str_replace("yapro.ru", $_SERVER['HTTP_HOST'], $content));
			
			$ex = explode("\n", $content);
			$install_sql = array();
			foreach($ex as $sql){
					if($sql){
						$install_sql[] = str_replace('`you_prefix_table_', '`'.$db_prefix, $sql);// меняем префиксы таблиц
					}
			}
			// добавляю настройки сайта:
			$install_sql[] = "UPDATE `".$db_prefix."settings` SET value='".$_SERVER['HTTP_HOST']."' WHERE name ='yapro_http_host'";
			
			// меняю настройки форума
			$install_sql[] = "UPDATE `".$db_prefix."f_config` SET config_value='".$_SERVER['HTTP_HOST']."' 
			WHERE config_name IN ('cookie_domain', 'server_name', 'sitename', 'site_desc')";
			
			// назначаю пароль администратору
			$install_sql[] = "UPDATE `".$db_prefix."f_users` 
			SET user_password='".$GLOBALS['SYSTEM']['users']-> hash($admin_pass)."' WHERE user_id = 2";
			
			// добавляю кэш-значение последнего редактирования страниц и смайлов, это нужно чтобы кэширование данных не было постоянным
			$install_sql[] = "INSERT INTO `".$db_prefix."cache` (name, time_modified) VALUES ('pages', '".time()."'), ('smiles', '".time()."')";
			
			$error_avatar_move = '';
			if($install_forum){// если выбрано форум - скрыть или выключить
				
				$install_sql[] = "UPDATE `".$db_prefix."f_config` SET config_value='/uploads' WHERE config_name = 'script_path'";
				$install_sql[] = "UPDATE `".$db_prefix."f_config` SET config_value='/avatars' WHERE config_name = 'avatar_path'";
				/*
				$from = '/forum/images/avatars/gallery/general/1.jpg';
				$to = '/uploads/avatars/1.jpg';
				if(!move_uploaded_file($_SERVER['DOCUMENT_ROOT'].$from, $_SERVER['DOCUMENT_ROOT'].$to)){
					$error_avatar_move = '<p><b>Однако:</b> ереместить аватару администратора не удалось, сделайте это самостоятельно.<br>
					Скопируйте файл '.$from.' в директорию '.$to.'</p>';
				}*/
			}
			
			if($install_forum){// если выбрано форум - скрыть (установить и выключить) обновляю настройки форума
				
				$install_sql[] = "UPDATE `".$db_prefix."f_config` SET config_value='1' WHERE config_name = 'board_disable'";
				
			}else if(!$install_forum){
				
				$install_sql[] = "UPDATE `".$db_prefix."settings` SET value='0' WHERE name ='yapro_authentication'";
				
			}
			
			$used_forum_tables = array(
			'config' => 'настройки форума, используется под настройки сайта',
			'users' => 'пользователи сайта, используется в комментариях',
			'groups' => 'группы пользователей, используется в взаимосвязях групп и пользователей на сайте',
			'user_group' => 'взаимосвязи групп и пользователей на сайте, используется в комментариях',
			'banlist' => 'банлист, используется в комментариях',
			'bots' => 'поисковые боты, используется в статистике посещаемости',
			'sessions' => 'сессии пользователей, используется для авторизации пользователей',
			'sessions_keys' => 'ключи сессии пользователей, используется для авторизации пользователей',
			'ranks' => 'звания пользователей, используется в комментариях',
			'profile_fields_data' => 'дополнительные поля пользователей',
			'profile_fields' => 'информация о дополнительных полях пользователей',
			'profile_lang' => 'используется для хранения русскоязычных имен полей и их описания в профилях пользователей',
			'profile_fields_lang' => 'используется под варианты выбора SELECT-полей в профилях пользователей'
			);
			
			foreach($install_sql as $sql){
				if($install_forum==2){// если выбрано форум - не нужен (не устанавливать)
					$e = explode('`'.$db_prefix.'f_', $sql);
					if($e['1']){// если это таблица форума
						$e = explode('` ', $e['1']);
						if($e['1'] && $e['0'] && !$used_forum_tables[$e['0']]){
							continue;
						}
					}
				}
				if(!mysql_query($sql) || mysql_error()){
					$sql_error .= $sql.";\n";
				}
			}
			
			// удаляю файлы инсталяции
			$path = $_SERVER['DOCUMENT_ROOT']."/install/";// удаляю загруженные файлы пользователей
			if($dir = @dir($path)){
				while ($file = $dir-> read()){
					if($file != "." && $file != ".."){
						if(!@unlink($path.$file)){ $error_uninstall++; }
					}
				}
				$dir->close();
			}
			if(!@rmdir($path)){ $error_uninstall++; }
			
			echo $top.'<h3 style="font-size: 17px; font-weight: bold;">Процесс установки успешно завешен</h3>
			'.($sql_error?'<p><b><span style="color: #FF0000;">Недостаточно прав выполнить следующие запросы. Поэтому, Выполните их самостоятельно:</span></b><br><textarea onclick="this.select()" rows="15" cols="135">'.htmlspecialchars($sql_error).'</textarea></p>':'').'
			'.($error_uninstall?'<p style="color: #FF0000;">Пожалуйста, удалите директорию <b>install</b></p>':'').'
			'.($error_avatar_move? $error_avatar_move :'').'
			<p><b>Обязательно сохраните данную страницу или запомните данные Администрирования:</b></p>
			<p>Адрес системы управления сайтом: <a href="http://'.$_SERVER['HTTP_HOST'].'/admin/" target="_blank">http://'.$_SERVER['HTTP_HOST'].'/admin/</a><br>
			Пользователь: Admin<br>
			Пароль: '.htmlspecialchars($admin_pass).'</p>
			
			<p><b style="color: #FF0000;">Важные рекомендации по настройке:</b></p>
			
			<p>1. Для пользователей проекта Denwer в файле .htaccess раскомментируйте строку AddDefaultCharset UTF-8</p>
			
			<p>2. Зайдите в систему управления сайтом - Система - Информация, и проверьте отсутствие ошибок прав доступа на запись в важные директории сайта</p>
			
			'.(($install_forum==2)?'<p>3. В корне сайта удалите директорию <b>forum</b> а на сайте страницу <b>Форум</b></p>':'').'
			
			<p>Если все вышеперечисленные действия выполнены и Вы сохранили настройки, то можете:</p>
			<ol type="1">
				<li><a href="http://'.$_SERVER['HTTP_HOST'].'/" target="_blank">Зайти на сайт»</a></li>
				<li><a href="http://'.$_SERVER['HTTP_HOST'].'/admin/" target="_blank">Зайти в систему управления сайтом»</a></li>
				'.(($install_forum==2)?'':'
				<li><a href="http://'.$_SERVER['HTTP_HOST'].'/forum/" target="_blank">Зайти на форум»</a></li>
				<li><a href="http://'.$_SERVER['HTTP_HOST'].'/forum/ucp.php?mode=login" target="_blank">Зайти в систему управления форумом»</a></li>
				').'
				<li><a href="http://yapro.ru/documents/video.html" target="_blank">Посмотрите видео-обучение»</a></li>
			</ol>
			
			<p><b>Рекомендации безопасности:</b></p>
			<p>1. для работы системы, пользователю базы данных <u>вполне достаточно</u> привелегии: SELECT, INSERT, UPDATE, DELETE<br>
			2. при необходимости раскоментируйте строки в файле .htaccess (уберите <b>#</b>)</p>
			<p>Если возникнут вопросы, <a href="http://yapro.ru/contacts.html" target="_blank">пишите нам »</a></p>
			<p><b>Спасибо за выбор CMS YaPro.</b></p><br><br>'.$bottom;
			
		}else{
			echo $top.'Недостаточно прав для чтения файла <b>/install/my.sql</b> или файл пуст! Проверьте, пожалуйста.'.$start.$bottom;
		}
		
	}else{// если файл конфигурации еще не создан:
		
		if($_POST['db_name']){ $_SESSION['install_db_name'] = trim($_POST['db_name']); }
		if($_POST['db_host']){
			$_SESSION['install_db_host'] = trim($_POST['db_host']);
		}
		if(!$_SESSION['install_db_host']){
			$_SESSION['install_db_host'] = 'localhost';
		}
		if($_POST){ $_SESSION['install_db_port'] = trim($_POST['db_port']); }
		if($_POST['db_user']){ $_SESSION['install_db_user'] = trim($_POST['db_user']); }
		if($_POST){ $_SESSION['install_db_pass'] = trim($_POST['db_pass']); }
		if($_POST){ $_SESSION['install_db_prefix'] = trim($_POST['db_prefix']); }
		if($_POST['admin_pass']){
			$_SESSION['install_admin_pass'] = trim($_POST['admin_pass']);
		}
		if(!$_SESSION['install_admin_pass']){
			$_SESSION['install_admin_pass'] = 'X'.substr(md5(time()),0,4);
		}
		if($_POST){ $_SESSION['install_forum'] = (int)$_POST['install_forum']; }
		
		
		if($_POST['db_name'] && $_POST['db_host'] && $_POST['db_user']){
			
			$connect_php_code = explode('$dbcnx', $content);
			
			eval(' ?>'.$connect_php_code['0'].' ?><?php ');
			
			$content =	str_replace('$dbname = \''.$dbname.'\';', '$dbname = \''.$_SESSION['install_db_name'].'\';//installed',
			str_replace('$dbhost = \''.$dbhost.'\';', '$dbhost = \''.$_SESSION['install_db_host'].'\';', 
			str_replace('$dbport = \''.$dbport.'\';', '$dbport = \''.$_SESSION['install_db_port'].'\';', 
			str_replace('$dbuser = \''.$dbuser.'\';', '$dbuser = \''.$_SESSION['install_db_user'].'\';', 
			str_replace('$dbpasswd = \''.$dbpasswd.'\';', '$dbpasswd = \''.$_SESSION['install_db_pass'].'\';', 
			str_replace('define(\'P\', \''.P.'\');', 'define(\'P\', \''.$_SESSION['install_db_prefix'].'\');', 
			str_replace('define(\'W\', \''.W.'\');', 'define(\'W\', \''.$_SESSION['install_db_prefix'].'\');', 
			str_replace('define(\'N\', \''.N.'\');', 'define(\'N\', \''.$_SESSION['install_db_prefix'].'\');', 
			str_replace('define(\'C\', \''.C.'\');', 'define(\'C\', \''.$_SESSION['install_db_prefix'].'\');', 
			str_replace('define(\'F\', \'f_\');', 'define(\'F\', \''.$_SESSION['install_db_prefix'].'f_\');', 
			$content))))))))));
			
			if(!$f = @fopen($_SERVER['DOCUMENT_ROOT'].'/config.php', "w")){
				
				echo $top.'<p>Недостаточно прав для записи в файл конфигурации (config.php) следующей информации:</p>
				<textarea onclick="this.select()" rows="15" cols="30" style="margin: 15px 0px">'.htmlspecialchars($content).'</textarea>
				<p>Попробуйте записать её самостоятельно!</p>'.$start.$bottom;
				
			}else{
				
				fwrite($f, $content);
				fclose($f);
				sleep(1);
				// перенаправляю, чтобы завершить установку (добавление таблиц)
				header("location: http://".$_SERVER['HTTP_HOST'].'/install/index.php');
				exit;
				
			}
			
		}else{
			
			$notice = '';
			/*******************************************************************************************************************/
			// определяем UTF-8
			ob_start();
			@passthru('locale -a | grep ru_RU');// установленные кодировки
			$command_str = ob_get_contents();
			ob_end_clean();
			if($command_str){
				$arr = explode("\n", $command_str);
				$installed_locale = '<ul>';
				foreach($arr as $str){
					if($str && $str!='ru_RU'){
						$check_utf8[strtolower_($str)]++;
					}
				}
				$installed_locale .= '</ul>';
			}
			$default_charset = ini_get('default_charset');
			if(!$check_utf8['ru_ru.utf-8'] && !$check_utf8['ru_ru.utf8'] && !$default_charset=='' && $default_charset!='UTF-8'){
				$notice = '<p>Обратите внимание: кодировка UTF-8 не определена</p>';
			}
			/*******************************************************************************************************************/
			if($_SESSION['install_error']){
				
				$notice .= $_SESSION['install_error'];
				
				$_SESSION['install_error'] = '';
				
			}
			/*******************************************************************************************************************/
			echo $top.'<h3 style="font-size: 17px; font-weight: bold;">Вас приветствует установщик CMS YaPro</h3>
			'.$notice.'
			<p>Пожалуйста, перед началом установки в MySQL создайте базу данных в кодировке UTF-8 и пользователя с правами доступа: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER</p>
			<p>'.($_POST?'<span style="color: #FF0000;">Укажите все необходимые данные!</span>':'Теперь введите свои данные:').'</p>
			<form method="post" action="/install/index.php">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="dataSet">
			    <tr>
			        <td width="50%" class="leftText">
			            Имя базы данных MySQL:
			        </td>
			        <td>
			            <input type="text" name="db_name" value="'.htmlspecialchars($_SESSION['install_db_name']).'" />
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Хост подключения к базе данных:
			        </td>
			        <td>
			            <input type="text" name="db_host" value="'.htmlspecialchars($_SESSION['install_db_host']).'" />
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Порт подключения к базе данных:
			        </td>
			        <td style="text-align:left">
			            <input type="text" name="db_port" value="'.htmlspecialchars($_SESSION['install_db_port']).'" style="width: 125px;" /> 
			    		(обычно не обязателен)
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Префикс таблиц в базе данных:
			        </td>
			        <td style="text-align:left"><!-- pre.mb_substr(md5(time()),0,5) -->
			            <input type="text" name="db_prefix" value="'.htmlspecialchars($_SESSION['install_db_prefix']).'" style="width: 125px;" /> 
			    		например my_ (обычно не обязателен)
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Имя пользователя базы данных:
			        </td>
			        <td>
			            <input type="text" name="db_user" value="'.htmlspecialchars($_SESSION['install_db_user']).'" />
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Пароль пользователя базы данных:
			        </td>
			        <td>
			            <input type="text" name="db_pass" value="'.htmlspecialchars($_SESSION['install_db_pass']).'" />
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Желаемый пароль администратора:
			        </td>
			        <td>
			            <input type="text" name="admin_pass" value="'.htmlspecialchars($_SESSION['install_admin_pass']).'" />
			        </td>
			    </tr>
			    <tr>
			        <td class="leftText">
			            Форум:
			        </td>
			        <td style="text-align:left">
			    		'.str_replace('value="'.$_SESSION['install_forum'].'"','value="'.$_SESSION['install_forum'].'" selected','
			            <select name="install_forum">
			    			<option value="0">нужен (установить и включить)</option>
			    			<option value="1">скрыть (установить и выключить)</option>
			    			<option value="2">не нужен (не устанавливать)</option>
			    		</select>
			    		').'
			        </td>
			    </tr>
			</table>
			<p style="text-align:center">
			  <input type="submit" value="Установить" onclick="this.style.display=\'none\'">
			</p>
			</form>
			<style>
			TABLE#dataSet {
				border-bottom: 1px solid #CCCCCC;
			}
			TABLE#dataSet TD {
				border-top: 1px solid #CCCCCC;
				padding: 5px 0;
				vertical-align:top;
			}
			TABLE#dataSet TD, 
			TABLE#dataSet P, 
			TABLE#dataSet DIV {
				text-align: right
			}
			TABLE#dataSet INPUT {
				width: 100%;
				font-size: 12px;
			}
			TABLE#dataSet TD.leftText {
				padding-right: 7px;
				*padding-top: 7px;
			}
			</style>
			'.$bottom;
		}
	}
}
?>