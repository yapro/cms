<?php
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// подгружаю конфигурационный файл

$x = explode($GLOBALS['SYSTEM']['config']['yapro_http_host'], $_SERVER['HTTP_HOST']);
$x = $x['1']? count(explode('.',$x['1']))-1 : 0;
define('HTTP_HOST_DOTS', $x);// определяем уровень глубины субдомена

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// подгрузка необходимых функций
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/Browser.php');// подгрузка необходимых функций
$GLOBALS['SYSTEM']['user']['access'] = 0;// права доступа пользователя по-умолчанию (временно)
if(!$GLOBALS['system_include']){
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');// интеграция с форумом
}
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/cookies.php');// подгрузка необходимых cookies функций

if(HTTP_HOST_DOTS > 1){// если домен 4го или более уровня
	header404('HTTP_HOST_DOTS > 1 : '.__FILE__);
}

if(count(explode('xn--',$_SERVER['HTTP_HOST']))>1){// поддержка кирилических доменов
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/idna_convert.class.php');
	$IDN = new idna_convert();
	$_SERVER['HTTP_HOST_NAME'] = $IDN->decode($GLOBALS['SYSTEM']['config']['yapro_http_host']);
}else{
	$_SERVER['HTTP_HOST_NAME'] = $GLOBALS['SYSTEM']['config']['yapro_http_host'];
}

if($GLOBALS['SYSTEM']['config']['yapro_site_closed'] && $GLOBALS['SYSTEM']['user']['user_rank']!=1){// Сайт. Закрыть файлом-заглушкой
	@header("HTTP/1.0 500 Internal Server Error");
	echo file_get_contents($GLOBALS['SYSTEM']['config']['yapro_site_closed']);
	exit;
}

@include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude.php');// подгрузка необходимых php-файлов

class system {
	
