<?php
// пользователь зашел в свой личный кабинет

$link = $GLOBALS['SYSTEM']['user_links'];// применяемые ссылки

$callback = '';
//-------------------- Изменяем имя пользолвателя -----------------
if($_POST['username']){
	
	$_POST['username'] = trim($_POST['username']);
	
	if($_POST['username'] == $GLOBALS['SYSTEM']['user']['username']){
		
		$callback = 'Вы указали Ваше текущее Имя пользователя.';
		
	}else if($GLOBALS['SYSTEM']['users']-> is_admin($_POST['username']) ){
		
		$callback = 'Вы указали недопустимое Имя пользователя.';
		
	}else{
		
		// проверка на уже заведенное Имя пользователя
		$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_username($_POST['username']);
		
		if($user_id){
			
			$callback = 'Такое Имя пользователя уже используется.';
			
		}else if(mysql_("UPDATE ".F."users SET 
			username="._.$_POST['username']._.", username_clean="._.$GLOBALS['SYSTEM']['users']-> username_clean($_POST['username'])._." 
			"._."WHERE"._." user_id = ".$GLOBALS['SYSTEM']['user']['user_id'])){
			
			$callback = 'Ваше Имя пользователя успешно сохранено.';
			
		}else{
			$callback = error('SQL change_username : '.__FILE__);
		}
		
	}
}
//---------------------- Изменение пароля -------------------------
if(!$callback && $_POST['user_newpasswd']){
	
	$user_actkey = md5(uniqid(time()*4));// Ключ активации пользователя
	
	if(mysql_query("UPDATE ".F."users SET 
		user_newpasswd='".$GLOBALS['SYSTEM']['users']-> hash($_POST['user_newpasswd'])."', 
		user_actkey='".$user_actkey."'
	WHERE user_id='".$GLOBALS['SYSTEM']['user']['user_id']."'")){
		
		$link_confirm = 'http://'. $_SERVER['HTTP_HOST'].$link['start'].'?a='.$user_actkey;
		
		$link_restore = 'http://'. $_SERVER['HTTP_HOST'].$link['restore'];
		
		$from_email = 'newpasswd@gmail.com';//host_clear($_SERVER['HTTP_HOST'])
		$from_name = 'Смена пароля: '.$_SERVER['HTTP_HOST'];
		$from_subject = 'Изменение пароля';
		
		// HTML-Текст письма:
        $text = $GLOBALS['SYSTEM']['users']-> respect().', '.$GLOBALS['SYSTEM']['users']-> username().'.<br>
		Кто-то, возможно Вы, пожелали сменить пароль к учетной записе на сайте.<br>
		Внимание: Ваш пароль ещё не изменен.<br>
		Если Вы подтверждаете изменение пароля, то перейдёте по следующей ссылке:<br><br>
		<a href="'.$link_confirm.'">'.$link_confirm.'</a><br>
		----------------------------<br>
		Новый пароль: '.htmlAccess($_POST['user_newpasswd']).' (активируется по ссылке, читайте выше)<br>
		----------------------------<br>
		Не забывайте свой пароль, он хранится в нашей базе в зашифрованном виде, и мы не сможем Вам его выслать.<br>
		Если Вы всё же забудете пароль, то сможете запросить новый на странице:<br><br>
		<a href="'.$link_restore.'">'.$link_restore.'</a>';
		
		if(!email($GLOBALS['SYSTEM']['user']['user_email'])){
			
			$callback = 'К сожалению, на Ваш E-mail адрес '.htmlAccess($GLOBALS['SYSTEM']['user']['user_email']).' не получилось отправить данные о изменении пароля. Попробуйте сохранить пароль еще раз.<br>В случае проблем, свяжитесь с нами любым удобным способом в разделе Контакты.';
			
		}else if(mail_($from_email, $from_name, $GLOBALS['SYSTEM']['user']['user_email'], $from_subject, $text)){
			
			$callback = 'Запрос на изменение пароля успешно выполнен, но пароль еще не изменен.<br>
			Проверьте свой E-mail для более подробной информации.';
			
		}else{
			$callback = error('MAIL 147 : '.__FILE__);
		}
	}else{
		$callback = error('SQL 150 : '.__FILE__);
	}
}
//-----------------------------------------------------------------
if(!$callback && $_POST['blog']){// проверяем и форматируем $_POST-данные (удаляем неразрешенные к редактированию)
	
	$_POST['blog'] = translite(strtolower_(trim($_POST['blog'])));
	
	// если после форматирования имени блога данные пусты ИЛИ данные с неправильными символами
	if(!$_POST['blog'] || !ereg('^[-a-z0-9]+$', $_POST['blog'])){
		
		$callback = '<font color="red">Разрешены только английские буквы и арабские цифры</font>';
		
	}else{
		
		if( $GLOBALS['SYSTEM']['users']-> update( array('blog' => $_POST['blog']) ) ){
			
			$callback = 'blog_ok:'.$_POST['blog'];
			
		}else{
			$callback = error('SQL create blog : '.__FILE__);
		}
	}
}
//-----------------------------------------------------------------
if(!$callback && $_POST){// проверяем и форматируем $_POST-данные (удаляем неразрешенные к редактированию)
	
	$_POST = $GLOBALS['SYSTEM']['users']-> format($_POST);// форматируем данные
	
	$error_avatar_upload = $GLOBALS['SYSTEM']['users']-> avatar_upload();// загружаем аватару если пользователь выбрал новое изображение
	
	if($error_avatar_upload){// если возникла ошибка при загрузке файла
		
		$callback = '<font color="red">'.$error_avatar_upload.'</font>';
		
	}else{
		
		$post = $_POST;
		$post['user_id'] = $GLOBALS['SYSTEM']['user']['user_id'];
		if($GLOBALS['SYSTEM']['users']-> update($post)){
			
			$callback = 'Настройки успешно сохранены.';
			
		}else{
			$callback = error('SQL 163 : '.__FILE__);
		}
	}
}
//-----------------------------------------------------------------
if($_POST){
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		echo $callback; exit;
	}else{
		$document = '<p>'.$callback.'</p>';
	}
}

//-----------------------------------------------------------------
$document .= '<style>
.checkbox {
	border: medium none;
	vertical-align: middle;
	margin: 0px;
}
/* FF2, FF3 только */
@-moz-document url-prefix(){
	.checkbox {
		margin:0 0 1px;
	}
}
/* Safari 3 и 4!, Chrome 2 */
@media screen and (-webkit-min-device-pixel-ratio:0) {
	body:first-of-type .checkbox{ vertical-align: top !important; margin-top: 6px }
}
.tabMe {
	padding: 10px 0 35px 0;
	border-bottom: 1px dashed #CCCCCC;
}
.tabMe P {
	padding: 0;
}
.tabName {
	font-family:"Lucida Grande",Arial,Verdana,sans-serif;
	font-size:18px;
	font-weight:normal;
}
.tabSubName {
	padding: 25px 0 3px;
	font-weight: bold;
}
.tabSubTable {
	margin: 15px 0 10px 0;
}
.tabSubTable TD {
	padding: 5px 0;
}
.tabSubTable DIV {
	font-weight: bold;
	text-align: right;
	padding-right: 5px;
	white-space: nowrap;
	vertical-align: top;
}
.tabSubTable INPUT {
	width:500px;
}
.tabSubTable P {
	margin:0;
}
.tabs TD {
	cursor: pointer;
	padding: 3px 15px 3px 15px;
	border-bottom: 1px solid #CCCCCC;
	border-right: 1px solid #CCCCCC;
}
TD.tabSelected {
	color: #000;
	border-bottom: none;
}
.tabDeSelected {
	color: #CCC;
}
TD.tabFirst {
	border-left: none;
	padding-left: 0px;
}
.tabFirst.tabSelected {
	
}
TD.tabLatest {
	border-left: none;
	border-right: none;
}
.tabLatest.tabSelected {
	border: none;
}
#user_allowsForm P {
	padding-left: 2px;
}
</style>

