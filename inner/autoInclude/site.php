<?php
$GLOBALS['SYSTEM']['pages_bookmarks'] = array();
function bookmark_pages($visitor_id=0){
	
	if(!$visitor_id){ return ; }
	
	if(isset($GLOBALS['SYSTEM']['pages_bookmarks'][$visitor_id])){
		return $GLOBALS['SYSTEM']['pages_bookmarks'][$visitor_id];
	}
	
	$a = array();
	if($q=mysql_query('SELECT page_id, time_created FROM '.P.'pages_bookmarks WHERE visitor_id = '.$visitor_id)){
		while($r = mysql_fetch_assoc($q)){
			$a[ $r['page_id'] ] = $r['time_created'];
		}
	}
	$GLOBALS['SYSTEM']['pages_bookmarks'][$page_id] = $a;
	return $a;
}

$GLOBALS['SYSTEM']['pages_rating'] = array();
function pages_rating($visitor_id=0){
	
	if(!$visitor_id){ return ; }
	
	if(isset($GLOBALS['SYSTEM']['pages_rating'][$visitor_id])){
		return $GLOBALS['SYSTEM']['pages_rating'][$visitor_id];
	}
	
	$a = array();
	if($q = mysql_query('SELECT page_id, time_created FROM '.P.'pages_rating WHERE visitor_id = '.$visitor_id)){
		while($r = mysql_fetch_assoc($q)){
			$a[ $r['page_id'] ] = $r['time_created'];
		}
	}
	$GLOBALS['SYSTEM']['pages_rating'][$page_id] = $a;
	return $a;
}

$GLOBALS['SYSTEM']['user_page'] = '/i/'.$_SESSION['SYSTEM']['VISITOR_ID'];
if($GLOBALS['SYSTEM']['user']['user_id']){
	$user_page = @mysql_fetch_row(mysql_('SELECT user_page FROM '.P.'users_pages WHERE user_id = '.$GLOBALS['SYSTEM']['user']['user_id']));
	if($user_page['0']){
		$GLOBALS['SYSTEM']['user_page'] = '/i/'.$user_page['0'];
	}else{
		//$x = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors WHERE user_id = '.$GLOBALS['SYSTEM']['user']['user_id']));
		$GLOBALS['SYSTEM']['user_page'] = '/i/u'.$GLOBALS['SYSTEM']['user']['user_id'];
	}
}else if(!$_SESSION['SYSTEM']['VISITOR_ID']){
	$GLOBALS['SYSTEM']['user_page'] = '#';//$GLOBALS['SYSTEM']['user_links']['start'];
}
?>