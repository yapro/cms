<?php
// скрипт быстрого удаления комментариев с сайта
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/libraries/functions.php');

if($GLOBALS['SYSTEM']['user']['user_id']==2 && $_GET['id'] && is_numeric($_GET['id'])){
	$delete_in = $_GET['id'].',';
	$parent_id_in = $_GET['id'].',';
	while($parent_id_in){
		if($q = mysql_query("SELECT comment_id FROM ".F."comments WHERE parent_id IN (".substr($parent_id_in, 0, -1).")")){
			$parent_id_in = '';
			while($r = mysql_fetch_assoc($q)){
				$delete_in .= $r['comment_id'].',';
				$parent_id_in .= $r['comment_id'].',';
			}
		}
	}
	if($delete_in && mysql_query("DELETE FROM ".F."comments WHERE comment_id IN (".substr($delete_in, 0, -1).")") && pages_comments_reindex()){
		
		echo 'ok';
		
	}else{
		echo 'error';
	}
}else{
	echo 'access denied';
}
?>