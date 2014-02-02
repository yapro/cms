<?php
// плагин для просмотра статистики в Яндекс.Метрика
$ex = explode('?ym_playback=', $_SERVER['REQUEST_URI']);
if($ex['0'] && $ex['1']){
	$_SERVER['REQUEST_URI'] = $ex['0'];
	$GLOBALS['YANDEX_METRIKA'] = true;
}else{
	$ex = explode('/?ym_cid=', $_SERVER['REQUEST_URI']);
	if($ex['0'] && $ex['1']){
		$_SERVER['REQUEST_URI'] = $ex['0'];
		$GLOBALS['YANDEX_METRIKA'] = true;
	}
}
?>
