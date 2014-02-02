<?php
//$GLOBALS['SYSTEM']['module_id'] = '2';
include_once('../../libraries/access.php');
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
if(access('w') && $_POST['ajax_pages']=='comments_reindex' && pages_comments_reindex()){// обновляем информацию о комментариях на страницах
	echo '{"message":"Переиндексация выполнена"}'; exit;
}
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
if(access('w') && $_POST['ajax_pages']=='delete_cache_bots'){// >Удалить кэш-файл ботов
	if(is_file($_SERVER['DOCUMENT_ROOT'].'/cache/bots.php') && !@unlink($_SERVER['DOCUMENT_ROOT'].'/cache/bots.php')){
		echo '{"message":"Ошибка удаления файла, возможно нехватает прав доступа к файлу"}'; 
	}else{
		echo '{"message":"Кэш-файл поисковых ботов очищен"}';
	}
	exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='delete_301'){// Удалить статистику по ошибкам 301
	if(mysql_('DELETE FROM '.P.'pages_301 WHERE path_ignored != 1')){
		$msg = 'Удаление выполнено';
	}else{
		$msg = error(__FILE__);
	}
	echo '{"message":'.ajaxHTML($msg).'}';
	exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='delete_404'){// Удалить статистику по ошибкам 404
	if(mysql_('DELETE FROM '.P.'pages_404 WHERE path_ignored != 1')){
		$msg = 'Удаление выполнено';
	}else{
		$msg = error(__FILE__);
	}
	echo '{"message":'.ajaxHTML($msg).'}';
	exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='delete_path_ignored'){// Удалить игнорируемые 301 и 404 страницы
	if(mysql_('DELETE FROM '.P.'pages_301 WHERE path_ignored = 1') && mysql_('DELETE FROM '.P.'pages_404 WHERE path_ignored = 1')){
		$msg = 'Удаление выполнено';
	}else{
		$msg = error(__FILE__);
	}
	echo '{"message":'.ajaxHTML($msg).'}';
	exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='delete_cache_table_pages'){// Удалить кэш-файл полей основной таблицы
	if(is_file($_SERVER['DOCUMENT_ROOT'].'/cache/pages_fields.php') && !@unlink($_SERVER['DOCUMENT_ROOT'].'/cache/pages_fields.php')){
		echo '{"message":"Ошибка удаления файла, возможно нехватает прав доступа к файлу"}'; 
	}else{
		echo '{"message":"Кэш-файл полей основной таблицы удален"}';
	}
	exit;
}
//------------------------------------------------------------// обновляем информацию родительских page_id на страницах
if(access('w') && $_POST['ajax_pages']=='parents_id_reindex'){
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/pages.php');// класс работы с таблицей страниц
	$GLOBALS['pages']-> parents_id_reindex();
	echo '{"message":"Переиндексация выполнена"}'; exit;
}

