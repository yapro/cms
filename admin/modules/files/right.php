<?php
// для того, чтобы можно было закачивать файлы большого объема
set_time_limit(300);
header("Connection: Keep-Alive");
header("Keep-Alive: timeout=300, max=1000");

include_once('../../libraries/access.php');

$GLOBALS['SYSTEM']['short_user_dir'] = '';// путь к директории пользователя от корня сайта
$GLOBALS['SYSTEM']['full_user_dir'] = $_SERVER['DOCUMENT_ROOT'].'/';// полный путь к директории пользователя

if($GLOBALS['SYSTEM']['user']['user_type']!=3){
	$GLOBALS['SYSTEM']['full_user_dir'] = $_SERVER['DOCUMENT_ROOT'].'/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'].'/';
	$GLOBALS['SYSTEM']['short_user_dir'] = '/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'];
	if(!is_dir($GLOBALS['SYSTEM']['full_user_dir'])){
		if(mkdir($GLOBALS['SYSTEM']['full_user_dir'])){
			@chmod($GLOBALS['SYSTEM']['full_user_dir'], 0775);
		}else{
			error('full_user_dir');
		}
	}
}

if($_GET['delete'] && $_GET['path']=='/'){ echo 'В корне сайта удаление запрещено!'; exit; }

// проверка обращения к корню сайта, корень сайта очень важен и хранит самые выжные данные, и поэтому запрещен!
if(mb_substr($_GET['path'], 1) || $GLOBALS['SYSTEM']['short_user_dir']){
	
	$path = $GLOBALS['SYSTEM']['full_user_dir'].mb_substr($_GET['path'], 1);
	
	// создаем директорию
	if(access('w') && $_GET['dir']){
		//$_GET['dir'] = realslash($_GET['dir']);
		$get_dir = $_GET['dir'];//charset($_GET['dir'], 'i', 'w');
		if(is_dir($path) && mkdir($path.$get_dir)){
			@chmod($path.$get_dir, 0775);
			$_SERVER['SYSTEM']['MESSAGE'] = 'Директория создана';
		}
		// удаляю пометку о создании директории
		$ex = explode('&dir=',$_GET['dir']);
		$_GET['dir'] = $ex['0'];
	}
	
	// загружаем на сервер файлы
	if(access('w') && $_POST['upload'] && $_FILES['userfile']['tmp_name']){
		foreach($_FILES['userfile']['tmp_name'] as $k=>$v){
			if($v){
				$name = $_FILES['userfile']['name'][$k];//charset($_FILES['userfile']['name'][$k], 'i', 'w');
				$type = array_reverse( explode('.', $name) );// узнаем тип файла
				$type = strtolower_($type['0']);// делаем тип в нижнем регистре
				if($type!='php' && $type!='htaccess' && @move_uploaded_file($v, $path.$name)){
					@chmod($path.$name, 0664);
					$check++;
				}
			}
		}
		if($check){
			$_SERVER['SYSTEM']['MESSAGE'] = 'Загрузка успешно завершена';
		}else{
			$_SERVER['SYSTEM']['MESSAGE'] = 'Загрузка отменена! Проверьте права доступа и наличие директории!';
		}
	}
	
	// удаляем директорию или файл
	if(access('d') && $_GET['delete']){
		if(is_dir($path.$_GET['delete'])){
			class recursive_read_dir {
				function recursive_read_dir($path){
					if($dir = @dir($path)){
						while ($file = $dir-> read()){
							if($file != "." && $file != ".."){
								if(is_dir($path.$file)){
									$this->rmdirs[] = $path.$file;
									$this-> recursive_read_dir($path.$file.'/');
								}else{
									$this->unlinks[] = $path.$file;
								}
							}
						}
					}
				}
			}
			$recursive_read_dir = new recursive_read_dir($path.$_GET['delete'].'/');
			if($recursive_read_dir->unlinks){
				foreach($recursive_read_dir->unlinks as $fpath){
					if(!$fpath || !@unlink($fpath)){
						$error++;
					}
				}
			}
			if($recursive_read_dir->rmdirs){
				krsort($recursive_read_dir->rmdirs);
				foreach($recursive_read_dir->rmdirs as $dpath){
					if(!$dpath || !@rmdir($dpath)){
						$error++;
					}
				}
			}
			if(!@rmdir($path.$_GET['delete'])){
				echo 'repeat_delete'; exit;// необходимо, т.к. в Denwer-e с первого раза директория не удаляется
			}
		}else{
			if(!@unlink($path.$_GET['delete'])){ $error++; }
		}
		echo $_SERVER['SYSTEM']['MESSAGE'] = $error? 'Удаление отменено! Проверьте права доступа и наличие директории!' : 'Удаление выполнено';
		exit;
	}
	
	// скачиваю файл
	if(access('w') && $_GET['download'] && is_dir($path)){
		$file_type = array_reverse(explode('.', $_GET['file']));// для проверки типа php файла
		if($file_type['0']!='php' && is_file($path.$_GET['download'])){
			header("Content-type: application/force-download");
			header('Content-Disposition: attachment; filename='.str_replace(' ', '_', $_GET['download']));
			readfile($path.$_GET['download']);
		}else{
			header("HTTP/1.1 204 No Content");
		}
		exit;
	}

}

