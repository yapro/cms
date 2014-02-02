<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------------------------------------------
$settings = array(
	'script_path' => 'Корневая директория форума. Например: /forum Без форума: /uploads',
	'avatar_path' => 'Дочерняя от форума директория под загрузку аватар. Например: /images/avatars Без форума: /avatars',
	'avatar_gallery_path' => 'Дочерняя от форума директория для директорий аватар-галерей (без форума - не используется)',
	'avatar_filesize' => 'Максимальный размер файла аватары (байты)',
	'avatar_max_height' => 'Максимальная высота аватары (px)',
	'avatar_max_width' => 'Максимальная ширина аватары (px)',
	'avatar_min_height' => 'Минимальная высота аватары (px)',
	'avatar_min_width' => 'Минимальная ширина аватары (px)',
	//'board_contact' => 'Результат обработки ошибок посылать на Email',
	//'gzip_compress' => 'Включить сжатие страниц с помощью GZip',
	'require_activation' => 'Вид регистрация пользователя: 0 - без подтверждения E-mail, 1 - обратной ссылкой в письме, 2 - после проверки модератором',
	//'email_check_mx' => 'Функции. mail_ - проверять MX-записи Email-адресов',
	'avatar_salt' => 'Хэш загруженных аватар',
	'<b>Не обязательные настройки (при использовании форума):</b>' => '',
	'board_disable' => 'Отключить форум (станет недоступным для посетителей). 1 - отключить, 0 - включить',
	'server_name' => 'Имя домена, на котором работает форум',
	'sitename' => 'Название форума',
	'site_desc' => 'Описание конференции',
	'*<i>Остальные настройки форума изменяйте в админке форума</i>' => ''
);
//---------------------------------------------------------------------------------------------------------------------------------------
$config = array();
if(F && $q = mysql_query("SELECT * FROM ".F."config")){
	while($r = mysql_fetch_assoc($q)){
		$config[ $r['config_name'] ] = $r;
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
if(access('w') && $_POST && $settings && $config){
	
	// сохраняю разрешенные к сохранению настройки форума phpbb
	foreach($_POST as $field => $value){
		
		if($field && $settings[ $field ] && $config[ $field ]){
			
			$config[ $field ]['config_value'] = $value;// обновляем данные в массиве конфигурации
			
			// если вначале слэш
			if( ($field == 'avatar_gallery_path') && substr($value, 0, 1)=='/'){ $value = substr($value, 1); }
			
			// если слева в окончании
			if( ($field == 'avatar_path' || $field == 'avatar_gallery_path' || $field == 'script_path') && substr($value, -1)=='/'){ $value = substr($value, 0, -1); }
			
			// если поле должно быть простым числом
			if($field == 'avatar_filesize' || $field == 'avatar_max_height' || $field == 'avatar_max_width' || $field == 'avatar_min_height' || $field == 'avatar_min_width'){ $value = (int)$value; }
			
			mysql_("UPDATE ".F."config 
			SET config_value="._.$value._." "._."WHERE"._." config_name="._.$field._."");
			
		}
	}
	//include_once($_SERVER['DOCUMENT_ROOT'].'/inner/Functions/system_cache.php');// класс работы с кэшем
	//$GLOBALS['system_cache']->update( $GLOBALS['system_cache']-> file_($_SERVER['DOCUMENT_ROOT'].'/index.php') );
	echo 'saved';
}
if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ exit; }
//---------------------------------------------------------------------------------------------------------------------------------------

$str = '';

// создаю строки полей настроек
if($settings && $config){
	foreach($settings as $config_name => $title){
		if($title){
			$str .= '<tr>
				<td title="'.$config_name.'">'.$title.'</td>
				<td><input name="'.$config_name.'" type="text" value="'.htmlspecialchars($config[ $config_name ]['config_value']).'" maxlength="255" spellcheck="false"></td>
			</tr>';
		}else{
			$str .= '<tr>
				<td colspan="2">'.$config_name.'</td>
			</tr>';
		}
	}
}
//--------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Настройки').'<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="myForm">
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="70%"><b>Описание</b></td>
		<td width="30%"><b>Значение</b></td>
	</tr>'.$str.'
	<tr><td colspan="2"><p style="text-align: center;"><input class="button" type="submit" value="Сохранить"></p></td></tr>
</table></form>';
?>
<style type="text/css">
.NameTable { position:fixed; z-index:1; top:0px }
.overflowTable { margin-top:28px }
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$(".overflowTable tr").each(function(){
		$("td:first", this).dblclick(function(){
			$(this).text( $(this).attr("title") );
		});
	});
	
	$("input:text").addClass("input_text");
	
	mouseMovementsClass("overflowTable");
	
	<?php if($_SERVER['SYSTEM']['MESSAGE']){ echo 'if(window.parent.left_frame){ window.parent.left_frame.location.reload(); }'; } ?>
	
	$("#myForm").submit(function(){
		$.post(this.action, formdata(this), function(r){
			if(!r || r==""){
				
				jAlert("Возникла ошибка сохранения данных");
				
			}else if(r=="saved"){
				
				var e = $("td.Name");
				var et = $(e).text();
				$(e).text("Данные успешно сохранены").typewriter(et);
				
			}else{
				jAlert(r);
			}
		});
		return false;
	});
});
</script>
</body>
</html>
