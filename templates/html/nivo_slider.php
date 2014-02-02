<?php
// плагин выводит слайдер из страниц, к которым приатачены изображения nivo_slider
//----------------------------------------------------------------------------
$file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-3),'html');// формируем имя кэш-файла

if($GLOBALS['system_cache']->name['pages'] && $GLOBALS['system_cache']->name['pages'] < filemtime($file)){
	
	$document = @file_get_contents($file);
	
}else{
	
	if($q = mysql_(sql('page_id, name, nivo_slider', "nivo_slider!=''"))){
		$str = '';
		while($r = mysql_fetch_assoc($q)){
			$name = clear($r['name']);
			$str .= '<a href="'.url($r['page_id']).'"><img src="'.$r['nivo_slider'].'" alt="'.$name.'" title="'.$name.'" /></a>';
		}
		$document = $str? '
		<div id="slider">'.$str.'</div>
		<script src="/js/nivo_slider/nivo_slider.js" language="Javascript" type="text/javascript"></script>
		<link href="/js/nivo_slider/nivo_slider.css" type="text/css" rel="stylesheet">
		<link href="/js/nivo_slider/nivo_slider_plus.css" type="text/css" rel="stylesheet">
		<script type="text/javascript">$(window).load(function(){ $("#slider").show().nivoSlider(); });</script>' : '';
	}
}
?>