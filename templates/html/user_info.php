<?php
if(defined('PAGE_USER_ID')){
	
	$r = $GLOBALS['SYSTEM']['users']-> data(PAGE_USER_ID);
	
	if(!$r['username']){
		
		header404('Выбранный пользователь не существует.');
		
	}
	
	if(!$r['user_close_page123']){
		
		$document = '';
		
		$user_birthday = '';
		if($r['user_birthday'] && $r['user_birthday']!=' 0- 0-   0'){
			
			$ex = explode('-', $r['user_birthday']);
			
			$d = (int)$ex['0'];
			$m = (int)$ex['1'];
			$y = (int)$ex['2'];
			
			if($d || $m || $y){
				$user_birthday = ($d? $d : '-').' '.($m? $GLOBALS['system']->ru['month'][$m] : '-').' '.($y? $y : '');
			}
		}
		
		//$document .= '<p>Текущее время года: <span>'.($r['user_dst']?'летнее':'зимнее').'</span></p>';
		
		//-------------------
		function t20110818($time=0){
			
			$year = ( date('Y', $time) == date('Y') )? '' : ' '.date('Y', $time).' года';
			
			if(date('Ymd', $time)==date('Ymd')){
				
				if($time>(time()-3600)){
					
					$x = round((time()-$time)/60);
					
					$x = $x? $x : 1;
					
					$t = $x.' '.noun($x, 'минуту', 'минуты', 'минут').' назад';
					
				}else if($time>(time()-36000)){
					
					$x = round((time()-$time)/3600);
					
					$t = $x.' '.noun($x, 'час', 'часа', 'часов').' назад';
					
				}else{
					
					$t = 'сегодня в '.date('H:i', $time);
					
				}
			}else if(date('Ymd',$time)==date('Ymd', (time()-86400))){
					
					$t = 'вчера в '.date('H:i', $time);
					
			}else{
				
				$t = date('j', $time).' '.$GLOBALS['system']->ru['month'][date('n', $time)].$year;
				
				if(time()-(30*86400) < $time){
					$t .= ' в '.date('H:i', $time);
				}
			}
			return $t;
		}
		//-------------------
		
		$comments = @mysql_fetch_assoc(mysql_('SELECT COUNT(*) AS c, SUM(karma) AS s FROM '.P.'comments WHERE user_id = '.$r['user_id']));
		$comment_latest_time_created = @mysql_fetch_row(mysql_('SELECT MAX(time_created) FROM '.P.'comments WHERE user_id = '.$r['user_id']));
		
		$karma = (int)$comments['s'];// отражение полезности
		
		$rating = round($comments['c']/25, 2);// отражение активности
		
		$rating += round($r['user_posts']/25, 2);// отражение активности
		
		//-------------------
		
		$user_avatar = $GLOBALS['SYSTEM']['users']-> avatar($r, 100);
		
		//-------------------
		
		$r['user_posts'] = 0;// Сообщений на форуме
		
		$status = '';//($demo['time_demo']?'модератор':'пользователь');
		
		$sex = '';//(($demo['sex']==1)?'мужской':'женский') : '');
		
		//-------------------
		$document = '
		<style>
		#userinfo {
			margin-top:10px;
		}
		#userinfo TD {
			vertical-align:top;
			padding-bottom: 10px;
		}
		#userinfo .td1 {
			font-weight: bold;
			padding: 0 10px 10px 0;
			text-align:right;
			width: 195px;
		}
		#userinfo_karma_rating TD {
			text-align: center;
			padding: 5px 10px 0 0;
			color: #CCC;
		}
		</style>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="userinfo">
		    <tr>
		        <td class="td1">
		            <img src="'.$user_avatar.'" style="'.($gravatar['s']?'height:'.$gravatar['s'].'px; width:'.$gravatar['s'].'px;':'').' border: 1px solid #CCC">
		        </td>
		        <td>
		            <h2>'.htmlAccess($r['username']).'</h2>
					<table border="0" cellpadding="0" cellspacing="0" id="userinfo_karma_rating">
					    <tr>
					        <td>
					            Карма:
					        </td>
					        <td>
					            Рейтинг:
					        </td>
					    </tr>
					    <tr>
					        <td style="font-size:21px; color: #66CC66;">
					            '.$karma.'
					        </td>
					        <td style="font-size:21px; color: #FF9900;">
					            '.$rating.'
					        </td>
					    </tr>
					    <tr>
					        <td>
					            отражение полезности
					        </td>
					        <td>
					            отражение активности
					        </td>
					    </tr>
					</table>
		        </td>
		    </tr>
		    '.($status? '<tr>
		        <td class="td1">
		            Статус на сайте:
		        </td>
		        <td>
		            '.$status.'
		        </td>
		    </tr>':'').'
		    <tr>
		        <td class="td1">
		            Зарегистрирован:
		        </td>
		        <td>
		            '.t20110818($r['user_regdate']).'
		        </td>
		    </tr>
		    '.($r['user_lastvisit']? '<tr>
		        <td class="td1">
		            Последний раз на сайте:
		        </td>
		        <td>
		            '.t20110818($r['user_lastvisit']).'
		        </td>
		    </tr>':'').'
		    <tr>
		        <td colspan="2" class="td1">
		    		<hr>
		        </td>
		    </tr>
		    '.($comments['c']? '<tr>
		        <td class="td1">
		            Cообщений на сайте:
		        </td>
		        <td>
		            '.$comments['c'].' шт., последнее '.t20110818($comment_latest_time_created['0']).'
		        </td>
		    </tr>
		    <!--<tr>
		        <td class="td1">
		            последнее сообщение:
		        </td>
		        <td>
		            '.t20110818($comment_latest_time_created['0']).'
		        </td>
		    </tr>-->':'').'
		    '.($r['user_posts']? '<tr>
		        <td class="td1">
		            Сообщений на форуме:
		        </td>
		        <td>
		            <a href="/forum/search.php?author_id='.$r['user_id'].'&sr=posts">'.$r['user_posts'].'</a> шт., последнее '.t20110818($r['user_lastpost_time']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_lastpost_time_']? '<tr>
		        <td class="td1">
		           последнее сообщение:
		        </td>
		        <td>
		            '.t20110818($r['user_lastpost_time']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_warnings']? '<tr>
		        <td class="td1">
		            Получил предупреждений:
		        </td>
		        <td>
		            '.$r['user_warnings'].' шт.
		        </td>
		    </tr>':'').'
		    
		    '.(($comments['c'] || $r['user_posts'])? '<tr>
		        <td colspan="2" class="td1">
		    		<hr>
		        </td>
		    </tr>':'').'
		    
		    <tr>
		        <td class="td1">
		            Часовой пояс:
		        </td>
		        <td>
		            '.$GLOBALS['SYSTEM']['users']-> timezones(0, $r['user_timezone']).'
		        </td>
		    </tr>
		    '.($r['user_from']? '<tr>
		        <td class="td1">
		            Местоположение:
		        </td>
		        <td>
		            '.htmlAccess($r['user_from']).'
		        </td>
		    </tr>':'').'
		    '.($sex? '<tr>
		        <td class="td1">
		            Пол:
		        </td>
		        <td>
		            '.$sex.'
		        </td>
		    </tr>':'').'
		    '.($user_birthday? '<tr>
		        <td class="td1">
		            Дата рождения:
		        </td>
		        <td>
		            '.$user_birthday.'
		        </td>
		    </tr>':'').'
		    '.($r['user_sig']? '<tr>
		        <td class="td1">
		            Подпись:
		        </td>
		        <td>
		            '.str_replace("\n",'<br>',htmlAccess($r['user_sig'])).'
		        </td>
		    </tr>':'').'
		    '.($r['user_occ']? '<tr>
		        <td class="td1">
		            Род занятий:
		        </td>
		        <td>
		            '.htmlAccess($r['user_occ']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_interests']? '<tr>
		        <td class="td1">
		            Интересы:
		        </td>
		        <td>
		            '.str_replace("\n",'<br>',htmlAccess($r['user_interests'])).'
		        </td>
		    </tr>':'').'
		    '.(($r['user_email'] && ($r['user_allow_viewemail'] || $GLOBALS['SYSTEM']['user']['user_type']==3))? '<tr>
		        <td class="td1">
		            E-mail:
		        </td>
		        <td>
		            '.htmlAccess($r['user_email']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_icq']? '<tr>
		        <td class="td1">
		            ICQ:
		        </td>
		        <td>
		            '.htmlAccess($r['user_icq']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_aim']? '<tr>
		        <td class="td1">
		            AIM:
		        </td>
		        <td>
		            '.htmlAccess($r['user_aim']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_yim']? '<tr>
		        <td class="td1">
		            Yahoo:
		        </td>
		        <td>
		            '.htmlAccess($r['user_yim']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_msnm']? '<tr>
		        <td class="td1">
		            WL/MSN:
		        </td>
		        <td>
		            '.htmlAccess($r['user_msnm']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_jabber']? '<tr>
		        <td class="td1">
		            Jabber:
		        </td>
		        <td>
		            '.htmlAccess($r['user_jabber']).'
		        </td>
		    </tr>':'').'
		    '.($r['user_website']? '<tr>
		        <td class="td1">
		            Сайт:
		        </td>
		        <td>
		            '.htmlAccess($r['user_website']).'
		        </td>
		    </tr>':'').'
		</table>';
		
	}else{
		
		$document .= '<p>Доступ к личным данным пользователя имеют только зарегистрированные пользователи.</p>
		<p>Чтобы просмотреть страницу, <a href="'.$GLOBALS['SYSTEM']['user_links']['registration'].'">зарегистрируйтесь</a> или <a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">авторизуйтесь »</a></p>';
		
	}
}else if(defined('PAGE_VISITOR_ID')){
	
	$h1 = 'Личный кабинет посетителя № '.PAGE_VISITOR_ID;
	$document .= '<p>Посетители не могут заполнять данные о себе, чтобы это сделать нужно <a href="'.$GLOBALS['SYSTEM']['user_links']['registration'].'">зарегистрироваться на сайте</a>.</p>';
	if(PAGE_VISITOR_ID == $_SESSION['SYSTEM']['VISITOR_ID']){
		$document .= '<p><a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">Авторизуйтесь</a> или <a href="'.$GLOBALS['SYSTEM']['user_links']['registration'].'">зарегистрируйтесь</a>, и Вы сможете воспользоваться всем функционалом сайта.</p>
		<p>Если не зарегистрированы, то можете потерять закладки и любимое, а если авторизованы - можете просматривать закладки с любого компьютера.</p>';
	}
}
?>