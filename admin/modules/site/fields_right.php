<?php
include_once('../../libraries/access.php');
include_once('../../libraries/cache.php');// класс работы с кэшем
include_once('../../libraries/profile_fields.php');
$forum_prefix = F;

//------------------------------------------------------------------------------------------------------------------------------
$_GET['field_id'] = (int)$_GET['field_id'];
//-----------------------------------------
$sql = "ALTER TABLE ".$forum_prefix."profile_fields_data";// начало запрос при синхронизации с полями базы страниц сайта
//-----------------------------------------
$profile_fields_colums = array();
if($q=mysql_query("SHOW COLUMNS FROM ".$forum_prefix."profile_fields_data")){// находим существующие поля
	while($r=mysql_fetch_assoc($q)){
		$ex = explode('pf_', $r['Field']);
		if($ex['1']){
			$profile_fields_colums[ $ex['1'] ] = 'pf';
		}
	}
}
//------------------------------------------
$comment = '';
$mysql_version = @mysql_result(mysql_query("SELECT VERSION()"), 0);
if($mysql_version>4 && $_POST['lang_name']){
	$comment = " COMMENT '".htmlspecialchars(trim($_POST['lang_name'].(($_POST['lang_name'] && $_POST['lang_explain'] && $_POST['lang_name']!=$_POST['lang_explain'])? ' - ' : '').$_POST['lang_explain']))."'";
}
//------------------------------------------------------------------------------------------------------------------------------
$default = $sql_type = '';

