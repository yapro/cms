<?php
// скрипт показывает информацию на странице /я/юзер/любимое

$pages_rating = pages_rating($visitor_id);

if(defined('PAGE_USER_ID')){
	
	$r = $GLOBALS['SYSTEM']['users']-> data(PAGE_USER_ID);
	$h1 = htmlAccess($r['username']).' - любимое';
	
}else{
	$h1 = 'Любимое посетителя № '.PAGE_VISITOR_ID;
}
$h1 .= ' ('.$pages_rating_link.count($pages_rating).' шт.)';

if($pages_rating){
	
	if($visitor_id == $_SESSION['SYSTEM']['VISITOR_ID']){
		$it_user_pages_rating = true;
	}
	
	$url_20111125 = $GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'];
	$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'] = $url_page_20111125;
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_pages_rating.php');
	
	$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'] = $url_20111125;
	
}else{
	$GLOBALS['SYSTEM']['noindex_nofollow'] = true;
	
	if($visitor_id == $_SESSION['SYSTEM']['VISITOR_ID']){
		
		$document .= '<p>Вы еще ничего не оценивали'.($GLOBALS['SYSTEM']['user']['user_id']?'':' или не <a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">авторизованы</a> на нашем сайте').'.</p>';
		
	}else{
		$document .= '<p>'.(defined('PAGE_USER_ID')? 'Пользователь':'Посетитель').' еще ничего не оценивал.</p>';
	}
}
?>
