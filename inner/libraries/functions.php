<?php
//$GLOBALS['SYSTEM_LOG_PAGE_ID'] = 123;// в этой переменной можно задать page_id страницы, по которой вести логи, на всех остальных страницах в это время логи октлючаются

// в случае, если емэйл указывают с точкой в конце
if($_POST['email'] && mb_substr($_POST['email'],-1)==='.'){
	$_POST['email'] = mb_substr($_POST['email'],0,-1);
}

if(!$_SERVER['HTTP_HOST_NAME']){ $_SERVER['HTTP_HOST_NAME'] = $_SERVER['HTTP_HOST']; }

// выводит на экран ошибку о функции если она уже была объявлена ранее
function yapro_noexist($function=''){
	if($function && function_exists($function)){
		echo $function.' уже объявлена (function exist)';
		exit;
	}
	return true;
}
// Функция определяет и отдает путь к директории вышестоящей на $num директорий выше чем файл, в котором выполняется данная функция
function topdir($file_path='', $num=0){
	if($file_path){
		$ex = explode($_SERVER['DOCUMENT_ROOT'], $file_path);
		if($ex['1']){ $file_path = $_SERVER['DOCUMENT_ROOT'].$ex['1']; }
		$this_dir = explode('/',$file_path);
		$stop = count($this_dir) - $num - 1;
		for($i=1; $i<$stop; $i++){
			$path .= '/'.$this_dir[$i];
		}
		return $path.'/';
	}
}
// Замер времени выполнения
function getmicrotime(){
	if(isset($GLOBALS['SYSTEM']['time_end'])){ $GLOBALS['SYSTEM']['time_last'] = $GLOBALS['SYSTEM']['time_end']; }// время последнего вызова микротайма
	list($usec, $sec) = explode(" ",microtime());
	$GLOBALS['SYSTEM']['time_end'] = ((float)$usec + (float)$sec);
	return $GLOBALS['SYSTEM']['time_end'];
}
$GLOBALS['SYSTEM']['time_start'] = getmicrotime();

// функция перенаправления c завершением выполнения скрипта
function header302($path='', $error_message=false){
	header301($path, $error_message);
}
function header301($path='', $error_message='', $pages_301=false){
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/301.php');
}
function header404($error_message='', $important=null){
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/404.php');
}
// функция очистки имени хоста
function host_clear($host=''){
	if($host){
		$host = (mb_substr($host,0,4)=='www.')? mb_substr($host,4) : $host;// избавляемся от www.
		$host = (mb_substr($host,-1)=='.')? mb_substr($host, 0, -1) : $host;// сайт-рефферер без точки в конце домена, напрмер: google.kz.
		return $host;
	}
}
// функция проверки емэйла
function email($email=''){
	
	// в случае, если емэйл указывают с точкой в конце
	if($email && mb_substr($email,-1)==='.'){
		$email = mb_substr($email,0,-1);
	}
	
	// если передано пустое значение или значение числовое
	if(!$email || is_numeric($email)){ return false; }
	
	if($GLOBALS['SYSTEM']['config']['email_check_mx']){// Проверять MX-записи Email-адресов
		
		// выбираем домен
		$ex = explode("@" , $email);
		
		// если нет MX-записи, соответствующие имени данного Internet-хоста, возвращаем false
		if(!getmxrr($ex[1], $mxhostsarr)){ return false; }
	}
	// проверка валидации
	if(!preg_match('/^[-a-zA-Z0-9_\.]+@[-a-zA-Z0-9]+\.[-a-zA-Z0-9\.]+$/', $email)){ return false; }else{ return true; }
	//if(!eregi('^[-a-zA-Z0-9_\.]+@[-a-zA-Z0-9]+\.[-a-zA-Z0-9\.]+$', $email)){ return false; }else{ return true; }
	
}
// собственный метод закавычивания символов регулярного выражения
function preg_quote_($str=''){
	$str = str_replace('-', '\-', str_replace('_', '\_', str_replace("'", "\\'", preg_quote($str, "/"))));
	return $str;
}
// пытается найти изображения, собирает их в массив и подготавливает HTML для отправки, если не находит - ничего не возвращает

