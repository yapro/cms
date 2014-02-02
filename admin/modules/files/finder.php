<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------------------------------------------------------
class finder {
	
	function finder($dirname='/',$name='',$search=''){
		
		if(!$name && !$search){ return ; }
		
		$this->list = '';
		
		$this->name = $name;
		
		$this->search = $search;
		
		$this->check_dir($_SERVER['DOCUMENT_ROOT'],$dirname);
	}
	
	function check_dir($path='', $dirname=''){
		
		if($path && $dirname){
			
			if($dir = @dir($path.$dirname)){
				
				while($file = $dir-> read()){
					
					if($file != "." && $file != ".."){
						
						if($this->name && !$this->search && stristr($file, $this->name)){// если поиск по имени файла или директории
							
							$this->list .= '<tr><td colspan=2>'.$dirname.$file.'</td></tr>';
							
						}
						
						if(is_dir($path.$dirname.$file)){// если есть поддиректории то продолжаем рекурсию
							
							$this-> check_dir($path, $dirname.$file."/");
							
						}else{// файл
							
							if($this->search){// если поиск по содержимому файла
								
								if($this->name && !stristr($file, $this->name)){// если поиск с учетом имени файла
									
									continue;
									
								}
								
								$f = file_get_contents($path.$dirname.$file);
								
								if(stristr($f, $this->search)){
									
									$this->list .= '<tr><td colspan=2>'.$dirname.$file.'</td></tr>';
									
								}
							}
						}
					}
				}
				$dir->close();
			}
		}
	}
}
if(($_POST['name'] || $_POST['search']) && access('w')){
	$finder = new finder($_POST['dirname'],$_POST['name'],$_POST['search']);
	if(!$finder->list){ $finder->list = '<tr><td colspan=2>Файлы не найдены</td></tr>'; }
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Поиск по заданной фразе в необходимых файлах').'
<form action="'.$url.'" method="post" onsubmit="return CheckSubmitForm(this)">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="no">
			<td width=1><b>В&nbsp;директории:</b></td>
			<td><input name="dirname" value="'.($_POST['dirname']? htmlspecialchars($_POST['dirname']) : '/').'"></td>
		</tr>
		<tr class="no">
			<td><b>Маска&nbsp;файла:</b></td>
			<td><input name="name" value="'.($_POST['name']? htmlspecialchars($_POST['name']) : '').'" placeholder="Например: name или .html"></td>
		</tr>
		<tr class="no">
			<td colspan=2><b>Искать следующее:</b></td>
		</tr>
		<tr class="no">
			<td colspan=2><textarea name="search">'.htmlspecialchars($_POST['search']).'</textarea></td>
		</tr>
		<tr class="no">
			<td>&nbsp;</td>
			<td><input type="submit" value="Найти"></td>
		</tr>
		'.($finder->list? $finder->list : '').'
	</table>
</form>';
?>
<style type="text/css">
TEXTAREA { width: 100%; height:170px; margin: 5px 0; }
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$("input:text").addClass("input_text");
	
	mouseMovementsClass("overflowTable");
	
	$("[name=search]").keydown(function(event){
	    if (event.which == 13 && event.ctrlKey) {
	       $('FORM:last').submit();
	    };
	});
	
	$("[name=name]").focus();
});
// проверка заполнения полей
function CheckSubmitForm(form){
	if(form.name.value == "" && form.search.value == ""){// проверка на ввод
		jAlert("Вы не указали искомое");
		return false;
	}
	$.fancybox.showActivity();
}
</script>
</body>
</html>
