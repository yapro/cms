<?php
// плагин выводит список заметок с ссылками на cтраницы по заданному $parent_id
$max = $max_important? $max_important : 7;// максимальное количество
$listing = 1;// вид листинга
$where = $where_important? $where_important : 'parent_id='.$this->id;// условия вывода
$start = 0;
$orderby = $orderby_important? $orderby_important : '';// условия сортировки
// если объявить переменную $news_parent_name, то вместо Читать далее - будет показано Имя раздела в котором размещена статья
//-----------------------------------------------------------------------------------------

if(!$without_folio && !$SEARCH_LIB){ include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/folio.php'); }

//-----------------------------------------------------------------------------------------

//$where .= ($where?' AND ':'');

//-----------------------------------------------------------------------------------------

$r = @mysql_fetch_row(mysql_(sql('COUNT(*)', $where)));
$count = $r['0'];
$start = $folio * $max;// показываем записи начиная с start

//-----------------------------------------------------------------------------------------

$select = 'page_id,
parent_id,
time_created,
time_modified,
name,
article,
img'.$select;

if($spec_kids_page_table){
	$select = $spec_kids_page_table.str_replace("\n", '', str_replace(',', ','.$spec_kids_page_table, $select));
	$sql = 'SELECT '.$select.' '.$spec_kids_from.' WHERE '.sql_where($spec_kids_page_table).
	' '.$spec_kids_order.' LIMIT '.$start.','.$max;
}else{
	$sql = sql($select, $where, 'page_id DESC', $start, $max);
}
$spec_kids_page_table = $select = $where = '';

//-----------------------------------------------------------------------------------------

if($count && $q = mysql_($sql)){
	$pages = array();
	while($r = mysql_fetch_assoc($q)){
		$pages[] = $r;
	}
	//--------------------
	$i = 0;
	$i_count = count($pages);
	$clear_first = '';
	
	$tm = $this->pages[ $this->id ]['time_modified'];
	foreach($pages as $r){
		
		$t = $r['time_modified']? $r['time_modified'] : $r['time_created'];
		$this->pages[ $this->id ]['time_modified'] = ($t > $tm)? $t : $tm;
		
		//----------------------------------------------------------------------------
		
		$year = ( date('Y', $r['time_created']) == date('Y') )? '' : ' '.date('Y', $r['time_created']).' года';
		
		if(date('Ymd', $r['time_created'])===date('Ymd')){
			
			if($r['time_created']>(time()-3600)){
				
				$x = round((time()-$r['time_created'])/60);
				
				$t = $x.' '.noun($x, 'минуту', 'минуты', 'минут').' назад';
				
			}else if($r['time_created']>(time()-36000)){
				
				$x = round((time()-$r['time_created'])/3600);
				
				$t = $x.' '.noun($x, 'час', 'часа', 'часов').' назад';
				
			}else{
				
				$t = 'сегодня в '.date('H:i', $r['time_created']);
				
			}
		}else if(date('j',$r['time_created'])===date('j', (time()-86400))){
				
				$t = 'вчера в '.date('H:i', $r['time_created']);
				
		}else{
			
			$t = date('j', $r['time_created']).' '.$this->ru['month'][date('n', $r['time_created'])].$year;
			
			if(time()-(30*86400) < $r['time_created']){
				$t .= ' в '.date('H:i', $r['time_created']);
			}
		}
		
		//----------------------------------------------------------------------------
		
		$i++;
		
		//----------------------------------------------------------------------------
		
		$url = url($r['page_id']);
		
		$clear = clear($r['name']);
		
		//----------------------------------------------------------------------------
		
		if($news_parent_name){
			$read = '<a href="'.url($r['parent_id']).'">'.$this-> data('name',$r['parent_id']).'</a>';
		}else{
			$read = '<a href="'.$url.'" rel="nofollow">Подробнее »</a>';
		}
		
		//----------------------------------------------------------------------------
		
		$a = pagebreak($r['article']);
		
		if($a['1']){
			
			$notice = $a['0'];
			
		}else if(!$a['1'] && $r['img']){
			
			$notice = '<a href="'.$url.'" style="border:none;"><img src="'.$r['img'].'" alt="'.$clear.'" style="margin-top: 5px;"></a>';
			$r['img'] = '';
			
		}else if($a['0']){
			
			$notice = $a['0'];
			
		}else{
			
			$notice = '';
			
		}
		
		//----------------------------------------------------------------------------
		
		$this->systemMeta .= $r['name'].' ';
		$kids_names .= $r['name'].', ';
		
		//----------------------------------------------------------------------------
		
		$document .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="KidsNotice">
			<tr>
			   	'.($r['img']? '<td width=1><a href="'.$url.'" style="border:none"><img src="'.$r['img'].'" alt="'.$clear.'" width="170"></a></td><td class="Right">' : '<td>').'
					<div class="name"><a href="'.$url.'">'.$r['name'].'</a></div>
					'.($notice? '<div class="notice">'.$notice.'</div>' : '').'
					<div class="footer">
						'.$t.'
						<span class="comments">'.($r['comments']?'<a href="'.$url.'#comments"> Комментариев '.$r['comments'].'»</a>':'Комментариев нет').'</span>
						<span  class="read">'.$read.'</span>
					</div>
				</td>
			</tr>
		</table>
		'.(($i==$i_count)?'':'<div class="cut"></div>');
		
	}
	
	if($folio && $clear_first){
		$this->pages[ $this->id ]['title'] = ($this->pages[ $this->id ]['title']? $this->pages[ $this->id ]['title'] : $this->pages[ $this->id ]['name']).' : '.$clear_first;
		$this->pages[ $this->id ]['keywords'] = '';
	}
	
	if(!$document){// если указали страницу листинга больше чем может быть - получится страница без данных
		header404(__FILE__);
	}
	
	if($count > 0){
		$this->pages[ $this->id ]['description'] .= mb_substr($kids_names,0,-2).' '.$description_pluse;
		$this->pages[ $this->id ]['keywords'] .= $kids_names.$keywords_pluse;
	}
}
//-----------------------------------------------------------------------------------------

if(!$without_folio){ include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/listing'.($SEARCH_LIB?(($SEARCH_LIB===true)?'':$SEARCH_LIB) : '').'.php'); }

?>