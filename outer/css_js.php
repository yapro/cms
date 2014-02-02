<?php
// скрипт объединения CSS и JavaScript файлов
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
//----------------------------------------------------------------------------------------------------------------------------------
if(!$_GET || count($_GET)>1 || ($_GET['a']!='js' && $_GET['a']!='css') ){
	exit;
}
//----------------------------------------------------------------------------------------------------------------------------------
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// подгрузка необходимых функций
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/Browser.php');// подгрузка необходимых функций
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');// интеграция с форумом
//----------------------------------------------------------------------------------------------------------------------------------
if($GLOBALS['SYSTEM']['user']['user_id']!=2){ echo '{"msg":"В доступе отказано"}'; exit; }// доступ только админу
//----------------------------------------------------------------------------------------------------------------------------------
$a = array();

$a['css'] = array(// CSS файлы
'/css/typography.css',
'/css/style.css',
'/js/jgrowl/jgrowl.css',
'/js/jquery.yapro.alert/latest.css',
'/js/fancybox/jquery.fancybox-1.3.4.css',
'/js/bookmarks/bookmarks.css',
'/js/rating/rating.css',
'/js/paginator/paginator.css',
'/js/tipsy/tipsy.css',
'/css/poll.css',
'/css/important.css'
);

$a['js'] = array(// JavaScript файлы
'/js/jquery-1.7.1.js',
'/js/timers.js',
'/js/system.js',
'/js/jgrowl/jgrowl.js',// подсказки в правом верхнем углу экрана
'/js/jquery.mousewheel.js',// обработка движения скрола
'/js/autoresize.jquery.min.js',// автоматически увеличивает высоту TEXTAREA при его заполнении
'/js/jquery.yapro.Spelling/latest.js',// нашли ошибку?
'/js/jquery.yapro.alert/latest.js',
'/js/fancybox/jquery.fancybox-1.3.4.pack.js',
'/js/rating/rating.js',
'/js/scrollTo.js',
'/js/paginator/paginator.js',
'/js/tipsy/tipsy.js',// title подсказки
'/js/site.js'
);

$s = '';
foreach($a[ $_GET['a'] ] as $path){
	if(!$path){ continue; }
	$s .= "\n".'/* '.$path.' */'."\n";
	$e = explode('://',$path);
	if(!$e['1']){
		$path = $_SERVER['DOCUMENT_ROOT'].$path;
	}
	$x = @file_get_contents($path);
	if($x){
		$s .= $x;
	}
}
if($s){
	$path = '/cache/css_js/'.time().'.'.$_GET['a'];
	write_($s, $_SERVER['DOCUMENT_ROOT'].$path);// сохраняю файл
	if($z = gzopen($_SERVER['DOCUMENT_ROOT'].$path.'.gz', 'w9')){// сохраняю зипованую версию файла
		gzputs($z, $s);
		gzclose($z);
	}
	write_($path, $_SERVER['DOCUMENT_ROOT'].'/cache/css_js/'.$_GET['a']);// сохраняю путь к файлу
}
echo '{"msg":"ok"}';
?>