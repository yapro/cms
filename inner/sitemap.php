<?php
/* Проверяется через http://www.xml-sitemaps.com/index.php?op=validate-xml-sitemap&go=1&sitemapurl=http://yapro.ru/sitemap.xml&submit=Validate
Почитать о написании скрипта можно тут - http://groups.google.com/group/google-sitemaps-ru/topics?start=50&sa=N
или от яндекса - http://webmaster.ya.ru/replies.xml?item_no=955&ncrnd=2042
Проверка:
'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84
http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
'.$map->s.'
</urlset>';
*/
header("Content-type: text/xml; charset=utf-8");

$file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-3).'xml');// формируем имя кэш-файла

if($GLOBALS['system_cache']->name['pages'] && $GLOBALS['system_cache']->name['pages'] < filemtime($file)){
	
	$document = @file_get_contents($file); 
	
}else{
	
	class map{
		function map(){
			
			if($q = mysql_(sql('page_id, parent_id, time_modified'))){
				
				$this->parent_id__page_id = $this->page_id__data = array();
				
				$i = 0;
				
				while($r = mysql_fetch_assoc($q)){
					
					$url = url($r['page_id'],2);
					if($url['url_type']==3){
						continue;
					}
					
					$this->parent_id__page_id[ $r['parent_id'] ][ $r['page_id'] ] = $url['url'];
					$this->page_id__time_modified[ $r['page_id'] ] = $r['time_modified'];
				}
				
				$this->priority = 1;
				$this->priority_max = 10;
				
				$this-> xml();
				
			}
		}
		function xml($parent_id=0){
			
			if(!$this->parent_id__page_id[ $parent_id ]){ return false; }
			
			foreach($this->parent_id__page_id[ $parent_id ] as $page_id=>$url){
				
				$time_modified = $this->page_id__time_modified[ $page_id ];
				
				if(!$time_modified){ $time_modified = time()-15000; }
				
				$url = ($page_id==$GLOBALS['SYSTEM']['config']['yapro_index_id'])? '/' : $url;
				
				$priority = 0.5;
				if($this->priority > 0.5){
					
					$priority = $this-> priority;
					
					$this->priority_max--;
					
					if(!$this->priority_max){
						$this->priority_max = 10;
						$this->priority -= 0.1;
					}
				}
				
				$this->xml .= '<url><loc>http://'. $_SERVER['HTTP_HOST'].$url.'</loc><lastmod>'.date('Y-m-d', $time_modified).'T'.date('H:i:s', $time_modified).'+00:00</lastmod><priority>'.str_replace(',','.',$priority).'</priority></url>';
				
				$this-> xml($page_id);
			}
		}
	}
	$map = new map();
	$document = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.google.com/schemas/sitemap/0.84">'.$map->xml.'</urlset>';
	write_($document, $file);
}
echo $document;
exit;
?>