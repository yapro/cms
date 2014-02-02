<?php
include_once('../../libraries/access.php');
$times_min_minus_day = 30;// если данные не выбраны и ни разу не указывали, вывести данные за последние X дней
include_once('../../libraries/times.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$sql = "FROM ".P."search_keywords";
//-------------------------------------------------------
$where = "WHERE (first_time BETWEEN '".$min."' AND '".$max."' OR last_time BETWEEN '".$min."' AND '".$max."')";
//-------------------------------------------------------
if($_GET['t']){
	$url .= '&t='.$_GET['t'];
	$where .= " AND keyword LIKE '%".mysql_real_escape_string($_GET['t'])."%'";
}
//-------------------------------------------------------
$max = 50;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_query("SELECT * ".$sql.' '.$where));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';//keyword_id 	users 	amount 	keyword 	found 	first_time 	last_time
$all = array();
if($q = mysql_query("SELECT * ".$sql." ".$where." ORDER BY found, amount DESC, users DESC LIMIT ".$start.', '.$max)){
	
	while($r = mysql_fetch_assoc($q)){
		
		if(!$r['keyword']){ continue; }
		
		$str .= '<tr>
			<td><p><input type="text" readonly value="'.htmlspecialchars($r['keyword']).'"></p></td>
			<td><p>'.$r['amount'].'</p></td>
			<td><p>'.$r['found'].'</p></td>
			<td><p>'.$r['users'].'</p></td>
			<td><p>'.date("d.m.Y H:i:s", $r['first_time']).'</p></td>
			<td><p>'.date("d.m.Y H:i:s", $r['last_time']).'</p></td>
		</tr>';
		
	}
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Какие фразы искали на сайте','','<td style="white-space:nowrap"><form action="'.$url.'" method="get">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">
			<b>Фраза :</b> <input name="t" value="'.htmlspecialchars($_GET['t']).'" style="width:275px"> 
			'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').'
'.($str? '
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td title="Ключевая фраза которую искали"><b>Фраза</b></td>
			<td width="1" title="Сколько раз искали на сайте (не количество посетителей)"><b>Искали</b></td>
			<td width="1" title="Количество найденных страниц"><b>Нашли</b></td>
			<td width="1" title="Сколько раз искали зарегистрированные пользователи (не количество пользователей)"><b>Юзеры</b></td>
			<td width="125" title="Дата времени, когда искали в первый раз"><b>Первый&nbsp;раз</b></td>
			<td width="125" title="Дата времени, когда искали в последний раз"><b>Последний&nbsp;раз</b></td>
		</tr>'.$str.'
	</table>'.$list : '<p style="padding:10px">За выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	mouseMovementsClass("overflowTable");
	
	$(":text").addClass("input_text").dblclick(function(){ this.select(); }).each(function(){
		$(this).attr("title", $(this).val() );
	});
	
	$(".yaproSortTR:first").css("cursor","help");
});
</script>
</body>
</html>
