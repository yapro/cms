<?php
// библиотека смайлов (автоматически кэшируется)

$GLOBALS['lebnik_smiles']['php'] = array();
$GLOBALS['lebnik_smiles']['file_js'] = '/cache/comment_smiles.js';
$GLOBALS['lebnik_smiles']['file_php'] = $_SERVER['DOCUMENT_ROOT'].'/cache/comment_smiles.php';// узнаем имя кэш-файла

// если кэш в актуальном состоянии
if($GLOBALS['system_cache']->name['smiles'] && $GLOBALS['system_cache']->name['smiles'] <= filemtime($GLOBALS['lebnik_smiles']['file_php'])){
	
	include_once($GLOBALS['lebnik_smiles']['file_php']);// подгружаем кэш-данные
	
}else{
	
	$js = '';
	$path = '/js/comments/smiles';// путь к директории смайлов
	$curpath = $_SERVER['DOCUMENT_ROOT'].$path;
	
	if($dir = dir($curpath)){
		$img_files = array();
		while ($file = $dir-> read()) {
			if($file != "." && $file != ".." && !is_dir($curpath.$file)){
				$img_files[$file]++;
			}
		}
		$dir-> close();
		
		if($img_files){
			
			if($q = mysql_query("SELECT smile_id, main, smile, alt, img FROM ".P."smiles ORDER BY priority DESC")){
				while($r=mysql_fetch_assoc($q)){
					
					if(!$img_files[ $r['img'] ]){ continue; }
					
					// обрабатываем последний слэш
					if(substr($r['smile'],-1)=='\\'){$smile = $r['smile'].'\\'; }else{$smile = $r['smile'];}
					
					$img = '<img src="'.$path.'/'.$r['img'].'" title="'.htmlspecialchars($r['alt']).'" alt="'.htmlspecialchars($smile).'">';
					
					if($r['main']){ $js .= $img; }
					
					$GLOBALS['lebnik_smiles']['php'][ ' '.htmlspecialchars($r['smile']) ] = $img;
				}
			}
		}
	}
	
	$GLOBALS['system_cache']-> update('smiles');// обновляю кэш-записи о смайлах
	
	// записываем данные о смайлах и файлах изображений в кэш-файл
	write_('<?php '.$GLOBALS["phpToStr"]->toStr($GLOBALS['lebnik_smiles']['php'], 'GLOBALS[\'lebnik_smiles\'][\'php\']').' ?>', $GLOBALS['lebnik_smiles']['file_php']);
	
	// записываем яваскрипт строку смайлов в кэш-файл
	write_("var comment_smiles = '".str_replace("'", "\'", $js)."';", $GLOBALS['lebnik_smiles']['file_js']);
}
?>