// карта сайта
class read_dir{
	var $parent_id = 0;
	function read_dir($curpath) {
		if($curpath){
			if($dir = @dir($curpath)){
				while ($file = $dir-> read()){
					if($file != "." && $file != ".."){
						
						$name = $file;// убрал т.к. на одном из сайтов получалось пустое значение (charget($file)!='i')? charset($file, 'w', 'i') : $file;
						
						if (is_dir($curpath.$file)){// если есть поддиректории то продолжаем рекурсию
							
							$this->id++;// идентификатор директории
							
							$this->dirs[$this->parent_id][$this->id] = $name;
							$this->dirnames[$this->parent_id][$this->id] = $file;
							
							if($this->parent_id==0){
								$this->diratimes[$this->id] = date('d.m.Y H:i:s', filemtime($curpath.$file));
								$this->dirperms[$this->id] = fileperms_($curpath.$file);
							}
							$old_parent_id = $this->parent_id;
							
							$this->parent_id = $this->id;
							if($GLOBALS['user']['show_size_dirs']){
								$this-> read_dir($curpath.$file."/");
							}
							$this->parent_id = $old_parent_id;
						}else{
							
							$this->files[$this->parent_id][$name] = filesize($curpath.$file);
							
							if($this->parent_id==0){
								
								$this->filemtimes[$this->parent_id][$name] = date('d.m.Y H:i:s', filemtime($curpath.$file));
								$this->fileperms[$this->parent_id][$name] = fileperms_($curpath.$file);
								$this->filenames[$this->parent_id][$name] = $file;
								$this->filedir[$this->parent_id][$name] = $curpath;
							}
						}
					}
				}
				$dir->close();
			}
		}
	}
	// метод проверки поддиректории в директории
	function explorer(){
		
		$this-> types();
		
		if($this->dirs['0']){
			
			//if($_GET['sort'])
			
			asort($this->dirs['0']);
			
			foreach($this->dirs['0'] as $id => $name){
				
				$this->sum = 0;
				$this-> size($id);
				$dirName = htmlspecialchars($name);
				$OpenDir = ' onclick="OpenDir(\''.rawurlencode($this->dirnames['0'][$id]).'\')"';
				$onInsert = str_replace('+','%20', rawurlencode($this->dirnames['0'][$id]) );
				$onDelete = rawurlencode($this->dirnames['0'][$id]);
				
				if($_COOKIE["system_files_viewer"]=='1'){// фото-режим
					
					$tooltip = htmlspecialchars('<div style="white-space:nowrap"><b>Имя:</b> '.$dirName.'<br>
					'.($GLOBALS['user']['show_size_dirs']? '<b>Размер:</b> '.filesize_($this->sum).'<br>' : '').'
					<b>Создана:</b> '.$this->diratimes[$id].'<br>
					<b>Права:</b> '.$this->dirperms[$id].'</div>');
					
					$this->str .= '<div class="inline-block" title="'.$tooltip.'">
						<div'.$OpenDir.'><img src="images/elements/folder.png" '.($_GET['insert']? 'alt="'.$onInsert.'"':'').' '.(access('d')? 'title="'.$onDelete.'"' :'').'></div>
						<div class="divName"><input type="text" value="'.$dirName.'"></div>
					</div>'."\n";
				}else{
					$this->str .= '<tr>
						<td class="TD0"'.$OpenDir.'><img src="images/types/folder.gif" title="Открыть"></a></td>
						<td '.($sortTableTR?'id="sortTableTR"':'').'>'.$dirName.'</td>
						<td>'.($GLOBALS['user']['show_size_dirs']? filesize_($this->sum) : ' ').'</td>
						<td>'.$this->diratimes[$id].'</td>
						<td>'.$this->dirperms[$id].'</td>
						'.($_GET['insert']?'<td width="1" class="insert"><img src="images/elements/pointer.gif" title="Вставить" onclick="onInsert(\''.$onInsert.'\')"></td>' : '').'
						'.(access('d')?'<td width="1" class="delete"><img src="images/elements/del.gif" alt="'.$onDelete.'"></td>':'').'
					</tr>'."\n";
				}
				$sortTableTR++;
			}
		}
		if($this->files['0']){
			
			ksort($this->files['0']);
			
			$access_type['gif'] = 1;
			$access_type['jpg'] = 2;
			$access_type['jpeg'] = 2;
			$access_type['png'] = 3;
			$trans_access_type = array_flip($access_type);
			
			foreach($this->files['0'] as $name => $size){
				
				$ex = array_reverse(explode('.', $name));
				$type = strtolower_($ex['0']);
				$onInsert = str_replace('+','%20', rawurlencode($this->filenames['0'][$name]) );
				$onDelete = rawurlencode($this->filenames['0'][$name]);
				
				if($_COOKIE["system_files_viewer"]=='1'){// фото-режим:
					
					if(!$access_type[ $type ]){ continue; }
					
					if($name){
						
						$img_info = getimagesize($this->filedir['0'][$name].$this->filenames['0'][$name]);
						
						if(!$trans_access_type[ $img_info['2'] ]){ continue; }
						
						$root_dir = $GLOBALS['SYSTEM']['full_user_dir'];// корневая директория пользователя
						$site = $GLOBALS['SYSTEM']['config']['yapro_http_host'];
						
						$ex_filedir = explode($root_dir, $this->filedir['0'][$name]);
						if($ex_filedir['1']){ $file_path = '/'.$ex_filedir['1']; }else{ $file_path = '/'; }
						if($GLOBALS['SYSTEM']['user']['user_type']!=3){ $file_path = '/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'].$file_path; }
						
						$tooltip = htmlspecialchars('<div style="white-space:nowrap">
						<b>Имя:</b> '.((mb_strlen($name)>55)?mb_substr($name,0,55).'...':$name).'<br>
						<b>Тип:</b> '.str_replace('image/','',$img_info['mime']).'<br>
						<b>Ширина:</b> '.$img_info['0'].' px<br>
						<b>Высота:</b> '.$img_info['1'].' px<br>
						<b>Размер:</b> '.filesize_($size).'<br>
						<b>Загружен:</b> '.$this->filemtimes['0'][$name].'<br>
						<b>Права:</b> '.$this->fileperms['0'][$name].'</div>');
						
						$href = 'http://'. $site.str_replace('+','%20', str_replace('%2F','/',urlencode($file_path)).urlencode($this->filenames['0'][$name]));
						$img_path = str_replace('%2F','/',rawurlencode($file_path)).rawurlencode($this->filenames['0'][$name]);
						$src_path = 'http://'.$site .'/outer/system_image_resize.php?w=74&h=64&path='.$img_path;
						
						if($size<150000){
							
							$max_w = $max_h = 64;
							$width = $img_info['0'];
							$height = $img_info['1'];
							
							if($width>$max_w){
								$w = $max_w;
								$h = ($w*$height)/$width;
							}else{
								$w = $width;
								$h = $height;
							}
							if($h>$max_h){
								$h = $max_h;
								$w = ($h*$width)/$height;
							}
							$src_path = $href.'" height="'.$h.'" width="'.$w;
							
						}
						
						$this->str .= '<div class="inline-block" title="'.$tooltip.'">
							<div style="text-align: center"><a class="miniPhoto" href="'.$href.'"><img src="'.$src_path.'"'.($_GET['insert']? ' alt="'.$onInsert.'"':'').(access('d')? ' title="'.$onDelete.'"' :'').'></a></div>
							<div class="divName"><input type="text" value="'.htmlspecialchars($name).'"></div>
						</div>'."\n";
					}
				}else{// режим просмотра файлов списком:
					$this->str .= '<tr>
						<td class="TD0"><img src="images/types/'.$this-> type($type).'" title="Скачать" onclick="onDownload(\''.urlencode($this->filenames['0'][$name]).'\')"></td>
						<td '.($sortTableTR?'id="sortTableTR"':'').' style="overflow:hidden">'.htmlspecialchars($name).'</td>
						<td>'.filesize_($size).'</td>
						<td>'.$this->filemtimes['0'][$name].'</td>
						<td>'.$this->fileperms['0'][$name].'</td>
						'.($_GET['insert']?'<td width="1" class="insert"><img src="images/elements/pointer.gif" title="Вставить" onclick="onInsert(\''.$onInsert.'\')"></td>' : '').'
						'.(access('d')?'<td width="1" class="delete"><img src="images/elements/del.gif" alt="'.$onDelete.'"></td>':'').'
					</tr>'."\n";
				}
				$sortTableTR++;
			}
		}
	}
	// метод проверки поддиректории в директории
	function size($id){
		if($id && ($this->files[$id] || $this->dirs[$id])){
			if($this->files[$id]){
				foreach($this->files[$id] as $name => $size){
					$this->sum += $size;
				}
			}
			if($this->dirs[$id]){// проверяем на субдиректории
				foreach($this->dirs[$id] as $dir_id => $dir_name){
					$this-> size($dir_id);
				}
			}
		}
	}
	// метод определения имеющихся изображений типов файлов
	function types(){
		$curpath = $_SERVER['SYSTEM']['PATH'].'images/types/';
		if($dir = @dir($curpath)){
			while ($file = $dir-> read()){
				if($file != "." && $file != ".." && !is_dir($curpath.$file)){
					$ex = explode('.', $file);
					$this->types[$ex['0']] = $file;
				}
			}
			$dir->close();
		}
	}
	// метод отдает нужное изображение
	function type($extension){
		if($this->types[$extension]){
			return $this->types[$extension];
		}else{
			return 'txt.gif';// не имеющие изображения типа
		}
	}
	// метод отдает нужное изображение
	function path($url){
		$g = mb_substr($_GET['path'], 1);
		if($g){
			
			$path = '/';
			$str = '/ <a href="'.$url.'&path=/">'.(($GLOBALS['SYSTEM']['user']['user_type']==3)? 'Корень' : 'uploads / users / '.$GLOBALS['SYSTEM']['user']['user_id']).'</a>';
			
			$ex = explode('/', $g);
			foreach($ex as $v){
				if($v){
					$path .= urlencode($v).'/';
					// убрал т.к. на одном из сайтов получалось пустое значение if(charget($v)!='i'){ $v = charset($v, 'w', 'i'); }
					$str .= ' / <a href="'.$url.'&path='.$path.'">'.$v.'</a>';
				}
			}
		}else{
			$str = '/ '.(($GLOBALS['SYSTEM']['user']['user_type']==3)? 'Корень' : 'uploads / users / '.$GLOBALS['SYSTEM']['user']['user_id']);
		}
		return $str;
	}
}
//--------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing'.($_GET['insert']?'&insert='.$_GET['insert']:'');// общий урл 
//--------------------------------------------------------------------------------------------------------------------------------
if(!$_GET['path']){// если выполнен просмотр без указания пути к просматриваемой директории
	if($check_path = $_COOKIE['system_dir_path']){// если до этого пользователь просматривал директорию
		header("location: ".$url.'&path='.$check_path);// перенаправляю его на директорию просматриваемую в последний раз
		exit;
	}else{
		$_GET['path'] = '/';// задаю переменную корня сайта для использования ее в коде ниже
	}
}else{// т.к. выполнен просмотр определенной директории, запоминаем выбор пользователя
	cookie('system_dir_path', $_GET['path']);//
}
//--------------------------------------------------------------------------------------------------------------------------------
$files_map = new read_dir($GLOBALS['SYSTEM']['full_user_dir'].mb_substr(rawurldecode($_GET['path']), 1));// читаю директорию
//--------------------------------------------------------------------------------------------------------------------------------
$files_map-> explorer();// создаю строку $files_map->str с директориями и файлами
//--------------------------------------------------------------------------------------------------------------------------------
$left[] = _.'images/types/back.gif" title="" onclick="history.back();" class="cursor'._.'Вернуться назад';
$left[] = _.'images/types/forvard.gif" onclick="history.forward();" class="cursor'._.'Перейти вперед';
$left[] = _.'images/types/txt.gif" id="userfile" class="cursor'._.'Загрузить файл на сервер';
$left[] = _.'images/types/folder.gif" onclick="create_dir()" class="cursor'._.'Создать новую директорию';

$right = '
<td style="white-space:nowrap">Вид просмотра:&nbsp;</td>
<td title="Переключатель режима просмотра"><select style="width: 61px" onchange="Cookie(\'system_files_viewer\', this.value, function(){ window.location.reload(); });">'.str_replace('="'.(int)$_COOKIE["system_files_viewer"].'"', '="'.(int)$_COOKIE["system_files_viewer"].'" selected', '<option value="0">файлы</option><option value="1">фото</option>').'</select></td>';

echo $_SERVER['SITE_HEADER'].Head('Менеджер файлов',$left,$right).'
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr class="HeadTR">
			<td><b>Путь:</b></td>
			<td width="100%">'.$files_map-> path($url).'</td>
		</tr>
	</table>
	'.($_COOKIE["system_files_viewer"]?
		'<div class="DivTableSky">'.$files_map->str.'</div>'
		:
		'<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
			<tr id="sortHeader">
				<td width="1"><b alt="type">Тип</b></td>
				<td><b alt="name">Имя</b></td>
				<td width="120"><b alt="size">Размер</b></td>
				<td width="135"><b alt="time">Изменен</b></td>
				<td width="1"><b alt="perms">Права</b></td>
				'.(($_GET['insert'] || access('d'))?'<td width="1"'.(($_GET['insert'] && access('d'))?' colspan=2':'').'><b>Действия</b></td>':'').'
			</tr>
			'.$files_map->str.'
		</table>'
	);
