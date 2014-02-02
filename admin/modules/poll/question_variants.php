<?php
include_once('../../libraries/access.php');

$_GET['poll_id'] = (int)$_GET['poll_id'];
$_GET['question_id'] = (int)$_GET['question_id'];

if(access('w') && $_POST['name']){
	
	function add_poll_variants_20091124($a=array()){// обрабатываем информацию о вариантах ответов
		
		if($a){
			
			mysql_query("DELETE FROM ".P."poll_variants WHERE question_id='".$_GET['question_id']."'");
			
			foreach($a as $id => $v){
				
				if(!$id || !$v['name'] || !$v['template']){ continue; }
				
				// добавляю
				if(!mysql_("INSERT INTO ".P."poll_variants VALUES(
				"._.(is_numeric($id)? $id : '')._.", 
				"._.$_GET['question_id']._.", 
				"._.$v['position']._.", 
				"._.$v['type']._.", 
				"._.$v['name']._.", 
				"._.$v['template']._.", 
				"._.$v['addition']._.")")){
					echo 'Ошибка INSERT poll_variants';
				}
			}
		}
	}
	
	// сохраняю
	if($_GET['question_id']){
		if(mysql_("UPDATE ".P."poll_questions SET 
		type="._.$_POST['type']._.", 
		position="._.(int)$_POST['position']._.",
		template="._.$_POST['template']._.", 
		name="._.$_POST['name']._." 
		"._."WHERE"._." question_id="._.$_GET['question_id']._."")){
			
			add_poll_variants_20091124($_POST['variants']);
			
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись сохранена';
		}
	// добавляю
	}else if(mysql_("INSERT INTO ".P."poll_questions VALUES(
	"._._.", 
	"._.$_GET['poll_id']._.", 
	"._.$_POST['type']._.", 
	"._.(int)$_POST['position']._.", 
	"._.$_POST['template']._.", 
	"._.$_POST['name']._."
	)")){
		
		$_GET['question_id'] = mysql_insert_id();
		
		add_poll_variants_20091124($_POST['variants']);
		
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись добавлена';
	}
}

function poll_variants_20091124($r=array()){
	
	$v = 'variants['.($r['variant_id']? $r['variant_id'] : 'New').']';// сокращение
	
	return '<tr>
		<td><input name="'.$v.'[name]" type="text" value="'.htmlspecialchars($r['name']).'"></td>
		<td><input name="'.$v.'[position]" type="text" value="'.(int)$r['position'].'"></td>
		<td><select name="'.$v.'[type]">'.str_replace('="'.$r['type'].'"', '="'.$r['type'].'" selected', $GLOBALS['types_variant']).'</select></td>
		<td><select name="'.$v.'[template]">'.str_replace('="'.$r['template'].'"', '="'.$r['template'].'" selected', $GLOBALS['templates']).'</select></td>
		<td><input name="'.$v.'[addition]" type="text" value="'.htmlspecialchars($r['addition']).'"></td>
		<td class="'.($r['variant_id']?'delete':'add').'"><a href="#">&nbsp;</a></td>
	</tr>';
}

$types = '<select name="type"><option value="0">Опрос</option><option value="1">Голосование</option></select>';

$types_variant = '<option value="0">radio</option><option value="1">checkbox</option><option value="2" >text</option><option value="3" >textarea</option>';

$templates = filesOptions($_SERVER['DOCUMENT_ROOT'].'/templates/poll');

$str = ''; $r = array();// предопределяю переменные

if($_GET['question_id'] && is_numeric($_GET['question_id'])){
	
	if($q = mysql_query("SELECT * FROM ".P."poll_variants WHERE question_id='".$_GET['question_id']."' ORDER BY position, variant_id")){
		while($r = mysql_fetch_assoc($q)){
			$str .= poll_variants_20091124($r);
		}
	}
	
	$r = @mysql_fetch_assoc(mysql_query("SELECT * FROM ".P."poll_questions WHERE question_id='".$_GET['question_id']."'"));
	
	$text_after_answer = $_COOKIE['pollWysiwyg']? $r['text_after_answer'] : str_replace('<br />', "\n", $r['text_after_answer']);
	
}

$url = $_SERVER['PHP_SELF'].'?poll_id='.$_GET['poll_id'];

$left[] = $_SERVER['SYSTEM']['URL_MODULE_DIR'].'polls_questions.php'._.'images/elements/insert.png'._.'Вернуться в темы';
$left[] = $url._.'images/elements/plus.gif'._.'Добавить опрос/голосование к текущей теме';

echo $_SERVER['SITE_HEADER'].Head('Опрос/голосование и варианты ответов (поставьте точку перед Вопросом, чтобы он не отображался на сайте)', $left).'
<form action="'.$url.'&question_id='.$r['question_id'].'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Вопрос:</b></td>
			<td><input name="name" type="text" value="'.htmlspecialchars($r['name']).'"></td>
		</tr><tr>
			<td width="50"><b>Позиция:</b></td>
			<td><input name="position" type="text" value="'.(int)$r['position'].'"></td>
		</tr><tr>
			<td><b>Тип:</b></td>
			<td>'.str_replace('="'.$r['type'].'"', '="'.$r['type'].'" selected', $types).'</td>
		</tr><tr>
			<td><b>Шаблон:</b></td>
			<td><select name="template">'.str_replace('="'.$r['template'].'"', '="'.$r['template'].'" selected', $templates).'</select></td>
		</tr><tr>
			<td colspan=2 style="padding:0px">
				<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC">
					<tr class="yaproSortTR">
						<td><b>Варианты ответов:</b></td>
						<td width="50"><b>Позиция:</b></td>
						<td width="70"><b>Тип:</b></td>
						<td width="1"><b>Шаблон:</b></td>
						<td><b>Доп. атрибуты:</b></td>
						<td width="16">&nbsp;</td>
					</tr>
					'.$str.poll_variants_20091124().'
				</table>
			</td>
		</tr><tr>
			<td colspan=2 style="text-align:center"><input class="submit" type="submit" value="Сохранить"></td>
		</tr>
	</table>
</form>';
?>
<style type="text/css">
.NameTable TD.Image { padding:0 5px }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }

