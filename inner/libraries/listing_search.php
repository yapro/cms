<?php
// скрипт проверяет наличие данных в переменной $document и строит линейку страниц по следующим переменным:
// $listing - вид листинга, $count - всего страниц, $folio - текущий номер страницы, $max - кол-во ссылок которое выводится на странице

$url = $GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'].$GLOBALS['SYSTEM']['config']['yapro_page_ext'];


if(!$document){
	
	$document = '<p>К сожалению, по Вашему запросу ничего не найдено.<br>'.($SEARCH_EMPTY_TEXT? $SEARCH_EMPTY_TEXT:'Попробуйте, сформулировать запрос иначе.').'</p>';
	
}else if($search && $count>$max){// строим листинг
	
	$search = '?search='.htmlAccess($search, false);
	
	$last = $separator = $next = '';
	
	$n = 0;
	for($i=0; $i<$count; $i+=$max){
		
		if( ($n>($folio-5) && $n<($folio+5)) || ($folio<5 && $n<10) ){
			
			$separator .= '<span class="Page">';
			
			if($n==$folio){
				
				$separator .= '<em>'.($n+1).'</em>';
				
			}else{
				
				$separator .= '<a href="'.$url.$search.($n?'&p='.$n : '').'">'.($n+1).'</a>';
				
			}
			
			$separator .= "</span>\n";
			
		}
		$n++;
	}
	if($n<2){
		
		$separator = '';
		
	}else if($folio < ($n-5) && $count > ($n*$max)){
		
		if($folio < ($n-6)){
			
			$separator .= '<span class="Page">...</span>'; 
			
		}
		
		$separator .= '<span class="Page"><a href="'.$url.$search.'&p='.($n-1).'">'.$n.'</a></span>';
		
	}
	
	$GLOBALS['system_listing'] = '<div class="Listing" alt="'.$n.'~K~0~K~'.(int)$folio.'~K~'.$url.$search.'&p=~PagerNumber~">'.$last.$separator.$next.'</div>';
}
?>
