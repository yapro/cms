<?php
// Класс для работы с данными полей в Системе Администрирования
class fields {
	function fields(){// эти данные нужны всегда и везьде
		
		$this->field_id__data = $this->parent_id__field_id = array();
		
		if($q = mysql_query("SELECT * FROM ".P."fields ORDER BY position")){
			while($r = mysql_fetch_assoc($q)){
				$this->field_id__data[ $r['field_id'] ] = $r;
				$this->parent_id__field_id[ $r['parent_id'] ][ $r['field_id'] ]++;
			}
		}
	}
	function kids($field_id=0){// нахожу field_id дочерних поле определенного родителя
		
		$in = '';
		
		if($field_id && $this->parent_id__field_id[ $field_id ]){
			
			foreach($this->parent_id__field_id[ $field_id ] as $k=>$true){
				
				$in .= $k.',';
				
				$in .= $this-> kids($k);
			}
		}
		return $in;
	}
	function kids_sync_name($field_id=0, $sync_name=''){// нахожу имена дочерних поле определенного родителя для последующего удаления
		
		$in = '';
		
		if($field_id && $sync_name && $this->parent_id__field_id[ $field_id ]){
			
			foreach($this->parent_id__field_id[ $field_id ] as $k=>$true){
				
				$in .= ($this->field_id__data[ $k ][ $sync_name ] && $this->page_fields[ $sync_name ])? " DROP `".$this->field_id__data[ $k ]['name']."`," : '';
				
				$in .= $this-> kids_sync_name($k, $sync_name);
			}
		}
		return $in;
	}
	function parents_options($parent_id=0, $i=0){// строю option для возможного выбора в качестве родительского элемента
		
		$in = '';
		
		if($this->parent_id__field_id[ $parent_id ] && (!$_GET['field_id'] || $_GET['field_id'] != $parent_id) ){
			
			if($i){ for($n=0; $n<($i*2); $n++){ $nbsp .= '&nbsp;&nbsp;&nbsp;'; } }
			
			foreach($this->parent_id__field_id[ $parent_id ] as $k=>$true){
				
				$r = $this->field_id__data[ $k ];
				
				if($k != $_GET['field_id'] && $r['title'] && ($r['type']==0 || $r['type']==3 || $r['type']==4 || $r['type']==6) ){
					
					$in .= '<option value="'.$k.'">'.$nbsp.strip_tags($r['title']).'</option>';
					
				}
				if($this->parent_id__field_id[ $k ] && (!$_GET['field_id'] || $_GET['field_id'] != $k) ){
					$i++;
					$in .= $this-> parents_options($k, $i);
					$i--;
				}
			}
		}
		return $in;
	}
	function javascript_parent_names($parent_id=0){// нахожу имена дочерних полей определенной формы
		
		$in = $kids = '';
		
		if($this->parent_id__field_id[ $parent_id ]){
			
			foreach($this->parent_id__field_id[ $parent_id ] as $k=>$true){
				
				if($this->field_id__data[ $k ]['name']){ $in .= '"'.$this->field_id__data[ $k ]['name'].'":true,'; }
				
				$kids .= $this-> javascript_parent_names($k);
			}
		}
		return 'var parent_'.$parent_id.' = {'.($in? substr($in, 0, -1) : '').'};'."\n".$kids;
	}
	function javascript_parent_id__position_max($parent_id=0){// нахожу максимальные позиции родительских элементов
		
		$in = ''; $max_position = 0;
		
		if($this->parent_id__field_id[ $parent_id ]){
			
			foreach($this->parent_id__field_id[ $parent_id ] as $k=>$true){
				
				$max_position = ($this->field_id__data[ $k ]['position'] > $max_position)? $this->field_id__data[ $k ]['position'] : $max_position;
				
				$in .= $this-> javascript_parent_id__position_max($k);
			}
		}
		return (int)$parent_id.':'.$max_position.','.$in;
	}
	function treeview($parent_id=0){// создаем массив для вывода в левом фрейме
		
		if(!$this->parent_id__field_id[ $parent_id ]){ return false; }
		
		foreach($this->parent_id__field_id[ $parent_id ] as $k=>$true){
			
			$r = $this->field_id__data[ $k ];
			
			$this->treeview .= '<li id="'.$k.'"><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'right.php?field_id='.$k.'" title="'.(int)$r['position'].'"'.($r['access']?'':' style="color:#999999"').'>'.htmlspecialchars($r['title']).'</a>';
			
			if($this->parent_id__field_id[ $k ]){
				$this->treeview .= '<ul>';
				$this-> treeview($k);
				$this->treeview .= '</ul>';
			}
			$this->treeview .= '</li>';
		}
	}
	function tr($form_id=0, $data=array()){// создает строки для заданной формы
		
		if(!$form_id || !$this->parent_id__field_id[ $form_id ]){ return false; }
		
		$trs = '';
		
		foreach($this->parent_id__field_id[ $form_id ] as $field_id => $true){
			
			$f = $this->field_id__data[ $field_id ];
			
			if(!$f['access']){ continue; }// если не показываем поле
			
			$data[ $f['name'] ] = trim($data[ $f['name'] ]);// определяем данные поля
			
			if($f['type']=='1'){// однострочное текстовое (INPUT)
				
				if($f['checking']=='3'){// проверка: дата времени
					$time_class = ' class="datepickerTimeField"';
					$data[ $f['name'] ] = date('d.m.Y H:i:s', $data[ $f['name'] ]? $data[ $f['name'] ] : time());
				}else{
					$time_class = '';
				}
				
				$trs .= '<tr>
					<td><p><b>'.$f['title'].'</b></p></td>
					<td><input type="text" name="'.$f['name'].'" value="'.htmlspecialchars($data[ $f['name'] ]).'"'.$time_class.''.($f['height']?'style="height:'.$f['height'].'px"':'').'></td>
				</tr>';
				
			}else if($f['type']=='2'){// многострочное текстовое (TEXTAREA)
				
				$trs .= '<tr'.$tr.'>
					<td class="NoGreyB" colspan="2"><p><b>'.$f['title'].'</b></p></td>
				</tr><tr class="no">
					<td class="NoGreyB" colspan="2" style="padding: 5px 5px 5px 5px;"><textarea name="'.$f['name'].'"'.($f['wysiwyg']?' class="wysiwyg"':'').' style="height:'.$f['height'].'px">'.htmlspecialchars($data[ $f['name'] ]).'</textarea></td>
				</tr>';
				
			}else if($f['type']=='3' || $f['type']=='4'){// выбор одного из множества (SELECT) || выбор множества из множества (SELECT)
				
				if($this->parent_id__field_id[ $field_id ]){// если имеются OPTION-ы
					
					$options = '';
					
					$d = array_flip($data[ $f['name'] ]);// меняем местами ключи и значения массива данных
					
					foreach($this->parent_id__field_id[ $field_id ] as $id => $true){
						
						$r = $this->field_id__data[ $id ];
						
						if(!$r['access']){ continue; }// если не показываем поле
						
						$selected = isset($d[ $r['name'] ])? ' selected' : '';
						
						$options .= '<option value="'.$r['name'].'"'.$selected.'>'.$r['title'].'</option>';
						
					}
					
					if($f['type']=='4'){ $f['name'] = $f['name'].'[] multiple'; }// выбор множества из множества (SELECT)
					
					$str = '<select name='.$f['name'].''.($f['height']?' style="height:'.$f['height'].'px"':'').'>'.$options.'</select>';
					
				}else{
					$str = '<p>Возможные варианты выбора не заданы</p>';
				}
				
				$trs .= '<tr>
					<td><p><b>'.$f['title'].'</b></p></td>
					<td>'.$str.'</td>
				</tr>';
				
			}else if($f['type']=='5'){// подтверждение (CHECKBOX)
				
				$trs .= '<tr'.$tr.'>
					<td><p><b>'.$f['title'].'</b></p></td>
					<td><input name="'.$f['name'].'" type="checkbox" '.(($data[ $f['default'] ] || $data[ $f['name'] ])?'checked':'').'></td>
				</tr>';
				
			}else if($f['type']=='6'){// выбор (RADIO)
				
				if($this->parent_id__field_id[ $field_id ]){// если имеются разные варианты
					
					$inputs = '';
					
					$d = array_flip($data[ $f['name'] ]);// меняем местами ключи и значения массива данных
					
					foreach($this->parent_id__field_id[ $field_id ] as $id => $true){
						
						$r = $this->field_id__data[ $id ];
						
						if(!$r['access']){ continue; }// если не показываем поле
						
						$checked = ($data[ $r['name'] ]==$r['name'])? ' checked' : '';
						
						$inputs .= '<label><input name="'.$f['name'].'['.$r['name'].']" type="radio"'.$checked.'> - '.$r['title'].'</label><br>';
						
					}
					
				}else{
					$inputs = '<input type="radio" name="'.$f['name'].'"'.($f['value']? ' value="'.$f['value'].'"':'').(($data[ $f['default'] ] || $data[ $f['name'] ]=='on')?' checked':'').'>';
				}
				
				$trs .= '<tr>
					<td><p><b>'.$f['title'].'</b></p></td>
					<td>'.$inputs.'</td>
				</tr>';
				
			}else if($f['type']=='8'){// загрузка файла заданного типа
				
				$trs .= '<tr>
					<td><p><b>'.$f['title'].'</b></p></td>
					<td class="UploadFile" alt="'.$f['name'].'~~~'.$f['file_types'].'~~~'.$data[ $f['name'] ].'"></td>
				</tr>';
				
			}else if($f['type']=='9' && $f['script_path']){// создавать скриптом
				
				$document = '';
				
				@include($_SERVER['SYSTEM']['PATH'].'modules/fields/include/'.$f['script_path']);
				
				if(strtolower_(substr($document, 0, 3))=='<tr'){
					
					$trs .= $document;
					
				}else{
					
					$trs .= '<tr>
						<td><p><b>'.$f['title'].'</b></p></td>
						<td>'.$document.'</td>
					</tr>';
					
				}
				
			}else if($f['type']=='10'){// информационная строка в центре
				
				$trs .= '<tr><td colspan="2" align="center"><p><b>'.$f['title'].'</b></p></td></tr>';
				
			}else if($f['type']=='11'){// активные кнопки
				
				$trs .= '<tr class="no"><td align="center" colspan="2"><input class="submit" type="submit" value="'.($data['page_id']?'Сохранить">&nbsp;&nbsp;<a href="'.$data['system_url'].'&delete_page_id='.$data['page_id'].'" onclick="return checkDel(this);" style="text-decoration: none; color: #FF0000;">Удалить</a>&nbsp;&nbsp;<a target="_blank" title="Предварительный осмотр" style="text-decoration: none;" href="'.$GLOBALS['pages']-> url($data['page_id']).'">Посмотреть</a>&nbsp;<input onclick="copyMe(this);" class="submit" type="button" value="Копировать' : 'Создать').'"></td></tr>';
				
			}else{// type==0 - форма || type==7 - вариант выбора (OPTION или RADIO)
				
				$trs .= '';
				
			}
		}
		return $trs;
	}
	function javascript_names_must($parent_id=0){// создаем JavaScript-строку объекта для проверки данных на заполнение
		
		$in = '';
		
		if($this->parent_id__field_id[ $parent_id ]){
			
			foreach($this->parent_id__field_id[ $parent_id ] as $k=>$true){
				
				if($this->field_id__data[ $k ]['name'] && $this->field_id__data[ $k ]['must']){
					
					$in .= '"'.$this->field_id__data[ $k ]['name'].'":true,';
					
				}
			}
		}
		return '{'.substr($in, 0, -1).'};';
	}
	function javascript_names_types($parent_id=0){// создаем JavaScript-строку объекта для проверки данных на валидность
		
		$in = '';
		
		if($this->parent_id__field_id[ $parent_id ]){
			
			foreach($this->parent_id__field_id[ $parent_id ] as $k=>$true){
				
				if($this->field_id__data[ $k ]['name'] && $this->field_id__data[ $k ]['checking']){
					
					$in .= '"'.$this->field_id__data[ $k ]['name'].'":'.$this->field_id__data[ $k ]['checking'].',';
					
				}
			}
		}
		return '{'.substr($in, 0, -1).'};';
	}
	function forms_options(){// создаем элементы option
		
		$this->field_id__data[ $r['field_id'] ] = $r;
				$this->parent_id__field_id[ $r['parent_id'] ][ $r['field_id'] ]++;
		
		
		if(!$this->parent_id__field_id['0']){ return false; }
		
		$options = '';
		
		foreach($this->parent_id__field_id['0'] as $field_id=>$true){
			if($field_id!=$_GET['field_id']){ $options .= '<option value="'.$field_id.'"> '.strip_tags($this->field_id__data[ $field_id ]['title']).'</option>'; }
		}
		
		return $options;
	}
	
	
	
	
	
	
	
	
	
	
	/*
	function javascript_kids_form_names($field_id=0){// нахожу имена дочерних полей определенной формы
		
		$in = '';
		
		if($field_id && $this->parent_id__field_id[ $field_id ]){
			
			foreach($this->parent_id__field_id[ $field_id ] as $k=>$true){
				
				if($this->field_id__data[ $k ]['name']){ $in .= '"'.$this->field_id__data[ $k ]['name'].'":true,'; }
				
				$in .= $this-> javascript_kids_form_names($k);
			}
		}
		return $in;
	}
	function forms_options(){// создаем элементы option
		
		if(!$this->forms){ return false; }
		
		foreach($this->forms as $k=>$data){
			if($k!=$_GET['field_id']){ $forms_options .= '<option value="'.$k.'"> '.strip_tags($data['title']).'</option>'; }
		}
		
		return $forms_options;
	}*/
	function js_form_check_must($parent_id){// создаем JavaScript-строку для массива проверки обязательного заполнения данных
		
		if(!$parent_id || !$this->form_id__field_id[$parent_id]){ return false; }
		
		foreach($this->form_id__field_id[$parent_id] as $field_id=>$true){
			if($field_id && $this->field_id__data[ $field_id ]['must'] && $this->field_id__data[ $field_id ]['name']){
				$str .= '"'.$this->field_id__data[ $field_id ]['name'].'",';
			}
		}
		
		return '['.mb_substr($str,0,-1).']';
	}
	function js_form_phone_names($parent_id){// создаем JavaScript-строку для массива проверки данных телефонных полей на валидность
		
		if(!$parent_id || !$this->form_id__field_id[$parent_id]){ return false; }
		
		foreach($this->form_id__field_id[$parent_id] as $field_id=>$true){
			if($field_id && $this->field_id__data[ $field_id ]['check']=='4' && $this->field_id__data[ $field_id ]['name']){
				$str .= '"'.$this->field_id__data[ $field_id ]['name'].'",';
			}
		}
		
		return '['.mb_substr($str,0,-1).']';
	}
	function form_name_multi_variant($form_id, $name){// создаем массив 
		
		if(!$form_id || !$this->form_id__field_id[$form_id] || !$name){ return false; }
		
		if($this->multi_variant[ $form_id ][ $name ]){
			return $this->multi_variant[ $form_id ][ $name ];
		}else{
			$this->multi_variant[ $form_id ][ $name ] = array();
		}
		
		foreach($this->form_id__field_id[$form_id] as $field_id=>$true){
			
			$multi_variant = null;// удаляем массив на случай повторной его проверки
			
			$field = $this->field_id__data[ $field_id ];
			
			if($field['name']==$name && $field['multi_variant']){
				
				eval($field['multi_variant']);// десериализация данных
				
				if($multi_variant){
					ksort($multi_variant);
					$this->multi_variant[ $form_id ][ $name ] = $multi_variant;
					return $multi_variant;
				}
			}
		}
		return false;
	}
	function data_ok($data='', $check=0){// проверка введенных данных
		
		if($data && $check){
			
			$check_type['1'] = '/^[a-zа-я]+$/iu';// слово
			
			$check_type['2'] = '/^[0-9]+$/i';// число
			
			$check_type['3'] = '/^[0-9\.\:\s]+$/i';// дата времени
			
			$check_type['4'] = '/^[0-9\-\(\)\s\+]+$/i';// номер телефона
			
			$check_type['5'] = '/^([\w\.\-]+)@([a-z0-9\-\.]+)\.([a-z]+)$/sUiu';// электронная почта
			
			$check_type['6'] = '/^[^0-9]+$/i';// отсутствие цифр
			
			$check_type['7'] = '/^[^a-zа-я]+$/iu';// отсутствие букв
			
			$check_type['8'] = '/[a-zа-я]+/iu';// хотя бы одна буква
			
			$check_type['9'] = '/[0-9]+/i';// хотя бы одна цифра
			
			$check_type['10'] = '/\s+/';// пробел - условие true: присутсвие хотябы 1 пробела, включая space, tab, form feed, line feed. Эквивалентно [ \f\n\r\t\v].
			
			preg_match_all($check_type[ $check ], $data, $a);
			
			if($a['0']['0']){ return true; }// данные в порядке!
		}else if(!$check){
			return true;// данные в порядке!
		}else{
			return false;// данные НЕ в порядке!
		}
	}
	function error($show_errors=false, $field=array(), $data=array()){// проверка правильности ввода данных
		
		if($show_errors && $field && $data){
			if($field['check'] && !$this-> data_ok($data[ $field['name'] ], $field['check']) ){
				$this->error = true;// фиксируем факт того, что произошла ошибка ввода данных
				return '<br><span style="color: #FF0000; font-weight: normal">данные заполнены неправильно, исправьте</span>';
			}
			if($field['must'] && empty($data[ $field['name'] ]) ){
				$this->error = true;// фиксируем факт того, что произошла ошибка ввода данных
				return '<br><span style="color: #FF0000; font-weight: normal">пожалуйста, заполните данное поле</span>';
			}
		}
	}
	function answer($form_id, $data=array()){// выводим строки
		
		$multi_variant = null;// удаляем массив на случай повторной его проверки
		
		if(!$this->form_id__field_id[ $form_id ]){ return false; }
		
		$td1 = ' style="border-right: 1px dotted #CCCCCC; width: 40%; text-align: right; padding: 5px;"';
		$td2 = ' style="padding: 5px; width: 60%;"';
		
		foreach($this->parent_id__field_id__position[ $form_id ] as $field_id=>$position){
			
			$field = $this->field_id__data[ $field_id ];
			
			$answer = '';
			
			$dont_answer = false;
			
			if($field['type']=='1'){// однострочное текстовое (INPUT)
				
				if($field['check']=='3'){// проверка: дата времени
					$data[ $field['name'] ] = date('d.m.Y H:i:s', $data[ $field['name'] ]? $data[ $field['name'] ] : time());
				}
				$answer = htmlspecialchars($data[ $field['name'] ]);
				
			}else if($field['type']=='2'){// многострочное текстовое (TEXTAREA)
				
				$answer = htmlspecialchars($data[ $field['name'] ]);
				
			}else if($field['type']=='3'){// выбор одного из множества (SELECT)
				
				eval($field['multi_variant']);// десериализация данных
				
				if($multi_variant){
					
					ksort ($multi_variant);
					
					foreach($multi_variant as $k=>$v){
						if($data[ $field['name'] ]==$k){ $answer = htmlspecialchars($v); break; }
					}
				}
				
			}else if($field['type']=='4'){// выбор множества из множества (SELECT)
				
				eval($field['multi_variant']);// десериализация данных
				
				if($multi_variant){
					
					if($data[ $field['name'] ]){
						eval('$d = '.$data[ $field['name'] ]);// десериализация данных
						$d = array_flip ($d);// меняем местами ключи и значения массива
					}else{
						$d = array();
					}
					ksort ($multi_variant);
					
					$selected = $options = '';
					
					foreach($multi_variant as $k=>$v){
						if( isset($d[$k]) ){ $answer .= htmlspecialchars($v).'<br>'; }
					}
					
				}
				
			}else if($field['type']=='5'){// подтверждение (CHECKBOX) или выбор (RADIO)
				
				eval($field['multi_variant']);// десериализация данных
				//-$inputs = '';
				
				if($multi_variant){// выбор (RADIO)
					
					foreach($multi_variant as $k=>$v){
						if($data[ $field['name'] ]==$k){ $answer = htmlspecialchars($v); break; }
					}
				}else{// подтверждение (CHECKBOX)
					$answer = ($data[ $field['default'] ] || $data[ $field['name'] ]=='on')? 'да' : 'нет';
				}
				
			}else if($field['type']=='6'){// файл заданного типа
				
			}else if($field['type']=='7'){// текстовое сопровождение в центре
				
				$answer = '<tr><td colspan="2" align="center"><p><b>'.$field['title'].'</b></p></td></tr>';
				
			}else if($field['type']=='8'){// активные кнопки
				
				$answer = '<tr><td align="center" colspan="2"><input type="submit" value="Отправить"></td></tr>';
				$dont_answer = true;
				
			}else if($field['type']=='9' && $field['script']){// создавать и обрабатывать скриптом
				
				include($_SERVER['DOCUMENT_ROOT'].'/inner/Libraries/fields/'.$field['script']);
				
				$answer = $document;
				
			}else{// type==0 - форма
				
			}
			if(!$dont_answer){
				
				if(strtolower_(mb_substr($answer, 0, 3))=='<tr'){// если ответ в виде строки
					
					$tr .= $answer;
					
				}else{
					
					$answer_i++;
					
					$bgcolor = ' bgcolor="#'.( !($answer_i%3)? 'F6F6F6' : (!($answer_i%2)? 'FFFFF4' : 'FFFFFF' ) ).'"';
					
					$td1 .= $bgcolor;
					$td2 .= $bgcolor;
					
					$tr .= '<tr>
					<td'.$td1.'>'.($this->field_id__data[ $field_id ]['must']?'<font color="red">*</font> ':'').$field['title'].'</td>
					<td'.$td2.'>'.$answer.'</td>
					</tr>';
					
				}
			}
		}
		$page_url = 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		return '<table border="0" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC" width="100%">
			<tr>
				<td'.$td1.' bgcolor="#F6F6F6"><b>Вопросы:</b></td>
				<td'.$td2.' bgcolor="#F6F6F6"><b>Ответы:</b></td>
			</tr>
			'.$tr.'
		</table>
		<br><p>Анкета размещена на странице: <a href="'.$page_url.'" target="_blank">'.$page_url.'</a></p>';
	}
	function update($data){// обязательно объявленные переменные data['field_id']
		if($data['field_id']){
			$this->delete($data['field_id']);// обязательные: field_id
			$new_id = $this->insert(&$data);// обязательные: data
			return $new_id;
		}
	}
	function insert($data){// обязательно объявленные переменные data
		
		if(!$data){ echo error('select: не указан data (данные для инсерта)'); return false; }
		
		$field_id = $data['field_id']? $data['field_id'] : ($this->max_field_id + 1);
		
		$block['field_id'] = $block['name_old'] = $block['field_sql'] = true;
		/*
		if($block['type']){
			$data['name'] = trim(translite(strtolower_(str_replace(' ','_',str_replace('•','',str_replace('.','',
							trim($data['name']?$data['name']:$data['title'])))))));
		}
		*/
		foreach($data as $name=>$value){
			
			if($name && $value && !$block[$name]){
				
				if(is_array($value)){
					$value = $GLOBALS["serializer"]->serialize_($value, $name);
				}
				if($value){
					;
				}
			}
		}
		$GLOBALS['system_cache']->update('/system/fields_updates.php');
		$this-> fields();// если добавляется, то нужно пересобрать массив с данными для его использования дальше
		return $field_id;
	}
	function delete($field_id){//обязательные: fields_id | возможные: sql
		if($field_id && is_numeric($field_id)){
			mysql_query("DELETE FROM ".P."fields WHERE field_id='".$field_id."'");
			$check_delete = mysql_affected_rows();
			if($check_delete){
				$GLOBALS['system_cache']->update('/system/fields_updates.php');
				$this-> fields();// если удаляется, то нужно пересобрать массив с данными для его использования дальше
				return true;
			}
		}
	}
}
$GLOBALS['fields'] = new fields;

if($_GET['ajax_iframe'] && !$_POST){ exit; }// запрос пустого ифрейма

// загружаю файлы и добавляю POST данные имен файлов
if(function_exists('access') && access('w')){ filesPost($fields->files); }else{ if($_POST['ajax_file']){ echo 'Недостаточно прав доступа!'; exit; } }
?>
