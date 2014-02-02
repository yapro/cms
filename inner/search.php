<?php
// Настройки плагина:
$max = 10;// количество выдаваемых результатов поиска на одной странице
$search_length = 3;// минимальная длинна поискового текста в символах
$search_result = 97;// количество символов обрамлящих (слева и справа) найденную фразу
$search_fields = 'name,article,tags';// разрешать поиск в полях (указывать через запятую)
$search_style = 'style="background-color: #FFFFAA; color: #000000;"';// CSS правила выделения найденного текста
$search_numbers = true;// показываеть нумерацию поиска (true - да, false - нет)
$search_form = false;// вывод формы поиска в теле страницы (перед результатами поиска)
$search_button = 'Найти';// кнопка поиска
$search_time = 0;// ограничивать по времени между запросами поиска (максимальная частота поиска) (в секундах) - не реализовано
$search_answer_null = 'К сожалению, по Вашему запросу ничего не найдено.';// текст выдаваемый посетителю в случае безрезультатного поиска

// текст ссылки альтернативного поиска через Google, выдаваемый посетителю в случае безрезультатного поиска
$search_answer_google = 'Попробовать найти данные с помощью Google »';

// CSS-правила и подключаемые JS-файлы (не обязательны)
$search_additional = '<link href="/js/paginator/paginator.css" type="text/css" rel="stylesheet">
<style>.Listing { border:0px; margin: 25px 0 0; padding:0px; }</style>
<script language="Javascript" type="text/javascript" src="/js/paginator/paginator.js"></script>
<script>$(document).ready(function(){ pag = new Paginator(".Listing", 16); });</script>';
$listing = 3;// вид листинга

$search_comments_auto = true;// если информация в страницах сайта не найдена, то попробовать найти в комментариях на сайте
//-----------------------------------------------------------------------------------------

include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/folio_search.php');

