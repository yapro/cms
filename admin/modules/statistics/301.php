<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$where = "WHERE date_time BETWEEN '".$min."' AND '".$max."'";
//---------------------------------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_GET['id'] && is_numeric($_GET['id'])){
	
	// находим данные о странице
	$r = @mysql_fetch_assoc(mysql_query("SELECT from_domain, from_path, error_link, to_path 
	FROM ".P."pages_301 WHERE id = ".$_GET['id']));
	
	$str = '';
	
	// находим строки по условиям просмотра
	if($r && $q = mysql_("SELECT date_time, ip, browser_version, browser_version_full, browser_name, browser_name_full 
	FROM ".P."pages_301 WHERE 
	from_domain = "._.$r['from_domain']._." AND 
	from_path = "._.$r['from_path']._." AND 
	error_link = "._.$r['error_link']._." AND 
	to_path = "._.$r['to_path']._." AND 
	date_time BETWEEN "._.$min._." AND "._.$max._."
	ORDER BY date_time")){
		while($r = mysql_fetch_assoc($q)){
			$ip = long2ip($r['ip']);
			$str .= '<tr>
				<td><p>'.date("d.m.Y H:i:s", $r['date_time']).'</p></td>
				<td><p><a href="https://www.nic.ru/whois/?query='.$ip.'" target="_blank">'.$ip.'</a></p></td>
				<td><p><input type="text" readonly value="'.htmlspecialchars($r['browser_name']).'" class="input_text"></p></td>
				<td><p><input type="text" readonly value="'.htmlspecialchars($r['browser_version_full']).'" class="input_text"></p></td>
				<td><p><input type="text" readonly value="'.htmlspecialchars($r['browser_name_full']).'" class="input_text"></p></td>
			</tr>';
		}
		$str = $str? '<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
				<tr class="yaproSortTR">
					<td width="125"><p><b>Дата времени</b></p></td>
					<td width="100"><p><b>IP посетителя</b></p></td>
					<td width="150"><p><b>Браузер</b></p></td>
					<td width="50"><p><b>Версия</b></p></td>
					<td><p><b>Полное имя</b></p></td>
				</tr>'.$str.'</table>' : '';
	}
	echo '{"tr":'.ajaxHTML($str).'}';
	exit;
}
//---------------------------------------------------------------------------------------------------
if(access('w') && $_POST['ignore']){
	foreach($_POST['ignore'] as $id=>$checked){
		mysql_query("UPDATE ".P."pages_301 SET path_ignored = 1 WHERE id = ".(int)$id."");
	}
	$_SERVER['SYSTEM']['MESSAGE'] = 'Обновление списка игнорирования успешно выполнено!';
}
//---------------------------------------------------------------------------------------------------
$sql = "FROM ".P."pages_301 ".$where." GROUP BY from_domain, from_path, error_link, to_path";
//-------------------------------------------------------
$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_query("SELECT COUNT(*) ".$sql));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_query("SELECT id, COUNT(DISTINCT ip) AS visitors, COUNT(*) AS visits, 
	from_domain, from_path, error_link, to_path, MAX(date_time) AS latest_time 
	".$sql." ORDER BY visitors DESC, visits DESC LIMIT ".$start.', '.$max)){
	
	// Игнорируемый страницы
	if($q2 = mysql_query("SELECT id FROM ".P."pages_301 WHERE path_ignored = 1")){
		while($r = mysql_fetch_assoc($q2)){
			$path_ignored[ $r['id'] ] = true;
		}
	}
	
	$that_host = host_clear($GLOBALS['SYSTEM']['config']['server_name']);
	
	while($r = mysql_fetch_assoc($q)){
		
		if($r['from_domain'] && $r['from_domain'] == $that_host){
			$r['from_domain'] = '';
		}
		
		if(!$r['from_domain'] && (!$r['from_path'] || $r['from_path']=='/')){
			$r['from_path'] = 'адрес набран вручную, с избранного, по закладке или браузер закэшеровал страницу но не файлы';
			continue;// - т.к. такой адрес исправить не получится
		}
		
		$str .= '<tr class="yaproSortTR">
			<td width="150" align="right"><p><b>'.$r['visitors'].'</b></p></td>
			<td><p>посетител'.noun($r['visitors'], 'ь', 'я', 'ей').' выполнил'.noun($r['visits'], '', 'и', 'и').' '.$r['visits'].' переход'.noun($r['visits'], '', 'а', 'ов').'</p></td>
		</tr><tr>
			<td><p>На неправильный адрес: </p></td>
			<td><p><input type="text" readonly value="'.htmlspecialchars($r['error_link']).'"></p></td>
		</tr><tr>
			<td><p>Со страницы: </p></td>
			<td><p><input type="text" readonly value="'.htmlspecialchars($r['from_domain'].$r['from_path']).'"></p></td>
		</tr><tr>
			<td><p>Правильный адрес: </p></td>
			<td><p><input type="text" readonly value="'.htmlspecialchars($r['to_path']).'"></p></td>
		</tr><tr>
			<td><p>Последний раз: </p></td>
			<td><p>'.date("d.m.Y H:i:s", $r['latest_time']).' - <a href="#" alt="'.$r['id'].'">подробнее</a>'.($path_ignored[ $r['id'] ]?'':' - <a href="#" alt="'.$r['id'].'">игнорировать</a>').'</p></td>
		</tr>';
		
		$all['a2'] += $r['visits'];
		$all['a3'][$r['from_domain'].$r['from_path']] = true;
		$all['a4'][$r['error_link']] = true;
		$all['a5'][$r['to_path']] = true;
		$all['a6'][date("d.m.Y", $r['latest_time'])] = true;
	}
	
	// находим кол-во уникальных посетителей
	$spec = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT ip) FROM ".P."pages_301 ".$where));
	$all['a1'] = $spec['0'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Переходы по старым адресам ссылок с разных мест сайтов','','<td style="white-space:nowrap"><form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').'
'.($str? '<form action="'.$url.'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		'.$str.'
	</table>
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Посетителей</b></td>
			<td width="1"><b>Переходов</b></td>
			<td><b style="color:#FF0000">Неправильных адресов</b></td>
			<td><b>С страниц</b></td>
			<td><b>Правильных адресов</b></td>
			<td width="125"><b>За дней</b></td>
		</tr>
		<tr>
			<td><p>'.$all['a1'].'</p></td>
			<td><p>'.$all['a2'].'</p></td>
			<td><p>'.count($all['a3']).'</p></td>
			<td><p>'.count($all['a4']).'</p></td>
			<td><p>'.count($all['a5']).'</p></td>
			<td><p>'.count($all['a6']).'</p></td>
		</tr>
	</table></form>'.$list : '<p style="padding:10px">Для выбранного сайта за выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

$(":text").addClass("input_text").click(function(){
	this.select();
}).dblclick(function(){
	window.open( "http://" + ( (this.value.substr(0, 1)=="/")? "<?=$GLOBALS['SYSTEM']['config']['server_name']?>" : "") + this.value);
}).css("cursor","pointer").each(function(){
	$(this).attr("title", $(this).val() );
});

$(":checkbox").css("vertical-align","middle");

mouseMovementsClass("overflowTable");

$(":text").css("cursor","pointer").each(function(){
	$(this).attr("title", $(this).val() );
});

$(".overflowTable A").click(function(){
	
	var a = this;
	
	if(!$(a).data("ajax_id")){
		
		var id = "ajax_pictures_"+Math.round(Math.random() * 1000000000);
		
		$(a).data("ajax_id", id);
		
		$.fancybox.showActivity();
		
		$.getJSON(document.location.pathname + "?id="+ $(a).attr("alt")+"<?=$date?>", function(r){
			
			$.fancybox.hideActivity();
			
			if(r.tr && r.tr !=""){
				$(a).closest("TR").after('<tr id="'+ id +'"><td colspan="2" style="padding:0px;">' + r.tr + '</td></tr>');
				mouseMovementsClass("overflowTable");
			}
		});
		
	}else{
		
		var id = $(a).data("ajax_id");
		
		if( $("#"+id).is(":visible") ){
			$("#"+id).hide();
		}else{// is(':hidden')
			$("#"+id).show();
		}
	}
	return false;
});
</script>
</body>
</html>
