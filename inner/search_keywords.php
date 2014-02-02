<?php
// скрипт собирает данные о пользовательском поиске по сайту

$search_decode = trim(rawurldecode(urldecode($search)));

$keyword_id = @mysql_fetch_row(mysql_("SELECT keyword_id FROM ".P."search_keywords WHERE keyword = "._.$search_decode._.""));

if($keyword_id['0']){
	mysql_query("UPDATE ".P."search_keywords SET 
		".($GLOBALS['SYSTEM']['user']['user_id']?'users = users+1,':'')."
		amount = amount+1,
		found = ".count($page_id__found_result).",
		last_time = ".time()."
	WHERE keyword_id = ".$keyword_id['0']);
}else{
	mysql_("INSERT INTO ".P."search_keywords SET 
		".($GLOBALS['SYSTEM']['user']['user_id']?'users = 1,':'')."
		amount = 1,
		keyword = "._.$search_decode._.",
		found = ".count($page_id__found_result).",
		first_time = ".time().",
		last_time = ".time()."
	");
	$keyword_id['0'] = mysql_insert_id();
}
mysql_("INSERT INTO ".P."search SET 
	user_id = ".(int)$GLOBALS['SYSTEM']['user']['user_id'].",
	user_ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._."),
	keyword_id = ".$keyword_id['0']);
?>
