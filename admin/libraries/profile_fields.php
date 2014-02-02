<?php
// Класс для работы с данными полей профиля пользователя в Системе Администрирования
class profile_fields {
	function profile_fields(){// эти данные нужны всегда и везьде
		
		$this->field_id__data = array();
		
		if(F && $q = mysql_query("SELECT f.field_id, f.field_name, f.field_order, l.lang_name, l.lang_explain 
			FROM ".F."profile_fields AS f 
			LEFT JOIN ".F."profile_lang AS l ON l.field_id = f.field_id AND l.lang_id = 1 
			ORDER BY f.field_order")){
			while($r = mysql_fetch_assoc($q)){
				$this->field_id__data[ $r['field_id'] ] = $r;
			}
		}
	}
	function fields_names(){// нахожу имена дочерних полей определенной формы
		
		$in = '';
		
		if($this->field_id__data){
			
			foreach($this->field_id__data as $r){
				
				if($r['field_name']){ $in .= ajaxHTML($r['field_name']).':true,'; }
				
			}
		}
		return 'var fields_names = {'.($in? substr($in, 0, -1) : '').'};';
	}
	function field_id__data($field_id=0){// нахожу данные выбранного поля
		
		if($field_id && is_numeric($field_id) && F){
			
			$r = @mysql_fetch_assoc(mysql_query("SELECT f.*, l.lang_name, l.lang_explain  
			FROM ".F."profile_fields AS f 
			LEFT JOIN ".F."profile_lang AS l ON l.field_id = f.field_id AND l.lang_id = 1
			WHERE f.field_id=".$field_id));
			
			if($r && $q = mysql_query("SELECT option_id, lang_value FROM ".F."profile_fields_lang 
			WHERE field_id=".$field_id." GROUP BY lang_value ORDER BY option_id")){
				while($a = mysql_fetch_assoc($q)){
					
					$r['lang_options'] .= $a['lang_value']."\n";
					
					//$r['lang_options'][ $a['option_id'] ] = $a['lang_value'];
					/*
					if($r['field_type']=='4'){// radio || checkbox
						
					}else{// select
						
					}
					*/
				}
			}
			return $r;
		}
	}
	function field_order_max(){// нахожу максимальное значение позиции среди полей
		
		$r = @mysql_fetch_row(mysql_query("SELECT MAX(field_order) FROM ".F."profile_fields"));
		
		return $r['0'];
	}
}
$GLOBALS['profile_fields'] = new profile_fields;
?>
