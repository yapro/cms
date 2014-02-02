<?php
// скрипт определяет номер текущей страницы и поисковую фразу с всевозможными проверками и ведением логов
if($GLOBALS['SYSTEM']['page_url'][ $this->id ]['url_type']!='1'){ header404('тип страницы не листинг : '.__FILE__); }

$additional = explode(url(), $_SERVER['REQUEST_URI']);

if($_POST['search']){
	
	$folio = 0;
	$search = trim($_POST['search']);
	
}else if($additional['1']){// если есть что-то больше чем /url
	
	if($_GET['p'] && is_numeric($_GET['p'])){// если определенная страница
		
		$folio = $_GET['p'];
		$search = $_GET['search'];
		
	}else{// если первая страница поиска
		
		$folio = 0;
		$search = $_GET['search'];
		
	}
	$search = trim(urldecode($search));
	
}else{
	
	$folio = 0;
	$search = '';
	
}
if(!$folio && $this->id==$GLOBALS['SYSTEM']['config']['yapro_index_id'] && $_SERVER['REQUEST_URI']!='/'){// проверка на 1-ую страницу листинга, если 1-ая страница - это индексная страница
	
	header301('/', '4-'.__FILE__);
	
}
if($search){
	$search = htmlAccess($search, false);
}
?>