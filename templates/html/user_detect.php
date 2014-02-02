<?php
// скрипт находит пользователя или посетителя и объявляет одну из констант: PAGE_USER_ID или PAGE_VISITOR_ID
$user_id = array();
if(!is_numeric($subdomain)){
	
	$user_id = @mysql_fetch_row(mysql_('SELECT user_id FROM '.P.'users_pages WHERE user_page = '._.$subdomain._));
}
if($user_id['0']){
	define('PAGE_USER_ID', $user_id['0']);
}else{
	if(is_numeric($subdomain)){
		$x = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors WHERE user_id = 0 AND visitor_id = '._.$subdomain._));
		if(!$x['0']){
			header404('1. '.__FILE__);
		}
		define('PAGE_VISITOR_ID', $x['0']);
	}else{// возможно это домен юзера
		$user_id = mb_substr($subdomain,1);// избавляемся от буквы u впереди домена u12345
		if(!is_numeric($user_id)){
			header404('2. '.__FILE__);
		}else{
			
			$x = @mysql_fetch_row(mysql_('SELECT user_page FROM '.P.'users_pages WHERE user_id = '.$user_id));
			if($x['0']){// если у пользователя уже есть субдомен
				//if($GLOBALS['SYSTEM']['config']['yapro_subdomains']){
				//header("location: http://".$x['0'].'.'.$GLOBALS['SYSTEM']['config']['yapro_http_host'], true, 301);
				header("location: http://".$GLOBALS['SYSTEM']['config']['yapro_http_host'].'/i/'.rawurlencode($x['0']), true, 301);
				exit;
			}
			
			$r = @mysql_fetch_assoc(mysql_query('SELECT user_email FROM '.F.'users WHERE user_id = '.$user_id.' AND user_type IN (0,3)'));
			if(!$r['user_email']){
				header404('3. '.__FILE__);
			}else{
				$e = explode('@',$r['user_email']);
				
				$eng_name = trim(str_replace('_-_', '-', preg_replace('/[\-]{2,}/', '-', preg_replace('/[\_]{2,}/', '_', 
				preg_replace('/[^-a-z0-9_]/sUi', '_', translite(strtolower_(trim($e['0'])), 's'))))));
				
				if($eng_name){
					if(is_numeric($eng_name)){
						$eng_name = 'u'.$eng_name;
					}
					$x = @mysql_num_rows(mysql_('SELECT user_id FROM '.P.'users_pages WHERE user_page = '._.$eng_name._));
					if($x){
						$eng_name = 'u'.$user_id;
					}
				}else{
					$eng_name = 'u'.$user_id;
				}
				// добавляем имя субдомена в базу - чтобы не производить такое кол-во проверок в след. раз
				mysql_('INSERT INTO '.P.'users_pages SET user_id = '.$user_id.', user_page = '._.$eng_name._);
				if($eng_name != $subdomain){
					//header("location: http://".$eng_name.'.'.$GLOBALS['SYSTEM']['config']['yapro_http_host'], true, 301);
					header("location: http://".$GLOBALS['SYSTEM']['config']['yapro_http_host'].'/i/'.rawurlencode($eng_name), true, 301);
					exit;
				}
				define('PAGE_USER_ID', $user_id);
			}
		}
	}
}

if(defined('PAGE_USER_ID')){
	
	$x = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors WHERE user_id = '.PAGE_USER_ID));
	$visitor_id = $x['0'];
	
}else if(defined('PAGE_VISITOR_ID')){
	
	$visitor_id = PAGE_VISITOR_ID;
	
}else{
	$visitor_id = 0;
}
?>