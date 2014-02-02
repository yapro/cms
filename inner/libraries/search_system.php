<?
// функция определения поисковой системы с которой зашли на данный сайт

if(!isset($GLOBALS['SYSTEM']['search_system'])){
	
	$GLOBALS['SYSTEM']['search_system'] = 0;
	
	function search_system(){
		
		if(!$_SERVER['HTTP_REFERER']){
			return ;
		}
		
		// [scheme] => http  [host] => hostname  [path] => /path  [query] => arg=value  [fragment] => anchor
		$referer = parse_url_($_SERVER['HTTP_REFERER']);// распарсим откуда идет посетитель
		
		if($referer['host']){
			
			$from_domain = host_clear($referer['host']);
			
			$to_domain = host_clear($_SERVER['HTTP_HOST']);
			
			if($from_domain != $to_domain){// 
				
				$e = array_reverse(explode(".", $from_domain));// разбиваем доменного имени сайта, с которого осуществлен рефферер
				
				if($e['1']=='yandex'){
					
					$x = 1;
					
				}else if($e['1']=='google' || $e['2'].'.'.$e['1']=='google.com'){
					
					$x = 2;
					
				}else if($e['1']=='googlesyndication'){
					
					$x = 2;
					
				}else if($e['1']=='rambler'){
					
					$x = 3;
					
				}else if($e['1']=='aport'){
					
					$x = 4;
					
				}else if($e['1']=='mail'){
					
					$x = 5;
					
				}else if($e['1']=='yahoo'){
					
					$x = 6;
					
				}else if($e['1']=='live'){// msn.com или live.com
					
					$x = 7;
					
				}else if($e['1']=='bing'){// bing.com
					
					$x = 8;
					
				}else{// если не поисковая система (обычный сайт-рефферер)
					
					// продумать информацию
					
				}
				
				$GLOBALS['SYSTEM']['search_system'] = $x;
			}
		}
	}
	search_system_data();
}
?>