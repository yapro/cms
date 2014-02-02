<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');// класс работы с кэшем
//-----------------------------------------------------------------------------------------------------------------------------

// добавление/изменение
if(access('w') && $_POST['img'] && $_POST['smile']){
	
	mysql_query("DELETE FROM ".P."smiles");
	
	$insert = "INSERT INTO ".P."smiles (main, priority, smile, alt, img) VALUES ";
	foreach($_POST['img'] as $k=>$img){
		if(!$_POST['delete'][$k] && $_POST['smile'][$k]){
			$insert .= '('._.($_POST['main'][$k]?1:0)._.', '._.$_POST['priority'][$k]._.', '._.$_POST['smile'][$k]._.', '._.$_POST['alt'][$k]._.', '._.$img._.'),';
		}
	}
	mysql_(substr($insert,0,-1));
	
	$_SERVER['SYSTEM']['MESSAGE'] = 'Изменение выполнено';
	
	$GLOBALS['system_cache']->update('smiles');
	$GLOBALS['system_cache']-> update('comments');
	
}

// копирование базы смайлов с др. сайта
if(access('w') && $_POST['copy'] && $_POST['site'] && $GLOBALS['SYSTEM']['access'][ $_POST['site'] ]){
	
	$insert = "INSERT INTO ".P."smiles (main, priority, smile, alt, img) VALUES ";
	
	if($q = mysql_query("SELECT main, priority, smile, alt, img FROM ".P."smiles")){
		while($r=mysql_fetch_assoc($q)){
			$insert .= '('._.$r['main']._.', '._.$r['priority']._.', '._.$r['smile']._.', '._.$r['alt']._.', '._.$r['img']._.'),';
		}
	}
	mysql_(substr($insert,0,-1));
	
	$_SERVER['SYSTEM']['MESSAGE'] = 'Копирование выполнено';
	
	$GLOBALS['system_cache']->update('smiles');
	$GLOBALS['system_cache']-> update('comments');
	
}

// удаление
if(access('d') && $_POST['delete']){
	foreach($_POST['delete'] as $smile_id=>$true){
		mysql_query("DELETE FROM ".P."smiles WHERE smile_id='".$smile_id."'");
	}
	$_SERVER['SYSTEM']['MESSAGE'] = 'Удаление выполнено';
	
	$GLOBALS['system_cache']->update('smiles');
	$GLOBALS['system_cache']-> update('comments');
	
}
//-----------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['REQUEST_URI'];// общий урл

// определяем изображения
$site = 'http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'].'/js/comments/smiles/';
$curpath = $_SERVER['DOCUMENT_ROOT'].'/js/comments/smiles';
if($dir = dir($curpath)){
	$img_options = '';
	while ($file = $dir-> read()) {
		if($file != "." && $file != ".." && !is_dir($curpath.$file)){
			$img_options .= '<option value="'.htmlspecialchars($file).'" style="background: url('.$site.$file.') no-repeat"> </option>';
		}
	}
	$dir-> close();
	if($img_options){
		if($q = mysql_query("SELECT smile_id, main, priority, smile, alt, img FROM ".P."smiles ORDER BY img, main DESC")){
			
			$str = '';
			while($r=mysql_fetch_assoc($q)){
				$str .= '<tr>
					<td><p>
						<img src="'.$site.$r['img'].'">
						<input name="img['.$r['smile_id'].']" type="hidden" value="'.htmlspecialchars($r['img']).'">
					</p></td>
					<td><p><input name="smile['.$r['smile_id'].']" type="text" value="'.htmlspecialchars($r['smile']).'"></p></td>
					<td><p><input name="alt['.$r['smile_id'].']" type="text" value="'.htmlspecialchars($r['alt']).'"></p></td>
					<td><p><input name="main['.$r['smile_id'].']" type="checkbox" '.($r['main']?'checked':'').'></p></td>
					<td><p><input name="priority['.$r['smile_id'].']" type="text" value="'.(int)$r['priority'].'"></p></td>
					<td><p><input name="delete['.$r['smile_id'].']" type="checkbox"></td>
				</tr>';
			}
			
			$right = '<td><input class="submit" type="button" value="Сохранить изменения" onclick="$(\'#mainForm\').submit()"></td>';
			
			$text = '<form action="'.$url.'" method="post" id="mainForm">
			<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
				<tr class="yaproSortTR">
					<td width="80"><b>Изображение</b></td>
					<td><b>Смайл (код)</b></td>
					<td><b>Описание (подсказка)</b></td>
					<td width="65"><b>Основной</b></td>
					<td width="65"><b>Приоритет</b></td>
					<td width="65"><b>Удаление</b></td>
				</tr><tr>
					<td><select name="img[]" id="smiles">'.$img_options.'</select></td>
					<td><p><input name="smile[]" type="text" value=""></p></td>
					<td><p><input name="alt[]" type="text" value=""></p></td>
					<td><p><input name="main[]" type="checkbox"></p></td>
					<td><p><input name="priority[]" type="text" value=""></p></td>
					<td><p style="text-align:center">Новый</p></td>
				</tr>
				'.$str.'
			</table></form>';
			
			if(!$str){// в б.д. смайлы не обнаружены:
				$text .= '<p style="padding: 5px"><b>В базе данных нет смайлов!</b> Вы можете добавлять смайлы вручную.</p>';
			}
			
		}else{
			$text = '<p style="padding: 35px 5px">Таблица смайлов не обнаружена. Создайте её.</p>';
		}
	}else{// файлов смайлов нет в директории
		
		$text = '<p style="padding: 35px 5px">Разместите изображения смайлов в директории '.$site.'</p>';
		
	}
}else{
	$text = '<p style="padding: 35px 5px">Cоздайте директорию '.$site.' в которой разместите изображения смайлов</p>';
}

echo $_SERVER['SITE_HEADER'].Head('Редактирование смайлов',0,$right,'http://yapro.ru/documents/video/rabota-s-kommentariyami-banlistom-i-smaylami.html' ).$text;
?>
<style type="text/css">
.NameTable { position:fixed; z-index:1; top:0px }
.overflowTable { margin-top:28px }

SELECT#smiles { width: 40px }
SELECT#smiles OPTION { height:19px }
.overflowTable TR TD:first-child { text-align: center}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$("input:text").addClass("input_text");
	$(":checkbox").css("margin-left","29px");
	mouseMovementsClass("overflowTable");
	$("SELECT#smiles").change(function(){
		$(this).css("background", $(":selected", this).css("background-image")+" no-repeat" );
	});
	$("SELECT#smiles").css("background", $(":selected", this).css("background-image")+" no-repeat" );// выставляем смайл по-умолчанию
});
</script>
</body>
</html>