.add A { background: url(images/elements/plus.gif) !important; display:block; height:16px; width:16px; margin-left:1px; }
.delete A { background: url(images/elements/del.gif) !important; display:block; height:16px; width:16px; }
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$('[name="name"]').focus();
	
	$("input:text").addClass("input_text");
	
	$("form").submit(function(){// проверка заполнения полей
		if(this.name.value == ""){
			jAlert("Вы не указали Название", (function(e){
				e.focus();
			})(this.name));
			return false;
		}
	});
	
	$(".delete").click(function(){
		
		$(this).firstParent("TR").remove();
		return false;
		
	});
	
	$(".add").click(function(){
		
		var tr = $(this).firstParent("TR");
		
		var clone = $(tr).clone();
		
		$(".add", clone).removeClass("add").addClass("delete").click(function(){
			
			$(this).firstParent("TR").remove();
			return false;
			
		});
		
		$("SELECT", tr).each(function(i){
			
			var o = $(this)[0].selectedIndex;
			
			$("SELECT:eq("+i+") option:eq("+o+")", clone).attr("selected","selected");
			
		});
		
		var variant = Math.round(Math.random() * 1000000000);
		
		$("*", clone).each(function(){// меняем имена полям добавляемой строки
			
			if(this.tagName=="INPUT" || this.tagName=="TEXTAREA" || this.tagName=="SELECT"){
				
				var name = $(this).attr("name");
				
				$(this).attr("name", name.replace("variants[New]", "variants[New"+variant+"]") );
				
			}
		});
		
		$(clone).insertBefore(tr);
		
		var position = $(':text:eq(1)', tr);
		
		$(position).val( eval( parseInt( $(position).val(), 10) + 10 ) );
		
		$(':text:eq(0)', tr).val("").focus();
		
		return false;
	});
});
</script>
</body>
</html>