?>
<style type="text/css">

.HeadTR TD {
	padding-left: 7px;
	height: 28px;
	background-image: url(images/elements/g1.png); 
	background-position: 0px -9px; 
}

.TD0 { padding-left: 11px; cursor:pointer }

/*
.insert, .delete { 
	width: <?php echo ($_GET['insert'] && access('d'))? '75' : (($_GET['insert'] || access('d'))?'100':''); ?>px; 
	text-align: center;
}*/
.insert IMG, .delete IMG { cursor: pointer; }
</style>
<script type="text/javascript">
var url = "<?php echo $url; ?>";
var path = "<?php echo str_replace('%2F','/',rawurlencode($_GET['path'])); ?>";// замена %2F на / нужна для правильной работы вставки ссылок в визуальный редактор
var insert = "<?php echo $_GET['insert']? $_GET['insert'] : 'null'; ?>";
function OpenDir(name){
	
	if(name){
		location.href = url+"&path="+path+name+"/";
	}
}
// скачка файла
function onDownload(name){//path!="/" && 
	if(name){ location.href = url+"&path="+path+"&download="+name; }
}
// Создаем директорию
function create_dir(){
	jPrompt("Введите имя новой директории:", "", function(name){
		if(name){
			location.href = url+"&path="+path+"&dir="+name;
		}
	}, 0, "создать");
}
// Инсерт ссылки
function onInsert(name){
	if(name && name!="" && typeof(insert)!="undefined" && window.opener){
		var v = openBrowserSite + "<?php echo $GLOBALS['SYSTEM']['short_user_dir']; ?>" + path + name;
		if(window.opener && window.opener.document.getElementById("href")){
			window.opener.document.getElementById("href").value = v;
		}else if(window.opener && window.opener.document.getElementById("src")){
			window.opener.document.getElementById("src").value = v;
		}
		window.opener.focus();
		window.close();
	}
}
// загрузка файла
function onUpload(e){
	AjaxWindowLoad();
	e.action = url+"&path="+path;
	return true;
}
$(document).ready(function(){
	
	mouseMovementsClass("overflowTable");
	
	$(".NameTable td.Image").each(function(i){
		$(this).css("padding-left", ( (i==0)? 8 : 0 )+"px");
	});
	
	// обрабатываем удаление директорий и файлов
	$(".delete IMG").attr("title","Удалить").click(function(){
		var img = this;
		var delete_name = $(img).attr("alt");
		if(!delete_name){ return ; }
		jConfirm("Подтверждаете удаление?", function(r) {
			if(r==true){
				$.get(url+"&path="+path+"&delete="+delete_name, function(msg){
					if(msg=="repeat_delete"){// необходимо, т.к. в Denwer-e с первого раза директория не удаляется
						$.get(url+"&path="+path+"&delete="+delete_name, function(msg){
							if(msg=="Удаление выполнено"){
								$(img).closest("TR").remove();
							}else{
								jAlert("Недостаточно прав для удаления");
							}
						});
					}else if(msg=="Удаление выполнено"){
						$(img).closest("TR").remove();
					}else{
						jAlert(msg);
					}
				});
			}
		});
	});
	
	
	$("#userfile").click(function(){
		
		SH("addFiles");
		
	});
	if(!$.browser.msie){
		
		$("#addFiles [type=file]").attr("multiple", true);
		$("#addFiles [type=file]:gt(0), #addFiles BR").remove();
		$("#addFiles [type=submit]").css("width", "");
	}
	
});
</script>

