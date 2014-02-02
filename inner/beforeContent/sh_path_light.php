<?php
$this_page_visibility = false;// в конце строки показывать/скрывать имя текущей страницы
$index_page_visibility = true;// вначале строки показывать/скрывать корневой раздел
//--------------------------------------------------------------------------------------
if($_SERVER['REQUEST_URI']!='/'){
	
	$time = $GLOBALS['system_cache']->name['pages'];// время изменения данных таблицы страниц
	
	$file = $GLOBALS['system_cache']-> file_(dirname(__FILE__).'/sh_path_light/'.$this->id.'.html');// формируем имя кэш-файла
	
	$this->SHPathCode = '<link rel="stylesheet" href="/js/sh_path/sh_path.css" type="text/css" charset="utf-8">';
	
	if($time && $time < filemtime($file)){
		
		$this->SHPathStr = @file_get_contents($file);
		
	}else{
		
		class SHPath {
			function SHPath($page_id=0){
				
				$parent_id = $GLOBALS['system']->pages[ $page_id ]['parent_id'];
				
				if($parent_id){
					
					$GLOBALS['system']-> data('name', $parent_id);// находим данные родительского раздела
					
					// строим основную линейку пути исключая те разделы, которым выставлены специальны права доступа (нужно разобраться в последствии с правами)
					if($GLOBALS['system']->pages[ $parent_id ]['name'] && !$GLOBALS['system']->pages[ $parent_id ]['access'] && !$GLOBALS['system']->pages[ $parent_id ]['access_inherited']){
						
						$this->str = '<a href="'.url($parent_id).'" rel="'.$parent_id.'">'.$GLOBALS['system']->pages[ $parent_id ]['name'].'</a> <span class="pointer"> &#187; </span> '.$this->str;
					}
					$this-> SHPath($parent_id);
				}
			}
		}
		$SHPath = new SHPath($this->id);
		
		if($index_page_visibility){
			
			$SHPath->str = '<a href="/" rel="0">'.$this-> data('name', $GLOBALS['SYSTEM']['config']['yapro_index_id']).'</a> <span class="pointer"> &#187; </span> '.$SHPath->str;
			
		}
		
		$this->SHPathStr = '<div id="SHPath">'.$SHPath->str.($this_page_visibility? $this->pages[ $this->id ]['name'] : '').'</div>';
		
		write_($this->SHPathStr, $file);
	}
}
?>
