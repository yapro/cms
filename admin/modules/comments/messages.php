<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю
$withOutSaveSelectedTime = true;// отключаю запоминание запоминания времени, ведь используется Ajax
include_once('../../libraries/times.php');// библиотека для работы с выбором времени
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');// класс работы с кэшем
if(!F){ echo 'Не указан префикс таблиц форума'; exit; }
//---------------------------------------------------------------------------------------------------------------------------------------
// обновляем кэшируемые данные
function admin_comments_cache_update(){
	
	if( ($_POST['comment'] && $_POST['comment_id'] && is_numeric($_POST['comment_id']) && access('w')) 
	|| ($_POST['delete_comment_id'] && is_numeric($_POST['delete_comment_id']) && access('d')) ){
		
		$comment_id = $_POST['comment_id']? $_POST['comment_id'] : $_POST['delete_comment_id'];
		
		$page_id = @mysql_fetch_row(mysql_query("SELECT page_id FROM ".P."comments WHERE comment_id = ".$comment_id));
		
		if($page_id['0']){
			
			//yapro_page_cache($page_id['0']);
			$GLOBALS['system_cache']-> update('comments');
			$GLOBALS['system_cache']-> update('comments_'.$page_id['0']);
		}
	}
}
//----------------------------------------------------
// рекурсивное удаление сообщения и дочерних сообщений
if(access('d') && $_POST['delete_comment_id'] && is_numeric($_POST['delete_comment_id'])){
	$delete_in = $_POST['delete_comment_id'].',';
	$parent_id_in = $_POST['delete_comment_id'].',';
	while($parent_id_in){
		if($q = mysql_('SELECT comment_id FROM '.P.'comments WHERE parent_id IN ('.substr($parent_id_in, 0, -1).')')){
			$parent_id_in = '';
			while($r = mysql_fetch_assoc($q)){
				$delete_in .= $r['comment_id'].',';
				$parent_id_in .= $r['comment_id'].',';
			}
		}
	}
	
	// узнаем ИД страницы на которой размещен коммент чтобы ниже его удалить
	$page_id = @mysql_fetch_row(mysql_('SELECT page_id FROM '.P.'comments WHERE comment_id = '.$_POST['delete_comment_id']));
	
	if($delete_in && mysql_('DELETE FROM '.P.'comments WHERE comment_id IN ('.substr($delete_in, 0, -1).')') && pages_comments_reindex()){
		
		echo 'ok';
		admin_comments_cache_update();
		@unlink($_SERVER['DOCUMENT_ROOT'].'/js/comments/new/'.$page_id['0']);
		@unlink($_SERVER['DOCUMENT_ROOT'].'/js/comments/new/latest_comment');
		write_('0', $_SERVER['DOCUMENT_ROOT'].'/js/comments/new/latest');
		
	}else{
		echo 'error';
	}
}
if(access('w')){
	// сохраняю
	if($_POST['comment'] && $_POST['comment_id']){
		if(mysql_("UPDATE ".P."comments SET 
			comment="._.($_COOKIE['commentsWysiwyg']? str_replace('<br />', "\n", $_POST['comment']) : $_POST['comment'])._.", 
			time_modified="._.time()._." 
		"._."WHERE"._." 
			comment_id="._.$_POST['comment_id']._."")){
			
			echo 'ok';
			admin_comments_cache_update();
			
		}else{
			echo 'error';
		}
	}
	// IP забанить
	if($_POST['ban_user_ip'] && is_numeric($_POST['ban_user_ip'])){
		if(mysql_query("INSERT INTO ".F."banlist SET ban_ip = '".long2ip($_POST['ban_user_ip'])."'")){
			
			echo 'ok';
			admin_comments_cache_update();
			
		}else{
			echo 'error';
		}
	}
	// IP разбанить
	if($_POST['del_ban_user_ip'] && is_numeric($_POST['del_ban_user_ip'])){
		if(mysql_query("DELETE FROM ".F."banlist WHERE ban_ip = '".long2ip($_POST['del_ban_user_ip'])."'")){
			
			echo 'ok';
			admin_comments_cache_update();
			
		}else{
			echo 'error';
		}
	}
	// USER_ID забанить
	if($_POST['ban_user_id'] && is_numeric($_POST['ban_user_id'])){
		if(mysql_query("INSERT INTO ".F."banlist SET ban_userid = '".$_POST['ban_user_id']."'")){
			
			echo 'ok';
			admin_comments_cache_update();
			
		}else{
			echo 'error';
		}
	}
	// USER_ID разбанить
	if($_POST['del_ban_user_id'] && is_numeric($_POST['del_ban_user_id'])){
		if(mysql_query("DELETE FROM ".F."banlist WHERE ban_userid = '".$_POST['del_ban_user_id']."'")){
			
			echo 'ok';
			admin_comments_cache_update();
			
		}else{
			echo 'error';
		}
	}
	// USER_ID вынести предупреждение
	if($_POST['warning_user_id'] && is_numeric($_POST['warning_user_id'])){
		if(mysql_query("UPDATE ".F."users SET user_warnings = (user_warnings + 1), user_last_warning = '".time()."' 
		WHERE user_id = '".$_POST['warning_user_id']."'")){
			
			echo 'ok';
			admin_comments_cache_update();
			
		}else{
			echo 'error';
		}
	}
	
	// IP вынести предупреждение
	if($_POST['warning_ip'] && is_numeric($_POST['warning_ip'])){
		$ip = @mysql_fetch_row(mysql_query("SELECT ip FROM ".W."warnings_ip WHERE ip = '".$_POST['warning_ip']."'"));
		if($ip['0']){// уже выносили предупреждение
			if(mysql_query("UPDATE ".W."warnings_ip SET warnings = (warnings + 1) WHERE ip = '".$ip['0']."'")){
				
				echo 'ok';
				admin_comments_cache_update();
				
			}else{
				echo 'error';
			}
		}else{// еще не выносили предупреждение
			if(mysql_query("INSERT INTO ".W."warnings_ip SET ip = '".$_POST['warning_ip']."', warnings = 1")){
				
				echo 'ok';
				admin_comments_cache_update();
				
			}else{
				echo 'error';
			}
		}
	}
}
// если метод Ajax - информация уже выдана через echo
if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
	if(!access('w') && !access('d')){
		echo 'Нет прав доступа.';
	}
	exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['REQUEST_URI'];// общий урл
$url .= '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------------------------------------------
// собираем в массив информацию о забаненных IP и пользователях
$ban_id = $ban_ip = array();
if(F && $q = mysql_query("SELECT ban_userid, INET_ATON(ban_ip) as ban_ip FROM ".F."banlist 
	WHERE ( ban_start < '".time()."' OR ban_start = 0) AND (ban_end > '".time()."' OR ban_end = 0) ")){
	while($r=mysql_fetch_assoc($q)){
		if($r['ban_userid']){ $ban_id[ $r['ban_userid'] ] = true; }
		if($r['ban_ip']){ $ban_ip[ $r['ban_ip'] ] = true; }
	}
}
$ban_ip_js = $ban_id_js = '';
if($ban_ip){
	foreach($ban_ip as $ip => $true){
		$ban_ip_js .= $ip.':true,';
	}
}
if($ban_id){
	foreach($ban_id as $id => $true){
		$ban_id_js .= $id.':true,';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
$where = '';
if($_POST['name']){// попытка найти комментарии по имени комментировавшего
	
	if($_POST['name']=='Гость'){
		
		$where .= ' AND c.nickname_id = 0 AND c.user_id = 0';
		
	}else{
		
		$r = @mysql_fetch_assoc(mysql_("SELECT id FROM ".N."nicknames WHERE nickname = "._.$_POST['name']._.""));
		
		$where .= ' AND (c.nickname_id = '.$r['id'].' OR u.username_clean = LOWER('._.$_POST['name']._.') )';
	}
}
if($_POST['user_id']){// попытка найти комментарии по user_id комментировавшего
	
	$where .= ' AND c.user_id = '._.(int)$_POST['user_id']._.'';
	
}
if($_POST['ip']){// попытка найти комментарии по ip комментировавшего
	
	$where .= ' AND c.user_ip = INET_ATON('._.$_POST['ip']._.')';
	
}
if($_POST['url']){// попытка найти комментарии по ip комментировавшего
	
	$ex = explode('#',$_POST['url']);
	
	$e = explode('/s/p?',$ex['0']);
	
	if($e['1'] && is_numeric($e['1'])){
		
		$r['page_id'] = $e['1'];
		
	}else{
		
		preg_match_all('/http(s|):\/\/([-a-z0-9]+\.[a-z0-9\.\:]+)\/(.*)/i', $ex['0'], $found);
		
		$_POST['url'] = $found['2']['0']? '/'.$found['3']['0'] : $ex['0'];
		
		$ext_length = mb_srtlen($GLOBALS['SYSTEM']['config']['yapro_page_ext']);
		
		$r = @mysql_fetch_assoc(mysql_('SELECT page_id FROM '.P.'pages_url WHERE 
		url = '._.mb_substr($_POST['url'],0,-$ext_length)._.""));
		
	}
	
	$where .= ' AND c.page_id = '.(int)$r['page_id'];
	
}
if($_POST['text']){
	
	$where .= ' AND c.comment LIKE '._.'%'.$_POST['text'].'%'._.'';
	
}
//---------------------------------------------------------------------------------------------------------------------------------------
$warnings_ip_sql = '';// строка для SQL-запроса
$warnings_user = array();
// time_modified=0 - означает что комментарий размещен скрытым и ожидает проверки модератором
if($q = mysql_('SELECT c.*, p.page_id, n.nickname, u.username, u.user_warnings FROM '.P.'comments AS c 
LEFT JOIN '.P.'pages AS p ON p.page_id = c.page_id 
LEFT JOIN '.N.'nicknames AS n ON n.id = c.nickname_id 
LEFT JOIN '.F.'users AS u ON u.user_id =  c.user_id 
WHERE (c.time_created BETWEEN '.$min.' AND '.$max.' OR c.time_modified BETWEEN '.$min.' AND '.$max.' OR c.time_modified=0) '.$where.' 
ORDER BY c.time_modified DESC')){
	
	while($r = mysql_fetch_assoc($q)){
		
		$r['comment'] = $_COOKIE['commentsWysiwyg']? nl2br($r['comment']) : $r['comment'];
		
		if($r['user_id'] && $r['username']){
			$name = $r['username'];
		}else if($r['nickname_id'] && $r['nickname']){
			$name = $r['nickname'];
		}else{
			$name = 'Гость';
		}
		
		$ip = long2ip($r['user_ip']);
		$user_id = (int)$r['user_id'];
		
		$warnings_ip_sql .= $r['user_ip'].',';
		if($r['user_warnings']){ $warnings_user[ $user_id ] = $r['user_warnings']; }
		
		$page = url($r['page_id']);
		$page = $page? $page : '/s/p?'.$r['page_id'];
		
		$page = $r['page_id']? '<input spellcheck="false" type="text" value="'.htmlspecialchars(urldecode($page)).'" class="page_id A" alt="'.$r['comment_id'].'">' : 'удалена';
		
		$str .= '<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable comment_'.$r['comment_id'].'">
		<tr>
			<td width="100" class="user_id" style="overflow: hidden;" alt="'.$user_id.'">
				<input spellcheck="false" type="text" value="'.htmlspecialchars($name).'" class="name A">
			</td>
			<td width="110">
				<p class="user_ip cursor A">'.$ip.'<input type="hidden" name="user_ip['.$r['comment_id'].']" value="'.$r['user_ip'].'"></p>
			</td>
			<td width="110"><p>'.date("d.m.Y H:i", $r['time_modified']).'</p></td>
			<td width="50"><p>'.$r['comment_id'].'</p></td>
			<td colspan="2" style="overflow:hidden">'.$page.'</td>
		</tr>
	</table>
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable comment_'.$r['comment_id'].'">
		<tr class="no">
			<td colspan="5" class="Comment"><div><textarea alt="'.$r['comment_id'].'">'.htmlspecialchars($r['comment']).'</textarea></div></td>
			<td width="125" class="actions" alt="'.$r['comment_id'].'.'.$r['user_ip'].'.'.$user_id.'.'.(int)$warnings_user[ $user_id ].'" valign="top"></td>
		</tr>
	</table>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
// собираем в массив информацию о предупреждениях IP
$warnings_ip_js = '';
if($warnings_ip_sql && $q = mysql_query("SELECT ip, warnings FROM ".W."warnings_ip WHERE ip IN (".substr($warnings_ip_sql,0,-1).")")){
	while($r=mysql_fetch_assoc($q)){
		$warnings_ip_js .= $r['ip'].':'.$r['warnings'].',';
	}
}
$warnings_user_js = '';
if($warnings_user){
	foreach($warnings_user as $user_id => $count){
		$warnings_user_js .= $user_id.':'.(int)$count.',';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Комментарии на сайте (отредактированные комментарии сохраняются автоматически)','','','http://yapro.ru/documents/video/rabota-s-kommentariyami-banlistom-i-smaylami.html').'
<style type="text/css">
.NameTable { position:fixed; z-index:1; top:0px }
#commentsFinder { position:fixed; z-index:1; top:27px }
.fieldsNamesTable { position:fixed; z-index:1; top:131px }
.commentsTable { display:block; height:1px; margin-top:157px }
.overflowTable td.Comment { padding:0px }
TEXTAREA { height: 170px; padding:5px; border:0px }
.name, .page_id { border:0px; width:100%; cursor:pointer }
.actions A { display:block; padding: 5px 0 }
</style>
<form action="'.$url.'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable" id="commentsFinder">
		<tr>
			<td style="text-align:right">
				<b>Имя:</b> 
			</td><td>
				<input name="name" type="text" class="input_text" style="width:150px" value="'.htmlspecialchars($_POST['name']).'">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b>IP-адрес:</b>&nbsp;
				<input name="ip" type="text" class="input_text" style="width:150px" value="'.htmlspecialchars($_POST['ip']).'">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b>ID пользователя:</b>&nbsp;
				<input name="user_id" type="text" class="input_text" style="width:50px" value="'.htmlspecialchars($_POST['user_id']).'">
			</td>
		</tr><tr>
			<td style="text-align:right"><b>URI cтраницы</b></td>
			<td><input name="url" type="text" class="input_text" value="'.htmlspecialchars($_POST['url']).'" ondblclick="this.select()"></td>
		</tr><tr>
			<td width="105"><b>Текст сообщения</b></td>
			<td><input name="text" type="text" class="input_text" value="'.htmlspecialchars($_POST['text']).'" ondblclick="this.select()"></td>
		</tr><tr>
			<td style="text-align:right"><b>Дата</b></td>
			<td>'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form>
'.($str?'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable fieldsNamesTable">
	<tr class="yaproSortTR">
		<td width="100"><b>Имя</b></td>
		<td width="110"><b>IP-адрес</b></td>
		<td width="110"><b>Дата</b></td>
		<td width="50"><b>ID</b></td>
		<td><b>Страница</b></td>
		<td width="125"><b>Действия</b></td>
	</tr>
</table>
<div class="commentsTable"></div>
'.$str.'
':'<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<script type="text/javascript">

var t = $("TEXTAREA:first")[0];
var td = firstParent(t, "TD");
$("TEXTAREA").each(function(){ $(this).css("width", (td.offsetWidth - 10)+"px"); });

$(document).ready(function(){
	
	$(".user_id INPUT, .page_id").attr("readonly","readonly");
	
	$(":checkbox").css("vertical-align", "middle");
	
	$(".user_id").attr("title","Информация о пользователе").click(function(){
		var user_id = $(this).attr("alt");
		if(user_id && user_id != '0'){
			$("[name=name]").val("");
			$("[name=ip]").val("");
			$("[name=user_id]").val( user_id ).focus();
		}else{
			$("[name=name]").val( $("input:first", this).val() ).focus();
			$("[name=ip]").val("");
			$("[name=user_id]").val("");
		}
	}).dblclick(function(){
		$("[name=name]").val("");
		$("[name=user_id]").val("");
		var user_id = $(this).attr("alt");
		if(user_id && user_id!="0"){
			window.open(openBrowserSite+"/system/user."+user_id+"<?php echo $GLOBALS['SYSTEM']['config']['yapro_page_ext']; ?>");
		}
	});
	
	$(".user_ip").attr("title","Информация о IP").click(function(){
		$("[name=ip]").val( $(this).text() ).focus();
	}).dblclick(function(){
		$("[name=ip]").val("");
		window.open("https://www.nic.ru/whois/?query="+$(this).text());
	});
	
	$(".page_id").attr("title","Просмотр страницы").click(function(){
		$("[name=url]").val(this.value).focus();
	}).dblclick(function(){
		$("[name=url]").val("");
		window.open(openBrowserSite+this.value+'#comment_'+this.alt);
	});
	
	$("textarea").each(function(){// т.к. $("textarea").change(function(){ в IE срабатывает не каждый раз, пришлось написать через this.onchange
		this.onchange = function(){
			AjaxWindowLoad();
			$.post(document.location.href, {comment:$(this).val(),comment_id: $(this).attr("alt")}, function(r){
				AjaxWindowLoad(1);
				if(r && r!="ok"){
					jAlert(r);
				}
			});
		}
		/*
		alert(1);
		
		*/
	});
	actions();
});



var ban_ip = {<?php echo substr($ban_ip_js,0,-1); ?>};
var ban_id = {<?php echo substr($ban_id_js,0,-1); ?>};
var warnings_ip = {<?php echo substr($warnings_ip_js,0,-1); ?>};
var warnings_user = {<?php echo substr($warnings_user_js,0,-1); ?>};

function actionsError(r){
	AjaxWindowLoad(1);
	jAlert(r);
}
function actions(){
	$(".actions").each(function(){
		var td = this;
		$(this).html("");
		var data = $(this).attr("alt").split(".");// comment_id.user_ip.user_id
		var user_ip = (data[1] && data[1]!="0")? data[1] : null;
		var user_id = (data[2] && data[2]!="0")? data[2] : null;
		var del_a = $('<a href="#">удалить</a>').click(function(){
			AjaxWindowLoad();
			$.post(document.location.href, {delete_comment_id:data[0]}, function(r){
				if(r=="ok"){
					$('.comment_'+data[0]).remove();
					AjaxWindowLoad(1);
				}else{
					actionsError(r);
				}
			});
			return false;
		});
		if(user_id){
			var v = warnings_user[ user_id ]? warnings_user[ user_id ] : 0;
			var warning_user_a = $('<a href="#">вынести предупреждение пользователю ('+v+')</a>').click(function(){
				AjaxWindowLoad();
				$.post(document.location.href, {warning_user_id:user_id}, function(r){
					if(r=="ok"){
						warnings_user[ user_id ] = (warnings_user[ user_id ]? warnings_user[ user_id ] : 0) + 1;
						actions();
					}else{
						actionsError(r);
					}
				});
				return false;
			});
		}else{
			var warning_user_a = null;
		}
		var v = warnings_ip[ user_ip ]? warnings_ip[ user_ip ] : 0;
		var warning_ip_a = $('<a href="#">вынести предупреждение IP-адресу ('+v+')</a>').click(function(){
			AjaxWindowLoad();
			$.post(document.location.href, {warning_ip:user_ip}, function(r){
				if(r=="ok"){
					warnings_ip[ user_ip ] = (warnings_ip[ user_ip ]? warnings_ip[ user_ip ] : 0) + 1;
					actions();
				}else{
					actionsError(r);
				}
			});
			return false;
		});
		if(ban_ip[ user_ip ]){
			var ban_ip_a = $('<a href="#">разбанить IP-адрес</a>').click(function(){
				AjaxWindowLoad();
				$.post(document.location.href, {del_ban_user_ip:user_ip}, function(r){
					if(r=="ok"){
						ban_ip[ user_ip ] = null;
						actions();
					}else{
						actionsError(r);
					}
				});
				return false;
			});
		}else{
			var ban_ip_a = $('<a href="#">забанить IP-адрес</a>').click(function(){
				AjaxWindowLoad();
				$.post(document.location.href, {ban_user_ip:user_ip}, function(r){
					if(r=="ok"){
						ban_ip[ user_ip ] = true;
						actions();
					}else{
						actionsError(r);
					}
				});
				return false;
			});
		}
		if(user_id){
			if(ban_id[ user_id ]){
				var ban_id_a = $('<a href="#">разбанить пользователя</a>').click(function(){
					AjaxWindowLoad();
					$.post(document.location.href, {del_ban_user_id:user_id}, function(r){
						if(r=="ok"){
							ban_id[ user_id ] = null;
							actions();
						}else{
							actionsError(r);
						}
					});
					return false;
				});
			}else{
				var ban_id_a = $('<a href="#">забанить пользователя</a>').click(function(){
					AjaxWindowLoad();
					$.post(document.location.href, {ban_user_id:user_id}, function(r){
						if(r=="ok"){
							ban_id[ user_id ] = true;
							actions();
						}else{
							actionsError(r);
						}
					});
					return false;
				});
			}
		}else{
			var ban_id_a = null;
		}
		$(this).append(del_a).append(warning_ip_a).append(warning_user_a).append(ban_ip_a).append(ban_id_a);
	});
	AjaxWindowLoad(1);
}
</script>
</body>
</html>
