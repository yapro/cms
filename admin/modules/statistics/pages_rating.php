<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
//---------------------------------------------------------------------------------------------------
$href = '&order_by=';//посетителям
$a_2 = '';//баллам
$a_3 = '';//оценкам
$info = 'посетителям, баллам, оценкам';

if($_GET['order_by']==3){
	
	$info = str_replace('посетителям', '<a href="'.$url.$href.'1">посетителям</a>',
	str_replace('баллам', '<a href="'.$url.$href.'2">баллам</a>', $info));
	
	$order_by = 'su DESC, u DESC, s DESC';
	
	$url .= $href.'3';
	
}else if($_GET['order_by']==2){
	
	$info = str_replace('посетителям', '<a href="'.$url.$href.'1">посетителям</a>',
	str_replace('оценкам', '<a href="'.$url.$href.'3">оценкам</a>', $info));
	
	$order_by = 's DESC, u DESC';
	
	$url .= $href.'2';
	
}else{// 
	
	$info = str_replace('баллам', '<a href="'.$url.$href.'2">баллам</a>',
	str_replace('оценкам', '<a href="'.$url.$href.'3">оценкам</a>', $info));
	
	$order_by = 'u DESC, s DESC';
	
	$url .= $href.'1';
	
}
//---------------------------------------------------------------------------------------------------
$sql = 'FROM '.P.'pages_rating GROUP BY page_id';
//-------------------------------------------------------
$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_('SELECT COUNT(*) '.$sql));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_('SELECT page_id, COUNT(user_ip) AS u, SUM(rating) AS s, ( SUM( rating ) / COUNT( user_ip ) ) AS su 
'.$sql.' ORDER BY '.$order_by.' LIMIT '.$start.', '.$max)){
	
	while($r = mysql_fetch_assoc($q)){
		
		$a = @mysql_fetch_assoc(mysql_('SELECT page_id, name FROM '.P.'pages WHERE page_id = '.$r['page_id']));
		
		$str .= '<tr>
			<td><p>'.$r['u'].'</p></td>
			<td><p>'.$r['s'].'</p></td>
			<td><p>'.round($r['su'], 2).'</p></td>
			<td><p><a href="/s/p?'.$r['page_id'].'" target="_blank">'.$a['name'].'</a></p></td>
		</tr>';
		
		$all['a2'] += $r['s'];
		$all['a3'][$r['page_id']] = true;
	}
	
	// находим кол-во уникальных посетителей
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT ip) FROM ".P."pages_rating ".$where));
	$all['a1'] = $spec['0'];
}

//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Популярность страниц выставленная посетителями, с сортировкой по: '.$info).'
'.($str? '
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Посетителей</b></td>
			<td width="1"><b>Баллов</b></td>
			<td width="1"><b>Оценка</b></td>
			<td><b>Страница</b></td>
		</tr>
		'.$str.'
	</table>'.$list : '<p style="padding:10px">За выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

mouseMovementsClass("overflowTable");

</script>
</body>
</html>
