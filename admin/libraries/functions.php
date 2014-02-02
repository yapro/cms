<?php
// Обновление даты кэша
function cache_update($cache_id, $last_id=false){
         if(!empty($cache_id)){
            $check = mysql_fetch_row(mysql_query("SELECT id FROM ".SITE_PREFIX."cache WHERE cache_id='".$cache_id."'"));
            if(!empty($check['0']) || $last_id){                
                mysql_query("UPDATE ".SITE_PREFIX."cache SET time_modified='".time()."' WHERE cache_id".($last_id? " BETWEEN '".$cache_id."' AND '".$last_id."'" : "='".$cache_id."'")."");
            }else{
                mysql_query("INSERT INTO ".SITE_PREFIX."cache VALUES ('".SITE_SID."', '".$cache_id."', '".time()."', '')");
            }
         }
}
// 
function start_header($info=''){
	//+if($info){ echo $info; exit; }
	header("Location: ".$_SERVER['SYSTEM']['URL']);
	exit;
}
// функция загрузки файлов в определенную директорию c возможностью определенного типа
function files($settings){
	
	$ok = $error = array();
	
	if($settings && $_FILES){
		
		foreach($_FILES as $field_name=>$file){
			
			// если файл сохранен в временной директории И указан путь загрузки
			if($file['tmp_name'] && $path = $settings[ $field_name ]['path']){// внимание: исключается путь загрузки в корневую директорию сайта!
				
				$types = array_flip( @explode('.', $settings[ $field_name ]['types']) );
				
				$type = array_reverse( explode('.', $file['name']) );// узнаем тип файла
				
				$type = strtolower_($type['0']);
				
				$name = $_POST[$field_name]? $_POST[$field_name] : $file['name'];// если указано имя для данного файла
				
				if(substr($path, 0,1)!='/'){// еслиу указан относительный путь к директории
					
					$path = $_SERVER['DOCUMENT_ROOT'].'/'.$path;// + Корневая директория сайта
					
				}
				
				if( (!$types || isset($types[$type])) && move_uploaded_file($file['tmp_name'], $path.$name)){
					
					@chmod($path.$name, 0664);
					
					$ok[$field_name] = $name;
					
				}else{
					$error[$field_name] = 'Недостаточно прав для загрузки файла : '.$path.$name;
				}
			}else{
				$error[$field_name] = 'Не задан путь загрузки файла: '.$file['name'];
			}
		}
	}
	return array('ok'=>$ok, 'error'=>$error);
}
// функция запускает функцию загрузки файлов и успешно загруженные имена файлов помещает в POST-данные
function filesPost($settings){
	
	$files = files($settings);
	
	if($files['ok'] || $files['error']){
		
		foreach($settings as $field_name=>$field_settings){
			// удаляем POST-данные файлов, которые не смогли загрузиться
			if($_FILES[$field_name] && !$files['ok'][$field_name]){ unset($_FILES[$field_name], $_POST[$field_name]); }
		}
	}
	if($_POST['ajax_file']){// ajax файл
		if($_FILES && $files['ok']){
			echo 'ok';
		}else{
			print_r($files['error']);
		}
		unset($_POST['ajax_file']);
		exit;
	}
	
	return $files['error'];
}
// функция загрузки файлов определенного типа В определенню директорию (уже не используется)
function filesControl($permission, $dir_path){
	
	if(access('w') && $permission && $dir_path && $_FILES){
		
		$dir = $_SERVER['DOCUMENT_ROOT'].'/'.$dir_path;
		
		foreach($_FILES as $field_name=>$file){
			
			if($file['tmp_name']){
				
				$type = array_reverse( explode('.', $file['name']) );
				
				$type = strtolower_($type['0']);
				
				$name = $file['tmp_name'].'_'.time().'.'.$type;
				
				if($permission[$type] && move_uploaded_file($file['tmp_name'], $dir.$name)){
					
					$_POST[$field_name] = $name;
					
					if($_POST['old_'.$field_name]){// если имеется старый файл, удаляем его
						if(!access('d') || !unlink($dir.$_POST['old_'.$field_name])){
							$_SERVER['SYSTEM']['MESSAGE'] = '<font color="red">Недостаточно прав для удаления файлов!</font> ';
						}
					}
					
				}else{
					$_SERVER['SYSTEM']['MESSAGE'] = '<font color="red">Недостаточно прав для загрузки файлов!</font> ';
				}
			}
			if(!$_POST[$field_name] && $_POST['old_'.$field_name]){// если файл не был загружен И имеется старый файл
				
				$type = array_reverse( explode('.', $_POST['old_'.$field_name]) );
				$type = strtolower_($type['0']);
				if($permission[$type]){// проверяю тип файла
					$_POST[$field_name] = $_POST['old_'.$field_name];// подготавливаю имя файла для внесения в б.д.
				}
			}
			unset($_POST['old_'.$field_name]);// удаляем переменную, чтобы не добавлять лишнее в б.д.
		}
	}
}
// функция возвращает HTML-код для работы с файлами (загрузка, просмотр, удаление), пример: filesField('notice_img','/uploads/notice/')
function filesField($field_name, $dir_path){
	
	$miniIMGid = 'miniPreStrIMG'.$field_name;
	
	$x = '<input name="'.$field_name.'" type="file" size="31" style="vertical-align: middle; height: 20px;">'.($GLOBALS['system']->data[$field_name]? '<img src="images/types/ai.gif" onmouseover="PreStrIMG(\''.$miniIMGid.'\', this, \''.$GLOBALS['SYSTEM']['config']['yapro_http_host'].$dir_path.htmlspecialchars($GLOBALS['system']->data[$field_name]).'\');" onmouseout="PreStrIMG(\''.$miniIMGid.'\');" style="vertical-align: middle;" id="'.$miniIMGid.'">
	<input type="button" value="Удалить" onclick="if(confirm(\'Удалить прикрепленное изображение?\')==true){ $(\'#old_'.$field_name.'\').val(\'\'); $(this).fadeOut(); $(\'#'.$miniIMGid.'\').fadeOut(); }" style="vertical-align: middle;">':'').'
	<input type="hidden" name="old_'.$field_name.'" id="old_'.$field_name.'" value="'.htmlspecialchars($GLOBALS['system']->data[$field_name]).'">';
	
	return $x;
	/*
	<input id="notice_img" name="notice_img" type="text" value="'.htmlspecialchars($GLOBALS['system']->data[$field_name]).'" ondblclick="openBrowser(this.id);" title="Двойной клик - выбор рисунка" style="width: 60%;">
	*/
}

// Псевдонимы
function alias(){
	
	if(isset($_SERVER['SYSTEM']['alias'])){ return $_SERVER['SYSTEM']['alias']; }// специально, чтобы не повторять SQL-запрос
	
	$alias = array();
	
	if(!$_SERVER['SYSTEM']['alias'] && $q = mysql_query("SELECT k, v FROM ".P."alias")){
		while($r = mysql_fetch_assoc($q)){
			$alias[$r['k']] = $r['v'];
		}
	}
	return $_SERVER['SYSTEM']['alias'] = $alias;
}
/*
Создаем options из файлов по заданному каталогу
filesOptions('/root/dir');// возвращает ПУТЬ_К_ФАЙЛУ_ОТ_УКАЗАННОЙ_ДИРЕКТОРИИ => ИМЯ_ФАЙЛА
filesOptions('~/dir');// как и в варианте 1, но директория определяется как $_SERVER['DOCUMENT_ROOT']/dir/
НЕРЕАЛИЗОВАННЫЕ Ф-ИИ:
filesOptions('/root/dir','from_root');// возвращает ПОЛНЫЙ_ПУТЬ_К_ФАЙЛУ => ИМЯ_ФАЙЛА
filesOptions('/root/dir','only_names');// возвращает ИМЯ_ФАЙЛА => ИМЯ_ФАЙЛА
*/
function filesOptions($folder, $rule='', $i=0, $subdir=''){
	
	if(mb_substr($folder, 0,1)=='~'){ $folder = $_SERVER['DOCUMENT_ROOT'].mb_substr($folder, 1); }
	
	if($folder && $dir = @dir($folder)){
		
		$alias = alias();
		
		for($n=0; $n<$i; $n++){ $nbsp .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; }
		
		/*
		if(mb_substr($folder, 0,1)!='/'){
			$prefix = $folder.'/';
		}else if($sub_prefix && is_string($sub_prefix)){
			$prefix = $sub_prefix.'/';
		}
		*/
		$dirs = $files = array();
		
		while($file = $dir->read()){
			if($file!="." && $file!=".."){
				if(is_dir($folder.'/'.$file)){
					$i++;
					$dirs[] = array('name'=> $file, 'folder' => $folder.'/'.$file, 'rule' => $rule, 'i' => $i, 'subdir' => $subdir.$file.'/');
					$i--;
				}else{
					$files[ $subdir.$file ] = $nbsp.($alias[$file]? $alias[$file] : $file);
				}
			}
		}
		
		$dir->close();
		
		if($files){
			asort($files);
			foreach($files as $path => $name){
				$options .= '<option value="'.$path.'">'.$name.'</option>';
			}
		}
		
		if($dirs){
			foreach($dirs as $r){
				$options .= '<OPTGROUP label="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nbsp.htmlspecialchars($r['name']).'">'.filesOptions($r['folder'], $r['rule'], $r['i'], $r['subdir']).'</OPTGROUP>';
			}
		}
	}
	return $options;
}
// проверка на обновление левого фрейма:
function reloadFrame(){
	$reload_frame = array();
	if($_POST['reload_frame']){
		foreach($_POST['reload_frame'] as $k=>$v){
			if($_POST[$k]!=$v){
				$reload_frame[$k] = true;
			}
		}
		unset($_POST['reload_frame']);
	}
	if($reload_frame){ return $reload_frame; }
}
// визуальный редактор используемый по-умолчанию
function system_wysiwyg(){
	return '';//'<script id="zxcvb" type="text/javascript" src="http://'.$_SERVER['HTTP_HOST'].'/js/tiny_mce/tiny_mce.js" onload="wysiwygOnload();" ></script>';
}

// добавляет нужные файлы javascript и css файлов для реализации выбора даты времени с помощью формы
function Datepicker($withoutHis=false){
	return '<link type="text/css" href="http://'.$_SERVER['HTTP_HOST'].'/js/jquery.yapro.Datepicker/latest.css" rel="Stylesheet" />
	<script type="text/javascript" src="/js/jquery.yapro.Datepicker/'.($withoutHis?'1.7.2':'ui.datepicker').'.js?v2"></script>';
}
// дефолтные настройки пользователя сохраненные в кукиес. Нужны права на запись!
function sDefaultCookiesData($data, $cookie_prefix, $names){
	
	if(access('w') && $data && $cookie_prefix && $names){
		
		if(!is_array($names)){ $names[] = $names; }
		
		foreach($names as $name){
			
			if($name && $cookie_data = $_COOKIE[$name.$cookie_prefix] ){
				
				$data[$name] = $cookie_data;
			}
		}
	}
}
// Переиндексация информации о комментариях страниц
function pages_comments_reindex(){
	
	if($q = mysql_('SELECT comment_id, page_id, time_created FROM '.P.'comments ORDER BY page_id')){
		
		$page_id__comments = $page_id__comment_id = $page_id__comment_time = array();
		
		while($r=mysql_fetch_assoc($q)){
			$time_created = $page_id__comment_time[ $r['page_id'] ];
			$page_id__comment_time[ $r['page_id'] ] = ($r['time_created']>$time_created)? $r['time_created'] : $time_created;
			$page_id__comment_id[ $r['page_id'] ] = $r['comment_id'];
			$page_id__comments[ $r['page_id'] ]++;
		}
		
		// обнуляем данные всем страницам - чтобы затем обновить эти данные тем страницам на которых есть комменты
		if(mysql_('UPDATE '.P.'pages SET comment_id=0, comment_time=0, comments=0')){
			
			if($page_id__comments){
				foreach($page_id__comments as $page_id => $comments){
						mysql_('UPDATE '.P.'pages SET 
							comment_id = '.$page_id__comment_id[ $page_id ].',
							comment_time = '.$page_id__comment_time[ $page_id ].',
							comments = '.$comments.'
						WHERE page_id = '.$page_id);
				}
			}
			
			if(mysql_('UPDATE '.P.'cache SET time_modified = '.time().' '._.'WHERE'._.' name = '._.'pages'._)){
				return true;
			}
		}
	}
}
// получаем нужные сортировки
function setOrder($default_sort){
	
	$e = explode(',', $_GET['order']? rawurldecode($_GET['order']) : $default_sort);
	$a = array();
	foreach($e as $v){// проходим по сортировкам
		if($v){
			$name = mb_substr($v, 1);
			$sort = mb_substr($v, 0, 1);
			$a[$name] = $sort;
		}
	}
	//print_r($a);
	$_SERVER['SYSTEM']['orderby']['sql'] = '';
	foreach($a as $name => $sort){
		
		$_SERVER['SYSTEM']['orderby']['get'][$name] = '&order='.(($sort=='A')?'D':'A').''.$name.'" class="Order'.(($sort=='A')?'D':'A').'" title="Отсортировать по '.(($sort=='A')?'убыванию':'возрастанию').'';
		
		$sort = ($sort=='A')?'ASC':'DESC';
		
		if($_SERVER['SYSTEM']['orderby']['sql']){
			$_SERVER['SYSTEM']['orderby']['sql'] .= ',';
		}
		$_SERVER['SYSTEM']['orderby']['sql'] .= '`'.$name.'` '.$sort;
	}
	//print_r($_SERVER['SYSTEM']['orderby']['get']);
	/*
			if($_SERVER['SYSTEM']['orderby_plus']){
				$_SERVER['SYSTEM']['orderby']['sql'] .= 
			}else{
				
			}
			
			$_SERVER['SYSTEM']['orderby_plus']['name'][mb_substr($v, 1)] = mb_substr($v, 0, 1);
		}
	}
	
	//print_r($_SERVER['SYSTEM']['orderby']['name']);
	if($_GET['order'] && $_SERVER['SYSTEM']['orderby']['name'][$_GET['order']]){// если ранее уже была сортировка по данному полю
		$fist_order[$_GET['order']] = ($_SERVER['SYSTEM']['orderby']['name'][$_GET['order']]=='A')?'D':'A';
	}else if($_GET['order']){// если сортировка по данному полю впервые
		$fist_order[mb_substr($_GET['order'], 1)] = mb_substr($_GET['order'], 0, 1);
	}
	if($fist_order){
		$temp = $_SERVER['SYSTEM']['orderby']['name'];
		unset($_SERVER['SYSTEM']['orderby']['name']);
		$_SERVER['SYSTEM']['orderby']['name'] = $fist_order;
		
		foreach($temp as $k=>$v){
			if($k!=$_GET['order']){
				$_SERVER['SYSTEM']['orderby']['name'][$k] = $v;
			}
		}
	}
	foreach($_SERVER['SYSTEM']['orderby']['name'] as $name => $by){
		$sql_orderby .= $name.' '.(($by=='A')?'ASC':'DESC').', ';
		$get_orderby .= (($by=='A')?'A':'D').$name.',';
		$_SERVER['SYSTEM']['orderby']['set'][$name] = '&order='.(($by=='A')?'A':'D').''.$name.'" class="Order'.(($by=='A')?'A':'D').'" title="Отсортировать по '.(($by=='A')?'убыванию':'возрастанию').'';
	}
	$_SERVER['SYSTEM']['orderby']['sql'] = mb_substr($sql_orderby, 0, -2);
	$_SERVER['SYSTEM']['orderby']['get'] = mb_substr($get_orderby, 0, -1);
	*/
}

// если указана сортировка - находим ORDER BY для MySQL-запроса
function orderBy(){
	return $_SERVER['SYSTEM']['orderby']['sql']? 'ORDER BY '.$_SERVER['SYSTEM']['orderby']['sql'] : '';
}

// устанавливаем дополнительные GET-сортировки для нужных URL-ссылок. примеры: getOrder('field_name') | getOrder('Dfield_name') | getOrder('table.field_name') | getOrder('Atable.field_name') | getOrder('Dtable.field_name,Atable.field_name2')
function getOrder($name){
	return $_SERVER['SYSTEM']['orderby']['get'][$name]? $_SERVER['SYSTEM']['orderby']['get'][$name] : '&order=A'.$name.'" title="Отсортировать по возрастанию';
}
?>
