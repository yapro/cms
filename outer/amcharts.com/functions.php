<?php
// функция возвращает код для отображения графика данных
function amcharts($str='', $xml='', $settings=''){
	
	if(!$str){ return ''; }
	
	if(!$_SERVER['SYSTEM']['amcharts_xml'] && !$xml){ return '<p>Не указан XML-файл данных.</p>'; }
	
	$settings = '/outer/amcharts.com/'.($settings? $settings : ($_SERVER['SYSTEM']['amcharts_settings']? $_SERVER['SYSTEM']['amcharts_settings'] : 'count.xml'));
	
	$settings = $_SERVER['SYSTEM']['amcharts_settings_siteroot']? $_SERVER['SYSTEM']['amcharts_settings_siteroot'] : $settings;
	
	$height = $_SERVER['SYSTEM']['amcharts_height']? $_SERVER['SYSTEM']['amcharts_height'] : 295;
	
	return '<div id="amcharts">
		<object><embed src="/outer/amcharts.com/amline.swf?path=/outer/amcharts.com/&amp;chart_id=amline&amp;settings_file='.$settings.'?v='.time().'&amp;data_file='.$_SERVER['SYSTEM']['amcharts_xml'].'&amp;preloader_color=#999999" loop="true" quality="high" wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" height="'.$height.'" width="100%" allowfullscreen="true"></object>
	</div>';
}
?>