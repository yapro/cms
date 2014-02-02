<?php
$url = explode('/s/r'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?', $_SERVER['REQUEST_URI']);
$url = $url['1'];
//echo $url; exit;
if(mb_substr($url, 0, 4)=='http' || mb_substr($url, 0, 6)=='ftp://'){
	
	$parse_url = @parse_url_($url);
	
	if($parse_url['scheme'] && $parse_url['host'] && str_replace('www.', '', $parse_url['host'])!=str_replace('www.', '', $_SERVER['HTTP_HOST'])){
		
		//echo '<META HTTP-EQUIV="Refresh" CONTENT="1; URL='.$url.'">';
		/*if($GLOBALS['SYSTEM']['config']['yapro_statistics_affiliates']){// если: включена опция логов переходов на аффилаты
			
			// специальное изменение серверных переменных, для определения нужных значений функцией statistics()
			$parse_to = @parse_url_($url);
			if(!$parse_to['path']){
				$path_to = '/';
			}else{// проверка нужна, т.к. знак вопроса удаляется при парсенге урл
				$path_to = trim($parse_to['path'].($parse_to['query']? '?'.$parse_to['query']:''));
			}
			$GLOBALS['SYSTEM']['statistics']['TO_HOST'] = $parse_to['host'];// куда - host сайта-афилата
			$GLOBALS['SYSTEM']['statistics']['TO_URI'] = $path_to;// куда - урл сайта-афилата
			
			statistics('yapro_statistics_affiliates');
		}
		*/
		header("location: ".$url);
		exit;
	}else{
		header404(__FILE__.' : не удалось правильно распарсить');
	}
}else{
	header404(__FILE__.' : урл не найден');
}
?>
