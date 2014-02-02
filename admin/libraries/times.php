<?php
$cookieNameSaveSelectedTime = md5($_SERVER['SYSTEM']['MODULE_PATH']);

if($_POST['min'] && $_POST['max']){
	
	$min_ex = explode('.', $_POST['min']);
	$_POST['d'] = $min_ex['0'];
	$_POST['m'] = $min_ex['1'];
	$_POST['y'] = $min_ex['2'];
	
	$max_ex = explode('.', $_POST['max']);
	$_POST['d2'] = $max_ex['0'];
	$_POST['m2'] = $max_ex['1'];
	$_POST['y2'] = $max_ex['2'];
	
}

if($_POST['d'] && $_POST['m'] && $_POST['y']){
	$d = $_POST['d'];
	$m = $_POST['m'];
	$y = $_POST['y'];
	if(!$withOutSaveSelectedTime){
		cookie($cookieNameSaveSelectedTime, mktime(0,0,0,$m,$d,$y));
	}
}else if($_GET['d'] && $_GET['m'] && $_GET['y']){
	$d = $_GET['d'];
	$m = $_GET['m'];
	$y = $_GET['y'];
	if(!$withOutSaveSelectedTime){
		cookie($cookieNameSaveSelectedTime, mktime(0,0,0,$m,$d,$y));
	}
}else if(!$withOutSaveSelectedTime && $_COOKIE[ $cookieNameSaveSelectedTime ]){
	$d = date('d', $_COOKIE[ $cookieNameSaveSelectedTime ]);
	$m = date('m', $_COOKIE[ $cookieNameSaveSelectedTime ]);
	$y = date('Y', $_COOKIE[ $cookieNameSaveSelectedTime ]);
}else{
	$d = date('d');
	$m = date('m');
	$y = date('Y');
	if($times_min_minus_day){// если указано вывести данные за последние X дней (пример: применяется в статистике посещаемости, скрипт count.php)
		$mktime = mktime(0,0,0,$m,($d-(int)$times_min_minus_day),$y);
		$d = date('d', $mktime);
		$m = date('m', $mktime);
		$y = date('Y', $mktime);
	}
}
$min = mktime(0,0,0,$m,$d,$y);
if($_POST['d2'] && $_POST['m2'] && $_POST['y2']){
	$d2 = $_POST['d2'];
	$m2 = $_POST['m2'];
	$y2 = $_POST['y2'];
	if(!$withOutSaveSelectedTime){
		cookie($cookieNameSaveSelectedTime.'max', mktime(0,0,0,$m2,$d2,$y2));
	}
}else if($_GET['d2'] && $_GET['m2'] && $_GET['y2']){
	$d2 = $_GET['d2'];
	$m2 = $_GET['m2'];
	$y2 = $_GET['y2'];
	if(!$withOutSaveSelectedTime){
		cookie($cookieNameSaveSelectedTime.'max', mktime(0,0,0,$m2,$d2,$y2));
	}
}else if(!$withOutSaveSelectedTime && $_COOKIE[ $cookieNameSaveSelectedTime.'max' ]){
	$d2 = date('d', $_COOKIE[ $cookieNameSaveSelectedTime.'max' ]);
	$m2 = date('m', $_COOKIE[ $cookieNameSaveSelectedTime.'max' ]);
	$y2 = date('Y', $_COOKIE[ $cookieNameSaveSelectedTime.'max' ]);
}else{
	$d2 = date('d');
	$m2 = date('m');
	$y2 = date('Y');
	if($times_max_minus_day){// если указано вывести данные за последние X дней (пример: применяется в статистике посещаемости, скрипт count.php)
		$mktime = mktime(0,0,0,$m2,($d2-(int)$times_max_minus_day),$y2);
		$d2 = date('d', $mktime);
		$m2 = date('m', $mktime);
		$y2 = date('Y', $mktime);
	}
}
$max = mktime(0,0,-1,$m2,($d2+1),$y2);

$time_select = '<span class="mousewheelSelect"><b>C</b>&nbsp;
<select size="1" name="d">'.option('1','31',$d).'</select>
<select size="1" name="m">'.option('1','12',$m).'</select>
<select size="1" name="y">'.option('2008',date('Y'),$y).'</select>
&nbsp;&nbsp;&nbsp;<b>По</b>&nbsp;
<select size="1" name="d2">'.option('1','31',$d2).'</select>
<select size="1" name="m2">'.option('1','12',$m2).'</select>
<select size="1" name="y2">'.option('2008',date('Y'),$y2).'</select></span>';

$input_min = '<input name="min" value="'.date('d.m.Y', $min).'" class="datepickerTimeField">';
$input_max = '<input name="max" value="'.date('d.m.Y', $max).'" class="datepickerTimeField">';
$inputs_td = '<td><p><b>с</b></p></td><td><div class="input"><div>'.$input_min.'</div></div></td><td><p><b>по</b></p></td><td><div class="input"><div>'.$input_max.'</div></div></td>';
?>
