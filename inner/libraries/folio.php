<?php
// скрипт определяет номер текущей страницы с всевозможными проверками и ведением логов
// объявление переменной $yapro_without_check_page_ext отменяет обязательное использование расширения страницы

if($GLOBALS['SYSTEM']['page_url'][ $this->id ]['url_type']!='1'){
	header404('тип страницы не листинг : '.__FILE__);
}

if(count($_GET) > 1 && !$GLOBALS['YANDEX_METRIKA']){
	header404('множество $_GET переменных : '.__FILE__);
}

if($_GET['p'] && !is_numeric($_GET['p'])){
	header404('неправильная $_GET переменная : '.__FILE__);
}

$folio = (int)$_GET['p'];

// проверка на 1-ую страницу листинга, если 1-ая страница - это индексная страница
if(!$folio && $this->id==$GLOBALS['SYSTEM']['config']['yapro_index_id'] && $_SERVER['REQUEST_URI']!='/'){
	header301('/', '6-'.__FILE__);
}
if($folio && $folio<0){// проверка на минусовое значение
	header404('folio указана с минусом : '.__FILE__);
}
?>
