<?php
//$GLOBALS['SYSTEM']['module_id'] = '2';
include_once('../../libraries/access.php');
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
echo $_SERVER['SITE_HEADER'].Head('Информация о страницах сайта '.$GLOBALS['SYSTEM']['config']['yapro_http_host']).'
<style type="text/css">
P { padding: 5px 10px; }
.templates {
	padding: 5px;
}
.templates TD {
	padding: 3px;
}
</style>';
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
if($q = mysql_query("SELECT page_id, parent_id FROM ".P."pages")){
	
	$pages = $parent_id__page_id = array();
	$sections = $clauses = 0;
	
	while($r = mysql_fetch_assoc($q)){
		$pages[] = $r['page_id'];
		$parent_id__page_id[ $r['parent_id'] ][ $r['page_id'] ]++;
	}
	foreach($pages as $page_id){
		if(count($parent_id__page_id[ $page_id ])>1){
			$sections++;
		}else{
			$clauses++;
		}
	}
	echo '<p style="padding-top:20px;"><b>разделов:</b> '.$sections.' &nbsp; <b>страниц:</b> '.$clauses.' &nbsp; <b>всего:</b> '.($sections+$clauses).'</p>';
}
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
if($q = mysql_("SELECT name, title FROM ".P."fields WHERE type=9 AND script_path='templates.php' AND sync_pages=0 GROUP BY name")){
	
	$templates = array();
	
	while($r = mysql_fetch_assoc($q)){
		$templates[ $r['name'] ] = $r['title'];
	}
	
	if($templates){
		
		echo '<p style="padding-top:15px;"><b>Поля и используемые в них файлы шаблонов:</b></p>
		<table border="0"><tr><td valign="top">';
		
		foreach($templates as $name => $title){
			
			$e = explode('_',$name);
			
			if($q = mysql_("SELECT COUNT(*) AS c, template, page_id 
			FROM ".P."pages_templates 
			WHERE template_level = ".$e['1']." 
			GROUP BY template ORDER BY c DESC")){
				
				echo '<p>'.$name.' - '.$title.'</p>
				<table border="0" class="templates">
					<tr>
						<td><b>Кол-во страниц</b></td>
						<td><b>Шаблон</b></td>
					</tr>';
				
				while($r = mysql_fetch_row($q)){
					
					echo '<tr>
						<td style="text-align:right;" title="'.$r['2'].'">'.$r['0'].'</td>
						<td>'.($r['1']? $r['1'].(is_file($_SERVER['DOCUMENT_ROOT'].'/templates/'.$r['1'])?'':' <span style="color:#FF0000;">не существует</span>') : 'наследуют у родителей').'</td>
					</tr>';
					
				}
				
				echo '</table>
				</td><td valign="top">';
			}
		}
		
		echo '</td></tr></table>';
	}
}
?>
<script type="text/javascript">
$(document).ready(function(){
	
});
</script>
</body>
</html>
