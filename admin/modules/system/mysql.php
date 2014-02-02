<?php
include_once('../../libraries/access.php');

function SHOW_TABLES_20111029(){
	$result = mysql_query('SHOW TABLES');
	while($i = mysql_fetch_array($result)){
		$result_columns = mysql_query('SHOW COLUMNS FROM `'.$i[0].'`');
		while($j = mysql_fetch_array($result_columns)) {
			$current_base[$i[0]][$j['Field']] = array(
				'field'		=> $j['Field'],
				'type'		=> $j['Type'],
				'null'		=> $j['Null'],
				'key'		=> $j['Key'],
				'default'	=> $j['Default'],
				'extra'		=> $j['Extra']
			);
		}
	}
	if($q = mysql_query('SHOW TABLE STATUS')){
		while($r = mysql_fetch_assoc($q)){
			$current_base[$r['Name']]['LEBNIK_TABLE_Comment'] = $r['Comment'];
		}
	}
	return $current_base;
}

$current_base = SHOW_TABLES_20111029();

$remote_base =  array();
if($_POST['compare']){
	mysql_select_db($_POST['compare']) or die(mysql_error());
	$remote_base = SHOW_TABLES_20111029();
}

$ret = '';
foreach ($current_base as $table=>$data) {
	$ret .= '<p style="padding: 5px; font-size">таблица <b>'.$table.' : </b>'.$data['LEBNIK_TABLE_Comment'].'</p>';
	unset($current_base[$table]['LEBNIK_TABLE_Comment']);
	unset($remote_base[$table]['LEBNIK_TABLE_Comment']);
	$ret .= '<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable" style="margin-bottom:25px">';
	$ret .= $remote_base?'<tr><th colspan="6">база '.htmlspecialchars($dbname).'</td><th colspan="6">база '.htmlspecialchars($_POST['compare']).'</td></tr>':'';
	$ret .= '<tr class="yaproSortTR">
		<td>Field</td><td>Type</td><td>Null</td><td>Key</td><td>Default</td><td>Extra</td>
		'.($remote_base?'<td>Field</td><td>Type</td><td>Null</td><td>Key</td><td>Default</td><td>Extra</td>':'').'
	</tr>';
	foreach ($data as $field=>$fdata) {
		$color = ($current_base[$table][$field] != $remote_base[$table][$field])? 'FFFFCC':'';//CCFFCC
		if(!$remote_base){ $color = 'FFF'; }
		if(isset($current_base[$table][$field]) && isset($remote_base[$table][$field])) {
			$ret .= '<tr style="background-color:#'.$color.'">
						<td>'.$current_base[$table][$field]['field'].'</td><td>'.$current_base[$table][$field]['type'].'</td><td>'.$current_base[$table][$field]['null'].'</td><td>'.$current_base[$table][$field]['key'].'</td><td>'.$current_base[$table][$field]['default'].'</td><td>'.$current_base[$table][$field]['extra'].'</td>
				'.($remote_base?'<td>'.$remote_base[$table][$field]['field'].'</td><td>'.$remote_base[$table][$field]['type'].'</td><td>'.$remote_base[$table][$field]['null'].'</td><td>'.$remote_base[$table][$field]['key'].'</td><td>'.$remote_base[$table][$field]['default'].'</td><td>'.$remote_base[$table][$field]['extra'].'</td>':'').'
			</tr>';
		} elseif (isset($current_base[$table][$field]) && !isset($remote_base[$table][$field])) {
			$color = $remote_base?'CCFFCC':'FFF';
			$ret .= '<tr style="background-color:#'.$color.'">
						<td>'.$current_base[$table][$field]['field'].'</td><td>'.$current_base[$table][$field]['type'].'</td><td>'.$current_base[$table][$field]['null'].'</td><td>'.$current_base[$table][$field]['key'].'</td><td>'.$current_base[$table][$field]['default'].'</td><td>'.$current_base[$table][$field]['extra'].'</td>
						'.($remote_base?'<td>'.$remote_base[$table][$field]['field'].'</td><td>'.$remote_base[$table][$field]['type'].'</td><td>'.$remote_base[$table][$field]['null'].'</td><td>'.$remote_base[$table][$field]['key'].'</td><td>'.$remote_base[$table][$field]['default'].'</td><td>'.$remote_base[$table][$field]['extra'].'</td>':'').'
			</tr>';
		}
		$exist[] = $current_base[$table][$field]['field'];
	}
	
	$color = 'CCFFFF';
	foreach ($remote_base[$table] as $field=>$fdata) {
		if(!in_array($remote_base[$table][$field]['field'], $exist)) {
			$ret .= '<tr style="background-color:#'.$color.'">
						<td>'.$current_base[$table][$field]['field'].'</td><td>'.$current_base[$table][$field]['type'].'</td><td>'.$current_base[$table][$field]['null'].'</td><td>'.$current_base[$table][$field]['key'].'</td><td>'.$current_base[$table][$field]['default'].'</td><td>'.$current_base[$table][$field]['extra'].'</td>
						'.($remote_base?'<td>'.$remote_base[$table][$field]['field'].'</td><td>'.$remote_base[$table][$field]['type'].'</td><td>'.$remote_base[$table][$field]['null'].'</td><td>'.$remote_base[$table][$field]['key'].'</td><td>'.$remote_base[$table][$field]['default'].'</td><td>'.$remote_base[$table][$field]['extra'].'</td>':'').'
			</tr>';
		}
	}
	//echo '<pre>'.print_r(array_diff_key($current_base[$table], $remote_base[$table])).'</pre>';
	$ret .= '</table>';
}


echo $_SERVER['SITE_HEADER'].Head('Структура таблиц базы данных: '.$dbname,'','<td style="white-space:nowrap"><form action="'.$_SERVER['PHP_SELF'].'" method="post"><b>С базой:</b> <input name="compare" value="'.htmlspecialchars($_POST['compare']).'" style="width:155px"> <input type="submit" value="Сравнить"></form></td>').$ret;
?>
</body>
</html>