if($_POST['field_type']==1){// Число
	
	$sql_type = $_POST['type']? $_POST['type'] : 'bigint(20)';
	
}else if($_POST['field_type']==2){// однострочное текстовое (INPUT)
	
	$sql_type = ($_POST['type']? $_POST['type'] : 'TINYTEXT').' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	
}else if($_POST['field_type']==3){// многострочное текстовое (TEXTAREA)
	
	$sql_type = ($_POST['type']? $_POST['type'] : 'TEXT').' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	
}else if($_POST['field_type']==4){// Логическое поле (Да или Нет) - выбор (RADIO или CHECKBOX)
	
	$sql_type = $_POST['type']? $_POST['type'] : 'tinyint(2)';
	
}else if($_POST['field_type']==5){// выбор одного из множества (SELECT) - поле под выбранное значениЕ в OPTION-ах
	
	$sql_type = $_POST['type']? $_POST['type'] : 'mediumint(8)';
	
}else if($_POST['field_type']==6){// Дата времени d-m-Y
	
	$sql_type = ($_POST['type']? $_POST['type'] : 'varchar(10)').' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	
}
//------------------------------------------------------------------------------------------------------------------------------
if($_GET['delete_field_id'] && is_numeric($_GET['delete_field_id']) && access('d')){// удаляю
	
	$r = @mysql_fetch_assoc(mysql_query("SELECT field_id, field_name FROM ".$forum_prefix."profile_fields 
	WHERE field_id = ".$_GET['delete_field_id']));
	
	// если данное поле ранее было создано И в таблице profile_fields_data поле существует
	if($r['field_name'] && $profile_fields_colums[ $r['field_name'] ]){
		
		mysql_query($sql." DROP `pf_".$r['field_name']."`");// удаляем поле
		
		mysql_query("DELETE FROM ".$forum_prefix."profile_lang WHERE field_id = ".$r['field_id']);// удаляем данные поля
		
		mysql_query("DELETE FROM ".$forum_prefix."profile_fields WHERE field_id = ".$r['field_id']);// удаляем данные поля
		
		mysql_query("DELETE FROM ".$forum_prefix."profile_fields_lang WHERE field_id = ".$r['field_id']);// удаляем данные поля
		
		$profile_fields_colums[ $r['field_name'] ] = null;
		
		$_SERVER['SYSTEM']['MESSAGE'] .= 'Удаление выполнено';
		$reload_left_frame = true;
	}
	
}
//------------------------------------------------------------
if($_POST['field_name'] && $sql_type && access('w')){// сохраняю/добавляю
	
	$_POST['field_name'] = strtolower_(trim($_POST['field_name']));
	
	/*if($_POST['field_default_value'] && $_POST['field_type']==6){// Дата времени d-m-Y
		
		$_POST['field_default_value'] = date('d-m-Y', strtotime($_POST['field_default_value']));
		
	}*/
	
	$set_profile_fields = "SET 
		field_name = "._.$_POST['field_name']._.",
		field_type = "._.$_POST['field_type']._.",
		field_ident = "._.$_POST['field_name']._.",
		field_length = "._.trim($_POST['field_length'])._.",
		field_minlen = ".(int)$_POST['field_minlen'].",
		field_maxlen = ".(int)$_POST['field_maxlen'].",
		field_novalue = "._.$_POST['field_novalue']._.",
		field_default_value = "._.$_POST['field_default_value']._.",
		field_validation = "._.$_POST['field_validation']._.",
		field_required = ".(($_POST['field_required']=='on')?1:0).",
		field_show_on_reg = ".(int)$_POST['field_show_on_reg'].",
		field_hide = ".(int)$_POST['field_hide'].",
		field_no_view = ".(int)$_POST['field_no_view'].",
		field_active = ".(($_POST['field_active']=='on')?1:0).",
		field_order = ".(int)$_POST['field_order'].",
		field_show_profile = ".(int)$_POST['field_show_profile'].",
		field_show_on_vt = ".(int)$_POST['field_show_on_vt'];
	
	$set_profile_lang = "SET 
		lang_id = 1,
		lang_name = "._.$_POST['lang_name']._.",
		lang_explain = "._.$_POST['lang_explain']._.",
		lang_default_value = "._.$_POST['field_default_value']._;
	
	// в таблице profile_fields_data поле (c старым названием) существует
	if($_POST['field_name_old'] && $profile_fields_colums[ $_POST['field_name_old'] ] && $_GET['field_id'] && is_numeric($_GET['field_id'])){
		
		mysql_query($sql." CHANGE `pf_".trim($_POST['field_name_old'])."` `pf_".$_POST['field_name']."` ".$sql_type." NOT NULL".$comment) or die(mysql_error());
		
		mysql_("UPDATE ".$forum_prefix."profile_fields ".$set_profile_fields." "._."WHERE"._." field_id = ".$_GET['field_id']);// обновляем данные поля
		
		mysql_("UPDATE ".$forum_prefix."profile_lang ".$set_profile_lang." "._."WHERE"._." lang_id = 1 AND field_id = ".$_GET['field_id']);// обновляем данные поля
		
		$_SERVER['SYSTEM']['MESSAGE'] .= 'Данные сохранены';
		
	// если задано имя поля И в таблице profile_fields_data поле не существует //, ADD INDEX ( field_name ) 
	}else if($_POST['field_name'] && !$profile_fields_colums[ $_POST['field_name'] ]){
		
		mysql_query($sql." ADD `pf_".$_POST['field_name']."` ".$sql_type." NOT NULL".$default.$comment) or die(mysql_error());// добавляем поле
		
		$profile_fields_colums[ $_POST['field_name'] ] = 'pf';
		
		mysql_("INSERT INTO ".$forum_prefix."profile_fields ".$set_profile_fields);// добавляем данные поля
		
		$_GET['field_id'] = mysql_insert_id();
		
		mysql_("INSERT INTO ".$forum_prefix."profile_lang ".$set_profile_lang.", field_id = ".$_GET['field_id']);// добавляем данные поля
		
		$_SERVER['SYSTEM']['MESSAGE'] .= 'Поле добавлено';
	}
	
	// Логическое поле (Да или Нет) - выбор (RADIO или CHECKBOX) ИЛИ выбор одного из множества (SELECT)
	if($_POST['lang_options'] && $_POST['field_type']==4 || $_POST['field_type']==5){
		
		if($r['field_type']=='4'){// radio || checkbox
			
			$ex = array($_POST['lang_options']);
			
		}else{// select
			
			$ex = explode("\n", $_POST['lang_options']);// по одному значению на строку
			
		}
		
		if($ex){
			
			mysql_query("DELETE FROM ".$forum_prefix."profile_fields_lang WHERE field_id = ".(int)$_GET['field_id']);// удаляем данные поля
			
			$option_id = 0;
			
			foreach($ex as $v){
				
				if($v){
					
					// добавляем данные поля
					mysql_("INSERT INTO ".$forum_prefix."profile_fields_lang SET 
					field_id = "._.$_GET['field_id']._.",
					lang_id = 1,
					option_id = "._.$option_id._.",
					field_type = "._.$_POST['field_type']._.",
					lang_value = "._.trim($v)._);
					
					$option_id++;
					
					if($r['field_type']=='4' && $option_id==2){ break; }// т.к. для radio || checkbox не может быть более 2х вариантов выбора
				}
			}
		}
	}
	
	$reload_left_frame = true;
}
//------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл

$r = $profile_fields-> field_id__data($_GET['field_id']);// нахожу данные выбранного поля

$buttons[] = $url._.'images/elements/plus.gif'._.'Создать новое поле';
//------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head($_SERVER['SYSTEM']['MESSAGE']? $_SERVER['SYSTEM']['MESSAGE'] : ( ($r['field_id']?'Редактирование':'Создание').' поля (пользователю MySQL необходимы права на выполнение ALTER команд)'), $buttons).'
<form action="'.$url.($r['field_id']?'?field_id='.$_GET['field_id']:'').'" method="post" onsubmit="return CheckSubmitForm(this)">
<input name="field_show_on_vt" type="hidden" value="'.(int)$r['field_show_on_vt'].'">
<input name="field_show_profile" type="hidden" value="'.($r['field_show_profile']? (int)$r['field_show_profile'] : 1).'">
<input name="field_no_view" type="hidden" value="'.(int)$r['field_no_view'].'">
<input name="field_hide" type="hidden" value="'.(int)$r['field_hide'].'">
<input name="field_required" type="hidden" value="'.(int)$r['field_required'].'">
<input name="field_novalue" type="hidden" value="'.$r['field_novalue'].'">
<input name="field_maxlen" type="hidden" value="'.($r['field_maxlen']? (int)$r['field_maxlen'] : 0).'">
<input name="field_minlen" type="hidden" value="'.($r['field_minlen']? (int)$r['field_minlen'] : 0).'">
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr>
		<td width="250"><p><b>Название:</b></p></td>
		<td><p><input name="lang_name" type="text" value="'.htmlspecialchars($r['lang_name']).'"></p></td>
	</tr>
	<tr>
		<td width="250"><p><b>Подсказка:</b></p></td>
		<td><p><input name="lang_explain" type="text" value="'.htmlspecialchars($r['lang_explain']).'"></p></td>
	</tr>
	<tr>
		<td><p><b>Порядковый номер:</b></p></td>
		<td><p><input name="field_order" type="text" value="'.($r['field_order']? (int)$r['field_order'] : ($profile_fields-> field_order_max() + 10) ).'"></p></td>
	</tr>
	<tr id="field_active">
		<td><p><b>Показывать:</b></p></td>
		<td><p><input name="field_active" type="checkbox" '.(($r['field_active'] || !$_GET['field_id'])?'checked':'').'></p></td>
	</tr>
	<tr>
		<td><p><b>Тип поля:</b></p></td>
		<td><p><select name="field_type">
			'.str_replace('="'.$r['field_type'].'"', '="'.$r['field_type'].'" selected class="Selected"', '
			<option value="1">Число (INPUT)</option>
			<option value="2">Однострочное текстовое (INPUT)</option>
			<option value="3">Многострочное текстовое (TEXTAREA)</option>
			<option value="4">Логическое поле (RADIO или CHECKBOX)</option>
			<option value="5">Раскрывающийся список (SELECT)</option>
			<option value="6">Дата d-m-Y (INPUT)</option>
			').'
		</select></p></td>
	</tr>
	<tr id="field_name">
		<td><p><b>Имя поля (на английском):</b></p></td>
		<td><p><input name="field_name_old" type="hidden" value="'.htmlspecialchars($r['field_name']).'">
		<input spellcheck="false" name="field_name" type="text" value="'.htmlspecialchars($r['field_name']).'"></p></td>
	</tr>
	<tr id="lang_options">
		<td><p><b>Варианты по 1 на строку:</b></p></td>
		<td><p><textarea name="lang_options" style="width:100%; margin:5px 0;" rows="15">'.htmlspecialchars($r['lang_options']).'</textarea></p></td>
	</tr>
	<tr id="field_default_value">
		<td><p><b>Значение по-умолчанию:</b></p></td>
		<td><p><input name="field_default_value" type="text" value="'.htmlspecialchars($r['field_default_value']).'"></p></td>
	</tr>
	<tr id="field_required">
		<td><p><b>Обязательность заполнения:</b></p></td>
		<td><p><input name="field_required" type="checkbox" '.($r['field_required']?'checked':'').'></p></td>
	</tr>
	<tr id="field_validation">
		<td><p><b>Проверка данных:</b></p></td>
		<td><p><select name="field_validation">
				'.str_replace('="'.$r['field_validation'].'"', '="'.$r['field_validation'].'" selected class="Selected"', '
				<option value="">Не проверять</option>
				<option value="[0-9]+">Только цифры (0-9)</option>
				<option value="[\w]+">Только буквенно-цифровые</option>
				<option value="[\w_\+\. \-\[\]]+">Только буквенно-цифровые и разделители(+ - . [ ] пробел)</option>
				').'
			</select></p>
		</td>
	</tr>
	<tr id="field_length">
		<td><p><b>javascript</b></p></td>
		<td><p>javascript</p></td>
	</tr>
	<tr class="no" id="actionButtons">
		<td>&nbsp;</td>
		<td><input class="submit" type="submit" value="'.($r['field_id']?'Сохранить">'.
			'&nbsp;&nbsp;<input class="submit" type="button" value="Копировать" onclick="copyField(this)">&nbsp;&nbsp;'.
			'<a href="'.$url.'?delete_field_id='.$r['field_id'].'" onclick="return checkDel(this);" lang_name="Удалить" style="text-decoration: none; color: #FF0000;">Удалить</a' : 'Создать"').'>
		</td>
	</tr>
</table>
</form>';
?>
<style type="text/css">
.DivTableEdite { padding: 28px 0px 30px 0px; }
.NameTable TD.Image { padding-left: 7px; }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }

.checkbox {
	border: medium none;
	vertical-align: middle;
	margin: 0px;
}
/* FF2, FF3 только */
@-moz-document url-prefix(){
	.checkbox {
		margin:0 0 2px;
	}
}
/* Safari 3 и 4!, Chrome 2 */
@media screen and (-webkit-min-device-pixel-ratio:0) {
	body:first-of-type .checkbox{ vertical-align: top !important; }
}
</style>
<script type="text/javascript">
function SetFileds(){
	
	$("#field_name").hide();
	$("#lang_options").hide();
	$("#field_length").hide();
	$("#field_validation").hide();
	
	var field_type = $("[name=field_type]").val();
	
	var field_default_value = $("[name=field_default_value]")[0];
	if(field_type!='6'){
		field_default_value.value = field_default_value.title = "";
	}
	
	// Число (INPUT) || однострочное текстовое (INPUT) || многострочное текстовое (TEXTAREA)
	if(field_type=='1' || field_type=='2' || field_type=='3'){
		
		$("#field_name").show();
		$("#field_validation").show();
		$("[name=field_name]").focus();
		
		if(field_type=='3'){
			
			$("#field_length").show();
			$("#field_length B:first").text("Кол-во строк|символов TEXTAREA:");
			$("#field_length p:last").html('<input name="field_length" type="text" value="<?php echo htmlspecialchars($r['field_length']); ?>">');
			
		}else{
			
		}
	}else if(field_type=='4'){// подтверждение (CHECKBOX) || выбор (RADIO)
		
		$("#field_name").show();
		$("#lang_options").show();
		$("[name=field_name]").focus();
		
		$("#field_length").show();
		$("#field_length B:first").text("Тип данных:");
		$("#field_length p:last").html('<?php echo str_replace('="'.$r['field_length'].'"', '="'.$r['field_length'].'" checked', '<label><input type="radio" value="1" name="field_length" class="checkbox"> Переключатели (RADIO)</label> &nbsp; &nbsp; <label><input type="radio" value="2" name="field_length" class="checkbox"> Флажки (CHECKBOX)</label>'); ?>');
		
		$("[name=lang_options]").attr("rows","1")
		
	}else if(field_type=='5'){// выбор одного из множества (SELECT)
		
		$("#field_name").show();
		$("#lang_options").show();
		$("[name=field_name]").focus();
		$("[name=lang_options]").attr("rows","5")
		
	}else{// field_type==6 Дата d-m-Y (INPUT)
		$("#field_name").show();
		$("#field_validation").show();
		$("[name=field_name]").focus();
		
		if(field_default_value.value==""){
			field_default_value.value = "0- 0-   0";
		}
		field_default_value.title = "Дата времени в формате дд-мм-гггг";
	}
}

var current_parent_id = <?php echo (int)$r['parent_id']; ?>;

$(document).ready(function(){
	
	$("input:text").addClass("input_text");
	
	$(".overflowTable TR").each(function(){ $("TD:first", this).css("text-align","right"); });
	
	$("[name=field_type]").change(function(){ SetFileds(); });
	
	SetFileds();
	
	$("[name=lang_name]").focus();
	
	<?php if($reload_left_frame){ echo 'reload_left_frame();'; } ?>
	
});

// используемые имена в формах (массивы данных)
<?php echo $profile_fields-> fields_names(); ?>

// копируем поле
function copyField(button){
	var form = $(button).closest("FORM")[0];
	if(CheckSubmitForm(form,'copy')==true){
		form.action = form.action.replace("field_id", "copy");
		$('[name=field_name_old]').remove();
		form.submit();
	}
}

// проверка заполнения формы
function CheckSubmitForm(form, copy){
	
	if(form.lang_name.value == ""){// проверка на ввод lang_name
		jAlert("Вы не указали Название", function(){
			$(form.lang_name).focus().Attention();
		});
		return false;
	}
	
	var field_name = $("[name=field_name]").val();
	
	// проверка на ввод name
	if(field_name==''){
		jAlert("Вы не указали Имя поля", function(){
			$(form.field_name).focus().Attention();
		});
		return false;
	}
	if(field_name.match(/^[0-9]+$/i)){
		jAlert("Имя поля должно содержать буквы", function(){
			$(form.field_name).focus().Attention();
		});
		return false;
	}
	
	// хотя бы одна цифра в строке
	if(field_name.match(/[0-9]+/i)){// в случае успеха - возвращает true, в случае если совпадение не найдено - null
		jAlert("Цифры не допустимы в Имени поля", function(){
			$(form.field_name).focus().Attention();
		});
		return false;
	}
	
	// проверка на идентичное имя в выбранной форме
	if(field_name!=''){
		
		var current_name = '<?php echo $r['field_name']; ?>';
		
		if(!copy && field_name==current_name){// если сохранение поля
			
			// выполняется обычное сохранение поля, без изменений
			
		}else{// если изменение/добавление/копирование поля
			
			if(fields_names && fields_names[ field_name ]){// если имя уже используется
				
				jAlert("Измените текущее имя поля, т.к. оно уже используется у одного из элементов выбранного родителя.", function(){
					
					$(form.field_name).focus().Attention();
					
				}, '400px');
				
				return false;
				
			}else{
				// имя поля еще не используется - разрешаем сохранить
			}
		}
	}
	// в конечном итоге мы должны удалить все скрытые поля, что бы данные этих полей не попадали в базу данных
	$("tr", form).each(function(){
		if($(this).css("display")=="none"){
			$(this).remove();
		}
	});
	return true;
}
</script>
</body>
</html>