//-----------------------------------------------------------------------------------------
$start = $folio * $max;// показываем записи начиная с start
//-----------------------------------------------------------------------------------------
$charset = charget($search);
if($charset!='i'){ $search = charset($search, 'w', 'i'); }
//------------------------------------
if($search){
	$GLOBALS['SYSTEM']['statistics']['search_html'] = htmlAccess($search);
}
//------------------------------------
$length = mb_strlen($search);
//------------------------------------
if($search && $search_answer_google){
	$search_answer_google = ' <a href="http://www.google.com/search?q=site%3A'. $_SERVER['HTTP_HOST'].'+'.rawurlencode($search).'" target="_blank">'.$search_answer_google.'</a>';
}else{
	$search_answer_google = '';
}
//-----------------------------------------------------------------------------------------
if($search && $length>=$search_length){// если поисковый текст найден
	
	$page_id__name = $page_id__url = $page_id__found_result = array();
	
	if($_POST){// обнуляем данные
		cookie_delete('search_dir');
		cookie_delete('search_comments');
	}
	
	if($_POST['search_dir']){// поиск по заданной урл-директории
		cookie('search_dir', $_POST['search_dir']);
	}
	
	if($_POST['search_comments']){// поиск только в комментариях
		cookie('search_comments', 1);
	}
	
	$sql_search =  ' LIKE '._."%".trim(str_replace('+', '%', str_replace(' ', '%', $search)))."%"._." OR ";
	
	if(!$GLOBALS['SYSTEM']['config']['board_disable'] && $GLOBALS['SYSTEM']['config']['script_path'] && $_COOKIE['search_dir']==$GLOBALS['SYSTEM']['config']['script_path']){// если поиск по форуму
		
		$from_where = ' FROM '.F.'posts WHERE post_subject'.$sql_search.'post_text'.substr($sql_search, 0, -4);
		
		$r = @mysql_fetch_row(mysql_('SELECT COUNT(*)'.$from_where));
		
		$count = (int)$r['0'];
		
		if($count && $q = mysql_('SELECT topic_id, post_subject, post_text'.$from_where.' LIMIT '.$start.', '.$max)){
			
			while($r = mysql_fetch_assoc($q)){
				$key = 'forum'.$r['topic_id'];
				$page_id__url[ $key ] = $GLOBALS['SYSTEM']['config']['script_path'].'/viewtopic.php?t='.$r['topic_id'];
				$page_id__name[ $key ] = $r['post_subject'];
				$page_id__found_result[ $key ] .= $r['post_text'];
			}
		}
		
	}else if($_COOKIE['search_comments']){
		
		$where = 'c.comment LIKE '._.'%'.trim(str_replace('+', '%', str_replace(' ', '%', $search))).'%'._.' AND '.sql_where('p.');
		
		if($_COOKIE['search_dir']){
			if(mb_substr($_COOKIE['search_dir'], -1)=='/'){ $_COOKIE['search_dir'] = mb_substr($_COOKIE['search_dir'], 0, -1); }
			$where .= 'AND ur.url LIKE '._.$_COOKIE['search_dir'].'%'._;
		}
		
		$r = @mysql_fetch_row(mysql_('SELECT COUNT(*) FROM '.P.'comments AS c 
		LEFT JOIN '.P.'pages_'.$GLOBALS['SYSTEM']['site_id'].' AS p ON p.page_id = c.page_id WHERE '.$where));
		$count = $r['0'];
		
		if($count){
			
			if($q = mysql_("SELECT c.comment_id, c.user_id, c.time_created, c.comment, c.karma, n.nickname, u.username, p.name, ur.url 
			FROM ".P."comments AS c 
			LEFT JOIN ".N."nicknames AS n ON n.id = c.nickname_id 
			LEFT JOIN ".F."users AS u ON u.user_id = c.user_id 
			LEFT JOIN ".P."pages AS p ON p.page_id = c.page_id 
			LEFT JOIN ".P."url AS ur ON ur.page_id = c.page_id 
			WHERE c.time_modified!=0 AND ".$where.' ORDER BY c.comment_id DESC LIMIT '.$start.','.$max)){
				while($r = @mysql_fetch_assoc($q)){
					
					$page_id__url[ $r['comment_id'] ] = url($r).'#comment_'.$r['comment_id'];
					$page_id__name[ $r['comment_id'] ] = $r['name'];
					$page_id__found_result[ $r['comment_id'] ] = htmlAccess(($r['username']? $r['username'] : ($r['nickname']? $r['nickname'] : 'Гость')).':'.$r['comment'], false);
					
				}
			}
		}
		
	}else{// поиск по всему сайту или разделу сайта
		
		if(!$search_fields){ $search_fields = 'name'; }// если не указано в каких полях выполнять поиск - выполняю поиск по имени страниц
		
		$where = '';
		if($search_fields){// выполняю поиск по указанным полям
			$ex = explode(',', $search_fields);
			foreach($ex as $field_name){
				if($field_name){
					$where .= $field_name.$sql_search;
				}
			}
		}
		
		$where = '('.substr($where, 0, -4).')';
		
		if($_COOKIE['search_dir']){
			if(mb_substr($_COOKIE['search_dir'], -1)=='/'){ $_COOKIE['search_dir'] = mb_substr($_COOKIE['search_dir'], 0, -1); }
			$page_id = array();
			if($_COOKIE['search_dir']){
				$page_id = @mysql_fetch_row(mysql_('SELECT page_id FROM '.P.'pages_url WHERE url = '._.$_COOKIE['search_dir']._));
				if($page_id['0']){
					$parent_id = @mysql_fetch_row(mysql_(sql('page_id', 'page_id='.$page_id['0'])));
					if($parent_id['0'] && $q = mysql_('SELECT page_id FROM '.P.'pages_parents WHERE parent_id = '.$parent_id['0'])){
						$in = '';
						while($r = mysql_fetch_assoc($q)){
							$in = $r['page_id'].',';
						}
						if($in){
							$where .= 'AND page_id IN ('.substr($in,0,-1).')';
						}
					}
				}
			}
			
		}
		
		$r = @mysql_fetch_row(mysql_(sql('COUNT(*)', $where)));
		$count = $r['0'];
		
		$order_by = '';
		if(substr($search_fields,0,5)=='tags,' || strstr($search_fields,',tags,') || substr($search_fields,-5)==',tags'){
			
			$search_fields .= ", IF( FIND_IN_SET("._.$search._.", REPLACE(tags, "._.", "._.", "._.","._.")) = 0, 123456789, FIND_IN_SET("._.$search._.", REPLACE(tags, "._.", "._.", "._.","._."))) AS LEBNIK_FIND_IN_SET";
			$order_by = 'LEBNIK_FIND_IN_SET';
		}
		
		if($count && $q = mysql_(sql('page_id, '.$search_fields, $where, $order_by, $start, $max))){
			while($r = mysql_fetch_assoc($q)){
				foreach($r as $k => $v){// проходим по полям
					
					if($k=='name'){
						$page_id__name[ $r['page_id'] ] = $v;
						$page_id__url[ $r['page_id'] ] = '/s/p?'.$r['page_id'];
					}else{// другие $search_fields поля (например текстовые)
						if($k=='page_id' || $k=='LEBNIK_FIND_IN_SET'){ continue; }
						$page_id__found_result[ $r['page_id'] ] .= $v;
					}
				}
			}
		}
	}
	
	/* а если куки не поддерживаются у человека? - переделать!
	if(!$search_comments_auto){
		
		cookie('search_comments', 1);
		header301('/system/search.'.urlencode($search));
		
	}
	*/
	@include($_SERVER['DOCUMENT_ROOT'].'/inner/search_keywords.php');// собираем данные статистики о поиске
	
	if(!$page_id__found_result){
		
		$document .= '<p id="emptySearchResult" alt="'.$max.'">'.$search_answer_null.$search_answer_google.'</p>';
		/*
	}else if(!$folio && count($page_id__found_result)==1 && !$_COOKIE['search_comments']){
		
		header301($page_id__url[ key($page_id__found_result) ]);// если в результате найдена только 1 страница - сразу перенаправляем на нее
		*/
	}else{
		
		$temp = ''; $search_text = $search_yellow = array();
		
		// Обводка ключевой фразы
		$ex_search = explode(' ', trim(str_replace('+', ' ', $search)));
		foreach($ex_search as $v){
			if($temp){
				$search_text[] = $v;
				$search_yellow[] = '/'.preg_quote_(trim($v)).'/sUiu';
			}
			// полные фразы
			$temp .= $v.' ';
			$search_text[] = trim($temp);
			$search_yellow[] = '/'.preg_quote_(trim($temp)).'/sUiu';
		}
		
		// делаем полные фразы предпочтительными для последующего CSS обрамления
		krsort($search_text);
		krsort($search_yellow);
		
		$target = (count($page_id__found_result)>1)? ' target="_blank"' : '';// если найдено более 1 страницы - открываем ссылки в новом окне
		
		$i = 0;
		foreach($page_id__found_result as $k=>$v){
			
			$i++;// номер ссылки по порядку
			
			$v = $this-> Clear($v);// чистим текст
			
			$firsttext = $lasttext = '';
			$firstposition = 0; $lastposition = 0;
			
			if($v){
				
				$info = strffarray($v, $search_text);
				
				$textlength = mb_strlen($v);
				
				if($info){
					
					// опережение текста
					$firsttext = ($info['first']>$search_result)? '...' : '';
					$firstposition = $firsttext? ($info['first']-$search_result) : 0;// длинна опережающего текста
					
					// продолжение текста
					$lasttext = (($textlength - $info['last']) > $search_result)? '...' : '';
					$lastposition = ( ($search_result*2) + mb_strlen($info['str']) );// длинна продолжающего текста
					
				}else{
					
					$firstposition = 0;
					$lastposition = ($search_result*2);
					
				}
				
				$text = $firsttext.mb_substr($v, $firstposition, $lastposition).$lasttext;
				
			}else{
				$text = '';
			}
			
			
			
			if($doc = preg_replace($search_yellow, '<span '.$search_style.'>$0</span>', $text)){
				$text = $doc;
			}
			
			$document .= '<DIV class="SearchResult"><b>'.($search_numbers?($start+$i).'. ':'').'<a href="'.$page_id__url[$k].'"'.$target.'>'.$page_id__name[$k].'</a></b>'.($text?'<br>'.str_replace('&amp;','&',$text):'').'</DIV>';
			
		}
		include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/listing_search.php');
	}
}
// выдаем форму поиска
$document = ((!$search || ($search && $length<$search_length))?'<p id="emptySearchResult" alt="'.$max.'">Ваш запрос менее '.$search_length.' символов. Попробуйте, сформулировать запрос иначе.'.$search_answer_google.'</p>':'').'
	'.($search_form?'<form action="/system/search.html" class="SearchForm" method="post">
    	<div class="SearchField"><input type="text" name="search" value="'.$this->search_text.'"></div>
    	<div class="SearchSubmit"><input type="submit" value="'.($search_button).'"></div>
	</form>':'').$search_additional.$document;
?>
