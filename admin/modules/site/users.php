<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------------------------------------------

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_POST['delete_user_id'] && is_numeric($_POST['delete_user_id'])){
	
	$x = $_POST['delete_user_id'];
	
	$visitor_id = @mysql_fetch_row(mysql_('SELECT visitor_id FROM '.C.'cookies_visitors WHERE user_id ='.$x));
	if($visitor_id['0']){
		mysql_('DELETE FROM '.C.'cookies WHERE visitor_id = '.$visitor_id['0']);
	}
	
	$forum_script_path = @mysql_fetch_row(mysql_('SELECT config_value FROM '.F.'config WHERE config_name='._.'script_path'._));
	
	$file = $_SERVER['DOCUMENT_ROOT'].'/'.$forum_script_path['0'].'/cache/data_global.php';
	
	if(!$x){
		echo '{"msg":"Программная ошибка 1"}';
	}else if(!$forum_script_path['0']){
		echo '{"msg":"Программная ошибка 2"}';
	}else if(is_file($file) && !unlink($file)){
		echo '{"msg":"Программная ошибка 3"}';
	}else if(
		
	mysql_('DELETE FROM '.P.'access WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.P.'subscribe WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.P.'subscribe_pages_comments WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.P.'users_pages WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.P.'pages_rating WHERE user_id = '.$x) && 
		
	mysql_('UPDATE '.P.'poll_users SET user_id = 0 WHERE user_id = '.$x) && 
	mysql_('UPDATE '.P.'search SET user_id = 0 WHERE user_id = '.$x) && 
	mysql_('UPDATE '.P.'comments SET user_id = 0 WHERE user_id = '.$x) && 
	mysql_('UPDATE '.P.'comments_karma SET user_id = 0 WHERE user_id = '.$x) && 
	mysql_('UPDATE '.P.'pages SET comment_user_id = 0 WHERE comment_user_id = '.$x) && 
		
	mysql_('DELETE FROM '.F.'users WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.F.'user_group WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.F.'bots WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.F.'sessions WHERE session_user_id = '.$x) && 
	mysql_('DELETE FROM '.F.'sessions_keys WHERE user_id = '.$x) && 
	mysql_('DELETE FROM '.F.'profile_fields_data WHERE user_id = '.$x)
		
	){
		@mysql_query('DELETE FROM '.F.'acl_users WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'bookmarks WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'drafts WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'forums_access WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'forums_track WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'forums_watch WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'log WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'moderator_cache WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'poll_votes WHERE vote_user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'privmsgs_folder WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'privmsgs_rules WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'privmsgs_to WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'reports WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'topics_posted WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'topics_track WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'topics_watch WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'warnings WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'zebra WHERE user_id = '.$x);
		@mysql_query('DELETE FROM '.F.'topics_track WHERE user_id = '.$x);
		
		echo '{"msg":"ok"}';
	}else{
		echo '{"msg":"Программная ошибка 4"}';
	}
	exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
$listing = 3;// вид листинга
$max = 27;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);
//---------------------------------------------------------------------------------------------------------------------------------------
$where = '';
//----------------------------------------------------
$group_id = (int)$_GET['group_id'];// если указана группа, то находим пользователей которые участвуют в этой группе
if($group_id && $q = mysql_query("SELECT user_id FROM ".F."user_group WHERE group_id = ".$group_id)){
	$where_user_id = '';
	while($r = mysql_fetch_assoc($q)){
		$where_user_id .= $r['user_id'].',';
	}
	$where .= 'user_id IN ('.($where_user_id? substr($where_user_id, 0, -1) : '-1').')';
}
//----------------------------------------------------
$rank_id = (int)$_GET['rank_id'];// если указана ранг, то определяем специальный он или нет
if($rank_id){
	$r = @mysql_fetch_assoc(mysql_query("SELECT rank_min, rank_special FROM ".F."ranks WHERE rank_id = ".$rank_id));
	$where = $r['rank_special']? 'user_rank = '.$rank_id : 'user_posts > '.$r['rank_min'];
}
//----------------------------------------------------
$sql = "FROM ".F."users WHERE ".($where? $where : 'user_type IN (0,3) ');
//-------------------------------------------------------
if($_POST['username']){// если выполняется поиск по Имени пользователя
	$max = 1000;// максимум строк в таблице
	$sql .= " AND username LIKE "._.'%'.trim($_POST['username']).'%'._."";
}
if($_POST['user_email']){// если выполняется поиск по определенному email-адресу
	$max = 1000;// максимум строк в таблице
	$sql .= " AND user_email LIKE "._.'%'.trim($_POST['user_email']).'%'._."";
}
//---------------------------------------------------------------------------------------------------------------------------------------
$all = @mysql_fetch_row(mysql_("SELECT COUNT(*) ".$sql));
$count = $all['0'];// кол-во записей по запросу
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];
setOrder("Ausername");// стандартная сортировка
//print_r($_SERVER['SYSTEM']['orderby']);
$str = '';// информация о всех зарегистрированных пользователях за исключением поисковых ботов (2,3,4,5 - REGISTERED,REGISTERED_COPPA,GLOBAL_MODERATORS,ADMINISTRATORS)
if($q = mysql_('SELECT user_id, username, user_email, user_regdate, user_ip '.$sql.' '.orderBy().' LIMIT '.($folio*$max).', '.$max)){
	while($r = mysql_fetch_assoc($q)){
		
		// $forum_script_path['0'].'/memberlist.php?mode=viewprofile&u='.$r['user_id']
		
		$subdomain = @mysql_fetch_row(mysql_('SELECT user_page FROM '.P.'users_pages WHERE user_id = '._.$r['user_id']._));
		if(!$subdomain['0']){
			$subdomain['0'] = 'u'.$r['user_id'];
		}
		
		//.'/system/user.'.$r['user_id'].$GLOBALS['SYSTEM']['config']['yapro_page_ext']
		//<td><a href="http://'. $subdomain['0'].'.'.$_SERVER['HTTP_HOST_NAME'].'" target="_blank">'.$r['user_id'].'</td>
		$str .= '<tr>
			<td><a href="http://'.$_SERVER['HTTP_HOST_NAME'].'/i/'.rawurlencode($subdomain['0']).'" target="_blank">'.$r['user_id'].'</td>
			<td>'.htmlspecialchars($r['username']).'</td>
			<td><a href="mailto:'. htmlspecialchars($r['user_email']).'">'.htmlspecialchars($r['user_email']).'</a></td>
			<td>'.date('Y.m.d H:i', $r['user_regdate']).'</td>
			<td><a href="#" alt="'.$r['user_id'].'" class="delete">Удалить</a></td>
			<td>'.$r['user_ip'].'</td>
			<td><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'communications.php?user_id='.$r['user_id'].'">Править»</a></td>
			<td><a href="'.$_SERVER['SYSTEM']['URL_PATH'].'modules/system/user_access.php?user_id='.$r['user_id'].'">Править»</a></td>
		</tr>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
$url .= '?group_id='.$group_id.'&order='.$_GET['order'];// общий урл
include_once('../../libraries/listing.php');// строим листинг результата поиска
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Управление пользователями','','<td style="white-space:nowrap" title="% - для гибкого поиска">
	<form action="'.$url.'" method="post">
		<b>Имя пользователя :</b> <input name="username" value="'.htmlspecialchars($_POST['username']).'" style="width:175px"> 
		<b>E-mail :</b> <input name="user_email" value="'.htmlspecialchars($_POST['user_email']).'" style="width:175px"> 
		<input type="submit" value="Найти">
	</form>
</td>').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1"><b><a href="'.$url.getOrder('user_id').'">ID<span>&nbsp;</span></a></b></td>
		<td><b><a href="'.$url.getOrder('username').'">Имя пользователя<span>&nbsp;</span></a></b></td>
		<td><b>E-mail</b></td>
		<td width="110"><b><a href="'.$url.getOrder('user_regdate').'">Дата регистрации<span>&nbsp;</span></a></b></td>
		<td width="1"><b>Удалить</b></td>
		<td width="1"><b>IP-адрес</b></td>
		<td width="1"><b>Данные</b></td>
		<td width="1"><b>Доступ</b></td>
	</tr>
	'.$str.'
</table>'.$list;
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

mouseMovementsClass("overflowTable");

var allow_ajax_kino_editor_data = true;

$(".delete").click(function(){
	
	if( allow_ajax_kino_editor_data ){
		
		allow_ajax_kino_editor_data = false;
		
		$.fancybox.showActivity();
		
		var e = $(this).closest("TR");
		
		var id = $(this).attr("alt");
		
		$.post(document.location.pathname,{"delete_user_id": id}, function(r){
			
			allow_ajax_kino_editor_data = true;
			
			$.fancybox.hideActivity();
			
			if(r.msg=="ok"){
				$(e).remove();
			}else{
				jAlert(r.msg);
			}
		},"json");
	}
	return false;
});
</script>
</body>
</html>
