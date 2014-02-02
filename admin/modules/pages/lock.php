<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_POST['lock_page_id']){// если пытаются отредактировать какую-то страницу
	
	if(!access('w')){ exit; }// тем, у кого нет доступа для редактирования - не выдаем сообщение
	
	$file_path = $_SERVER['SYSTEM']['MODULE_DIR'].'lock/'.$_POST['lock_page_id'].'.txt';
	
	$file_text =  @file_get_contents($file_path);
	
	$file_data = explode('.',$file_text);
	
	$user_id = (int)$file_data['0'];
	$lock_time = (int)$file_data['1'];
	
	if($user_id == $GLOBALS['SYSTEM']['user']['user_id'] || $lock_time < (time()-2) ){
		
		write_($GLOBALS['SYSTEM']['user']['user_id'].'.'.time(), $file_path);
		
	}else{
		
		echo '{"message":'.ajaxHTML('Данная страница редактируется пользователем '.$GLOBALS['SYSTEM']['users']-> username($GLOBALS['SYSTEM']['users']-> data($user_id, 'username'))).'}';
	}
	exit;
}
?>
