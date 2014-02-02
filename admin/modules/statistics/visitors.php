<?php
include_once('../../libraries/access.php');
include_once('../../libraries/times.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/outer/amcharts.com/functions.php');
//---------------------------------------------------------------------------------------------------
if($_POST['search']){
	if($_POST['action_name']=='path404'){
		
		$_GET['path404_id'] = path_id($_POST['search']);
		if(!$_GET['path404_id']){ $_GET['path404_id'] = -1; }
		
	}else if($_POST['action_name']=='path'){
		
		$_GET['to_path_id'] = path_id($_POST['search']);
		if(!$_GET['to_path_id']){ $_GET['to_path_id'] = -1; }
		
	}else if($_POST['action_name']=='keyword'){
		
		$_GET['keyword_id'] = keyword_id($_POST['search']);
		if(!$_GET['keyword_id']){ $_GET['keyword_id'] = -1; }
		
	}else if($_POST['action_name']=='refferer'){
		
		$_GET['refferer_id'] = domain_id($_POST['search']);
		if(!$_GET['refferer_id']){ $_GET['refferer_id'] = -1; }
		
	}else if($_POST['action_name']=='affiliate'){
		
		$_GET['affiliate_id'] = domain_id($_POST['search']);
		if(!$_GET['affiliate_id']){ $_GET['affiliate_id'] = -1; }
		
	}
}

