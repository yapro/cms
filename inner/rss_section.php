<?php
// скрипт выводит ленту RSS-новостей указанного ID раздела
// конструкции {~ запрещены, потому что среди обычных страниц могут попадаться констукции, которые могут остановить скрипт РСС-ленты

$max = 25;// максимальное кол-во новостей
$without_comments_link = $sys_rss_section_without_comments_link? true : false;
$search_in_parent = false;// если true - то в случае отсутствия дочерних страниц внутри раздела, пользователь будет перенаправлен на RSS-вышестоящего раздела
//--------------------------------------------------------------------------------------------

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/get_page_id.php');

//------------------------------- валидность RSS проверено с помощью http://feedvalidator.org/
$file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-4).'/'.$get_page_id.'.xml');// узнаем имя кэш-файла

// get_page_id==all - подгружаем ленту всего сайта только из кэша (это нужно чтобы подгружать ее быстро)
if($GLOBALS['system_cache']->name['pages'] && $GLOBALS['system_cache']->name['pages'] < filemtime($file)){
	
	$document = @file_get_contents($file);
	
}else{
	
	//-------------------------------
	
	$lastModified = time()-555;
	
	$pages = array();// создаем массив данных о страницах
	
	if($get_page_id=='all'){
		
		$where = '';
		
		$title = 'Самое новое';
		$description = 'cо всего сайта '.$_SERVER['HTTP_HOST'];
		$link = '/';
		
	}else{
		
		// находим данные выбранного раздела
		$r = @mysql_fetch_assoc(mysql_query(sql('page_id, name', 'page_id='.$get_page_id)));
		
		if(!$r['page_id']){
			header404(__FILE__.' : выбран несуществующий раздел');
		}
		
		$url = url($r['page_id'],3);
		
		$page_id_in = '';
		if($q = mysql_('SELECT page_id FROM '.P.'pages_url WHERE url LIKE '._.$url.'%'._)){
			while($a = mysql_fetch_assoc($q)){
				$page_id_in .= $a['page_id'].',';
			}
		}
		
		if(!$page_id_in){ header404(__FILE__.' : дочерние страницы не найдены'); }
		
		$where = "page_id IN (".substr($page_id_in,0,-1).") AND ";
		
		$title = htmlAccess($r['name']);
		
		$description = 'раздела '.$title;
		
		$link = ($get_page_id==$GLOBALS['SYSTEM']['config']['yapro_index_id'])? '/' : $url;
		
	}
	
	$str = '';
	
	// находим данные страниц, за исключаем прямых ссылок (ведь это не страницы)
	if($q = mysql_query(sql('page_id, time_created, name, article, img, comments', $where." name!='' AND article!='' AND article NOT LIKE '%{~%'", 'time_created DESC', $max))){
		
		while($r = mysql_fetch_assoc($q)){
			
			if(url($r['page_id'],1)==3){// если url_type ссылка
				continue;
			}
			
			$url = 'http://'.$_SERVER['HTTP_HOST'].url($r['page_id']);
			
			$article = pagebreak($r['article']);
			
			if($article['0'] && $article['1']){// если у статьи раздела есть анонс и содержание
				
				$notice = $article['0'];// то в RSS-ленту попадает анонс
				
			}else if($article['0']){// если же только содержание
				
				$notice = $article['0'];// то попадает содержание целиком
				
			}else if(!$article['0'] && $r['img']){// если же нет ни анонса, ни содержания, но есть изображение анонса
				
				$notice = '<a href="'.$url.'" style="border:none"><img src="'.$r['img'].'" alt="'.$this->clear($r['name']).'" border="0"></a>';// показывается изображение анонса ссылкой на статью
				
			}else{// в противном случае
				
				$notice = '';// показываем только название статьи
				
			}
			
			if($without_comments_link || $article['1']){// добавляем доп. ссылки
				
				$notice .= '<p>';
				if($article['1']){
					$notice .= '<a href="'.$url.'#article" target="_blank">Подробнее &#8230;</a> &nbsp; ';
				}
				if(!$without_comments_link){
					$notice .= '<a href="'.$url.'#comments" target="_blank">Комментировать '.($r['comments']?'('.$r['comments'].')':'').' »</a>';
				}
				$notice .= '</p>';
			}
			
			$notice = str_replace('&amp;','&', str_replace('&laquo;','«', str_replace('&raquo;','»',$notice)));
			
			$str .= '<item><title><![CDATA['.$r['name'].']]></title>'."\n".
			'<guid isPermaLink="true">'.$url.'#article</guid>'."\n".
			'<link>'.$url.'</link>'."\n".
			'<description><![CDATA['.$notice.']]></description>'."\n".
			//'<content:encoded><![CDATA['.$notice.']]></content:encoded>'."\n".
			'<dc:creator>Admin</dc:creator>'."\n".
			'<pubDate>'.date("r", $r['time_created']).'</pubDate>'."\n".
			'<comments>'.$url.'#comments</comments>'."\n".
			'<slash:comments>'.(int)$r['comments'].'</slash:comments>'."\n".
			'</item>';
			
			$lastModified = $r['time_created']>$lastModified? $r['time_created'] : $lastModified;// данные для хедера
			
		}
	}
	
	if(!$str && $search_in_parent){// т.к. данных нет, то наверняка это не раздел, а страница какого-то раздела
		
		$parent_id = $this-> data('parent_id', $get_page_id);// узнаем page_id родительского раздела
		
		if(!$parent_id){// т.к. вышестоящий раздел - корень сайта ($parent_id==0)
			
			header404(__FILE__.' : в данном разделе данных нет или в во всех дочерних страницах используются конструкции вида {~~}');
			
		}else{// переадресуем на страницу раздела
			
			header301('/s/rss?'.$parent_id, __FILE__.' : выполняю попытку найти данные в родительской RSS-ленте');
			
		}
		
	}
	
	//-------------------------------
	
	if($str){
		$description = 'Последние новости '.($description? $description : 'сайта');
	}else{
		$description = 'К сожалению, данный раздел пока не имеет новостей';
	}
	
	$document = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
<channel>
<title>'.host_clear($_SERVER['HTTP_HOST_NAME']).' | '.($title? $title : 'RSS').'</title>
<atom:link href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" rel="self" type="application/rss+xml" />
<link>http://'.$_SERVER['HTTP_HOST'].$link.'</link>
<language>ru</language>
<description>'.$description.'</description>
<generator>CMS '.$_SERVER['HTTP_HOST'].'</generator>'."\n".$str."\n".'</channel>'."\n".'</rss>'.$lastModified;
	
	write_($document, $file);
}
$ex = explode('</rss>',$document);
$header['Content-type'] = 'text/xml';
$header['Last-Modified'] = $ex['1'];
$this->pages[ $this->id ]['access_inherited'] = 0;// полный доступ к странице
$this-> headers($header);
echo $ex['0'].'</rss>';
exit;
?>