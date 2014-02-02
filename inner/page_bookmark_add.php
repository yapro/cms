<?php
// скрипт добавления закладок на страницы
include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/folio.php');

if($folio && $_SESSION['SYSTEM']['VISITOR_ID']){
	
	$error = '';
	$pluse = false;
	
	$id = @mysql_fetch_row(mysql_('SELECT id FROM '.P.'pages_bookmarks 
	WHERE visitor_id = '.$_SESSION['SYSTEM']['VISITOR_ID'].' AND page_id = '.$folio));
	
	if($id['0']){
		
		if(mysql_('DELETE FROM '.P.'pages_bookmarks WHERE id = '.$id['0'])){
			
			$document = 'Закладка успешно удалена. <a href="'.$_SERVER['REQUEST_URI'].'">Восстановить закладку »</a>';
			
		}else{
			$error = error('DELETE : '.__FILE__);
		}
		
	}else{
		
		if(mysql_('INSERT INTO '.P.'pages_bookmarks SET 
		visitor_id = '.$_SESSION['SYSTEM']['VISITOR_ID'].', 
		page_id = '.$folio.', 
		time_created = '.time())){
			
			$pluse = true;
			$document = 'Закладка успешно добавлена. <a href="'.$_SERVER['REQUEST_URI'].'">Удалить закладку »</a>';
			
		}else{
			$error = error('INSERT : '.__FILE__);
		}
	}
	/*
	if(!$error){
		mysql_('UPDATE '.P.'pages 
		SET pages_bookmarks = pages_bookmarks '.($pluse?'+':'-').' 1 
		'._.'WHERE'._.' page_id = '.$folio);
	}
	*/
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		echo '{"info":'.ajaxHTML($error? $error : 'ok').'}';
		exit;
	}
	$document = '<p>'.$error.$document.'</p>';
}else{
	header404(__FILE__);
}
?>
