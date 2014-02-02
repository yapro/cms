<?php
$this_page_visibility = false;// в конце строки показывать/скрывать имя текущей страницы
$index_page_visibility = true;// вначале строки показывать/скрывать корневой раздел
//--------------------------------------------------------------------------------------
if($_SERVER['REQUEST_URI']!='/'){
	
	$time = $GLOBALS['system_cache']->name['pages'];// время изменения данных таблицы страниц
	
	$file = $GLOBALS['system_cache']-> file_(dirname(__FILE__).'/sh_path/'.$this->id);// формируем имя кэш-файла
	
	$file_html = $file.'.html';
	$file_js = $file.'.js';
	
	$this->SHPathCode = '<link rel="stylesheet" href="/js/sh_path/sh_path.css" type="text/css" charset="utf-8">
	<script language="Javascript" type="text/javascript" charset="utf-8" src="/js/sh_path/sh_path.js"></script>
	<script type="text/javascript" charset="utf-8" src="'.str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_js).'?'.$time.'"></script>';
	
	if($time && $time < filemtime($file_html)){
		
		$this->SHPathStr = @file_get_contents($file_html);
		
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
				}
				
				if($parent_id != $GLOBALS['system']->pages[ $GLOBALS['system']->id ]['parent_id']){
					
					// находим страницы по идентификатору $parent_id
					if($q = mysql_(sql('page_id, name', 'parent_id='.$parent_id, 'position, name'))){
						
						$inner = '';
						
						while($r = mysql_fetch_assoc($q)){
							
							if($r['page_id']==$page_id){ continue; }
							
							// исключаем текущую страницу И главную страницу сайта
							if($r['page_id']==$GLOBALS['system']->id || $r['page_id']==$GLOBALS['SYSTEM']['config']['yapro_index_id']){ continue; }
							
							$inner .= '<p><a href="'.url($r['page_id']).'">'.$r['name'].'</a></p>';
							
						}
						
						if($inner){
							
							$this->js .= 'SHPath['.$parent_id.'] = $(\'<table cellpadding="0" cellspacing="0" border="0" class="SHPathSubMenu" alt="'.$parent_id.'"><tr><td class="T0" /><td class="T1" /><td class="T2" /></tr><tr><td class="T3" /><td><div>'.str_replace("'", "\'", str_replace("\n", '', str_replace("\r", '', $inner))).'</div></td><td class="T4" /></tr><tr><td class="T5" /><td class="T6" /><td class="T7" /></tr></table>\').prependTo(document.body);'."\n";
							
						}
					}
				}
				
				if($parent_id){// исключаем рекурсию на 0 (нулевое значение)
					$this-> SHPath($parent_id);
				}
			}
		}
		$SHPath = new SHPath($this->id);
		
		if($index_page_visibility){
			
			$SHPath->str = '<a href="/" rel="0">'.$this-> data('name', $GLOBALS['SYSTEM']['config']['yapro_index_id']).'</a> <span class="pointer"> &#187; </span> '.$SHPath->str;
			
		}
		
		$this->SHPathStr = '<div id="SHPath">'.$SHPath->str.($this_page_visibility? $this->pages[ $this->id ]['name'] : '').'</div>';
		
		if($this->SHPathStr){
			write_($this->SHPathStr, $file_html);
		}
		if($SHPath->js){
			write_($SHPath->js, $file_js);
		}
	}
}
?>
