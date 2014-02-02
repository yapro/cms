<?php
// плагин RSS комментариев (пример обращения .../rss.php?123 - определенная страница или .../rss.php?all - всего сайта
$max = 25;// максимальное количество выводимых комментариев
//--------------------------------------------------------------------------
if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');// файл конфигурации

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции
//--------------------------------------------------------------------------
// находим идентификатор страницы
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/get_page_id.php');
//--------------------------------------------------------------------------

@include_once('settings.php');// свои настройки

// false - смайлы не нужны, true - смайлы нужны
$smiles = $comments_smiles? $comments_smiles : true;

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');// кэш класс

//--------------------------------------------------------------------------

$page_id_in = '';
$pages = array();// создаем массив данных о страницах

if($get_page_id=='all'){
	
	$where = '';
	
	$title_channel = 'Комментарии cо всего сайта';
	$description_channel = 'cо всего сайта '.$_SERVER['HTTP_HOST'];
	$link = '/';
	
}else{
	
	$r = @mysql_fetch_assoc(mysql_query(sql('page_id, name', 'page_id='.$get_page_id)));
	
	if(!$r['page_id']){
		header404(__FILE__.' : выбран несуществующий раздел');
	}
	
	if($q = mysql_('SELECT page_id FROM pages_url WHERE url LIKE '._.$url.'%'._)){
		while($a = mysql_fetch_assoc($q)){
			$page_id_in .= $a['page_id'].',';
			//$pages[ $a['page_id'] ] = $a;
		}
	}
	$where = "page_id IN (".$page_id_in.$get_page_id.") AND ";
	
	$title_channel = htmlAccess($r['name']);
	
	$description_channel = ((count($pages)>1)? 'раздела' : 'со страницы').' '.$title_channel;
	
	$title_channel .= ' | Комментарии';
	
	$link = ($get_page_id==$GLOBALS['SYSTEM']['config']['yapro_index_id'])? '/' : url($r);
	
}

$str = '';
$lastModified = time()-555;

