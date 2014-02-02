<?php
class system_users {
	function system_users(){
		// настройки форума
		$this->clear_domain = host_clear($_SERVER['HTTP_HOST']);
		if($GLOBALS['SYSTEM']['config']['yapro_authentication_subdomains']){
			$this->clear_domain = '.'.$this->clear_domain;
		}
	}
	// запись данных в лог-файл
	function log($data=''){
		log_($data, $_SERVER['DOCUMENT_ROOT'].'/uploads/'.md5($_SERVER['DOCUMENT_ROOT']).'_users.log');
	}
	// возвращает все данные зарегистрированного пользователя по его ID
	function cookie($name='', $value='', $now=true){
		
		if(!$name){ return ; }
		
		if($value){// устанавливаем на 1 год
			
			setcookie($name, addslashes($value), (time() + 31536000), $GLOBALS['SYSTEM']['config']['cookie_path'], $this->clear_domain);
			
			if($now){ $_COOKIE[ $name ] = $value; }
			
		}else{
			if($_COOKIE){
				
				$value = $_COOKIE[ $name ];
				
			}else if($_SESSION['user_data']){
				
				$value = $_SESSION['user_data'][ $name ];
				
			}
			if($value){
				
				return htmlAccess(stripslashes($value));
				
			}
		}
	}
	// удаляем куки-идентификации (например чтобы завести демо-счет на др. емэйл-адрес)
	function cookie_delete($name='',$now=true){
		
		if(!$name){ return ; }
		@setcookie($name, 'delete', (time() -3600), $GLOBALS['SYSTEM']['config']['cookie_path'], $this->clear_domain);
		@setcookie($name, 'delete', (time() -3600), $GLOBALS['SYSTEM']['config']['cookie_path'], $this->clear_domain);
		if($now){ $_COOKIE[ $name ] = null; }
		return true;
	}
	// возвращает все данные зарегистрированного пользователя по его ID
	function cookie_u(){
		
		return $_COOKIE[$GLOBALS['SYSTEM']['config']['cookie_name'].'_u'];
	}
	// возвращает все данные зарегистрированного пользователя по его ID
	function cookie_sid(){
		
		return $_COOKIE[$GLOBALS['SYSTEM']['config']['cookie_name'].'_sid'];
	}
	// возвращает все данные зарегистрированного пользователя по его ID
	function cookie_k(){
		
		return $_COOKIE[$GLOBALS['SYSTEM']['config']['cookie_name'].'_k'];
	}
	// находим все существующие поля
	function fields(){
		
		if($this->fieldsGet){ return $this->fields; }// проверка для отмены повторного нахождения данных
		
		$this->fieldsGet = true;// создаем переменную информирующую о том, что нахождение полей уже производилось
		
		if($q=mysql_query('SHOW COLUMNS FROM '.F.'users')){// находим существующие поля
			while($r=mysql_fetch_assoc($q)){
				$this->fields[ $r['Field'] ] = true;
			}
			if($q=mysql_query('SHOW COLUMNS FROM '.F.'profile_fields_data')){// находим дополнительные поля профиля
				while($r=mysql_fetch_assoc($q)){
					$ex = explode('pf_', $r['Field']);
					if($ex['1']){
						$this->fields[ $ex['1'] ] = 'pf';
					}
				}
			}
		}
		return $this->fields;
	}
	// возвращает POST-данные, проверяя их с учетом пользовательских префиксов
	function post($name=''){
		
		if(!$name){ return ; }
		if($_POST[ $name ]){ return $_POST[ $name ]; }
		else if($_POST[ 'user_'.$name ]){ return $_POST[ 'user_'.$name ]; }
		else if($_POST[ 'user'.$name ]){ return $_POST[ 'user'.$name ]; }
		
	}
	// получаем массив (rank_id => rank_title) существующих званий (названий рангов)
	function ranks(){
		
		if($this->ranksGet){ return $this->ranks; }// проверка для отмены повторного нахождения данных
		
		$this->ranksGet = true;// создаем переменную информирующую о том, что нахождение полей уже производилось
		
		if($q = mysql_query("SELECT rank_id, rank_title, rank_min, rank_special FROM ".F."ranks ORDER BY rank_title")){
			$this->ranks = array();
			while($r = mysql_fetch_assoc($q)){
				$this->ranks[ $r['rank_id'] ] = $r['rank_title'];
			}
			return $this->ranks;
		}
	}
	// определяем звание (название ранга)
	function rank_title($rank_id=0){
		
		if(!$rank_id || !is_numeric($rank_id)){ return ; }
		
		if($this->rankGet[ $rank_id ]){ return $this->rank_title[ $rank_id ]; }// проверка для отмены повторного нахождения данных
		
		$this->rankGet[ $rank_id ] = true;// создаем переменную информирующую о том, что нахождение полей уже производилось
		
		$r = @mysql_fetch_row(mysql_query("SELECT rank_title FROM ".F."ranks WHERE rank_id = ".$rank_id));
		
		$this->rank_title[ $rank_id ] = $r['0'];
		
		return $this->rank_title[ $rank_id ];
	}
	// псевдоним rank_title()
	function rank_name($rank_id=0){
		
		return $this-> rank_title($rank_id);
	}
	// определяем звание (название ранга) пользователя
	function user_rank($user_id=0){
		
		if($user_id && is_numeric($user_id)){
			$this-> data($user_id);
			return $this-> rank_title($this->data($user_id,'user_rank'));
		}
	}
	// возвращает адрес аватарки пользователя по user_id (находя его данные) ИЛИ по массиву переданных в ф-ю данных avatar( array(user_id, user_avatar, user_avatar_type) );
	function avatar($data=null, $width=0){
		
		if(!$data){ $data = $GLOBALS['SYSTEM']['user']['user_id']; }
		
		if(!$data){ return ; }
		
		if(is_numeric($data)){// если переменная $data является user_id
			$data = $this-> data($data);
		}
		
		if($data && is_array($data) && $data['user_avatar'] && $data['user_avatar_type']){
			
			if($data['user_avatar'] && $GLOBALS['SYSTEM']['config']['board_disable']){
				$data['user_avatar_type'] = '1';
			}
			
			if($data['user_avatar_type']=='3' && $GLOBALS['SYSTEM']['config']['avatar_gallery_path']){// указанный файл
				
				return $GLOBALS['SYSTEM']['config']['script_path'].'/'.$GLOBALS['SYSTEM']['config']['avatar_gallery_path'].'/'.$data['user_avatar'];
				
			}else if($data['user_avatar_type']=='2'){// прямая ссылка на файл
				
				return $data['user_avatar'];
				
			}else if($data['user_avatar_type']=='1' && is_numeric($data['user_id']) ){// загруженный файл
				
				$ext = explode('.',$data['user_avatar']);
				if($ext['1']){
					
					// $GLOBALS['SYSTEM']['config']['script_path'].'/download/file.php?avatar='.$data['user_avatar'];
					return $GLOBALS['SYSTEM']['config']['script_path'].$GLOBALS['SYSTEM']['config']['avatar_path'].'/'.$GLOBALS['SYSTEM']['config']['avatar_salt'].'_'.$data['user_id'].'.'.$ext['1'];
					
				}else{
					return $this-> avatar_default($data, $width);
				}
			}
		}else{
			return $this-> avatar_default($data, $width);
		}
	}
	// возвращает адрес аватарки пользователя по-умолчанию
	function avatar_default($r=array(), $width=25){
		if($r){
			$gravatar = array('s'=>100,'r'=>'any','d'=>'wavatar','guest'=>'monsterid','without_name'=>'mm');
			$gravatar_d = $gravatar['d'];
			if($r['user_email']){
				$gravatar_id = md5(strtolower_($r['user_email']));
			}else{
				$gravatar_id = $r['user_id'];
			}
			
			$user_avatar = 'http://www.gravatar.com/avatar/'. $gravatar_id.'?s='.$gravatar['s'].'&r='.$gravatar['r'].'&d='.$gravatar_d;
			
			if(substr($gravatar_d,0,1)=='/' || substr($gravatar_d,0,1)=='http'){
				$user_avatar = $gravatar_d;
			}
			return $user_avatar;
		}
		return '/js/comments/avatar.gif';
	}
	// возвращает изображение аватарки пользователя по user_id (находя его данные) ИЛИ по массиву переданных в ф-ю данных avatar( array(user_id, user_avatar, user_avatar_type) ); ИЛИ по-умолчанию
	function avatar_img($data=null){
		
		return '<img src="'.$this-> avatar_src($data).'" border=0>';
	}
	// возвращает адрес аватарки пользователя по user_id (находя его данные) ИЛИ по массиву переданных в ф-ю данных avatar( array(user_id, user_avatar, user_avatar_type) ); ИЛИ по-умолчанию
	function avatar_src($data=null, $width=0){
		
		return $this-> avatar($data)? $this-> avatar($data).($_POST?'?v'.time():'') : $this-> avatar_default($data, $width);
	}
	// возвращает возможные типы файлов для аватара (возможно получение данных строкой через запятую с пробелами между расширений)
	function avatar_types($get_str=false){
		
		$allow = array('jpg'=>true, 'jpeg'=>true, 'png'=>true, 'gif'=>true);
		
		if($get_str){
			foreach($allow as $type => $true){
				$str .= $type.', ';
			}
			return substr($str,0,-2);
		}else{
			return $allow;
		}
	}
	// загружает файл аватары, в случае ошибки возвращает описание ошибки, а в случае успеха ничего не возвращает но добавляет данные о аватаре в базу данных и в массив информации о пользователе
	function avatar_upload(){
		
		if($_FILES && $_FILES['user_avatar']['tmp_name'] && $_FILES['user_avatar']['name'] && $GLOBALS['SYSTEM']['user']['user_id']){
			
			$type = array_reverse( explode('.', $_FILES['user_avatar']['name']) );// узнаем тип файла
			$type = strtolower_($type['0']);// делаем тип в нижнем регистре
			$allow = $this-> avatar_types();
			if(!$allow[ $type ]){
				return 'Загрузка файла отменена из-за неразрешенного типа файла: '.htmlAccess($type);
			}
			
			$dir = $_SERVER['DOCUMENT_ROOT'].$GLOBALS['SYSTEM']['config']['script_path'].$GLOBALS['SYSTEM']['config']['avatar_path'].'/';
			if(!is_dir($dir)){
				return error('Директория для загрузки файла не существует : '.$dir);
			}
			
			$size = getimagesize($_FILES['user_avatar']['tmp_name']);
			$w  = $size['0'];// настоящая ширина картинки
			$h = $size['1'];// настоящая высота картинки
			
			if($h < $GLOBALS['SYSTEM']['config']['avatar_min_height'] || $h > $GLOBALS['SYSTEM']['config']['avatar_max_height'] || $w < $GLOBALS['SYSTEM']['config']['avatar_min_width'] || $w > $GLOBALS['SYSTEM']['config']['avatar_max_width']){
				return 'Загрузка изображения отменена из-за несоблюдения размеров.';
			}
			
			if( filesize($_FILES['user_avatar']['tmp_name']) > $GLOBALS['SYSTEM']['config']['avatar_filesize'] ){
				return 'Загрузка изображения отменена из-за большого размера файла.';
			}
			
			$avatar_path = $dir.$GLOBALS['SYSTEM']['config']['avatar_salt'].'_'.$GLOBALS['SYSTEM']['user']['user_id'].'.'.$type;
			
			if(move_uploaded_file($_FILES['user_avatar']['tmp_name'], $avatar_path)){
				
				@chmod($avatar_path, 0664);
				
				$GLOBALS['SYSTEM']['user']['user_avatar_type'] = 1;
				$GLOBALS['SYSTEM']['user']['user_avatar'] = $GLOBALS['SYSTEM']['user']['user_id'].'_'.time().'.'.$type;
				
				$update_info =  array(
					'user_avatar_type' => $GLOBALS['SYSTEM']['user']['user_avatar_type'],
					'user_avatar' => $GLOBALS['SYSTEM']['user']['user_avatar'],
					'user_avatar_width' => (int)$w,
					'user_avatar_height' => (int)$h
				);
				
				if( $this-> update($update_info) ){
					
					// обновляем данные в массиве данных пользователя
					$this->data[ $GLOBALS['SYSTEM']['user']['user_id'] ]['user_avatar_type'] = $GLOBALS['SYSTEM']['user']['user_avatar_type'];
					$this->data[ $GLOBALS['SYSTEM']['user']['user_id'] ]['user_avatar'] = $GLOBALS['SYSTEM']['user']['user_avatar'];
					
				}
				
			}else{
				return error('Недостаточно прав для загрузки файла в директорию загрузки.');
			}
		}
	}
	// возвращает все данные зарегистрированного пользователя по его ID
	function data($user_id=0, $field_name='', $withoutcache=false){
		
		if(!$user_id || !is_numeric($user_id)){ return ; }
		
		if($this->dataGet[ $user_id ] && !$withoutcache){// проверка для отмены повторного нахождения данных
			
			return $field_name? $this->data[ $user_id ][ $field_name ] : $this->data[ $user_id ];
			
		}
		
		$this->dataGet[ $user_id ] = true;// создаем переменную информирующую о том, что нахождение полей уже производилось
		
		$this->data[ $user_id ] = @mysql_fetch_assoc(mysql_query('SELECT * FROM '.F.'users WHERE user_id = '.$user_id));
		
		if($q=mysql_query('SELECT * FROM '.F.'profile_fields_data WHERE user_id = '.$user_id)){
			while($r=mysql_fetch_assoc($q)){
				foreach($r as $field => $value){
					$ex = explode('pf_', $field);
					if($ex['1']){
						$this->data[ $user_id ][ $ex['1'] ] = $value;
					}
				}
			}
		}
		
		return $field_name? $this->data[ $user_id ][ $field_name ] : $this->data[ $user_id ];
	}
	// самописная ф-я: заводит phpbb3 сессию пользователя по заданному user_id (в случае успеха - возвращает тот же user_id)
	function session_start($user_id=0, $new=false){
		
		if(!$user_id || !is_numeric($user_id)){ return ; }
		
		$time = time();
		
		// если включена опция: Сайт. Авторизация с меньшей нагрузкой на сервер (без поддержки форума phpBB3)
		if($GLOBALS['SYSTEM']['config']['yapro_authentication']){
			
			$u = $GLOBALS['SYSTEM']['user'] = $this-> data($user_id);
			
			if($u){
				
				$this-> cookie($GLOBALS['SYSTEM']['config']['cookie_name'].'_u', $user_id.'.'.md5($u['user_password'].$_SERVER['HTTP_USER_AGENT']));
				
				// если пользователь авторизуется впервые И отключена одновременная авторизация с более чем 1 компа
				if($new && $GLOBALS['SYSTEM']['config']['yapro_user_inactive_time']){
					
					$this-> cookie('yapro_user_inactive_time', $time);
					
					mysql_('UPDATE '.F.'users SET user_inactive_time = '.$time.' WHERE user_id = '.$user_id);
				}
				
				return $user_id;
			}
			return ;
		}
		
		$s = @mysql_fetch_assoc(mysql_("SELECT session_id, session_page FROM ".F."sessions 
		WHERE session_user_id = ".$user_id." AND session_ip = "._.$_SERVER['REMOTE_ADDR']._));
		
		$set_browser = '';
		if($GLOBALS['SYSTEM']['config']['browser_check']){
			$set_browser = "session_browser = "._.$_SERVER['HTTP_USER_AGENT']._.", ";
		}
		
		if(!$s['session_id']){
			
			$s['session_id'] = md5(uniqid(microtime()));
			
			mysql_("INSERT INTO ".F."sessions SET 
			session_id = "._.$s['session_id']._.",
			session_user_id = "._.$user_id._.", 
			session_last_visit = "._.$time._.", 
			session_start = "._.$time._.", 
			session_time = "._.$time._.", 
			session_ip = "._.$_SERVER['REMOTE_ADDR']._.", 
			".$set_browser."
			session_page = "._.'index.php'._.", 
			session_autologin = 1");
			
			/*mysql_query("UPDATE ".F."users SET user_lastpage = 'index.php' WHERE user_id = ".$user_id);*/
			
		}else{
			
			mysql_("UPDATE ".F."sessions SET ".$set_browser."session_time = ".$time."
			"._."WHERE"._." session_user_id = ".$user_id);
			
			/*if($s['session_page']){
				mysql_("UPDATE ".F."users SET user_lastpage = "._.$s['session_page']._." 
				"._."WHERE"._." user_id = ".$user_id);
			}*/
		}
		
		$key_id = @mysql_fetch_row(mysql_("SELECT key_id FROM ".F."sessions_keys 
		WHERE user_id = ".$user_id." AND last_ip = "._.$_SERVER['REMOTE_ADDR']._." ORDER BY last_login DESC LIMIT 1"));
		
		if(!$key_id['0']){
			
			$key_id['0'] = $this-> unique_id(hexdec(substr($s['session_id'], 0, 8)));//md5(uniqid(microtime()));
			
			mysql_("INSERT INTO ".F."sessions_keys SET 
			key_id = "._.(string) md5($key_id['0'])._.", 
			user_id = "._.$user_id._.", 
			last_ip = "._.$_SERVER['REMOTE_ADDR']._.", 
			last_login = "._.$time._."");
			
			$this-> cookie($GLOBALS['SYSTEM']['config']['cookie_name'].'_k', $key_id['0']);
			
		}/*else{
			$key_id['0'] = $this-> cookie_k();
		}*/
		//log_('3='.$user_id.'=!='.$key_id['0'].'=!='.$s['session_id']);
		$this-> cookie($GLOBALS['SYSTEM']['config']['cookie_name'].'_u', $user_id);
		$this-> cookie($GLOBALS['SYSTEM']['config']['cookie_name'].'_sid', $s['session_id']);
		
		mysql_('UPDATE '.F.'users SET user_lastvisit = '.$time.' WHERE user_id = '.$user_id);
		
		$GLOBALS['SYSTEM']['user'] = $this-> data($user_id);
		//$GLOBALS['SYSTEM']['user']['access'] = 1;
		return $user_id;
	}
	// возвращает user_id по Имени пользователя
	function user_id_by_username($username=''){
		
		return $this-> user_id_by_field_name('username', $username._." OR username_clean="._.$this-> username_clean($username));
		/*if(!$username){ return ; }
		$r = @mysql_fetch_row(mysql_("SELECT user_id FROM ".F."users 
		WHERE username = "._.$username._." OR username_clean="._.$this-> username_clean($username)._." LIMIT 1"));
		return $r['0'];*/
	}
	// псевдоним user_id_by_username()
	function user_id_by_name($username=''){
		
		return $this-> user_id_by_username($username);
	}
	// возвращает Имена пользователей начинающихся с указанного Имени пользователя
	function users_by_username($username=''){
		
		if(!$username){ return ; }
		
		if($q = mysql_("SELECT LOWER(username), LOWER(username_clean) FROM ".F."users 
		WHERE username LIKE "._.$username."%"._." OR username_clean LIKE "._.$this-> username_clean($username)."%"._)){
			$a = array();
			while($r = mysql_fetch_row($q)){
				$a[ $r['0'] ] = true;
				$a[ str_replace('ё','е',$r['1']) ] = true;
			}
			return $a;
		}
	}
	// возвращает user_id по совпадению заданного поля
	function user_id_by_field_name($field_name='', $value=''){
		
		if(!$field_name || !$value){ return ; }
		
		$this-> fields();
		
		if($this->fields[ $field_name ] === 'pf'){
			
			$r = @mysql_fetch_row(mysql_('SELECT user_id FROM '.F.'profile_fields_data WHERE pf_'.trim($field_name).' = '._.trim($value)._.' LIMIT 1'));
			
		}else{
			
			$r = @mysql_fetch_row(mysql_('SELECT user_id FROM '.F.'users WHERE '.trim($field_name).' = '._.trim($value)._.' LIMIT 1'));
			
		}
		return $r['0'];
	}
	// возвращает user_id по E-mail адресу
	function user_id_by_user_email($user_email=''){
		
		return $this-> user_id_by_field_name('user_email', $user_email);
		
	}
	// псевдоним user_id_by_user_email()
	function user_id_by_email($user_email=''){
		
		return $this-> user_id_by_user_email($user_email);
	}
	// возвращает user_id по user_actkey
	function user_id_by_user_actkey($user_actkey=''){
		
		return $this-> user_id_by_field_name('user_actkey', $user_actkey);
	}
	// возвращает имя авторизованного пользователя или очищенное HTML имя пользователя, например: echo username(); или echo username('Алиса');
	function username($name='',$ghost=false){
		
		if(!$name){ $name = $GLOBALS['SYSTEM']['user']['username']; }
		if($name){
			return htmlAccess(strip_tags($name));
		}else if($ghost){
			return 'Гость';
		}
	}
	// подготавливаем имя пользователя для вставки/проверки в таблице users ( в phpBB3 за это отвечает ф-я utf8_clean_string )
	function username_clean($name=''){
		
		if($name){
			return str_replace('ё','е',strtolower_(trim($name)));
		}
	}
	// возвращает окончание слова в зависимости от пола по имени пользователя, например: echo 'Уважаем'.respect('Барабанова').' Алиса';
	function respect($name=''){
		
		$name = $this-> username($name);
		return respect($name);
	}
	// определяем идентификатор группы (group_id), к которой относятся обычные зарегистрированные пользователи
	function group_id_reg(){
		
		$r = @mysql_fetch_row(mysql_query("SELECT group_id FROM ".F."groups WHERE group_name = 'REGISTERED'"));
		return $r['0'];
	}
	// определяем идентификаторы групп (group_id), в которые входит зарегистрированный пользователь
	function user_groups($user_id=0){
		$user_id = (int)$user_id;
		if($user_id && $q = mysql_query("SELECT group_id FROM ".F."user_group 
		WHERE user_id = ".$user_id)){
			$a = array();
			while($r = mysql_fetch_row($q)){
				$a[ $r['0'] ] = true;
			}
			return $a;
		}
	}
	// определяем является ли зарегистрированный пользователь(user_id) участником группы (group_id)
	function user_in_group($group_id=0, $user_id=0){
		
		$group_id = (int)$group_id;
		
		$user_id = (int)$user_id;
		
		if(!$user_id){ $user_id = (int)$GLOBALS['SYSTEM']['user']['user_id']; }
		
		if($user_id && $group_id && $a = $this-> user_groups($user_id)){
			return $a[$group_id];
		}
	}
	// если пользователь решил выйти, очищаем его куки
	function session_end(){
		
		$this-> cookie_delete($GLOBALS['SYSTEM']['config']['cookie_name'].'_u');
		$this-> cookie_delete($GLOBALS['SYSTEM']['config']['cookie_name'].'_sid');
		$_COOKIE[$GLOBALS['SYSTEM']['config']['cookie_name'].'_k'] = '';
		unset($GLOBALS['SYSTEM']['user']);
		
	}
	// проверяем имя на наличие слова админ, если оно присутствует - функция возвращает true, в противном случае false
	function is_admin($name=''){
		
		if($name && (
			mb_stristr($name, 'admin') || // eng
			mb_stristr($name, 'админ') || // рус
			mb_stristr($name, 'аdmin') || // рус (а) + eng (dmin)
			mb_stristr($name, 'aдмин')    // eng (a) + рус (дмин)
			)){
				return true;
		}
	}
	// автоматическая авторизация пользователя по существующим данным
	function authentication(){
		
		// если включена опция: Сайт. Авторизация с меньшей нагрузкой на сервер (без поддержки форума phpBB3)
		if($GLOBALS['SYSTEM']['config']['yapro_authentication']){
			
			$user_cookies = $this-> cookie($GLOBALS['SYSTEM']['config']['cookie_name'].'_u');
			
			if($user_cookies){
				
				$e = explode('.',$user_cookies);
				
				$user_id = (int)$e['0'];
				
				if($user_id){
					
					$user = @mysql_fetch_assoc(mysql_query("SELECT user_password, user_inactive_time FROM ".F."users 
					WHERE user_id = ".$user_id.' AND user_type IN (0,3)'));
					
					if($user['user_password'] && md5($user['user_password'].$_SERVER['HTTP_USER_AGENT'])==$e['1']){
						
						if($GLOBALS['SYSTEM']['config']['yapro_user_inactive_time']){// если отключена одновременная авторизация с более чем 1 компа
							
							$user_inactive_time = (int)$this-> cookie('yapro_user_inactive_time');
							
							if($user_inactive_time != $user['user_inactive_time']){
								return ;
							}
						}
						
						$this-> session_start($user_id);// заводим phpbb3 сессию пользователю
					}
				}
			}
			return ;
		}
		
		$check = array();
		
		// если заданные переменные кукиес не пусты, проверяем
		if($this-> cookie_sid() && $this-> cookie_u()){
			
		    $check = @mysql_fetch_assoc(mysql_("SELECT u.user_id 
			FROM ".F."sessions AS s 
			LEFT JOIN ".F."users AS u ON u.user_id = s.session_user_id
			WHERE s.session_id = "._.$this-> cookie_sid()._."
				AND s.session_user_id="._.$this-> cookie_u()._."
				AND u.user_type IN (0,3)"));
			
		}
		// если сессия пользователя закончилась - пробуем найти пользователя по md5-ключу
		if(!$check['user_id'] && $this-> cookie_u() && $this-> cookie_k()){
			
			// метод из файла /forum/includes/session.php : строка 549
			$check = @mysql_fetch_assoc(mysql_("SELECT u.user_id
			FROM ".F."sessions_keys AS k
			LEFT JOIN ".F."users AS u ON u.user_id = k.user_id 
			WHERE k.user_id="._.$this-> cookie_u()._."
			AND k.key_id="._.md5( $this-> cookie_k() )._."
			AND u.user_type IN (0,3)"));
		}
		/*
		// если пользователь не опознан проверяем на пост-данные (в случае их наличия)
		if(!$check['user_id'] && $_POST['username'] && $_POST['password']){// && $_POST['system_user_authentication']
			
			$check = @mysql_fetch_assoc(mysql_("SELECT user_id, user_password FROM ".F."users
			WHERE username = "._.$_POST['username']._." AND user_type IN (0,3)"));
			
		    if(!$check['user_id'] || !$this-> check_hash($_POST['password'], $check['user_password']) ){
				
				header301('/system/user.restore.html');//Восстановление пароля
				//Восстановление пароля - /forum/ucp.php?mode=sendpassword
				//Регистрация - /forum/ucp.php?mode=register
				
			}
		}
		*/
		if($check['user_id']){// если пользователь найден - завожу ему сессию и определяю его данные
		   	
		   	$this-> session_start($check['user_id']);// заводим phpbb3 сессию пользователю
		    //$GLOBALS['SYSTEM']['user'] = $this-> data($check['user_id']);// находим данные пользователя
		   	//$GLOBALS['SYSTEM']['user']['access'] = 1;
		}
	}
	// функция обновляет данные пользователя (данные тех полей, которые указываются и существуют), в случае успеха возвращает user_id
	function update($data=array()){
		
		if(!$data){ return ; }
		
		$this->fields();
		
		if(!$this->fields){ return ; }
		
		$user_id = ($data['user_id'] && is_numeric($data['user_id']) )? $data['user_id'] : $GLOBALS['SYSTEM']['user']['user_id'];
		
		$set_users = $set_profile = '';
		
		foreach($this->fields as $Field => $true){
			
			if($Field && array_key_exists($Field, $data)){
				
				if($true === true){
					
					$set_users .= '`'.$Field.'` = '._.$data[ $Field ]._.',';
					
				}else{
					$set_profile .= '`pf_'.$Field.'` = '._.$data[ $Field ]._.',';
				}
			}
		}
		
		if($user_id){
			
			$command_start = 'UPDATE';
			$command_end = _.'WHERE'._.' user_id = '.$user_id;
			
		}else{
			
			$command_start = 'INSERT INTO';
			$command_end = '';
			
		}
		
		if($set_users){
			
			if(!mysql_($command_start.' '.F.'users SET '.substr($set_users,0,-1).' '.$command_end)){
				
				error('update SQL users: '.__FILE__); return ;
				
			}
			
			if($command_start == 'INSERT INTO'){// если добавляем пользователя, то добавим его в группу зарегистрированных пользователей
				
				$user_id = mysql_insert_id();
				
				if(!mysql_('INSERT INTO '.F.'user_group SET 
				group_id = '.$this-> group_id_reg().', 
				user_id = '.$user_id.', 
				user_pending = 0')){
					
					error('update SQL user_group : '.__FILE__); return ;
					
				}
				$command_end = '';
			}
		}
		
		if($set_profile){
			
			if(!$user_id){ error(__FILE__); }
			
			if($command_start == 'UPDATE'){// если обновляем, выясним есть ли строка данных у пользователя в таблице profile_fields_data
				
				$profile_fields_data = mysql_fetch_row(mysql_('SELECT user_id FROM '.F.'profile_fields_data WHERE user_id = '.$user_id));
				
				if(!$profile_fields_data['0']){// если строки данных нет у пользователя в таблице profile_fields_data
					
					$command_start = 'INSERT INTO';
					$command_end = ', user_id = '.$user_id;
				}
			}else{// $command_start == INSERT INTO
				$command_end = ', user_id = '.$user_id;
			}
			
			if(!mysql_($command_start.' '.F.'profile_fields_data SET '.substr($set_profile,0,-1).' '.$command_end)){
				
				error('update SQL profile_fields_data : '.__FILE__); return ;
				
			}
		}
		if($user_id){// обновляем массив данных
			$GLOBALS['SYSTEM']['user'] = $GLOBALS['SYSTEM']['users']-> data($user_id, '', true);
			return $user_id;
		}
	}
	// псевдоним update()
	function insert($data=array()){
		return $this-> update($data);
	}
	// возвращает массив часовых поясов или пх options
	function timezones($options=null, $selected=null){
		
		$arr = array('-12' => '[UTC - 12] Меридиан смены дат (запад)',
		'-11' => '[UTC - 11] о. Мидуэй, Самоа',
		'-10' => '[UTC - 10] Гавайи',
		'-9.5' => '[UTC - 9:30] Маркизские острова',
		'-9' => '[UTC - 9] Аляска',
		'-8' => '[UTC - 8] Тихоокеанское время (США и Канада) и ...',
		'-7' => '[UTC - 7] Аризона',
		'-6' => '[UTC - 6] Мехико, Центральная Америка, Централь...',
		'-5' => '[UTC - 5] Индиана (восток), Восточное время (СШ...',
		'-4.5' => '[UTC - 4:30] Венесуэла',
		'-4' => '[UTC - 4] Каракас, Сантьяго, Атлантическое врем...',
		'-3.5' => '[UTC - 3:30] Ньюфаундленд',
		'-3' => '[UTC - 3] Бразилия, Гренландия',
		'-2' => '[UTC - 2] Среднеатлантическое время',
		'-1' => '[UTC - 1] Азорские острова, острова Зеленого мыса',
		'0' => '[UTC 0] Время по Гринвичу: Дублин, Лондон, Лиссаб...',
		'1' => '[UTC + 1] Берлин, Мадрид, Париж, Рим, Западная ...',
		'2' => '[UTC + 2] Афины, Вильнюс, Киев, Минск, Рига, Та...',
		'3' => '[UTC + 3] Москва, Санкт-Петербург, Волгоград',
		'3.5' => '[UTC + 3:30] Тегеран',
		'4' => '[UTC + 4] Киев, Баку, Ереван, Самара, Тбилиси',
		'4.5' => '[UTC + 4:30] Кабул',
		'5' => '[UTC + 5] Екатеринбург, Исламабад, Карачи, Орен...',
		'5.5' => '[UTC + 5:30] Бомбей, Калькутта, Мадрас, Нью-Дели',
		'5.75' => '[UTC + 5:45] Катманду',
		'6' => '[UTC + 6] Алматы, Астана, Новосибирск, Омск',
		'6.5' => '[UTC + 6:30] Рангун',
		'7' => '[UTC + 7] Бангкок, Красноярск',
		'8' => '[UTC + 8] Гонконг, Иркутск, Пекин, Сингапур',
		'8.75' => '[UTC + 8:45] Юго-восточная Западная Австралия',
		'9' => '[UTC + 9] Токио, Сеул, Чита, Якутск',
		'9.5' => '[UTC + 9:30] Дарвин',
		'10' => '[UTC + 10] Владивосток, Канберра, Мельбурн, Сидней',
		'10.5' => '[UTC + 10:30] Лорд-Хау',
		'11' => '[UTC + 11] Магадан, Сахалин, Соломоновы о-ва',
		'11.5' => '[UTC + 11:30] Остров Норфолк',
		'12' => '[UTC + 12] Камчатка, Новая Зеландия, Фиджи',
		'12.75' => '[UTC + 12:45] Острова Чатем',
		'13' => '[UTC + 13] Острова Феникс, Тонга',
		'14' => '[UTC + 14] Остров Лайн');
		
		if($options){
			
			if(substr($selected,-1)==0){// необходимо т.к. mysql сохраняет с 2 цифрами после запрятой
				$selected = substr($selected,0,-1);
			}
			$selected = ($arr[ $selected ])? $selected : (int)$selected;
			
			foreach($arr as $k => $v){
				$s .= '<option value="'.$k.'"'.(($k==$selected)?' selected':'').'>'.$v.'</option>';
			}
			return $s;
			
		}else if(isset($selected)){
			
			return $arr[ $selected ]? $arr[ $selected ] : $arr[ (int)$selected ];
			
		}else{
			return $arr;
		}
	}
	// возвращает данные форматированные в правильные значения
	function format($data=array()){
		
		if(!$data){ return ; }
		
		$disallow = array(
			'user_permissions' => true,
			'user_perm_from' => true,
			'user_regdate' => true,
			'user_passchg' => true,
			'user_pass_convert' => true,
			'user_email_hash' => true,
			'user_lastvisit' => true,
			'user_ip' => true,
			'user_lastmark' => true,
			'user_lastpost_time' => true,
			'user_lastpage' => true,
			'user_last_confirm_key' => true,
			'user_last_search' => true,
			'user_warnings' => true,
			'user_last_warning' => true,
			'user_login_attempts' => true,
			'user_inactive_reason' => true,
			'user_inactive_time' => true,
			'user_posts' => true,
			'user_rank' => true,
			'user_colour' => true,
			'user_new_privmsg' => true,
			'user_unread_privmsg' => true,
			'user_last_privmsg' => true,
			'user_message_rules' => true,
			'user_full_folder' => true,
			'user_emailtime' => true,
			'user_options' => true,
			'user_sig_bbcode_uid' => true,
			'user_sig_bbcode_bitfield' => true,
			'user_actkey' => true,
			'user_newpasswd' => true,
			'user_form_salt' => true,
			'user_new' => true,
			'user_reminded' => true,
			'user_reminded_time' => true
		);
		
		$new = array();
		
		foreach($data as $k => $v){
			
			if($disallow[ $k ]){ continue; }
			
			if($k=='username'){
				
				$new[ $k ] = $v;//ucfirst_($v)
				
			}else if($k=='username_clean'){
				
				$new[ $k ] = $this-> username_clean($v);
				
			}else if($k=='user_password'){
				
				$new[ $k ] = $this-> hash($v);
				
			}else if($k=='user_email' || $k=='user_jabber'){
				
				$new[ $k ] = email($v)? $v : '';
				
			}else if($k=='birthday_d' || $k=='birthday_m' || $k=='birthday_y'){
				
				// объявляем переменную чтобы сохранить настройки даты рождения
				$new['user_birthday'] = (int)$data['birthday_d'].'-'.(int)$data['birthday_m'].'-'.(int)$data['birthday_y'];
				
			}else if($k=='user_birthday'){
				
				/*if($data['birthday_d'] || $data['birthday_m'] || $data['birthday_y']){
				if($data['birthday_d'] && $data['birthday_m'] && $data['birthday_y']){
					$v = (int)$data['birthday_d'].'-'.(int)$data['birthday_m'].'-'.(int)$data['birthday_y'];
				}else{
					$v = strtotime($v);
				}
				
				$new[ $k ] = date('d-m-Y', $v);*/
				$new[ $k ] = $v;
				
			}else if($k=='user_lang'){
				
				$new[ $k ] = substr($v,0,2);
				
			}else if($k=='user_timezone'){
				
				$timezones = $this-> timezones();
				$new[ $k ] = $timezones[ $v ]? $v : '0.00';
				
			}else if($k=='user_dst'){
				
				$new[ $k ] = ($v==1)? 1 : 0;
				
			}else if($k=='user_dateformat'){
				
				$new[ $k ] = strtotime( date( htmlAccess($v) ) )? $v : 'd M Y H:i';
				
			}else if($k=='user_topic_sortby_type'){
				
				$new[ $k ] = ($v=='a' || $v=='r' || $v=='s' || $v=='v')? $v : 't';
				
			}else if($k=='user_topic_sortby_dir' || $k=='user_post_sortby_dir'){
				
				$new[ $k ] = ($v=='a')? $v : 'd';
				
			}else if($k=='user_post_sortby_type'){
				
				$new[ $k ] = ($v=='a' || $v=='s')? $v : 't';
				
			}else if($k=='user_id' || $k=='user_type' || $k=='group_id' || 
			$k=='user_style' || $k=='user_topic_show_days' || $k=='user_post_show_days' || 
			$k=='user_notify' || $k=='user_notify_pm' || $k=='user_notify_type' || 
			$k=='user_allow_pm' || $k=='user_allow_viewonline' || $k=='user_allow_viewemail' || 
			$k=='user_allow_massemail' || $k=='user_avatar_type' || $k=='user_avatar_width' || 
			$k=='user_avatar_height' || $k=='user_icq'){
				
				$new[ $k ] = $v? (int)$v : '';
				
			}else if($k=='user_website'){
				
				if(mb_substr($v,0,5)=='http:' || mb_substr($v,0,6)=='https:'){
					
					preg_match_all('/http(s|):\/\/([-a-z0-9\.]+\.[a-z0-9\.\:]+)\/(.*)/i', $v, $found);
					
					if($found['2']['0']){// если правилен и имеет вид - http(s)://site.ru/путь.html
						$new[ $k ] = $v;
					}
				}else{
					$new[ $k ] = 'http://'. $v;
				}
				
			}else{
				
				$new[ $k ] = $v;
				
			}
		}
		return $new;
	}
	// русскоязычные поля (ф-я в разработке)
	function fields_ru(){
		return array('user_id' => 'Ваш идентификатор',
			'user_ip' => 'Ваш IP адрес',
			'user_regdate' => 'Дата регистрации',
			'username' => 'Ваше имя',
			'user_password' => 'Ваш пароль',
			'user_passchg' => 'Последний раз Вы меняли пароль',
			'user_email' => 'Ваш E-mail адрес электронной почты',
			'user_birthday' => 'Дата Вашего рождения',
			'user_lastvisit' => 'Дата последнего визита нашего сайта',
			'user_lastpost_time' => 'Последний раз Вы размещали комментарии на форуме',
			'user_lastpage' => 'Последняя просматриваемая Вами страница форума',
			'user_last_search' => 'Последний раз Вы искали на форуме',
			'user_warnings' => 'Вам объявлено предупреждений',
			'user_last_warning' => 'Последний раз Вам объявляли предупреждение',
			'user_login_attempts' => '',
			'user_inactive_reason' => '',
			'user_inactive_time' => '',
			'user_posts' => '',
			'user_lang' => '',
			'user_timezone' => '',
			'user_dst' => '',
			'user_dateformat' => '',
			'user_style' => '',
			'user_rank' => '',
			'user_colour' => '',
			'user_new_privmsg' => '',
			'user_unread_privmsg' => '',
			'user_last_privmsg' => '',
			'user_message_rules' => '',
			'user_full_folder' => '',
			'user_emailtime' => '',
			'user_topic_show_days' => '',
			'user_topic_sortby_type' => '',
			'user_topic_sortby_dir' => '',
			'user_post_show_days' => '',
			'user_post_sortby_type' => '',
			'user_post_sortby_dir' => '',
			'user_notify' => '',
			'user_notify_pm' => '',
			'user_notify_type' => '',
			'user_allow_pm' => '',
			'user_allow_viewonline' => '',
			'user_allow_viewemail' => '',
			'user_allow_massemail' => '',
			'user_options' => '',
			'user_avatar' => '',
			'user_avatar_type' => '',
			'user_avatar_width' => '',
			'user_avatar_height' => '',
			'user_sig' => '',
			'user_sig_bbcode_uid' => '',
			'user_sig_bbcode_bitfield' => '',
			'user_from' => '',
			'user_icq' => '',
			'user_aim' => '',
			'user_yim' => '',
			'user_msnm' => '',
			'user_jabber' => '',
			'user_website' => '',
			'user_occ' => '',
			'user_interests' => '',
			'user_actkey' => '',
			'user_newpasswd' => '',
			'user_form_salt' => '',
			'user_new' => '',
			'user_reminded' => '',
			'user_reminded_time' => '',
		);
	}
	function config($config_name='', $config_value='', $is_dynamic = false)
	{
		if($config_name && $config_value && $GLOBALS['SYSTEM']['config']['script_path'] && 
		mysql_('UPDATE '.F.'config SET config_value='._.$config_value._.' '._.'WHERE'._.' config_name = '._.$config_name._)){
			if($is_dynamic){
				@unlink($_SERVER['DOCUMENT_ROOT'].$GLOBALS['SYSTEM']['config']['script_path'].'/cache/data_global.php');
			}
			return true;
		}
	}
	// возвращает безопасный никнэйм для вывода в HTML с учетом возможных указанных данных
	function comment_name($username='', $nickname=''){
		
		if($username){
			
			return htmlAccess($username);
			
		}else if($nickname){
			
			return htmlAccess($nickname).' (гость)';
			
		}else{
			
			return 'Гость';
			
		}
	}
	// возвращает ссылку с автоматической авторизацией пользователя
	function access_link($user=null, $url=''){
		
		if(is_array($user)){
			$r = $user;
		}else if(is_numeric($user)){
			$r = $this-> data($user);
		}
		
		if(!$r || 
		!$r['user_id'] || !is_numeric($r['user_id']) || 
		!$r['user_regdate'] || !is_numeric($r['user_regdate']) || 
		!$r['user_password']){
			return ;
		}
		
		if(!$url){ $url = $_SERVER['REQUEST_URI']; }
		
		$url = str_replace('.','___DOT___',str_replace('-','___DASH___', $url));
		
		return '/system/user.authorization.'.$r['user_id'].'-'.$r['user_regdate'].'-'.md5($r['user_password']).'-'.$url;
	}
	//-------------------- ф-ии позаимствованные из phpBB3: -------------------------
	// Проверяет корректность введенного пароля и хеша из базы - phpbb_check_hash() @return bool Returns true if the password is correct, false if not.
	function check_hash($password='', $hash='')
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		if (strlen($hash) == 34)
		{
			return ($this-> hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
		}
		return (md5($password) === $hash) ? true : false;
	}
	// немного измененная ф-я из phpbb3 - создает хэш-пароля введенный пользователем для последующей вствке в таблицу users.user_password, пример применения $this-> hash('пароль');
	function hash($password='', $unique_id=0)
	{
		$unique_id = $this-> unique_id();// by Lebnik: rand(0, time());
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$random_state = $unique_id;
		$random = '';
		$count = 6;

		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($random) < $count)
		{
			$random = '';

			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5($unique_id . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}

		$hash = $this-> hash_crypt_private($password, $this-> hash_gensalt_private($random, $itoa64), $itoa64);

		if (strlen($hash) == 34)
		{
			return $hash;
		}

		return md5($password);
	}
	/* The crypt function/replacement hash_crypt_private() */
	function hash_crypt_private($password='', $setting='', &$itoa64)
	{
		$output = '*';

		// Check for correct hash
		if (substr($setting, 0, 3) != '$H$')
		{
			return $output;
		}

		$count_log2 = strpos($itoa64, $setting[3]);

		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);

		if (strlen($salt) != 8)
		{
			return $output;
		}

		/**
		* We're kind of forced to use MD5 here since it's the only
		* cryptographic primitive available in all versions of PHP
		* currently in use.  To implement our own low-level crypto
		* in PHP would result in much worse performance and
		* consequently in lower iteration counts and hashes that are
		* quicker to crack (by non-PHP code).
		*/
		if (PHP_VERSION >= 5)
		{
			$hash = md5($salt . $password, true);
			do
			{
				$hash = md5($hash . $password, true);
			}
			while (--$count);
		}
		else
		{
			$hash = pack('H*', md5($salt . $password));
			do
			{
				$hash = pack('H*', md5($hash . $password));
			}
			while (--$count);
		}

		$output = substr($setting, 0, 12);
		$output .= $this-> hash_encode64($hash, 16, $itoa64);

		return $output;
	}
	/* Generate salt for hash generation hash_gensalt_private() */
	function hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6)
	{
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
		{
			$iteration_count_log2 = 8;
		}

		$output = '$H$';
		$output .= $itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
		$output .= $this-> hash_encode64($input, 6, $itoa64);

		return $output;
	}

	/* Encode hash hash_encode64() */
	function hash_encode64($input, $count, &$itoa64)
	{
		$output = '';
		$i = 0;

		do
		{
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}

			$output .= $itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}

			$output .= $itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			$output .= $itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}
	/**
	* Return unique id
	* @param string $extra additional entropy
	*/
	function unique_id($extra = 'c')
	{
		static $dss_seeded = false;
		
		$val = $GLOBALS['SYSTEM']['config']['rand_seed'] . microtime();
		$val = md5($val);
		$GLOBALS['SYSTEM']['config']['rand_seed'] = md5($GLOBALS['SYSTEM']['config']['rand_seed'] . $val . $extra);
		
		if ($dss_seeded !== true && ($GLOBALS['SYSTEM']['config']['rand_seed_last_update'] < time() - rand(1,10)))
		{
			$this-> config('rand_seed', $GLOBALS['SYSTEM']['config']['rand_seed'], true);
			$this-> config('rand_seed_last_update', time(), true);
			$dss_seeded = true;
		}
		return substr($val, 4, 16);
	}
	/**
	* Hashes an email address to a big integer
	*
	* @param string $email		Email address
	*
	* @return string			Unsigned Big Integer
	*/
	function email_hash($email='')
	{
		if(!$email){ return ; }
		return sprintf('%u', crc32(strtolower($email))) . strlen($email);
	}
}
$GLOBALS['SYSTEM']['users'] = new system_users;

if($_POST['user_out']){ $GLOBALS['SYSTEM']['users']-> session_end(); }// 1. если пользователь решил выйти, очищаем его куки

if(!$GLOBALS['SYSTEM']['without_user_auto_authentication']){ $GLOBALS['SYSTEM']['users']-> authentication(); }// автоматическая авторизация пользователя по существующим данным
?>
