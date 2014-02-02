<?php
// скрипт редиректит пользователя с короткого УРЛ на полный УРЛ

$e = explode('/s/p'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?', $_SERVER['REQUEST_URI']);
$a = explode('&comment_', $e['1']);
$anchor = '';
if($a['1']){
	if(!isset($a['2']) && is_numeric($a['1']) && !strstr($a['1'],'.')){
		$e['1'] = $a['0'];
		$anchor = '#comment_'.$a['1'];
	}else{
		header404('4.'.__FILE__);
	}
}else{
	$a = explode('&', $e['1']);
	if($a['1']){
		if($a['1']=='comments'){
			$e['1'] = $a['0'];
			$anchor = '#comments';
		}else{
			header404('5.'.__FILE__);
		}
	}
}

if(!$e['0'] && !isset($e['2']) && $e['1'] && is_numeric($e['1']) && !strstr($e['1'],'.')){
	
	$page_id = @mysql_fetch_row(mysql_(sql('page_id', 'page_id='.$e['1'],0,0,0,1)));
	
	if(!$page_id['0']){
		header404('1.'.__FILE__);
	}
	
	$url = url($page_id['0']);
	
	if(!$url){
		header404('2.'.__FILE__);
	}
	
	header301($url.$anchor);
	
}else{
	header404('3.'.__FILE__);
}
?>