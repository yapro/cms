<?php
$allowUploadFileType['jpg']++;
$allowUploadFileType['jpeg']++;
$allowUploadFileType['png']++;
$allowUploadFileType['gif']++;

// загружаю файлы и возвращаю имена загруженных файлов
if($_FILES && $_FILES['html5_multiple_file_upload']['tmp_name'] && $_FILES['html5_multiple_file_upload']['name'] && function_exists('access') && access('w') && $_GET['page_id']){
	
	$path = '/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'].date('/Y/m/d/');
	if($error = path($_SERVER['DOCUMENT_ROOT'], $path)){
		echo 'Ошибка: '.$error; exit;
	}
	
	foreach($_FILES['html5_multiple_file_upload']['tmp_name'] as $k=>$v){
		
		if($v){
			
			$type = array_reverse( explode('.', $_FILES['html5_multiple_file_upload']['name'][$k]) );// узнаем тип файла
			$type = strtolower_($type['0']);// переводим тип файла в нижний регистр
			if(!$allowUploadFileType[ $type ]){
				continue;
			}
			$type = time().'.'.$type;// добавляем окончание к имени файла с временем загрузки
			
			$name = '';// находим имя файла без расширения
			$ex = explode('.', $_FILES['html5_multiple_file_upload']['name'][$k]);
			array_pop($ex);
			foreach($ex as $name_part){
				if($name_part){
					$name .= $name_part.'.';
				}
			}
			
			if(move_uploaded_file($_FILES['html5_multiple_file_upload']['tmp_name'][$k], $_SERVER['DOCUMENT_ROOT'].$path.$name.$type)){
				
				@chmod($_SERVER['DOCUMENT_ROOT'].$path.$name.$type, 0664);
				
				$data = array();
				$data['img'] = $path.$name.$type;
				
				if(mb_substr($name,-1)=='.'){ $name = mb_substr($name,0,-1); }
				$data['name'] = $name;
				
				$data['parent_id'] = $_GET['page_id'];
				
				$data['url'] = $GLOBALS['pages']-> url_uniq($data);// формируем уникальный урл для страницы
				
				if($GLOBALS['pages']->insert($data)){
					$GLOBALS['reload_frame'] = true;// информирование о перезагрузке левого фрейма
				}
				
			}else{
				echo 'Недостаточно прав для загрузки файла : '.$path.$_FILES['html5_multiple_file_upload']['name'][$k]; exit;
			}
		}
	}
}
$document = '<input type="file" name="html5_multiple_file_upload[]" multiple onchange="withoutAjaxForm = true;"><script>$("#formPage").attr("enctype","multipart/form-data");</script>';
?>
