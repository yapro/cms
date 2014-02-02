<?php
include_once('../../libraries/access.php');
if(!$_POST){
	$_POST['min'] = date('d.m.Y', (time()-90000) );
	$_POST['max'] = date('d.m.Y');
}
include_once('../../libraries/times.php');
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$where = 'WHERE date_time BETWEEN '._.$min._.' AND '._.$max._;
//---------------------------------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_GET['id'] && is_numeric($_GET['id'])){
	
	// находим данные о странице
	$r = @mysql_fetch_assoc(mysql_('SELECT from_domain, from_path, path_404 FROM '.P.'pages_404 WHERE id = '.$_GET['id']));
	/*
	$search_system['1'] = 'Yandex';
	$search_system['2'] = 'Google';
	$search_system['3'] = 'Rambler';
	$search_system['4'] = 'Aport';
	$search_system['5'] = 'Mail';
	$search_system['6'] = 'Yahoo';
	$search_system['7'] = 'Msn / Live';
	$search_system['8'] = 'Bing';
	*/
	$str = '';
	
	// находим строки по условиям просмотра
	if($r && $q = mysql_('SELECT date_time, from_search_system, ip, browser_version, browser_version_full, browser_name, browser_name_full 
	FROM '.P.'pages_404 WHERE 
	from_domain = '._.$r['from_domain']._.' AND 
	from_path = '._.$r['from_path']._.' AND 
	path_404 = '._.$r['path_404']._.' AND 
	date_time BETWEEN '._.$min._.' AND '._.$max._.'
	ORDER BY date_time')){
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
if(access('w') && isset($_POST['ignored']) && $_POST['id'] && is_numeric($_POST['id'])
&& mysql_('UPDATE '.P.'pages_404 SET path_ignored = '.(int)$_POST['ignored'].' '._.'WHERE'._.' id = '.$_POST['id'])){
	echo '{"msg":"ok"}';//Обновление списка игнорирования успешно выполнено!
	exit;
}
//---------------------------------------------------------------------------------------------------
if(access('d') && $_POST['delete']){
	if(mysql_('DELETE FROM '.P.'pages_404 WHERE path_ignored != 1')){
		$msg = 'ok';
	}else{
		$msg = error(__FILE__);
	}
	echo '{"msg":'.ajaxHTML($msg).'}';
	exit;
}
//---------------------------------------------------------------------------------------------------
$sql = 'FROM '.P.'pages_404 '.$where.' GROUP BY from_domain, from_path, path_404';
//-------------------------------------------------------
$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_('SELECT COUNT(*) '.$sql));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_('SELECT id, from_domain, from_path, path_404, MAX(date_time) AS latest_time, COUNT(DISTINCT ip) AS visitors, COUNT(*) AS visits 
	'.$sql.' ORDER BY visitors DESC, visits DESC LIMIT '.$start.', '.$max)){
	
	// Игнорируемый страницы
	if($q2 = mysql_query("SELECT id FROM ".P."pages_404 WHERE path_ignored = 1")){
		while($r = mysql_fetch_assoc($q2)){
			$path_ignored[ $r['id'] ] = true;
		}
	}
	
	while($r = mysql_fetch_assoc($q)){
		
		if($r['from_domain']){
			$r['from_domain'] = 'http://'. $r['from_domain'];
		}
		
		if(!$r['from_domain'] && (!$r['from_path'] || $r['from_path']=='/')){
			$r['from_path'] = 'адрес набран вручную, с избранного, по закладке или браузер закэшеровал страницу но не файлы';
		}
		
		$str .= '<tr>
			<td><p>'.$r['visitors'].'</p></td>
			<td><p>'.$r['visits'].'</p></td>
			<td><p><input type="text" readonly value="'.htmlspecialchars($r['from_domain'].$r['from_path']).'"></p></td>
			<td><p><input type="text" readonly value="'.htmlspecialchars($r['path_404']).'"></p></td>
			<td><p>'.date("d.m.Y H:i:s", $r['latest_time']).'</p></td>
			<td><p><input type="checkbox" class="ignored" alt="'.$r['id'].'"'.($path_ignored[ $r['id'] ]?' checked':'').'></p></td>
		</tr>';
		$all['a2'] += $r['visits'];
		$all['a3'][$r['from_domain'].$r['from_path']] = true;
		$all['a4'][$r['path_404']] = true;
		$all['a5'][date("d.m.Y", $r['latest_time'])] = true;
	}
	
	// находим кол-во уникальных посетителей
	$spec = mysql_fetch_row(mysql_query('SELECT COUNT(DISTINCT ip) FROM '.P.'pages_404 '.$where));
	$all['a1'] = $spec['0'];
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Переходы на несуществующие страницы сайта c группировкой из разных мест','','<td style="white-space:nowrap"><form action="'.$url.'" method="post">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td align="center">'.$time_select.' <input type="submit" value="Найти"></td>
		</tr>
	</table>
</form></td>').'
'.($str? '<form action="'.$url.'" method="post">
	<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td width="1"><b>Посетителей</b></td>
			<td width="1"><b>Посещений</b></td>
			<td><b>Откуда (с страницы)</b></td>
			<td><b>Куда (на 404 страницу)</b></td>
			<td width="125"><b>Последний раз</b></td>
			<td width="1" title="Игнорировать" style="cursor:pointer"><b>?</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="4" align="center"><p><b>Итог:</b></p></td><td colspan="2" align="center"><p><a href="#" id="delete" style="color:#FF0000">Удалить всё »</a></p></td>
		</tr>
		<tr>
			<td><p>'.$all['a1'].'</p></td>
			<td><p>'.$all['a2'].'</p></td>
			<td><p>'.count($all['a3']).'</p></td>
			<td><p>'.count($all['a4']).'</p></td>
			<td><p>'.count($all['a5']).'</p></td>
			<td>&nbsp;</td>
		</tr>
	</table></form>'.$list.'
<script type="text/javascript">
$(document).ready(function(){
	$(":text").addClass("input_text").click(function(){this.select()}).dblclick(function(){
		window.open( ( (this.value.substr(0, 4)!="http")? "http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'].'" : "") + this.value);
	});
});
</script>
':'<p style="padding:10px">За выбранный период времени данные не найдены.</p>');
?>
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

$(":checkbox").css("vertical-align","middle");

mouseMovementsClass("overflowTable");

$(":text").attr("title","Двойной клик откроет страницу").css("cursor","pointer").each(function(){
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
				$(a).closest("TR").after('<tr id="'+ id +'"><td colspan="6" style="padding:0px;">' + r.tr + '</td></tr>');
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

var allow_ajax_404 = true;

$("#delete").unbind("click").click(function(){
	
	jConfirm("Подтверждаете удаление?", function(r) {
		
		if(r==true){
			
			if( allow_ajax_404 ){
				
				allow_ajax_404 = false;
				
				$.fancybox.showActivity();
				
				$.post(document.location.pathname, {"delete": true}, function(r){
					
					allow_ajax_404 = true;
					
					$.fancybox.hideActivity();
					
					if(r.msg=="ok"){// Данные успешно обновлены
						document.location.href = document.location.href;
					}else{
						jAlert(r.msg);
					}
				},"json");
			}
		}
	});
	return false;
});

var allow_send_data = true;

$(".ignored").click(function(){
	
	var id = $(this).attr("alt");
	
	/* хитрая особенность проверки checked в момент клика заключается в том, что при клике сначала выполняется действие пользователя, 
	а затем срабатывают события, т.е. проверки должны быть обратные */
	if(this.checked == true){
		$(this).attr('checked','true');
		var ignored = 1;
	}else{
		$(this).removeAttr('checked');
		var ignored = 0;
	}
	
	if(id && id != "" && id != " " && id != "0"){
		
		if( allow_send_data ){
			
			allow_send_data = false;
			
			$.fancybox.showActivity();
			
			$.post(document.location.pathname,{"ignored": ignored, "id": id}, function(r){
				
				allow_send_data = true;
				
				$.fancybox.hideActivity();
				
				if(r.msg!="ok"){
					jAlert(r.msg);
				}
			},"json");
		}
	}
});
</script>
</body>
</html>
