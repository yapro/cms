<?php
include_once('../../libraries/access.php');
include_once('../../libraries/cache.php');// класс работы с кэшем
include_once('../../libraries/fields.php');
//------------------------------------------------------------------------------------------------------------------------------
$_GET['field_id'] = (int)$_GET['field_id'];

$page_table = P."pages";// таблица страниц сайта

$sql = "ALTER TABLE ".$page_table;// начало запрос при синхронизации с полями базы страниц сайта

$fields->page_fields = array();
if($q=mysql_query("SHOW COLUMNS FROM ".$page_table)){// находим существующие поля $page_table
	while($r=mysql_fetch_assoc($q)){
		$fields->page_fields[ $r['Field'] ] = $r;
	}
}

//------------------------------------------------------------------------------------------------------------------------------
$comment = '';
$mysql_version = @mysql_result(mysql_query("SELECT VERSION()"), 0);
if($mysql_version>4 && $_POST['title']){
	$comment = ' COMMENT '._.htmlspecialchars(trim($_POST['title']))._;
}
//------------------------------------------------------------------------------------------------------------------------------
function f20120102($default=''){
	
	if($_POST['field_type'] && (
		$_POST['field_type']=='tinyint(1) unsigned' || 
		$_POST['field_type']=='tinyint(3) unsigned' || 
		$_POST['field_type']=='smallint(5) unsigned' || 
		$_POST['field_type']=='mediumint(8) unsigned' || 
		$_POST['field_type']=='int(10) unsigned'
	)){
		return ' '.$_POST['field_type'];
	}else{
		return ($_POST['field_type']? $_POST['field_type'] : $default).' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	}
}

$default = '';

if($_POST['type']==1){// однострочное текстовое (INPUT)
	
	$sql_type = f20120102('TINYTEXT');
	
}else if($_POST['type']==2){// многострочное текстовое (TEXTAREA)
	
	$sql_type = f20120102('MEDIUMTEXT');
	
}else if($_POST['type']==3){// выбор одного из множества (SELECT)
	
	$sql_type = f20120102('TINYTEXT');// создаем поле под выбранное значениЕ в OPTION-ах
	
}else if($_POST['type']==4){// выбор множества из множества (SELECT)
	
	$sql_type = f20120102('TINYTEXT');// поля и значения будут добавлены в OPTION-ах
	
}else if($_POST['type']==5){// подтверждение (CHECKBOX)
	
	$_POST['field_type'] = 'tinyint(1) unsigned';
	$sql_type = f20120102();// подтверждение (CHECKBOX)
	
	// скрываем, т.к. обработку значения по-умолчаню производим специально самостоятельно в modules/pages/right.php : $default = $_POST['value']? " DEFAULT '".$_POST['value']."' " : '';
	
}else if($_POST['type']==6){// выбор (RADIO)
	
	$sql_type = f20120102('TINYTEXT');
	
}else if($_POST['type']==7){// вариант выбора (OPTION или RADIO)
	
	$sql_type = f20120102('TINYTEXT');
	
}else if($_POST['type']==8){// загрузка файла заданного типа
	
	$sql_type = f20120102('TINYTEXT');
	
}else{
	
	$sql_type = $_POST['field_type']? $_POST['field_type'] : '';//'TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	
}
//------------------------------------------------------------------------------------------------------------------------------
if($_POST['title'] && access('w')){
	
	if($_POST['type']==10 && $_POST['name'] && $_POST['sync_pages']){// создавать скриптом
		$info = 'Данное поле синхронизируйте самостоятельно.';
	}
	
	if($sql_type && $_POST['name']){
		
		if($_POST['sync_pages']){// пользователь решил синхронизировать данное поле
			
			// в таблице $page_table поле (c старым названием) существует
			if($_POST['name_old'] && $fields->page_fields[ $_POST['name_old'] ]){
				
				mysql_($sql.' CHANGE `'.trim($_POST['name_old']).'` `'.trim($_POST['name']).'` '.$sql_type.' NOT NULL'.$default.$comment);
				
				$fields->page_fields[ $_POST['name'] ] = $fields->page_fields[ $_POST['name_old'] ];
				
				/*
				if($_POST['name']!=$_POST['name_old']){// меняем имя полю
					mysql_query($sql." CHANGE `".trim($_POST['name_old'])."` `".trim($_POST['name'])."` ".$sql_type." NOT NULL".$default."");
				}
				*/
			}else if(!$fields->page_fields[ $_POST['name'] ]){// в таблице $page_table поле не существует //, ADD INDEX ( field_name ) 
				
				mysql_($sql.' ADD `'.trim($_POST['name']).'` '.$sql_type.' NOT NULL'.$default.$comment);// добавляем поле
				
				$fields->page_fields[ $_POST['name'] ] = true;
			}
			
		}else if($_GET['field_id']){// пользователь отказался от синхронизации данного поля
			
			$r = @mysql_fetch_assoc(mysql_('SELECT * FROM '.P.'fields WHERE field_id='._.$_GET['field_id']._));// нахожу данные
			
			// если данное поле ранее было синхронизировано И в таблице $page_table поле существует
			if($r['sync_pages'] && $r['name'] && $fields->page_fields[ $r['name'] ]){
				
				mysql_($sql.' DROP `'.$r['name'].'`');// удаляем поле
				unset($fields->page_fields[ $r['name'] ]);
			}
		}
	}
	
	if($_GET['field_id']){// сохраняю
		
		if(mysql_("UPDATE ".P."fields SET 
		parent_id = "._.$_POST['parent_id']._.",
		position = "._.$_POST['position']._.",
		access = "._.(($_POST['access']=='on')?1:0)._.",
		checking = "._.$_POST['checking']._.",
		must = "._.(($_POST['must']=='on')?1:0)._.",
		type = "._.$_POST['type']._.",
		title = "._.trim($_POST['title'])._.",
		name = "._.trim($_POST['name'])._.",
		value = "._.trim($_POST['value'])._.",
		script_path = "._.trim($_POST['script_path'])._.",
		file_types = "._.trim($_POST['file_types'])._.",
		wysiwyg = "._.(($_POST['wysiwyg']=='on')?1:0)._.",
		height = "._.$_POST['height']._.",
		sync_pages = "._.(($_POST['sync_pages']=='on')?1:0)._.",
		sync_mail = "._.(($_POST['sync_mail']=='on')?1:0)._.""._."WHERE"._." field_id="._.$_GET['field_id']._."")){
			
			$_SERVER['SYSTEM']['MESSAGE'] .= 'Запись сохранена';
			$reload_left_frame = true;
		}
		
	}else if(mysql_("INSERT INTO ".P."fields VALUES (
		"._._.",
		"._.$_POST['parent_id']._.",
		"._.$_POST['position']._.",
		"._.(($_POST['access']=='on')?1:0)._.",
		"._.$_POST['checking']._.",
		"._.(($_POST['must']=='on')?1:0)._.",
		"._.$_POST['type']._.",
		"._.trim($_POST['title'])._.",
		"._.trim($_POST['name'])._.",
		"._.trim($_POST['value'])._.",
		"._.trim($_POST['script_path'])._.",
		"._.trim($_POST['file_types'])._.",
		"._.(($_POST['wysiwyg']=='on')?1:0)._.",
		"._.$_POST['height']._.",
		"._.(($_POST['sync_pages']=='on')?1:0)._.",
		"._.(($_POST['sync_mail']=='on')?1:0)._."
		)")){// добавляю
		
		$_GET['field_id'] = mysql_insert_id();
		
		$_SERVER['SYSTEM']['MESSAGE'] .= 'Запись добавлена';
		$reload_left_frame = true;
	}
	@unlink($_SERVER['DOCUMENT_ROOT'].'/cache/pages_fields.php');
}

// удаляю
if($_GET['delete_field_id'] && access('d')){
	
	if(mysql_("DELETE FROM ".P."fields WHERE field_id IN(".$fields-> kids($_GET['delete_field_id']).$_GET['delete_field_id'].")")){
		/* закомментировал т.к. на сайте бывает нужно использовать несколько форм и при случайном удалении случайно созданного поля не в той форме - нужное поле из таблицы pages_x может быть удалено,
		поэтому, после удаления - ранее синхронизированное поле остается, и если его нужно удалить из таблицы pages_x, то нужно было его заранее рассинхронизировать,
		если этого не было сделано, то теперь придется удалять его вручную!
		if($sync_fields = $fields-> kids_sync_name($_GET['delete_field_id'], 'sync_pages')){// нахожу синхронизированные поля
			
			mysql_query($sql.substr($sync_fields, 0, -1) );// удаляю поля
			
		}
		*/
		$_SERVER['SYSTEM']['MESSAGE'] .= 'Удаление выполнено';
		$reload_left_frame = true;
		
	}
	@unlink($_SERVER['DOCUMENT_ROOT'].'/cache/pages_fields.php');
}
//------------------------------------------------------------------------------------------------------------------------------
$fields->fields();// обновляем данные о полях
$parents_options = $fields-> parents_options();
if($_POST['name'] && $_POST['field_type'] && $_POST['sync_pages']=='on'){// если указан определенный тип поля
	$fields->page_fields[$_POST['name']]['Type'] = $_POST['field_type'];
}
//---------------------------------------------
/*
$site_select_data = true;
if($_GET['field_id'] && $fields->parent_id__field_id[ $_GET['field_id'] ]){// если это SELECT
	$site_select_data = false;
	foreach($fields->parent_id__field_id[ $_GET['field_id'] ] as $field_id => $true){// смотрим OPTION-ы
		if($fields->field_id__data[ $field_id ]['sync_pages']){// если OPTION синхронизирован
			$site_select_data = true;
		}
	}
}
*/
//------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл

$r = $fields->field_id__data[ $_GET['field_id'] ];// нахожу данные выбранного поля

if($_GET['parent_id']){ $parent_id = $_GET['parent_id']; }else{ $parent_id = $r['parent_id']; }
$buttons[] = $url._.'images/elements/plus.gif'._.'Создать новое поле';
$buttons[] = $url.'?parent_id='.$r['parent_id']._.'images/elements/plus_sister.gif'._.'Добавить подобное поле';
$buttons[] = $url.'?parent_id='.$_GET['field_id']._.'images/elements/plus_kid.gif'._.'Добавить дочернее поле';
$sync_pages = ($r['sync_pages'] || $fields->page_fields[ $r['name'] ])?'checked':'';
//------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head(($r['title']?'Редактирование':'Создание').' поля или формы',$buttons).'
<form action="'.$url.($r['title']?'?field_id='.$_GET['field_id']:'').'" method="post" onsubmit="return CheckSubmitForm(this)">
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	'.($parents_options? '<tr id="parent_id">
		<td><p><b>Родительский элемент:</b></p></td>
		<td><p><select name="parent_id" size="1">
			'.str_replace('value="'.$parent_id.'"', 'value="'.$parent_id.'" selected class="Selected"', $parents_options).'
		</select></p></td>
	</tr>': '').'
	<tr>
		<td width="250"><p><b>Название / Подсказка:</b></p></td>
		<td><p><input name="title" type="text" value="'.htmlspecialchars($r['title']).'"></p></td>
	</tr>
	<tr>
		<td><p><b>Порядковый номер:</b></p></td>
		<td><p><input name="position" type="text" value="'.($r['position']? $r['position'] : ($fields->max_position+10)).'"></p></td>
	</tr>
	<tr id="access">
		<td><p><b>Показывать:</b></p></td>
		<td><p><input name="access" type="checkbox" '.(($r['access'] || !$_GET['field_id'])?'checked':'').'></p></td>
	</tr>
	<tr id="sync_pages"'.($sync_pages?' alt="synced"':'').'>
		<td><p><b>Синхронизация с таблицей страниц:</b></p></td>
		<td><p><input name="sync_pages" type="checkbox" '.$sync_pages.' style="vertical-align:middle"> - пользователю MySQL необходимы права на выполнение ALTER команд</p></td>
	</tr>
	<tr>
		<td><p><b>Тип поля:</b></p></td>
		<td><p><select name="type">
			'.str_replace('="'.$r['type'].'"', '="'.$r['type'].'" selected class="Selected"', '
			<option value="0">- форма -</option>
			<option value="1">однострочное текстовое (INPUT)</option>
			<option value="2">многострочное текстовое (TEXTAREA)</option>
			<option value="3">выбор одного из множества (SELECT)</option>
			<option value="4">выбор множества из множества (SELECT)</option>
			<option value="5">подтверждение (CHECKBOX)</option>
			<option value="6">выбор (RADIO)</option>
			<option value="7">вариант выбора (OPTION или RADIO)</option>
			<option value="8">загрузка файла заданного типа</option>
			<option value="9">создавать скриптом</option>
			<option value="10">информационная строка</option>
			<option value="11">активные кнопки</option>
			').'
		</select></p></td>
	</tr>
	<tr id="name">
		<td><p><b>Имя поля (на английском):</b></p></td>
		<td><p><input name="name_old" type="hidden" value="'.htmlspecialchars($r['name']).'">
		<input name="name" type="text" value="'.htmlspecialchars($r['name']).'"></p></td>
	</tr>
	<tr id="value">
		<td><p><b>Значение по-умолчанию:</b></p></td>
		<td><p><input name="value" type="text" value="'.htmlspecialchars($r['value']).'"></p></td>
	</tr>
	<tr id="must">
		<td><p><b>Обязательность заполнения:</b></p></td>
		<td><p><input name="must" type="checkbox" '.($r['must']?'checked':'').'></p></td>
	</tr>
	<tr id="file_types">
		<td><p><b>Разрешенные типы файлов (через точку):</b></p></td>
		<td><p><input name="file_types" type="text" value="'.htmlspecialchars($r['file_types']).'"></p></td>
	</tr>
	<tr id="script">
		<td><p><b>Имя скрипта:</b></p></td>
		<td><p><select name="script_path" size="1">
			'.str_replace('value="'.$r['script_path'].'"', 'value="'.$r['script_path'].'" selected class=Selected', filesOptions($_SERVER['SYSTEM']['PATH'].'modules/fields/include') ).'
		</select></p></td>
	</tr>
	<tr id="checking">
		<td><p><b>Проверка данных:</b></p></td>
		<td><p><select name="checking">
				'.str_replace('="'.$r['checking'].'"', '="'.$r['checking'].'" selected class="Selected"', '
				<option value="0">- без проверки -</option>
				<option value="1">только буквы</option>
				<option value="2">только цифры</option>
				<option value="3">дата времени</option>
				<option value="4">номер телефона</option>
				<option value="5">электронная почта</option>
				<option value="6">отсутствие цифр</option>
				<option value="7">отсутствие букв</option>
				<option value="8">хотя бы одна буква</option>
				<option value="9">хотя бы одна цифра</option>
				').'
			</select></p>
		</td>
	</tr>
	<tr id="wysiwyg_mode">
		<td><p><b>Визуальный режим:</b></p></td>
		<td><p><input name="wysiwyg" type="checkbox" '.($r['wysiwyg']?'checked':'').'></p></td>
	</tr>
	<tr id="height">
		<td><p><b>Высота поля в пикселах:</b></p></td>
		<td><p><input name="height" type="text" value="'.htmlspecialchars($r['height']).'"></p></td>
	</tr>
	<tr id="field_type">
		<td><p><b>Типы данных (для знатоков):</b></p></td>
		<td><p><select name="field_type">'.str_replace('="'.$fields->page_fields[ $r['name'] ]['Type'].'"', '="'.$fields->page_fields[ $r['name'] ]['Type'].'" selected class="Selected"', '<option value="">Автоматический</option>
	<option value="tinyint(1) unsigned">tinyint(1) unsigned</option>
	<option value="tinyint(3) unsigned">tinyint(3) unsigned</option>
	<option value="smallint(5) unsigned">smallint(5) unsigned</option>
	<option value="mediumint(8) unsigned">mediumint(8) unsigned</option>
	<option value="int(10) unsigned">int(10) unsigned</option>
	<option value="varchar(32)">varchar(32)</option>
	<option value="varchar(255)">varchar(255)</option>
	<option value="tinytext">tinytext</option>
	<option value="text">text</option>
	<option value="mediumtext">mediumtext</option>
	
	<!--
	<option value=""></option>
	<option value=""></option>
	<option value=""></option>
	<option value=""></option><option value="">Автоматический</option><option value="INT">INT</option><option value="VARCHAR">VARCHAR</option><option value="TEXT">TEXT</option><option value="DATE">DATE</option><optgroup label="NUMERIC"><option value="TINYINT">TINYINT</option><option value="SMALLINT">SMALLINT</option><option value="MEDIUMINT">MEDIUMINT</option><option value="INT">INT</option><option value="BIGINT">BIGINT</option><option value="-">-</option><option value="DECIMAL">DECIMAL</option><option value="FLOAT">FLOAT</option><option value="DOUBLE">DOUBLE</option><option value="REAL">REAL</option><option value="-">-</option><option value="BIT">BIT</option><option value="BOOL">BOOL</option><option value="SERIAL">SERIAL</option></optgroup><optgroup label="DATE and TIME"><option value="DATE">DATE</option><option value="DATETIME">DATETIME</option><option value="TIMESTAMP">TIMESTAMP</option><option value="TIME">TIME</option><option value="YEAR">YEAR</option></optgroup><optgroup label="STRING"><option value="CHAR">CHAR</option><option value="VARCHAR">VARCHAR</option><option value="-">-</option><option value="TINYTEXT">TINYTEXT</option><option value="TEXT">TEXT</option><option value="MEDIUMTEXT">MEDIUMTEXT</option><option value="LONGTEXT">LONGTEXT</option><option value="-">-</option><option value="BINARY">BINARY</option><option value="VARBINARY">VARBINARY</option><option value="-">-</option><option value="TINYBLOB">TINYBLOB</option><option value="MEDIUMBLOB">MEDIUMBLOB</option><option value="BLOB">BLOB</option><option value="LONGBLOB">LONGBLOB</option><option value="-">-</option><option value="ENUM">ENUM</option><option value="SET">SET</option></optgroup><optgroup label="SPATIAL"><option value="GEOMETRY">GEOMETRY</option><option value="POINT">POINT</option><option value="LINESTRING">LINESTRING</option><option value="POLYGON">POLYGON</option><option value="MULTIPOINT">MULTIPOINT</option><option value="MULTILINESTRING">MULTILINESTRING</option><option value="MULTIPOLYGON">MULTIPOLYGON</option><option value="GEOMETRYCOLLECTION">GEOMETRYCOLLECTION</option></optgroup>-->').'</select></p></td>
	</tr>
	'.($_GET['field_id']? '<tr id="code_form">
		<td><p><b>Код вывода формы:</b></p></td>
		<td><p><input type="text" value="'.htmlspecialchars('{~include_once($GLOBALS["SYSTEM"]["config"]["0"]."/libraries/fields.php"); echo $GLOBALS["fields"]-> tr("'.$_GET['field_id'].'");').'"></p></td>
	</tr>': '').'
	'.($r['name']? '<tr id="code_data">
		<td><p><b>Код вывода на сайте:</b></p></td>
		<td><p><input type="text" value="'.htmlspecialchars('{~$GLOBALS["system"]-> data("'.$r['name'].'")~}').'" onclick="this.select()"></p></td>
	</tr>': '').'
	<tr class="no" id="actionButtons">
		<td>&nbsp;</td>
		<td><input class="submit" type="submit" value="'.($r['title']?'Сохранить">'.
			'&nbsp;&nbsp;<input class="submit" type="button" value="Копировать" onclick="copyField(this)">&nbsp;&nbsp;'.
			'<a href="'.$url.'?delete_field_id='.$_GET['field_id'].'" onclick="return checkDelField(this);" title="Удалить" style="text-decoration: none; color: #FF0000;">Удалить</a' : 'Создать"').'>
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
</style>
<script type="text/javascript">
function SetFileds(){
	
	$("#name").hide();
	$("#height").hide();
	$("#value").hide();
	$("#checking").hide();
	$("#must").hide();
	$("#script").hide();
	$("#file_types").hide();
	$("#code_form").hide();
	$("#parent_id").show();
	$("#code_data").hide();
	$("#sync_pages").hide();
	$("#wysiwyg_mode").hide();
	
	var type = $("select[name=type]").val();
	
	var value = $("[name=value]").val();
	if(type=='2'){
		$("#value P:last").html('<textarea name="value" rows="7" style="width:100%; margin: 3px 0;">'+value+'</textarea>');
	}else{
		$("#value P:last").html('<input name="value" type="text" value="'+value+'" class="input_text">');
	}
	
	if(type=='1' || type=='2'){// однострочное текстовое (INPUT) || многострочное текстовое (TEXTAREA)
		$("#name").show();
		$("#height").show();
		$("#value").show();
		$("#checking").show();
		$("#must").show();
		$("#sync_pages").show();
		$("#wysiwyg_mode").show();
		$("input[name=name]").focus();
	}else if(type=='3' || type=='4'){// выбор одного из множества (SELECT) || выбор множества из множества (SELECT)
		$("#name").show();
		$("#must").show();
		$("#height").show();
		$("#value").show();
		$("#sync_pages").show();
		$("input[name=name]").focus();
	}else if(type=='5' || type=='6' || type=='7'){// подтверждение (CHECKBOX) || выбор (RADIO) || вариант выбора (OPTION или RADIO)
		$("#name").show();
		$("#must").show();
		if( type!='7' ){ $("#value").show(); }
		$("#sync_pages").show();
		$("input[name=name]").focus();
	}else if(type=='8'){// загрузка файла заданного типа
		$("#name").show();
		$("#must").show();
		$("#file_types").show();
		$("#sync_pages").show();
		$("input[name=name]").focus();
	}else if(type=='9'){// создавать скриптом
		$("#name").show();
		$("#sync_pages").show();
		$("#script").show();
	}else if(type=='10'){// информационная строка - текстовое сопровождение в центре
		
	}else if(type=='11'){// активные кнопки
		
	}else{// type==0 - форма
		$("#parent_id").hide();
		$("#code_form").show();
	}
	if(!$("[name=access]").attr("checked")){// если поле не показывать
		$("#must").hide();
		$("#checking").hide();
		$("#wysiwyg_mode").hide();
		$("#height").hide();
	}
	if( $("#sync_pages").css("display")!="none" && $("[name=sync_pages]").attr("checked")){
		$("#code_data").show();
	}
}
function setPosition(){
	
	var position = 0;
	
	var selected_parent_id = $("[name=parent_id]").val();
	
	if(selected_parent_id == current_parent_id){
		position = current_position;
	}else{
		position = (parent_id__position_max[ selected_parent_id ]? parent_id__position_max[ selected_parent_id ] : 0) + 10;
	}
	$("[name=position]").val(position);
	
}

var current_position = <?php echo (int)$r['position']; ?>;
var current_parent_id = <?php echo (int)$r['parent_id']; ?>;

$(document).ready(function(){
	
	$("input:text").addClass("input_text");
	
	$(".overflowTable TR").each(function(){ $("TD:first", this).css("text-align","right"); });
	
	mouseMovements("TableEdite");
	
	$("[name=parent_id]").change(function(){ setPosition(); });
	<?php echo $r['module_id']?'':'setPosition();'; ?>
	
	$("[name=type], [name=sync_pages]").change(function(){ SetFileds(); });
	SetFileds();
	
	$("[name=title]").focus();
	
	<?php if($reload_left_frame){ echo 'reload_left_frame();'; } ?>
	
	$("[name=access]").change(function(){ SetFileds(); });
});

// используемые имена в формах (массивы данных)
<?php echo $fields-> javascript_parent_names(); ?>

// максимальные позиции родительских элементов (массивы данных)
var parent_id__position_max = {<?php echo substr( $fields-> javascript_parent_id__position_max(), 0, -1); ?>};

// копируем поле
function copyField(button){
	var form = $(button).firstParent("FORM")[0];
	if(CheckSubmitForm(form,'copy')==true){
		form.action = form.action.replace("field_id", "copy");
		$('[name=name_old]').remove();
		form.submit();
	}
}

// проверка заполнения формы
function CheckSubmitForm(form, copy){
	
	if(form.title.value == ""){// проверка на ввод title
		jAlert("Вы не указали Название / Подсказку.", function(){
			$(form.title).focus().Attention();
		});
		return false;
	}
	
	var name = $("input[name=name]").val();
	var type = $("select[name=type]").val();
	
	if(type>0 && type<9){// проверка на ввод name
		if(name==''){
			jAlert("Вы не указали Имя поля", function(){
				$(form.name).focus().Attention();
			});
			return false;
		}
		if(type!=7 && name.match(/^[0-9]+$/i)){
			jAlert("Имя поля должно содержать буквы", function(){
				$(form.name).focus().Attention();
			});
			return false;
		}
	}
	
	// проверка на идентичное имя в выбранной форме
	if(name!=''){
		
		var current_name = '<?php echo $r['name']; ?>';
		var selected_parent_id = $("[name=parent_id]").val();
		
		if(!copy && selected_parent_id==current_parent_id && name==current_name){// если сохранение поля
			
			// выполняется обычное сохранение поля, без изменений
			
		}else{// если изменение/добавление/копирование поля
			
			var names = eval('parent_'+selected_parent_id);
			
			if(names && names[ name ]){// если имя уже используется
				
				jAlert("Измените текущее имя поля, т.к. оно уже используется у одного из элементов выбранного родителя.", function(){
					$(form.name).focus().Attention();
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
	$.fancybox.showActivity();
	return true;
}

// проверка удаления формы или поля
function checkDelField(a){
	if($("[name=type]").val()=="0"){
		
		confirm_message = "Уверены что хотите удалить данную форму?<br>Так же Будут удалены все поля этой формы!<br>"+
		"Учтите, что удаляя форму, синхронизированные<br>поля не будут удалены из таблицы страниц сайта.<br>"+
		"Чтобы удалить поля формы из таблицы страниц сайта<br>их нужно сначала рассинхронизировать и сохранить."
		
	}else{
		
		confirm_message = "Уверены что хотите удалить данное поле?<br>";
		
		if($("#sync_pages").attr("alt")=="synced"){
			confirm_message += "Т.к. данное поле является синхронизированным, то при<br>его удалении оно не будет удалено из таблицы страниц сайта.<br>"+
			"Чтобы удалить данное поле из таблицы страниц<br> сайта - сначала уберите галочку возле надписи<br>«Синхронизация с таблицей страниц» и сохраните, после этого можете удалять Удаляйте поле."
		}
	}
	return checkDel(a);
}
</script>
</body>
</html>
