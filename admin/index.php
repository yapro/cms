<?php
@header("content-type:text/html; charset=UTF-8");
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

// находим имя Административной директории для выставления cookie доступа
$system_dir_name = array_reverse(explode('/',str_replace('\\','/',__FILE__)));
$dir_name = '/'.$system_dir_name['1'].'/';

$GLOBALS['WITHOUT_CONFIG'] = true;// объявление переменной обеспечивает работу системы адмнистрирования без учета заданных параметров сайта
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/cookies.php');// подгрузка необходимых cookies функций
//---------------------------------------------------------------------------------------------------

$callback = '';

if(isset($_GET['exit'])){// если пользователь решил выйти из админки
	
	$GLOBALS['SYSTEM']['users']-> session_end();
	$ex = explode('index.php', $_SERVER['PHP_SELF']);
	header("Location: ".addslashes($ex['0']));
	
}else if(!$GLOBALS['SYSTEM']['user']['user_id'] && substr($_SERVER['REQUEST_URI'], -1)=='/' && $_POST['username'] && $_POST['user_password']){// проверяем на доступ в директорию
	
	$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_username($_POST['username']);
	
	if($user_id){
		
		$r = $GLOBALS['SYSTEM']['users']-> data($user_id);
		
		$user_password_ok = $GLOBALS['SYSTEM']['users']-> check_hash($_POST['user_password'], $r['user_password']);
		
		if($r['user_id'] && $user_password_ok){
			
			$GLOBALS['SYSTEM']['users']-> session_start($r['user_id'],true);
			
			cookie('system_user_name', $r['username']);
			
		}else if($r['user_id']){
			
			$callback = 'Вы указали неверный Пароль.';
			
		}else{
			
			$callback = 'Вы указали неверное Имя пользователя или Пароль.';
			
		}
	}else{
		
		$callback = 'Вы указали неверное Имя пользователя.';
		
	}
    
}

$access = true;// доступ разрешен
if($GLOBALS['SYSTEM']['user']['user_id'] && $GLOBALS['SYSTEM']['user']['allow_ip']){// Доступ только с IP (возможно несколько, через запятую)
	
	$access = false;// доступ запрещен
	$ex = explode(',', $GLOBALS['SYSTEM']['user']['allow_ip']);
	
	// Вход в Систему Администрирования только с IP-адресов (через запятую)
	include_once('libraries/allow_ip.php');
	$ex2 = @explode(',', $allow_ip);
	
	$a = array_merge($ex,$ex2);
	
	foreach($a as $v){
		if($v){
			$v = trim($v);
			if($_SERVER['REMOTE_ADDR'] == $v){
				$access = true;// доступ разрешен
			}
		}
	}
}

