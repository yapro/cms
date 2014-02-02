<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю

include_once('../../libraries/ajaxUploadFile.php');// ajax загрузка изображений

if($_POST['text_after_answer'] && !$_COOKIE['pollWysiwyg']){ $_POST['text_after_answer'] = str_replace("\n", '<br />', $_POST['text_after_answer']); }

if($_POST['show_result']=='on'){ $_POST['show_result'] = 1; }

if(access('w') && $_POST['name'] && $_POST['template']){
	
	//+include_once($_SERVER['DOCUMENT_ROOT'].'/inner/Functions/system_cache.php');// класс работы с кэшем
	//+$GLOBALS['system_cache']->update('system_poll');
	
	// сохраняю
	if($_GET['poll_id']){
		if(mysql_("UPDATE ".P."poll SET name="._.$_POST['name']._.", template="._.$_POST['template']._.", time_start="._.time_($_POST['time_start'])._.", time_end="._.time_($_POST['time_end'])._.", show_result="._.$_POST['show_result']._.", text_after_answer="._.$_POST['text_after_answer']._." "._."WHERE"._." poll_id="._.$_GET['poll_id']._."")){
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись сохранена';
		}
	// добавляю
	}else if(mysql_("INSERT INTO ".P."poll VALUES("._._.", "._.time_($_POST['time_start'])._.", "._.time_($_POST['time_end'])._.", "._.$_POST['show_result']._.", "._.$_POST['template']._.", "._.$_POST['name']._.", "._.$_POST['text_after_answer']._.")")){
		$_GET['poll_id'] = mysql_insert_id();
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	}
}

$templates = '<select name="template">'.filesOptions($_SERVER['DOCUMENT_ROOT'].'/templates/poll').'</select>';

$text_after_answer = ''; $r = array();// предопределяю переменные

if($_GET['poll_id'] && is_numeric($_GET['poll_id'])){
	
	$r = @mysql_fetch_assoc(mysql_query("SELECT * FROM ".P."poll WHERE poll_id='".$_GET['poll_id']."'"));
	
	$text_after_answer = $_COOKIE['pollWysiwyg']? $r['text_after_answer'] : str_replace('<br />', "\n", $r['text_after_answer']);
}

$left[] = $_SERVER['SYSTEM']['URL_MODULE_DIR'].'polls_questions.php'._.'images/elements/insert.png'._.'Вернуться в темы';
$left[] = $_SERVER['PHP_SELF']._.'images/elements/plus.gif'._.'Добавить новую тему';

$right = '<td><img src="images/logotypes/edite.gif" id="wysiwyg" alt="pollWysiwyg"></td>';

echo $_SERVER['SITE_HEADER'].Head('Тема опросов и голосований (поставьте точку перед названием, чтобы оно не отображалось на сайте)', $left, $right).'
<form action="'.$_SERVER['PHP_SELF'].'?poll_id='.$r['poll_id'].'" method="post" onsubmit="return CheckSubmitForm(this)">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable" id="TableEdite">
		<tr class="yaproSortTR">
			<td width="75"><b>Название:</b></td>
			<td><input name="name" type="text" value="'.htmlspecialchars($r['name']).'"></td>
		</tr><tr>
			<td><b>Шаблон:</b></td>
			<td>'.str_replace('="'.$r['template'].'"', '="'.$r['template'].'" selected', $templates).'</td>
		</tr><tr>
			<td><b>Начать:</b></td>
			<td><input name="time_start" type="text" value="'.date('d.m.Y H:i:s', $r['time_start']? $r['time_start'] : time() ).'" class="datepickerTimeField"></td>
		</tr><tr>
			<td><b>Завершить:</b></td>
			<td><input name="time_end" type="text" value="'.date('d.m.Y H:i:s', $r['time_end']? $r['time_end'] : time()+2592000 ).'" class="datepickerTimeField"></td>
		</tr><tr>
			<td colspan=2><b>Текст после заполнения:</b> (заполнять не обязательно)</td>
		</tr><tr>
			<td colspan="2" style="padding: 5px"><textarea name="text_after_answer" class="wysiwyg">'.htmlspecialchars($text_after_answer).'</textarea></td>
		</tr><tr>
			<td colspan=2><label><input name="show_result" type=checkbox'.($r['show_result']?' checked':'').'> - разрешить просмотр результата ответов на сайте</label></td>
		</tr>'.($r['poll_id']?'<tr>
			<td><b>Код вставки:</b></td>
			<td><input type="text" value="'.htmlspecialchars('{~$poll_id='.$r['poll_id'].';~AND~poll.php~}').'" onclick="select()"></td>
		</tr>':'').'<tr>
			<td><b>Действия:</b></td>
			<td><input class="submit" type="submit" value="'.($r['poll_id']?'Сохранить">&nbsp; &nbsp;<a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'question_variants.php?poll_id='.$r['poll_id'].'" class="add_variants">Добавить опрос/голосование»</a':'Создать"').'></td>
		</tr>
	</table>
</form>'.Datepicker();
?>
<style type="text/css">
TEXTAREA { width: 100%; height:<?php echo $_COOKIE['pollWysiwyg']? 2 : 1; ?>50px }
.NameTable TD.Image { padding:0 5px }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; margin: 0 3px }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }
.add_variants { background: url(images/elements/add.gif) no-repeat 0 3px; padding-left: 15px; }
</style>
<script type="text/javascript">
$("input[name=name]").focus();// ставим фокус на имя
$("input:checkbox").css("vertical-align","bottom");
$("input:text").addClass("input_text");
// проверка заполнения полей
function CheckSubmitForm(form){
	if(form.name.value == ""){
		jAlert("Вы не указали Название", function(){
			form.name.focus();
		});
		return false;
	}
	if(form.template.value == ""){
		jAlert("Вы не указали Шаблон", function(){
			form.template.focus();
		});
		return false;
	}
}
wysiwygOnload();
</script>
</body>
</html>