//---------------------------------------------------------------------------------------------------
function statistics_visitor_in($where='', $to_domain_id=0){
	
	if(!$where){ return ; }
	
	global $domains, $min, $max;
	
	$in = '';
	if($q = mysql_query("SELECT s.visitor_id FROM ".P."statistics AS s WHERE 
	s.to_domain_id='".($to_domain_id? $to_domain_id : $domains['domain_id'])."' AND ".$where." AND s.date_time BETWEEN '".$min."' AND '".$max."' 
	GROUP BY s.visitor_id")){
		while($r = mysql_fetch_assoc($q)){
			
			$in .= $r['visitor_id'].',';
			
		}
	}
	return " AND s.visitor_id IN (".substr($in, 0, -1).")";
}
//---------------------------------------------------------------------------------------------------
if(is_numeric($_GET['path404_id'])){// неправильное обращение
	
	$action_name = 'path404';
	$_POST['search'] = $_POST['search']? $_POST['search']: id_path($_GET['path404_id']);
	$where = "s.config_id IN(12,13,14,15,16)";
	$where .= statistics_visitor_in("s.config_id = '13' AND s.to_path_id = ".$_GET['path404_id']);
	
}else if(is_numeric($_GET['to_path_id'])){// посещение страницы / ушел с страницы / закачка файла / (все кроме индексирования)
	
	$action_name = 'path';
	$_POST['search'] = $_POST['search']? $_POST['search'] : id_path($_GET['to_path_id']);
	
	$where = "s.config_id IN(12,13,14,15,16)";
	$where .= statistics_visitor_in($where." AND s.to_path_id = ".$_GET['to_path_id']);
	
}else if(is_numeric($_GET['keyword_id'])){// с поисковой системы по ключевой фразе
	
	$action_name = 'keyword';
	$_POST['search'] = $_POST['search']? $_POST['search']: id_keyword($_GET['keyword_id']);
	$where = "s.config_id IN(12,13,14,15,16)";
	$where .= statistics_visitor_in("s.config_id = '12' AND s.from_search_system != '0' AND s.keyword_id = '".$_GET['keyword_id']."'");
	
}else if(is_numeric($_GET['refferer_id'])){// пришел с сайта
	
	$action_name = 'refferer';
	$_POST['search'] = $_POST['search']? $_POST['search']: id_domain($_GET['refferer_id']);
	$where = "s.config_id IN(12,13,14,15,16)";
	$where .= statistics_visitor_in("s.config_id IN (12,13) AND s.from_domain_id = '".$_GET['refferer_id']."'");
	
}else if(is_numeric($_GET['affiliate_id'])){// ушел на сайт
	
	$action_name = 'affiliate';
	$_POST['search'] = $_POST['search']? $_POST['search']: id_domain($_GET['affiliate_id']);
	$where = "s.config_id IN(12,13,14,15,16)";
	$where .= statistics_visitor_in("s.config_id = '15' AND s.from_domain_id = '".$domains['domain_id']."'", $_GET['affiliate_id']);
	
}else{// посещение страницы
	
	$where =  "config_id = '12'";
	
}
//---------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$url .= $date = '&d='.$d.'&m='.$m.'&y='.$y.'&d2='.$d2.'&m2='.$m2.'&y2='.$y2;
//---------------------------------------------------------------------------------------------------
$sql = "FROM ".P."statistics AS s
LEFT JOIN ".P."statistics_visitors AS v ON v.visitor_id = s.visitor_id
WHERE (s.from_domain_id='".$domains['domain_id']."' OR s.to_domain_id='".$domains['domain_id']."') AND 
".$where." AND s.date_time BETWEEN '".$min."' AND '".$max."' GROUP BY s.visitor_id";
//-------------------------------------------------------
$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$count = @mysql_num_rows(mysql_query("SELECT COUNT(*) ".$sql));// кол-во записей по запросу
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
$str = '';
if($q = mysql_query("SELECT s.visitor_id, v.ip, s.to_path_id, COUNT(*) AS actions, COUNT(DISTINCT s.to_path_id) AS paths, 
MIN(s.date_time) AS start, MAX(s.date_time) AS end ".$sql." ORDER BY start LIMIT ".$start.', '.$max)){
	while($r = mysql_fetch_assoc($q)){
		
		$ip = long2ip($r['ip']);
		
		$str .= '<tr>
			<td><p>'.$r['visitor_id'].'</p></td>
			<td><p><a href="https://www.nic.ru/whois/?query='.$ip.'" target="_blank">'.(($ip==$_SERVER['REMOTE_ADDR'])?'<span style="color: #FF0000" title="Совпадает с Вашим IP-адресом">'.$ip.'<span>':$ip).'</a></p></td>
			<td><p>'.$r['actions'].'</p></td>
			<td><p>'.$r['paths'].'</p></td>
			<td><p>'.date ("d.m.Y H:i:s", $r['start']).'</p></td>
			<td><p>'.date ("d.m.Y H:i:s", $r['end']).'</p></td>
			<td><p><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'visitor.php?visitor_id='.$r['visitor_id'].$date.'" target="_blank">Подробнее»</a></p></td>
		</tr>';
		
		$all['visitors']++;
		$all['ip'][ $ip ]++;
		$all['actions'] += $r['actions'];
		$all['paths'] += $r['paths'];
	}
}
//---------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Посетители по действию').'
<form action="'.$url.'" method="post" onsubmit="return checkForm()">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="HeadPluse">
		<tr class="yaproSortTR">
			<td width="1" style="white-space: nowrap;">&nbsp;'.$time_select.'</td>
			<td width="1">&nbsp;&nbsp;&nbsp;<b>Посетителей:</b></td>
			<td width="1">
				<select size="1" name="action_name">'.str_replace('="'.$action_name.'"', '="'.$action_name.'" selected', '
					<option value="path">посетивших URL</option>
					<option value="path404">посетивших 404 URL</option>
					<option value="keyword">зашедших по фразе</option>
					<option value="refferer">пришедших с сайта</option>
					<option value="affiliate">ушедших на сайт</option>
				').'</select>
			</td>
			<td>
				<input type="text" value="'.htmlspecialchars($_POST['search']).'" name="search" class="input_text" style="width: 100%; _margin-bottom: 3px;">
			</td>
			<td width="1"><input type="submit" value="Найти"></td>
		</tr>
	</table>
</form>'.($str? '
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr>
			<td width="90"><b>Посетитель</b></td>
			<td><b>IP-адрес</b></td>
			<td width="75"><b>Действий</b></td>
			<td width="100"><b>Уникальных URL</b></td>
			<td width="125"><b>Время входа</b></td>
			<td width="125"><b>Время выхода</b></td>
			<td width="90"><b>Действия</b></td>
		</tr>
		'.$str.'
		<tr class="yaproSortTR">
			<td colspan="7" align="center"><p><b>Суммы:</b></p></td>
		</tr>
		<tr>
			<td><p>'.$all['visitors'].'</p></td>
			<td><p>'.count($all['ip']).' уникальных</p></td>
			<td><p>'.$all['actions'].'</p></td>
			<td><p>'.$all['paths'].'</p></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
</table>'.$list : '<p style="padding:10px">Для выбранного сайта по заданным условиям данные не найдены.</p>');
?>
<style type="text/css">
.HeadPluse TD { padding: 3px; }

#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">

$("[name=search]").dblclick(function(){this.select()});

mouseMovementsClass("overflowTable");

$(".overflowTable tr").each(function(){
	var a = $("A:first", this);
	$(a).attr("title", "Узнать информацию о IP-адресе");
});

function checkForm(){
	
	var s = $("[name=action_name]");// находим наш элемент Select
	var error = "";
	
	if($(s).val() == "path404" && $("[name=search]").val() == ""){
		error = "Введите неправильный URL";
	}
	
	if($(s).val() == "keyword" && $("[name=search]").val() == ""){
		error = "Введите поисковую фразу";
	}
	
	if($(s).val() == "refferer" && $("[name=search]").val() == ""){
		error = "Введите доменное имя сайта рефферера";
	}
	
	if($(s).val() == "affiliate" && $("[name=search]").val() == ""){
		error = "Введите доменное имя сайта афилата";
	}
	
	if(error != ""){
		jAlert(error, function(){
			$("[name=search]").focus();
		});
		return false;
	}
}
</script>
</body>
</html>
