<?php
/*
Скрипт проверяет наличие данных в переменной $count и по переменным строит линейку страниц присваивая ее переменной $list

Нуждается в переменных:
$url - урл текущей страницы (не обязательна)
$count - всего страниц
$folio - текущий номер страницы
$max - кол-во ссылок которое выводится на странице

Рекомендуемые CSS-правила:
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
*/

$list = '';// строим листинг результата поиска
if($count){
	if(!$url){ $url = $_SERVER['PHP_SELF'].'?nothing'; }// общий урл
	if(!$listing){ $listing = 3; }// если вид листинга не указан, то делаем его максимальным
	if($count>$max){
		$last = $separator = $next = '';
		if($listing==1 || $listing==3){// строим линейку страниц
			$n = 0;
			for($i=0; $i<$count; $i+=$max){
				$separator .= '<span class="Page">';
				if($n==$folio){
					$separator .= '<em>'.($n+1).'</em>';
				}else{
					$separator .= '<a href="'.$url.'&folio='.$n.'">'.($n+1).'</a>';
				}
				$separator .= "</span>\n";
				$n++;
			}
			if($n<2){ $separator = ''; }
		}
		if($listing>1){// строим Предыдущая | Следующая
			$last = $folio?'<a class="inThePresent" href="'.$url.'&folio='.($folio-1).'">&laquo; Предыдущая</a> ':'';
			$next = ($count>( ($folio*$max) + $max) )? ' <a class="inThePast" href="'.$url.'&folio='.($folio+1).'">Следующая &raquo;</a>' : '';
		}
		$list = '<div id="Listing">'.$last.$separator.$next.'</div>';
	}
}
?>
