<?php
// Старт-таймер для крон-файлов. Суть - не дает запускать пхп-крон-файл если он уже запущен. (есть еще Стоп-таймер : cron_timer_end.php)

$min = ($min && is_numeric($min) )? $min : 10;// по-умолчанию разрешаем выполнять скрипт не более 10 минут

if(!$file){
	echo error('$file is undefined : '.__FILE__);
}

$file_process = dirname(__FILE__).'/process/'.$file;// формируем имя процесс-файла

if(is_file($file_process)){// если процесс уже выполняется
	
	$time_start = filemtime($file_process);
	$time_max = time() - ($max * 60);// максимальное время, когда скрипт должен закончить свою работу
	
	if($time_start < $time_max){// если процесс выполняется больше $time_max
		error('long execute : '.$file_process);
	}
	exit;
}

function timer(){
	write_(time(), $GLOBALS['file_process'].'_TIMER.log');
}

timer();
write_(time(), $file_process);// создаем процесс-файл

if(!is_file($file_process)){// если файла нет - значит создать процесс-файл не удалось
	
	error('write_ : '.__FILE__);
	exit;
	
}

function exit_($error=''){// удаляем процесс-файл
	
	global $file_process;
	
	
	if(!$GLOBALS['file_process']){
		echo error('$file_process is undefined : '.__FILE__);
	}
	if(!unlink($GLOBALS['file_process'])){
		error(__FILE__.' : unlink : '.$GLOBALS['file_process']);
		exit;
	}else{
		unlink($GLOBALS['file_process'].'_TIMER.log');
	}
	if($error){
		error($error);
	}
	exit;
}
?>