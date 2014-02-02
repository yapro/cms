<?php
// скрипт регистрации, подтверждения регистрации пользователя и восстановления учетной записи

if($_GET && ( !$_GET['a'] || count($_GET) > 1 )){
	header404('неправильные ГЕТ-данные '.__FILE__);
}

//---------------------------------------------------

$link = $GLOBALS['SYSTEM']['user_links'];// применяемые ссылки
//log_($_SERVER['HTTP_REFERER'].'--'.print_r($_POST,1).'--'.$_SERVER['REQUEST_URI'].'--'.$_COOKIE['user_redirect']);
//log_('$_COOKIE='.print_r($_COOKIE,1));
//log_($_SERVER['HTTP_REFERER'].'--'.$_COOKIE['user_redirect']);
if($_SERVER['HTTP_REFERER'] && !$_POST && !$_COOKIE['user_redirect'] && 
($_SERVER['REQUEST_URI'] == $link['start'] || $_SERVER['REQUEST_URI'] == $link['vkontakte'])){
	$referer = parse_url_($_SERVER['HTTP_REFERER']);// распарсим откуда идет посетитель
	//log_('$referer-host=='.$referer['host']);
	if($referer['host']==$GLOBALS['SYSTEM']['config']['yapro_http_host']){
		$e = explode($referer['scheme'].'://'.$referer['host'], $_SERVER['HTTP_REFERER']);
		//log_($referer['scheme'].'://'.$referer['host'].'==$e=='.$e['1']);
		if($e['1'] && $e['1'] != $link['out']){
			//log_('user_redirect='.$_SERVER['HTTP_REFERER']);
			// устанавливаем на 2 месяца
			setcookie('user_redirect', $_SERVER['HTTP_REFERER'], (time()+5555555), '/');
		}
	}
}
$GLOBALS['SYSTEM']['user_redirect'] = $link['start'];
function user_redirect_20111123(){
	if($_COOKIE['user_redirect']){// страница, на которую переадресуется пользователь в случае конечно успешного действия
		$GLOBALS['SYSTEM']['user_redirect'] = $_COOKIE['user_redirect'];
		@setcookie('user_redirect', 'delete_cookie', (time()-3600), '/');
	}
	//log_('user_redirect_20111123()=='.$GLOBALS['SYSTEM']['user_redirect']);
	// надпись при успешном авторизации любым из способов
	return ' В течении 7 секунд Вы будете перенаправлены '.($_COOKIE['user_redirect']?'на страницу, с которой пришли':'в <a href="'.$link['start'].'">личный кабинет</a>').'.</p><META HTTP-EQUIV="Refresh" CONTENT="7; URL='.$GLOBALS['SYSTEM']['user_redirect'].'">';
}

/*Ссылки для использования на форуме:
Восстановление пароля - /forum/ucp.php?mode=sendpassword
Регистрация - /forum/ucp.php?mode=register
$_SERVER['REQUEST_METHOD']!='POST'
$without_assistance = true;// обработка POST-запроса выполняется самостоятельно
include_once($_SERVER["DOCUMENT_ROOT"]."/inner/system_forms.php");*/

//---------------------------------------------------

$error = '';// ошибок нет