<table cellspacing="1" id="addFiles" class="" style="display: none; position: fixed; left: 40%; top: 40%; z-index: 100; background-color: #FFFFFF; -position: absolute; -top: expression( sFixed(155) );">
	<tr><form action="" method="post" enctype="multipart/form-data" onsubmit="return onUpload(this);">
		<td>
			<input name="userfile[]" type="file" size="33"><br>
			<input name="userfile[]" type="file" size="33"><br>
			<input name="userfile[]" type="file" size="33"><br>
			<input name="userfile[]" type="file" size="33"><br>
			<input name="userfile[]" type="file" size="33"><br>
			<input type="submit" name="upload" value="Загрузить файлы" style="width: 100%">
		</td>
	</tr></form>
</table>
<!-- Подсказки [ -->
<?php
if($_COOKIE["system_files_viewer"]){// фото-режим:
	?>
	<style>
	.DivTableSky { text-align: justify }
	.DivTableSky .inline-block {
		margin: 10px 10px 10px 10px;
		cursor: pointer;
	}
	.miniPhoto IMG {
		background: #F7F7F7 none repeat scroll 0 0;
		border: 1px solid #CCCCCC;
		padding: 5px;
	}
	.divName { padding-bottom: 3px; }
	.divName INPUT { width: 84px; border: 0px; text-align: center; }
	</style>
	
	<link rel="stylesheet" href="/js/jquery.yapro.Tooltip/jquery.tooltip.css" />
	<script type="text/javascript" src="/js/jquery.yapro.Tooltip/jquery.tooltip.pack.js"></script>
	
	<script>
	$(document).ready(function(){
		$(".miniPhoto").attr("rel", "gallery").fancybox({
			'margin' : 0,
			'speedIn' : 0,
			'speedOut' : 0,
			'titleShow' : false
		});
		$(".inline-block").tooltip({ 
		    track: true, 
		    delay: 0, 
		    showURL: false, 
		    showBody: " - ", 
		    extraClass: "pretty", 
		    opacity: 0.95, 
		    left: 0 
		});
		
		// Добавляем отпозиционированный блок активных действий (вставить / удалить)
		$(document.body).prepend('<div id="actionButton" class="Hidden" style="z-index: 11111; vertical-align: top"><?php if($_GET['insert']){ ?><img src="images/elements/insert.png" title="Вставить" class="cursor" style="vertical-align: super;"><?php } ?><img src="images/elements/delete2.png" title="Удалить" class="cursor"></div>');
		
		var insertButton = $("#actionButton img:first");
		var deleteButton = $("#actionButton img:last");
		
		$(".inline-block").bind("mouseenter",function(){// выставляю события блокам изображениий
			
			var inline_block = this;
			
			// обрабатываем вставку изображения в поп-ап окно редактирования изображения
			var insert_name = $("img", this).attr("alt");
			if(insert_name){
				$(insertButton).unbind('click').click(function(){
					
					onInsert(insert_name);
					
				});
				
			}
			
			// обрабатываем удаление изображения 
			var delete_name = $("img", this).attr("title");
			if(delete_name){
				$(deleteButton).unbind('click').click(function(){
					jConfirm("Подтверждаете удаление?", function(r) {
					    if(r==true){
					    	$.get(url+"&path="+path+"&delete="+delete_name, function(msg){
					    		if(msg=="repeat_delete"){// необходимо, т.к. в Denver-e с первого раза директория не удаляется
					    			$.get(url+"&path="+path+"&delete="+delete_name, function(msg){
					    				 if(msg=="Удаление выполнено"){
											$(inline_block).hide();
							    			$('#actionButton').hide();
										}else{
											jAlert("Недостаточно прав для удаления");
										}
					    			});
					    		}else if(msg=="Удаление выполнено"){
									$(inline_block).hide();
					    			$('#actionButton').hide();
								}else{
									jAlert(msg);
								}
					    	});
						}
					});
				});
			}
			
			// позиционируем активные кнопки в зависимости от положения изображения
			if(typeof(delete_name)!="undefined" || typeof(insert_name)!="undefined"){
				var position = PosElement(this);
				var right_minus = (typeof(delete_name)!="undefined" && typeof(insert_name)!="undefined" && insert_name!="")? 44 : 19;
				$('#actionButton').css({
						top: (position.Top - 10) + 'px',
						left: (position.Right - right_minus) + 'px'
				}).show();
			}
		});
		
		// если кликаем по изображению для его увеличения, то скрываем активные кнопки
		$(".miniPhoto IMG").click(function(){
			$('#actionButton').hide();
		});
		
		// отменяем возможность менять имена файлам А при двойном клике - выделяем имя файла для копирования
		$(".divName input").attr("readonly", true).dblclick(function(){
			$(this).select();
		});
	});
	</script>
	<?php
}
?>
<!-- ] Подсказки -->
</body>
</html>
