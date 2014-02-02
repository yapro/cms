<?php
// скрипт загружает файлы в пользовательскую директорию и возвращает путь к файлу или текст ошибки (если объявить массив $ajaxUploadFile['jpg']++; - можно применять проверку типа файлов)
if($_GET['ajaxUploadFile']){
	
	if($_FILES['ajax']['name']){
		$_FILES['ajax']['name'] = preg_replace('/[^-а-яa-z0-9\.]/sUui', '_', $_FILES['ajax']['name']);
	}
	
	if($_FILES && $_FILES['ajax']['tmp_name'] && $_FILES['ajax']['name']){// загружаю файлы и возвращаю имена загруженных файлов
		
		if(function_exists('access') && access('w')){
			
			if($ajaxUploadFile){
				$type = array_reverse( explode('.', $_FILES['ajax']['name']) );// узнаем тип файла
				$type = strtolower_($type['0']);// делаем тип в нижнем регистре
				if(!$ajaxUploadFile[ $type ]){
					echo 'Загрузка файла отменена из-за неразрешенного типа файла: '.$type;
					exit;
				}
			}
			
			$path = '/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'].date('/Y/m/d/');
			$error = path($_SERVER['DOCUMENT_ROOT'], $path);
			
			if($error){
				echo 'Ошибка: '.$error;
			}else{
				$name = '';// находим имя файла
				$ex = explode('.', $_FILES['ajax']['name']);
				array_pop($ex);
				foreach($ex as $name_part){
					if($name_part){
						$name .= translite($name_part).'_';
					}
				}
				$type = array_reverse(explode('.', $_FILES['ajax']['name']));// находим расширение файла
				$name .= time().'.'.$type['0'];// добавляем окончание к имени файла с временем загрузки
				
				if(move_uploaded_file($_FILES['ajax']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$path.$name)){
					
					@chmod($_SERVER['DOCUMENT_ROOT'].$path.$name, 0664);
					
					echo $path.$name;
					
				}else{
					echo 'Недостаточно прав для загрузки файла : '.$path.$_FILES['ajax']['name'];
				}
			}
		}else{
			echo 'Вашей учетной записи недостаточно прав доступа!';
		}
	}
	exit;
}
?>
