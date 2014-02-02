<?php
// скрипт проверяет наличие данных в переменной $document и по переменным строит линейку страниц
// нуждается в переменных: $count - всего страниц, $folio - текущий номер страницы, $max - кол-во ссылок которое выводится на странице, $listing - вид листинга (1 - строить только линейку страниц 1, 2, 3), (2 - строить только В прошлое | К настоящему), (3 - сочитать 1 и 2)

if(!$document){
	
	header404(__FILE__.' : текст страницы не обнаружен, возможно злоумышленником указана $folio больше чем может быть на самом деле');
}

$index_page = ($this->id==$GLOBALS['SYSTEM']['config']['yapro_index_id'])? 1 : 0;

// строим листинг
if($count > $max){
	
	$last = $separator = $next = '';
	
	$url = $GLOBALS['SYSTEM']['page_url'][ $this->id ]['url'];
	
	// строим линейку страниц
	$n = 0;
	for($i=0; $i<$count; $i+=$max){
		
		if( ($n>($folio-5) && $n<($folio+5)) || ($folio<5 && $n<10) ){
			
			$separator .= '<span class="Page">';
			
			if($n==$folio){
				
				$separator .= '<em>'.($n+1).'</em>';
				
			}else{
				
				$separator .= '<a href="'.($n? $url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?p='.$n : ($index_page? '/' : $url.$GLOBALS['SYSTEM']['config']['yapro_page_ext']) ).'">'.($n+1).'</a>';
				
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
		
		$separator .= '<span class="Page"><a href="'.$url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?p='.($n-1).'">'.$n.'</a></span>';
		
	}
	
	// строим В прошлое | К настоящему
	if($folio=='1'){
		$last = $index_page? '/' : $url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'];
	}else{
		$last = $url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?p='.($folio-1);
	}
	$last = $folio?'<a class="inThePresent" href="'.$last.'">&laquo; К настоящему</a>':'';
	
	$next = ($count>( ($folio*$max) + $max) )? '<a class="inThePast" href="'.$url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?p='.($folio+1).'">В прошлое &raquo;</a>' : '';
	
	if($listing==1){
		$last = 'Страницы: &nbsp;';
		$next = '';
	}
	
	$GLOBALS['system_listing'] = '<!--NoRedirect--><div class="Listing" alt="'.$n.'~K~'.$index_page.'~K~'.$folio.'~K~'.$url.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'?p=~PagerNumber~">'.$last.$separator.$next.'</div><!--/NoRedirect-->';
}
?>