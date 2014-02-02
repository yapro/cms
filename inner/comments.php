<?php
@include_once($_SERVER['DOCUMENT_ROOT'].'/js/comments/settings.php');// свои настройки

// название кнопки добавления комментария
$button = $comments_button? $comments_button : 'Добавить комментарий';

// надпись выше всех комментариев
$name = $comments_name? $comments_name : 'Комментарии посетителей:';

// сортировка вывода комментариев (ASC - в порядке добавления, DESC - в обратном порядке
$sort = $comments_sort? $comments_sort : 'ASC';

// только авторизованные пользователи могут оставлять комментарий
$authentication = $comments_authentication? $comments_authentication : false;

// если емэйл указан, скрипт отправлять отчет при размещеннии нового комментария на указанный емэйл
$moderator_email = $comments_moderator_email ? $comments_moderator_email : '';

// false - смайлы не нужны, true - смайлы нужны
$smiles = isset($comments_smiles)? $comments_smiles : true;

// если указан текст, значит комментарии должны быть проверены, а указанный в данной переменной текст будет отображен пользователю оставившему комментарии
$moderation = $comments_moderation ? $comments_moderation : '';//'Ваше сообщение отправлено и появится после проверки модератора.';

// если указать путь к файлу с данными подключения, скрипт будет отправлять комментарии jabber-пользователю
$comments_jabber = $_SERVER['DOCUMENT_ROOT'].'/jabber.php';

$file_js = $comments_js? $comments_js : '/js/comments/comments.js?v=5';// файл javascript

$file_css = $comments_css? $comments_css : '/js/comments/comments.css?v2';// файл css-стилей

$file_comment = $comments_file? $comments_file : '/js/comments/comment.html';// файл шаблона комментария

$file_form = $comments_form? $comments_form : '/js/comments/form.html';// файл шаблона формы комментария

$gravatar = $gravatar? $gravatar : array('s'=>25,'r'=>'any','d'=>'wavatar','guest'=>'monsterid','without_name'=>'mm');
// автоматическое создание ссылок в комментариях
// не реализовано: $autolinks = $comments_autolinks ? $comments_autolinks : true;

// количество сообщений на странице (если сообщений больше чем указано, то старые не будут показываться)
// не реализовано: $comments_show = $comments_show? $comments_show : '12345';

// функция выводит блок капчи
function comments_captcha($Name=''){
	
	$js = 'onclick="var c=document.getElementById(\'captchaImg'.$Name.'\'); c.src=c.src+\'&nocache=\'+Math.random(); return false;"';
	
	return '<td class="captcha_comments" width="1" style="padding: 7px 9px 0 0; cursor:pointer" '.$js.' title="Сменить изображение" valign="top"><img src="/images/reload.png" alt="CaptchaReload"></td>
	<td class="captcha_comments" width="1" valign="top"><img src="'.captchaSrc($Name).'" id="captchaImg'.$Name.'" alt="Captcha" style="cursor:pointer" title="Сменить изображение" '.$js.'></td>
	<td class="captcha_comments" width="1" valign="top" style="padding: 7px 5px 0; white-space:nowrap">Введите цифры изображенные слева:</td>
	<td class="captcha_comments" width="1" valign="top" style="padding-right:7px" class="input">
		<div><input name="captcha'.$Name.'" type="text" style="width:70px; font-size:23px"></div>
	</td>';
}

//---------------------------------------------------------------------------------------------------------------------------------------------------