//------------------------------------------------------------// Исправить наследуемый доступ страниц (access_inherited)
if(access('w') && $_POST['ajax_pages']=='pages_fix_access_inherited'){
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/pages.php');// класс работы с таблицей страниц
	
	if( isset($GLOBALS['pages']->parent_id__page_id) && isset($GLOBALS['pages']->parent_id__page_id['0']) ){
		
		foreach( $GLOBALS['pages']->parent_id__page_id['0'] as $parent_id => $array_pages ){
			
			$page_data = $GLOBALS['pages']->select($parent_id);
			
			$GLOBALS['pages']->access_inherited($parent_id, $page_data['access']);
		}
	}
	echo '{"message":"Исправление выполнено"}'; exit;
}
//------------------------------------------------------------ удаляю посетителей, которые не заходили на сайт уже более 4 месяцев (123 дня)
if(access('w') && $_POST['ajax_pages']=='delete_old_cookies' 
&& $q = mysql_('SELECT visitor_id FROM '.P.'cookies_visitors WHERE time_modified < '.(time() - (86400 * 123) ))){
	$error = 0;
	while($r = mysql_fetch_assoc($q)){
		if(mysql_('DELETE FROM '.P.'cookies_visitors WHERE visitor_id = '.$r['visitor_id'])){// удаляем посетителей
			if(!mysql_('DELETE FROM '.P.'cookies WHERE visitor_id = '.$r['visitor_id'])){// удаляем данные несуществующих посетителей
				$error++;
			}
		}else{
			$error++;
		}
	}
	if($error){
		echo '{"message":"Ошибка удаления"}';
	}else{
		echo '{"message":"Удаление выполнено"}';
	}
	exit;
}
//------------------------------------------------------------
function rating_reindex_20110301(){// обновляем информацию оценок страниц
	if($q = mysql_('SELECT page_id, SUM(rating) AS s, COUNT(user_ip) AS u FROM '.P.'pages_rating GROUP BY page_id')){
		while($r = mysql_fetch_assoc($q)){
			
			mysql_('UPDATE '.P.'pages SET rating_sum = '.(int)$r['s'].', rating_users = '.(int)$r['u'].' WHERE page_id = '.$r['page_id']);
			
		}
		return true;
	}
}
if(access('w') && $_POST['ajax_pages']=='rating_reindex' && rating_reindex_20110301()){
	echo '{"message":"Переиндексация выполнена"}'; exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='sunc_phpbb3_users_group'){
	
	// находим пользователей, которые не участвуют ни в одной из групп на форуме - следовательно у них даже нет прав на просмотр форума
	if($q = mysql_query('SELECT u.user_id, u.group_id
	FROM '.F.'users AS u
	LEFT JOIN '.F.'user_group AS g ON g.user_id = u.user_id
	WHERE u.user_type IN (0,1) AND g.user_id IS NULL')){
		while($r = mysql_fetch_assoc($q)){
			
			mysql_query('INSERT INTO '.F.'user_group SET 
			group_id = '.$r['group_id'].', user_id = '.$r['user_id'].', group_leader = 0, user_pending = 0') or die(mysql_error());
			
		}
	}
	echo '{"message":"Синхронизация выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу комментариев
if(access('w') && $_POST['ajax_pages']=='delete_comments' 
&& mysql_query("TRUNCATE TABLE ".P."comments")
&& mysql_query("TRUNCATE TABLE ".P."comments_karma")
&& mysql_query("UPDATE ".P."pages SET comment_id=0, comment_user_id=0, comment_time=0, comments=0")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу никнэймов в комментариях
if(access('w') && $_POST['ajax_pages']=='delete_nicknames' 
&& mysql_query("TRUNCATE TABLE ".N."nicknames")
&& mysql_query("UPDATE ".P."comments SET nickname_id=0")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу cookies
if(access('w') && $_POST['ajax_pages']=='delete_cookies' 
&& mysql_query("TRUNCATE TABLE ".P."cookies")
&& mysql_query("TRUNCATE TABLE ".C."cookies_visitors")
&& mysql_query("TRUNCATE TABLE ".P."pages_bookmarks")
&& mysql_query("UPDATE ".P."pages_rating SET visitor_id=0")
&& mysql_query("DELETE FROM ".P."pages_rating WHERE visitor_id=0 AND user_id=0")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу "что ищут на сайте"
if(access('w') && $_POST['ajax_pages']=='delete_search' 
&& mysql_query("TRUNCATE TABLE ".P."search")
&& mysql_query("TRUNCATE TABLE ".P."search_keywords")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу опросов и голосований
if(access('w') && $_POST['ajax_pages']=='delete_poll' 
&& mysql_query("TRUNCATE TABLE ".P."poll")
&& mysql_query("TRUNCATE TABLE ".P."poll_answered_users")
&& mysql_query("TRUNCATE TABLE ".P."poll_answers")
&& mysql_query("TRUNCATE TABLE ".P."poll_questions")
&& mysql_query("TRUNCATE TABLE ".P."poll_users")
&& mysql_query("TRUNCATE TABLE ".P."poll_variants")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу предупреждений IP-адресам
if(access('w') && $_POST['ajax_pages']=='delete_warnings_ip' && mysql_query("TRUNCATE TABLE ".W."warnings_ip")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='karma_clear' && mysql_query("TRUNCATE TABLE ".P."comments_karma") // обнулить карму комментариев
&& mysql_query("UPDATE ".P."comments SET karma = 0")){
	echo '{"message":"Обунление выполнено"}'; exit;
}
//------------------------------------------------------------ Очистить базу отправленных с сайта писем
if(access('w') && $_POST['ajax_pages']=='delete_mail_duplicates' && mysql_query("TRUNCATE TABLE ".P."mail_duplicates")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу подписчиков на новости сайта
if(access('w') && $_POST['ajax_pages']=='delete_subscribe' && mysql_query("TRUNCATE TABLE ".P."subscribe")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу подписчиков на комментарии к страницам
if(access('w') && $_POST['ajax_pages']=='delete_subscribe_pages_comments' && mysql_query("TRUNCATE TABLE ".P."subscribe_pages_comments")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу отписанных от всех рассылок с сайта
if(access('w') && $_POST['ajax_pages']=='delete_unsubscribe' && mysql_query("TRUNCATE TABLE ".P."unsubscribe")){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------ Очистить базу оценок страниц
if(access('w') && $_POST['ajax_pages']=='delete_pages_rating' 
&& mysql_("TRUNCATE TABLE ".P."pages_rating") 
&& mysql_('UPDATE '.P.'pages SET rating_sum = 0, rating_users = 0')){
	echo '{"message":"Очистка выполнена"}'; exit;
}
//------------------------------------------------------------
// cинхронизация информации: удалим лишние строки участников возможно уже несуществующих голосований из таблиц poll_users и poll_answered_users
function poll_users_sync_20110301(){
	if($q = mysql_query("SELECT DISTINCT poll_user_id FROM ".P."poll_answers")){
		$in_poll_user_id = '';
		while($r = mysql_fetch_assoc($q)){
			$in_poll_user_id .= $r['poll_user_id'].',';
		}
		if($in_poll_user_id){
			mysql_query("DELETE FROM ".P."poll_users WHERE poll_user_id NOT IN (".substr($in_poll_user_id,0,-1).")");
			mysql_query("DELETE FROM ".P."poll_answered_users WHERE poll_user_id NOT IN (".substr($in_poll_user_id,0,-1).")");
		}
		return true;
	}
}
if(access('w') && $_POST['ajax_pages']=='poll_users_sync' && poll_users_sync_20110301()){
	echo '{"message":"Cинхронизация данных выполнена"}'; exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='cache_delete'){// очистка кэш-директории
	$error = false;
	class recursive_read_dir {
		function recursive_read_dir($path){
			if($dir = @dir($path)){
				while ($file = $dir-> read()){
					if($file != "." && $file != ".."){
						if(is_dir($path.$file)){
							if($path.$file==$_SERVER['DOCUMENT_ROOT'].'/cache/url'){ continue; }
							$this->rmdirs[] = $path.$file;
							$this-> recursive_read_dir($path.$file.'/');
						}else{
							if($path.$file==$_SERVER['DOCUMENT_ROOT'].'/cache/info.txt'){ continue; }
							$this->unlinks[] = $path.$file;
						}
					}
				}
			}
		}
	}
	$recursive_read_dir = new recursive_read_dir($_SERVER['DOCUMENT_ROOT'].'/cache/');
	if($recursive_read_dir->unlinks){
		foreach($recursive_read_dir->unlinks as $fpath){
			if(!$fpath || !@unlink($fpath)){
				$error++;
			}
		}
	}
	if($recursive_read_dir->rmdirs){
		krsort($recursive_read_dir->rmdirs);
		foreach($recursive_read_dir->rmdirs as $dpath){
			if(!$dpath || !@rmdir($dpath)){
				$error++;
			}
		}
	}
	if($error){
		echo '{"message":"Возникла ошибка удаления"}';
	}else{
		echo '{"message":"Кэш директория очищена"}';
	}
	exit;
}
//------------------------------------------------------------
function delete_files_201106($path){
	if(!$path){ return false; }
	$error = false;
	if(is_dir($path) && $dir = @dir($path)){
		while ($file = $dir-> read()){
			if($file != "." && $file != ".." && is_file($path.$file)){
				if(!@unlink($path.$file)){
					$error++;
				}
			}
		}
	}
	return $error? false : true;
}
//------------------------------------------------------------ Очистить кэш CSS и JavaScript файлов
if(access('w') && $_POST['ajax_pages']=='delete_cache_css_js'){
	if(delete_files_201106($_SERVER['DOCUMENT_ROOT'].'/cache/css_js/')){
		echo '{"message":"Кэш-директория очищена"}';
	}else{
		echo '{"message":"Возникла ошибка удаления"}';
	}
	exit;
}
//------------------------------------------------------------ Очистить архив изменений страниц
if(access('w') && $_POST['ajax_pages']=='archive_delete' && $_SERVER['SYSTEM']['PATH']){// очистка директорию архивных копий страниц сайта
	if(delete_files_201106($_SERVER['SYSTEM']['PATH'].'modules/pages/archive/') && mysql_('DELETE FROM '.P.'pages_archive WHERE id!=0')){
		echo '{"message":"Архив очищен"}';
	}else{
		echo '{"message":"Возникла ошибка удаления"}';
	}
	exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_pages']=='cache_url_delete'){// очистка кэшей адресов страниц
	if(delete_files_201106($_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/')
	&& delete_files_201106($_SERVER['DOCUMENT_ROOT'].'/cache/url/md5_name__dir/')){
		echo '{"message":"Кэши адресов страниц очищены"}';
	}else{
		echo '{"message":"Возникла ошибка удаления"}';
	}
	exit;
}
//------------------------------------------------------------
if(access('w') && $_POST['ajax_delete_users'] && $_GET['where']){
	
	if($_GET['where'] == 'no_comments_no_visit_12_mounts'){// не комментировавших - без учета времени
		$in_del = '';
		$exclude = array();
		if($q = mysql_query('SELECT user_id FROM '.P.'comments 
		WHERE user_id != 0 AND time_modified != 0')){// находим тех, кто хоть раз комментировал
			while($r=mysql_fetch_assoc($q)){
				if(!$exclude[ $r['user_id'] ]){
					$exclude[ $r['user_id'] ] = true;
					$in_del .= $r['user_id'].',';// отменяем удаление данных пользователей
				}
			}
		}
		
		$mounts_12 = (time()-(86400*30*12));
		
		$_GET['where'] = 'user_lastvisit < '.$mounts_12.' AND user_lastpost_time=0'.($in_del? ' AND user_id NOT IN ('.substr($in_del,0,-1).')' : '');
	}
	/*if($_GET['where'] == 'no_comments'){// не комментировавших - без учета времени
		$in_del = '';
		if($q = mysql_query("SELECT user_id FROM ".P."comments 
		WHERE user_id != 0 AND time_modified != 0")){
			while($r=mysql_fetch_assoc($q)){
				$in_del .= $r['user_id'].',';// отменяем удаление данных пользователей
			}
		}
		$_GET['where'] = 'user_lastpost_time=0'.($in_del? ' AND user_id NOT IN ('.substr($in_del,0,-1).')' : '');
	}*/
	
	if($q = mysql_query("SELECT user_id FROM ".F."users 
	WHERE user_type IN (0,1) AND ".str_replace('AND',' AND ',$_GET['where']))){// удаляем пользователей
		
		$in = '';
		while($r = mysql_fetch_assoc($q)){
			$in .= $r['user_id'].',';
		}
		$table = F;
		if($in){
			$in = ' IN ('.substr($in,0,-1).')';
			mysql_query("DELETE FROM ".$table."user_group WHERE user_id".$in);
			mysql_query("DELETE FROM ".$table."banlist WHERE ban_userid".$in);
			mysql_query("DELETE FROM ".$table."sessions_keys WHERE user_id".$in);
			mysql_query("DELETE FROM ".$table."sessions WHERE session_user_id".$in);
			mysql_query("DELETE FROM ".$table."profile_fields_data WHERE user_id".$in);
			
			@mysql_query("DELETE FROM ".$table."acl_users WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."bookmarks WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."drafts WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."forums_access WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."forums_track WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."forums_watch WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."log WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."moderator_cache WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."poll_votes WHERE vote_user_id".$in);
			@mysql_query("DELETE FROM ".$table."privmsgs_folder WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."privmsgs_rules WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."privmsgs_to WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."reports WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."topics_posted WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."topics_track WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."topics_watch WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."warnings WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."zebra WHERE user_id".$in);
			@mysql_query("DELETE FROM ".$table."topics_track WHERE user_id".$in);
			
			mysql_query("DELETE FROM ".P."comments_karma WHERE user_id".$in);
			mysql_query("UPDATE ".P."comments SET user_id = 0 WHERE user_id".$in);
			mysql_query("DELETE FROM ".P."pages_rating WHERE user_id".$in);
			mysql_query("DELETE FROM ".P."subscribe WHERE user_id".$in);
			mysql_query("DELETE FROM ".P."subscribe_pages_comments WHERE user_id".$in);
			mysql_query("UPDATE ".P."search SET user_id = 0 WHERE user_id".$in);
			//mysql_query("UPDATE ".P."pages SET comment_user_id = 0, user_id = 0 WHERE user_id".$in);
			pages_comments_reindex();// обновляем информацию о комментариях на страницах
			rating_reindex_20110301();// обновляем информацию оценок страниц
			poll_users_sync_20110301();// удалим уже несуществующих участников голосований
		}
	}
	echo '{"message":"Удаление выполнено"}'; exit;
}
//------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ echo '{"message":"Неизвестная ситуация"}'; exit; }
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
echo $_SERVER['SITE_HEADER'].Head('Действия над сайтом').'
<style type="text/css">
P { padding: 5px 10px; }
</style>';
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
if($q = mysql_query("SELECT user_regdate, user_lastvisit, user_lastpost_time, user_last_search, user_inactive_time, user_type, user_id
	FROM ".F."users WHERE user_type IN (0,1)")){
	
	$users = $user_regdate = $user_type = $users_site_comments = array();
	
	$mounts_12 = (time()-(86400*30*12));
	$mounts_6 = (time()-(86400*30*6));
	$mounts_3 = (time()-(86400*30*3));
	$mounts_1 = (time()-(86400*30));
	
	if($q2 = mysql_query("SELECT user_id, time_modified FROM ".P."comments 
	WHERE user_id != 0 AND time_modified != 0 ORDER BY time_modified")){
		while($r=mysql_fetch_assoc($q2)){
			$users_site_comments[ $r['user_id'] ] = $r['time_modified'];// значением будет всегда самая последняя дата
		}
	}
	
	while($r=mysql_fetch_assoc($q)){
		
		$users[$r['user_id']] = $r;
		$user_regdate[] = $r['user_regdate'];
		$last_time_comment = (int)$users_site_comments[ $r['user_id'] ];
		
		if($r['user_lastvisit']<$mounts_12){
			if($r['user_type']=='1'){ $user_type['12']++; }
			if(!$r['user_lastpost_time'] && $last_time_comment<$mounts_12){ $user_lastpost_time_12++; }
			$user_rmounts_12++;
		}
		if($r['user_lastvisit']<$mounts_6){
			if($r['user_type']=='1'){ $user_type['6']++; }
			if(!$r['user_lastpost_time'] && $last_time_comment<$mounts_6){ $user_lastpost_time_6++; }
			$user_rmounts_6++;
		}
		if($r['user_lastvisit']<$mounts_3){
			if($r['user_type']=='1'){ $user_type['3']++; }
			if(!$r['user_lastpost_time'] && $last_time_comment<$mounts_3){ $user_lastpost_time_3++; }
			$user_rmounts_3++;
		}
		if($r['user_lastvisit']<$mounts_1){
			if($r['user_type']=='1'){ $user_type['1']++; }
			if(!$r['user_lastpost_time'] && $last_time_comment<$mounts_1){ $user_lastpost_time_1++; }
			$user_rmounts_1++;
		}
		
		if($r['user_last_search']<$mounts_12){ $user_last_search_12++; }
		if($r['user_last_search']<$mounts_6){ $user_last_search_6++; }
		if($r['user_last_search']<$mounts_3){ $user_last_search_3++; }
		if($r['user_last_search']<$mounts_1){ $user_last_search_1++; }
		
		if(!$r['user_last_search']){ $user_last_search_never++; }
		if(!$r['user_lastpost_time'] && !$last_time_comment){ $user_lastpost_time_never++; }
		if(!$r['user_lastpost_time'] && !$last_time_comment && $r['user_lastvisit']<$mounts_12){ $no_comments_no_visit_12_mounts++; }
	}
	
	function del_20101230($where=''){
		return ' <a href="'.$_SERVER['PHP_SELF'].'?where='.$where.'">удалить</a>';
	}
	
	echo '<p style="padding-top:15px;"><b>Информация о пользователях форума</b> с '.date('d.m.Y', @min($user_regdate)).' по '.date('d.m.Y', @max($user_regdate)).'</p>
						<table border="0" width="100%" style="padding-left:5px" cellspacing="7" id="users_table">
						    <tr>
						        <td title="период времени"><b>Время</b></td>
						        <td title="не посещающих сайт"><b>Не посещающих</b></td>
						        <td title="не оставляющих комментариев на форуме"><b>Не комментирующих</b></td>
						        <td title="не использующих поиск"><b>Не пользующихся поиском</b></td>
						        <td title="не подтвердивших регистрацию"><b>Без регистрации</b></td>
						    </tr>
						    <tr>
						        <td>более 1 года: </td>
						        <td>'.(int)$user_rmounts_12.del_20101230('user_lastvisit<='.$mounts_12).'</td>
						        <td>'.(int)$user_lastpost_time_12.del_20101230('user_lastvisit<='.$mounts_12.'ANDuser_lastpost_time=0').'</td>
						        <td>'.(int)$user_last_search_12.del_20101230('user_last_search<='.$mounts_12).'</td>
						        <td>'.(int)$user_type['12'].del_20101230('user_lastvisit<='.$mounts_12.'ANDuser_type=1').'</td>
						    </tr>
						    <tr>
						        <td>более 6 месяцев: </td>
						        <td>'.(int)$user_rmounts_6.del_20101230('user_lastvisit<='.$mounts_6).'</td>
						        <td>'.(int)$user_lastpost_time_6.del_20101230('user_lastvisit<='.$mounts_6.'ANDuser_lastpost_time=0').'</td>
						        <td>'.(int)$user_last_search_6.del_20101230('user_last_search<='.$mounts_6).'</td>
						        <td>'.(int)$user_type['6'].del_20101230('user_lastvisit<='.$mounts_6.'ANDuser_type=1').'</td>
						    </tr>
						    <tr>
						        <td>более 3 месяцев: </td>
						        <td>'.(int)$user_rmounts_3.del_20101230('user_lastvisit<='.$mounts_3).'</td>
						        <td>'.(int)$user_lastpost_time_3.del_20101230('user_lastvisit<='.$mounts_3.'ANDuser_lastpost_time=0').'</td>
						        <td>'.(int)$user_last_search_3.del_20101230('user_last_search<='.$mounts_3).'</td>
						        <td>'.(int)$user_type['3'].del_20101230('user_lastvisit<='.$mounts_3.'ANDuser_type=1').'</td>
						    </tr>
						    <tr>
						        <td>более 1 месяца: </td>
						        <td>'.(int)$user_rmounts_1.del_20101230('user_lastvisit<='.$mounts_1).'</td>
						        <td>'.(int)$user_lastpost_time_1.del_20101230('user_lastvisit<='.$mounts_1.'ANDuser_lastpost_time=0').'</td>
						        <td>'.(int)$user_last_search_1.del_20101230('user_last_search<='.$mounts_1).'</td>
						        <td>'.(int)$user_type['1'].del_20101230('user_lastvisit<='.$mounts_1.'ANDuser_type=1').'</td>
						    </tr>
						</table>
			        </td>
			    </tr>
			</table>
	<!-- <p style="padding-top:15px;"><b>Ни разу не воспользовашихся поиском</b>: '.$user_last_search_never.'</p>
	<p style="padding-top:15px;"><b>Никогда не комментировавших</b>: '.$user_lastpost_time_never.del_20101230('no_comments').'</p> -->
	<p style="padding-top:15px;" class="users_table"><b>Никогда не комментировавших на форуме и не посещающих сайт более 1 года</b>: '.$no_comments_no_visit_12_mounts.del_20101230('no_comments_no_visit_12_mounts').'</p>
	<p style="padding-top:15px;"><b>Всего пользователей</b>: '.count($users).'</p>';
}
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
if(!access('w') || !$_POST['format_time']){
	$_POST['format_time'] = time();
}

if(access('w') && $_POST['format_date']){
	$ex=explode(' ',$_POST['format_date']);// разбираем время в нормальном представлении Часы:минуты:секунды День.Месяц.Год ИЛИ День.Месяц.Год
	if($ex['1']){// если указаны Часы:минуты:секунды
		$ex_right = explode('.',$ex['0']);
		$ex_left  = explode(':',$ex['1']);
		$_POST['format_time'] = mktime($ex_left['0'], $ex_left['1'], $ex_left['2'], $ex_right['1'], $ex_right['0'], $ex_right['2']);
	}else{// не указаны Часы:минуты:секунды
		$ex_right = explode('.',$ex['0']);
		$_POST['format_time'] = mktime(0,0,0, $ex_right['1'], $ex_right['0'], $ex_right['2']);
	}
}else{
    $_POST['format_date'] = date('d.m.Y H:i:s', $_POST['format_time']);
}

echo '
	<p style="padding-top:15px;"><b>Действия</b></p>
	
	<p> - <a href="#" alt="rating_reindex" class="ajax_pages">Переиндексировать информацию о оценках страниц »</a></p>
	
	<p> - <a href="#" alt="comments_reindex" class="ajax_pages">Переиндексировать информацию о комментариях на страницах »</a></p>
	
	<p> - <a href="#" alt="parents_id_reindex" class="ajax_pages">Переиндексировать информацию о родительских разделах страниц »</a></p>
	
	<p> - <a href="#" alt="pages_fix_access_inherited" class="ajax_pages">Исправить наследуемый доступ страниц (access_inherited) »</a></p>
	
	<p> - <a href="#" alt="sunc_phpbb3_users_group" class="ajax_pages">Синхронизировать пользователей сайта и их группы, при необходимости исправить »</a></p>
	
	<p> - <a href="#" alt="delete_cache_bots" class="ajax_pages">Удалить кэш-файл ботов</a> (нужно выполнять, если внесены изменения в таблице поисковых ботов)</p>
	
	<p> - <a href="#" alt="delete_cache_table_pages" class="ajax_pages">Удалить кэш-файл полей основной таблицы</a> (нужно выполнять, если внесены структурные изменения в таблице pages)</p>
	
	<p> - <a href="#" alt="delete_old_cookies" class="ajax_pages">Удалить старых посетителей сайта и их временные данные »</a> (не пользователей)</p>
	
	<p> - <a href="#" alt="delete_301" class="ajax_pages">Удалить статистику по ошибкам 301 »</a></p>
	
	<p> - <a href="#" alt="delete_404" class="ajax_pages">Удалить статистику по ошибкам 404 »</a></p>
	
	<p> - <a href="#" alt="delete_path_ignored" class="ajax_pages">Удалить игнорируемые 301 и 404 страницы »</a></p>
	
	<fieldset><legend>&nbsp;Сброс настроек сайта (часто применяется при разработке нового проекта)&nbsp;</legend>
	
	<p> - <a href="#" alt="karma_clear" class="ajax_pages" style="color:#FF0000">Обнулить карму комментариев »</a></p>
	
	<p> - <a href="#" alt="delete_comments" class="ajax_pages">Очистить базу комментариев »</a></p>
	
	<p> - <a href="#" alt="delete_nicknames" class="ajax_pages">Очистить базу никнэймов в комментариях »</a></p>
	
	<p> - <a href="#" alt="delete_cookies" class="ajax_pages">Очистить базу cookies »</a> (пропадут закладки на страницы и оценки страниц посетителями, не пользователями)</p>
	
	<p> - <a href="#" alt="delete_pages_rating" class="ajax_pages">Очистить базу оценок страниц »</a></p>
	
	<p> - <a href="#" alt="delete_search" class="ajax_pages">Очистить базу "что ищут на сайте" »</a></p>
	
	<p> - <a href="#" alt="delete_poll" class="ajax_pages">Очистить базу опросов и голосований »</a></p>
	
	<p> - <a href="#" alt="delete_warnings_ip" class="ajax_pages">Очистить базу предупреждений IP-адресам »</a></p>
	
	<p> - <a href="#" alt="delete_mail_duplicates" class="ajax_pages">Очистить базу отправленных с сайта писем »</a></p>
	
	<p> - <a href="#" alt="delete_subscribe" class="ajax_pages">Очистить базу подписчиков на новости сайта »</a></p>
	
	<p> - <a href="#" alt="delete_subscribe_pages_comments" class="ajax_pages">Очистить базу подписчиков на комментарии к страницам »</a></p>
	
	<p> - <a href="#" alt="delete_unsubscribe" class="ajax_pages">Очистить базу отписанных от всех рассылок с сайта »</a></p>
	
	<p> - <a href="#" alt="delete_cache_css_js" class="ajax_pages">Очистить кэш CSS и JavaScript файлов »</a></p>
	
	<p> - <a href="#" alt="archive_delete" class="ajax_pages">Очистить архив изменений страниц »</a></p>
	
	<p> - <a href="#" alt="cache_delete" class="ajax_pages">Очистить общую кэш-директорию »</a> (кэши адресов страниц не удалятся)</p>
	
	<p> - <a href="#" alt="cache_url_delete" class="ajax_pages" style="color:#FF0000">Очистить кэши адресов страниц »</a> (общий кэш останется)</p>
	
	</fieldset>
	
	<p style="padding-top:15px"><b>Веб-программисту</b></p>
	<table border="0" style="padding-left:10px">
	    <tr><form action="'.$_SERVER['REQUEST_URI'].'" method="post">
	        <td align="right">дд.мм.гггг чч.мм.сс: </td>
	        <td width="270"><input name="format_date" type="text" value="'.htmlspecialchars(strip_tags($_POST['format_date'])).'" class="datepickerTimeField" style="width: 100%"></td>
	        <td><input type="submit" value="Перевести в Timeshtamp"></td>
	    </tr></form>
	    <tr><form action="'.$_SERVER['REQUEST_URI'].'" method="post">
	        <td align="right">Timeshtamp: </td>
	        <td><input name="format_time" type="text" value="'.htmlspecialchars(strip_tags($_POST['format_time'])).'" style="width: 100%"></td>
	        <td><input type="submit" value="Перевести в дд.мм.гггг чч.мм.сс"></td>
	    </tr></form>
	    <tr><form action="'.$_SERVER['REQUEST_URI'].'" method="post">
	        <td align="right">Строка: </td>
	        <td><input name="md5" type="text" value="'.((!empty($_POST['md5']))? md5($_POST['md5']) : '' ).'" style="width: 100%"></td>
	        <td><input type="submit" value="Высчитать md5 хэш"></td>
	    </tr></form>
	</table>';

?>

<?php echo Datepicker(); ?>

<style>
FIELDSET {
	border: 1px solid #CCC;
	margin: 10px;
	line-height: 1.5em;
	padding: 5px 0;
}
LEGEND {
	margin-left: 10px;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$("#users_table TR:first TD").css("cursor","help");
	
	$("#users_table A, .users_table A").click(function(){
		var path = this.href;
		jConfirm("Подтверждаете удаление?", function(a){
			if(a==true){
				$.fancybox.showActivity();
				$.post(path, {ajax_delete_users:true}, function(r){
					$.fancybox.hideActivity();
					if(!r || typeof(r)!="object"){
						jAlert("Недостаточно прав доступа");
					}else if(r.message!=""){
						jAlert(r.message);
					}
				}, "json");
			}
		});
		return false;
	});
	
	$("input:text").addClass("input_text");
	
	$(".ajax_pages").click(function(){
		$.fancybox.showActivity();
		$.post(document.location.pathname, {ajax_pages:$(this).attr("alt")}, function(r){
			$.fancybox.hideActivity();
			if(!r || typeof(r)!="object"){
				jAlert("Недостаточно прав доступа");
			}else if(r.message!=""){
				jAlert(r.message);
			}
		}, "json");
		return false;
	});
});
</script>
</body>
</html>
