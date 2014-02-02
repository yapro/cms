<?php
include_once('../../libraries/access.php');
/*
group_type:
0 - По запросу
1 - Закрытая
2 - Скрытая
4 - Открытая
*/
//---------------------------------------------------------------------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_POST['new_username'])){// проверка не уникальное имя пользлователя
	
	$username = $new_username = trim($_POST['new_username']);
	$username_clean = $new_username_clean = $GLOBALS['SYSTEM']['users']-> username_clean($username);
	
	// находим пользователей у которых имена совпадает с текущим
	$a = $GLOBALS['SYSTEM']['users']-> users_by_username($username);
	
	if($a){// проверка на уже заведенное Имя пользователя
		$i = 0;
		$check = true;
		while($check){
		    if($a[ $username ] || $a[ $username_clean ]){// если Имя пользователя уже зарегистрировано в системе
			    
			    $i++;
			    
				// немного меняем имя пользователю, делая его уникальным
				$new_username = $username.$i;
				$new_username_clean = $username_clean.$i;
			    
			    if(!$a[ $new_username ] && !$a[ $new_username_clean ]){// если имя не используется
			    	$check = false;
			    }
			}else{// такое имя не используется
				$check = false;
			}
		}
	}
	if($username != $new_username || $username_clean != $new_username_clean){
		echo $new_username;
	}
	exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_POST['new_user_email'])){// проверка не уникальное мыло
	// проверка на уже заведенный E-mail
	echo $GLOBALS['SYSTEM']['users']-> user_id_by_user_email($_POST['new_user_email']);
	exit;
}
//---------------------------------------------------------------------------------------------------------------------------------------
if(access('w') && $_POST['username'] && $_POST['user_email']){// добавление ИЛИ изменение данных о пользователе
	
	$data = array();
	
	$data['user_id'] = (int)$_GET['user_id'];
	
	$data['username'] = $_POST['username'];
	
	$data['username_clean'] = $GLOBALS['SYSTEM']['users']-> username_clean($data['username']);
	
	$data['user_sex'] = (int)$_POST['user_sex'];
	
	$data['user_timezone'] = $_POST['user_timezone'];
	
	$data = $GLOBALS['SYSTEM']['users']-> format($data);
	
	$data['user_email'] = $_POST['user_email'];
	
	$data['user_rank'] = (int)$_POST['user_rank'];
	
	if($_POST['user_newpasswd']){ $data['user_password'] = $GLOBALS['SYSTEM']['users']-> hash($_POST['user_newpasswd']); }
	
	$now_user_id = $GLOBALS['SYSTEM']['user']['user_id'];
	$GLOBALS['SYSTEM']['user']['user_id'] = 0;// отменяем значение переменной
	
	if(!$data['user_id'] && $data['user_email']){// если добавление нового пользователя
		
		$data['group_id'] = $GLOBALS['SYSTEM']['users']-> group_id_reg();
		$data['user_regdate'] = time();
		$data['user_email_hash'] = $GLOBALS['SYSTEM']['users']-> email_hash($data['user_email']);
		$data['user_notify'] = 1;// Сообщать пользователю о новых сообщениях в темах, в которых он тоже участвовал
		$data['user_dateformat'] = 'D M d, Y g:i a';
		$data['user_lang'] = 'ru';
		$data['user_lastmark'] = time();//Дата времени, когда пользователь кликнул по ссылке "Отметить все форумы как прочтённые"
		$data['user_type'] = 0;
		$data['user_form_salt'] = $GLOBALS['SYSTEM']['users']-> unique_id();
		
	}
	
	$data['group_id'] = (int)$_POST['group_id_default'];
	
	if($data['user_email'] && $user_id = $GLOBALS['SYSTEM']['users']-> update($data)){
		
		if(!$data['user_id'] && $user_id){
			
			if(!mysql_query('INSERT INTO '.F.'user_group SET 
			group_id = '.$data['group_id'].', 
			user_id = '.$user_id.', 
			user_pending = 0')){
				
				echo error('SQL INSERT user_group : '.__FILE__);
				exit;
				
			}else{
				$_GET['user_id'] = $user_id;
			}
		}
		
		$_SERVER['SYSTEM']['MESSAGE'] = 'Данные сохранены';
		
	}else{
		
		error('SQL update : '.__FILE__);
		$_SERVER['SYSTEM']['MESSAGE'] = 'Ошибка обработки данных';
	}
	
	$GLOBALS['SYSTEM']['user']['user_id'] = $now_user_id;
}
//---------------------------------------------------------------------------------------------------------------------------------------

// добавляю пользователя в группу
if(access('w') && $_GET['user_id'] && is_numeric($_GET['user_id']) && $_POST['group_id'] && is_numeric($_POST['group_id'])
 && mysql_query("INSERT INTO ".F."user_group SET user_id=".$_GET['user_id'].", group_id=".$_POST['group_id']."") ){
	
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	
}
// удаляю пользователя из группы
if(access('d') && $_GET['user_id'] && is_numeric($_GET['user_id']) && $_GET['delete_group_id'] && is_numeric($_GET['delete_group_id']) 
&& mysql_query("DELETE FROM ".F."user_group WHERE user_id=".$_GET['user_id']." AND group_id=".$_GET['delete_group_id']."")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
}

//---------------------------------------------------------------------------------------------------------------------------------------

$user = $GLOBALS['SYSTEM']['users']-> data((int)$_GET['user_id'], '', 1);

//---------------------------------------------------------------------------------------------------------------------------------------

$ranks_options = '<select name="user_rank"><option value="-">- без звания -</option>';
if($q = mysql_query("SELECT rank_id, rank_title FROM ".F."ranks WHERE rank_special = 1 ORDER BY rank_title")){
	while($r = mysql_fetch_assoc($q)){
		$ranks_options .= '<option value="'.$r['rank_id'].'"'.(($r['rank_id']==$user['user_rank'])?' selected':'').'>'.htmlspecialchars($r['rank_title']).'</option>';
	}
}
$ranks_options .= '</select>';

//---------------------------------------------------------------------------------------------------------------------------------------

$groups_options = $groups_options_default = '';
if($q = mysql_query('SELECT group_id, group_type, group_name FROM '.F.'groups ORDER BY group_name')){
	while($r = mysql_fetch_assoc($q)){
		if($r['group_type']==2){// группы, в которых может быть пользователь
			$groups_options .= '<option value="'.$r['group_id'].'">'.htmlspecialchars($r['group_name']).'</option>';
		}else{
			// для выбора группы по-умолчанию
			$groups_options_default .= '<option value="'.$r['group_id'].'">'.htmlspecialchars($r['group_name']).'</option>';
		}
	}
}

//---------------------------------------------------------------------------------------------------------------------------------------

$url = $_SERVER['PHP_SELF'].'?user_id='.(int)$_GET['user_id'];
$str = '';
if($q = mysql_query("SELECT ug.group_id, g.group_name FROM ".F."user_group AS ug 
LEFT JOIN ".F."groups AS g ON g.group_id = ug.group_id
WHERE g.group_type=2 AND ug.user_id=".$_GET['user_id']." ORDER BY g.group_name")){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td>'.htmlspecialchars($r['group_name']).'</td>
			<td><a class="delete" href="'.$url.'&delete_group_id='.$r['group_id'].'">Удалить</a></td>
		</tr>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
/*
$str_fields = '';
$fields = array(
	'user_sig' => array('name'=>'Cтатус/подпись', 'type'=>'textarea'),
	'user_occ' => array('name'=>'Род занятий (профессия или хобби)', 'type'=>'input'),
	'user_interests' => array('name'=>'Интересы (о себе)', 'type'=>'textarea'),
);
$a = $GLOBALS['SYSTEM']['users']-> fields();
if($a){
	
	foreach($a as $Field => $true){
		
		
		
		$str_fields .= '<tr>
			<td>'.htmlspecialchars($Field).'</td>
			<td><a class="delete" href="'.$url.'&delete_group_id='.$r['group_id'].'">Удалить</a></td>
		</tr>';
		
		if($Field && isset($data[ $Field ])){
			
			if($true === true){
				
				$set_users .= '`'.$Field.'` = '._.$data[ $Field ]._.',';
				
			}else{
				$set_profile .= '`pf_'.$Field.'` = '._.$data[ $Field ]._.',';
			}
		}
	}
}
<form action="'.$url.'" method="post">
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1"><b>Поле</b></td>
		<td><b>Данные</b></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input class="submit" type="submit" value="Сохранить"></td>
	</tr>
	'.$str_fields.'
</table>
</form>
*/
//---------------------------------------------------------------------------------------------------------------------------------------

if(!$user['group_id']){
	$x = @mysql_fetch_row(mysql_("SELECT group_id FROM ".F."groups WHERE group_name = 'REGISTERED'"));
	$user['group_id'] = (int)$x['0'];
}

echo $_SERVER['SITE_HEADER'].Head('Данные пользователя <b style="color:#FF0000">'.htmlspecialchars($user['username']).'</b> : '.$_SERVER['SYSTEM']['MESSAGE']).'
<form action="'.$url.'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr>
			<td width="1"><b>Логин:</b></td>
			<td><input type="text" name="username" value="'.htmlspecialchars($user['username']).'" style="width:250px" alt="'.htmlspecialchars($user['username']).'"><span id="username_borrow">?</span></td>
		</tr>
		<tr>
			<td width="1"><b>Пароль:</b></td>
			<td><input type="text" name="user_newpasswd" value="'.htmlspecialchars($_POST['user_newpasswd']).'" style="width:250px"></td>
		</tr>
		<tr>
			<td width="1"><b>E-mail:</b></td>
			<td><input type="text" name="user_email" value="'.htmlspecialchars($user['user_email']).'" style="width:250px" alt="'.htmlspecialchars($user['user_email']).'"><span id="user_email_borrow">?</span></td>
		</tr>
		<tr>
			<td width="1"><b>Пол:</b></td>
			<td>
				<p>
					<label><input name="user_sex" type="radio" value="2" class="checkbox"'.(!$user['user_sex']?' checked':'').'> - безлико <span style="color:#CCCCCC;">(Здравствуйте)</span></label>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input name="user_sex" type="radio" value="1" class="checkbox"'.(($user['user_sex']==1)?' checked':'').'> - мужчина <span style="color:#CCCCCC;">(Уважаемый)</span></label>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input name="user_sex" type="radio" value="2" class="checkbox"'.(($user['user_sex']==2)?' checked':'').'> - женщина <span style="color:#CCCCCC;">(Уважаемая)</span></label>
				</p>
			</td>
		</tr>
		<tr>
			<td width="1"><b>Часовой&nbsp;пояс:</b></td>
			<td><select name="user_timezone">'.$GLOBALS['SYSTEM']['users']-> timezones('options', $user['user_timezone']).'</select></td>
		</tr>
		<tr>
			<td width="1"><b>Звание:</b></td>
			<td>'.$ranks_options.'</td>
		</tr>
		<tr>
			<td width="1"><b>Группа&nbsp;по-умолчанию:</b></td>
			<td><select name="group_id_default">'.str_replace('value="'.$user['group_id'].'"', 'value="'.$user['group_id'].'" selected', $groups_options_default).'</select></td>
		</tr>
		<tr>
			<td width="1">&nbsp;</td>
			<td><input class="submit" type="submit" value="'.($_GET['user_id']? 'Сохранить':'Добавить').'">'.($_GET['user_id']?' &nbsp; | &nbsp; <a href="'.$_SERVER['SYSTEM']['URL_PATH'].'modules/system/user_access.php?user_id='.$_GET['user_id'].'">Править административные настройки и доступ »</a>':'').'</td>
		</tr>
	</table>
</form>
'.(($_GET['user_id'] && $groups_options)? '<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>Участие в группах</b></td>
		<td width="1"><b>Действие</b></td>
	</tr>
	<form action="'.$url.'" method="post"><tr>
		<td><select name="group_id">'.$groups_options.'</select></td>
		<td><input class="submit" type="submit" value="Добавить"></td>
	</tr></form>
	'.$str.'
</table>' : '');
?>
<style type="text/css">
#username_borrow, #user_email_borrow {
	padding-left: 15px;
	color: #FF0000;
	display:none;
}

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
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$("[name=username]").focus();
	
	$("input:text").addClass("input_text");
	
	//mouseMovementsClass("overflowTable");
	
	$('.delete').click(function(){
		var a = this;
		jConfirm("Подтверждаете удаление?", function(r) {
		    if(r==true){
		    	location.href = a.href;
			}
		});
		return false;
	});
	
	var stop = false;// отменяем возможность делать 2 одновременных запроса ( new Date().valueOf(); )
	$("input[name=username]").bind("keyup change", function(){
		var alt = $(this).attr("alt");
		if(stop == false && this.value != alt){
			stop = true;
			$.post(document.location.pathname, {"new_username":this.value}, function(r){
				if(r && r != "" && r != " "){
					$("#username_borrow").html("занят, попробуйте : "+r).show();
				}else{
					$("#username_borrow").hide();
				}
				stop = false;
			});
		}
	});
	
	var stop_email = false;// отменяем возможность делать 2 одновременных запроса
	$("input[name=user_email]").bind("keyup change", function(){
		var alt = $(this).attr("alt");
		if(stop_email == false && this.value != alt){
			stop_email = true;
			$.post(document.location.pathname, {"new_user_email":this.value}, function(r){
				if(r && r != "" && r != " "){
					$("#user_email_borrow").html("уже зарегистрирован").show();
				}else{
					$("#user_email_borrow").hide();
				}
				stop_email = false;
			});
		}
	});
	/*
	$("[name=user_rank]").change(function(){
		$.get("<?php echo $url; ?>&user_rank="+this.value, function(r){
			if(r!="ajax_rank_ok"){
				jAlert("Ошибка изменения ранга");
			}
		});
	});*/
});
</script>
</body>
</html>