<script>
$(document).ready(function(){
	
	$("h1").css("margin-bottom","10px");
	
	var tabMe = $(".tabMe").css("border","0px")
	
	var table = "<table cellpadding=0 cellspacing=0 border=0 class=tabs><tr>";
	$(tabMe).each(function(i){
		table += "<td"+( (i==tabMe.length-1)? " class=tabLatest" : ( (i==0)? " class=tabFirst" : "" ) )+"> </td>";
	});
	table += "</tr></table>";
	table = $(table);
	
	$(".tabMe:first").before(table);
	
	var tableTD = $("TD", table);
	
	$(tableTD).each(function(i){
		
		$(this).append( $(" > :first-child", $(tabMe).eq(i) ) );
		$(this).click(function(){//mouseover
			
			$(tabMe).hide();
			$(tabMe).eq(i).show();
			
			$(tableTD).removeClass("tabSelected").addClass("tabDeSelected");
			$(this).removeClass("tabDeSelected").addClass("tabSelected");
			
		});
	});
	
	$(tabMe).hide();
	$(tabMe).eq(0).show();
	
	$(tableTD).addClass("tabDeSelected");
	$(tableTD).eq(0).removeClass("tabDeSelected").addClass("tabSelected");
	
	var tab_i = document.location.href.split("#tab=")[1];
	
	if(tab_i){
		$(".tabs TD").eq(tab_i).trigger("click");
	}
});
</script>
<!-- userPersonally -->
<div class="tabMe">
	
	<div class="tabName">Личное</div>
	
	<form action="'.$link['edit'].'" method="post" id="personallyForm">
		
		<div class="tabSubName">Мой статус/подпись:</div>
		<p><textarea name="user_sig" style="width:500px">'.htmlAccess($GLOBALS['SYSTEM']['user']['user_sig']).'</textarea></p>
		
		<div class="tabSubName">Род занятий (Ваша профессия или хобби):</div>
		<p><input name="user_occ" type="text" style="width:500px" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_occ']).'"></p>
		
		<div class="tabSubName">Мои интересы (расскажите о себе):</div>
		<p style="padding: 3px 0 25px 0">
			<textarea name="user_interests" style="width:500px; height: 250px;">'.htmlAccess($GLOBALS['SYSTEM']['user']['user_interests']).'</textarea><br>
			<span>Не более 32500 символов.</span>
		</p>
		
		<p><input type="submit" value="Сохранить настройки"></p>
		
	</form>
