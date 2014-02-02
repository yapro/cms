<?php
// скрипт показывает закладки на странице /i/юзер

$subdomain = $dir = rawurldecode($e['2']);

include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_detect.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_info.php');

if(defined('PAGE_USER_ID') && PAGE_USER_ID == $GLOBALS['SYSTEM']['user']['user_id']){
	
	$GLOBALS['SYSTEM']['page_user_profile'] = true;
	
}



$max_important = 5;// кол-во строк из закладок
$bookmark_page = bookmark_pages($visitor_id);
$bookmark_page_link = '<a href="'.$_SERVER['REQUEST_URI'].'/bookmarks">';
$document .= '<hr><h3>Закладки ('.$bookmark_page_link.count($bookmark_page).' шт.</a>)</h3>';
if($bookmark_page){
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_bookmarks.php');
	
	$GLOBALS['poster_list_info'] = $GLOBALS['system_listing'] = '';// удаляем Отсортировано по... И листинг
	
	if($count > $max_important){
		$document .= '<div style="text-align:center">'.$bookmark_page_link.'Смотреть все закладки »</a></div>';
	}
	
}else{
	
	$GLOBALS['SYSTEM']['noindex_nofollow'] = true;
	
	if($visitor_id == $_SESSION['SYSTEM']['VISITOR_ID']){
		
		$document .= '<p>Вы еще не делали закладки'.($GLOBALS['SYSTEM']['user']['user_id']?'':' или не <a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">авторизованы</a> на нашем сайте').'.</p>';
		
	}else{
		$document .= '<p>'.(defined('PAGE_USER_ID')? 'Пользователь':'Посетитель').' еще не делал закладки.</p>';
	}
}



$max_important = 5;// кол-во строк из закладок
$pages_rating = pages_rating($visitor_id);
$pages_rating_link = '<a href="'.$_SERVER['REQUEST_URI'].'/rating">';
$document .= '<hr><h3>Любимое ('.$pages_rating_link.count($pages_rating).' шт.</a>)</h3>';
if($pages_rating){
	
	if($visitor_id == $_SESSION['SYSTEM']['VISITOR_ID']){
		$it_user_pages_rating = true;
	}
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_pages_rating.php');
	
	$GLOBALS['poster_list_info'] = $GLOBALS['system_listing'] = '';// удаляем Отсортировано по... И листинг
	
	if($count > $max_important){
		$document .= '<div style="text-align:center">'.$pages_rating_link.'Смотреть всё любимое »</a></div>';
	}
	
}else{
	
	$GLOBALS['SYSTEM']['noindex_nofollow'] = true;
	
	if($visitor_id == $_SESSION['SYSTEM']['VISITOR_ID']){
		
		$document .= '<p>Вы еще ничего не оценивали'.($GLOBALS['SYSTEM']['user']['user_id']?'':' или не <a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">авторизованы</a> на нашем сайте').'.</p>';
		
	}else{
		$document .= '<p>'.(defined('PAGE_USER_ID')? 'Пользователь':'Посетитель').' еще ничего не оценивал.</p>';
	}
}
?>