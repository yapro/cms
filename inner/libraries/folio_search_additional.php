<?php
// скрипт определяет номер текущей страницы и поисковую фразу с всевозможными проверками и ведением логов

if($GLOBALS['SYSTEM']['page_url'][ $this->id ]['url_type']!='1'){
	header404('тип страницы не листинг : '.__FILE__);
}

if(count($_GET) > 1){
	header404('множество $_GET переменных : '.__FILE__);
}

if($_GET['p'] && !is_numeric($_GET['p'])){
	header404('неправильная $_GET переменная : '.__FILE__);
}

$folio = (int)$_GET['p'];

$e = explode('?', $_SERVER['REQUEST_URI']);
$additional = explode(url(0,3).'/', $e['0']);

if($additional['1']){// если есть что-то больше чем /url
	// в именах актеров часто встречаются точки, пример: Kyle O. Ingleman - но для поисковиков это расширение страниц, поэтому эт плохо
	if(strstr($additional['1'],'.') || strstr($additional['1'],'/') || strstr($additional['1'],',')){// если есть запрещенный символ - значит не поисковая фраза
		header404('есть запрещенный символ : '.__FILE__);
	}
	
	$search = trim(rawurldecode($additional['1']));
	
}else{
	
	$search = '';
	
}
if($search){
	$search = htmlAccess($search, false);
}
?>
