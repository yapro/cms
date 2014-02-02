<?php
// функция приводит в порядок URL-путь
function address_path($address=''){
	
	if(!$GLOBALS['dbcnx'] && !$GLOBALS['db']->db_connect_id){ return false; }// если нет подключения к Б.Д.
	
	if($address){
		
		preg_match_all('/http(s|):\/\/([-a-z0-9\.]+\.[a-z0-9\.\:]+)\/(.*)/i', $address, $found);
		
		if($found['2']['0']){// если REFERER правилен и имеет вид - protocol://site.ru/путь.html
			
			$path = '/'.$found['3']['0'];
			
			$ex = array_reverse(explode(".", $found['2']['0']));
			
			if($ex['1']=='rambler' || $ex['1']=='aport' || $ex['1']=='mail' && charget($path)!='i'){
				
				$path = charset($path, 'w', 'i');
			}
			
		}else{// если $address имеет вид пути: /путь.html
			$path = $address;
		}
		
		if($path){// избавляемся от всяческих доп. переменных
			
			$path = trim($path);
			
			// если присутсвует идентификатор гугла, то обрезаем его - /google.php?f=1&gclid=CLi88O2m8YoCFRTlXgodaFP3lA
			$ex_sid = explode('gclid=', $path);
			if($ex_sid['1']){ $path = mb_substr($ex_sid['0'], 0, -1); }
			
			// если присутсвует идентификатор phpBB, то обрезаем его - /forum/viewforum.php?f=2&sid=19213d16d86bf07a5543fb0109744f93
			$ex_sid = explode('sid=', $path);
			if(!empty($ex_sid['1'])){ $path = mb_substr($ex_sid['0'], 0, -1); }
			
			// если присутсвует идентификатор Yandex-a, то обрезаем его - /yandex.php?f=3&stat-id=23
			$ex_sid = explode('stat-id=', $path);
			if(!empty($ex_sid['1'])){ $path = substr($ex_sid['0'], 0, -1); }
			
			// если присутсвует идентификатор Yandex-a, то обрезаем его - /yandex.php?f=4&_openstat=dgvzddsxoze7&stat-id=3
			$ex_sid = explode('_openstat=', $path);
			if(!empty($ex_sid['1'])){ $path = substr($ex_sid['0'], 0, -1); }
			
			$path = substr($path,0,255);// специально не используется mb_substr т.к. UTF-8 полю path в MySQL прописано 255 байт - это не 255 символов
		}
	}
	
	if(!$path){// проверка нужна т.к. браузер IE при первом заходе на главную страницу выдает неправильный рефферер с пустой $page_url
		
		$path = '/';
		
	}
	return $path;
}
?>