$error = '';
$captchaName = 'comments';
$GLOBALS['comment_id_added'] = 0;// ID нового добавленного комментария
if(isset($_POST['comment'])){
	
	//if($authentication && !$GLOBALS['SYSTEM']['user']['user_id']){ $error = 'Только авторизованные пользователи могут оставлять комментарии'; }
	
	/* проверка на русские буквы */
	$comment_strtolower = strtolower_($_POST['comment']);
	if( translite($comment_strtolower,'s') == $comment_strtolower ){// значит чисто англ. текст
		$error = 'В Вашем комментарии отсутствуют русские буквы';
	}
	
	if(!$error && isBot($captchaName)){ $error = 'Цифры с изображения введены неверно'; }
	
	if(!$error && ban()){ $error = 'Вы заблокированы администратором сайта'; }
	
	if(!$error){// добавляем комментарий
		
		$trim_comment = trim($_POST['comment']);
		
		if(!$trim_comment){ $error = 'Вы не указали текст сообщения'; }
		
	}
	
	if(!$error){// добавляем комментарий
		
		// Проверка на существование копии данного сообщения
		$check = @mysql_fetch_assoc(mysql_("SELECT comment_id FROM ".P."comments 
		WHERE page_id="._.$this->id._." AND comment="._.$_POST['comment']._.""));
		
		if($check['comment_id']){
			
			$error = 'Аналогичное сообщение уже размещено на данной странице';
			
		}else{
			
			// получаем $nickname['id'] по переменной $_POST['username'] ИЛИ из $_POST['email']
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/user_nickname.php');
			
			// отменяем возможность добавлять пользователя в таблицу пользователей если он не указал email
			$cancel_addition_user_if_without_email = true;
			
			// автоматическое добавление пользователя и информации о нем
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/user_info.php');
			
			if(!$error){
				
				if(mysql_("INSERT INTO ".P."comments VALUES (
				"._._.",
				"._.$_POST['parent_id']._.",
				"._.$this->id._.",
				"._.$GLOBALS['SYSTEM']['user']['user_id']._.",
				INET_ATON("._.$_SERVER['REMOTE_ADDR']._."),
				"._.time()._.",
				"._.($moderation? 0 : time() )._.",
				"._.$nickname['id']._.",
				"._.$_POST['comment']._.",
				"._._.")")){
					
					$comment_id = mysql_insert_id();
					
					//$this-> updateTimeModified();// обновляем дату изменения текущей страницы
					
					// обновляем информацию о комментариях на странице
					mysql_query('UPDATE '.P.'pages SET 
					comment_id='.$comment_id.', 
					comment_user_id = '.$GLOBALS['SYSTEM']['user']['user_id'].', 
					comment_time='.time().', 
					comments=comments+1, 
					time_modified='.time().' 
					WHERE page_id='.$this->id);
					
					// подписываем пользователя на комментарии данной страницы
					if($GLOBALS['SYSTEM']['user']['user_id']){
						@mysql_query('INSERT INTO '.P.'subscribe_pages_comments SET 
						page_id = '.$this->id.', 
						user_id = '.$GLOBALS['SYSTEM']['user']['user_id'].',
						time_subscribe = '.time());
					}
					
					$GLOBALS['system_cache']-> update('comments');
					//-$GLOBALS['system_cache']-> update('comments_'.$this->id);
					
					if($moderation){ $error = $moderation; }
					
					$moderator_info = ($_SERVER['HTTP_REFERER']? $_SERVER['HTTP_REFERER'] : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])."#comment_".$comment_id."\n".
					$_SERVER['REMOTE_ADDR']." : ".($GLOBALS['SYSTEM']['user']['user_id']? htmlspecialchars($GLOBALS['SYSTEM']['user']['username']).' USER_ID' : htmlspecialchars($nick).' (Гость)' )." : ".
					($GLOBALS['SYSTEM']['user']['user_id']? $GLOBALS['SYSTEM']['user']['user_id'] : (int)$nickname['id']).":\n".
					htmlspecialchars($_POST['comment'])."\n".
					'http://'.$_SERVER['HTTP_HOST'].'/outer/delete_comment.php?id='.$comment_id;
					
					if($comments_jabber){// шпион сообщений - отправляет отчет при размещеннии нового комментария на JID-модератора
						$jabber = array();
						@include($comments_jabber);
						if($jabber && $jabber['toJID']){
							include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/XMPPHP/XMPP.php');
							$conn = new XMPPHP_XMPP($jabber['server'], $jabber['port'], $jabber['nikname'], $jabber['password']);
							$conn->connect();
							$conn->processUntil('session_start');
							$conn->message($jabber['toJID'], $moderator_info);
						}
					}else if($moderator_email){// шпион сообщений - отправляет отчет при размещеннии нового комментария на емэйл указанный выше
						if(!mail_('spy@gmail.com', $_SERVER['HTTP_HOST'], $moderator_email, 'New comment', 
						htmlspecialchars($_SERVER['HTTP_REFERER']).'<hr>'.$_POST['comment'])){
							error('mail_ '.__FILE__);
						}
					}
					
					// Сообщение успешно добавлено
					if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
						$GLOBALS['comment_id_added'] = $comment_id;
					}else{
						header302($_SERVER['REQUEST_URI']."#comment_".$comment_id);
					}
					
					$_POST['comment'] = '';// очищаем переменную, чтобы не использовать ее в нижней части кода текущего скрипта
					
				}else{
					$error = error('mysql_ '.__FILE__);
				}
			}
		}
	}
	if($error){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ echo 'error:'.$error; exit; }
		$document = '<DIV id="CommentError" style="color:#FF0000">'.$error.'</DIV>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------------------
/*$file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-3).$this->id.'.html');// создаем имя кэш-файла
$filemtime = filemtime($file);
необходимо применять файловый кэш т.к. глубо забирать в базе данных значения всех кэшей для всех страниц:
if($GLOBALS['system_cache']->name['comments'] < $filemtime && $GLOBALS['system_cache']->name['comments_'.$this->id] < $filemtime){
	
	$document = @file_get_contents($file);
	
}else{//------------------------------------------------------------------------------------------------------------------------
	*/
	class comments_class {
		function comments_class($sort='', $file_comment='', $gravatar=array()){
			
			if($sort && $file_comment && $q = mysql_query("SELECT comment_id, parent_id, user_id, time_created, nickname_id, comment, karma 
			FROM ".P."comments 
			WHERE page_id='".$GLOBALS['system']->id."' AND time_modified!='0' ORDER BY parent_id ASC, comment_id ".$sort."")){
				
				$this->gravatar = $gravatar;
				
				$user_id_in = $nickname_id_in = '';
				$this->users = $this->data = $this->parent_id__comment_id = array();
				
				while($r = @mysql_fetch_assoc($q)){
					$this->data[ $r['comment_id'] ] = $r;
					$this->parent_id__comment_id[ $r['parent_id'] ][ $r['comment_id'] ]++;
					if($r['user_id']){ $user_id_in .= $r['user_id'].','; }
					if($r['nickname_id']){ $nickname_id_in .= $r['nickname_id'].','; }
				}
				
				if($nickname_id_in && $q = mysql_query("SELECT id, nickname FROM ".N."nicknames WHERE id IN(".substr($nickname_id_in,0,-1).")")){
					while($r=mysql_fetch_assoc($q)){
						$this->nicknames[ $r['id'] ] = $r['nickname'];
					}
				}
				
				if($user_id_in && $q = mysql_query("SELECT user_id, username, user_email, user_avatar, user_avatar_type FROM ".F."users 
				WHERE username!='Anonymous' AND user_type IN (0,1,3) AND user_id IN(".substr($user_id_in,0,-1).")")){
					while($r=mysql_fetch_assoc($q)){
						$this->users[ $r['user_id'] ] = $r;
					}
				}
				
				$this->file_comment = $file_comment;// содержание шаблона комментария
				
				if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $GLOBALS['comment_id_added']){//
					echo 'user_id:'.(int)$GLOBALS['SYSTEM']['user']['user_id'].',comment:'.ajaxHTML($this-> comment($GLOBALS['comment_id_added']));
					exit;
				}
				$this-> i = 0;
				$this-> comments();
			}
		}
		
		function comment($comment_id=0){
			
			if($comment_id){// если имеются дочерние комментарии
				
				$r = $this->data[ $comment_id ];
				$user = $this->users[ $r['user_id'] ];
				
				$user_avatar = $this->users[ $r['user_id'] ]? $GLOBALS['SYSTEM']['users']-> avatar($user) : '';
				
				if(!$user_avatar){
					$gravatar_d = $this->gravatar['d'];
					if($user['user_email']){
						$gravatar_id = md5(strtolower_($user['user_email']));
					}else if($r['user_id']){
						$gravatar_id = $r['user_id'];
					}else if($r['nickname_id']){
						$gravatar_id = $r['nickname_id'];
						$gravatar_d = $this->gravatar['guest'];
					}else{
						$gravatar_id = $r['time_created'];
						$gravatar_d = $this->gravatar['without_name'];
					}
					//$user_avatar = '/js/comments/avatar.gif';
					//$user_avatar = 'http://www.gravatar.com/avatar.php?gravatar_id='. $gravatar_id.'&size=25&r=any&default=identicon';
					$user_avatar = 'http://www.gravatar.com/avatar/'. $gravatar_id.'?s='.$this->gravatar['s'].'&r='.$this->gravatar['r'].'&d='.$gravatar_d;
					
					if(substr($gravatar_d,0,1)=='/' || substr($gravatar_d,0,1)=='http'){
						$user_avatar = $gravatar_d;
					}
				}
				
				if($user && $user['username']){
					
					$username = '<a href="/i/u'.$user['user_id'].'">'.htmlAccess($user['username']).'</a>';
					
				}else if($r['nickname_id'] && $this->nicknames[ $r['nickname_id'] ]){
					
					$username = htmlAccess($this->nicknames[ $r['nickname_id'] ]).' (гость)';
					
				}else{
					$username = 'Гость';
				}
				
				$time_created = date('d', $r['time_created']).' '.$GLOBALS['system']->ru['month'][date('n', $r['time_created'])].' '.date('Y', $r['time_created']).', '.date('H:i', $r['time_created']);
				
				$karma = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $GLOBALS['comment_id_added'])? '' : ' полезен : <span class="karma'.($r['karma']? ' karma_'.(($r['karma']>0)?'pluse':'minus') : '').'">'.(($r['karma']>0)?'+':'').$r['karma'].'</span>';
				
				return '<li id="comment_'.$comment_id.'" class="comment">'.
				str_replace('$comment', '<div class="Message"><div class="Text">'.
					str_replace('$','&#036;',
						str_replace('  ', ' &nbsp;', 
							str_replace("\t", ' &nbsp; &nbsp; &nbsp; ', 
								strtr(nl2br(preg_replace('/(http|https):\/\/(.+)\s/suUi', '<a href="$1://$2" target="_blank">$0</a> ', htmlAccess($r['comment'].' '))), $GLOBALS['lebnik_smiles']['php'])
							)
						)
					).'</div></div>', 
					str_replace('$karma', $karma, 
						str_replace('$time_created', $time_created, 
							str_replace('$link','<a href="#comment_'.$comment_id.'" title="Ссылка на комментарий">#</a>', 
								str_replace('$username', str_replace('$','&#036;', $username), 
									str_replace('$user_avatar', $user_avatar, $this->file_comment)
								)
							)
						)
					)
				).'</li>';
			}
		}
		
		function comments($parent_id=0){
			
			if($this->parent_id__comment_id[ $parent_id ]){// если имеются дочерние комментарии
				
				$this-> i++;
				
				foreach($this->parent_id__comment_id[ $parent_id ] as $comment_id=>$true){
					
					$r = $this->data[ $comment_id ];
					$user = $this->users[ $r['user_id'] ];
					
					$this->comments .= $this-> comment($comment_id);
					
					if($this->parent_id__comment_id[ $comment_id ]){// если имеются дочерние комментарии
						$this->comments .= '<ul class="commentBG'.(($this-> i%2)?1:2).'">';
						$this-> comments($comment_id);
						$this->comments .= '</ul>';
					}
				}
				
				$this-> i--;
			}
		}
	}
	
	if($comments_closed){// в переменной можно указать почему закрыта форма комментариев
		
		$document .= ($comments_closed===true)? '' : $comments_closed;
		$comments_form_closed = true;
		
	}else{
		
		$GLOBALS['lebnik_smiles']['php'] = array();// необходимо объявить массив
		if($smiles){ include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/smiles.php'); }
		$comment_template = $_SERVER['DOCUMENT_ROOT'].$file_comment;// полный путь к шаблону комментария
		$comment_template_value = @file_get_contents($comment_template);// нахожу содержимое шаблона комментария
		$comments_class = new comments_class($sort, $comment_template_value, $gravatar);// создаю данные комментариев и смайлов
	}
	//-----------------------------------------------------------------------------------------------------------------------------------------------
	
	if($comments_form_closed){// в переменной можно указать почему закрыта форма комментариев
		
		$file_form = ($comments_form_closed==true)? '' : $comments_form_closed;
		
	}else{
		
		$file_form = @file_get_contents($_SERVER['DOCUMENT_ROOT'].$file_form);// нахожу содержимое шаблона формы
		
		if($file_form){
			$file_form = str_replace('<form', '<form action="'.$_SERVER['REQUEST_URI'].'"', $file_form);
			$file_form = str_replace('<form', '<form onsubmit="return CommentSubmit(this);"', $file_form);
			$file_form = str_replace('$comment_button', $button, $file_form);
			$file_form = str_replace('name="subscribe"', 'name="subscribe_comments['.$this->id.']"', $file_form);
			$file_form = str_replace('name="email" value=""', 'name="email" value="'.htmlAccess($_POST['email']).'"', $file_form);
			$file_form = str_replace('</textarea>', htmlAccess($_POST['comment']).'</textarea>', $file_form);
			$file_form = str_replace('$captcha', isBot($captchaName)? comments_captcha($captchaName) : '', $file_form);
			$file_form = str_replace('</form>', '<input type="hidden" name="parent_id"></form>', $file_form);
			if($GLOBALS['SYSTEM']['user']['user_id']){
				$file_form = preg_replace('/<!--ifUserNotReg-->(.+)<!--\/ifUserNotReg-->/sUi', '<input type="hidden" name="username" value="'.htmlAccess($GLOBALS['SYSTEM']['user']['username']).'">', $file_form);
			}else{
				if($_COOKIE['username']){
					
					$file_form = str_replace('name="username" value=""', 'name="username" value="'.htmlAccess(stripslashes($_COOKIE['username'])).'"', 
					$file_form);
					
				}
			}
		}
		
		//--------------------------------------------------------------------------------------------------------------------------------------
		
		$document .= ($file_css?'<link rel="stylesheet" href="'.$file_css.'" type="text/css" media="screen" charset="utf-8">':'').
		'<div id="comments">'.
		($comments_class->comments? '<div id="comments_name">'.$name.'</div><div id="commentsList"><ul class="comments">'.$comments_class->comments.'</ul></div>' : '').
		'</div><hr id="HRcommentForm">'.$file_form.
		($file_js?'<script>
			var comments_sort = "'.$sort.'";
			'.($_COOKIE[ $GLOBALS['notBot'] ]? 'comments_notBot = true;' : '').'
			</script>'.
			($GLOBALS['lebnik_smiles']['file_js']?'<script type="text/javascript" src="'.$GLOBALS['lebnik_smiles']['file_js'].'" charset="utf-8"></script>':'').'
			<script type="text/javascript" src="'.$file_js.'" charset="utf-8"></script>'
			:
			''
		);
	}
	
	//write_($document, $file);
	
//}
?>
