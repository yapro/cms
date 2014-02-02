<?php
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
@header("content-type:text/html; charset=UTF-8");// сообщаем кодировку документов
setlocale(LC_ALL, 'ru_RU.UTF-8');// устанавливаем локальную информацию.
mb_internal_encoding("UTF-8");// устанавливаем внутреннюю кодировку символов
$GLOBALS['WITHOUT_CONFIG'] = true;// объявление переменной обеспечивает работу системы адмнистрирования без заданных параметров сайта
//---------------------------------------------------------------------------------------------------------------------------------------------------
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
//---------------------------------------------------------------------------------------------------------------------------------------------------
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/cookies.php');// подгрузка необходимых cookies функций
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/idna_convert.class.php');
$IDN = new idna_convert();// поддержка кирилических доменов
if(substr($_SERVER['HTTP_HOST'],0,4)=='xn--'){// поддержка кирилических доменов
	$_SERVER['HTTP_HOST_NAME'] = $IDN->decode($_SERVER['HTTP_HOST']);
}else{
	$_SERVER['HTTP_HOST_NAME'] = $_SERVER['HTTP_HOST'];
}
//---------------------------------------------------------------------------------------------------------------------------------------------------
// имя директории системы
$system_dir_name = array_reverse(explode('/',str_replace('\\','/',__FILE__)));
$_SERVER['SYSTEM']['NAME'] = $system_dir_name['2'];

// полный путь к директории системы
$_SERVER['SYSTEM']['PATH'] = $_SERVER['DOCUMENT_ROOT'].'/'.$_SERVER['SYSTEM']['NAME'].'/';