</div>
<script>
$("#personallyForm").submit(function(){
	var a = this.action;
	var d = $(this).serialize();
	$.post(a, d, function(msg){
		if(msg){
			$.jGrowl(msg);
		}else{
			ajax_return_empty(a,d);
		}
	});
	return false;
});
if($.isFunction($.fn.autoResize)){
	$("[name=user_interests]").autoResize().trigger("change");
}
</script>
<!-- /userPersonally -->

<!-- userAvatar -->
<div class="tabMe">
	
	<div class="tabName">Аватара</div>
	
	<form action="'.$link['edit'].'" method="post" enctype="multipart/form-data">
		
		<p>'.$GLOBALS['SYSTEM']['users']-> avatar_img().'</p>
		
		<div class="tabSubName">Минимальные размеры аватары:</div>
		<p>
			<span>Ширина × высота: '.$GLOBALS['SYSTEM']['config']['avatar_min_width'].' × '.$GLOBALS['SYSTEM']['config']['avatar_min_height'].' пикселов</span>
		</p>
		
		<div class="tabSubName">Максимальные размеры аватары:</div>
		<p>
			<span>Ширина × высота: '.$GLOBALS['SYSTEM']['config']['avatar_max_width'].' × '.$GLOBALS['SYSTEM']['config']['avatar_max_height'].' пикселов</span>
		</p>
		
		<div class="tabSubName">Правила размещения аватары:</div>
		<p>
			<span>возможны изображения: '.$GLOBALS['SYSTEM']['users']->avatar_types('str').'</span><br>
			<span>максимальный размер файла: '.filesize_($GLOBALS['SYSTEM']['config']['avatar_filesize'],0).' ('.$GLOBALS['SYSTEM']['config']['avatar_filesize'].' байт)</span><br>
		</p>
		
		<div class="tabSubName">Изменение аватары:</div>
		<p><input name="user_avatar" type="file"></p>
		
		<p style="padding-top:25px"><input type="submit" value="Загрузить аватару" name="submit_avatar"></p>
		
	</form>
</div>
<!-- /userAvatar -->';

$ex = explode('-', $GLOBALS['SYSTEM']['user']['user_birthday']? $GLOBALS['SYSTEM']['user']['user_birthday'] : $GLOBALS['SYSTEM']['users']-> cookie('birthday'));

$days = '<select name="birthday_d"><option value=""> - </option>'.option(1, 31, (int)$ex['0']).'</select>';// число
$mounts = '<select name="birthday_m"><option value=""> - </option>'.option(1, 12, (int)$ex['1']).'</select>';// месяц
$years = '<select name="birthday_y"><option value=""> - </option>'.option(1900, date('Y'), (int)$ex['2']).'</select>';// год

