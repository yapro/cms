<?php
if(!$_SERVER['SYSTEM']['fields_templates']){
	$_SERVER['SYSTEM']['fields_templates'] = filesOptions($_SERVER['DOCUMENT_ROOT'].'/templates');
}

$v_template = array();
$e_templates = explode('_', $f['name']);

if($e_templates['0']==='template' && isset($e_templates['1']) && is_numeric($e_templates['1']) && $data['page_id']){
	
	$v_template = @mysql_fetch_row(mysql_('SELECT template FROM '.P.'pages_templates 
	WHERE page_id='.$data['page_id'].' AND template_level='.$e_templates['1']));
	
}

$document = '<select name="'.$f['name'].'">
			<option value="">- Наследовать у родителя -</option>
			<optgroup label="&nbsp;Имеющиеся шаблоны:">'.str_replace('="'.$v_template['0'].'"', '="'.$v_template['0'].'" selected class="Selected"', $_SERVER['SYSTEM']['fields_templates']).'</optgroup>
		</select>';
?>