if($access && $GLOBALS['SYSTEM']['user']['user_id']){ 
	
	if(!isset($_COOKIE['system_wysiwyg'])){// при первом входе в админку с любого браузера или компьютера
		cookie('system_wysiwyg', '1');
	}
	
	$version = @file_get_contents('version.txt');
	
	if(substr($_SERVER['HTTP_HOST'],0,4)=='xn--'){// поддержка кирилических доменов
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/idna_convert.class.php');
		$IDN = new idna_convert();
		$_SERVER['HTTP_HOST'] = $IDN->decode($_SERVER['HTTP_HOST']);
	}
	
	echo '<html>
	<head>
	<title>'.$_SERVER['HTTP_HOST'].' '.($version? $version : 'special version').'</title>
	</head>
	<frameset rows="1*" cols="260, 100%">
        <frame name="left_frame" marginwidth="0" marginheight="0" src="'.$dir_name.'modules/index/left.php" scrolling="auto">
        <frame name="right_frame" marginwidth="0" marginheight="0" src="'.(cookie('save_url')? cookie('save_url') : $dir_name.'modules/index/right.php').'" scrolling="auto">
	<noframes>
	<body>
	<p>CMS работает только используя фреймы!</p>
	</body>
	</noframes>
	</frameset>
	</html>
	';
}else{
	
	// проверка версии браузера
	// т.к. в XP иногда IE выдает двойне имя вида: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; InfoPath.3)
	$ex = explode('Windows NT', $_SERVER['HTTP_USER_AGENT']);// берем только первую часть имени браузера, т.к. вторая часть это его предок
	$msie5 = spliti('MSIE 5', $ex['0']);
	$msie6 = spliti('MSIE 6', $ex['0']);
	$msie7 = spliti('MSIE 7', $ex['0']);
	
	echo '<html>
	<head>
	<title>System - '.$_SERVER['HTTP_HOST'].'</title>
	</head>
	<body bgcolor="#FFFFFF">
	<script language="javascript">
	<!--
	//--проверка на фреймы
	if(self.parent.frames.length != 0){self.parent.location = document.location;}
	//document.cookie = "system_screen_height="+screen.height+"; path=/; domain="+document.location.host+"; expires=Friday, 15-May-2015 10:45:30 GMT";

	//-->
	</script>
	<style type="text/css">
	INPUT {
		width: 200px;
	}
	INPUT.Spec {
		border-top: 1px solid #C0BABA;
		border-left: 1px solid #C0BABA;
		border-right: 1px solid #F1F1F1;
		border-bottom: 1px solid #F1F1F1;
		background-color: #FFFFFF;
		text-align: center;
	}
	TD {font: 12px Verdana, Arial, Helvetica, sans-serif; text-align: right;}
	</style>
	
	<table align="center" border="0" height="100%">
	    <tr>
	        <td>
	        <!-- Detect JavaScript Start -->
	    	
			<div id="javascript_detect" align="center"><u>Внимание</u>: <b>отключена поддержка JavaScript</b>, пожалуйста, <a href="http://yapro.ru/documents/helpful/javascript.html" target="_blank">включите её &raquo;</a></div>
			
			'.(($msie5['1'] || $msie6['1'] || $msie7['1'])?'<div align="center"><b>Внимание</b>: Вы используете старую версию браузера Internet Explorer,<br>которая небезопасна для использования и не поддерживается Системой Администрирования.<br><br>Пожалуйста, установите более новую версию браузера!<br><br><table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
<ul style="text-align: left;">
    <li><a href="http://www.microsoft.com/downloads/details.aspx?FamilyID=341c2ad5-8c3d-4347-8c03-08cdecd8852b&DisplayLang=ru" target="_blank">Скачать браузер Internet Explorer 8</a></li>
    <li><a href="http://www.mozilla-europe.org/ru/firefox/" target="_blank">Скачать браузер Firefox</a></li>
    <li><a href="http://ru.opera.com/download/thanks/win/" target="_blank">Скачать браузер Opera</a></li>
</ul>
        </td>
    </tr>
</table></div>' : '
				<p align="center" style="color:#FF0000">'.$callback.'</p>
				<form action="" method="post" id="FormEnter" style="display: none;">
				<table border="0" align=center>
					<tr>
						<td><b>логин:</b></td>
						<td><input name="username" type="text" value="'.htmlspecialchars(cookie('system_user_name')).'" class="Spec" id="name"></td>
					</tr><tr>
						<td><b>пароль:</b></td>
						<td><input name="user_password" type="password" value="" class="Spec"></td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><input type="submit" value="Войти"></td>
					</tr>
				</table>
				</form>').'
	        </td>
	    </tr>
	</table>
	<script type="text/javascript">
	document.getElementById("javascript_detect").style.display="none";
	if(document.getElementById("FormEnter")){ document.getElementById("FormEnter").style.display=""; }
	function foc(){ var e = document.getElementById("name"); if(e){ e.focus(); } }
	if (window.addEventListener){ window.addEventListener("load", foc, false); }else if (window.attachEvent){ window.attachEvent("onload", foc); }
	</script>
	</body>
	</html>';
}
?>
