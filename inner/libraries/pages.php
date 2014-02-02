<?php
/* Класс для работы с данными страниц сайта
Класс обладает логированием вызова ф-ий для отслеживания ошибок, для этого достаточно выполнить текстовую замену этого:
// log_('call:
на это:
 log_('call:
и обратную при завершении работ по отслеживанию ошибок.
*/
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');// класс работы с кэшем
if($GLOBALS['SYSTEM']['config']['yapro_url_transliterate_off']){// если Отключить транслитерацию URL
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/rurl.php');// класс работы с русскими урл
}
class pages {
	// эти данные нужны всегда и везьде
	function pages(){// log_('call: pages()');
		
		$this->page_id__parent_id = $this->parent_id__page_id = $this->page_id__access = array();
		
		if($q = mysql_('SELECT page_id, parent_id FROM '.P.'pages ORDER BY time_created, page_id')){// находим базовые данные страниц сайта
			while($r = mysql_fetch_assoc($q)){
				$this->page_id__parent_id[ $r['page_id'] ] = $r['parent_id'];
				$this->parent_id__page_id[ $r['parent_id'] ][ $r['page_id'] ]++;
			}
			
			// автоматическое включение опции ограничения показываемых страниц
			if(!$GLOBALS['SYSTEM']['config']['yapro_admin_pages_max'] && $this->page_id__parent_id && count($this->page_id__parent_id)>1000){
				$GLOBALS['SYSTEM']['config']['yapro_admin_pages_max'] = 100;
				mysql_('UPDATE '.P.'settings SET value = 100 '._.'WHERE'._.' name = '._.'yapro_admin_pages_max'._);
			}
		}
	}
	// узнаем максимальное значение позиции в выбранном разделе
	function max_position_on_parent($parent_id=0){// log_('call: max_position_on_parent('.$parent_id.')');
		
		if($this->parent_id__page_id[ $parent_id ]){
			
			$max = @mysql_fetch_row(mysql_('SELECT MAX(position) FROM '.P.'pages WHERE parent_id='.(int)$parent_id));
			
			return $max['0'];
		}
	}
	// создаем дерево страниц (например для вывода в левом фрейме ИЛИ при выборе родительского раздела)
	function treeview($parent_id=0){// log_('call: treeview('.$parent_id.')');
		
		if(!$this->parent_id__page_id[ $parent_id ]){ return false; }
		
		// определяем сортировку данных
		if($GLOBALS['SYSTEM']['sort_pages']){
			$order_by = mb_substr($GLOBALS['SYSTEM']['sort_pages'],1).' '.( (mb_substr($GLOBALS['SYSTEM']['sort_pages'],0,1)=='A')? '' : 'DESC');
		}else{
			$order_by = 'page_id';
		}
		//-----------------------------
		$start = $end = 0;
		$latest = '';
		if($GLOBALS['SYSTEM']['config']['yapro_admin_pages_max']){// если установлен лимит
			$start = $_POST['start']? (int)$_POST['start'] : 0; unset($_POST['start']);// удаляем $_POST['start'] т.к. он нужен только в 1-ый раз
			$end = (int)$GLOBALS['SYSTEM']['config']['yapro_admin_pages_max'];
			$limit = " LIMIT ".$start.", ".($end+1);// добавляем +1 для проверки на последнюю
		}
		//-----------------------------
		$limit_page_id = 0;
		$limit_page_id_ok = false;
		if($_COOKIE['system_pages_left_selected_parents_id']){
			
			$ex = array_flip(array_reverse(explode(',', $_COOKIE['system_pages_left_selected_parents_id'])));
			
			foreach($this->parent_id__page_id[ $parent_id ] as $page_id => $true){
				if($page_id && isset($ex[ $page_id ])){// если в текущем разделе есть один из page_id
					$limit = ' LIMIT '.$start.',777777777777777';// отменяем лимит
					$limit_page_id = $page_id;// указываем конечную выводимую page_id в данном раделе (если она будет > лимит максимума)
					break;
				}
			}
		}
		//-----------------------------
		// находим базовые данные страниц сайта
		if($q = mysql_('SELECT page_id, access, position, name FROM '.P.'pages WHERE parent_id='.(int)$parent_id.' ORDER BY '.$order_by.$limit)){
			$i = 0;
			while($r = mysql_fetch_assoc($q)){
				
				// проверка применяемая в правом фрэйме при выборе родителей, поэтому пропускается текущая страница
				if($r['page_id']==$_GET['page_id']){ continue; }
				
				$i++;
				
				if($limit_page_id){// если указана выбранная страница в данном раделе
					
					if($limit_page_id_ok && $end && $i > $end){ $latest = ''; break; }
					
					if($limit_page_id == $r['page_id']){// если это выбранная страница/раздел
						
						$limit_page_id_ok = true;// даем последующим итерация информацию об этом
						
					}
					
				}else{
					// если привышается лимит выводимых страниц, значит предыдущая страница была не последней
					if($end && $i > $end){ $latest = ''; break; }
				}
				
				$latest = '<li id="'.$r['page_id'].'"';// переменная для определения самой последней записи
				
				$color = ($this->page_id__parent_id[ $_GET['page_id'] ]==$r['page_id'])? ' style="color:#FF0000"' : '';// цвет родительского раздела
				
				if(!$color && $this->page_id__access && !$this-> page_id__access($r['page_id'])){ $color = ' style="color:#999"'; }
				
				$this->treeview .= '<li id="'.$r['page_id'].'" alt="'.(int)$r['access'].'.'.(int)$r['position'].'.'.($start + $i).'"><a'.$color.'>'.str_replace('&amp;','&',htmlspecialchars(strip_tags($r['name']))).'</a>';
				
				
				// если не отменено построение дочернего древовидного меню
				if($this->parent_id__page_id[ $r['page_id'] ]){
					$this->treeview .= '<ul>';
					$this-> treeview($r['page_id']);
					$this->treeview .= '</ul>';
				}
				
				$this->treeview .= '</li>';
				
			}
			// чтобы обозначить самые последние пункты меню, мы ставим им пометку 
			if($latest){ $this->treeview = str_replace($latest, $latest.' class="latest"', $this->treeview); }
		}
	}
	// проверяет доступ пользователя к page_id
	function page_id__access($page_id=0){// log_('call: page_id__access('.$page_id.')');
		
		if($page_id && (!$GLOBALS['SYSTEM']['communications']->page_id__allow || $this->page_id__access[ $page_id ]) ){
			// log_('return: доступ - есть');
			return true;
		}
		// log_('return: доступ - нет');
	}
	// ф-я нахождения данных по page_id - для модуля /admin/modules/pages/right.php
	function select($page_id=0){// log_('call: select('.$page_id.')');
		
		if(!$page_id || !$this-> page_id__access($page_id) ){ return false; }
		
		$r = @mysql_fetch_assoc(mysql_('SELECT * FROM '.P.'pages WHERE page_id='.$page_id));// находим данные основных полей
		if($r){
			$a = url($r['page_id'], 2, false);
			$r['url'] = $a['real_url'];
			$r['url_type'] = $a['url_type'];
			$r['url_link'] = $a['url'];
			if($r['url_type']<'2'){
				$dir = array_reverse(explode('/',$r['url']));
				$r['url'] = $dir['0'];
			}
		}
		return $r;
	}
	// проверяет наличие parent_id в дочерних страницах page_id
	function parent_in_children($parent_id=0, $page_id=0){// log_('call: parent_in_children('.$parent_id.', '.$page_id.')');
		if($page_id && $this->parent_id__page_id[ $page_id ]){
			$return = false;
			foreach($this->parent_id__page_id[ $page_id ] as $k=>$v){
				
				if($parent_id == $k){ return true; }// parent_id в дочерних страницах page_id найден
				
				if($this->parent_id__page_id[ $k ] && !$return){
					$return = $this-> parent_in_children($parent_id, $k);
				}
			}
			return $return? true : false;// parent_id в дочерних страницах page_id найден/не найден
		}
	}
	// проверяет возможность добавления/сохранения страницы в выбранный parent_id
	function access_insert_to_parent($parent_id=0, $page_id=0){// log_('call: access_insert_to_parent(parent_id='.$parent_id.', page_id='.$page_id.')');
		
		// если настроек доступа нет - доступ есть ИЛИ если родительский раздел, к которому есть доступ - доступ есть
		if(!$GLOBALS['SYSTEM']['communications']->page_id__allow || (!$parent_id && $GLOBALS['SYSTEM']['communications']->page_id__allow[ $page_id ]==3)){
			// log_('return: true - доступ - есть');
			return true;
		}
		
		// если в один из родительских разделов разрешено сохранять - то в дочерние сохранение так же разрешено (т.к. наследуется)
		while(isset($parent_id)){// проходим по существующим родительским страницам
			
			if($GLOBALS['SYSTEM']['communications']->page_id__allow[ $parent_id ]==2 || $GLOBALS['SYSTEM']['communications']->page_id__allow[ $parent_id ]==3){
				// log_('return: true - доступ - есть');
				return true;// доступ - есть
			}
			
			// определяем parent_id для текущего ID для этого выполняем доп. проверки
			if($parent_id && $this->page_id__parent_id[ $parent_id ] && $parent_id != $this->page_id__parent_id[ $parent_id ]){
				$parent_id = $this->page_id__parent_id[ $parent_id ];// проверим доступ у родителя текущего родителя
			}else{
				unset($parent_id);// прекращаем итерацию
			}
		}
		// log_('return: false - доступ - нет');
		return false;// доступ - нет
	}
	// создает на основе переданных данных УРЛ и проверяет его среди действующих URL в таблице page_url (исключая URL-ссылки - url_type==3)
	function url_check($r=array()){// log_('call: url_check('.print_r($r,1).')');
		
		$url = $this-> url_create($r);// формируем полный урл данной страницы
		
		// попробуем найти такой же урл в базе урл, но непринадлежащий текущей странице (за исключением прямых ссылок)
		$other = @mysql_fetch_assoc(mysql_('SELECT page_id FROM '.P.'pages_url WHERE 
		page_id != '.(int)$r['page_id'].' AND url = '._.$url._.' AND url_type != 3 LIMIT 1'));
		
		if($other['page_id'] && $other['page_id']!=$r['page_id']){// если в базе урл - принадлежащий не текущей странице урл уже существует:
			
			if($r['url_type']<'2'){// если у текущей страницы не уникальный урл и не прямая ссылка: возвращем только конечную часть урл
				$ex = array_reverse(explode('/',$url));
				$url = $ex['0'];
			}
			return $url;// отдаем сформированный УРЛ который уже используется среди действующих URL в таблице page_url
		}
		return ;// если УРЛ не используется среди действующих URL в таблице page_url, ничего не отдаем
	}
	// делает то же самое, что и функция url_check, но создает обычный но уникальный УРЛ, возвращая переданные в функцию массив данных
	function url_uniq($r=array()){// log_('call: url_uniq('.print_r($r,1).')');
		
		if(!$r){ echo 'url_uniq: данные не переданы'; exit; }
		if(!is_array($r)){ echo 'url_uniq: данные не являются массивом'; exit; }
		
		$r['url_type'] = 1;// создаем обычный УРЛ
		
		$url = $this-> url_create($r, true);// разрешаем генерировать УРЛ при недостаточном количестве данных и формируем полный урл данной страницы
		
		if($q = mysql_('SELECT url FROM '.P.'pages_url WHERE 
		page_id != '.(int)$r['page_id'].' AND url LIKE '._.$url.'%'._.' AND url_type != 3')){
			$a = array();
			while($r = mysql_fetch_row($q)){// находим похожие УРЛ
				$a[ $r['0'] ] = true;
			}
			if($a){// проверка на уже заведенное УРЛ
			    $i = 0;
			    $check = true;
			    while($check){
			    	if($a[ $url ]){// если УРЛ уже зарегистрировано в системе
			    		$i++;
			    		$new_url = $url.$i;// немного меняем УРЛ, делая его уникальным
			    		if(!$a[ $new_url ]){
			    			$url = $new_url;
			    			$check = false;
			    		}
			    	}else{
			    		$check = false;
			    	}
			    }
			}
		}
		return $url;
	}
	// сохраняем изображения с других сайтов на сайте, например: images('/home/site', '/uploads/dir', 'site.ru', 'Текст с изображениями')
	function images($root='', $dir='', $host='', $text=''){// log_('call: images('.$root.', '.$dir.', '.$host.', '.$text.')');
		
		// проверяем обязательные переменные
		if(!$host){ echo '$host не указан'; exit; }
		if(!$root){ echo '$root не указан'; exit; }
		if(!is_dir($root)){ echo '$root не является директорией'; exit; }
		if(!$dir){ echo '$dir не указан'; exit; }
		if(!is_dir($root.$dir)){ echo '$root.$dir не является директорией'; exit; }
		if(!write_('content', $root.$dir.'/test_save_images')){ echo 'Недостаточно прав для записи в директорию $root.$dir'; exit; }
		
		if($text){
			
			$images = imgSite($text);
			$host = host_clear($host);
			
			if($images){// если найдены адреса изображений
				
				foreach($images as $path){
					
					if(mb_substr($path,0,7)=='http://'){// если указан прямой путь
						
						$outer = @parse_url_($path);
						
						if($outer['host']){// если найдено доменное имя внешнего сайта
							
							$outer_host = host_clear($outer['host']);
							
							if($outer_host != $host){// если это изображение с стороннего сайта
								
								$file_content = file_get_contents($path);// находим содержимое изображения
								
								if($file_content){// если содержание файла получено
									
									$type = array_reverse(explode('.', $path));// находим расширение файла
									
									// если расширение изображения более 4 символов - сохраняем его как jpg
									$type = (mb_strlen($type['0'])>4)? 'jpg' : addslashes($type['0']);
									
									$name = md5($path).'.'.$type;// формируем имя файла
									
									$to_path = $dir.'/'.$name;
									
									if(!write_($file_content, $root.$to_path)){// пытаемся сохранить файл в директории пользователя
										
										log_(__FILE__.' : Недостаточно прав для записи файла : '.$root.$dir.$name);
										
									}else{
										
										@chmod($root.$dir.$name, 0664);
										
										// меняем внешние адреса изображений на внутренние
										$text = str_replace($path, "http://".$host.$to_path, $text);
									}
								}
							}
						}
					}
				}
			}
			return $text;
		}
	}
	// обновление данных
	function update($r=array()){// log_('call: update('.print_r($r,1).')');
		// log_('update: 1');
		$r['page_id'] = (int)$r['page_id'];
		if($r['page_id']){
			$this->page = @mysql_fetch_assoc(mysql_('SELECT time_created FROM '.P.'pages WHERE page_id = '.$r['page_id']));
			$this->page['url'] = url($r['page_id'],3);
			$this->page['url_type'] = url($r['page_id'],1);
		}else{
			$this->page = array();
		}
		// проверяем наличие page_id && parent_id не должен быть равен page_id && parent_id не должен быть дочерним для page_id
		if($r['page_id'] 
		&& $r['page_id']!=$r['parent_id'] 
		&& !$this-> parent_in_children($r['parent_id'], $r['page_id']) 
		&& $this-> access_insert_to_parent($r['parent_id'], $r['page_id']) 
		&& $this-> page_id__access($r['page_id'])){
			// log_('update: 2');
			$page_id = $this->insert($r);
			// отладка: echo '-UPDATE OK-'; return false;
			return $page_id;
		}
	}
	// добавление страницы
	function insert($r=array()){// log_('call: insert('.print_r($r,1).')');
		
		// log_('insert: 1');
		if(!$r){ return false; }
		// log_('insert: 2');
		// проверка на неправильное добавление к родителю, который на самом деле является дочерним
		if($r['page_id'] && ($r['page_id']==$r['parent_id'] || $this-> parent_in_children($r['parent_id'], $r['page_id']) ) ){ return false; }
		// log_('insert: 3');
		// проверяет возможность добавления в выбранный parent_id
		if(($r['page_id'] && !$this-> page_id__access($r['page_id'])) || !$this-> access_insert_to_parent($r['parent_id'], $r['page_id']) ){ return false; }
		// log_('insert: 4');
		// если позиция не задана - автоматически устанавливаем значение позиции инкрементом
		if(!$r['position'] && $GLOBALS['SYSTEM']['config']['yapro_admin_pages_autoincrement'] & $this->parent_id__page_id[ $r['parent_id'] ]){
			$r['position'] = $this-> max_position_on_parent($r['parent_id']) + 10;
		}
		
		if($r['parent_id']){
			$parents_id = '';
			$parent_id = $r['parent_id'];
			while($parent_id){
				$parents_id .= $parent_id.',';
				$parent_id = $this->page_id__parent_id[ $parent_id ];
			}
			$this-> parents_id($r['page_id'], $parents_id);
		}
		
		// работа с шаблонами
		if($r['page_id']){
			mysql_('DELETE FROM '.P.'pages_templates WHERE page_id='.$r['page_id']) or die(mysql_error());
		}
		$template_level__template = array();
		foreach($r as $k => $v){
			$e = explode('_', $k);
			
			if($k && $v && $e['0']==='template' && isset($e['1']) && is_numeric($e['1'])){
				$template_level__template[ $e['1'] ] = $v;
				unset($r[$k]);
			}
		}
		
		$r['name'] = $r['name']? $r['name'] : '-без названия '.date("d.m.Y H:i:s").'-';// если забыли добавить название
		
		if($r['time_created']){
			if(strstr($r['time_created'], '.')){
				$r['time_created'] = time_($r['time_created']);
			}
		}else{// если забыли добавить дату публикации
			$r['time_created'] = $this->page['time_created']? $this->page['time_created'] : time();
		}
		
		if($r['time_modified']){
			if(strstr($r['time_modified'], '.')){
				$r['time_modified'] = time_($r['time_modified']);
			}
		}else{// если забыли добавить дату изменения
			$r['time_modified'] = time();
		}
		
		$r['access_inherited'] = $r['access'];// наследуемый доступ к дочерним страницам
		
		$nesting = @mysql_fetch_row(mysql_('SELECT nesting FROM '.P.'pages WHERE page_id = '.(int)$r['parent_id']));
		$r['nesting'] = (int)$nesting['0'] + 1;// уроверь вложенности (иными словами глубина страницы)
		
		// отладка: echo '-INSERT OK-'; return false;
		
		if($q=mysql_('SHOW COLUMNS FROM '.P.'pages')){// находим существующие поля $page_table
			// log_('insert: 5');
			$insert_arr = array();// обрабатываем и подготавливаем POST-данные
			while($f=mysql_fetch_assoc($q)){
				$value = $r[ $f['Field'] ];
				if($value && is_array($value)){// если  данные существуют и являются массивом (при мультивыборе в SELECT)
					$str = '';
					foreach($value as $k=>$v){// перебираем каждое значение массива
						if($v){
							//$r[ $v ] = $v;
							$str .= $v.'.';// создаем строку данных разделенных точкой
						}
					}
					$r[ $f['Field'] ] = substr($str,0,-1);
				}
				$insert_arr[ $f['Field'] ]++;// создаем массив существующих полей в таблице $page_table
			}
			
			$insert = '';// создаем INSERT
			foreach($insert_arr as $Field => $one){
				if( array_key_exists($Field, $r) ){// если данные указаны для сохранения (они могут быть даже пустые)
					$value = $r[ $Field ];
					$insert .= '`'.$Field.'` = '._.( ($value==='on')? 1 : $value )._.',';// заполняем строку вставки
				}
			}
			
			if($insert && mysql_(($r['page_id']? 'UPDATE' : 'INSERT INTO').' '.P.'pages 
			SET '.substr($insert,0,-1).($r['page_id']? _.'WHERE'._.' page_id='.$r['page_id'] :''))){
				// log_('insert: 6');
				if(!$r['page_id']){ $r['page_id'] = mysql_insert_id(); }
				
				// заполняем массивы для правильных проверок в др. ф-ях данного класса
				$this->page_id__parent_id[ $r['page_id'] ] = $r['parent_id'];
				$this->parent_id__page_id[ $r['parent_id'] ][ $r['page_id'] ]++;
				
				// работа с шаблонами
				if($template_level__template){
					foreach($template_level__template as $template_level => $template){
						if(!mysql_('INSERT INTO '.P.'pages_templates SET 
						page_id = '.$r['page_id'].', 
						template = '._.$template._.', 
						template_level = '.$template_level)){
							exit;
						}
					}
				}
				/* если у страницы не указан урл - пусть вместо урл будет ее ИД (на доработке)
				if(!$r['url'] && !$r['url_type']){
					mysql_('DELETE FROM '.P.'pages_url WHERE page_id = '.$r['page_id']);
				}
				*/
				if($r['url'] || $r['url_type']){
					
					$set = 'url = '._.$r['url']._.', url_type = '.(int)$r['url_type'].', page_id = '.$r['page_id'];
					mysql_('INSERT INTO '.P.'pages_url SET '.$set.' ON DUPLICATE KEY UPDATE '.$set);
					
					// если данная страница ранее существовала и данные об URL у нее поменялись
					if($r['url_type']<'3'){
						
						write_($r['page_id'], $_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/'.md5($r['url']));// добавляем хеш URL
						
						if($r['url_type']<'2'){// если у текущей страницы не уникальный урл и не прямая ссылка: возвращем только конечную часть урл
							
							if($this->page && ($r['url']!=$this->page['url'] || $r['url_type']!=$this->page['url_type']) ){
								
								$this-> url_update($r['page_id'], $r['url']);// выполняем изменение дочерних урл, страницам у которых url_type<2
								
							}
						}
					}
				}
				
				// определяем реальный доступ к текущей странице И выставляем данный реальный доступ дочерним страницам
				$parent_inherited = @mysql_fetch_row(mysql_('SELECT access_inherited FROM '.P.'pages WHERE page_id='.(int)$r['parent_id']));
				
				if($parent_inherited['0']>$r['access_inherited']){// если реальный доступ у родителя > выбранного доступа страницы - сохраняем
					
					$r['access_inherited'] = $parent_inherited['0'];
					
					mysql_('UPDATE '.P.'pages SET access_inherited='.$parent_inherited['0'].' '._.'WHERE'._.' page_id='.$r['page_id']);
					
				}
				// меняем реальный доступ дочерним страницам - только тем, у кого реальный доступ меньше чем у текущей страницы
				$this-> access_inherited($r['page_id'], $r['access_inherited']);
				
				$GLOBALS['system_cache']->update('pages');// обновляю кэш время изменения страниц
				
				return $r['page_id'];
			}
		}
	}
	// применение доступа к дочерним страницам
	function access_inherited($parent_id=0, $access_inherited=0){// log_('call: access_inherited('.$parent_id.', '.$access_inherited.')');
		
		if($parent_id && $this->parent_id__page_id[ $parent_id ]){// если у текущей страницы есть дочерние страницы
			
			mysql_('UPDATE '.P.'pages SET access_inherited='.$access_inherited.' 
			'._.'WHERE'._.' parent_id='.$parent_id.' AND access<='.$access_inherited);
			
			foreach($this->parent_id__page_id[ $parent_id ] as $page_id => $true){//возможно дочерние страницы являются разделами
				
				if($this->parent_id__page_id[ $page_id ]){// если дочерняя страница являются разделом
					
					$this-> access_inherited($page_id, $access_inherited);
					
				}
			}
		}
	}
	// нахождение всех дочерних page_id
	function getDelete($page_id){// log_('call: getDelete('.$page_id.')');
		if($page_id && $this->parent_id__page_id[$page_id]){
			foreach($this->parent_id__page_id[$page_id] as $k=>$true){
				$this->delete_in .= $this-> page_id__access($k)? ','.$k : '';
				$this-> getDelete($k);
			}
		}
	}
	// удаление страницы или раздела целиком
	function delete($page_id=0, $delete_kids=false){// log_('call: delete('.$page_id.', '.$delete_kids.')');
		
		if(!$page_id || !$this-> page_id__access($page_id)){ return false; }
		
		$this->delete_in = $page_id;
		
		if($delete_kids){// если страница удаляется
			
			$this-> getDelete($page_id);// находим рекурсивно все дочерние строки
			
			// если задана конфигурация: Страницы. При удалении страниц удалять прикрепленные файлы из полей
			if($GLOBALS['SYSTEM']['config']['yapro_admin_pages_autodelete_files'] && 
			$q = mysql_('SELECT '.$GLOBALS['SYSTEM']['config']['yapro_admin_pages_autodelete_files'].' FROM '.P.'pages 
			WHERE page_id IN ('.$this->delete_in.')')){
				while($r = mysql_fetch_assoc($q)){
					if($r){
						foreach($r as $field => $file){
							if($v && is_file($_SERVER['DOCUMENT_ROOT'].'/'.$file)){
								@chmod($_SERVER['DOCUMENT_ROOT'].'/'.$file, 0664);
								if(!@unlink($_SERVER['DOCUMENT_ROOT'].'/'.$file)){
									log_(__FILE__.' не могу удалить файл: '.$_SERVER['DOCUMENT_ROOT'].'/'.$file);
								}
							}
						}
					}
				}
			}
		}
		// отладка: echo '-DELETE OK-'; return true;
		
		mysql_('DELETE FROM '.P.'pages WHERE page_id IN ('.$this->delete_in.')');
		$check = mysql_affected_rows();// количество удаленных страниц
		
		mysql_('DELETE FROM '.P.'pages_templates WHERE page_id IN ('.$this->delete_in.')');
		
		mysql_('DELETE FROM '.P.'pages_url WHERE page_id IN ('.$this->delete_in.')');
		
		mysql_('DELETE FROM '.P.'pages_bookmarks WHERE page_id IN ('.$this->delete_in.')');
		
		mysql_('DELETE FROM '.P.'pages_rating WHERE page_id IN ('.$this->delete_in.')');
		
		if($delete_kids){// если удаляется страница со всеми дочерними
			
			$this-> pages();//пересобираем массив базовых данными для его использования дальше
			
			if($this-> page_id__access[$page_id]){ unset($this-> page_id__access[$page_id]); }// удаляем права доступа к ней
			
		}
		
		if($check){ $GLOBALS['system_cache']->update('pages'); }// обновляю кэш время изменения страниц
		
		return $check? true : false;
	}
	// ф-я находит и отдает строку всех дочерних page_id разделенных запятой
	function parent_kids($parent_id=0){// log_('call: parent_kids('.$parent_id.')');
		if($parent_id && $this->parent_id__page_id[ $parent_id ]){
			foreach($this->parent_id__page_id[ $parent_id ] as $k=>$v){
				//+if($parent_id==$k){ continue; }
				$return .= $k.',';
				if($this->parent_id__page_id[ $k ]){
					$return .= $this-> parent_kids($k);
				}
			}
			return $return;
		}
	}
	// создает полный урл на основе данных страницы /parent/url_dir
	function url_create($data=array(), $generate=false){// log_('call: url_create('.print_r($data,1).')');
		
		if($data['url']){// если УРЛ-директория задана
			$url = $data['url'];
		}else if($generate){// если разрешено автоматически генерировать УРЛ
			if($data['name']){// если Имя страницы задано
				$url = $data['name'];
			}else if($data['page_id']){// если существует уникальный page_id
				$url = $data['page_id'];
			}else{// генерируем УРЛ
				$url = time().uniqid();
			}
		}
		
		$data['url_type'] = (int)$data['url_type'];
		
		if($url && $data['url_type']<2){// если создается обычный УРЛ или обычный УРЛ с листингом
			
			// очищаем УРЛ
			if($GLOBALS['SYSTEM']['config']['yapro_url_transliterate_off']){// если Отключить транслитерацию URL
				
				$dir = $GLOBALS['rurl']-> rudir($url, $data['page_id']);
				
			}else{
				
				$dir = str_replace('_-_', '-', preg_replace('/[\-]{2,}/', '-', preg_replace('/[\_]{2,}/', '_', 
				str_replace(' ','_', preg_replace('/[^-a-z0-9]/sUi', ' ', translite(strtolower_(trim($url)), 's'))))));
				
			}
			
			$parent = array();
			if($data['parent_id'] && is_numeric($data['parent_id'])){
				$parent = @mysql_fetch_assoc(mysql_('SELECT url, url_type FROM '.P.'pages_url WHERE page_id = '.(int)$data['parent_id']));
			}
			
			if($dir){// если после отчистки УРЛ существует
				
				if(mb_substr($dir,0,1)=='-'){ $dir = mb_substr($dir,1); }// убираем знак - в начале
				
				if($dir && mb_substr($dir,-1)=='-'){ $dir = mb_substr($dir,0,-1); }// убираем знак - в конце
				
				if(mb_substr($dir,0,1)=='_'){ $dir = mb_substr($dir,1); }// убираем знак _ в начале
				
				if($dir && mb_substr($dir,-1)=='_'){ $dir = mb_substr($dir,0,-1); }// убираем знак _ в конце
				
				if(!$dir){ $dir = $data['page_id']? $data['page_id'] : time().uniqid(); }// если после удаления знаков минуса урл перестает существовать
				
				$url = '/'.$dir;
				
				if($parent['url']){// если родительский URL найден
					$url = $parent['url'].$url;
				}
			}else{// т.к. после очистки УРЛ не существует
				
				$url = '/'.($data['page_id']? $data['page_id'] : time().uniqid() );// создаем урл из тех данных, что есть
				
				if($parent['url']){// если родительский URL найден
					$url = $parent['url'].$url;
				}
			}
		}
		return trim($url);
	}
	// изменение урл страницы в соответсвии с урл родителя
	function url_update($parent_id=0, $url=''){// log_('call: url_update('.$parent_id.', '.$url.')');
		
		if(!$this->parent_id__page_id[ $parent_id ] || !$url){ return false; }
		
		// находим все дочерние page_id
		if($q = mysql_('SELECT page_id FROM '.P.'pages WHERE parent_id = '.$parent_id)){
			
			$inserted_page_id = array();// массив который нужен для проверки, чтобы работать с последними версиями URL
			
			while($r = mysql_fetch_assoc($q)){
				
				$a = url($r['page_id'], 2, false);
				$page_url = $a['url'];
				$page_url_type = $a['url_type'];
				
				if($url_type > 1){ continue; }
				
				if($inserted_page_id[ $r['page_id'] ]){ continue; }// если данной странице уже изменен урл - пропускаем ее
				
				$inserted_page_id[ $r['page_id'] ] = 1;// делаем отметку о том, что данной странице урл изменен
				
				$dir = array_reverse(explode('/', $page_url));
				
				if(!$dir['0']){ $dir['0'] = $r['page_id']; }// если урл нет (был какой-то сбой переноса данных)
				
				$new_url = $url.'/'.$dir['0'];
				
				write_($r['page_id'], $_SERVER['DOCUMENT_ROOT'].'/cache/url/md5__page_id/'.md5($new_url));
				
				mysql_('UPDATE '.P.'pages_url SET url = '._.$new_url._.' '._.'WHERE'._.' page_id = '.$r['page_id']);
				
				$this-> url_update($r['page_id'], $new_url);// выполняем изменение дочерних урл
				
			}
		}
	}
	// построение урл страницы page_id
	function url($page_id=0){// log_('call: url('.$page_id.')');
		if($page_id==$GLOBALS['SYSTEM']['config']['yapro_index_id']){// если ID индексной страницы
			$r['url'] = '/';
		}else if($page_id){
			$r = @mysql_fetch_assoc(mysql_('SELECT url, url_type FROM '.P.'pages_url WHERE page_id = '.$page_id));
			$r['url'] .= ($r['url_type']<2)? $GLOBALS['SYSTEM']['config']['yapro_page_ext'] : '';
		}else{
			$r['url'] = '/404';
		}
		return ( ($r['url_type']==3 && (substr($r['url'], 0, 7)=='http://' || substr($r['url'], 0, 7)=='https://') )? '' : 'http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'] ).$r['url'];
	}
	// построение списка option-сортировки по существующим полям
	function fields_sort_options(){// log_('call: fields_sort_options()');
		
		$options = '';// создаем SELECT сортировки
		if($q = mysql_('SELECT title, name FROM '.P.'fields WHERE parent_id=1 AND sync_pages=1 ORDER BY position')){
			while($r = mysql_fetch_assoc($q)){
				
				switch($r['name']){
					case 'time_created':
						$t = 'по дате публикации';
						$a = 'от старых к новым';
						$d = 'от новых к старым';
						break;
					case 'time_modified':
						$t = 'по дате изменения';
						$a = 'от старых к новым';
						$d = 'от новых к старым';
						break;
					case 'name':
						$t = 'по имени';
						$a = 'от А до Я';
						$d = 'от Я до А';
						break;
					case 'position':
						$t = 'по порядковому номеру';
						$a = 'от меньшего к большему';
						$d = 'от большего к меньшему';
						break;
					default:
						$t = strip_tags($r['title']);
						$a = 'по возрастанию';
						$d = 'по убыванию';
				}
				
				$options .= '<OPTGROUP LABEL="&nbsp;'.$t.'">
					<option value="A'.$r['name'].'">'.$a.'</option>
					<option value="D'.$r['name'].'">'.$d.'</option>
				</OPTGROUP>';
			}
		}
		return $options;
	}
	function parents_id($page_id=0, $parents_id=''){// log_('call: parents_id('.$page_id.', '.$parents_id.')');
		
		if(!$page_id || !$parents_id){ return ; }
		
		if($parents_id){
			mysql_('DELETE FROM '.P.'pages_parents WHERE page_id='.$page_id) or die(mysql_error());
			$e = explode(',',$parents_id);
			foreach($e as $parent_id){
				if($parent_id && is_numeric($parent_id)){
					mysql_('INSERT INTO '.P.'pages_parents SET page_id='.$page_id.', parent_id='.$parent_id) or die(mysql_error());
				}
			}
		}
	}
	// переиндексировать информации о родительских разделах страниц
	function parents_id_reindex($parent_id=0, $parents_id=''){// log_('call: parents_id_reindex('.$parent_id.', '.$parents_id.')');
		
		if(!$this->parent_id__page_id[ $parent_id ]){ return false; }
		
		if(!$parent_id && !$parents_id){
			mysql_('TRUNCATE TABLE '.P.'pages_parents');
		}
		
		foreach($this->parent_id__page_id[ $parent_id ] as $page_id => $true){
			
			$this-> parents_id($page_id, $parents_id);
			
			$this-> parents_id_reindex($page_id, $parents_id.','.$page_id);
			
		}
		return true;
	}
}
$GLOBALS['pages'] = new pages;
?>
