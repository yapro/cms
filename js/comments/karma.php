<?php
// плагин установки кармы комментариев с уникальных IP-адресов
$users_reg_only = false;// если true - то карму могут выставлять только зарегистрированные пользователи, false - посетители с уникальными IP-адресами
//--------------------------------------------------------------------------
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции
//--------------------------------------------------------------------------

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_POST['comment_id'] && is_numeric($_POST['comment_id']) && isset($_POST['karma']) && is_numeric($_POST['karma'])){
	
	$comments = @mysql_fetch_assoc(mysql_("SELECT comment_id, user_id FROM ".P."comments WHERE comment_id=".$_POST['comment_id']));
	
	if(!$comments['comment_id']){// комментария не существует, возможно он был удален модератором
		echo '{"error":"Данного комментария более не существует, обновите страницу"}'; exit;
	}
	
	$where = '';
	
	if(F){
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');// интеграция с форумом
		
		// если карму могут выставлять только зарегистрированные пользователи И если не авторизован
		if($users_reg_only && !$GLOBALS['SYSTEM']['user']['user_id']){
			
			exit;// запрещаем выставлять карму
			
		}else{// карму могут выставлять зарегистрированные пользователи И посетители с уникальными IP-адресами
			
			// не даем возможности пользователю проголосовать за свой комментарий
			if($comments['user_id'] && $comments['user_id']==$GLOBALS['SYSTEM']['user']['user_id']){
		    	
		    	echo '{"error":"Нельзя голосовать за свои комментарии"}'; exit;
		    	
		    }
			
			$where = ' AND user_id = '.(int)$GLOBALS['SYSTEM']['user']['user_id'];
			
			if(!$GLOBALS['SYSTEM']['user']['user_id']){// если обычный посетитель
				$where .= " AND user_ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._.")";
			}
		}
		
	}else{// карму могут выставлять зарегистрированные пользователи И посетители с уникальными IP-адресами
		$where = " AND user_ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._.")";
	}
	
	$check = @mysql_fetch_assoc(mysql_("SELECT comment_id, user_id, karma FROM ".P."comments_karma WHERE 
	comment_id=".$_POST['comment_id']." ".$where." LIMIT 1"));
	
	if($check['comment_id'] && $check['karma']==$_POST['karma']){
    	
    	echo '{"error":"Голосовать можно всего 1 раз"}';
    	
    // если за комммент участник не голосовали ИЛИ участник ставил такую же карму
    }else if(!$check['comment_id'] || ($check['comment_id'] && $check['karma']!=$_POST['karma'])){
    	
    	if(!$check['comment_id']){
    		
    		if(!mysql_("INSERT INTO ".P."comments_karma VALUES (
			".$_POST['comment_id'].",
			INET_ATON("._.$_SERVER['REMOTE_ADDR']._."), 
			".(int)$GLOBALS['SYSTEM']['user']['user_id'].",
			"._.$_POST['karma']._.")")){
				
				echo '{"error":'.ajaxHTML(error('2)'.__FILE__)).'}'; exit;
				
			}
			
    	}else{
    		
    		if(!mysql_("UPDATE ".P."comments_karma SET karma = "._.$_POST['karma']._." 
    		"._."WHERE"._." comment_id=".$_POST['comment_id']." ".$where)){
				
				echo '{"error":'.ajaxHTML(error('3)'.__FILE__)).'}'; exit;
				
			}
    	}
    	
    	if(mysql_query("UPDATE ".P."comments SET karma=karma".($_POST['karma']?'+':'-').($check['comment_id']? 2 : 1)." 
    	WHERE comment_id=".$_POST['comment_id'])){
			
			echo '{"karma":"ok","value":"'.($check['comment_id']? 2 : 1).'"}';
			
			$page_id = @mysql_fetch_row(mysql_query("SELECT page_id FROM ".P."comments WHERE comment_id = ".$_POST['comment_id']));
			
			//yapro_page_cache($page_id['0']);
			
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');// класс работы с кэшем
			$GLOBALS['system_cache']-> update('comments_'.$page_id['0']);
			
		}else{
			
			echo '{"error":'.ajaxHTML(error('1)'.__FILE__)).'}';
			
		}
    }
    exit;
}
/* Идеи: 
- сделать проверку на наличие рефферера, чтобы злоумышленники не реализовали возможность голосовать за свои комментарии с помощью запросов с IP-адресов посетителей своих сайтов */
?>
