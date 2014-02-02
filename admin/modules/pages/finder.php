<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю
include_once('../../libraries/fields.php');// настройки полей форм

if(!$GLOBALS['SYSTEM']['pages_communications']['form_id']){ $GLOBALS['SYSTEM']['pages_communications']['form_id'] = 1; }
//---------------------------------------------------------------------------------------------------------------------------------------
$str = '';// создаем строки для заданной формы
$field_title = array();
if($GLOBALS['SYSTEM']['pages_communications']['form_id'] && $GLOBALS['fields']->parent_id__field_id[ $GLOBALS['SYSTEM']['pages_communications']['form_id'] ]){
	
	$page_fields = array();
	if($q=mysql_query("SHOW COLUMNS FROM ".P."pages")){// находим существующие поля $page_table
		while($r=mysql_fetch_assoc($q)){
			$page_fields[ $r['Field'] ]++;
		}
	}
	
	$i = 0;
	$field_arr = array();
	foreach($GLOBALS['fields']->parent_id__field_id[ $GLOBALS['SYSTEM']['pages_communications']['form_id'] ] as $field_id => $true){
		
		$f = $GLOBALS['fields']->field_id__data[ $field_id ];
		
		if(!$f['parent_id'] || !$page_fields[ $f['name'] ]){ continue; }// !$f['sync_pages'] || $f['type']==4 || $f['type']==7 || $f['type']>8
		
		$i++;
		
		$field_title[ $f['name'] ] = $f['title'];
		
		$field_arr[] = '
		<td width="1"><input id="checkbox'.$i.'" name="fields['.$f['name'].']" type="checkbox" '.($_GET['fields'][ $f['name'] ]? 'checked' : '').'></td>
		<td><label for="checkbox'.$i.'">'.$f['title'].' / '.$f['name'].'</label></td>';
		
	}
	
	$field_title['page_id'] = 'Идентификатор страницы:';
	$field_arr[] = '
	<td width="1"><input id="checkbox0" name="fields[page_id]" type="checkbox" '.($_GET['fields']['page_id']? 'checked' : '').'></td>
	<td><label for="checkbox0">Идентификатор страницы / page_id</label></td>';
	
	$field_title['template'] = 'Шаблон страницы:';
	$field_arr[] = '
	<td width="1"><input id="checkbox_template" name="fields[template]" type="checkbox" '.($_GET['fields']['template']? 'checked' : '').'></td>
	<td><label for="checkbox_template">Шаблон страницы</label></td>';
	
	
	$field_title['url'] = 'Урл страницы:';
	$field_arr[] = '
	<td width="1"><input id="checkbox_url" name="fields[url]" type="checkbox" '.($_GET['fields']['url']? 'checked' : '').'></td>
	<td><label for="checkbox_url">Урл страницы</label></td>';
	$i = 0;
	$str = '<tr>';
	foreach($field_arr as $v){
		
		if($i && $i%2==0){
			$str .= '</tr><tr>';
		}
		
		$i++;
		
		$str .= $v;
	}
	
	if($i && $i%2!=0){
		$str .= '<td>&nbsp;</td><td>&nbsp;</td>';
	}
	
	$str .= '</tr>';
}
//---------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?getStart'.($_GET['moduleID']?'&moduleID='.(int)$_GET['moduleID']:'');// общий урл
//---------------------------------------------------------------------------------------------------------------------------------------
$result = '';// результат поиска
$listing = 3;// вид листинга
$max = 25;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
//---------------------------------------------------------------------------------------------------------------------------------------
$search = isset($_GET['search'])? urldecode($_GET['search']) : '';
if($_GET['fields'] && $search){
	
	$where = '';
	
	foreach($_GET['fields'] as $name => $on){
		if($name && $on){
			$url .= '&fields['.$name.']='.$on;
			if($name=='template'){
				if($q = mysql_('SELECT page_id FROM '.P.'pages_templates WHERE template LIKE '._.'%'.$search.'%'._)){
					$in = '';
					while($r = mysql_fetch_assoc($q)){
						$in .= $r['page_id'].',';
					}
					if($in){
						$where .= 'page_id IN('.substr($in,0,-1).') OR ';
					}
				}
			}else if($name=='url'){
				if($q = mysql_('SELECT page_id FROM '.P.'pages_url WHERE url LIKE '._.'%'.$search.'%'._)){
					$in = '';
					while($r = mysql_fetch_assoc($q)){
						$in .= $r['page_id'].',';
					}
					if($in){
						$where .= 'page_id IN('.substr($in,0,-1).') OR ';
					}
				}
			}else{
				$where .= '`'.$name.'`'.(($search=='~0~')? '!='._._ : ' LIKE '._.'%'.$search.'%'._).' OR ';
			}
		}
	}
	
	if($where){
		
		$all = @mysql_fetch_row(mysql_('SELECT COUNT(*) FROM '.P.'pages WHERE '.substr($where,0,-3)));
		$count = $all['0'];// кол-во записей по запросу
		
		if($count && $q = mysql_('SELECT * FROM '.P.'pages WHERE '.substr($where,0,-3).' LIMIT '.($folio*$max).', '.$max)){
			while($r = mysql_fetch_assoc($q)){
				
				$result .= '<tr><td colspan=2><a href="'.$_SERVER['SYSTEM']['URL_PATH'].'modules/pages/right.php?page_id='.$r['page_id'].'" target="_blank">'.$r['name'].'</a> (<a href="http://' .$GLOBALS['SYSTEM']['config']['yapro_http_host'].'/s/p?'.$r['page_id'].'" target="_blank">ссылка</a>)</td></tr>';
				
				$fields = '';
				foreach($r as $k => $v){
					if(!$v || !$_GET['fields'][$k] || !mb_stristr($v, $search) || !$field_title[ $k ]){ continue; }
					
					$fields .= $field_title[ $k ].'('.$k.'), ';
					
					/*$textarea = (strlen($v)>400)? true : false;
					$result .= '<tr'.($textarea?' class=no':'').'><td>'.$k.'</td><td>'.($textarea?'<textarea>':'').htmlspecialchars($v).($textarea?'</textarea>':'').'</td></tr>';*/
				}
				
				$result .= '<tr class="yaproSortTR"><td colspan=2 style="padding-left:25px">доступ: '.$r['access'].($fields?' | искомое найдено в полях: '.substr($fields,0,-2):'').'</td></tr>';
				
			}
			if($result){
				
				$url .= '&search='.urlencode($_GET['search']);
				
				include_once('../../libraries/listing.php');
				
				$result = '<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
					<tr class="yaproSortTR"><td colspan=2><b>Результат поиска: '.$count.'</b></td></tr>
					'.$result.'
					'.($list? '<tr><td colspan=2 style="text-align:center">'.$list.'</td></tr>' : '').'
				</table>';
			}
		}
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Поиск страниц сайта по выбранным полям','','','http://yapro.ru/documents/video/osobennosti-zapoleninie-stranic-poisk-i-arhiv-kopiy.html' ).$result.'
<form action="'.$url.'" method="get" onsubmit="return CheckSubmitForm(this);">
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1" class="cursor" id="switcher"><b>&nbsp;&#8595;</b></td>
		<td colspan="3"><b>Выберите имена полей в которых необходимо выполнить поиск</b></td>
	</tr>
	'.$str.'
	<tr>
		<td colspan="4"><b>Искать строку или часть строки:</b> (введите ~0~ чтобы найти страницы с непустым значением полей)</td>
	</tr>
	<tr class="no">
		<td colspan="4"><textarea name="search" style="width:100%; height:150px">'.htmlspecialchars($search).'</textarea></td>
	</tr>
	<tr class="no">
		<td colspan="4"><input type="submit" value="Найти" title="Ctrl + Enter"></td>
	</tr>
</table>
</form>';
?>
<style type="text/css">
TEXTAREA { width: 100%; height:170px }
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
	$(".overflowTable :checkbox").css("text-align","right").click(function(){
		$("textarea").focus();
	});
	$("#switcher").click(function(){
		$(":checkbox").each(function(){
			this.checked = (this.checked==true)?false:true;
		});
		$("textarea").focus();
	});
	$("[name=search]").keydown(function(event){
	    if (event.which == 13 && event.ctrlKey) {
	       $('FORM:last').submit();
	    };
	});
});
// проверка заполнения полей
function CheckSubmitForm(form){
	if(form.search.value == ""){// проверка на ввод
		jAlert("Вы не указали искомое", function(){
			form.search.focus();
		});
		return false;
	}
	$.fancybox.showActivity();
}
$("#Listing A").click(function(){ $.fancybox.showActivity(); });
</script>
</body>
</html>
