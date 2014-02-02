<?php
include_once('../../libraries/access.php');
include_once('../../libraries/modules.php');

// показываем настройки доступа пользователя
if($_GET['user_id'] && is_numeric($_GET['user_id'])){
	
	if(access('w') && $_POST['module_id']){// проверка прав (начало)
		
		$data = array();
		$data['user_id'] = (int)$_GET['user_id'];
		$data['admin_info'] = $_POST['admin_info'];
		$data['auto_save'] = (int)$_POST['auto_save'];
		$data['save_url'] = ($_POST['save_url']=='on')? 1 : 0;
		$data['save_selected'] = ($_POST['save_selected']=='on')? 1 : 0;
		$data['show_size_dirs'] = ($_POST['show_size_dirs']=='on')? 1 : 0;
		//$data['browser_detect'] = (int)$_POST['browser_detect'];
		//$data['check_ip'] = (int)$_POST['check_ip'];
		$data['allow_ip'] = $_POST['allow_ip'];
		
		$data = $GLOBALS['SYSTEM']['users']-> format($data);
		
		$GLOBALS['SYSTEM']['users']-> update($data);
		
		// удаляю все права пользователя
		if(mysql_query("DELETE FROM ".P."access WHERE user_id='".$_GET['user_id']."'")){
			
			$module_id__access = array();
			
			$module_id__access = $GLOBALS['SYSTEM']['module_id__access'];
			
			foreach($module_id__access as $module_id => $access){// просматриваю права доступа к модулю определенного сайта
				
				$r = $_POST['module_id'][$module_id]['r'];
				$w = $_POST['module_id'][$module_id]['w'];
				$d = $_POST['module_id'][$module_id]['d'];
				
				if($r=='on' && $w=='on' && $d=='on'){
					$a = 7;
				}else if($r!='on' && $w=='on' && $d=='on'){
					$a = 6;
				}else if($r=='on' && $w!='on' && $d=='on'){
					$a = 5;
				}else if($r=='on' && $w=='on' && $d!='on'){
					$a = 4;
				}else if($r!='on' && $w!='on' && $d=='on'){
					$a = 3;
				}else if($r!='on' && $w=='on' && $d!='on'){
					$a = 2;
				}else if($r=='on' && $w!='on' && $d!='on'){
					$a = 1;
				}else{
					$a = 0;
				}
				// если назначаем права по данному модулю
				if($a && $a<=$access){// если выставляемые права права меньше или равны правам текущего пользователя по данному модулю
					// назначаю права доступа
					mysql_("INSERT INTO ".P."access VALUES(
						"._.$module_id._.", 
						"._.$_GET['user_id']._.", 
						"._.$a._.")");
				}
			}
		}
		$_SERVER['SYSTEM']['MESSAGE'] = '<G>Запись сохранена<!>';
	}
	
	// определяю доступ выбранного пользователя
	$module_id__access = array();
	if($q = mysql_query("SELECT * FROM ".P."access WHERE user_id='".$_GET['user_id']."'")){
		while($r=mysql_fetch_assoc($q)){
			$module_id__access[$r['module_id']] = $r['access'];
		}
	}
	
	$modules-> access_explorer(0, $module_id__access);
	
	$left[] = $_SERVER['SYSTEM']['URL_PATH'].'modules/site/communications.php'._.'images/elements/plus.gif'._.'Добавить нового пользователя';
	
	$user = $GLOBALS['SYSTEM']['users']-> data($_GET['user_id']);
	
	echo $_SERVER['SITE_HEADER'].Head('Права доступа и настройки пользователя',$left).'
	<form action="'.$_SERVER['PHP_SELF'].'?user_id='.$_GET['user_id'].'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="no">
	        <td width="270"><b>Информация:</b></td>
	        <td><input name="admin_info" type="text" value="'.htmlspecialchars($user['admin_info']).'"></td>
	    </tr><tr class="no">
	        <td><b>Автосохранение:</b></td>
	        <td><input name="auto_save" type="text" value="'.htmlspecialchars($user['auto_save']).'" style="width:85px"> - если больше нуля, измененные данные сохраняются каждые Х минут</td>
	    </tr><tr class="no">
	        <td><b>Помнить URL:</b></td>
	        <td><label><input name="save_url" type="checkbox" '.($user['save_url']?'checked':'').' style="vertical-align: bottom"> - помнить последний шаг в системе администрирования (удобство пользования)</label></td>
	    </tr><tr class="no">
	        <td><b>Помнить выбор:</b></td>
	        <td><label><input name="save_selected" type="checkbox" '.($user['save_selected']?'checked':'').' style="vertical-align: bottom"> - помнить выбор в элементах заполнения (удобство пользования)</label></td>
	    </tr><tr class="no">
	        <td><b>Размер директорий:</b></td>
	        <td><label><input name="show_size_dirs" type="checkbox" '.($user['show_size_dirs']?'checked':'').' style="vertical-align: bottom"> - показывать размер директорий в файлменеджере (ресурсоемкий процесс)</label></td>
	    </tr>
	    <!--
	    <tr class="no">
	        <td><b>Проверка браузера:</b></td>
	        <td><label><input name="browser_detect" type="checkbox" '.($user['browser_detect']?'checked':'').' style="vertical-align: bottom"> - проверять имя браузера при авторизации в системе администрирования (повышается защита)</label></td>
	    </tr><tr class="no">
	        <td><b>Проверка IP:</b></td>
	        <td><label><input name="check_ip" type="checkbox" '.($user['check_ip']?'checked':'').' style="vertical-align: bottom"> - проверять IP-адрес при авторизации в системе администрирования (повышается защита)</label></td>
	    </tr>
	    -->
	    <tr class="no">
	        <td><b>Доступ только с IP:</b></td>
	        <td><input name="allow_ip" type="text" value="'.htmlspecialchars($user['allow_ip']).'" style="width:300px"> (можно несколько, через запятую)</td>
	    </tr>
	</table>
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><p><b>Модуль</b></p></td>
			<td width=75 class="center cursor" onclick="return InvertChecked(\'r\');" title="Инвертировать выбор"><p><b>чтение</b></p></td>
			<td width=75 class="center cursor" onclick="return InvertChecked(\'w\');" title="Инвертировать выбор"><p><b>запись</b></p></td>
			<td width=75 class="center cursor" onclick="return InvertChecked(\'d\');" title="Инвертировать выбор"><p><b>удаление</b></p></td>
		</tr>
		'.$modules->access_explorer.'
		<tr class="no"><td colspan="4"><p style="text-align: center;"><input class="submit" type="submit" value="Сохранить"></p></td></tr>
	</table></form>';
	
}else{
	echo $_SERVER['SITE_HEADER'].Head('Пользователи').'<p>Данное действие недоступно</p>';
}
?>
<style>
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; margin: 0 3px }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }
</style>
<script type="text/javascript">

$("input:text").addClass("input_text");

mouseMovementsClass("overflowTable");

$(".LightGrey, .LightBlue").each(function(){// горизонтальное инвертирование выбранного
	var tr = this;
	$("td:first", this).click(function(){
		$("INPUT",tr).each(function(){
			this.checked = (this.checked==true)?false:true;
		});
	});
});

// вертикальное инвертирование выбранного
function InvertChecked(fist_word){
	var e = document.getElementsByTagName("input");
	num_inputs = e.length;// определяю кол-во захваченных элементов
	for (i=0; i<num_inputs; i++){
		if(e[i].id.substr(0, 1)==fist_word){
			e[i].checked = (e[i].checked==true)?false:true;
		}
	}
	return false;
}
</script>
</body>
</html>
