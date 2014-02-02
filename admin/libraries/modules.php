<?php
class modules {
	function modules(){// находим данные по всем модулям
		if($q = mysql_query("SELECT * FROM ".P."modules ORDER BY position")){
			while($r = mysql_fetch_assoc($q)){
				$this->parent_id__module_id[$r['parent_id']][$r['module_id']] = 1;
				$this->data[ $r['module_id'] ] = $r;
			}
		}
	}
	function treeview($parent_id=0, $edit=false){// создаем дерево для вывода в левом фрейме - при работе с модулем
		
		if(!$this->parent_id__module_id[$parent_id]){ return false; }
		
		foreach($this->parent_id__module_id[$parent_id] as $k=>$true){
			
			$a = $GLOBALS['SYSTEM']['module_id__access'][ $k ];
			
			$access = $edit? true : ( ($this->data[$k]['type']!='1' && ($a=='1' || $a=='4' || $a=='5' || $a=='7'))? true : false );
			
			if($access){// если не скрыт И доступен для чтения
				
				if($edit){
					$href = $_SERVER['SYSTEM']['URL_MODULE_DIR'].'right.php?module_id='.$k;
					if($this->data[$k]['type']=='1'){$href .= '" style="color: #999999';}
					else if($this->data[$k]['type']=='2'){$href .= '" style="color: #0099FF';}
					else if($this->data[$k]['type']=='3'){$href .= '" style="color: #FF9933';}
				}else{
					$href = $this->data[$k]['path']? $this->data[$k]['path'].(strstr($this->data[$k]['path'], '?')? '&':'?').'moduleID='.$k : '#';
				}
				
				$this->treeview .= '<li id="'.$k.'" alt="'.(int)$this->data[$k]['type'].'"><a href="'.$href.'" alt="'.(int)$this->data[$k]['position'].'">'.htmlspecialchars($this->data[$k]['name']).'</a>';
				
				if($this->parent_id__module_id[$k]){
					$this->treeview .= '<ul>';
					$this-> treeview($k, $edit);
					$this->treeview .= '</ul>';
				}
				$this->treeview .= '</li>';
			}
		}
	}
	function options($parent_id=0){// создаем дерево для вывода в элементе SELECT - при редактировании модулей
		
		if(!$this->parent_id__module_id[$parent_id]){ return false; }
		
		foreach($this->parent_id__module_id[$parent_id] as $k=>$true){
			
			if($k!=$_POST['module_id'] && $k!=$_GET['module_id']){
				
				$i++;
				
				for($b=0; $b<($this->n*2); $b++){ $nbsp .= '&nbsp;&nbsp;&nbsp;'; }
				
				if($this->data[$k]['type']=='1'){$style.='e5e5e5';}
				else if($this->data[$k]['type']=='2'){$style.='99FFFF';}
				else if($this->data[$k]['type']=='3'){$style.='FFEFC0';}
				
				$this->options .= '<option value="'.$k.'" '.($style?'style="background-color: #'.$style.';"':'').'>'.$nbsp.$i.'. '.strip_tags($this->data[$k]['name']).' ('.$this->data[$k]['position'].')</option>';
				
				$nbsp = $style = '';
				
				$max_position = ($this->data[$k]['position']>$max_position)?$this->data[$k]['position']:$max_position;
				
				if($this->parent_id__module_id[$k]){
					$this->n++;
					$this-> options($k);
					$this->n--;
				}
			}
		}
		$this->js_option_max_position .= 'js_option_max_position['.$parent_id.']='.(int)$max_position.';';
	}
	function select($module_id){// нахождение информации о модуле
		if($module_id && is_numeric($module_id)){
			$this-> modules();
			return $this->data[ $module_id ];
		}
	}
	function insert($data=array()){// добавление модуля и информации о нем
		if($data && mysql_("INSERT INTO ".P."modules VALUES ("._._.", 
			"._.$data['parent_id']._.", 
			"._.$data['position']._.", 
			"._.$data['type']._.", 
			"._.$data['name']._.", 
			"._.$data['path']._.")")){
			
			$module_id = mysql_insert_id();
			
			// добавляю администратору права к новому модулю
			mysql_query("INSERT INTO ".P."access VALUES('".$module_id."', '".$GLOBALS['SYSTEM']['user']['user_id']."', '7')");
			
			return $module_id;
		}
	}
	function update(){// обновление данных о модуле
		if(mysql_("UPDATE ".P."modules SET 
			parent_id="._.$_POST['parent_id']._.", 
			position="._.$_POST['position']._.", 
			type="._.$_POST['type']._." , 
			name="._.$_POST['name']._." , 
			path="._.$_POST['path']._." 
			"._."WHERE"._." module_id="._.$_GET['module_id']._."")){
			return mysql_affected_rows();
		}
	}
	function getDelete($module_id){// находим рекурсивно все дочерние модули
		$this->delete_in .= $module_id;
		if($this->parent_id__module_id[$module_id]){
			foreach($this->parent_id__module_id[$module_id] as $id=>$v){
				if($this->parent_id__module_id[$id]){
					$this-> getDelete($module_id);
				}else{
					$this->delete_in .= "','".$id;
				}
			}
		}
	}
	function delete($module_id=0){// удаление модулей
		
		if($module_id){
			
			$this-> getDelete($module_id);// находим рекурсивно все дочерние строки
			
			if($this->delete_in && mysql_query("DELETE FROM ".P."modules WHERE module_id IN ('".$this->delete_in."')")){// удаляем модуль и возможные дочерние модули
				
				mysql_query("DELETE FROM ".P."access WHERE module_id IN ('".$this->delete_in."')");// удаляем настройки доступа удаляемых модулей
				
				return mysql_affected_rows();
			}
		}
	}
	function access_explorer($parent_id, $module_id__access){// настроки доступа пользователя
		
		if(!$this->parent_id__module_id[$parent_id]){ return false; }
		
		for($i=0; $i<($this->access_explorer_i*2); $i++){ $nbsp .= '&nbsp;&nbsp;&nbsp;'; }
		
		$access = $GLOBALS['SYSTEM']['module_id__access'];// доступ к модулям текущего сайта
		
		foreach($this->parent_id__module_id[$parent_id] as $k=>$true){
			
			if($access[$k]){// если у текущего пользователя имеются права к модулю - разрешаю выставление прав доступа
				
				$a = $module_id__access[$k];// права выбранного пользователя к данному модулю
				
				$style = $this->data[$k]['type']? 'style="color:#'.(($this->data[$k]['type']==1)?'999999':'009933').'"' : '';
				
				$read = $write = $delete = '';
				if($a=='1' || $a=='4' || $a=='5' || $a=='7'){ $read = 'checked'; }
				if($a=='2' || $a=='4' || $a=='6' || $a=='7'){ $write = 'checked'; }
				if($a=='3' || $a=='5' || $a=='6' || $a=='7'){ $delete = 'checked'; }
				
				$this->access_explorer.='<tr>
					<td><p><b '.$style.'>'.$nbsp.$this->data[$k]['name'].'</p></td>
					<td class="center"><input id="r'.$k.'" name="module_id['.$k.'][r]" type="checkbox" '.$read.' '.(($access[$k]<'1')?'disabled':'').'></td>
					<td class="center"><input id="w'.$k.'" name="module_id['.$k.'][w]" type="checkbox" '.$write.' '.(($access[$k]<'2')?'disabled':'').'></td>
					<td class="center"><input id="d'.$k.'" name="module_id['.$k.'][d]" type="checkbox" '.$delete.' '.(($access[$k]<'3')?'disabled':'').'></td>
				</tr>';
				
				if($this->parent_id__module_id[$k]){
					$this->access_explorer_i++;
					$this-> access_explorer($k, $module_id__access);
					$this->access_explorer_i--;
				}
			}
		}
	}
	function fullname($child_id){
		
		$parent_id = $this->data[$child_id]['parent_id'];
		
		if($parent_id){
			$name = $this-> fullname($parent_id).' » ';
		}
		$name .= $this->data[$child_id]['name'];
		
		return htmlspecialchars(strip_tags($name));
		
	}
	function pages_options($parent_id=0){// создает список option только для модулей страниц
		
		if(!$this->parent_id__module_id[$parent_id]){ return false; }
		
		foreach($this->parent_id__module_id[$parent_id] as $k=>$true){
			
			if($k){
				
				for($b=0; $b<($this->n*2); $b++){ $nbsp .= '&nbsp;&nbsp;&nbsp;'; }
				
				$this->pages_options .= '<option value="'.$k.'" '.(($this->data[$k]['type']==3)?'':'disabled').'>'.$nbsp.htmlspecialchars($this->data[$k]['name']).'</option>';
				
				$nbsp = '';
				
				if($this->parent_id__module_id[$k]){
					$this->n++;
					$this-> pages_options($k);
					$this->n--;
				}
			}
		}
	}
}
$modules = new modules();
?>
