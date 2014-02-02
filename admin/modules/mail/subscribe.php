<?php
include_once('../../libraries/access.php');
include_once('../../libraries/ajaxUploadFile.php');// ajax загрузка изображений
//------------------------------------------------------------------------------------------

$dir = dirname(__FILE__).'/subscribe/';
$file_from_email = $dir.'from_email.txt';
$file_from_name = $dir.'from_name.txt';
$file_subj = $dir.'subj.txt';
$file_message = $dir.'message.txt';
$file_emails = $dir.'emails.txt';
$file_email_i = $dir.'email_i.txt';

//------------------------------------------------------------------------------------------
if($_POST){
	
	$error = $status = '';
	
	if(!access('w')){ $error = 'Нет прав доступа'; }
	//-------------------------------------------------
	if($_POST['save_name']){
		
		if(!$error){
			
			$file = ${'file_'.$_POST['save_name']};// выбираю нужный файл
			
			if($file && $fp = fopen($file, 'w')){
				if(!fwrite($fp, $_POST['save_data'])){
					$error = 'Не удалось сохранить список адресов';
				}
				fclose($fp);
			}else{
				$error = 'Не получилось открыть файл '.$file;
			}
		}
		echo '{"error":"'.$error.'"}';
		exit;
	}
	//-------------------------------------------------
	if($_POST['get_emails']=='reg_users'){
		
		$emails = '';
		
		if(!$error){
			
			$sql_reg_users = 'FROM '.F.'users AS u 
			LEFT JOIN '.P.'unsubscribe AS ue ON ue.email = u.user_email
			WHERE u.user_type = 0
			AND u.user_notify = 1
			AND (ue.unsubscribe is NULL OR ue.unsubscribe = 0)
			AND (ue.email_failed is NULL OR ue.email_failed = 0)';
			//$reg_users = mysql_fetch_row(mysql_query('SELECT COUNT(*) '.$sql_reg_users));
			// 'SELECT u.user_id, u.username, u.user_password, u.user_email, u.user_regdate '.$sql_reg_users
			if($q = mysql_query('SELECT u.user_email '.$sql_reg_users)){
				while($r=mysql_fetch_assoc($q)){
					
					$emails .= $r['user_email']."\n";
					
				}
			}
		}
		echo '{"error":"'.$error.'","emails":'.ajaxHTML(mb_substr($emails,0,-1)).'}';
		exit;
	}
	//-------------------------------------------------
	if($_POST['get_emails']=='no_comments_no_visit_12_mounts'){
		
		$emails = '';
		
		if(!$error){
			
			$mounts_12 = (time()-(86400*30*12));// находим тех, кто ни разу не комментировал
			
			//$no_comments_no_visit_12_mounts = 0;
			$sql_no_comments_no_visit_12_mounts_users_in = '';
			if($q = mysql_query('SELECT user_id FROM '.P.'comments 
			WHERE user_id != 0 AND time_modified != 0')){// находим тех, кто хоть раз комментировал
				$in_del = '';
				$exclude = array();
				while($r=mysql_fetch_assoc($q)){
					if(!$exclude[ $r['user_id'] ]){
						$exclude[ $r['user_id'] ] = true;
						$in_del .= $r['user_id'].',';// отменяем удаление данных пользователей
					}
				}
				if($q = mysql_query('SELECT user_id FROM '.F.'users
				WHERE user_type IN (0,1) AND user_lastvisit < '.$mounts_12.' AND user_lastpost_time = 0
				'.($in_del? ' AND user_id NOT IN ('.substr($in_del,0,-1).')' : ''))){
					while($r = mysql_fetch_assoc($q)){
						//$no_comments_no_visit_12_mounts++;
						$sql_no_comments_no_visit_12_mounts_users_in .= $r['user_id'].',';// отменяем удаление данных пользователей
					}
					// SELECT user_id, username, user_password, user_email, user_regdate
					if($q = mysql_query('SELECT user_email 
					FROM '.F.'users 
					WHERE user_id IN ('.substr($sql_no_comments_no_visit_12_mounts_users_in,0,-1).')')){
						while($r=mysql_fetch_assoc($q)){
							
							$emails .= $r['user_email']."\n";
							
						}
					}
				}
			}
		}
		echo '{"error":"'.$error.'","emails":'.ajaxHTML(mb_substr($emails,0,-1)).'}';
		exit;
	}
	
	//-------------------------------------------------
	
	if(!$error && $_POST['email_i']=='clear'){// очищаем файл номеров
		
		if($fp = fopen($file_email_i, 'w')){
			if(!fwrite($fp, '0')){
				$error = 'Не удалось очистить файл с номерами получателя';
			}
			fclose($fp);
		}else{
			$error = 'Не получилось открыть файл '.$file_email_i;
		}
		echo '{"error":"'.$error.'"}';
		exit;
	}
	
	//-------------------------------------------------
	if(!$error && !$_POST['to_email']){ $error = 'Кому - не задано'; }
	
	if(!$error && !$_POST['from_email']){ $error = 'От e-mail адреса - не задано'; }
	
	if(!$error && !email($_POST['from_email'])){ $error = 'От e-mail адреса - задано неправильно'; }
	
	if(!$error && !$_POST['from_name']){ $error = 'От имени - не задано'; }
	
	if(!$error && !$_POST['subj']){ $error = 'Тема письма - не задано'; }
	
	if(!$error && !$_POST['message']){ $error = 'Текст письма - не задано'; }
	
	//-------------------------------------------------
	
	if(!$error && $_POST['email_i']){// сохраняем номер письма
		
		if($fp = fopen($file_email_i, 'w')){
			if(!fwrite($fp, $_POST['email_i'])){
				$error = 'Не удалось сохранить номер получателя';
			}
			fclose($fp);
		}else{
			$error = 'Не получилось открыть файл '.$file_email_i;
		}
	}
	
	//-------------------------------------------------
	
	if(!$error){
		
		$message = $_POST['message'];
		
		$files = mail_img_files($message);// находим файлы изображений
		
		$hash = '';
		
		// попытка найти пользователя в базе данных
		$user_info = @mysql_fetch_assoc(mysql_('SELECT user_id, username, user_password, user_regdate 
		FROM '.F.'users 
		WHERE user_email = '._.$_POST['to_email']._.''));
		
		if($user_info){
			
			$http_site = 'http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'];
			
			$preg_quote_host = preg_quote_($GLOBALS['SYSTEM']['config']['yapro_http_host']);
			
			$f = array();
			
			preg_match_all('/<a(.+)href=("|\'|)http:\/\/'.$preg_quote_host.'(.*)("|\'|\s|)>/sUi', $message, $f);
			
			if($f['3']['0']){
				
				foreach($f['0'] as $k => $v){
					
					$url = $f['3'][$k].$f['4'][$k];
					
					$url = $GLOBALS['SYSTEM']['users']-> access_link($user_info, $url);
					
					$message = str_replace($v, '<a'.$f['1'][$k].'href='.$f['2'][$k].$http_site.$url.' target="_blank">', $message);
				}
			}
			
			$hash = $user_info['user_id'].'.'.$user_info['user_regdate'];
			
			$unsubscribe_link = $http_site.'/outer/unsubscribe.php?hash='.$hash;
			
			$message_array = pagebreak($message);
			
			if($message_array['1']){
				$message = $message_array['0'];
				$signature = $message_array['1'];
			}else{
				$signature = '';
			}
			
			// HTML-Текст письма:
			$message = '<p>'.respect($user_info['username']).', '.htmlspecialchars($user_info['username']).'.</p>
			'.$message.'
			<hr />
			<p>Отписаться от всех рассылок с нашего сайта, можно <a href="'.$unsubscribe_link.'" targe="_blank">здесь »</a></p>'.$signature;
			
		}
		
		$http_site = 'http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'];
		
		if(mail_($_POST['from_email'], $_POST['from_name'], $_POST['to_email'], $_POST['subj'], $message, $files, false)){
			
			$status = 'отправлено';
			
		}else{
			
			$status = '<b>не отправлено</b>';
			
			if($_POST['unsubscribe'] && $user_info){
				
				if(!mysql_query('UPDATE '.F.'users SET user_notify = 0 WHERE user_id = '.$user_info['user_id'])){
					
					$error = error('1. '.__FILE__);
					
				}
				/*
				$set = 'unsubscribe = 1, uniq_hash = '._.$hash._.', time_unsubscribe = '._.time()._.'';
				
				if(!$error && !mysql_('INSERT INTO '.$unsubscribe_table.' SET email = '._.$_POST['to_email']._.', '.$set.' ON DUPLICATE KEY UPDATE '.$set)){
					
					$error = error('2. '.__FILE__);
					
				}*/
			}
		}
	}
	echo '{"error":'.ajaxHTML($error).', "status":"'.$status.'"}';
	
}
if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ exit; }

//------------------------------------------------------------------------------------------

$variants = '<select name="variant_id">
<option value="">- не выбрано -</option>
<option value="reg_users">Зарегистрированным пользователям сайта</option>
<option value="no_comments_no_visit_12_mounts">Пользователям никогда не комментироваших и не посещающих сайт больше года</option>';
if($q = mysql_query("SELECT * FROM ".P."subscribe_variants ORDER BY position")){
	
	while($r=mysql_fetch_assoc($q)){
		$variants .= '<option value="'.$r['variant_id'].'">'.htmlspecialchars($r['name']).'</option>';
	}
}
$variants .= '</select>';
$result_str = '';

//------------------------------------------------------------------------------------------

$from_email = @file_get_contents($file_from_email);
$from_name = @file_get_contents($file_from_name);
$subj = @file_get_contents($file_subj);
$message = @file_get_contents($file_message);
$emails = @file_get_contents($file_emails);
$email_i = @file_get_contents($file_email_i);

if(!$from_email){ $from_email = $GLOBALS['SYSTEM']['config']['yapro_board_contact']; }
if(!$from_name){ $from_name = 'Команда '.$GLOBALS['SYSTEM']['config']['yapro_http_host']; }
if(!$subj){ $subj = 'Новости '.$GLOBALS['SYSTEM']['config']['yapro_http_host']; }
if(!$subj){ $subj = 'Новости '.$GLOBALS['SYSTEM']['config']['yapro_http_host']; }

$str_email_i = $email_i? '<tr id="email_i" alt="'.$email_i.'"><td colspan="2"><p><b style="color:#FF0000">Внимание:</b> рассылка была прервана на '.$email_i.' человеке. Если нажать на кнопку Отправить - рассылка продолжится. Чтобы отменить продолжение прошлой рассылки, нажмите кнопку Остановить.</p></td></tr>' : '';

//------------------------------------------------------------------------------------------

$right = '<td><img src="images/logotypes/edite.gif" id="wysiwyg"></td>';

echo $_SERVER['SITE_HEADER'].Head('Рассылка','',$right).'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable" id="TableEdite">
	<tr>
        <td width="150"><p><img src="images/elements/reload.png" id="get_emails" class="actionIMG" title="Получить список"> <b>Списки:</b></p></td>
        <td><p>'.$variants.'</p></td>
    </tr>'.$str_email_i.'<tr>
        <td colspan="2"><p><img src="images/elements/save.gif" alt="emails" class="ajaxSave" id="ajaxSaveEmails"> <b>Отправить кому:</b> (E-mail адреса по одному в каждой строке)</p></td>
    </tr><tr class="no">
		<td class="NoGreyB" colspan="2" style="padding: 5px;"><textarea name="emails" style="height: 350px;">'.htmlspecialchars($emails).'</textarea></td>
	</tr><tr>
        <td width="150"><p><img src="images/elements/save.gif" alt="from_email" class="ajaxSave"> <b>От e-mail адреса:</b></p></td>
        <td><p><input name="from_email" type="text" value="'.htmlspecialchars($from_email).'"></p></td>
    </tr><tr>
        <td><p><img src="images/elements/save.gif" alt="from_name" class="ajaxSave"> <b>От имени:</b></p></td>
        <td><p><input name="from_name" type="text" value="'.htmlspecialchars($from_name).'"></p></td>
    </tr><tr>
        <td><p><img src="images/elements/save.gif" alt="subj" class="ajaxSave"> <b>Тема письма</b></p></td>
        <td><p><input name="subj" type="text" value="'.htmlspecialchars($subj).'"></p></td>
    </tr><tr>
        <td colspan="2"><p><img src="images/elements/save.gif" alt="message" class="ajaxSave"> <b>Текст письма:</b></p></td>
    </tr><tr class="no">
		<td class="NoGreyB" colspan="2" style="padding: 5px;"><textarea name="message" class="wysiwyg" style="height: 350px;">'.htmlspecialchars($message).'</textarea></td>
	</tr><tr>
        <td style="text-align: right"><input name="result" type="checkbox" checked></td>
        <td><p><label for="result"> Выводить результаты успешной / неуспешной отправки</label></p></td>
    </tr><tr>
        <td style="text-align: right"><input name="unsubscribe" type="checkbox" checked></td>
        <td><p><label for="unsubscribe"> Отписать от рассылки тех, кому не отправится письмо</label></p></td>
    </tr><!--<tr>
        <td><p><input name="sleep" type="text" value="1" style="text-align:right"></p></td>
        <td><p> Задержка в секундах между отправкой писем</p></td>
    </tr>--><tr id="report_id">
        <td colspan=2><p>
    		<input class="submit" type="button" value="Отправить" id="submit_button">
    		<input class="submit" type="button" value="Пауза" id="pause_button">
    		<input class="submit" type="button" value="Остановить" id="stop_button">
    	</p></td>
    </tr>
</table>';
?>
<style type="text/css">
TEXTAREA { width: 100%; }
.actionIMG, .ajaxSave {
	vertical-align:middle;
	cursor:pointer;
}
</style>
<script type="text/javascript">

var domain = "<?php echo $GLOBALS['SYSTEM']['config']['yapro_http_host']; ?>";// применяется при быстрой загрузке картинок

//$("#TableEdite tr").each(function(){ $("td:first", this).css("white-space", "nowrap"); });

var td = $("#TableEdite tr:first td:eq(1)")[0];

$(td).css("width", "100%").css("width", (td.offsetWidth - $(td).allPaddingsLR() )+"px");

$("input:text").addClass("input_text");// изменяем CSS-правила

$("input:checkbox").css("vertical-align","middle");// изменяем CSS-правила

$("[name=from_email]").focus();


// подгрузка списка рассылки
$("#get_emails").click(function(){
	
	var variant = $("[name=variant_id]").val();
	
	if(variant){
		
		$.fancybox.showActivity();
		
		$.post(document.location.pathname, {"get_emails":variant}, function(r){
			
			$.fancybox.hideActivity();
			
			if(r.error){
				
				jAlert(r.error);
				
			}
			if(r.emails){
				
				$("[name=emails]").val(r.emails);
				
			}
			
		},"json");
	}
});

var timerID = null;//
var stop_send = false;// отменяем возможность делать 2 одновременных запроса ( new Date().valueOf(); )
$("#submit_button").click(function(){
	
	// находим емэйлы
	var emails = $("TEXTAREA[name=emails]").val().split("\n");
	if(!emails || emails==""){
		jAlert("Не указаны E-mail адреса получателей", function(){
			$("TEXTAREA[name=emails]").focus();
		});
		return ;
	}
	
	$("#ajaxSaveEmails").trigger("click");// сохраняем список емэйл-адресов
	
	var arr_emails = new Array();// создаем архив емэйлов
	$(emails).each(function(i,l){
		arr_emails[ i ] = l.replace(" ","");// очищаем от пробелов
	});
	
	$(".status_email").remove();// удаляем строки статусов прошлой рассылки
	
	if(typeof(tinyMCE)=='object'){// сохраняем данные wysiwyg в TEXTAREA
		$("TEXTAREA.wysiwyg").each(function(){
			if( $(this).css("display")=="none" ){
				$(this).val( tinyMCE.get( $(this).attr("name") ).save() );
			}
		});
	}
	// формируем POST-запрос
	var ajax_post = {
		"from_email" : $("INPUT[name=from_email]").val(),
		"from_name" : $("INPUT[name=from_name]").val(),
		"subj" : $("INPUT[name=subj]").val(),
		"message" : $("TEXTAREA[name=message]").val(),
		"signature" : $("TEXTAREA[name=signature]").val()
	};
	
	$("#TableEdite INPUT").attr('disabled', 'disabled');// блокирую активные поля и кнопки
	$("#pause_button,#stop_button").removeAttr('disabled');// разрешаю нажимать на паузу и останавливать рассылку
	
	// определяю номер емэйл-адреса с которого нужно начать рассылку
	var arr_i = 0;
	var email_i = ~~$("#email_i").attr("alt");
	if(email_i){
		arr_i = email_i;
	}
	
	timerID = setInterval(function(){
		
		if(stop_send == false){
			
			var email = arr_emails[ arr_i ];
			
			arr_i += 1;
			
			if(email && email != "" && email != " "){
				
				stop_send = true;
				
				ajax_post.to_email = email;
				
				if($("[name=unsubscribe]").attr("checked")){
					ajax_post.unsubscribe = 1;
				}else{
					ajax_post.unsubscribe = 0;
				}
				
				ajax_post.email_i = arr_i;
				
				//l(arr_i);
				
				$.post(document.location.pathname, ajax_post, function(r){
					
					stop_send = false;
					
					if(r.error){
						
						jAlert(r.error);
						
						clearInterval(timerID);
						
						$("#TableEdite input").removeAttr('disabled');
						
					}else{
						if($("[name=result]").attr("checked")){
							$("#report_id").after('<tr class="status_email"><td colspan=2><p>'+email+' - '+r.status+'</p></td></tr>');
						}
					}
					
				},"json");
				
			}else{// отменяем рассылки
				
				clearInterval(timerID);
				
				$("#TableEdite input").removeAttr('disabled');
				
				$("#report_id").after('<tr class="status_email"><td colspan=2><p>Рассылка выполнена</p></td></tr>');
				
				email_i_clear();
			}
		}
	}, 1000);
	
});
$("#pause_button").click(function(){
	if(stop_send == false){
		stop_send = true;
		$(this).val("Продолжить");
	}else{
		stop_send = false;
		$(this).val("Пауза");
	}
});
$("#stop_button").click(function(){
	
	clearInterval(timerID);
	
	$("#TableEdite input").removeAttr('disabled');
	
	email_i_clear();
});



function email_i_clear(){// делаем пометку о том, что последняя рассылка выполнена успешно
	
	$("#email_i").remove();
	
	$.fancybox.showActivity();
	
	$.post(document.location.pathname, {"email_i":"clear"}, function(r){
		
		$.fancybox.hideActivity();
		
		if(r.error){
			
			jAlert(r.error);
			
		}
	},"json");
	
}



var stop_save_parameters = false;// отменяем возможность делать 2 одновременных запроса
$(".ajaxSave").click(function(){
	
	if(typeof(tinyMCE)=='object'){
		$("TEXTAREA.wysiwyg").each(function(){
			if( $(this).css("display")=="none" ){
				$(this).val( tinyMCE.get( $(this).attr("name") ).save() );
			}
		});
	}
	
	if(stop_save_parameters == false){
		
		var name = $(this).attr("alt");
		
		$.fancybox.showActivity();
		
		$.post(document.location.pathname, {"save_name":name, "save_data":$("[name="+name+"]").val() }, function(r){
			
			stop_save_parameters = false;
			
			$.fancybox.hideActivity();
			
			if(r.error){
				
				jAlert(r.error);
				
			}
			
		},"json");
	}
	return false;
});

wysiwygOnload();
</script>
</body>
</html>
