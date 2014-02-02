<?php
// скрипт показывает страницы отобранные пользователем в закладки
$doc = $document;
$document = '';

if($bookmark_page){
	
	$spec_kids_page_table = 'p.';
	$spec_kids_from = 'FROM '.P.'pages_bookmarks AS pb
	LEFT JOIN '.P.'pages AS p ON p.page_id = pb.page_id';
	$spec_kids_order = 'AND pb.visitor_id = '.$visitor_id.' ORDER BY pb.id DESC';
	
	$where = 'page_id IN (';
	foreach($bookmark_page as $page_id => $time){
		$where .= $page_id.',';
	}
	$where_important = substr($where,0,-1).')';
	
	//$sort_info = 'Отсортировано по дате добавления в закладки';
	
	include($_SERVER['DOCUMENT_ROOT'].'/inner/catalog.php');
	
}else{
	$document .= 'Вы еще не делали закладки'.($GLOBALS['SYSTEM']['user']['user_id']?'':' или не авторизованы на нашем сайте').'.';
}

$document = $doc.$document;
?>