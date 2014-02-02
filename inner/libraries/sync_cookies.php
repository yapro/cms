<?php
// ф-я синхронизации кукиес-даных гостя с данными зарегистрированного пользователя
function sync_cookies(){
	
	$GLOBALS['SYSTEM_SCRIPT'] = __FILE__;
	
	//log_('sync_cookies: user_id : '.$GLOBALS['SYSTEM']['user']['user_id']);
	
	if(!$GLOBALS['SYSTEM']['user']['user_id']){ return ; }
	
	$VISITOR_ID = (int)$_SESSION['SYSTEM']['VISITOR_ID'];
	
	//log_('sync_cookies: VISITOR_ID : '.$_SESSION['SYSTEM']['VISITOR_ID']);
	
	// синхронизация работает только в случае если есть откуда копировать данные (есть $_SESSION['SYSTEM']['VISITOR_ID'])
	if(!$VISITOR_ID){ return false; }// если посетитель не определен - отменяем синхронизацию т.к. куки не поддерживаются
	
	// узнаю visitor_id зарегистрированного пользователя
	$visitor_id = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors 
	WHERE user_id = '.$GLOBALS['SYSTEM']['user']['user_id']));
	
	//log_('sync_cookies $visitor_id : '.$visitor_id['0']);
	
	// если visitor_id не найден по user_id - значит зарегистрированный пользователь удалил свой visitor_id из базы (или его удалил я)
	if(!$visitor_id['0']){
		
		// добавляем нового visitor_id с user_id и меняем ему куку visitor_hash
		mysql_('INSERT INTO '.C.'cookies_visitors SET 
		user_id = '.$GLOBALS['SYSTEM']['user']['user_id'].',
		hash = '._.'user_'.$GLOBALS['SYSTEM']['user']['user_id']._.',
		ip = INET_ATON('._.$_SERVER['REMOTE_ADDR']._.'),
		time_created = '.time().',
		time_modified = '.time().',
		cookies_count = 0');
		
		$visitor_id['0'] = $_SESSION['SYSTEM']['VISITOR_ID'] = mysql_insert_id();
		
		if(!$visitor_id['0']){
			error('new VISITOR_ID '.__FILE__);
		}
		
	}
	
	if(!$visitor_id['0']){ return ; }// если ИД не определен, синхронизация априори невозможна
	
	//log_('sync_cookies $_SESSION_SYSTEM_VISITOR_ID : '.$_SESSION['SYSTEM']['VISITOR_ID']);
	
	//log_('sync_cookies: cookies');
	// нахожу данные гостя
	if($q = mysql_('SELECT id, name, value FROM '.P.'cookies WHERE visitor_id = '.$VISITOR_ID)){
		while($r = mysql_fetch_assoc($q)){
			// присваиваю данные гостя - зарегестрированному пользователю (чтобы не делать дубликатов)
			mysql_query('UPDATE '.P.'cookies SET visitor_id = '.$visitor_id['0'].' WHERE id='.$r['id']);
			$mysql_errno = mysql_errno();
			if($mysql_errno && $mysql_errno==1062){// удаляю дубликаты
				mysql_('DELETE FROM '.P.'cookies WHERE id='.$r['id']);
			}else{
				$_COOKIE[ $r['name'] ] = $_SESSION['SYSTEM']['cookies'][ $r['name'] ] = $r['value'];
			}
		}
	}
	
	//log_('sync_cookies: pages_bookmarks - нахожу данные гостя И синхронизирую закладки на страницы');
	if($q = mysql_('SELECT id FROM '.P.'pages_bookmarks WHERE visitor_id = '.$VISITOR_ID)){
		while($r = mysql_fetch_assoc($q)){
			// присваиваю данные гостя - зарегестрированному пользователю (чтобы не делать дубликатов)
			mysql_query('UPDATE '.P.'pages_bookmarks SET visitor_id = '.$visitor_id['0'].' WHERE id='.$r['id']);
			$mysql_errno = mysql_errno();
			if($mysql_errno && $mysql_errno==1062){// удаляю дубликаты
				mysql_('DELETE FROM '.P.'pages_bookmarks WHERE id='.$r['id']);
			}
		}
	}
	
	//log_('sync_cookies: rating - нахожу любимые страницы гостя');
	if($q = mysql_('SELECT * FROM '.P.'pages_rating WHERE visitor_id = '.$VISITOR_ID)){
		while($r = mysql_fetch_assoc($q)){
			// присваиваю данные гостя - зарегестрированному пользователю (чтобы не делать дубликатов)
			mysql_query('UPDATE '.P.'pages_rating SET 
				visitor_id = '.$visitor_id['0'].', 
				user_id = '.$GLOBALS['SYSTEM']['user']['user_id'].' 
			WHERE 
				rating_id = '.$r['rating_id'].' AND 
				user_id = 0');
			$mysql_errno = mysql_errno();
			if($mysql_errno && $mysql_errno==1062){// удаляю дубликаты
				mysql_('DELETE FROM '.P.'pages_rating WHERE rating_id='.$r['rating_id']);
			}
		}
	}
}
sync_cookies();
?>