// пользователь решил выйти из учетной записи /s/user?a=out
if($_SERVER['REQUEST_URI'] == $link['out']){
	
	$GLOBALS['SYSTEM']['users']-> session_end();
	
	$document = '<p>Сеанс учетной записи успешно завершен.<br>
	В течении 7 секунд Вы будете перенаправлены на страницу авторизации.</p>
	<META HTTP-EQUIV="Refresh" CONTENT="7; URL='.$link['start'].'">';
	
// пользователь подтверждает свою учетку ссылокой - /s/user?a=md5
}else if($_GET['a'] && strlen($_GET['a'])==32){
	
	/*пользователям на сайте разрешено подтвержать себя через емэйл
	if($GLOBALS['SYSTEM']['config']['require_activation']==2){ $error = 'Подтверждение пользователя на нашем сайте работает только после администратора.'; }*/
	
	$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_user_actkey($_GET['a']);
	
	// предусмотрена многочисленная активация по последней ссылке, т.к. пользователь может выполнять 2-ой щелчок по ссылке подтверждения
	
	$user_newpasswd = $user_id? $GLOBALS['SYSTEM']['users']-> data($user_id, 'user_newpasswd') : '';
	
	if($user_id && mysql_query('UPDATE '.F.'users 
	SET '.($user_newpasswd? 'user_password = user_newpasswd, ':'').'user_type = 0 WHERE user_id = '.$user_id)){
		
		$GLOBALS['SYSTEM']['users']->data[ $user_id ]['user_password'] = $user_newpasswd;
		
		$GLOBALS['SYSTEM']['users']-> session_start($user_id, true);
		
		include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/sync_cookies.php');
		
		$document = '<p>'.$GLOBALS['SYSTEM']['users']-> respect().', '.$GLOBALS['SYSTEM']['users']-> username().'.
		Ваша учётная запись подтверждена.<br>'.user_redirect_20111123();
		
	}else{// т.к. пользователи могут обращаться на липовый урл вида - /s/user?a=ЕРУНДА
		
		$document ='<p>К сожалению, активация пользователя не выполнена. Попробуйте <a href="'.$link['registration'].'">зарегистрироваться еще раз»</a> или обратитесь к администрации сайта.</p>';
		
	}
	
// если пользователь авторизован
}else if($GLOBALS['SYSTEM']['user']['user_id']){
	$to = $GLOBALS['SYSTEM']['user_page'];//'http://'.
	/* ситуация отключена т.к. при 301 редиректе - setcookie не срабатывает
	log_('1='.$to);
	if($_COOKIE['user_redirect']){
		$to = $_COOKIE['user_redirect'];
		@setcookie('user_redirect', 'delete_cookie', (time()-3600), '/');
	}
	log_('2='.$to);
	*/
	if(!$to || $to=='#'){
		echo error('user_page'); exit;
	}
	header('location: '.$to, true, 301);// 301 - Ресурс окончательно перенесен
	exit;
	
// пользователь авторизовывает свою учетку через вконтакте.ру
}else if($_SERVER['REQUEST_URI'] == $link['vkontakte']){
	
	header('location: http://api.vkontakte.ru/oauth/authorize?client_id=2305107&scope=notify&display=page&response_type=code&redirect_uri='.$_SERVER['HTTP_HOST'].'/outer/vkontakte.php', true, 301);
	exit;
	
// пользователь авторизовывает свою учетку ссылкой - /s/user?a=user_id-user_regdate-md5(user_password)
}else if($_GET['a'] && count(explode('-',$_GET['a']))==3){
	
	$ex = explode('-',$_GET['a']);
	$user_id = (int)$ex['0'];
	$user_regdate = $ex['1'];
	$user_password_MD5 = $ex['2'];
	$url = str_replace('___DOT___','.',str_replace('___DASH___','-',$ex['3']));
	
	$r = $GLOBALS['SYSTEM']['users']-> data($user_id);
	
	// предусмотрена многочисленная активация по последней ссылки, т.к. пользователь может выполнять 2-ой щелчок по ссылке подтверждения
	if($r && $r['user_regdate'] == $user_regdate && md5($r['user_password']) == $user_password_MD5){
		
		$GLOBALS['SYSTEM']['users']-> session_start($user_id, true);
		
		include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/sync_cookies.php');
		
		$document = '<p>'.$GLOBALS['SYSTEM']['users']-> respect().', '.$GLOBALS['SYSTEM']['users']-> username().'. Спасибо за регистрацию.<br>
		Ваша учётная запись была активирована.<br>
		'.user_redirect_20111123();
		
		if($url){
			header301($url);
		}
		
	}else{// т.к. пользователи могут обращаться на липовый урл вида - /s/user?a=Е-РУН-ДА
		
		$document ='<p>К сожалению, активация пользователя не выполнена.<br>
		Попробуйте <a href="'.$link['registration'].'">зарегистрироваться еще раз»</a> или обратитесь к администрации сайта.</p>';
		
	}
	
// пользователь ввел свой емейл в форме восстановления и ему высылается: новый пароль и ссылка подтверждения
}else if($_SERVER['REQUEST_URI'] == $link['restore'] && $_POST['email_or_name']){// /s/user?a=restore и ПОСТ
	
	$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_user_email($_POST['email_or_name']);
	
	if(!$user_id){
		$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_username($_POST['email_or_name']);
	}
	
	if(!$user_id){
		
		$callback = 'Данный пользователь не зарегистрирован на нашем сайте.<br>
		Попробуйте <a href="'.$link['restore'].'">ввести другой E-mail</a> в <a href="'.$link['restore'].'">форме восстановления »</a>';
		
	}else{
		
		$user_actkey = md5(uniqid(time()*4));
		
		$user_newpasswd = substr(md5(uniqid(time()*4)), 0, 7);
		
		if($GLOBALS['SYSTEM']['users']-> update(array('user_id'=>$user_id, 'user_newpasswd'=>$GLOBALS['SYSTEM']['users']-> hash($user_newpasswd), 'user_actkey'=>$user_actkey)) ){
			
			$link_restore = 'http://'. $_SERVER['HTTP_HOST'].$link['restore'];
			
			$link_confirm = 'http://'. $_SERVER['HTTP_HOST'].$link['start'].'?a='.$user_actkey;
			
			$r = $GLOBALS['SYSTEM']['users']-> data($user_id);// находим данные о пользователе
			
			// HTML-Текст письма:
	        $text = $GLOBALS['SYSTEM']['users']-> respect($r['username']).', '.$GLOBALS['SYSTEM']['users']-> username($r['username']).'.<br>
			Кто-то, возможно Вы, пожелали восстановить данную учетную запись.<br>
			Ваша учётная запись ещё не изменена.<br>
			Если Вы подтверждаете восстановление учетной записи с созданием нового пароля, то перейдёте по следующей ссылке:<br><br>
			<a href="'.$link_confirm.'">'.$link_confirm.'</a><br><br>
			После того, как Вы перейдете по ссылке восстановления, параметры вашей учётной записи будут такими:<br>
			----------------------------<br>
			Имя пользователя: '.htmlAccess($r['username']).'<br>
			Пароль: '.htmlAccess($user_newpasswd).' (активируется по ссылке, читайте выше)<br>
			----------------------------<br>
			Не забывайте свой пароль, он хранится в нашей базе в зашифрованном виде, и мы не сможем Вам его выслать.<br>
			Если Вы всё же забудете пароль, то сможете запросить новый, который можно будет получить по следующей ссылке:<br><br>
			<a href="'.$link_restore.'">'.$link_restore.'</a>';
			
			$from_email = 'robot-user@gmail.com';
			$from_name = 'Restore: '.$_SERVER['HTTP_HOST_NAME'];
			$from_subject = 'Восстановление пароля';
			
			if(mail_($from_email, $from_name, $r['user_email'], $from_subject, $text)){
				
				$callback = 'Запрос на создание нового пароля успешно выполнен, но данные учётной записи еще не изменены.<br>
				Проверьте свою электронную почту <b>'.htmlAccess($r['user_email']).'</b> для более подробной информации.';
				
			}else{
				$callback = error('MAIL 2 : '.__FILE__, '', 'without_mail');// мыло может не отправиться т.к. указано криво
			}
		}else{
			$callback= error('SQL'.__FILE__);
		}
	}
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		echo $callback; exit;
	}else{
		$document = '<p>'.$callback.'</p>';
	}
	
// начало восстановления пароля - пользователь должен ввести свой емэйл
}else if($_SERVER['REQUEST_URI'] == $link['restore']){// /s/user?a=restore
	
	$document = '<p>Для восстановления доступа укажите Ваш E-mail или Имя пользователя:</p>
	<form action="'.$link['restore'].'" method="post" onsubmit="return checkRestoreUserForm(this);" id="emailRestoreForm">
	<div class="input" style="width:250px"><div><input name="email_or_name" type="text" value="'.htmlAccess($_POST['email_or_name']).'"></div></div>
	<p><input type="submit" value="Выслать новый пароль"></p>
	</form>
	<script language="javascript">
	function checkRestoreUserForm(form){
		var e = $("[name=email]", form);
		if($(e).val()==""){
			$.jGrowl("Пожалуйста, укажите E-mail", { speed: 0, theme: "red" });
			var position = PosElement(e[0])
			scrollTo(0, position.Top-30);
			$(e).focus();
			return false;
		}
	}
	</script>';
	
// регистрация пользователя
}else if($_SERVER['REQUEST_URI'] == $link['registration']){// /s/user?a=registration
	
	$registration_ok = false;
	
	if($_POST){
		
		if(!$_POST['email']){
			
			$callback = 'Введите E-Mail адрес.';
			
		}else if( !email($_POST['email']) ){
			
			$callback = 'E-Mail адрес введен неправильно.';
			
		}else if(!$_POST['username']){
			
			$callback = 'Введите Имя пользователя.';
			
		}else{
			
			// автоматическое добавление пользователя и информации о нем (если возникает проблема - создает переменную $error с описанием)
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/user_info.php');
			
			if($GLOBALS['SYSTEM']['user']['user_id']){
				
				$registration_ok = true;
				
				if($GLOBALS['SYSTEM']['config']['require_activation']=='2'){
					
					$callback = 'Учётная запись была создана, но не активирована.<br>
					В ближайшее время Ваши данные будут проверены и Вы получите письмо о активации Вашей учетной записи.';
					
				}else if($GLOBALS['SYSTEM']['config']['require_activation']){
					
					$callback = 'Учётная запись была создана.<br>
					Вам требуется активизация учётной записи, ключ для активизации был выслан Вам на E-Mail.<br>
					Проверьте свою почту для более подробной информации.';
					
				}else{
					
					$callback = 'Учётная запись создана и активна.<br>
					Проверьте свою почту для более подробной информации.<br>
					'.user_redirect_20111123();
					
				}
				
			}else if($user_id_in_database){
				
				$callback = 'Учетная запись не может быть создана, т.к. введенный E-Mail уже используется на нашем сайте.<br>
				Если это действительно Ваш E-Mail адрес, попробуйте <a href="'.$link['restore'].'">восстановить свою учетную запись »</a>';
				
			}else if($error){
				
				$callback = $error;
				
			}else{
				$callback = error('регистрация : '.__FILE__);
			}
		}
		//-----------------------------------------------------------------
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			echo ($registration_ok? 'registration_ok:' : '').$callback; exit;
		}else{
			$document = '<p>'.$callback.'</p>';
		}
	}
	//-----------------------------------------------------------------
	$document = $registration_ok? $document : ($document.'
	<style>
	#registrationTable TD {
		padding-top: 5px;
	}
	#registrationTable .user_tips {
		font-weight: bold;
		text-align: right;
		padding-top: 7px;
		padding-right: 0px;
		white-space: nowrap;
	}
	</style>
	<form action="'.$link['registration'].'" method="post" id="registrationForm">
		<p>Для регистрации заполните следующую форму:</p>
		<table cellpadding="0" cellspacing="0" border="0" id="registrationTable" class="cellpadding5">
			<tr>
				<td class="user_tips">Имя пользователя:</td>
				<td><div class="input"><div><input name="username" type="text" value="'.htmlAccess($_POST['username']).'"></div></div></td>
			</tr>
			<tr>
				<td class="user_tips">E-Mail адрес:</td>
				<td><div class="input"><div><input name="email" type="text" value="'.htmlAccess($_POST['email']).'"></div></div></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td width="250"><input type="submit" value="Зарегистрироваться"></td>
			</tr>
		</table>
		<p>Если забудете пароль, попробуйте <a href="'.$link['restore'].'">восстановить свою учетную запись »</a></p>
		<p><b>Обратите внимание:</b></p>
		<ul>
		    <li>После регистрации Вы получите на электронный адрес (E-Mail), письмо с логином и паролем.</li>
		    <li>Личные сведения, полученные в распоряжение при регистрации или каким-либо иным образом, без разрешения пользователей передаваться третьим организациям и лицам не будут, за исключением ситуаций, когда этого требует закон или судебное решение.</li>
		</ul>
	</form>
	<script language="javascript">
	$("#registrationForm").submit(function(){
		
		var e = $("[name=username]", this);
		if($(e).val()==""){
			$.jGrowl("Пожалуйста, укажите Имя пользователя", { speed: 0, theme: "red" });
			$(e).focus();
			return false;
		}
		
		var e = $("[name=email]", this);
		if($(e).val()==""){
			$.jGrowl("Пожалуйста, укажите E-mail", { speed: 0, theme: "red" });
			$(e).focus();
			return false;
		}
		var a = this.action;
		var d = $(this).serialize();
		$.post(a, d, function(msg){
			if(msg){
				var ex = msg.split(":");
				if(ex[0]=="registration_ok"){
					
					$("#registrationForm").html("<p>"+ex[1]+"</p>");
					
					var ex = ex[1].split("URL=");
					if(ex[1]){
						var ex = ex[1].split(\'"\');
						if(ex[0]){
							setTimeout(function(){
								document.location.href = ex[0];
							}, 7000);
						}
					}
				}else{
					$.jGrowl(msg, { speed: 0, life:7000, theme: "red" });
				}
			}else{
				ajax_return_empty(a, d);
			}
		});
		return false;
	});
	</script>');
	
// Авторизация:
}else if($_SERVER['REQUEST_URI'] == $link['start']){// /s/user
	
	if($_POST){
		
		$authentication = false;
		
		if(!$_POST['username']){
			
			$callback = 'Введите Имя пользователя.';
			
		}else if(!$_POST['user_password']){
			
			$callback = 'Введите Пароль доступа.';
			
		}else{
			
			$r = @mysql_fetch_assoc(mysql_("SELECT user_id, user_password, user_type FROM ".F."users
			WHERE username = "._.$_POST['username']._." LIMIT 1"));
			
			$user_password_ok = $GLOBALS['SYSTEM']['users']-> check_hash($_POST['user_password'], $r['user_password']);
			
			if($r['user_id'] && $user_password_ok && $r['user_type']==1){
				
				$callback = 'Перед авторизацией, Вы должны подтвердить свой E-Mail адрес, высланной Вам ссылкой подтверждения, или дождаться активации учетной записи модератором сайта.';
				
			}else if($r['user_id'] && $user_password_ok){
				
				$GLOBALS['SYSTEM']['users']-> session_start($r['user_id'], true);
				
				include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/sync_cookies.php');
				
				$callback = 'Авторизация успешно выполнена.<br>'.user_redirect_20111123();
				
				$authentication = $GLOBALS['SYSTEM']['user_redirect'];// страница, на которую переадресуется пользователь в случае конечно успешного действия
				
			}else if($r['user_id']){
				
				$callback = 'Вы указали неверный Пароль.';
				
			}else{
				
				$callback = 'Вы указали неверное Имя пользователя или Пароль.';
				
			}
		}
		//-----------------------------------------------------------------
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			if($authentication){
				echo '{"authentication":'.ajaxHTML($authentication).',"html":'.ajaxHTML($callback).'}';
			}else{
				echo '{"error":'.ajaxHTML($callback).'}';
			}
			exit;
			
		}else{
			
			$document = '<p>'.$callback.'</p>';
			
		}
	}
	//-----------------------------------------------------------------
	$document = $document.'
	<form action="'.$link['start'].'" method="post" id="authenticationForm">
	<style>
	#authenticationTable TD {
		padding-top: 5px;
	}
	#authenticationTable .user_tips {
		font-weight: bold;
		text-align: right;
		padding-top: 7px;
		padding-right: 0px;
		white-space: nowrap;
	}
	</style>
	<p>Для авторизации введите следующие данные:</p>
	<table cellpadding="0" cellspacing="0" border="0" id="authenticationTable" class="cellpadding5">
		<tr>
			<td class="user_tips">Имя пользователя:</td>
			<td><div class="input"><div><input name="username" type="text" value="'.htmlAccess($_POST['username']).'"></div></div></td>
		</tr>
		<tr>
			<td class="user_tips">Пароль:</td>
			<td><div class="input"><div><input name="user_password" type="text" value="'.htmlAccess($_POST['user_password']).'"></div></div></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td width="250"><input type="submit" value="Войти" class="button"></td>
		</tr>
	</table>
	<p><a href="'.$link['registration'].'">Регистрация на сайте</a> | <a href="'.$link['restore'].'">Восстановление пароля</a></p>
	</form>
	<script language="javascript">
	$("#authenticationForm [name=username]").focus();
	$("#authenticationForm").submit(function(){
		
		var e = $("[name=username]", this);
		if($(e).val()==""){
			$.jGrowl("Пожалуйста, укажите Имя пользователя", { speed: 0, theme: "red" });
			$(e).focus();
			return false;
		}
		
		var e = $("[name=user_password]", this);
		if($(e).val()==""){
			$.jGrowl("Пожалуйста, укажите Пароль", { speed: 0, theme: "red" });
			$(e).focus();
			return false;
		}
		var a = this.action;
		var d = $(this).serialize();
		$.post(a, d, function(r){
			if(r && r.authentication){
				$("#authenticationForm").html(r.html);
				setTimeout(function(){
					document.location.href = r.authentication;
				},7000);
			}else if(r && r.error){
				$.jGrowl(r.error, { speed: 0, life:7000, theme: "red" });
			}else{
				ajax_return_empty(a,d);
			}
		},"json");
		return false;
	});
	</script>';
	
}else{
	
	error('Неизвестная ситуация '.__FILE__);
	header404('Неизвестная ситуация '.__FILE__);
	
}
?>