function mail_img_files($html=''){
	if($html){
		if(ini_get('magic_quotes_gpc')=='1'){ $html = str_replace("\'", "'", str_replace('\"', '"', $html)); }
		$images = imgSite($html);
		if($images){
			
			foreach($images as $src){
				if($src){
					$content = file_get_contents($src);
					if($content){
						$files[] = array('src'=>$src, 'content'=>$content);
					}
				}
			}
			if($files){ return $files; }
		}
	}
}
function mail_img_files_V1($html=''){
	if($html){
		if(ini_get('magic_quotes_gpc')=='1'){ $html = str_replace("\'", "'", str_replace('\"', '"', $html)); }
		$images = imgSite($html);
		if($images){
			foreach($images as $src){
				$path = '/<img(.+)src=("|\'|)'.str_replace('-', '\-', str_replace('_', '\_', preg_quote($src, "/"))).'(.*)>/sUi';
				
				$ex = array_reverse(explode('.', $src));
				$name = md5($src).'.'.$ex['0'];
				
				preg_match_all($path, $html, $finded);
				if($finded && $content = file_get_contents($src)){
					$files[$name] = $content;
					$f = '<img'.$finded['1']['0'].'src='.$finded['2']['0'].$src.$finded['3']['0'].'>';
					$r = '<img'.$finded['1']['0'].'src='.$finded['2']['0'].'cid:'.$name.$finded['3']['0'].'>';
					$html = str_replace($f, $r, $html);
				}
			}
			if($html && $files){ return array('html'=>$html, 'files'=>$files); }
		}
	}
}
// 
// Отправляем почту в виде текста или с приатаченым(и) файлом(и) конец
// mail_('отку@да', 'от кого', 'ку@да', 'тема'='Без темы', 'сообщение'='', 'файлы'=null, 'сохранять копию в базу'=false)
function mail_($from_email='', $from_name='', $to_email='', $subj='Без темы', $message='', $file=null, $copy=null){
	
	$from_email = trim($from_email);
	$to_email = trim($to_email);
	
	if(!$from_email){ $from_email = 'robot@gmail.com'; }
	
	if(!$to_email){ log_('не указано $to_email', $_SERVER['DOCUMENT_ROOT'].'/uploads/'.md5($_SERVER['DOCUMENT_ROOT']).'_mail.txt'); return false; }
	
	if(!email($from_email)){
		return false;
	}
	if(ini_get('magic_quotes_gpc')=='1'){
		$subj = str_replace('\"', '"', str_replace("\'", "'", $subj));
		$message = str_replace("\'", "'", str_replace('\"', '"', $message));
	}
	
	$e = explode(',', $to_email);
	foreach($e as $v){
		if(!email(trim($v))){
			return false;
		}else{
			if(($GLOBALS['SYSTEM']['config']['yapro_mail_duplicates'] || $copy) && $copy!=='without_duplicate' && $copy!=='wd'){
				mysql_('INSERT INTO '.P.'mail_duplicates SET 
				size = '._.strlen($message)._.',
				time_sent = '._.time()._.',
				from_email = '._.$from_email._.',
				to_email = '._.$v._.',
				subj = '._.$subj._.',
				message = '._.$message._);
			}
		}
	}
	
	// Is the OS Windows or Mac or Linux
	if(strtolower_(mb_substr(PHP_OS,0,3))=='win'){
		$eol="\r\n";
	}else if(strtolower_(mb_substr(PHP_OS,0,3))=='mac'){
		$eol="\r";
	}else{
		$eol="\n";
	}
	
	$subj = phpbb3_mail_encode($subj);
	
	$from_name = $from_name? $from_name : $_SERVER['HTTP_HOST_NAME'];// если не указано Имя отправителя - пусть будет имя хоста
	
	$from_name = phpbb3_mail_encode( text($from_name) );
	
	if(mb_strlen(strip_tags($message))==mb_strlen($message)){ $message = str_replace($eol, '<br>',$message); }// Если текст, то заменяю \n на <br>
	//+$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);// позаимствовано из phpBB3
	
	$un = strtolower_(uniqid(time()));
	// Собираем хеадер
	//$headers  = 'X-PHP-Script: YaPro.ru/mail_'.$eol;
	$headers .= 'From: "'.$from_name.'" <'.text($from_email).'>'.$eol;
	$headers .= 'Return-path: <'.text($from_email).'>'.$eol;
	$headers .= 'Reply-To: "'.$from_name.'" <'.text($from_email).'>'.$eol;
	$headers .= "Mime-Version: 1.0".$eol;
	$headers .= "Content-Type: multipart/related; ";// если указать multipart/mixed - внизу письма показывает все участвующие файлы как вложенные
	$headers .= "boundary=----------".$un."".$eol.$eol;
	
	// Собираем заголовок
	$zag  = "------------".$un."".$eol;
	$zag .= "Content-Type:text/html; charset=UTF-8".$eol;
	$zag .= "Content-Transfer-Encoding: 8bit".$eol.$eol."".$message."".$eol.$eol;// Добавили наш текст в письмо
	
	if($file){// если добавляем файл или несколько файлов // && (!is_array($file) || !$file_name)
			
		if(is_array($file)){// если массив указателей/ссылок на файлы
			foreach($file as $k=>$v){
				$files[$k] = $v;
			}
		}else{// если прикрепляем один файл
			$files[] = $file;
		}
		
		if(!$GLOBALS['SYSTEM']['image_mime_types']){// задаю MIME-типы в зависимости от расширения изображения
			$GLOBALS['SYSTEM']['image_mime_types'] = array(
				'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg',
				'gif'=>'image/gif',
				'png'=>'image/png'
			);
		}
		
		foreach($files as $k=>$v){
			$mime = 'application/octet-stream';
			$content = null;
			if(is_array($v)){
				if($v['src'] && $v['content']){
					$ex = array_reverse(explode('.', $v['src']));
					if($ex['0'] && $ex['1'] && $GLOBALS['SYSTEM']['image_mime_types'][ $ex['0'] ]){
						$mime = $GLOBALS['SYSTEM']['image_mime_types'][ $ex['0'] ];
						$file_name = $ex['1'].'.'.$ex['0'];
						$content = $v['content'];// определяем содержание прикрепляемого файла
					}
				}
			}else{
				if(mb_stristr($v, $eol) || mb_stristr($v, "\t")){// данный файл $k имеет содержание файла $v (в котором есть $eol или \t)
					$file_name = phpbb3_mail_encode($k);
					$content = $v;
				}else{
					if($k && !is_numeric($k)){
						$file_name = $k;
					}else{
						$ex_name = array_reverse(explode('/', $v));
						$file_name = strip_tags($ex_name['0']);
					}
					$content = file_get_contents($v);// определяем содержание прикрепляемого файла
				}
			}
			if($content){
				// вписываем нужные заголовки о файле
				$zag .= "------------".$un."\n";
				//$zag .= "Content-ID: ".basename($file_name).$eol;
				$zag .= "Content-Type: ".$mime."; ";
				$zag .= "name=\"".basename($file_name)."\"\n";
				if($v['src']){
					$zag .= "Content-Location: ".$v['src']."\n";
					$zag .= "Content-Transfer-Encoding:base64\n\n";
				}else{
					$zag .= "Content-Transfer-Encoding:base64\n";
					$zag .= "Content-Disposition:attachment;";
					$zag .= "filename=\"".basename($file_name)."\"\n\n";
				}
				$zag .= chunk_split(base64_encode($content))."\n";
			}
		}
	}
	
	$oldphpself = $_SERVER['PHP_SELF'];
	//$oldremoteaddr = $_SERVER['REMOTE_ADDR'];
	
	$_SERVER['PHP_SELF'] = '/mail_';
	//$_SERVER['REMOTE_ADDR'] = $_SERVER['SERVER_ADDR']; 
	
	$return = false;
	if(mail($to_email, $subj, $zag, $headers)){
		$return = true;
	}
	
	$_SERVER['PHP_SELF'] = $oldphpself;
	//$_SERVER['REMOTE_ADDR'] = $oldremoteaddr;
	
	return $return;
}
// функция создания оптионов
function option($min=0,$max=0,$selected=0,$without_value=false){
	for($i=(int)$min;$i<=$max;$i++){
		$value = $without_value? '' : ' value="'.((mb_strlen($min)>1 && $i<10)?'0':'').$i.'"';
		$s .= '<option'.$value.(($i==$selected)?' selected':'').'>'.((mb_strlen($min)>1 && $i<10)?'0':'').$i.'</option>';
	}
	return $s;
}
// end function
// функция перевода в нижний регистр английского и русского алфавита
$GLOBALS['SYSTEM']['trans'] = array(
/* АНГЛИЙСКИЙ АЛФАВИТ */
'A'=>'a',
'B'=>'b',
'C'=>'c',
'D'=>'d',
'E'=>'e',
'F'=>'f',
'G'=>'g',
'H'=>'h',
'I'=>'i',
'J'=>'j',
'K'=>'k',
'L'=>'l',
'M'=>'m',
'N'=>'n',
'O'=>'o',
'P'=>'p',
'Q'=>'q',
'R'=>'r',
'S'=>'s',
'T'=>'t',
'U'=>'u',
'V'=>'v',
'W'=>'w',
'X'=>'x',
'Y'=>'y',
'Z'=>'z',
/* ВЕРХНИЙ РЕГИСТР БУКВ КИРИЛИЦЫ */
'А'=>'а',
'Б'=>'б',
'В'=>'в',
'Г'=>'г',
'Д'=>'д',
'Е'=>'е',
'Ё'=>'ё',
'Ж'=>'ж',
'З'=>'з',
'И'=>'и',
'Й'=>'й',
'К'=>'к',
'Л'=>'л',
'М'=>'м',
'Н'=>'н',
'О'=>'о',
'П'=>'п',
'Р'=>'р',
'С'=>'с',
'Т'=>'т',
'У'=>'у',
'Ф'=>'ф',
'Х'=>'х',
'Ц'=>'ц',
'Ч'=>'ч',
'Ш'=>'ш',
'Щ'=>'щ',
'Ъ'=>'ъ',
'Ы'=>'ы',
'Ь'=>'ь',
'Э'=>'э',
'Ю'=>'ю',
'Я'=>'я',
/* ВЕРХНИЙ РЕГИСТР БУКВ УКРАИНСКИХ */
'Є'=>'e',
'І'=>'i',
'Ї'=>'ї',
/* Другие символы */
'ñ'=>'n',
'ë'=>'e',
'€'=>'e');
function strtolower_($str='', $check=''){
	
	if($check){// перевод в верхний регистр
		
		$trans_copy = $GLOBALS['SYSTEM']['trans'];// делаем копию массива
		// удаляем ВЕРХНИЙ РЕГИСТР БУКВ УКРАИНСКИХ и Другие символы (дополнительные)
		unset($trans_copy['Є'], $trans_copy['І'], $trans_copy['Ї'], $trans_copy['ñ'], $trans_copy['ë'], $trans_copy['€']);
		$trans_flip = array_flip($trans_copy);// переворачиваем массив
		return strtr($str, $trans_flip);
		
	}else{
		return strtr($str, $GLOBALS['SYSTEM']['trans']);
	}
}
function strtoupper_($str=''){// перевод в верхний регистр
	return strtolower_($str, 'up');
}

// Транслит
function translite($text='', $i=false){
	$s = array(// алфавит
		'а'=>'a',
		'б'=>'b',
		'в'=>'v',
		'г'=>'g',
		'д'=>'d',
		'е'=>'e',
		'ё'=>'yo',
		'ж'=>'j',
		'з'=>'z',
		'и'=>'i',
		'й'=>'y',
		'к'=>'k',
		'л'=>'l',
		'м'=>'m',
		'н'=>'n',
		'о'=>'o',
		'п'=>'p',
		'р'=>'r',
		'с'=>'s',
		'т'=>'t',
		'у'=>'u',
		'ф'=>'f',
		'х'=>'h',
		'ц'=>'c',
		'ч'=>'ch',
		'ш'=>'sh',
		'щ'=>'sh',
		'ъ'=>'i',
		'ы'=>'i',
		'ь'=>'i',
		'э'=>'e',
		'ю'=>'yu',
		'я'=>'ya',
		'є'=>'e',
		'і'=>'i',
		'ї'=>'yi');
	$z = array(// доплнительные символы
		' '=>'-',
		"\t"=>'-',
		"\n"=>'-',
		"\r"=>'-',
		"\f"=>'-',
		'–'=>'-',
		'*'=>'-',
		'+'=>'-',
		'&'=>'-',
		'%'=>'-',
		'$'=>'-',
		'#'=>'-',
		'@'=>'-',
		'!'=>'-',
		'~'=>'-',
		'`'=>'-',
		'“'=>'-',
		'='=>'-',
		'('=>'-',
		')'=>'-',
		'?'=>'-',
		'^'=>'-',
		';'=>'-',
		'№'=>'-',
		'"'=>'-',
		'\''=>'-',
		'>'=>'-',
		'<'=>'-',
		':'=>'-',
		'/'=>'-',
		'|'=>'-',
		'.'=>'-',
		','=>'-',
		']'=>'-',
		'\\'=>'-',
		'['=>'-',
		'«'=>'-',
		'»'=>'-',
		'—'=>'-');
	if($i=='s'){// символы
		$t = &$s;
	}else if($i=='z'){// знаки
		$t = &$z;
	}else{// символы и знаки
		$t = array_merge($s, $z);
	}
	return strtr($text, $t);
}
// Удаление пробелов
function blank($text=''){
	if($text){
		return preg_replace('/[\s]{2,}/ui', ' ', $text);
	}
}
// переводим из  24.08.2007 11:54:29  в  timeshtamp
function time_($date=''){
	if($date){
		$ex=explode(' ', $date);
		$ex_right = explode('.',$ex['0']);
		$ex_left  = explode(':',$ex['1']);
		$r = mktime((int)$ex_left['0'], (int)$ex_left['1'], (int)$ex_left['2'], (int)$ex_right['1'], (int)$ex_right['0'], (int)$ex_right['2']);
		return $r;
	}
}
// добавляем нужный атрибут с описанием к необходимым элементам, у которых этого атрибута нет
function attr($element='', $attribute='', $attribute_value='', $text=''){
	
	if($text && $attribute_value && $element && $attribute){
		
		$text = str_replace($attribute.'=""', '', 
				str_replace($attribute."=''", '', 
				str_replace($attribute.'="\"', '', 
				str_replace($attribute."='\'", '', 
				str_replace($attribute.'=\"', $attribute.'="', 
				str_replace($attribute."=\'", $attribute."='", $text))))));
		
		$replace_modifiers = array("\n","\t","\r","\f");
		
		$attribute_value = str_replace('"', '&quot;', str_replace($replace_modifiers, " ", strip_tags($attribute_value) ) );
		
		preg_match_all('/<'.$element.'.*>/sUi', $text, $found);
		
		foreach($found['0'] as $v){
			if(!mb_stristr($v, $attribute.'=')){// если атрибут отсутствует, например нет alt=
				
				if(mb_substr($v, -3)==' />'){// если окончание тега с слэшем и пробелом ( />)
					$left = mb_substr($v, 0, -2);
					$right = ' />';
				}else{// если окончание тега без слэша (>)
					$left = mb_substr($v, 0, -1);
					$right = '>';
				}
				$text = str_replace($v, $left.' '.$attribute.'="'.$attribute_value.'"'.$right, $text);
			}
		}
	}
	return $text;
}
// Автоматически добавит атрибут title элементам, у которых title нет
function title($data='', $element=''){// Например: title($text, 'a')
	
	if($data && $element){
		
		$data = str_replace(' />', '>', 
				str_replace('title=""', '', 
				str_replace("title=''", '', 
				str_replace('title="\"', '', 
				str_replace("title='\'", '', 
				str_replace('title=\"', 'title="', 
				str_replace("title=\'", "title='", $data)))))));
		
		$replace_modifiers = array("\n","\t","\r","\f");
		
		preg_match_all('/<'.$element.'(.+)>(.*)<\/'.$element.'>/sUi', $data, $found);// (.*) - указано потому, что иногда элемент может быть просто якорем, например <a name="якорь"></a>
		
		foreach($found['1'] as $k=>$v){
			if(!mb_stristr($v, 'title=')){
				$title = trim(str_replace('"', '&quot;', str_replace($replace_modifiers, " ", strip_tags($found['2'][$k]))));
				if($title){
					$data = str_replace($found['0'][$k], '<'.$element.$v.' title="'.$title.'">'.$found['2'][$k].'</'.$element.'>', $data);
				}
			}
		}
	}
	return $data;
}
// перевод числа из байтов в более читабельный вид
function filesize_($size=0, $round=2){
	$type = array('Байт', 'Кб',  'Мб', 'Гб', 'Тб', 'Пб');
	for (; $size>1024; $size=$size/1024){$i++;}
	if(empty($i)){$i='0';}
	//return round($size, $round).' '.noun(round($size,0), $type[$i], $type[$i].'а', $type[$i]);
	return round($size, $round).' '.$type[$i];
}
// конвертирует строку в запрошенную кодировку символов (работает на основе и подобно функции iconv)
// при условии, что на сервере поддерживается данная кодировка, если не поддерживается - не изменяет строку
function charset($str='', $from='', $to=''){
	$chars = Array(
		'k' => 'koi8-r',
		'w' => 'windows-1251',
		'd' => 'x-cp866',
		'i' => 'UTF-8',
		'm' => 'x-mac-cyrillic'
		);
	if($str && $from && $to && $chars[$from] && $chars[$to] && function_exists('iconv')){
		//echo $str.' '.$chars[$from].', '.$chars[$to];
		$n = iconv($chars[$from], $chars[$to], $str);
		
		if(!$n && $from=='w' && $to=='i'){// выполняем еще одну попытку конвертации
			$n = cp1251_utf8($str);
		}
		
		$str = $n? $n : $str;
		
		return $str;
	}else{
		return false;
	}
}
// функция конвертации текста из кодировки cp1251 в кодировку utf8
function cp1251_utf8($str=''){
	$conv=array();
	for($x=128;$x<=143;$x++) $conv[$x+112]=chr(209).chr($x);
	for($x=144;$x<=191;$x++) $conv[$x+48]=chr(208).chr($x);
	$conv[184]=chr(209).chr(145); #ё
	$conv[168]=chr(208).chr(129); #Ё
	$conv[179]=chr(209).chr(150); #і
	$conv[178]=chr(208).chr(134); #І
	$conv[191]=chr(209).chr(151); #ї
	$conv[175]=chr(208).chr(135); #ї
	$conv[186]=chr(209).chr(148); #є
	$conv[170]=chr(208).chr(132); #Є
	$conv[180]=chr(210).chr(145); #ґ
	$conv[165]=chr(210).chr(144); #Ґ
	$conv[184]=chr(209).chr(145); #Ґ
	$ar=str_split($str);
	foreach($ar as $b) if(isset($conv[ord($b)])) $nstr.=$conv[ord($b)]; else $nstr.=$b;
	return $nstr;
}
// определение кодировки символов в строке
function charget($str=''){
	$lowercase = '3';
	$uppercase = '1';
	$charsets = Array(
	'k' => 0,
	'w' => 0,
	'd' => 0,
	'i' => 0,
	'm' => 0
	);
	for ( $i = 0, $length = strlen($str); $i < $length; $i++ ) {
	    $char = ord($str[$i]);
	    /* non-russian characters */
	    if ($char < 128 || $char > 256) continue;

	    /* CP866 */
	    if (($char > 159 && $char < 176) || ($char > 223 && $char < 242))
	        $charsets['d']+=$lowercase;
	    if (($char > 127 && $char < 160)) $charsets['d']+=$uppercase;

	    /* KOI8-R */
	    if (($char > 191 && $char < 223)) $charsets['k']+=$lowercase;
	    if (($char > 222 && $char < 256)) $charsets['k']+=$uppercase;

	    /* WIN-1251 */
	    if ($char > 223 && $char < 256) $charsets['w']+=$lowercase;
	    if ($char > 191 && $char < 224) $charsets['w']+=$uppercase;

	    /* MAC */
	    if ($char > 221 && $char < 255) $charsets['m']+=$lowercase;
	    if ($char > 127 && $char < 160) $charsets['m']+=$uppercase;

	    /* ISO-8859-5 */
	    if ($char > 207 && $char < 240) $charsets['i']+=$lowercase;
	    if ($char > 175 && $char < 208) $charsets['i']+=$uppercase;

	}
	arsort($charsets);
	return key($charsets);
}
// запись данных в лог-файл
function log_($data='', $path_root=''){
	
	//if($GLOBALS['SYSTEM_LOG_PAGE_ID'] && $GLOBALS['system']->id != $GLOBALS['SYSTEM_LOG_PAGE_ID']){ return ; }
	//if(!$GLOBALS['SYSTEM']['user']['user_id'] || $GLOBALS['SYSTEM']['user']['user_id'] != 2){ return ; }
	
	$path = $path_root? $path_root : $_SERVER['DOCUMENT_ROOT'].'/uploads/'.md5($_SERVER['DOCUMENT_ROOT']).'.log';
	if($data){
		if($fp = fopen($path, 'a')){
			fwrite ($fp, "-----------------------------Дата: ".date("H:i:s d.m.Y")."-----------------------------\n".(is_array($data)? print_r($data,1) : $data)."\n");
			fclose ($fp);
			@chmod($path, 0664);
		}else{
			mail_('error-log@gmail.com', '', $GLOBALS['SYSTEM']['config']['yapro_board_contact'], 'System Error Log', 'Не могу открыть файл '.$path);
		}
	}
}
// запись данных в файл (примеры применения: 1. write_('text','/dir/file'); 2. write_('text',$_SERVER['DOCUMENT_ROOT'].'/dir/file');
function write_($data='', $file_path='', $mode='w'){
	if($data!='' && $file_path && !strstr($file_path, './')){
		
		// делаем путь вида от корня сайта /dir/file
		$ex = explode($_SERVER['DOCUMENT_ROOT'], $file_path);
		if($ex['1']){ $file_path = $ex['1']; }
		
		$dir_parent = $_SERVER['DOCUMENT_ROOT'].'/';
		$dirs = explode('/', $file_path);
		for($i=1, $l=(count($dirs)-1); $i<$l; $i++){
			if(!is_dir($dir_parent.$dirs[$i])){
				if(!mkdir($dir_parent.$dirs[$i])){
					log_(__FILE__.' write_: Не могу создать директорию '.$dir_parent.$dirs[$i]);
					$error = 'mkdir'; break;
				}else{
					@chmod($dir_parent.$dirs[$i], 0775);
				}
			}
			$dir_parent .= $dirs[$i].'/';
		}
		if(!$error){
			if($fp = fopen($_SERVER['DOCUMENT_ROOT'].$file_path, $mode)){
				if(!fwrite($fp, $data)){ $error = 'fwrite'; }
				fclose ($fp);
				chmod($_SERVER['DOCUMENT_ROOT'].$file_path, 0775);
			}else{
				$error = 'fopen';
			}
		}
		if($error){
			log_(__FILE__.' write_: '.$error.' проблема cохранения файла - '.$_SERVER['DOCUMENT_ROOT'].$file_path);
		}else{
			return true;// успешная запись
		}
	}
}
// проверяет и при отсутствии досоздает директории в указанной директории, а при нехватке прав возвращает информацию о ошибке (Например: $error = path('/home/user/dir/','uploads/')
function path($root='',$path=''){
	if($root && $path && !strstr($path, './')){
		if(mb_substr($root,-1)!='/'){ $root .= '/'; }
		$dirs = explode('/', $path);
		foreach($dirs as $name){
			if($name){
				if(!is_dir($root.$name)){
					if(!mkdir($root.$name) && !is_dir($root.$name)){
						return (__FILE__.' Не могу создать директорию '.$root.$name);
					}else{
						@chmod($root.$name, 0775);
					}
				}
				$root .= $name.'/';
			}
		}
	}else{
		return (__FILE__.' Неправильный путь: '.$root.$path);
	}
}
/*function path($root='',$path=''){
	if(!$root){
		return __FILE__.' не указан путь';
	}else if($path && strstr($path, './')){
		return __FILE__.' нельзя применять ./';
	}else if($path){
		$root = $root.$path;
	}
	$str = '';
	$dirs = explode('/', $root);
	foreach($dirs as $name){
		if($name){
			$str .= '/'.$name;
			if(!is_dir($str)){
				if(!mkdir($str) && !is_dir($str)){
					return (__FILE__.' Не могу создать директорию '.$str);
				}else{
					@chmod($str, 0775);
				}
			}
		}
	}
}*/
// в тех случаях, когда допущена обишка разработчиком (функция отладки)
function error($text='не указана', $file='', $without_mail=false){
	
	if(!$file && $GLOBALS['SYSTEM']['config']['yapro_file_errors']){ $file = $GLOBALS['SYSTEM']['config']['yapro_file_errors']; }
	
	$error = 'Ошибка на странице: '.$_SERVER['REQUEST_URI']."\n".
	'При переходе с страницы: '.$_SERVER['HTTP_REFERER']."\n".
	($GLOBALS['SYSTEM_SCRIPT']?'SYSTEM_SCRIPT: '.$GLOBALS['SYSTEM_SCRIPT']."\n":'').
	'IP пользователя: '.$_SERVER['REMOTE_ADDR']."\n".
	'Отладочная информация: '.$text."\n".
	(mysql_errno()?"-----------------------MySQL-----------------------\n".mysql_error()."\n":'').
	($_SESSION?"------------------------SESSION----------------------\n".print_r($_SESSION, true):'').
	($_COOKIE?"------------------------COOKIE----------------------\n".print_r($_COOKIE, true):'').
	($_GET?"-----------------------GET-----------------------\n".print_r($_GET, true)."\n":'').
	($_POST?"------------------------POST----------------------\n".print_r($_POST, true)."\n":'').
	($_FILES?"------------------------FILES----------------------\n".print_r($_FILES, true):'');
	
	log_($error,$file);
	
	if($GLOBALS['SYSTEM']['config']['yapro_board_contact'] && !$without_mail
		&& !mail_('error@gmail.com', '', $GLOBALS['SYSTEM']['config']['yapro_board_contact'], 'System Error', str_replace("\n", '<br>', htmlspecialchars($error)), false, 'without_duplicate')){
			log_('не удалось отправить лог ошибки на емэйл: '.$GLOBALS['SYSTEM']['config']['yapro_board_contact']);// если не получилось оптавить
	}
	$return = 'Извините, возникла ошибка программного характера.<br />Программный отдел уже осведомлен и решает данную проблему.<br />Приносим свои извинения за доставленное неудобство.';
	return $_POST['ajax']? str_replace('<br />', " \n", $return) : $return;
}
// Функция собственной обработки php-ошибок
function errorLog($errno=0, $errstr='', $errfile='', $errline=0, $errcontext=''){
	if($errfile!='Unknown' && $errno!='8' && $errno!='2048'){// исключаем замечания и timezone settings
		$errortype = array (
			1   =>  "Ошибка",
			2   =>  "Предупреждение",
			4   =>  "Ошибка синтаксического анализа",
			8   =>  "Замечание",
			16  =>  "Ошбика ядра",
			32  =>  "Предупреждение ядра",
			64  =>  "Ошибка компиляции",
			128 =>  "Предупреждение компиляции",
			256 =>  "Ошибка пользователя",
			512 =>  "Предупреждение пользователя",
			1024=>  "Замечание пользователя"
		);
		if($errno!='2'){// обработка предупреждений
			error($errortype[$errno]."\nФайл: ".$errfile."\nСтрока: ".$errline."\nСообщение: ".$errstr."\nКод с ошибкой: ".$errcontext);
		}
	}
}
// метод преобразования HTML текста в обычный текст
function text($text=''){
	return mysql_escape_string(trim(strip_tags(realslash($text))));
}
/*
function post_text2html($s){
	return mysql_escape_string(str_replace("\r\n", '<br>', trim(strip_tags(realslash($s)))));
}
// Обработка POST HTML текста
function html($s){
	return mysql_escape_string(trim(realslash($s)));
}*/
// Функция удаляет возможное автоматическое эскепирование текста в POST и GET запросах
// не надо применять ее в обработке переменных значения которых вы прописываете скриптах)
function realslash($s=''){
	if($s && ini_get('magic_quotes_gpc')=='1'){// если \ заменяется на \\, производим обратную замену
		$s = str_replace(S_, "\\", str_replace("\\", '', str_replace("\\\\", S_, $s)));
            /*
            1. меняем \\ на ~SuniqueS~
            2. уничтожаем \
            3. меняем ~SuniqueS~ на \
            */
            $s = str_replace(S_, "\\", str_replace("\\", '', str_replace("\\\\", S_, $s)));
		//$s = str_replace('\"', '~~', $s);
			/*
            1. меняем \" на "
            2. уничтожаем \
            3. меняем ~SuniqueS~ на \
            */
	}
	return $s;
}
// Функция эскепирует данные для последующего использования в MySQL-запросе
function mysqlslash($s='', $updateORdelete=false){
	
	if($updateORdelete){
		$s = str_replace("\\", "\\\\\\\\", $s);
	}else{
		$s = str_replace("\\", "\\\\", $s);
	}
	$s = str_replace("'", "''", $s);
	return $s;
}
// Функция Escape-рования запроса
function sql_($s='', $print=false){
	
	$switch_check = strtolower_(mb_substr($s, 0, 6));
	switch ($switch_check){
		
		case 'insert':
			$s = str_replace("\\", "\\\\", $s);
			break;
		
		case 'update':
			$e = explode(_.'WHERE'._, $s);
			if($e['1']){
				$s = str_replace("\\", "\\\\", $e['0']).' WHERE '.str_replace("\\", "\\\\\\\\", $e['1']);
			}else{
				$s = str_replace("\\", "\\\\", $s);
			}
			break;
		
		case 'delete':
			$s = str_replace("\\", "\\\\\\\\", $s);
			break;
		
		default://--select
			//3.x $s = str_replace("\\", "\\\\\\\\\\\\\\", $s);
			$s = str_replace("\\", "\\\\", $s);
	}
	//echo '<!-- '.str_replace(_, "'", str_replace("'", "''", $s)).' -->';
	if($print==='log' || $print===2){
		log_(str_replace(_, "'", str_replace("'", "''", $s)));
	}else if($print){
		echo "<br>".str_replace(_, "'", str_replace("'", "''", $s)).'<br>';
	}
	
	return str_replace(_, "'", str_replace("'", "''", $s));
}
// Функция Escape-рования запроса
function mysql_($s='', $print=null, $ignore_errno=0){
	
	if($print=='only_print'){
		//echo $sql.'<br>'."\n";
		$print = false;
	}
	$e = explode(_, $s);
	if(count($e)>1){
		$sql = sql_($s, $print);
	}else{
		$sql = $s;
		if($print=='log' || $print==2){
			log_($sql);
		}else if($print){
			echo "<br>".$sql.'<br>';
		}
	}
	
	$GLOBALS['SYSTEM']['mysql_errno'] = 0;
	
	$q = mysql_query($sql);
	
	$GLOBALS['SYSTEM']['mysql_errno'] = $errno = mysql_errno();
	
	if($errno && (!$ignore_errno || ($ignore_errno && $errno!=$ignore_errno) ) ){// 1062 - Duplicate
		
		error('mysql_ : '.$errno.' : '.mysql_error()."\n".$sql);
		
	}
	return $q;
}
// возвращает права доступа в виде 0754
function fileperms_($path=''){
    $perms = @fileperms($path);
    return substr(decoct($perms), -4);
    //return substr(decoct($perms), 2);// 10-ричное в 8-ричное
}
// изменяет и при необходимости возвращает bbcode форматированный в html
function bb2html($text='', $nl2br=false){
	$text = trim(strip_tags(str_replace('<!---->', '[!----]', $text)));
	$search = array("'\[url=(https://|http://|)([A-z0-9\.\?\+\-/_=&%#:;]+?)\](.+?)\[/url\]'si", 
					"'\[url=mailto\:([-a-z0-9_\.]+@[-a-z0-9]+\.[-a-z0-9\.]+)\](.+?)\[/url\]'si",
					"'\[color=([A-z0-9#]+?)\](.+?)\[/color\]'si", 
				//	"'\[p=([0-9]+?)\](.+?)\[/p\]'si", 
					"'\[img\]([A-z0-9\.\?\+\-/_=&%#:;]+?)\[/img\]'si");
	
	$replace = array("<a href=\"$1$2\">\\3</a>", 
					"<a href=\"mailto:$1\">\\2</a>", 
					"<span style=\"color:$1\">\\2</span>", 
				//	"<p style=\"padding-left: $1\px;\">\\2</p>", 
					"<img src=\"$1\" border=0>");
	
	$text = preg_replace($search, $replace, $text);
	
	$text = str_replace('[amp]', '&', $text);
	
	$light = array('b'=>'b', 'i'=>'i', 'u'=>'u', 'code'=>'code', 'quote'=>'blockquote', 'ol'=>'ol', 'ul'=>'ul', 'li'=>'li', 'strike'=>'strike', 'p'=>'p', 'div'=>'div', 'br'=>'br', '!----'=>'!----', 'amp'=>'&');
	
	foreach($light as $k=>$v){
		$text = str_replace('['.$k.']', '<'.$v.'>', str_replace('[/'.$k.']', '</'.$v.'>', $text));
		//$search[] = "'\[".$k."\](.+?)\[/".$k."\]'si";
		//$replace[] = "<".$v.">\\1</".$v.">";
	}
	
	if($nl2br){ $text = nl2br($text); }
	
	return $text;
}
// аналог javascript функции unescape в php
function unescape($source='') {
	$decodedStr = "";
	$pos = 0;
	$len = strlen ($source);
	while ($pos < $len) {
		$charAt = substr ($source, $pos, 1);
		if ($charAt=='%') {
			$pos++;
			$charAt = substr ($source, $pos, 1);
			if($charAt=='u'){
				// we got a unicode character
				$pos++;
				$unicodeHexVal = substr ($source, $pos, 4);
				$unicode = hexdec ($unicodeHexVal);
				$entity = "&#". $unicode . ';';
				$decodedStr .= utf8_encode ($entity);
				$pos += 4;
			}else {
				// we have an escaped ascii character
				$hexVal = substr ($source, $pos, 2);
				$decodedStr .= chr (hexdec ($hexVal));
				$pos += 2;
			}
		} else {
			$decodedStr .= $charAt;
			$pos++;
		}
	}
	return $decodedStr;
}// end function
// класс сериализации и десериализации вне зависимости от внутренних знаков
class serializer{
	function serializer(){
		$this->re['s'] = '~~S~~';
		$this->re['q'] = '~~Q~~';
		$this->re['dq'] = '~~DQ~~';
	}
	function serialization(&$a){
		if(is_array($a) && !empty($a)){
			foreach($a as $k=>$v){
				if(!empty($v)){
					if(is_array($v)){
						$this->serialization($a[$k]);
					}else{
						$a[$k] = $this->serializeMe? 
									str_replace('"', $this->re['dq'], str_replace("'", $this->re['q'], str_replace('\\', $this->re['s'], 
									str_replace($this->re['dq'], '', str_replace($this->re['q'], '', str_replace($this->re['s'], '', $v))))))
									:
									str_replace($this->re['dq'], '"', str_replace($this->re['q'], "'", str_replace($this->re['s'], '\\', $v)));
					}
				}else{
					unset($a[$k]);
				}
			}
		}
	}
	function serialize_($a, $name=false){
		if(!$a || empty($a)){ return false; }
		$this->serializeMe = true;
		$this-> serialization($a);
		//$name = ($name && !is_numeric($name))? $name : 'array_not_name_or_initiate_integer_'.$name;
		if(!empty($a)){
			return ($name?'$'.$name.'=':'').'$GLOBALS["serializer"]->unserialize_(\''.serialize($a).'\');';
		}else{
			return '';
		}
	}
	function unserialize_($a){
		if(!$a || empty($a)){ return false; }
		$a = unserialize($a);
		$this->serializeMe = false;
		$this-> serialization($a);
		return $a;
	}
}
$serializer = new serializer();

// функция выводит блок капчи
function captcha($Name=''){
	return '<div style="text-align:center; font-size:11px; padding: 3px;">Введите изображенные цифры:</div>
	<table id="CaptchaTable'.$Name.'" border="0" cellpadding="0" cellspacing="0" style="width:100%">
	    <tr>
	        <td valign="top" width="50%" style="text-align:right;line-height:normal"><img src="'.captchaSrc($Name).'" id="captchaImg'.$Name.'" alt="Captcha" style="cursor:pointer" title="Сменить изображение" onclick="var c=document.getElementById(\'captchaImg'.$Name.'\'); c.src=c.src+\'&nocache=\'+Math.random();"></td>
	        <td valign="top" width="50%" style="text-align:left">
	    		<input name="captcha'.$Name.'" type="text" style="width:70px; font-size:23px" class="border">
	        </td>
	    </tr>
	</table>';
}

// функция возвращает путь к изображению капчи
function captchaSrc($Name=''){ return '/outer/captcha.ru.1.2.6/kcaptcha.php?'.($Name?'captcha='.$Name.'&':'').'nocache='.time().''; }

// функция возвращает блок изображения капчи с кнопкой-ссылкой смены изображения
function captchaImg($Name=''){
	return '<div class="CaptchaImg CaptchaImg'.$Name.'" style="cursor:pointer" title="Сменить изображение" onclick="document.getElementById(\'captchaImg'.$Name.'\').src=document.getElementById(\'captchaImg'.$Name.'\').src+\'&nocache=\'+Math.random();"><img src="'.captchaSrc($Name).'" id="captchaImg'.$Name.'" alt="Captcha"><br>Сменить изображение</div>';
}

// функция проверяет верность введения капчи
function captchaCheck($Name=''){
	if($_POST['captcha'.$Name] && $_SESSION['captcha'.$Name] && $_POST['captcha'.$Name]==$_SESSION['captcha'.$Name]){
		unset($_SESSION['captcha'.$Name]);
		return true;
	}else{
		return false;
	}
}

// функция проверяет пользователя на бота и при необходимости помечает пользователя, как Не Бот
$GLOBALS['notBot'] = 'notBot'.md5($_SERVER['DOCUMENT_ROOT']);
function isBot($captchaName=''){
	if($GLOBALS['SYSTEM']['user']['user_id'] || $_COOKIE[ $GLOBALS['notBot'] ] || captchaCheck($captchaName) ){
		if(!$_COOKIE[ $GLOBALS['notBot'] ]){ cookie($GLOBALS['notBot'], 1, (time()+5555555), '/'); }
		return false;
	}
	return true;
}

// функция проверяет пользователя на бан
function ban(){
	if(F){
		$check = @mysql_fetch_assoc(mysql_("SELECT ban_id FROM ".F."banlist 
		WHERE ban_ip="._.$_SERVER['REMOTE_ADDR']._." AND ( ban_start < "._.time()._." OR ban_start = 0) AND (ban_end > "._.time()._." OR ban_end = 0)
		".($GLOBALS['SYSTEM']['user']['user_id']?"OR ban_userid="._.$GLOBALS['SYSTEM']['user']['user_id']._ : '').""));
		if($check['ban_id']){ return true; }
	}
	return false;
}

// Функции позаимствованные из phpBB3 с измененными именами благодаря префиксу phpbb_
function phpbb3_mail_encode($str=''){
	// define start delimimter, end delimiter and spacer
	$start = "=?UTF-8?B?";
	$end = "?=";
	$spacer = $end . ' ' . $start;
	$split_length = 64;

	$encoded_str = base64_encode($str);

	// If encoded string meets the limits, we just return with the correct data.
	if (strlen($encoded_str) <= $split_length)
	{
		return $start . $encoded_str . $end;
	}

	// If there is only ASCII data, we just return what we want, correctly splitting the lines.
	if (strlen($str) === phpbb3_utf8_strlen($str))
	{
		return $start . implode($spacer, str_split($encoded_str, $split_length)) . $end;
	}

	// UTF-8 data, compose encoded lines
	$array = phpbb3_utf8_str_split($str);
	$str = '';

	while (sizeof($array))
	{
		$text = '';

		while (sizeof($array) && intval((strlen($text . $array[0]) + 2) / 3) << 2 <= $split_length)
		{
			$text .= array_shift($array);
		}

		$str .= $start . base64_encode($text) . $end . ' ';
	}

	return substr($str, 0, -1);
}
function phpbb3_utf8_strlen($text=''){
		// Since utf8_decode is replacing multibyte characters to ? strlen works fine
		return strlen(utf8_decode($text));
}
function phpbb3_utf8_str_split($str='', $split_len = 1){
	if (!is_int($split_len) || $split_len < 1)
	{
		return false;
	}
	
	$len = phpbb3_utf8_strlen($str);
	if ($len <= $split_len)
	{
		return array($str);
	}
	
	preg_match_all('/.{' . $split_len . '}|[^\x00]{1,' . $split_len . '}$/s', $str, $ar);// $/us
	return $ar[0];
}

$GLOBALS['HTML']['increment'] = 0;
$GLOBALS['HTML']['before'] = array();
$GLOBALS['HTML']['after'] = array();
$GLOBALS['HTML']['uniqHTMLSave'] = 'HTMLSave'.mb_substr(_,1);
// ф-я сохраняет заданную строку в массив $GLOBALS['HTML']['before'], добавляет инкремент строки (для ее восстановления) в массив $GLOBALS['HTML']['after'], и возвращает инкремент для замены данной строки
function HTMLSave($s=''){
	// в связи с регуляркой данные поступают в эскепированном виде, поэтому мы их правильно расслэшиваем (оставляя слэши там где нужно)
	$s = str_replace('Save'.S_.'escapes', "\'", stripslashes( str_replace("\'", 'Save'.S_.'escapes', $s)));
	$GLOBALS['HTML']['increment']++;
	$GLOBALS['HTML']['before'][$GLOBALS['HTML']['increment']] = $s;
	return $GLOBALS['HTML']['after'][ $GLOBALS['HTML']['increment'] ] = '['.$GLOBALS['HTML']['uniqHTMLSave'].$GLOBALS['HTML']['increment'].']';
}
// ф-я находит в тексте ИНКРЕМЕНТ строки, и заменяет его на реальные данные строки из массива $GLOBALS['HTML']['before']
function HTMLBack($s=''){
	if($s && $GLOBALS['HTML']['after'] && $GLOBALS['HTML']['before']){
		while($s && strstr($s, '['.$GLOBALS['HTML']['uniqHTMLSave']) && $level<10){
			$level++;
			$s = str_replace($GLOBALS['HTML']['after'], $GLOBALS['HTML']['before'], $s);
		}
	}
	return $s;
}
// возвращает позицию первого вхождения(существования) одной из строк в массиве (без учета регистра)
function strffarray($text='', $array){// string find first in array
	$text = strtolower_($text);
	foreach($array as $v){
		$v = strtolower_($v);
		if(mb_stristr($text, $v)){// пробуем найти строку в строке
			$ex = explode($v, $text);
			$first = mb_strlen($ex['0']);
			return array('str'=>$v, 'first'=>$first, 'last'=>($first+mb_strlen($v)) );
		}
	}
	return false;
}

// возвращает массив $images с полными http-путями к изображениям определенного HTML контекста [для указанного http://site.ru/
function imgSite($html='', $site=''){
	
	if($html){
		
		if(ini_get('magic_quotes_gpc')=='1'){ $html = str_replace("\'", "'", str_replace('\"', '"', $html)); }
		
		if($escape_script = preg_replace('/<script(.+)<\/script>/sUi', '', $html)){
			$html = $escape_script;
		}
		preg_match_all('/<img(.+)src=("|\'|)(.+)>/sUi', $html, $img);
		
		if($img['3']){
			if($site){// если указан адрес сайта - находим путь к директории страницы
				$url = @parse_url($site);
				$site_images = $url['scheme'].'://'.$url['host'];// сайт
				$dirs = explode('/', $url['path']);
				for($i=0; $i<(count($dirs)-1); $i++){
					$path_dir .= '/'.$dirs[$i];
				}
				$page_dir_images = $url['scheme'].'://'.$url['host'].$path_dir.'/';// путь к директории страницы 
			}
			foreach($img['3'] as $k=>$v){
				if($img['2'][$k]){// если путь на изображение обромлен " или '
					$ex = explode($img['2'][$k], $v);
					$v = $ex['0'];
				}
				if(mb_substr($v,0,4)=='http'){// если полный путь к изображению
					$images[] = $v;
				}else if(mb_substr($v,0,1)=='/' && $site_images){// если указан путь от корня сайта
					$images[] = $site_images.$v;
				}else if($page_dir_images){// если указан путь от страницы $site
					$images[] = $page_dir_images.$v;
				}
			}
			return $images;
		}
	}
}
// функция безопасной обработки урл-пути
function parse_url_($url=''){
	if($url){
		if(!mb_stristr($url, '://')){// если это /путь.html?data=x
			$url = 'http://' .$_SERVER['HTTP_HOST'].$url;// создаем полный URL
		}
		$ex = explode('://', $url);// разбиваем урл - protocol://site/путь.html?data=x
		$c=count($ex);
		if($c>1){// если урл правилен и имеет вид - protocol://site/путь.html
			$url = $ex['0'].'://'.$ex['1'];// первым делом создаем protocol://site/путь.html
			if($c>2){// если в пути имеются данные другого урл, то кодируем их
				for($i=2; $i<$c; $i++){
					$url .= urlencode('://'.$ex[ $i ]);
				}
			}
			$arr = @parse_url($url);// парсим правильный урл
			return $arr;// отдаем массив
		}
	}
}
// функция логов используемой ОЗУ памяти в определенный момент
function memory_get_usage_($text=''){
	
	if(!function_exists('memory_get_usage')){ return false; }
	
	$time_end = $GLOBALS['SYSTEM']['time_end'];
	$time = getmicrotime();
	$memory_get_usage = memory_get_usage();
	
	$x0 = $time - $time_end;
	
	$x1 = round($x0, 2);
	
	$x2 = $memory_get_usage - $GLOBALS['SYSTEM']['memory_usage'];
	
	$x3 = round(( $time - $GLOBALS['SYSTEM']['time_start'] ), 2);
	
	$x4 = $memory_get_usage;
	
	$x5 = (function_exists('memory_get_peak_usage'))? memory_get_peak_usage() : '';
	
	if($text){ $text .= "\n"; }
	
	log_($text."затрачено времени на итерацию: ".$x1." сек.\n".
	"использовано памяти на итерацию: ".filesize_($x2)."\n".
	"прошло времени с начала интерпритации: ".$x3." сек.\n".
	"использовано памяти с начала интерпритации: ".filesize_($x4)."\n".
	($x5? "выделено памяти на интерпритацию кода: ".filesize_($x5)."\n" : '').
	$x0."\t\t".$x1."\t\t".$x2."\t\t".$x3."\t\t".$x4."\t\t".$x5);
	
	$GLOBALS['SYSTEM']['memory_usage'] = $memory_get_usage;
}
// функция ведения логов запуска функции класса system
function log_fs($function=''){
	
	//if($GLOBALS['SYSTEM_LOG_PAGE_ID'] && $GLOBALS['system']->id != $GLOBALS['SYSTEM_LOG_PAGE_ID']){ return ; }
	//if(!$GLOBALS['SYSTEM']['user']['user_id'] || $GLOBALS['SYSTEM']['user']['user_id'] != 2){ return ; }
	
	if($function){
		log_($GLOBALS['system']->id.' '.$function.': время от старта: '.(getmicrotime() - $GLOBALS['SYSTEM']['time_start']));
	}
}
// возвращает нужное кол-во слов
function words($text='', $words){
	$i = 0;
	$str = '';
	$a = explode(' ', $text);// разбираем текст по словам
	if($a){
		foreach($a as $word){
			if($i==$words){ break; }
			if($word){ $str .= $word.' '; $i++; }// собираем данные
		}
	}
	return trim($str);
}
/*
функция работает в 3-х режимах:
1. проверка типа файла в базе типов
2. возвращает строку типов файлов разделенных через точку
3. возвращает тип файла
*/
function filetype_($file_path='', $get=false){
	$types = 'art ais ddf dib rle crw thm djv djvu iw4 emf fpx icl icn ico cur ani iff lbm ilbm jpe jif jfif kdc mag pbm pcd pcx dcx pgm pic pct pict pix ppm psd psp ras rsb sgi rgb rgba bw int inta sid tga tif tiff xif ttf ttc wmf xbm xpm ';// Графические. (поддерживаемые в браузерах: gif png bmp jpg jpeg)
	$types .= 'asf asx wpl wm wmx wmd wmz wma wax wmv wvx cda avi wav mpeg mpg mpe mpa mp2 mp3 mpv vob ac3 aac m3u pls mid midi rmi aif aifc aiff au snd ';// Видео, аудио.
	// Архивы, образы дисков.
	$types .= 'rar zip cab arj lzh ace tar gzip uue bz2 jar iso ';// Winrar
	$types .= '7z ';// 7zip
	$types .= 'nrg iso cue bin img ccd sub ';// ultraISO, Nero burning rom
	$types .= 'bkf ';// Архивация Данных из стандартной поставки Windows
	// Документы.
	$types .= 'pdf ';// Adobe reader
	$types .= 'mht mhtml ';// Internet Explorer из стандартной поставки Windows (htm html) 
	$types .= 'txt rtf wri doc ';// WordPad из стандартной поставки Windows
	$types .= 'doc dot olk pab scd wpd wps docs docx ';// Microsoft Word
	$types .= 'prn csv mdb mde iqy dqy oqy rqy wq1 wks dbf xlm xla xlc xlw xls xlt xll xlb slk dif xlk bak xlsx ';// Microsoft Excel
	$types .= 'mdb adp hta asp mda mdw mde ade dbf db tab asc ';// Microsoft Access
	$types .= 'dwt asp aspx js vbs css xsd xsl xslt dtd ';// Microsoft FrontPage
	$types .= 'xsn xsf ';// Microsoft InfoPath
	$types .= 'one ';// Microsoft OneNote
	$types .= 'pst vcf vcs or4 or5 pab sc2 scd ';// Microsoft Outlook
	$types .= 'ppt pps ppa pot pptx ';// Microsoft PowerPoint
	$types .= 'mpp mpd mpt mpw mpx ';// Microsoft Project
	$types .= 'pub ';// Microsoft Publisher
	$types .= 'vsd vdx vss vsx vst vtx vsw svg svgz dwg dxf emz emf ';// Microsoft Visio
	$types .= 'mdi ';// Microsoft DocumentImaging
	// Без сортировки
	$types .= 'ttf fon ';// файлы шрифтов
	$types .= 'exe msi msu';// Файлы программ
	$types .= 'uha qz tgz chm';// другие файлы
	if($file_path){
		$ex_type = array_reverse( explode('.', $file_path));
		$check_type = strtolower_($ex_type['0']);
		if($get){ return $check_type; }
		$ex = explode(' ', $types);
		foreach($ex as $type){
			if($check_type==$type){ return true; }
		}
	}else{
		return str_replace(' ', '.', $types);
	}
}// end function

// класс преобразования php-переменных и массивов в строку
class phpToStr{
	function arr2str($a){
		$i = 0;
		if(is_array($a) && !empty($a)){
			foreach($a as $k=>$v){
				if($v || $v===0 || $v=='0'){
					if($i){ $this->data .= ","; }
					if(is_array($v)){
						$this->data .= $this->quote($this->Association($k))."=>array(";
						$this->arr2str($a[$k]);
						$this->data .= ')';
					}else{
						$this->data .= $this->quote($this->Association($k))."=>".$this->quote($v);//."\n"
					}
					$i++;
				}else{
					unset($a[$k]);
				}
			}
		}
	}
	function quote($str){
		if(isset($str)){
			if($str){
				$str = str_replace("~~S~~", "'", str_replace("'", "\'", str_replace("\'", "\\~~S~~", $str)));// экранируем одиночные кавычки
				if(substr($str,-1)=='\\'){// т.к. в конце строки есть слэш
					preg_match_all('/([\\\]{1,}+)/U', $str, $arr);// находим участки со слэшами
					$arr = array_reverse($arr['1']);// разворачиваем массив, делая последний участок со слэшами - 1ым
					$str = mb_substr($str, 0, -mb_strlen($arr['0'])).str_replace('\\','\\\\',$arr['0']);
				}
			}
			return is_numeric($str)? $str : "'".$str."'";
		}
	}
	function Association($key){
		return $this->associations[$key]? $this->associations[$key] : $key;
	}
	function toStr($data='', $name='without_name', $associations=array(), $only_result=false){
		if($data){
			if(is_array($data)){
				$this->associations = $associations;
				$this->data = 'array(';
				$this-> arr2str($data);
				$this->data .= ')';
			}else{
				$this->data = $this->quote($data);
			}
			return $only_result? $this->data : '$'.$name.'='.$this->data.';';
		}
	}
}
$phpToStr = new phpToStr();

// возвращает ключ -+$pos от текущего значения ключа $key, пример: pos_($arr, 'my_key', -15)
function pos_($array=array(), $key=0, $pos=0, $real=false){
	
	//echo $key.'==';
	$i=0;
	$pos_key = $key_pos = array();
	foreach($array as $k=>$v){
		if(!$i){ $first = $k; }
		$last = $k;
		$i++;
		$pos_keys[$i] = $k;
		$key_pos[$k] = $i;
	}
	if($real){
		$return = $pos_keys[ ($key_pos[$key] + $pos) ];
	}else{
		$return = ($pos>0)? $last : $first;
	}
	//echo $return.'<br>';
	return $return;
}
// ф-я передачи/получения данных по урл через сокет-соединение
function open_url($url='', $get=0, $data=array(), $post=false, $referer=''){
	
	$pu = parse_url($url);
	
	$ex = explode(':',$pu['host']);// проверим наличие указанного порта
	
	if($pu['query']){ $pu['path'] = $pu['path'].'?'.$pu['query']; }// если имеются GET-данные - не теряем их
	
	// открывает сокет соединения домена Internet и если установили связь - проверяем сылку
	if($fp = fsockopen((($pu['scheme']=='https')?"ssl://":'').$pu['host'], (($pu['scheme']=='https')? 443 : ($pu['port']?$pu['port']:80)),$e,$r,5)){
		
		// формируем заголовок на указатель
		$request = ($post?'POST':'GET')." ".trim($pu['path'])." HTTP/1.0\r\n";
		$request .= "Host: ".$pu['host']."\r\n";
		$request .= "User-Agent: PHP Script\r\n";
		if($referer){
			$request .= "Referer: http://".$pu['host']."/\r\n";
		}
		if($data){
			$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: ".strlen($data)."\r\n";
		}
		$request .= "Connection: close\r\n";
		$request .= "\r\n";// в конце параметров запроса всегда должны идти еще один раз \r\n
		
		if($data){
			$data_str = '';
			foreach($data as $k=>$v){
				$data_str .= $data_str? '&' : '';
				$data_str .= $k.'='.str_replace('+','%20', $v);
			}
			$request .= $data_str;
		}
		
		if(fputs ($fp, $request)){// Передаем заголовок на указатель
			$content = '';
			while (!feof($fp)){// проверяет end-of-file/конец файла указателя файла.
				$content .= fgets($fp,1024);// получает строку из указателя на файл, в данном случае получаем 1024 байта
			}
			fclose ($fp);
			if($get){
				$ex_content = explode("\r\n\r\n",$content);// разделяем хедеры и содержание ответа
				if($get==1){// возвращаем только хедеры ответа
					$content = $ex_content['0'];
				}else if($get==2){// возвращаем только содержание ответа
					$content = str_replace($ex_content['0'], '', $content);
				}
			}
			return trim($content);
		}else{
			echo 'нет данных по '.htmlAccess($url).'<br>';
		}
	}else{
		echo 'не могу открыть по '.htmlAccess($url).'<br>';
	}
}
// перевод через гугл. Примеры: echo translate("hello"); С Английского на Немецкий: echo translate("hello", "en|de")
function translate($text='',$lg=''){
	if(!$text || !$lg){ return ; }
	$text = urlencode($text);
	$content = file_get_contents('http://google.com/translate_t?langpair='.urlencode($lg).'&text='.$text);
	if(!$content){ return ; }
	preg_match('/<div id=result_box dir=\"ltr\">(.*?)<\/div>/', $link, $out);
	return $out['1'];
}
// метод правильного определения размера
function time_size($size=0){
	$type = array(' сек.', ' мин.', ' час.', ' дн.', ' мес.', ' год.', ' столет.', ' тысячилет.');
	$uslovie = 0;// сек
	for ($i = 0; abs($size)>$uslovie; $i++){
		if($uslovie){
			$size = ($size/$uslovie);
		}
		//log: echo $size.' | '.$uslovie.' | '.$i.' | '.$type[$i]."\n";
		switch($i){
			case 0:
				$uslovie = 60;// из сек в мин
				break;
			case 1:
				$uslovie = 60;// из мин в час
				break;
			case 2:
				$uslovie = 24;// из час в дни
				break;
			case 3:
				$uslovie = 30;// из дней в мес.
				break;
			case 4:
				$uslovie = 12;// из мес. в года
				break;
			case 5:
				$uslovie = 100;// из год. в стол.
				break;
			case 5:
				$uslovie = 1000;// из год. в стол.
				break;
			default:
				return 'MANY';
		}
	}
	return round($size, 0).$type[$i-1];
}
// Склонение существительных, например echo noun(7, 'яблоко', 'яблока', 'яблок');
function noun($num=0, $str1='', $str2='', $str5='') {
	$num = abs($num);
	if($num > 100) $num %= 100;
	if($num > 20) $num %= 10;
	switch($num) {
		case 1: return $str1;
		case 2: return $str2;
		case 3: return $str2;
		case 4: return $str2;
		default: return $str5;
	}
}
// метод деления текста на части (обычно это анонс И текст, иногда текст для форм заполнения)
// <p><span style="color: red;"><strong><!-- pagebreak --><br /></strong></span></p>
function pagebreak($text='', $return=null){
	if($text){
		$found = preg_split('/(<p><!-- pagebreak --><\/p>|<p><!-- pagebreak -->|<!-- pagebreak --><\/p>|<!-- pagebreak -->)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		if($found){
			$i = 0;
			$a = array();
			foreach($found as $n => $v){
				if(stristr($v, '<!-- pagebreak -->')){
					$ex = explode('<!-- pagebreak -->', $v);
					if($ex['0']=='<p>' && !$ex['1']){
						$a[ $i+1 ] .= $ex['0'];
					}else if(!$ex['0'] && $ex['1']=='</p>'){
						$a[ $i ] .= $ex['1'];
					}
				}else{
					$a[ $i ] .= $v;
					$i++;
				}
			}
			return isset($return)? $a[ $return ] : $a;
		}
	}
}
// UTF-8 доработанный аналог функции ucfirst для применения в именах
function ucfirst_($str=''){
	return $str? trim( strtolower_(mb_substr($str,0,1),'up').mb_substr( strtolower_($str), 1) ) : '';
}
// php аналог javascript функции escape
function javascript_escape($str=''){
	if($str){
		$n = 0;
		$return = '';
		$length = mb_strlen($str);
		while($n<$length){
			$return .= '%'.dechex(ord($str[ $n ]));
			$n++;
		}
		return $return;
	}
}
// возвращает javascript записывающий во вставленном месте нужный контент
function javascript_eval_unescape($str=''){
	if($str){
		return '<script type="text/javascript">eval(unescape("'.javascript_escape("document.write('<a href=".'"mailto:'. $str .'">'. $str .'</a>'."');").'"))</script>';
	}
}
// ф-я возвращает эскепированные данные для использования в JavaScript (обычно необходима прменяется для callback объектов)
// например echo 'field_1:'.ajaxHTML('HTML 1').', "field_2":'.ajaxHTML('HTML 2').', '.ajaxHTML($field_3).':'.ajaxHTML($value);
function ajaxHTML($s='', $quote=true){
	$s = $s? str_replace('"','\"', str_replace("\r",'\r',str_replace("\t",'\t',str_replace("\n",'\n', str_replace('\\','\\\\', $s ) ))) ) : '';
	if($quote){
		$s = '"'.$s.'"';
	}
	return $s;
}
// ф-я создает превью изображения и в случае ошибки возвращает ее, пример применения img_preview('/original.jpg', '/preview.jpg', 150)
function img_preview($path_root='', $path_cache='', $mw=0, $mh=0, $quality=90, $max=false){
	
	if(!$path_root || !$path_cache || (!$mw && !$mh) ){ return 'img_preview: недостаточно данных для работы'; }
	
	if (!function_exists('getimagesize')){ return 'img_preview: ф-я getimagesize не существует'; }
	
	@unlink($path_cache);
	$size = getimagesize($path_root);// определяю параметры изображения
	
	if (!$size){ return 'img_preview: данные файла не найдены'; }
	
	$width  = (int)$size['0'];// настоящая ширина картинки
	$height = (int)$size['1'];// настоящая высота картинки
	
	// автоматическое нахождение максимальных высоты или ширины
	$max_w = (!is_numeric($mw) || $mw>500 || $mw<10)? '100' : $mw;// если ширина меньше 10 и больше 500 - ресайз на 100
	$max_h = (!is_numeric($mh) || $mh>500 || $mh<10)? '120' : $mh;// если высота меньше 10 и больше 500 - ресайз на 120
	
	$format = strtolower(substr($size['mime'],strpos($size['mime'],'/')+1));
	
	$icfunc = 'imagecreatefrom'.$format;
	
	if (!function_exists($icfunc)){ return 'img_preview: ф-я '.$icfunc.' не существует'; }
	
	if($max){// если нужно чтобы пропорции были по наибольшему из указанных $max_w или $max_h:
		
		if ($width > $height && $width > $max_w) {
			$w = $max_w;
			$h = intval($max_w*$height/$width);
		} elseif ($height >= $width && $height > $max_h) {
			$h = $max_h;
			$w = intval($max_h*$width/$height);
		} else {
			$w = $width;
			$h = $height;
		}
		
	}else{// если нужно чтобы пропорции были по наименьшему из указанных $max_w или $max_h:
		
		if($width>$max_w){
			$w = $max_w;
			$h = ($w*$height)/$width;
		}else{
			$w = $width;
			$h = $height;
		}
		if($h>$max_h){
			$h = $max_h;
			$w = ($h*$width)/$height;
		}
	}
	
	$isrc = $icfunc($path_root);// читаем файл
	
	// создаем пустое изображение с размерами  $mw * $mh
	if (function_exists('imagecreatetruecolor')) {
		$idest = imagecreatetruecolor($w,$h);
	} else  {
		$idest = imagecreate($w, $h);
	}
	// копирует и изменяет размеры части изображения с пересэмплированием
	if (function_exists('imagecopyresampled')) {
		imagecopyresampled($idest,$isrc,0,0,0,0,$w,$h,$width,$height);
	} else {
		imagecopyresized($idest,$isrc,0,0,0,0,$w,$h,$width,$height);
	}
	// сохраняем изображение с качеством $quality%
	$isfunc = 'image'.$format;
	
	if (!function_exists($isfunc)){ return 'img_preview: ф-я '.$isfunc.' не существует'; }
	
	@$isfunc($idest,$path_cache,$quality);
	
	if(!is_file($path_cache)){
		return 'img_preview: не удалось сохранить файл '.$path_cache;
	}else if(filesize($path_cache)<100){
		@unlink($path_cache);
		return 'img_preview: файлу не может быть выполнен ресайз';
	}
}
// отдает данные по-умолчанию преобразуя хтмл-символы в мнемоники и делая безопасным использование данных пользователей
function htmlAccess($str='', $htmlspecialchars=true, $safe=true){
	if($str){
		if($htmlspecialchars){
			$str = str_replace('$','&#036;', htmlspecialchars($str));
		}
		if($safe){// тильду обрабатываем после htmlspecialchars т.к. если перед, то <!----> превратится в мнемоники
			$str = str_replace('{~', '{<!---->~', $str);
		}
		return $str;
	}
}
// генерирует уникальный строковый (не числовой) идентификатор длинной от 29 до 33 символов
function uniq(){
	return mb_substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 33);
}
// отдает дату времени timeshtamp с микросекундами (данные числовые, не строковые) длинной 18 символов
function uniqtime(){
	$t = explode(" ",microtime());
	$s = explode('.',$t['0']);
	return $t['1'].$s['1'];
}
// хороший подход для работы с реальными кавычками (найден на http://webew.ru/posts/785.webew)
function kill_magic_quotes(&$str){
	if(is_array($str)){
		while(list($key, $val) = each($str)){
			$str[$key] = kill_magic_quotes($val);
		}
	}else{
		$str = stripslashes($str);
	}
	return $str;
}
if(get_magic_quotes_gpc()){
    kill_magic_quotes($_GET);
    kill_magic_quotes($_POST);
    kill_magic_quotes($_COOKIE);
    kill_magic_quotes($_REQUEST);
}
function yapro_page_cache_url($url=''){
	if($url){
		$r = parse_url_($url);
		if($r['path']){
			
		}
	}
}
// Сайт. Полнотекствое кэширование страниц - метод очистки кэша заданной страницы
function yapro_page_cache($page_id=0){
	if($page_id){
		$path = $_SERVER['DOCUMENT_ROOT'].'/cache/pages/';
		if($dir = @dir($path)){
			while ($file = $dir-> read()){
				if($file != "." && $file != ".."){
					$ex = explode('.', $file);
					if($ex['0']==$page_id){
						@unlink($path.$file);
					}
				}
			}
		}
	}
}
$GLOBALS['SYSTEM']['url_encode'] = array();
function url_encode($r=array(), $cache=true){ // log_fs('url_encode');
	if($r['page_id'] && $r['url_type']<3){
		if( isset($GLOBALS['SYSTEM']['url_encode'][ $r['page_id'] ]) && $cache ){
			$r['url'] = $GLOBALS['SYSTEM']['url_encode'][ $r['page_id'] ];
		}else{
			if($GLOBALS['rurl']){
				$r['url'] = $GLOBALS['rurl']-> urlencode_($r['url']);
			}else{
				$r['url'] = str_replace('%2F','/', rawurlencode($r['url']));
			}
			$GLOBALS['SYSTEM']['url_encode'][ $r['page_id'] ] = $r['url'];
		}
	}
	return $r;
}
// находим URL-адрес страницы по данным страницы или page_id
$GLOBALS['SYSTEM']['page_url'] = array();
function url($page_id=null, $type_data=0, $cache=true){
	
	if(!$page_id){ $page_id = $GLOBALS['system']->id; }
	
	if(!$page_id){ error('$page_id empty : '.__FILE__); return ; }
	
	if(is_array($page_id)){// если массив данных - создаем из них один урл
		
		$r = $page_id;
		
		return $r['url'].( ($r['url_type']<2)? $GLOBALS['SYSTEM']['config']['yapro_page_ext'] : '');
		
	}else{// несколько ИД,ИД,ИД
		$in = '';
		$empty = true;
		$a = $in_a =array();
		$e = explode(',',$page_id);
		foreach($e as $id){
			if(!$id){ continue; }
			if(isset($GLOBALS['SYSTEM']['page_url'][ $id ]['url']) && $cache){
				$a[ $id ] = $GLOBALS['SYSTEM']['page_url'][ $id ];
			}else{
				$in .= $id.',';
				$in_a[ $id ]++;
			}
			$empty = false;
		}
		if($empty){
			return ;
		}
		if($in && $q = mysql_('SELECT page_id, url, url_type FROM '.P.'pages_url WHERE page_id IN ('.substr($in,0,-1).')')){
			while($r = mysql_fetch_assoc($q)){
				$GLOBALS['SYSTEM']['page_url'][ $r['page_id'] ] = $r;
				$GLOBALS['SYSTEM']['page_url'][ $r['page_id'] ]['real_url'] = $r['real_url'] = $r['url'];
				$r = url_encode($r, $cache);
				$GLOBALS['SYSTEM']['page_url'][ $r['page_id'] ]['url'] = $r['url'];
				$a[ $r['page_id'] ] = $GLOBALS['SYSTEM']['page_url'][ $r['page_id'] ];
				unset($in_a[ $r['page_id'] ]);
				/* следующий метод не подходит, т.к. нужно знать например имя директории для главной странице /page
				if($r['page_id']==$GLOBALS['SYSTEM']['config']['yapro_index_id']){
					$GLOBALS['SYSTEM']['page_url'][ $r['page_id'] ]['url'] = '/';
				}*/
			}
			/*if($in_a){
				foreach($in_a as $id => $true){
					$r = array('page_id'=>);
				}
			}*/
		}
		if(is_numeric($page_id)){
			$r = $a[ $id ];
			if($type_data===4){
				return $r['real_url'];
			}else if($type_data===3){
				return $r['url'];
			}else if($type_data===2){
				return $r;
			}else if($type_data===1){
				return $r['url_type'];
			}else{
				return $r['url'].( ($r['url_type']<2)? $GLOBALS['SYSTEM']['config']['yapro_page_ext'] : '');
			}
		}else if(count($a)>0){
			return $a;
		}
	}
}
// Метод выводит необходимые хедеры
function headers($data=array()){
	
	// регулировка кэширования в браузерах
	$ETag = 'ETagHash';//.md5($_SERVER['REQUEST_URI']);
	if(!$_COOKIE[$ETag] || $_SERVER['REQUEST_METHOD']=='POST'){// устанавливается в первый раз при заходе на страницу
		$etag_hash = md5(time());
		setcookie($ETag, $etag_hash, (time()+86400), '/');
	}else{
		$etag_hash = $_COOKIE[$ETag];
	}
	header('ETag: "'.str_replace('"','\"',$etag_hash).'"');
	
	@header('Content-type:'.($data['Content-type']? $data['Content-type'] : 'text/html').'; charset=UTF-8');// кодировка страницы
	
	// Время кэширования (дата времени с которого начинается кэширование со стороны браузера)
	@header("Expires: ".gmdate("D, d M Y H:i:s", time() )." GMT");
	
	// количество времени кэширования страницы со стороны браузера (в секундах)
	@header("Cache-Control: post-check=0,pre-check=0");
	
	// Дата последнего изменения страницы
	$time_modified = $data['Last-Modified']? $data['Last-Modified'] : $GLOBALS['system']->pages[ $GLOBALS['system']->id ]['time_modified'];
	@header("Last-Modified: ".gmdate("D, d M Y H:i:s", ($time_modified? $time_modified : (time()-15000) ) )." GMT");
	
	// Если страница скрыта с выдачей заголовка 404
	if($GLOBALS['system']->pages[ $GLOBALS['system']->id ]['access_inherited']=='8'){ header("HTTP/1.0 404 Not Found"); }
	
}
// создает правильный запрос для выбора всех данных строк с учетом проверки доступа пользователя и времени публикации
function sql($select='', $where='', $orderby='', $limit_start='', $limit_end='', $with_hidden=false){
	if(mb_substr($orderby,-1)==','){ $orderby = mb_substr($orderby,0,-1); }
	$return = 'SELECT '.($select? $select : '*').' FROM '.P.'pages 
	WHERE '.sql_where('', $where, $with_hidden).
	'ORDER BY '.($orderby? $orderby : ($GLOBALS['SYSTEM']['config']['yapro_time_created']? 'time_created DESC' : 'page_id DESC')).
	(($limit_start || $limit_end)? ' LIMIT '.$limit_start.($limit_end? ','.$limit_end:'') : '');
	//log_($return);
	return $return;
}
// создает правильное условие для стандартного SQL-запроса, при выборе данных строк с учетом проверки доступа пользователя и времени публикации
function sql_where($dbtable='', $where='', $with_hidden=false){
	return ' ('.$dbtable.'access_inherited<='.(int)$GLOBALS['SYSTEM']['user']['access'].
	($with_hidden? ' OR '.$dbtable.'access_inherited IN(7,8) ' : '').') 
	'.($GLOBALS['SYSTEM']['config']['yapro_time_created']? 'AND '.$dbtable.'time_created<'.time() : '').($where? ' AND '.$where : '').' ';
}
// возвращает тип файла в нижнем регистре
function ext($file=''){
	if($file){
		$ex_type = array_reverse(explode('.', $file));
		return strtolower_($ex_type['0']);
	}
}
// возвращает массив с 2 ключами => значениями: путь к файлу (имени) и тип файла в нижнем регистре
function path_ext($path=''){
	$path = '';// находим имя файла
	$e = explode('.', $path);
	$type = array_reverse($e);// находим расширение файла
	array_pop($e);
	foreach($e as $part){
		if($part){
			$path .= $part.'.';
		}
	}
	return array('path'=>mb_substr($path,0,-1), 'ext'=>$type['0']);
}
// возвращает урл без системного расширения
function url_without_ext($url=''){
	if($url && $GLOBALS['SYSTEM']['config']['yapro_page_ext']){// если имеется расширение страниц
		$ex = explode($GLOBALS['SYSTEM']['config']['yapro_page_ext'], $url);
		$url = $ex['0'];//избавляемся от расширения
	}
	return $url;
}
// возвращает окончание слова в зависимости от пола по имени пользователя, например: echo respect('Барабанова').' Алиса';
function respect($name=null){
	if($name==0){
		return 'Здравствуйте';
	}else if(is_numeric($name)){
		return 'Уважаем'.(($name==2)? 'ая':'ый');
	}else if($name){
		return 'Уважаем'.((mb_substr($name,0-1)=='A' || mb_substr($name,0-1)=='a' || mb_substr($name,0-1)=='А' || mb_substr($name,0-1)=='а')? 'ая':'ый');
	}else{
		return 'Уважаемый/ая';
	}
}
// очищает текст от кода, который непозволителен в выводе атрибутов HTML-тегов и мнемонизирует его (при необходимости мнемонизацию можно отключить
function clear($text='', $dont_htmlspecialchars=false){
	//log_($dont_htmlspecialchars.'=='.$text);
	if(!$text){ return ; }
	
	// формируем текст
	if(strstr($text,'{~') && strstr($text,'~}')){
		$text = preg_replace('/\{\~(.+)\~\}/sUi', ' ', $text);
	}
	if(strstr($text,'<') && strstr($text,'>')){
		$close_tags = array("'<\!\-\-(.+)\-\-\>'sUi", "'<noindex(.+)noindex>'sUi", "'<style(.+)style>'sUi", "'<script(.+)script>'sUi", "'<(.+)>'sUi");
		$text = strip_tags( preg_replace($close_tags, ' ', $text) );
	}
	
	$replace_modifiers = array("\n","\t","\r","\f");
	
	// очищенный текст
	$text = str_replace($replace_modifiers, ' ', str_replace('&nbsp;', ' ', $text) );
	
	$text = $dont_htmlspecialchars? $text :  htmlspecialchars( $text );
	
	$text = blank($text);// удяляем лишние пробелы
	
	return $text;
}
if(!function_exists('mb_stristr')){// для поддержки php < 5
	function mb_stristr($haystack='', $needle=''){
		return stristr($haystack, $needle);
	}
}
// функция разработанная для того, чтобы приводить строки в целые числа, сделана т.к. при (int) максимально-возможное число: 2147483647
function int($s=''){
	return $s? preg_replace('/[^\d]/','',$s) : 0;
}
// ф-я разработана в замен ф-и is_numeric() которая 1e309 считала числом
function isint($n=0){
	if($n===0 || int($n)===$n){
		return 1;
	}
}
?>