<?php
// скрипт рассылки новых комментариев со страниц сайта
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции
ignore_user_abort('1');
set_time_limit(500);
//------------------------------------------------------------------------------------------

$GLOBALS['SYSTEM']['config']['yapro_board_contact'] = '';// чтобы почта о ошибках не уходила
$GLOBALS['SYSTEM']['config']['yapro_file_errors'] = dirname(__FILE__).'/errors.log';// глобальный файл ошибок

function logX($s=null,$file=''){
	log_($s, dirname(__FILE__).'/'.$file.'.log');
}

$max = 10;// максимальное время выполнения (в минутах), если скрипт будет выполняться больше этого времени - администратор получит уведомление
$file = current(array_reverse(explode('/',dirname(__FILE__))));// имя файла по-умолчанию
include_once($_SERVER['DOCUMENT_ROOT'].'/cron/timer.php');
$GLOBALS['CRON_STOP_FILE'] = dirname(__FILE__).'/stop';// файл остановки выполнения в любой момент

logX('start', 'timer');

/******************************************************/

$max = 5;// максимальное количество комментариев прилагаемое к странице

$sql = 'SELECT spc.page_id, spc.time_lastmail, p.name, pu.url, pu.url_type, u.username, u.user_id, u.user_regdate, u.user_email 
FROM '.P.'subscribe_pages_comments AS spc
LEFT JOIN '.P.'pages AS p ON p.page_id = spc.page_id
LEFT JOIN '.P.'pages_url AS pu ON pu.page_id = p.page_id
LEFT JOIN '.F.'users AS u ON u.user_id = spc.user_id
LEFT JOIN '.P.'unsubscribe AS ue ON ue.email = u.user_email
WHERE spc.unsubscribe = 0 
AND u.user_type IN (0,3)
AND u.user_notify = 1
AND (ue.unsubscribe is NULL OR ue.unsubscribe = 0)
AND (ue.email_failed is NULL OR ue.email_failed = 0)
AND p.comment_user_id != spc.user_id
AND p.comment_time > spc.time_lastmail
AND p.comment_time < '.(time()-1500).'
AND u.user_email NOT LIKE '._.'%@vkontakte.ru'._.'
ORDER BY u.user_id';