	function system(){// log_fs('system');
		
		$this->replace_modifiers = array("\n","\t","\r","\f");// заменяемые модификторы используемые в большинстве случаев
		
		$this->ru['month'] = array('1'=>'января', '2'=>'февраля', '3'=>'марта', '4'=>'апреля', '5'=>'мая', '6'=>'июня', '7'=>'июля', '8'=>'августа', '9'=>'сентября', '10'=>'октября', '11'=>'ноября', '12'=>'декабря');
		
		$this->ru['day'] = array('0'=>'воскресенье', '1'=>'понедельник', '2'=>'вторник', '3'=>'среда', '4'=>'четверг', '5'=>'пятница', '6'=>'суббота', '7'=>'воскресенье');
		
		// определение возможности сжатия документа
		if($GLOBALS['SYSTEM']['config']['yapro_gzip_compress'] && function_exists('gzencode') && extension_loaded('zlib')){
			if(stristr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
				$this->accept_encoding = 'gzip';
				$this->gzip = true;
			}else if(stristr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate')){
				$this->accept_encoding = 'deflate';
				$this->deflate = true;
			}
		}
		
		$file = $_SERVER['DOCUMENT_ROOT'].'/cache/pages_fields.php';// формируем имя кэш-файла
		if(is_file($file)){// если кэш существует
			include_once($file);// подгружаем кэш-данные
		}else{
			if($q=mysql_query('SHOW COLUMNS FROM '.P.'pages')){// находим существующие поля в таблице $page_table
				while($r=mysql_fetch_assoc($q)){
					$this->fields[ $r['Field'] ]++;
				}
			}
			write_('<?php '.$GLOBALS["phpToStr"]->toStr($this->fields, 'this->fields').' ?>', $file );// записываем данные в кэш-файл
		}
	}
	function url_cache($md5=''){
		write_($this->id, $_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/'.$md5);
		write_($this->id, $_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/'.md5($_SERVER['REQUEST_URI']));
	}
	// выполняет include_once всех файлой заданной директории : $this-> includeDir($_SERVER['DOCUMENT_ROOT'].'/inner/afterContent/');
	function includeDir($path=''){// log_fs('includeDir('.$path.')');
		if($path && $dir = @dir($path)){
			while($file = $dir->read()){
				if(!is_dir($path.$file) && mb_substr($file, -4)=='.php'){
					include_once($path.$file);
				}
			}
			$dir->close();
		}
	}
	// создает правильный запрос для выбора всех данных строк с учетом проверки доступа пользователя и времени публикации
	function sql($select='', $where='', $orderby='', $limit_start='', $limit_end='', $with_hidden=false){// log_fs('sql');
		return sql($select, $where, $orderby, $limit_start, $limit_end, $with_hidden);
	}
    // находит данные поля field_name определенного page_id
	function data($field_name='', $page_id=0, $parent=0){// log_fs('data('.$field_name.', '.$page_id.', '.$parent.')');
		
		// log_('data: попытка найти данные по: field_name='.$field_name.' page_id='.$page_id);
		
		if(!$field_name || !is_numeric($page_id)){
			// log_('data: условие 1 не выполнено: '.$page_id.' | '.$this->fields[ $field_name ].' | '.print_r($this->fields,1));
			return ;
		}
		
		if(!$page_id){ $page_id = $this->id; }// если ID не указан, значит находим данные текущей страницы
		
		// log_('data: page_id='.$page_id.' данные: '.$this->pages[ $page_id ][ $field_name ].' проверка: '.$this->dataChecked[ $page_id ]);
		
		if(!$this->pages[ $page_id ][ $field_name ] && !$this->dataChecked[ $page_id ] && $this->fields[ $field_name ]){
			
			$this->dataChecked[ $page_id ] = true;// ставлю отметку о том, что уже пробовал искать заданные данные
			
			// log_('data: sql='.sql('', 'page_id='.$page_id, '', '', '', true));
			
			$this->pages[ $page_id ] = @mysql_fetch_assoc(mysql_query( sql('', 'page_id='.$page_id, '', '', '', true) ));
			
		}
		
		if(!$this->pages[ $page_id ][ $field_name ]// если данные не найдены
		&& $parent// если указано выполнить поиск у родительских страниц
		&& $this->pages[ $page_id ]['parent_id']// если parent_id существует
		&& $this->fields[ $field_name ]){// если такое поле существует
			// log_('data: выполняю попытку найти данные у родительской страницы');
			$this->pages[ $page_id ][ $field_name ] = $this-> data($field_name, $this->pages[ $page_id ]['parent_id'], $parent);
		}
		// log_('data: для page_id='.$page_id.' имеются данные: '.print_r($this->pages[ $page_id ],true));
		// log_('data: данные '.($this->pages[ $page_id ][ $field_name ]?'':'НЕ ').'найдены');
		if($field_name==='url' && !$this->pages[ $page_id ][ $field_name ]){
			$this->pages[ $page_id ][ $field_name ] = url($page_id,3);
		}
		
		return $this->pages[ $page_id ][ $field_name ];
	}
    // находит комментарий поля field_name если существуют данные определенного page_id
	function comment($field_name='', $page_id=0){// log_fs('comment');
		
		// log_('comment: попытка найти данные по: field_name='.$field_name.' page_id='.$page_id);
		
		if(!$field_name || !$this->fields[ $field_name ] || !is_numeric($page_id)){ return ; }
		
		if(!$page_id){ $page_id = $this->id; }// если ID не указан, значит находим данные текущей страницы
		
		if(!isset($this->columnComment)){
			
			$this->columnComment = array();// ставлю отметку о том, что уже пробовал искать заданные данные
			
			if($q = mysql_query("SHOW FULL COLUMNS FROM ".P."pages")){
				while($r = mysql_fetch_assoc($q)){
					$this->columnComment[ $r['Field'] ] = $r['Comment'];
				}
			}
		}
		if($this->columnComment[ $field_name ] && $this->pages[ $page_id ][ $field_name ]){
			return $this->columnComment[ $field_name ];
		}
	}
	// возвращает полный путь к файлу (вспомогательная ф-я для ф-ии подобной content)
	function path($file_path=''){// log_fs('path('.$file_path.')');
		if(!$file_path){ return ''; }
		if(mb_substr($file_path,0,1)=='/' || mb_substr($file_path,1,2)==':/'){// второй случай для Денвера Z:/dir/file
			return $file_path;
		}else{
			return $_SERVER['DOCUMENT_ROOT'].'/'.$file_path;
		}
	}
	// интерпритирует пхп-код и затем подключает пхп-файл
	function php($system_name){// log_fs('php('.$system_name.')');
		$return = null;
		ob_start();
		$system_filename = $system_name;
		$system_ex_code = array_reverse(explode('~AND~', $system_name));
		if($system_ex_code['1']){
			$return = eval(' ?><?php '.$system_ex_code['1'].' ?><?php ');
			$system_filename = $system_ex_code['0'];
		}
		$system_filename = (strstr($system_filename,'/')? '' : 'inner/').$system_filename;
		$system_filename = $this-> path($system_filename);
		if(is_file($system_filename)){
			include($this-> path($system_filename));
		}
		$document .= ob_get_contents();
		ob_end_clean();
		if($return === false && function_exists('error_get_last') && ( $error = error_get_last() )){
			errorLog($error['type'], $error['message'], $error['file'], $error['line'], $system_ex_code['1']);
			return '---error:see_log_file---';
		}
		return $document;
	}
	// возвращает содержимое хтмл-файла
	function html($system_name){// log_fs('html('.$system_name.')');
		return @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/templates/'.$system_name);
	}
	// интерпритирует пхп-код
	function code($system_name){// log_fs('code('.$system_name.')');
		$return = null;
		ob_start();
		$return = eval(' ?><?php '.substr($system_name, 0, -2).' ?><?php ');
		$document .= ob_get_contents();
		ob_end_clean();
		if($return === false && function_exists('error_get_last') && ( $error = error_get_last() )){
		    errorLog($error['type'], $error['message'], $error['file'], $error['line'], $system_name);
			return '---error:see_log_file---';
		}
		return $document;
	}
	// подключает файл
	function include_($file=''){// log_fs('include_('.$file.')');
		if(!$file){ return ; }
		ob_start();
		include($this-> path($file));
		$document = ob_get_contents();
		ob_end_clean();
		return $document;
	}
	// проверка и вставка по найденному
	function content(&$system_document='', $system_level=0){// log_fs('content');
		
		if(!$system_document){ return ''; }
		
		//$this->system_content[ $system_level ] = $system_document;
		
        // находим то, что между {~ и ~} то есть находим имена файлов в которых тоже надо порыться
        preg_match_all('/(?<={~).*?(?=~})/si', $system_document, $system_found_names);
		
        if($system_found_names['0']){
        	
        	// log_fs('content: '.print_r($system_found_names['0'],1));
			
            foreach($system_found_names['0'] as $system_name){// пробегаем по массиву
				
				$system_ex = array_reverse( explode('.', $system_name) );
				
				$system_processing = $system_ex['0'];
				
				switch ($system_ex['0']){// если заканчивается на:
					case 'html':// шаблон
						
						$document = $this-> html($system_name);
						break;
						
					case 'php':// скрипт ИЛИ пхп-код~AND~скрипт
						
						$document = $this-> php($system_name);
						break;
						
					case '$':// вычисляем строку как PHP-код
						
						$document = $this-> code($system_name);
						break;
						
					default:
						
						if(mb_substr($system_name, 0, 1)=='$'){// php-переменная
							
							if(!$this->depth[$system_name] || $this->depth[$system_name]<7){// 7 - максимальный уровень вложенности
								
								eval('$document = '.$system_name.';');
								
								if(!$document){// даем возможность проверить данный код в более глубоком уровне вложенности
									$document = '{~'.$system_name.'~}'; 
									$this->depth[$system_name]++;
								}
							}
							
						}else{// просто выставляем пустое значение
							$document = '';
						}
				}
				$system_document = str_replace('{~'.$system_name.'~}', $document, $system_document);
				//log_fs("{~".$system_name."~}");
				/*
				log_("Уровень вложенности: ".$level.
				"\n--------------\n".
				"Проверка кода:\n{~".$system_name."~}".
				"\n----------------------\n".
				"Получаемое содержание:\n".$document.
				"\n------------------------\n".
				"Cодержание после замены:\n".$system_document);
				*/
				$system_ex = $document = '';
			}
			if($system_level>25){
				$system_document .= '<h1 style="color: #FF0000">Внимание: уровень вложенности привысил отметку 25</h1>';
			}else if(stristr($system_document, '{~')){
				$system_level++;
				$this-> content($system_document, $system_level);
			}
        }
    }
    // очищает текст от кода, который непозволителен в выводе атрибутов HTML-тегов и мнемонизирует его (при необходимости мнемонизацию можно отключить)
	function clear($text='', $dont_htmlspecialchars=false){// log_fs('clear');
		return clear($text, $dont_htmlspecialchars);
	}
    // очищает цепочку ключевых фраз
	function clear_keywords($text=''){// log_fs('clear_keywords');
		return $text? str_replace(array(' (',')', '.', '=', '?', '!'), '', str_replace('.,', ',', str_replace(',,', ',', str_replace(':,', ',', $text)))) : '';
	}
    // очищает цепочку ключевых фраз
	function description($t=''){// log_fs('clear_keywords');
		$e = explode(' ', $t);// разбираем текст по словам
		foreach($e as $w){
			$w = trim($w);
			if($w){
				$s .= $w.' ';
				if(mb_strlen($s)>259){ break; }
			}
		}
		return $s;
	}
    // метод формирует переменные мета-данных
    function meta(){// log_fs('meta');
		
		if(!$GLOBALS['SYSTEM']['config']['yapro_autotitle'] && !$GLOBALS['SYSTEM']['config']['yapro_autokeywords'] && !$GLOBALS['SYSTEM']['config']['yapro_autodescription']){ return ; }
		
		if(!$this->systemMeta){// если мета-данные не указаны - находим их по содержанию
			preg_match_all('/<!--MetaData-->(.+)<!--\/MetaData-->/sUiu', $this->document, $meta);
			if($meta['1']){
				foreach($meta['1'] as $v){
					if($v){ $this->systemMeta .= $v.' '; }
				}
			}
		}
		
		$text = $this-> clear($this->systemMeta);
		
		$max = (int)$GLOBALS['SYSTEM']['config']['yapro_title_max_words'];
		$max = $max? $max : 25;
		
		if($text){
			
            $word = explode(' ', $text);// разбираем текст по словам
            
            $x = $kw_i = 0;
            for($i=0; ($i<100 || $x<500); $i++){// собираем данные
				
				$w = trim($word[$i]);
				
				if($w){
					
					if($i<=$max && (!$GLOBALS['browser']->msie || mb_strlen($title.$w, 'utf-8')<128)){ $title .= $w.' '; }// 127 символов - максимум который IE может поместить в Избранное
					
					if($kw_i<=50){
						$kw = $this-> clear_keywords($w);
						if(mb_strlen($kw)>1){
							$kw_i++;
							$keywords .= $kw.', ';
						}
					}
					
					if(mb_strlen($description)<259){ $description .= $w.' '; }
				}
				$x++;
            }
		}
		
		$titleData = $this-> data('title');
		if(empty($titleData) && $this-> data('name')){ $titleData = $this-> data('name'); }
		if($titleData){ $titleData = $this-> clear( $titleData ); }
		if($GLOBALS['SYSTEM']['config']['yapro_autotitle'] && !$titleData){ $titleData = trim($title); }
		if($GLOBALS['SYSTEM']['title_prefix']){ $titleData = $GLOBALS['SYSTEM']['title_prefix'].$titleData; }
		$titleData = str_replace('&amp;', '&', $titleData);
		if(mb_substr($titleData,-1)=='\\'){ $titleData .= ' '; }// чтобы в HTML не заслэшивать закрывающую кавычку
		$this->document = str_replace('[~title~]', $titleData, $this->document);
		
		$descriptionData = $this-> data('description');
		if($descriptionData){ $descriptionData = trim($this-> clear( $descriptionData )); }
		if($GLOBALS['SYSTEM']['config']['yapro_autodescription'] && !$descriptionData){ $descriptionData = trim($description); }
		if($GLOBALS['SYSTEM']['description_prefix']){ $descriptionData = $GLOBALS['SYSTEM']['description_prefix'].$descriptionData; }
		if(mb_substr($descriptionData,-1)=='\\'){ $descriptionData .= ' '; }// чтобы в HTML не заслэшивать закрывающую кавычку
		$this->document = str_replace('[~description~]', $descriptionData, $this->document);
		
		// если keywords не задан
		$keywordsData = $this-> data('keywords');
		if($keywordsData){ $keywordsData = $this-> clear( $keywordsData ); }
		if($GLOBALS['SYSTEM']['config']['yapro_autokeywords'] && !$keywordsData){
			$tags = trim($this-> data('tags'));
			if($tags){
				if(mb_substr($tags,-1)!=','){ $tags .= ','; }
				$tags = str_replace(',  ', ', ', str_replace(',', ', ', $tags));
			}
			if(!$keywords){ $keywords = ','; }
			$keywordsData = mb_substr(trim($tags.$keywords), 0, -1);// удаляем последнюю запятую
		}
		if($GLOBALS['SYSTEM']['keywords_prefix']){ $keywordsData = $GLOBALS['SYSTEM']['keywords_prefix'].$keywordsData; }
		$keywordsData = preg_replace("'\&(.+)\;'sUi", '', $this-> clear_keywords($keywordsData));
		if(mb_substr($keywordsData,-1)=='\\'){ $keywordsData .= ' '; }// чтобы в HTML не заслэшивать закрывающую кавычку
		$this->document = str_replace('[~keywords~]', $keywordsData, $this->document);
    }
    // Метод сжатия документа
	function zip(&$document){// log_fs('zip');
		// специально НЕ используется функция mb_strlen
		if($GLOBALS['SYSTEM']['config']['yapro_gzip_compress'] && $this->accept_encoding && strlen($document)>1024){
			if($this->accept_encoding=='gzip'){
				header('Content-Encoding: gzip');
				$document = gzencode($document, 9, FORCE_GZIP);
			}else if($this->accept_encoding=='deflate'){
				header('Content-Encoding: deflate');
				$document = gzencode($document, 9, FORCE_DEFLATE);
			}
		}
	}
	// Метод выводит необходимые хедеры
	function headers($data=array()){// log_fs('headers');
		/*
		// регулировка кэширования в браузерах
		$ETag = 'ETagHash';//.md5($_SERVER['REQUEST_URI']);
		if(!$_COOKIE[$ETag] || $_SERVER['REQUEST_METHOD']=='POST'){// устанавливается в первый раз при заходе на страницу
			$etag_hash = md5(time());
			setcookie($ETag, $etag_hash, (time()+86400), '/');
		}else{
			$etag_hash = $_COOKIE[$ETag];
		}
		header('ETag: "'.str_replace('"','\"',$etag_hash).'"');// дата изменения страницы и ее размер
		*/
		
		@header('Content-type:'.($data['Content-type']? $data['Content-type'] : 'text/html').'; charset=UTF-8');// кодировка страницы
/*
 $cache_expire = 60*60*24*365;
 header("Pragma: public");
 header("Cache-Control: max-age=".$cache_expire);
 header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$cache_expire) . ' GMT');
*/
		// Время кэширования (дата времени с которого начинается кэширование со стороны браузера)
		@header("Expires: ".gmdate("D, d M Y H:i:s", time() )." GMT");
		
		// количество времени кэширования страницы со стороны браузера (в секундах)
		@header("Cache-Control: post-check=0,pre-check=0");// работает не только с 5й версии ИЕ (ФФ тоже)
		
		$r = $this->pages[ $this->id ];// Дата последнего изменения страницы
		$time_modified = $data['Last-Modified']? $data['Last-Modified'] : ($r['time_modified']?$r['time_modified']:$r['time_created']);
		@header("Last-Modified: ".gmdate("D, d M Y H:i:s", ($time_modified? $time_modified : (time()-15000) ) )." GMT");
		/*
		if($data['content_size']){// array('content_size'=>strlen($this->document))
			header('ETag: "etag-'.$data['content_size'].'-'.$time_modified.'"');
			header("HTTP/1.1 304 Not Modified");
			exit;
		}
		*/
		// Если страница скрыта с выдачей заголовка 404
		if($this->pages[ $this->id ]['access_inherited']=='8'){ header("HTTP/1.0 404 Not Found"); }
		
    }
    // сохраняем данные в которых нельзя производить замены
    function beforeLinksReplace($pluse=''){// log_fs('beforeLinksReplace');
		
		if($this->document){
    		
    		$body = preg_split("/<body/i", $this->document);
			if($body['1']){
				$doc = $body['1'];
			}else{
				$doc = $body['0'];
			}
			
			// метод замены строк содержащих символ " пока не разработан!
			
			if($escape_script = preg_replace('/<script(.+)<\/script>/sUei', "HTMLSave('<script\\1</script>')", $doc)){
				$doc = $escape_script;
			}
			if($escape_css = preg_replace('/<style(.+)<\/style>/sUei', "HTMLSave('<style\\1</style>')", $doc)){
				$doc = $escape_css;
			}
			if($escape_textarea = preg_replace('/<textarea(.+)<\/textarea>/sUei', "HTMLSave('<textarea\\1</textarea>')", $doc)){
				$doc = $escape_textarea;
			}
			if($escape_input = preg_replace('/<input(.+)>/sUei', "HTMLSave('<input\\1>')", $doc)){
				$doc = $escape_input;
			}
			if($escape_img = preg_replace('/<img(.+)>/sUei', "HTMLSave('<img\\1>')", $doc)){
				$doc = $escape_img;
			}
			if($pluse && $escape_a = preg_replace('/<!--'.$pluse.'-->(.+)<!--\/'.$pluse.'-->/sUei', "HTMLSave('\\1')", $doc)){
				$doc = $escape_a;
			}
			if($body['1']){
				$this->document = $body['0'].'<body'.$doc;
			}else{
				$this->document = $doc;
			}
		}
    }
	// проверяет и выполняет преобразование текста в ссылки
	function textToLinks(){// log_fs('textToLinks');
		
		if($GLOBALS['HTML']['name_link'] && $this->document){
			
			if($escape_a = preg_replace('/<a(.+)<\/a>/sUei', "HTMLSave('<a\\1</a>')", $this->document)){
				$this->document = $escape_a;
			}
			$page_name = $this->pages[ $this->id ]['name'];//$this-> data('name', $this->id);
			
			$noB = (!stristr($this->document, '<b>'))? true : false;
			
			$noStrong = (!stristr($this->document, '<strong>'))? true : false;
			
			foreach($GLOBALS['HTML']['name_link'] as $name=>$link){
				
				if($name && $name!=$page_name && $link && $link!=$_SERVER['REQUEST_URI']){
					
					$i = $GLOBALS['HTML']['register_important'][$name]? '' : 'i';// важность регистра
					
					$name = trim($name);
					
					if(!strstr($this->document, $name)){ continue; }
					
					$title = preg_quote_( trim($name) );//$this-> clear()
					
					$w = preg_quote_($name);
					
					$a = "' '.HTMLSave('<a href=\"".$link."\" title=\"".$title."\">\\1</a>').'\\2'";
					
					if($replaced_A = preg_replace('/\s('.$w.')(\s|\.\s|\,\s|\<\/|\.\<\/)/ue'.$i, $a, $this->document)){
						$this->document = $replaced_A;
					}
					
					$bold = "HTMLSave('<a href=\"".$link."\" title=\"".$title."\">\\1</a>')";
					
					if(!$noB){
						if($replaced_B = preg_replace('/(<b>'.$w.'<\/b>)/ue'.$i, $bold, $this->document)){
							$this->document = $replaced_B;
						}
					}
					
					if(!$noStrong){
						if($replaced_STRONG = preg_replace('/(<strong>'.$w.'<\/strong>)/ue'.$i, $bold, $this->document)){
							$this->document = $replaced_STRONG;
						}
					}
				}
			}
		}
	}
    // Метод обновления даты изменения страницы
	function updateTimeModified($page_id=0){// log_fs('updateTimeModified');
		if($page_id && is_numeric($page_id)){
			if(mysql_query('UPDATE '.P.'pages SET time_modified='.time().' WHERE page_id='.$page_id)){
				return true;
			}
		}
	}
	function start(){// log_fs('start');
		
		set_error_handler("errorLog");// установливаем свой обработчик ошибок и отдаем void для функции restore_error_handler
		$this->error_handler = 'in file: /index.php';
		
		// находим текущий id по URL c проверкой доступа к нему для текущего пользователя
		if($_SERVER['REQUEST_URI']=='/'){
			
			$this->id = $GLOBALS['SYSTEM']['config']['yapro_index_id'];
			
			$this-> data('page_id');
			
			if(!$this->pages[ $this->id ]){// если данные не найдены
				header404(__FILE__.' : 1. данные не найдены или посетителю недостаточно прав для просмотра страницы '.$_SERVER['REQUEST_URI']);
			}
			$r = url($this->id,2);
			$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'] = $r['url'];
			$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url_type'] = $r['url_type'];
			
		}else{
			
			$R_U = $_SERVER['REQUEST_URI'];
			
			$url_additional = array(
				//'%D0%B0%D0%BA%D1%82%D0%B5%D1%80%D1%8B'=>'актеры',
				'i'=>'i');
			foreach($url_additional as $k => $v){
				$ex = '/'.$k;
				$e = explode($ex.'/',$R_U);
				if(!$e['0'] && $e['1']){
					$R_U = $ex.$GLOBALS['SYSTEM']['config']['yapro_page_ext'];
					$this->url_additional = $e['1'];
				}
			}
			
			if($GLOBALS['SYSTEM']['config']['yapro_slash_autourl'] && mb_substr($R_U,-1)=='/'){//!$url_type && 
				if(is_dir($_SERVER['DOCUMENT_ROOT'].$R_U)){
					header404(__FILE__.' : просмотр директорий запрещен');
				}else{
					// $url = mb_substr($_SERVER['REQUEST_URI'],0,-1);
					header301(mb_substr($R_U,0,-1), __FILE__.' : переадресация по слэшу в конце : ');
				}
			}
			
			$url = rawurldecode($R_U);//$url = strtolower_($R_U);
			
			// log_fs('url: 1='.$url);
			$url_type = 0;// предустановленная переменная
			
			$get = explode('?',$url);
			if($get['1']){// в url найден разделитель вопросом
				$url = $get['0'];
				$url_type = 1;
				//*-log_('в url найден разделитель вопросом');
				
				if($GLOBALS['SYSTEM']['config']['yapro_page_ext']){// если указано использовать расширение страниц
					$ex = explode($GLOBALS['SYSTEM']['config']['yapro_page_ext'], $url);
					if(!isset($ex['1'])){
						header301($ex['0'].$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?'.$get['1'], __FILE__.' : расширения не хватает');
					}
				}
			}
			
			if($GLOBALS['SYSTEM']['config']['yapro_page_ext']){// если имеется расширение страниц
				$ex = explode($GLOBALS['SYSTEM']['config']['yapro_page_ext'], $url);
				$url = $ex['0'];//избавляемся от расширения
				//*-// log_('url_type может быть любым');// url_type может быть любым
				if(!isset($ex['1'])){
					header301($ex['0'].$GLOBALS['SYSTEM']['config']['yapro_page_ext'].$ex['1'], __FILE__.' : не хватало расширения страницы');
				}
			}
			// log_fs('url: 2='.print_r($ex,1));
			/*
			$dot = explode('.',$url);
			if($dot['1']){// в url найден разделитель точкой
				$url = $dot['0'];
				$url_type = 1;
				//*-log_('в url найден разделитель точкой');
			}
			// log_fs('url: 3='.$url);
			*/
			
			if(!$url){
				
				header404(__FILE__.' : url: '.$R_U.' не распознан');
				
			}else{// попробуем найти page_id по урл
				
				$md5 = md5($url);
				
				// попробуем найти page_id по урл
				$this->id = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/'.$md5);
				
				// log_fs('url: 1) page_id = '.$this->id);
				if(!$this->id){// возможно это уникальный URL
					
					$this->id = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/'.md5($R_U));
					
					// log_fs('url: 2) page_id = '.$this->id);
					if(!$this->id){// выполню попытку найти страницу по конечному имени url-директории
						
						$ex = array_reverse(explode('/', $url));
						if(substr($url,-1)=='/'){ $dir = $ex['1']; }else{ $dir = $ex['0']; }// проверка на слэш в конце имени
						
						if(!$dir){
							header404('URL не определен '.__FILE__);
						}else{
							
							// log_fs('url: 3) page_id = '.$this->id);
							
							if($r['page_id']){
								
								$this->id = $r['page_id'];
								$this-> url_cache($md5);
								
							}else{
								
								// log_fs('url: 5) page_id = '.$this->id);
								$r = @mysql_fetch_assoc(mysql_('SELECT page_id FROM '.P.'pages_url WHERE url LIKE '._.$url._.' OR url='._.$R_U._));
								
								if($r['page_id']){
									
									$this->id = $r['page_id'];
									$this-> url_cache($md5);
									
								}else{
									
									// log_fs('url: 6) page_id = '.$this->id);
									
									//$r = @mysql_fetch_assoc(mysql_( sql('page_id', 'url LIKE '._.'%'.$dir._.'', '', 1, '', 1)));
									$r = @mysql_fetch_assoc(mysql_('SELECT page_id FROM '.P.'pages_url WHERE url LIKE '._.$dir._));
									
									if($r['page_id']){
										
										$this->id = $r['page_id'];
										$this-> url_cache($md5);
										
									}else{
										
										// log_fs('url: 7) page_id = '.$this->id);
										header404('URL не найден: '.__FILE__);
									}
								}
							}
						}
					}
				}
			}
			
			// log_fs('url: ok $this->id = '.$this->id);
			
			$this-> data('page_id');// находим все данные текущей страницы с проверкой прав доступа посетителя для просмотра данной страницы
			$r = $this->pages[ $this->id ];
			
			if(!$r){// если данные не найдены
				header404($this->id.' = '.__FILE__.' : 2. данные не найдены или посетителю недостаточно прав для просмотра страницы '.$R_U);
			}
			
			$real_url = url(0,4);
			$real_url_type = url(0,1);
			
			$real_url_check = $GLOBALS['rurl']-> urlencode_($real_url);
			
			if($real_url_type==2){// если уникальный
				
				if($real_url_check!=$R_U){// если не совпадает с просматриваемым в браузере (возможно старый адрес)
					
					header301($real_url, __FILE__.' : неправильное обращение к уникальному адресу (страница '.$this->id.')');
					
				}
				
			}else if($real_url_type==1){// если разрешаются метки
				//-echo $url.' != '.$real_url; exit;
				if($url!=$real_url){// если не используются метки И урл указан странно
					header301($real_url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'], __FILE__.' : неверное обращение', 'db');
				}
				
			}else if($real_url_type==0){// если обычный урл
				
				// если не совпадает с просматриваемым в браузере
				if($real_url_check.$GLOBALS['SYSTEM']['config']['yapro_page_ext']!=$R_U){
					
					header301($real_url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'], __FILE__.' : неправильное обращение: '.
					$real_url_check.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'!='.$R_U, 'db');
					
				}
				
			}else{
				header404(__FILE__.' : URL-ситуация неизвестна');
			}
			
			$GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'] = $real_url_check;
		}
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/beforeContent.php');// подгружаем доп. модули на изменение данных до проверки системой
		
		// находим шаблон страницы
		$tpl = '';
		$template_level = 0;
		$page_id = $this->id;
		$templates = $page_id__parent_id = array();
		while(true){
			if($q = mysql_('SELECT template, template_level FROM '.P.'pages_templates WHERE page_id='.$page_id.' ORDER BY template_level')){
				while($r = mysql_fetch_row($q)){
					$templates[ $page_id ][ $r['1'] ] = $r['0'];
				}
			}
			
			$tpl = $templates[ $page_id ][ $template_level ];
			if($tpl){
				break;
			}else{// попробуем найти у родителя
				$template_level++;
				$parent_id = @mysql_fetch_row(mysql_('SELECT parent_id FROM '.P.'pages WHERE page_id='.$page_id));
				$page_id__parent_id[ $page_id ] = $parent_id['0'];
				$page_id = $page_id__parent_id[ $page_id ];
				if(!$page_id){
					break;
				}
			}
		}
		// если правильно найти шаблон не удалось, определяем шаблон по нулевому шаблону ближайшего родителя
		$page_id = $this->id;
		if(!$tpl){
			while(true){
				$tpl = $templates[ $page_id ][ $template_level ];
				if($templates[ $page_id ]){
					$template_level = key($templates[ $page_id ]);// находим нулевой шаблон
					$tpl = $templates[ $page_id ][ $template_level ];
					break;
				}else{
					$page_id = $page_id__parent_id[ $page_id ];
					if(!$page_id){
						break;
					}
				}
			}
		}
		if(!$tpl){ $tpl = 'index.php'; }
		
		$this-> alert($tpl);
	}
	function alert($tpl=''){
		
		//-echo $this->id.'--'.$page_id__parent_id[ $this->id ].'--'.$tpl; exit;
		// начинаю построение документа
		$this->document = $this-> include_($_SERVER['DOCUMENT_ROOT'].'/templates/'.$tpl);
		//-echo $this->document; exit;
		if(!$this->document){ $this-> headers(); echo 'Шаблон страницы '.$tpl.' с ID='.$this->id.' не найден или не имеет содержания!'; exit; }
		// log_($this->id.' X=1 '.$this->document);
		$this-> content($this->document);// получаем содержание страницы
		// log_($this->id.' X=2 '.$this->document);
		@include_once($_SERVER['DOCUMENT_ROOT'].'/inner/afterContent.php');// доп. модули на изменение контента
		// log_($this->id.' X=3 '.$this->document);
		//log_fs('X=3');
		if($GLOBALS['SYSTEM']['config']['yapro_redirect_affiliates']){//если Присваивать redirect к URL чужих сайтов
			//log_fs('X=3.1');
			$this-> beforeLinksReplace('NoRedirect');// сохраняем данные в которых нельзя производить замены
			// log_($this->id.' X=3.1 '.$this->document);
			//log_fs('X=3.2');
			$preg_quote_host = preg_quote_($_SERVER['HTTP_HOST']);
			//log_fs('X=3.3');
			if($doc = preg_replace('/<a(.+)href=("|\'|)http:\/\/(?!'.$preg_quote_host.'|www\.'.$preg_quote_host.')(.*)("|\'|\s|)>/sUi', '<a$1href=$2http://'.$_SERVER['HTTP_HOST'].'/s/r?http://$3$4 target="_blank">', $this->document)){
				$this->document = $doc;
			}
			//log_fs('X=3.4');
			/*
				if($this->id=='173'){
					preg_match_all('/<a(.+)href=("|\'|)http:\/\/(?!'.$preg_quote_host.'|www\.'.$preg_quote_host.')(.*)("|\'|\s|)>/sUi', $this->document, $f);
					log_('/<a(.+)href=("|\'|)http:\/\/(?!'.$preg_quote_host.'|www\.'.$preg_quote_host.')(.*)("|\'|\s|)>/sUi');
					log_('$f==='.print_r($f,1));
					log_('$this->document==='.print_r($this->document,1));
				}
			*/
			$this->document = HTMLBack($this->document);// преобразуем запрещенный код в нормальный вид
		}
		if($GLOBALS['SYSTEM']['config']['yapro_text_to_links']){// Сайт. Автоматическое добавление ссылок - фразам в тексте
			
			$this-> beforeLinksReplace('NoReplace');// сохраняем данные в которых нельзя производить замены
			
			$this-> textToLinks();// выполняем преобразование текста в ссылки
			//log_fs('X=3.5');
			// log_($this->id.' X=3.3 '.$this->document);
			$this->document = HTMLBack($this->document);// преобразуем запрещенный код в нормальный вид
			//log_fs('X=3.6');
			// log_($this->id.' X=3.4 '.$this->document);
		}
		//log_fs('X=4');
		// log_($this->id.' X=4 '.$this->document);
		$this->document = str_replace("\xEF\xBB\xBF", '', $this->document);// удаляем BOM-символы
		// log_($this->id.' X=5 '.$this->document);
		//log_fs('X=5');
		$this-> meta();// получем мета-данные
		$this->document = str_replace('<!--NoRedirect-->', '', str_replace('<!--/NoRedirect-->', '', str_replace('<!--MetaData-->', '', str_replace('<!--/MetaData-->', '', $this->document))));
		
		// log_($this->id.' X=6 '.$this->document);
		$this-> headers();// отправялем заголовки
		// log_($this->id.' X=7 '.$this->document);
		$this-> zip($this->document);// сжатие документа
		// log_($this->id.' X=8 '.$this->document);
		
		echo $this->document;// выводим страницу
		//+log_('OK==='.(getmicrotime() - $GLOBALS['SYSTEM']['time_start']));
	}
}

$system = new system();
if(!$GLOBALS['system_include']){
	if(HTTP_HOST_DOTS > 0 && $GLOBALS['SYSTEM']['config']['yapro_subdomains']){
		$x = $IDN? $GLOBALS['IDN']->decode($_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST'];
		$e = explode('.',$x);
		header("location: http://".$GLOBALS['SYSTEM']['config']['yapro_http_host'].'/i/'.rawurlencode($e['0']), true, 301);
	}else{
		$system-> start();
	}
}
?>
