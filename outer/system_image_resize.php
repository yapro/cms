<?php
// скрипт ресайза изображений
error_reporting(E_COMPILE_ERROR | E_ERROR | E_PARSE);

// объявляю доступные для ресайза расширения изображений файлов
$access_type['jpg']++;
$access_type['jpeg']++;
$access_type['gif']++;
$access_type['png']++;

if(!$_GET['path']){ exit; }

if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');

$path = str_replace('+', ' ', $_GET['path']);// путь к файлу
$path_root = $_SERVER['DOCUMENT_ROOT'].$path;// путь к файлу от корня

$type = filetype_($path, true);// нахожу тип файла

if(!$access_type[$type] || !is_file($path_root)){// проверяю разрешение на тип файла И реальное расположение файла
	// закомментил чтобы предотвратить возможной зацикливание header("location: http://".$_SERVER['HTTP_HOST'].'/'.$_GET['img'].'.'.$_GET['type']);
	exit;
}

$file_name = array_pop(explode('/',$path)).'.'.($_GET['w']?'w'.$_GET['w']:'h'.$_GET['h']).'.'.$type;// задаю имя файла с параметрами (ширина и высота) + 2-ое расширение

$path_dir = '/';// директория расположения файла
$ex = explode('/',$path);
array_pop($ex);
foreach($ex as $name){
	if($name){
		$path_dir .= $name.'/';
	}
}

$path_cache = $_SERVER['DOCUMENT_ROOT'].'/cache'.$path_dir.$file_name;// путь к кэш-файлу

$blank = false;

if(!is_file($path_cache)){// пробуем сделать превью изображения
	
	// автоматическое создание директорий к превью файлам (может не работать на некоторых хостингах в связи с ограниченными правами доступа)
	$error = path($_SERVER['DOCUMENT_ROOT'].'/', 'cache'.$path_dir);
	
	if($error){
		
		$blank = 1;
		log_(__FILE__.' : '.$error);
		
	}else{
		
		$error = img_preview($path_root, $path_cache, $_GET['w'], $_GET['h']);
		
		if($error){
			$blank = 2;
		}
	}
	
}

if($blank){
	//echo $blank; exit;
	$size['mime'] = 'image/gif';
	$path_cache = $_SERVER['DOCUMENT_ROOT'].'/cache/outer/system_image_resize.gif';
	write_(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/outer/system_image_resize.gif'), $path_cache);// сохраняем файл, чтобы каждый раз не выполнять ресайз невозможного к ресайзу изображения
}else{
	$size = getimagesize($path_cache);
}
header("Content-type: ".$size['mime']);
echo file_get_contents($path_cache);
?>