if($q = mysql_($sql)){
	
	// time()-1500 это количество секунуд данное модератору на проверку сообщений (1500 секунд это 25 минут)
	// user_notify = 1 : Сообщать пользователю о новых сообщениях в темах, в которых он тоже участвовал
	// позже добавим user_notify_type : Тип уведомления о новых сообщениях (джабер и т.д.)
	
	$user_id = 0;
	$page_id_in = '';
	$data = $pages = $user_id__data = array();
	
	while($r = mysql_fetch_assoc($q)){
		
		$data[] = $r;
		
		$page_id_in .= $r['page_id'].',';
		
		$pages[ $r['page_id'] ] = $r;
		
		$user_id__data[ $r['user_id'] ] = $r;
		
	}
	//-print_r($data); exit;
	if($data){
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');// класс юзеров
		
		$http_site = 'http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'];
		
		if(count(explode('xn--',$_SERVER['HTTP_HOST']))>1){// поддержка кирилических доменов
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/idna_convert.class.php');
			$IDN = new idna_convert();
			$_SERVER['HTTP_HOST_NAME'] = $IDN->decode($GLOBALS['SYSTEM']['config']['yapro_http_host']);
		}else{
			$_SERVER['HTTP_HOST_NAME'] = $GLOBALS['SYSTEM']['config']['yapro_http_host'];
		}
		
		//----------------------------------------
		
		$user_id_in = $nickname_id_in = '';
		$page_id__comments = array();// находим комментарии страниц
		if($q = mysql_query("SELECT comment_id, page_id, user_id, time_created, nickname_id, comment, karma 
		FROM ".P."comments 
		WHERE page_id IN (".substr($page_id_in,0,-1).") 
		AND time_modified!='0' 
		ORDER BY comment_id DESC")){
			
			while($r = @mysql_fetch_assoc($q)){
				
				$page_id__comments[ $r['page_id'] ][ $r['comment_id'] ] = $r;
				
				if($r['user_id']){ $user_id_in .= $r['user_id'].','; }
				
				if($r['nickname_id']){ $nickname_id_in .= $r['nickname_id'].','; }
			}
		}
		
		//----------------------------------------
		
		$nicknames = array();// находим данны о никнэймах
		if($nickname_id_in && $q = mysql_query("SELECT id, nickname FROM ".N."nicknames WHERE id IN(".substr($nickname_id_in,0,-1).")")){
			while($r=mysql_fetch_assoc($q)){
				$nicknames[ $r['id'] ] = $r['nickname'];
			}
		}
		
		//----------------------------------------
		
		$users = array();// находим данны о аватарах
		if($user_id_in && $q = mysql_query("SELECT user_id, username, user_avatar, user_avatar_type 
		FROM ".F."users 
		WHERE username!='Anonymous' AND user_type IN (0,3) AND user_id IN(".substr($user_id_in,0,-1).")")){
			while($r=mysql_fetch_assoc($q)){
				$users[ $r['user_id'] ] = $r;
			}
		}
		//----------------------------------------
		
		// создаем список страниц на которых появились новые комментарии
		foreach($data as $d){
			
			$url = $http_site.'/s/p?'.$d['page_id'];// адрес страницы
			
			$latest_comments = '';
			
			if($page_id__comments[ $d['page_id'] ]){
				
				$page_id__comments_count = array();
				
				foreach($page_id__comments[ $d['page_id'] ] as $comment_id => $r){
					
					if($r['time_created'] < $d['time_lastmail']){ continue; }// если комментарий был размещен ранее чем комментарий текущего пользователя
					
					if($r['user_id'] == $d['user_id']){ continue; }// если это комментарий текущего пользователя
					
					$page_id__comments_count[ $r['page_id'] ]++;
					
					if($page_id__comments_count[ $r['page_id'] ] > $max){ continue; }
					
					$user = $users[ $r['user_id'] ];
					
					$url_user = $http_site.'/s/u'.$user['user_id'];
					
					$nickname = str_replace('&#036;','$', $GLOBALS['SYSTEM']['users']-> comment_name($user['username'], $nicknames[ $r['nickname_id'] ]));
					
					$description = str_replace('  ', ' &nbsp;', str_replace("\t", ' &nbsp; &nbsp; &nbsp; ', nl2br(htmlAccess(trim($r['comment'])))));
					
					$latest_comments = '<p><a href="'.$url_user.'" targe="_blank">'.$nickname.'</a> : '.$description.' <a href="'.$url.'&comment_'.$comment_id.'" targe="_blank">Ответить »</a></p>'.$latest_comments;
					//'.($r['karma']?' | <b>полезность комментария:</b> '.$r['karma']:'').'
				}
			}
			
			if(!$latest_comments){ continue; }// т.к. после проверки комментарии не найдены - продолжим проверку комментов в на др. странице
			
			$page_name_link = '<a href="'.$url.'" targe="_blank">'.htmlspecialchars($d['name']).'</a>';
			
			$user_id__data[ $d['user_id'] ]['message'] .= '<hr />
			<p>На странице '.$page_name_link.' появились новые комментарии:</p>';
			
			$unsubscribe_link = $http_site.'/outer/unsubscribe_pages_comments.php?hash='.$d['user_id'].'.'.$d['user_regdate'].'.'.$d['page_id'];
			
			$user_id__data[ $d['user_id'] ]['message'] .= $latest_comments.'<p><a href="'.$url.'&comments" targe="_blank">Читать все комментарии »</a><br><a href="'.$unsubscribe_link.'" targe="_blank">Отписаться от новых комментариев с данной страницы »</a>';
		}
		//----------------------------------------
		
		$site_link = '<a href="'.$http_site.'" targe="_blank">'.$_SERVER['HTTP_HOST_NAME'].'</a>';
		
		$mail_true = $mail_false = array();
		
		foreach($user_id__data as $user_id => $r){
			
			// т.к. после проверки комментарии не найдены - исключаем рассылку данному пользователю
			if(!$r['message']){
				$mail_true[ $r['user_id'] ] = $r['user_email'];
				continue;
			}
			
			$unsubscribe_pages_comments = $http_site.'/outer/unsubscribe_pages_comments.php?hash='.$r['user_id'].'.'.$r['user_regdate'];
			
			$unsubscribe_link = $http_site.'/outer/unsubscribe.php?hash='.$r['user_id'].'.'.$r['user_regdate'];//.'&email='.rawurlencode($r['user_email'])
			
			/*$unsubscribe_email = $http_site.'/outer/unsubscribe.php?email='.rawurlencode($r['user_email']);
			<p>Если данное письмо не имеет к Вам отношения, отпишите Ваш E-mail от рассылки по данной ссылке:<br><a href="'.$unsubscribe_email.'" targe="_blank">'.$unsubscribe_email.'</a></p>*/
			
			// HTML-Текст письма:
	        $message = '<p>'.respect($r['username']).', '.htmlspecialchars($r['username']).'.<br>
			Кто-то, возможно Вы, пожелали следить за комментариями на страницах сайта '.$site_link.'.<br>
			Поэтому, мы информируем Вас о появлении новых сообщений на страницах нашего сайта.</p>
			'.$r['message'].'
			<hr />
			<p>Отписаться от рассылки комментариев со всех страниц сайта, можно <a href="'.$unsubscribe_pages_comments.'" targe="_blank">здесь »</a></p>
			<p>Отписаться от всех рассылок с нашего сайта, можно <a href="'.$unsubscribe_link.'" targe="_blank">здесь »</a></p>
			<hr />
			<p>С уважением, коллектив '.$site_link.'</p>';
			
			//Отправляем почту
			if(mail_('subscribe@gmail.com', 'Рассылка : '.$_SERVER['HTTP_HOST_NAME'], $r['user_email'], 'Новые комментарии', $message, '', true)){
				$mail_true[ $r['user_id'] ] = $r['user_email'];
			}else{
				$mail_false[ $r['user_id'] ] = $r['user_email'];
			}
		}
		//----------------------------------------
		if($mail_true){// обновляем дату отправки последней рассылки
			$in = '';
			foreach($mail_true as $user_id => $user_email){
				$in .= $user_id.',';
			}
			if(!mysql_('UPDATE '.P.'subscribe_pages_comments SET time_lastmail = '.time().' WHERE user_id IN ('.substr($in,0,-1).')')){
				error('mail_true : '.__FILE__);
			}
		}
		if($mail_false){// обновляем/добавляем информацию о фиктивных E-mail адресах
			
			foreach($mail_false as $user_id => $user_email){
				
				@mysql_query(sql_('INSERT INTO '.P.'unsubscribe SET email_failed = 1, email = '._.$user_email._));
				
				$mysql_errno = mysql_errno();
				
				if($mysql_errno && $mysql_errno == 1062){
					
					mysql_('UPDATE '.P.'unsubscribe SET email_failed = 1 WHERE email = '._.$user_email._);
					
				}else if($mysql_errno){
					
					error('INSERT unsubscribe : '.$user_email.' : '.__FILE__);
					
				}
			}
		}
		//----------------------------------------
	}
}
logX('exit_', 'timer');
exit_();
//------------------------------------------------------------------------------------------
/*
<img src="/outer/cron_page_comments.php?update='.time().'" alt="cron" width="1" height="1" />

// Output transparent gif
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: 43');

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
*/
?>