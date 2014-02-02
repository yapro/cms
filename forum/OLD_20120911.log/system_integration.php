<?php
$system_data['SYSTEM_TOP'] = '<center style="font-size: 25px; padding-top:10px">Добро пожаловать на форум</center>';
$system_data['SYSTEM_BOTTOM'] = '<br>{~footer.html~}';
if($system_data){
	foreach($system_data as $k=>$v){
		$GLOBALS['system']-> content($v, true);
		$template-> assign_vars(array($k => $v));
	}
}
// альтернативный (ручной способ добавления)
$template-> assign_vars(array('SYSTEM_OTHER' => '<!-- какой-то другой код -->'));
?>