$document .= '<!-- userAllows -->
<div class="tabMe">
	<div class="tabName">Настройки</div>
	<form action="'.$link['edit'].'" method="post" id="user_allowsForm">
		
		<div class="tabSubName">Дата моего рождения:</div>
		<p style="font: 12px Arial,Helvetica,sans-serif; padding-top: 3px">
			число:&nbsp;'.$days.'&nbsp;&nbsp;&nbsp;
			месяц:&nbsp;'.$mounts.'&nbsp;&nbsp;&nbsp;
			год:&nbsp;'.$years.'
		</p>
		
		<div class="tabSubName">Откуда я (страна, город и т.п.):</div>
		<p><input name="user_from" type="text" style="width:500px" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_from']).'"></p>
		
		<div class="tabSubName">Текущее время года моего местоположения:</div>
		<p>
			<label class="radio"><input name="user_dst" type="radio" value="1" class="checkbox"'.($GLOBALS['SYSTEM']['user']['user_dst']?' checked':'').'> - летнее</label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="radio"><input name="user_dst" type="radio" value="0" class="checkbox"'.(!$GLOBALS['SYSTEM']['user']['user_dst']?' checked':'').'> - зимнее</label>
		</p>
		
		<div class="tabSubName">Часовой пояс:</div>
		<p><select name="user_timezone">'.$GLOBALS['SYSTEM']['users']-> timezones('options', $GLOBALS['SYSTEM']['user']['user_timezone']).'</select></p>
		
		<div class="tabSubName">Скрывать мое пребывание на сайте:</div>
		<p>
			<label><input name="user_allow_viewonline" type="radio" value="1" class="checkbox"'.($GLOBALS['SYSTEM']['user']['user_allow_viewonline']?' checked':'').'> - да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label><input name="user_allow_viewonline" type="radio" value="0" class="checkbox"'.(!$GLOBALS['SYSTEM']['user']['user_allow_viewonline']?' checked':'').'> - нет</label>
		</p>
		<div class="tabSubName">Разрешать зарегистрированным пользователям видеть мой E-mail:</div>
		<p>
			<label><input name="user_allow_viewemail" type="radio" value="1" class="checkbox"'.($GLOBALS['SYSTEM']['user']['user_allow_viewemail']?' checked':'').'> - да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label><input name="user_allow_viewemail" type="radio" value="0" class="checkbox"'.(!$GLOBALS['SYSTEM']['user']['user_allow_viewemail']?' checked':'').'> - нет</label>
		</p>
		<!-- 
		<div class="tabSubName">Сообщать мне на E-mail о появлении новых сообщений в темах, которых я участвовал: (форум)</div>
		<p>
			<label class="radio"><input name="user_notify" type="radio" value="1" class="checkbox"'.($GLOBALS['SYSTEM']['user']['user_notify']?' checked':'').'> - да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="radio"><input name="user_notify" type="radio" value="0" class="checkbox"'.(!$GLOBALS['SYSTEM']['user']['user_notify']?' checked':'').'> - нет</label>
		</p>
		<div class="tabSubName">Разрешать пользователям посылать мне личные сообщения на сайте: (форум)</div>
		<p>
			<label class="radio"><input name="user_allow_pm" type="radio" value="1" class="checkbox"'.($GLOBALS['SYSTEM']['user']['user_allow_pm']?' checked':'').'> - да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="radio"><input name="user_allow_pm" type="radio" value="0" class="checkbox"'.(!$GLOBALS['SYSTEM']['user']['user_allow_pm']?' checked':'').'> - нет</label>
		</p>
		-->
		<div class="tabSubName">Подписать меня на массовую рассылку от администрации:</div>
		<p>
			<label class="radio"><input name="user_allow_massemail" type="radio" value="1" class="checkbox"'.($GLOBALS['SYSTEM']['user']['user_allow_massemail']?' checked':'').'> - да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="radio"><input name="user_allow_massemail" type="radio" value="0" class="checkbox"'.(!$GLOBALS['SYSTEM']['user']['user_allow_massemail']?' checked':'').'> - нет</label>
		</p>
		
		<div class="tabSubName"><input type="submit" value="Сохранить настройки"></div>
	</form>
