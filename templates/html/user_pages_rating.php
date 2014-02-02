<?php
// скрипт показывает любимые страницы пользователя (оцененные страницы)

$doc = $document;
$document = '';

if($pages_rating){
	
	$spec_kids_page_table = 'p.';
	$spec_kids_from = 'FROM '.P.'pages_rating AS pr
	LEFT JOIN '.P.'pages AS p ON pr.page_id = p.page_id';
	$spec_kids_order = 'AND pr.visitor_id = '.$visitor_id.' ORDER BY pr.rating_id DESC';
	
	$where = 'page_id IN (';
	foreach($pages_rating as $page_id => $time){
		$where .= $page_id.',';
	}
	$where_important = substr($where,0,-1).')';
	
	$sort_info = 'Отсортировано по дате выставления Ваших оценок';
	
	include($_SERVER['DOCUMENT_ROOT'].'/inner/catalog.php');
	
}else{
	$document = 'Вы еще ничего не оценивали'.($GLOBALS['SYSTEM']['user']['user_id']?'':' или не авторизованы на нашем сайте').'.';
}

$document = $doc.$document;
?>