// URL системной директории
$_SERVER['SYSTEM']['URL'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['SYSTEM']['NAME'].'/';

// URL к директори системы от корня сайта
$_SERVER['SYSTEM']['URL_PATH'] = '/'.$_SERVER['SYSTEM']['NAME'].'/';

// путь к директории исполняемого модуля от корня системы (админки) /admin/modules/plugin/
$_SERVER['SYSTEM']['MODULE_DIR_PATH'] = mb_substr($_SERVER['SCRIPT_NAME'], 0, mb_strrpos($_SERVER['SCRIPT_NAME'], '/')+1);

// URL к директории исполняемого модуля
$_SERVER['SYSTEM']['URL_MODULE_DIR'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SYSTEM']['MODULE_DIR_PATH'];

// URL к исполняемому модулю
$_SERVER['SYSTEM']['URL_MODULE_PATH'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

// путь к исполняемому модулю от корня системы (админки)
$_SERVER['SYSTEM']['MODULE_SCRIPT_PATH'] = str_replace($_SERVER['SYSTEM']['URL_PATH'], '', $_SERVER['SCRIPT_NAME']);

// полный путь к директории исполняемого модуля (/root/site/module/)
$_SERVER['SYSTEM']['MODULE_DIR'] = $_SERVER['DOCUMENT_ROOT'].$_SERVER['SYSTEM']['MODULE_DIR_PATH'];

// полный путь к исполняемому модулю
$_SERVER['SYSTEM']['MODULE_PATH'] = $_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'];
include_once($_SERVER['SYSTEM']['PATH'].'libraries/functions.php');
//---------------------------------------------------------------------------------------------------------------------------------------------------
// переменные для поддержки старой версии:
$_SERVER['SYSTEM']['URL_PATH'] = $_SERVER['SYSTEM']['URL_PATH'];
//---------------------------------------------------------------------------------------------------------------------------------------------------
if(!$GLOBALS['SYSTEM']['user']['user_id']){
	start_header(1);
}

$user = $GLOBALS['SYSTEM']['user'];

if($user['allow_ip']){
	$allow = false;
	$ex = explode(',', $user['allow_ip']);
	
	// Вход в Систему Администрирования только с IP-адресов (через запятую)
	include_once($_SERVER['SYSTEM']['PATH'].'libraries/allow_ip.php');
	$ex2 = @explode(',', $allow_ip);
	
	$a = array_merge($ex,$ex2);
	foreach($a as $ip){
		if($ip){
			$ip = trim($ip);
			if($ip==$_SERVER['REMOTE_ADDR']){
				$allow = true;
			}
		}
	}
	if(!$allow){ start_header(5); }
}

//---------------------------------------------------------------------------------------------------------------------------------------------------

// функция вывода шапки таблиц
function Head($name='Имя не задано', $left=null, $right='', $help=''){
	
	if($left){
		if(is_array($left)){
			foreach($left as $value){
				$ex = explode(_, $value);
				$img .= '<td class="Image">'.($ex['0']?'<a href="'.$ex['0'].'">':'').'<img src="'.$ex['1'].'" title="'.$ex['2'].'">'.($ex['0']?'</a>':'').'</td>';
			}
		}else{
			$img = '<td class="Image">'.$left.'</td>';
		}
	}
	
	if(!$_SERVER['SYSTEM']['TABLE_NAME']){ $_SERVER['SYSTEM']['TABLE_NAME'] = 'Имя не задано'; }
	
	return '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="NameTable">
    <tr>
        '.($img?$img:'<td class="Image"><img src="images/logotypes/m-www.png"></td>').'
        <td class="Name" width="100%">'.$name.'</td>
    	'.$right.'
		<td class="Help" title="Информация о модуле"><a href="'.($help?$help:'http://yapro.ru/forum/').'" target="_blank"><img src="images/logotypes/help.gif"></a></td>
    </tr>
</table>';
	
}
//---------------------------------------------------------------------------------------------------------------------------------------------------
// проверка на права доступа
function access($x){
	$a = $GLOBALS['SYSTEM']['module_id__access'][ $GLOBALS['SYSTEM']['module_id'] ];
	if($x=='r' && ($a=='1' || $a=='4' || $a=='5' || $a=='7')){
		return true;
	}else if($x=='w' && ($a=='2' || $a=='4' || $a=='6' || $a=='7')){
		return true;
	}else if($x=='d' && ($a=='3' || $a=='5' || $a=='6' || $a=='7')){
		return true;
	}else if($x==$a){// пример: 5==5 или 7==7
		return true;
	}else{
		return false;
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------------------
class communications {// проверка на права доступа
	
	function permissions(){// данную ф-ю разрешается запускать только после подключения библиотеки pages.php
		
		if($GLOBALS['pages']){// нахожу module_id главного раздела страниц
			
			$this->page_id__allow = array();
			
			$module_id = @mysql_fetch_row(mysql_query("SELECT parent_id FROM ".P."modules WHERE module_id='".$GLOBALS['SYSTEM']['module_id']."'"));
			
			// нахожу настройки модуля
			if($r = @mysql_fetch_assoc(mysql_query("SELECT form_id, permission_id FROM ".P."communications 
			WHERE module_id='".$module_id['0']."'"))){
				
				$this->form_id = $r['form_id'];
				
				// определяю доступ к страницам
				if($r['permission_id'] && $q = mysql_('SELECT page_id, allow FROM '.P.'pages_permissions_data 
				WHERE permission_id='.$r['permission_id'])){
					while($r =mysql_fetch_assoc($q)){
						$this->page_id__allow[ $r['page_id'] ] = $r['allow'];
					}
					if($this->page_id__allow){
						$this->apply_allow();// применяю настройки доступа
					}
				}
			}
		}
	}
	function apply_allow($parent_id=0){// назначаем права для каждого page_id согласно настройкам модуля для текущего пользователя админки
		
		if(!$this->page_id__allow || !$GLOBALS['pages']->parent_id__page_id[ $parent_id ]){ return ; }
		
		foreach($GLOBALS['pages']->parent_id__page_id[ $parent_id ] as $k=>$v){
			
			if($this->page_id__allow[ $k ]==6){
				
				$this->close_children = true;// запрет доступа к дочерним страницам - включить
				
			}else if($this->page_id__allow[ $k ]==5){
				
				$this->close_children = true;// запрет доступа к дочерним страницам - включить
				if($this->open_children){ $GLOBALS['pages']->page_id__access[ $k ] = true; }// доступ к странице - есть
				
			}else if($this->page_id__allow[ $k ]==4){// доступ к странице - нет
				
			}else if($this->page_id__allow[ $k ]==3){
				
				$open_children = true;// доступ к дочерним страницам - включить
				$GLOBALS['pages']->page_id__access[ $k ] = true;// доступ к странице - есть
				
			}else if($this->page_id__allow[ $k ]==2){
				
				if($this->open_children){ $GLOBALS['pages']->page_id__access[ $k ] = true; }// доступ к странице - есть
				$open_children = true;// доступ к дочерним страницам - включить
				
			}else if($this->page_id__allow[ $k ]==1 || $this->open_children){
				
				$GLOBALS['pages']->page_id__access[ $k ] = true;// доступ к странице - есть
				
			}
			
			$before_open_children = $this->open_children? true : false;// создаем переменную говорящую о том, был ли дан доступ к дочерним страницам - раньше!
			
			if($GLOBALS['pages']->parent_id__page_id[$k] && !$this->close_children){
				
				if($open_children){ $this->open_children = true; }// доступ к дочерним страницам - включить
				
				$this-> apply_allow($k);
			}
			
			if($open_children && !$before_open_children){ $this->open_children = false; } $open_children = false;// доступ к дочерним страницам - отключить
			
			// отменяем настройки доступа для последующей итерации
			if($this->page_id__allow[ $k ]==6){
				
				$this->close_children = false;// запрет доступа к дочерним страницам - отключить
				
			}else if($this->page_id__allow[ $k ]==5){
				
				$this->close_children = false;// запрет доступа к дочерним страницам - отключить
				
			}
		}
	}
}
$GLOBALS['SYSTEM']['communications'] = new communications();
//---------------------------------------------------------------------------------------------------------------------------------------------------
if(!$GLOBALS['SYSTEM']['module_id'] && $_GET['moduleID']){
	
	$GLOBALS['SYSTEM']['module_id'] = (int)$_GET['moduleID'];
	
}else if(!$GLOBALS['SYSTEM']['module_id']){// если ID модуля не задано, пробуем найти его из базы по пути к модулю
	
	$d = mysql_fetch_assoc(mysql_("SELECT module_id FROM ".P."modules WHERE path="._.$_SERVER['SYSTEM']['MODULE_SCRIPT_PATH']._));
	$GLOBALS['SYSTEM']['module_id'] = $d['module_id'];
	
}
if(!$GLOBALS['SYSTEM']['module_id']){ start_header(4); }
//---------------------------------------------------------------------------------------------------------------------------------------------------
// находим возмоные доступы пользователя
if($GLOBALS['SYSTEM']['user']['user_id'] && $q = mysql_query("SELECT * FROM ".P."access WHERE user_id = ".$GLOBALS['SYSTEM']['user']['user_id'])){
	while($r=mysql_fetch_assoc($q)){
		if($r['access']>'0'){
			$GLOBALS['SYSTEM']['module_id__access'][$r['module_id']] = $r['access'];
			$GLOBALS['SYSTEM']['access'] = ($r['access']>$GLOBALS['SYSTEM']['access'])? $r['access'] : $GLOBALS['SYSTEM']['access'];
		}
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------------------

// если доступа ни к одному сайту нет ИЛИ нет доступа к текущему модулю - выдаем сообщение о невозможном доступе
if(!$GLOBALS['SYSTEM']['module_id__access'][ $GLOBALS['SYSTEM']['module_id'] ]){
	
	// выдаем сообщение о невозможном доступе
	echo '<html><head></head><body style="background-color: #FFFFFF;"><table align="center" border="0" height="100%"><tr><td style="text-align: center;"><img src="'.$_SERVER['SYSTEM']['URL'].'images/elements/stop.jpg" border="0"><br>- нет доступа -<br><a href="'.$_SERVER['SYSTEM']['URL'].'index.php?exit">Переавторизоваться »</a></td></tr></table></body></html>';
	
	exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------------------
// запоминаем последий шаг
if(!$GLOBALS['SYSTEM']['notSaveUrl'] && !$_GET['ajax_result_left_frame'] && !$_GET['ajaxUploadFile']){ cookie('save_url', $_SERVER['REQUEST_URI']); }
//---------------------------------------------------------------------------------------------------------------------------------------------------
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/Browser.php');// подгрузка необходимых функций

// шапка любой страницы
$_SERVER['SITE_HEADER'] = ($GLOBALS['!DOCTYPE']?'<html>':'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-ru" xml:lang="ru-ru">').'
<head>
<title>CMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="ru">
<BASE href="'.$_SERVER['SYSTEM']['URL'].'">
<link href="css/system.css?v2" type="text/css" rel="stylesheet">
<!--[if IE]><link href="css/ie.css" rel="stylesheet" media="all" /><![endif]-->
<script type="text/javascript" src="/js/jquery-1.7.1.js" charset="utf-8"></script>
<script type="text/javascript" src="/js/system.js?v3" charset="utf-8"></script>
<script type="text/javascript" src="js/system.js?v8" charset="utf-8"></script>
<script type="text/javascript" src="/js/jquery.mousewheel.js" charset="utf-8"></script>
<script type="text/javascript" src="/js/jquery.mousewheel.Select.js" charset="utf-8"></script>
<link type="text/css" rel="stylesheet" href="/js/jquery.yapro.alert/latest.css">
<script type="text/javascript" src="/js/jquery.yapro.alert/latest.js" charset="utf-8"></script>
<script type="text/javascript" src="/outer/cookies.php?cookies_javascript_object" charset="utf-8"></script>
<script type="text/javascript" src="/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
</head>
<body bgcolor="white">
<input type="hidden" id="ADMIN_URL" value="'.$_SERVER['SYSTEM']['URL'].'"><!-- URL админки -->
<input type="hidden" id="ADMIN_URL_PATH" value="'.$_SERVER['SYSTEM']['URL_PATH'].'"><!-- URL админки без http://site.ru -->
<input type="hidden" id="ADMIN_URL_MODULE_DIR" value="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'"><!-- URL к директории текущего модуля -->
<input type="hidden" id="ADMIN_URL_MODULE_PATH" value="'.$_SERVER['SYSTEM']['URL_MODULE_PATH'].'"><!-- URL к текущему модулю -->
<input type="hidden" id="ADMIN_MODULE_DIR_PATH" value="'.$_SERVER['SYSTEM']['MODULE_DIR_PATH'].'"><!-- /admin/modules/plugin/ -->
<input type="hidden" id="ADMIN_MODULE_SCRIPT_PATH" value="'.$_SERVER['SYSTEM']['MODULE_SCRIPT_PATH'].'"><!-- /admin/modules/plugin/file.php -->
<input type="hidden" id="ADMIN_MODULE_ID" value="'.$GLOBALS['SYSTEM']['module_id'].'"><!-- Идентификатор текущего модуля -->
<input type="hidden" id="ADMIN_SITE_NAME" value="'.$_SERVER['HTTP_HOST'].'"><!-- Доменное имя текущего сайта -->
<input type="hidden" id="ADMIN_MODULE_ACCESS_W" value="'.(int)access('w').'"><!-- Доступ к текущему модулю на запись -->
<input type="hidden" id="ADMIN_MODULE_ACCESS_D" value="'.(int)access('d').'"><!-- Доступ к текущему модулю на удаление -->
<input type="hidden" id="ADMIN_PAGE_ID" value="'.$_GET['page_id'].'"><!-- Идентификатор текущего сайта -->
<script type="text/javascript">
var openBrowserPuth = "'.$_SERVER['SYSTEM']['URL'].'modules/files/right.php?insert=1";
var openBrowserSite = "http://'.$_SERVER['HTTP_HOST'].'";
</script>
<style>
.submit {
	padding: '.(($GLOBALS['browser']->short == 'opera')?'2px 4px':'1px 3px').';
}
</style>';
//---------------------------------------------------------------------------------------------------------------------------------------------------
$_SERVER['SYSTEM']['URL_MODULE_SELF'] = $_SERVER['SYSTEM']['URL_MODULE_PATH'].'?moduleID='.$GLOBALS['SYSTEM']['module_id'];
//---------------------------------------------------------------------------------------------------------------------------------------------------
define('RESULT_FILE_PATH', $_SERVER['SYSTEM']['PATH'].'results/'.$GLOBALS['SYSTEM']['module_id'].'.'.cookie('visitor_hash').'.html');
function result($data=' '){
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_GET['ajax_result_left_frame']){
		echo RESULT_FILE_PATH;
		write_($data, RESULT_FILE_PATH);
	}else{
		echo trim($data);
	}
}
result(' ');// начинаем сохранение результата
?>
