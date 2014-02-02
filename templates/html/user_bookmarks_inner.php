<?php
// скрипт показывает информацию на странице /я/юзер/закладки

$bookmark_page = bookmark_pages($visitor_id);

if(defined('PAGE_USER_ID')){
	
	$r = $GLOBALS['SYSTEM']['users']-> data(PAGE_USER_ID);
	$h1 = htmlAccess($r['username']).' - закладки';
	
}else if(PAGE_VISITOR_ID == $_SESSION['SYSTEM']['VISITOR_ID']){
	$h1 = 'Ваши закладки';
}else{
	$h1 = 'Закладки посетителя № '.PAGE_VISITOR_ID;
}
$h1 .= ' ('.$bookmark_page_link.count($bookmark_page).' шт.)';

if($bookmark_page){
	
	$url_20111125 = $GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'];
	$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'] = $url_page_20111125;
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_bookmarks.php');
	
	$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'] = $url_20111125;
	
}else{
	$GLOBALS['SYSTEM']['noindex_nofollow'] = true;
	
	if($visitor_id == $_SESSION['SYSTEM']['VISITOR_ID']){
		
		$document .= '<p>Вы еще не делали закладки'.($GLOBALS['SYSTEM']['user']['user_id']?'':' или не <a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">авторизованы</a> на нашем сайте').'.</p>';
		
	}else{
		$document .= '<p>'.(defined('PAGE_USER_ID')? 'Пользователь':'Посетитель').' еще не делал закладки.</p>';
	}
}
?>