</div>
<script>
$("#user_allowsForm").submit(function(){
	var a = this.action;
	var d = $(this).serialize();
	$.post(a, d, function(msg){
		if(msg){
			$.jGrowl(msg);
		}else{
			ajax_return_empty(a,d);
		}
	});
	return false;
});
</script>
<!-- /userAllows -->

<!-- userContact -->
<div class="tabMe">
	
	<div class="tabName">Контакты</div>
	
	<form action="'.$link['edit'].'" method="post" id="contactForm">
		
		<table cellpadding="0" cellspacing="0" border="0" class="tabSubTable" width="270">
			<tr>
				<td><div>ICQ:</div></td>
				<td><div class="input"><div><input name="user_icq" type="text" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_icq']).'"></div></div></td>
			</tr>
			<tr>
				<td><div>AIM:</div></td>
				<td><div class="input"><div><input name="user_aim" type="text" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_aim']).'"></div></div></td>
			</tr>
			<tr>
				<td><div>Yahoo:</div></td>
				<td><div class="input"><div><input name="user_yim" type="text" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_yim']).'"></div></div></td>
			</tr>
			<tr>
				<td><div>WL/MSN:</div></td>
				<td><div class="input"><div><input name="user_msnm" type="text" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_msnm']).'"></div></div></td>
			</tr>
			<tr>
				<td><div>Jabber:</div></td>
				<td><div class="input"><div><input name="user_jabber" type="text" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_jabber']).'"></div></div></td>
			</tr>
			<tr>
				<td><div>Сайт:</div></td>
				<td><div class="input"><div><input name="user_website" type="text" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['user_website']).'"></div></div></td>
			</tr>
		</table>
		
		<p><input type="submit" value="Сохранить настройки" class="button"></p>
		
	</form>
</div>
<script>
$("#contactForm").submit(function(){
	var a = this.action;
	var d = $(this).serialize();
	$.post(a, d, function(msg){
		if(msg){
			$.jGrowl(msg);
		}else{
			ajax_return_empty(a,d);
		}
	});
	return false;
});
</script>
<!-- userContact -->

<!-- userAuthentication -->
<div class="tabMe">
	<div class="tabName">Авторизация</div>
	<div class="tabSubName">E-mail используемый для регистрации: <span style="font-weight:normal">'.htmlAccess($GLOBALS['SYSTEM']['user']['user_email']).'</span></div>
	<div class="tabSubName">Введите новое желаемое Имя пользователя:</div>
	<form action="'.$link['edit'].'" method="post" id="usernameForm">
		<p class="nameChange"><input name="username" type="text" style="width:500px" value="'.$GLOBALS['SYSTEM']['users']-> username().'"></p>
		<p><input type="submit" value="Сохранить новое Имя пользователя"></p>
	</form>
	<script>
	$("#usernameForm").submit(function(){
		var e = $("[name=username]", this).get(0);
		var v = $(e).val()
		if(v==""){
			$.jGrowl("Укажите новое желаемое Имя пользователя", { speed: 0, theme: "red" });
			var position = PosElement(e)
			scrollTo(0, position.Top-30);
			$(e).focus();
		}else{
			var a = this.action;
			$.post(a, {username:v}, function(msg){
				if(msg){
					$.jGrowl(msg);
				}else{
					ajax_return_empty(a,v);
				}
			});
		}
		return false;
	});
	</script>
	
	<div class="tabSubName">Введите новый пароль для входа на сайт:</div>
	<form action="'.$link['edit'].'" method="post" id="user_newpasswdForm">
		<p class="nameChange"><input name="user_newpasswd" style="width:500px" type="text" value=""></p>
		<p><input type="submit" value="Сохранить новый пароль"></p>
	</form>
	<script>
	$("#user_newpasswdForm").submit(function(){
		var e = $("[name=user_newpasswd]", this).get(0);
		var v = $(e).val()
		if(v==""){
			$.jGrowl("Укажите новый желаемый пароль", { speed: 0, theme: "red" });
			var position = PosElement(e)
			scrollTo(0, position.Top-30);
			$(e).focus();
		}else{
			var a = this.action;
			$.post(a, {user_newpasswd:v}, function(msg){
				if(msg){
					$.jGrowl(msg, {life: 7000});
				}else{
					ajax_return_empty(a,v);
				}
			});
		}
		return false;
	});
	</script>
</div>
<!-- /userAuthentication -->';
?>