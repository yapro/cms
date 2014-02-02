<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?getStart'.($_GET['moduleID']?'&moduleID='.(int)$_GET['moduleID']:'');// общий урл
//---------------------------------------------------------------------------------------------------------------------------------------
$str = '';// строка данных
$listing = 3;// вид листинга
$max = 34;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);
$end = ($start+$max);
$page_id = (int)$_GET['page_id'];
$name = $_GET['name']? trim(urldecode($_GET['name'])) : '';
$where = '';
if($_GET['page_id']){
	$where = 'page_id = '.$page_id;
}else if($_GET['name']){
	$where = 'pagename LIKE '._.'%'.$name.'%'._;
}
if($where){
	$where = 'WHERE '.$where;
}
//---------------------------------------------------------------------------------------------------------------------------------------
$right = '<td style="white-space:nowrap"><form action="'.$url.'" method="get"><b>Имя :</b> <input name="name" value="'.htmlspecialchars($name).'" style="width:457px"> или <b>ID :</b> <input name="page_id" value="'.$page_id.'" style="width:55px"> <input type="submit" value="Найти"></form></td>';
//---------------------------------------------------------------------------------------------------------------------------------------
$url .= '&page_id='.$page_id.'&name='.urlencode($name);

$r = @mysql_fetch_row(mysql_('SELECT COUNT(*) FROM '.P.'pages_archive '.$where));

$count = (int)$r['0'];

// находим данные архивных страниц
if($count && $q = mysql_('SELECT p.*, u.username, u.user_email 
FROM '.P.'pages_archive AS p 
LEFT JOIN '.F.'users AS u ON p.user_id = u.user_id
'.$where.'
ORDER BY time_modified DESC
LIMIT '.$start.','.$max)){
	
	while($r = mysql_fetch_assoc($q)){
		
		$str .= '<tr>
			<td class="page_id">'.$r['page_id'].'</td>
			<td><a href="'.$_SERVER['SYSTEM']['URL_PATH'].'modules/pages/right.php?moduleID='.$r['module_id'].'&form_id='.$r['form_id'].'&archive='.$r['page_id'].'.'.$r['user_id'].'.'.$r['module_id'].'.'.$r['form_id'].'.'.$r['time_modified'].'.php">'.$r['pagename'].'</a></td>
			<td title="'.str_replace('"','\"',$r['user_email']).'">'.htmlspecialchars($r['username']?$r['username']:'удален').'</td>
			<td style="white-space:nowrap;">'.date('d.m.Y H:i:s', $r['time_modified']).'</td>
		</tr>';
		
	}
}
include_once('../../libraries/listing.php');// строим листинг результата поиска
$str .= $list? '<tr class="no"><td colspan=4 style="text-align:center">'.$list.'</td></tr>' : '';

//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Записей в архиве: '.(int)$count, '', $right,'http://yapro.ru/documents/video/osobennosti-zapoleninie-stranic-poisk-i-arhiv-kopiy.html').'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1"><b>ID</b></td>
		<td width="100%"><b>Название страницы</b></td>
		<td width="95"><b>Пользователь</b></td>
		<td style="white-space:nowrap"><b>Дата изменения</b></td>
	</tr>
	'.$str.'
</table>';
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
	$(".page_id").css("cursor","pointer").attr("title","Просмотреть архивы только данной страницы").click(function(){
		$("[name=page_id]").val( $(this).text() );
		$("FORM:first").submit();
	});
});
</script>
</body>
</html>
