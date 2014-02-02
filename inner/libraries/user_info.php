<?php
// скрипт добавляет пользователя (если его нет) и информацию о нем (обновление информации будет реализовано позже)
// для добавления доп. информации нужно указать $form_id
$error = null;
$user_id_in_database = false;
if(!$GLOBALS['SYSTEM']['user']['user_id']){// если ID пользователя не известен - добавляем пользователя в базу данных
	
	if(!function_exists('post20090831')){// если не выполняли проверку данных
		
		function post20090831($name){// возвращает POST-данные и удаляет возможность обращаться к ним еще раз
			if($_POST[ $name ]){ $return = $_POST[ $name ]; $_POST[ $name ] = null; }
			else if($_POST[ 'user_'.$name ]){ $return = $_POST[ 'user_'.$name ]; $_POST[ 'user_'.$name ] = null; }
			else if($_POST[ 'user'.$name ]){ $return = $_POST[ 'user'.$name ]; $_POST[ 'user'.$name ] = null; }
			return $return;
		}
		
		if(F){// для базы пользователей используется таблица форума
			
			$username = trim(post20090831('name'));// определяем введеное пользователем имя
			$email = strtolower_(trim($_POST['email']));// определяем введенный емэйл
			if(!email($email)){ $email = ''; }// если не валидный емэйл - отменяем его
			
			// если преопределить переменную  $cancel_addition_user_if_without_email = false; - то пользователи могут регистрироваться без $email
			$end = $cancel_addition_user_if_without_email? true:false;// предопределяем переменную
			
			if($email){// мыло указал
				
				@include($_SERVER['DOCUMENT_ROOT'].'/inner/user_reg_bad_domains.php');
				
				// проверяем наличие мыла в базе
				$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_user_email($email);
				
				if($user_id){// мыло в базе
					
					$user_id_in_database = true;
					$end = true;// не авторизовываем пользователя (пользователь будет Гостем - т.к. при желании он мог бы авторизоваться)
					
				}else{// мыла нет в базе
					
					$end = false;
					if(!$username){ $ex = explode('@',$email); $username = $ex['0']; }
					// добавляем пользователя и указанную им информацию в базу (ниже)
					// отправляем пользователю письмо о регистрации (ниже)
					// авторизовываем пользователя (ниже)
					
				}
				
				cookie('email', $email);// setcookie('email', addslashes($email), (time() + 622080000), "/");
				
			}else{// мыло не указал
				
				// не отправляем письмо (проверка на существование мыла стоит ниже)
				
				if($username=='гость'){
					$end = true;// не авторизовываем пользователя (пользователь будет Гостем - т.к. пожелал быть таковым)
				}else{
					// если преопределена $cancel_addition_user_if_without_email = false; - то пользователи могут регистрироваться без $email
					// по $_POST['username'] ИЛИ $_POST['name'] ИЛИ из $_POST['email'] получаем переменную $nick и добавляем в таблицу nicknames
					include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/user_nickname.php');
					$username = $nick;
					// добавляем пользователя и указанную им информацию в базу (ниже)
					// авторизовываем пользователя (ниже)
				}
			}
			
			if(!$end && $username){
				
				$confirm_uniq   = md5(uniqid(time()*4));
				
				$username = trim($username);
				$username_clean = $GLOBALS['SYSTEM']['users']-> username_clean($username);
				
				// находим пользователей у которых имена совпадает с текущим
				$a = $GLOBALS['SYSTEM']['users']-> users_by_username($username);// возвращенные имена все в нижнем регистре
				
				if($a){// проверка на уже заведенное Имя пользователя
			    	$i = 0;
			    	$check = true;
			    	while($check){
			    		
			    		$username_clean = $GLOBALS['SYSTEM']['users']-> username_clean($username);
			    		
			    		if($a[ $username_clean ]){// если Имя пользователя уже зарегистрировано в системе
			    			
			    			$i++;
			    			$username = trim($username.$i);// немного меняем имя пользователю, делая его уникальным
			    			$username_clean = $GLOBALS['SYSTEM']['users']-> username_clean($username);
			    			
			    			if(!$a[ $username_clean ]){
			    				$check = false;
			    			}
			    		}else{
			    			$check = false;
			    		}
			    	}
			    }
				
				$user_password = post20090831('password');// определяем введенный пользователем пароль
				if(!$user_password){ $user_password = mb_substr(md5(rand(0, time())),0,7); }
				
				$user_birthday = post20090831('birthday');// user_birthday - имеет формат d-m-Y
				$user_avatar = post20090831('avatar');// user_avatar - имеет формат http://site.ru/img.jpg
				$user_icq = post20090831('icq');
				$user_aim = post20090831('aim');
				$user_yim = post20090831('yim');
				$user_msnm = post20090831('msnm');
				$user_jabber = post20090831('jabber');
				$user_website = post20090831('website');
				
				// определяем группу (group_id), к которой относятся обычные зарегистрированные пользователи
				$group_id = $GLOBALS['SYSTEM']['users']-> group_id_reg();
				
				if(mysql_("INSERT INTO ".F."users SET 
					group_id = "._.$group_id._.",
					user_ip = "._.$_SERVER['REMOTE_ADDR']._.",
					username = "._.$username._.",
					username_clean = "._.$username_clean._.",
					user_regdate = "._.( $user_regdate? (int)$user_regdate : time() )._.",
					user_password = "._.$GLOBALS['SYSTEM']['users']-> hash($user_password)._.",
					user_passchg = "._.time()._.",
					user_email = "._.$email._.",
					user_email_hash = "._.$GLOBALS['SYSTEM']['users']-> email_hash($email)._.",
					user_notify = 1,
					user_timezone = "._."3.00"._.",
					user_dateformat = "._."D M d, Y g:i a"._.",
					user_lang = "._."ru"._.",
					user_lastmark = "._.time()._.",
					".($user_birthday? "user_birthday = "._.$user_birthday._.",":'')."
					".($user_avatar?"user_avatar = "._.$user_avatar._.",":'')."
					".($user_icq?"user_icq = "._.$user_icq._.",":'')."
					".($user_aim?"user_aim = "._.$user_aim._.",":'')."
					".($user_yim?"user_yim = "._.$user_yim._.",":'')."
					".($user_msnm?"user_msnm = "._.$user_msnm._.",":'')."
					".($user_jabber?"user_jabber = "._.$user_jabber._.",":'')."
					".($user_website?"user_website = "._.$user_website._.",":'')."
					user_type = ".($GLOBALS['SYSTEM']['config']['require_activation']? 1 : 0 ).",
					user_actkey = "._.($GLOBALS['SYSTEM']['config']['require_activation']? $confirm_uniq : 0 )._.",
					user_form_salt = "._.$GLOBALS['SYSTEM']['users']-> unique_id()._."")){
					
					$user_id = mysql_insert_id();
					
					if(mysql_query("INSERT INTO ".F."user_group SET 
					group_id = ".$group_id.", 
					user_id = ".$user_id.", 
					user_pending = 0")){
						
						$GLOBALS['SYSTEM']['users']-> session_start($user_id);// заводим phpbb3 сессию пользователю
						$GLOBALS['SYSTEM']['user'] = $GLOBALS['SYSTEM']['users']-> data($user_id);// находим данные пользователя
						
						include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/sync_cookies.php');
						
						if($GLOBALS['SYSTEM']['config']['require_activation']=='2'){
							
							$mail_text = 'Ваша учётная запись ещё не активна.
							В ближайшее время Ваши данные будут проверены и Вы получите письмо о активации Вашей учетной записи.<br>';
							
						}else if($GLOBALS['SYSTEM']['config']['require_activation']){
							
							$link_confirm = 'http://'. $_SERVER['HTTP_HOST'].'/s/user'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?a='.$confirm_uniq;
							
							$mail_text = 'Ваша учётная запись ещё не активна. Для ее активации перейдите по следующей ссылке:<br>
							<a href="'.$link_confirm.'">'.$link_confirm.'</a><br>';
							
						}else{
							$mail_text = '';//Ваша учётная запись активна.
						}
						
						$from_email = 'robot-user-info@gmail.com';
						$from_name = 'Registration: '.$_SERVER['HTTP_HOST_NAME'];
						$from_subject = 'Добро пожаловать на '.$_SERVER['HTTP_HOST_NAME'];
						
						$link_user_room = 'http://'. $_SERVER['HTTP_HOST'].$GLOBALS['SYSTEM']['user_links']['start'];
						
						//$link_restore = 'http://'. $_SERVER['HTTP_HOST'].'/forum/ucp.php?mode=sendpassword';
						$link_restore = 'http://'. $_SERVER['HTTP_HOST'].$GLOBALS['SYSTEM']['user_links']['restore'];
						
						$rf_referer = str_replace('://'.$_SERVER['HTTP_HOST'], '://'.$_SERVER['HTTP_HOST_NAME'], $_SERVER['HTTP_REFERER']);
						$rf_link_user_room = str_replace('://'.$_SERVER['HTTP_HOST'], '://'.$_SERVER['HTTP_HOST_NAME'], $link_user_room);
						$rf_link_restore = 'http://'. $_SERVER['HTTP_HOST_NAME'].$GLOBALS['SYSTEM']['user_links']['restore'];
						
						//HTML-Текст письма:
						$text = 'Добро пожаловать на '.$_SERVER['HTTP_HOST_NAME'].'<br>
						'.respect($username).', '.htmlAccess($username).'.<br>
						'.$mail_text.'
						Вы зарегистрировались на странице <a href="'.$_SERVER['HTTP_REFERER'].'">'.$rf_referer.'</a><br>
						Пожалуйста сохраните это сообщение или данные Вашей учётной записи:<br>
						----------------------------<br>
						Имя пользователя: '.htmlAccess($username).'<br>
						Пароль: '.htmlAccess($user_password).'<br>
						----------------------------<br>
						Имя пользователя и Пароль можно изменить на странице <a href="'.$link_user_room.'">'.$rf_link_user_room.'</a><br>
						----------------------------<br>
						Не забывайте свой пароль, он хранится в нашей базе в зашифрованном виде, и мы не сможем Вам его выслать.<br>
						Если Вы всё же забудете пароль, то сможете запросить новый, который можно будет получить по следующей ссылке:<br><br>
						<a href="'.$link_restore.'">'.$rf_link_restore.'</a><br><br>
						Спасибо за регистрацию.<br><br>
						Если Вы не регистрировались на нашем сайте, просто проигнорируйте данное письмо, мы более не будем Вам докучать.';
						
						if($email && !mail_($from_email, $from_name, $email, $from_subject, $text)){
							$error = error('mail_ в '.__FILE__);
						}
					}else{
						$error = error('user_group - при добавлении пользователя в '.__FILE__);
					}
				}else{
					$error = error('users - при добавлении пользователя в '.__FILE__);
				}
			}// проверка на $end ]
		}// проверка на использование таблицы пользователей данных phpbb3 ]
	}// проверка на множество раз ]
}else{// т.к. пользователь есть, обновляем введенные им данные
	// будет реализовано позже
}
if($error){ error('error in file '.__FILE__); }// ведем логи ошибок данного скрипта
?>
