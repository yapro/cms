<?php
// создает карту древовидного вида, на основе проверки разделов на наличие дочерних страниц, с сортировкой position ASC
class map {
	function map($parent_id){
		if($q = mysql_(sql('page_id, parent_id, name'))){
			while($r = mysql_fetch_assoc($q)){
				$this->parent_id__page_id[ $r['parent_id'] ][ $r['page_id'] ] = url($r['page_id']);
				$this->page_id__name[ $r['page_id'] ] = $r['name'];
			}
			if($this->page_id__name){
				$this->explorer();
			}
		}
	}
	function explorer($parent_id=0){
		
		if(!$this->parent_id__page_id[ $parent_id ]){ return false; }
		
		foreach($this->parent_id__page_id[ $parent_id ] as $page_id => $url){
			
			if($this->page_id__name[ $page_id ]){
				
				$i++;
				
				$this->s .= '<span style="padding-left: '.($this->i*2).'0px;">'.$i.'. <a href="'.$url.'">'.strip_tags($this->page_id__name[ $page_id ]).'</a></span><br />';
				
				$this->i++;
				$this-> explorer($page_id);
				$this->i--;
			}
		}
	}
}

$file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-3).'txt');// формируем имя кэш-файла

if($GLOBALS['system_cache']->name['pages'] && $GLOBALS['system_cache']->name['pages'] < filemtime($file) && $TEST_20110105){
	
	$document = @file_get_contents($file); 
	
}else{
	
	$map = new map("0");
	$document = $map->s;
	write_($document, $file);
}
?>