if($q = mysql_query("SELECT comment_id, parent_id, page_id, user_id, time_created, nickname_id, comment, karma 
FROM ".P."comments 
WHERE ".$where." time_modified!='0' ORDER BY comment_id DESC LIMIT ".$max)){
	
	$page_id_in = '';
	$comments = array();
	while($r = @mysql_fetch_assoc($q)){
		
		$comments[ $r['comment_id'] ] = $r;
		
		if($r['user_id']){ $user_id_in .= $r['user_id'].','; }
		
		if($r['nickname_id']){ $nickname_id_in .= $r['nickname_id'].','; }
		
		$page_id_in .= $r['page_id'].',';
	}
	
	$nicknames = array();// находим данны о никнэймах
	if($nickname_id_in && $q = mysql_query("SELECT id, nickname FROM ".N."nicknames WHERE id IN(".substr($nickname_id_in,0,-1).")")){
		while($r=mysql_fetch_assoc($q)){
			$nicknames[ $r['id'] ] = $r['nickname'];
		}
	}
	
	$users = array();// находим данны о аватарах
	if($user_id_in && $q = mysql_query("SELECT user_id, username, user_avatar, user_avatar_type 
	FROM ".F."users 
	WHERE username!='Anonymous' AND user_type IN (0,3) AND user_id IN(".substr($user_id_in,0,-1).")")){
		while($r=mysql_fetch_assoc($q)){
			$users[ $r['user_id'] ] = $r;
		}
	}
	
	// если просмотр комментариев со всего сайта, нужно найти данные страниц на которых оставлены последние комменты
	if(!$pages && $page_id_in && $q = mysql_query(sql('page_id, name', "page_id IN (".substr($page_id_in,0,-1).")"))){
		while($r = mysql_fetch_assoc($q)){
			$pages[ $r['page_id'] ] = $r;
		}
	}
	
	if($comments){
		
		$comments_smiles = array();
		
		if($smiles){// смайлы
			
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/smiles.php');
			
			$comments_smiles = $GLOBALS['lebnik_smiles']['php'];// создаю данные комментариев и смайлов
			
		}
		//----------------------------------------------------------------------------------
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');// класс юзеров
		
		foreach($comments as $comment_id => $r){
			
			$user = $users[ $r['user_id'] ];
			
			$url_user = 'http://'. $_SERVER['HTTP_HOST'].'/system/user'.($user['user_id']? '.'.$user['user_id'] : '').$GLOBALS['SYSTEM']['config']['yapro_page_ext'];
			
			$nickname = str_replace('&#036;','$', $GLOBALS['SYSTEM']['users']-> comment_name($user['username'], $nicknames[ $r['nickname_id'] ]));
			
			$description = str_replace('  ', ' &nbsp;', str_replace("\t", ' &nbsp; &nbsp; &nbsp; ', 
								strtr(nl2br(htmlAccess($r['comment'])), $comments_smiles)
							));
			
			if($r['user_id']){
				$avatar = '<a href="'.$url_user.'"><img src="'.$GLOBALS['SYSTEM']['users']-> avatar_src($user).'" width="24" border="0"></a>';
			}else{
				$avatar = '<img src="'.$GLOBALS['SYSTEM']['users']-> avatar_default().'" width="24" border="0">';
			}
			
			if($pages[ $r['page_id'] ]){
				
				$title = $pages[ $r['page_id'] ]['name'];
				
				$url = 'http://'. $_SERVER['HTTP_HOST'].url($r['page_id']);
				
				if($r['user_id']){
					$description = $avatar.' <a href="'.$url_user.'">'.$nickname.'</a> : '.$description;
				}else{
					$description = $avatar.' <b>'.$nickname.'</b>  : '.$description;
				}
				
			}else{
				
				$title = $nickname;
				
				$url = 'http://'. $_SERVER['HTTP_HOST'].url($r['page_id']);
				
				$description = $avatar.' : '.$description;
			}
			
			$url_comment = $url.'#comment_'.$comment_id;
			
			if($r['karma']){ $description .= '<p><b>полезность комментария:</b> '.$r['karma'].'</p>'; }
			
			$description .= '<p><a href="'.$url.'">Читать страницу »</a> &nbsp; <a href="'.$url_comment.'">Комментировать »</a></p>';
			
			$str .= '<item><title><![CDATA['.$title.']]></title>'."\n".
			'<guid isPermaLink="true">'.$comment_id.'</guid>'."\n".
			'<link>'.$url_comment.'</link>'."\n".
			'<description><![CDATA['.str_replace('&amp;','&', str_replace('&laquo;','«', str_replace('&raquo;','»',$description))).']]></description>'."\n".
			'<dc:creator><![CDATA['.$nickname.']]></dc:creator>'."\n".
			'<pubDate>'.date("r", $r['time_created']).'</pubDate>'."\n".
			'<comments>'.$url.'#comments</comments>'."\n".
			'</item>';
			
			$lastModified = $r['time_created']>$lastModified? $r['time_created'] : $lastModified;// данные для хедера
		}
	}
}
/*if(!$str){
	header404(__FILE__.' : !$str');
}*/

if($str){
	$description_channel = 'Последние комментарии '.($description_channel? $description_channel : 'сайта');
}else{
	$description_channel = 'К сожалению, в данном разделе пока никто не комментировал';
}

$document = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	>
<channel>
<title>'.host_clear($_SERVER['HTTP_HOST_NAME']).' | '.($title_channel? $title_channel : 'RSS').'</title>
<atom:link href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" rel="self" type="application/rss+xml" />
<link>http://'.$_SERVER['HTTP_HOST'].$link.'</link>
<language>ru</language>
<description>'.$description_channel.'</description>
<generator>CMS '.$_SERVER['HTTP_HOST'].'</generator>'."\n".$str."\n".'</channel>'."\n".'</rss>'.$lastModified;

$ex = explode('</rss>',$document);
$header['Content-type'] = 'text/xml';
$header['Last-Modified'] = $ex['1'];
headers($header);
echo $ex['0'].'</rss>';
exit;
?>
