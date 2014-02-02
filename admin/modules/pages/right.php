<?php
include_once('../../libraries/access.php');// проверка доступа к данному модулю

include_once('lock.php');// проверка редактирования страницы

include_once('../../libraries/ajaxUploadFile.php');// ajax загрузка изображений

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/pages.php');// класс работы с таблицей страниц

include_once('../../libraries/fields.php');// настройки полей формы

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/cache.php');// класс работы с кэшем
//---------------------------------------------------------------------------------------------------------------------------------------
$GLOBALS['SYSTEM']['communications']-> permissions();// проверяем доступ и находим $form_id
if(!$GLOBALS['SYSTEM']['communications']->form_id){// если нет привязки формы - отображаем форму по-умолчанию
	$r = @mysql_fetch_assoc(mysql_query("SELECT field_id FROM ".P."fields WHERE parent_id=0 AND access=1"));// нахожу первую из форму (форму по-умолчанию)
	$GLOBALS['SYSTEM']['communications']->form_id = $r['field_id'];// присваиваю идентификатор формы по-умолчанию
}

$GLOBALS['SYSTEM']['sort_pages'] = $_COOKIE['system_sort_pages_right'];// сортировка страниц в выпадающем списке
//---------------------------------------------------------------------------------------------------------------------------------------
@include_once($_SERVER['SYSTEM']['MODULE_DIR'].'action_post.php');// дополнительные действия производимые до работы с страницей
//---------------------------------------------------------------------------------------------------------------------------------------
if($_POST['name']){
	
	$_GET['page_id'] = (int)$_GET['page_id'];
	
	// добавляем ключ page_id, чтобы передать его в метод создания УРЛ
	$data_url = $_POST;
	$data_url['page_id'] = $_GET['page_id'];
	
	// проверка копии действующего URL в таблице page_url (исключая URL-ссылки - url_type==3)
	$url = $GLOBALS['pages']-> url_create($data_url, $GLOBALS['SYSTEM']['config']['yapro_url_auto']);// формируем полный урл данной страницы
	
	// попробуем найти такой же урл в базе урл, но не принадлежащий текущей странице (за исключением прямых ссылок)
	$other = @mysql_fetch_assoc(mysql_('SELECT page_id, url_type FROM '.P.'pages_url WHERE 
	page_id != '._.$_GET['page_id']._.' AND url = '._.$url._.' AND url_type != 3 LIMIT 1'));
	
	if($other['page_id']){// проверим на существование страницы
		$check = @mysql_fetch_row(mysql_('SELECT page_id FROM '.P.'pages WHERE page_id = '.$other['page_id']));
		if(!$check['0']){// если страницы уже не существует, а ее урл еще в базе - удаляем его
			if(mysql_('DELETE FROM '.P.'pages_url WHERE page_id = '.$other['page_id'])){
				$other['page_id'] = 0;
			}
		}
	}
	
	// если в базе урл - принадлежащий не текущей странице урл уже существует И текущая страница не ссылка на нее:
	if($other['page_id'] && $other['page_id']!=$_GET['page_id'] && $_POST['url_type']!='3'){
		
		if($_POST['url_type']<'2'){// если у текущей страницы не уникальный урл и не прямая ссылка: возвращем только конечную часть урл
			$ex = array_reverse(explode('/',$url)); $url = $ex['0'];
		}
		echo htmlspecialchars($url); exit;// отдаем Ajax-ответ (прошу указать другой URL)
	}
	
	$reload_frame = reloadFrame();// проверка изменения значений полей (reload_frame[]) регулирующих перезагрузку левого фрейма
	
	$reload_page = false;// информирование о перезагрузке текущей страницы (применяется при автоматическом сохранении изображений с других сайтов)
	
	if(access('w')){
		
		foreach($_POST as $k=>$v){
			//---------------------------------------------------------------------------------------------------
			if(!$v){ continue; }
			//---------------------------------------------------------------------------------------------------
			if($GLOBALS['SYSTEM']['config']['yapro_text_strong2b']){// меняем <strong> на <b>
				$_POST[$k] = str_replace('<strong>', '<b>', str_replace('</strong>', '</b>', $_POST[$k]));// примечание: визивик делает имена всех тегов в нижнем регистре
			}
			//---------------------------------------------------------------------------------------------------
			if($GLOBALS['SYSTEM']['config']['yapro_img_alt']){// добавляем атрибут alt изображениям
				$_POST[$k] = attr('img', 'alt', $_POST['name'], $_POST[$k]);
			}
			//---------------------------------------------------------------------------------------------------
			if($GLOBALS['SYSTEM']['config']['yapro_a_title']){// добавляем атрибут title изображениям
				$_POST[$k] = title($_POST[$k], 'a');
			}
			//---------------------------------------------------------------------------------------------------
			// TinyMCE fix "
			if($escape = preg_replace('/<script(.+)script>/sUei', "HTMLSave('<script\\1script>')", $_POST[$k])){
				$_POST[$k] = $escape;
			}
			if($escape = preg_replace('/<style(.+)style>/sUei', "HTMLSave('<style\\1style>')", $_POST[$k])){
				$_POST[$k] = $escape;
			}
			if($escape = preg_replace('/<(.+)>/sUei', "HTMLSave('<\\1>')", $_POST[$k])){
				$_POST[$k] = str_replace('"','&quot;', $escape);
			}
			$_POST[$k] = HTMLBack($_POST[$k]);
			//---------------------------------------------------------------------------------------------------
			if($_POST[$k]){// TinyMCE fix ../ в ссылках и адресах
				preg_match_all('/="\.\.\/.+"/sU', $_POST[$k], $found);
				if($found && $found['0']){
					foreach($found['0'] as $old){
						$new = str_replace('="', '="/', str_replace('../', '', $old) );
						$_POST[$k] = str_replace($old, $new, $_POST[$k]);
					}
				}
			}
			//---------------------------------------------------------------------------------------------------
			if($GLOBALS['SYSTEM']['config']['yapro_autosave_img']){// Текст. Сохранять изображения с других сайтов на своем сайте
				
				$images = imgSite($_POST[$k]);
				
				preg_match_all('/<img(.+)src=("|\'|)(.+)>/sUi', $_POST[$k], $img);
				if($img['3']){
					foreach($img['3'] as $key => $v){
						if($img['2'][$key]){// если путь на изображение обромлен " или '
							$ex = explode($img['2'][$key], $v);
							$v = $ex['0'];
						}
						if(mb_substr($v,0,11)=='data:image/'){
							$images[] = $v;
						}
					}
				}
				
				if($images){// если найдены адреса изображений
					
					$dir_path = '/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'].date('/Y/m/d/');// определяем путь директории польлзователя
					$error = path($_SERVER['DOCUMENT_ROOT'], $dir_path);// проверяем путь
					
					if($error){
						echo $error; exit;// отдаем Ajax-ответ
					}
					
					$hosts = array();// находим доменные имена сайтов текущей CMS-системы без www.
					$host = (mb_substr($_SERVER['HTTP_HOST'],0,4)=='www.')? mb_substr($_SERVER['HTTP_HOST'],4) : $_SERVER['HTTP_HOST'];
					$hosts[ $host ]++;
					$hosts[ $_SERVER['HTTP_HOST'] ]++;
					
					foreach($images as $path){
						
						$name = $file_content = '';
						
						if(mb_substr($path,0,7)=='http://'){// если указан прямой путь
							
							$outer = @parse_url_($path);
							
							if($outer['host']){// если найдено доменное имя внешнего сайта
								
								$outer_host = (mb_substr($outer['host'],0,4)=='www.')? mb_substr($outer['host'],4) : $outer['host'];// избавляемся от www.
								
								$outer_host = (mb_substr($outer_host,-1)=='.')? mb_substr($outer_host, 0, -1) : $outer_host;// внешний сайт без точки в конце домена, напрмер: google.kz.
								
								if(!$hosts[ $outer_host ]){// если это изображение с другого сайта
									
									$file_content = file_get_contents(str_replace(' ','%20',$path));// находим содержимое изображения
									
									if($file_content){// если содержание файла получено
										
										$type = array_reverse(explode('.', $path));// находим расширение файла
										$type = (mb_strlen($type['0'])>4)? 'jpg' : addslashes($type['0']);
										$name = md5($path).'.'.$type;// формируем имя файла
									}
								}
							}
						}else if(mb_substr($path,0,11)=='data:image/'){
							
							$e = explode(',',$path);// data:image/png;base64,изображение
							if($e['1']){
								$file_content = base64_decode($e['1']);
								$e = explode(';',$e['0']);
								if($e['1']){
									$e = explode('/',$e['0']);
									if($e['1']){
										$name = md5($file_content).'.'.$e['1'];
									}
								}
							}
						}
						if($name && $file_content){
							
							if(!write_($file_content, $_SERVER['DOCUMENT_ROOT'].$dir_path.$name)){// пытаемся сохранить файл в директории пользователя
								
								log_(__FILE__.' : Недостаточно прав для записи файла : '.$_SERVER['DOCUMENT_ROOT'].$dir_path.$name);
								
							}else{
								
								@chmod($_SERVER['DOCUMENT_ROOT'].$dir_path.$name, 0664);
								
								// 'http://'. $GLOBALS['SYSTEM']['config']['yapro_http_host'].
								$_POST[$k] = str_replace($path, $dir_path.$name, $_POST[$k]);// меняем внешние адреса изображений на внутренние
								
								$reload_page = true;
							}
						}
					}
				}
			}
			//---------------------------------------------------------------------------------------------------
		}
		
		$message = $error = '';
		
		$data = $_POST;
		$data['url'] = $url;
		
		if($_GET['page_id']){// сохраняю
			
			$data['page_id'] = $_GET['page_id'];
			
			if($GLOBALS['pages']-> update($data)){
				
				$message = 'Запись сохранена';
				//yapro_page_cache($data['page_id']);
				
			}else{
				
				$error = 'Ошибка при сохранении';
				
			}
		}else{// добавляю
			
			if($_GET['page_id'] = $GLOBALS['pages']-> insert($data)){
				
				$message = 'Запись добавлена';
				//yapro_page_cache($_GET['page_id']);
				
			}else{
				
				$error = 'Ошибка при добавлении';
				
			}
		}
		
		if($error){
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ echo ',error:"Недостаточно прав на запись"'; }
		}else{
			
			if($reload_page){ echo ',reload_page:1'; }
			
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ echo ',message:"'.$message.'"'; }
			
			$time_modified = time();
			
			// сохраняю копию в архиве
			write_('<?php '.$phpToStr->toStr($_POST, 'r').' ?>', $_SERVER['SYSTEM']['MODULE_DIR'].'archive/'.$_GET['page_id'].'.'.$GLOBALS['SYSTEM']['user']['user_id'].'.'.
			$GLOBALS['SYSTEM']['module_id'].'.'.$GLOBALS['SYSTEM']['communications']->form_id.'.'.$time_modified.'.php');
			
			mysql_('INSERT IGNORE INTO '.P.'pages_archive SET 
			page_id = '._.$_GET['page_id']._.',
			user_id = '._.$GLOBALS['SYSTEM']['user']['user_id']._.',
			module_id = '._.$GLOBALS['SYSTEM']['module_id']._.',
			form_id = '._.$GLOBALS['SYSTEM']['communications']->form_id._.',
			time_modified = '._.$time_modified._.',
			pagename = '._.$_POST['name']._);
		}
	}
}

// удаляю
if(access('d') && $_GET['delete_page_id'] && $GLOBALS['pages']->delete($_GET['delete_page_id'], true)){
	//yapro_page_cache($_GET['delete_page_id']);
	
	$message = 'Запись удалена';
	$reload_frame = true;
}
//---------------------------------------------------------------------------------------------------------------------------------------
@include_once($_SERVER['SYSTEM']['MODULE_DIR'].'action.php');// дополнительные действия производимые после работы с страницей
//---------------------------------------------------------------------------------------------------------------------------------------
$r = array();
$headName = '';// работа с архивными данными
if($_GET['archive']){
	@include_once('archive/'.$_GET['archive']);
	if($r){
		foreach($r as $k=>$v){// переводим даты вида 28.01.2010 10:37:15 в timeshtamp
			if(ereg("^[0-9]{2}\.[0-9]{2}\.[0-9]{4} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$", $v, $regs)){
				$r[$k] = strtotime($v);
			}
		}
		// находим page_id (благодаря этому появляется возможность пересохранить данные)
		$ex = explode('.',$_GET['archive']);
		$r['page_id'] = $ex['0'];
	}
}else{
	$r = $GLOBALS['pages']-> select((int)$_GET['page_id']);// находим данные определенной страницы
}
//---------------------------------------------------------------------------------------------------------------------------------------
if($_GET['parent_id']){ $r['parent_id'] = $_GET['parent_id']; }// проверка для автоматического подставления выбранного родительского ID

$url = $_SERVER['PHP_SELF'].'?getStart';// общий урл 

$url .= $_GET['moduleID']?'&moduleID='.(int)$_GET['moduleID']:'';// если указан модуль страница

$r['system_url'] = $url;// урл для возможности применения ссылок в функции tr() построения полей
//---------------------------------------------------------------------------------------------------------------------------------------
$left = '<a href="'.$url.'"><img src="images/elements/plus.gif" title="Создать новую"></a>
<a href="'.$url.'&parent_id='.$r['parent_id'].'"><img src="images/elements/plus_sister.gif" title="Добавить сестринскую"></a>
<a href="'.$url.'&parent_id='.$r['page_id'].'"><img src="images/elements/plus_kid.gif" title="Добавить дочернюю"></a>';
//---------------------------------------------------------------------------------------------------------------------------------------
$url .= $r['page_id']?'&page_id='.$r['page_id']:'';// если выбрана страница
//---------------------------------------------------------------------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_POST['name']){// если POST-ajax запрос - выводим результаты его действий
	
	$parents_id = '';
	if($r['page_id'] && $q = mysql_('SELECT parent_id FROM '.P.'pages_parents WHERE page_id='.$r['page_id'])){
		while($parent_id = mysql_fetch_row($q)){
			$parents_id .= ','.$parent_id['0'];
		}
	}
	
	Cookie('system_pages_left_selected_parents_id', $r['page_id'].$parents_id);
	
	echo ',url:"'.$r['url'].'",url_type:'.(int)$r['url_type'].',position:"'.$r['position'].'",name:"'.str_replace('"','\"',$r['name']).'",actionForm:"'.$url.'#info_add=1",actionButtons:\'<input class="submit" type="submit" value="Сохранить">&nbsp;&nbsp;<a href="'.$url.'&delete_page_id='.$r['page_id'].'" onclick="return deletePage(this);" style="text-decoration: none; color: #FF0000;">Удалить</a>&nbsp;&nbsp;<a target="_blank" title="Предварительный просмотр" style="text-decoration: none;" href="/s/p?'.$r['page_id'].'">Посмотреть</a>&nbsp;<input onclick="copyMe(this);" class="submit" type="button" value="Копировать">\',reload_frame:'.(int)$reload_frame;
	
	exit;
	
}

//---------------------------------------------------------------------------------------------------------------------------------------
$right = '<td title="Переключатель сортировки выбора родительского разадела" style="padding-left: 10px"><select onchange="treeviewSort(this);" style="width: 167px">
'.str_replace('="'.$GLOBALS['SYSTEM']['sort_pages'].'"', '="'.$GLOBALS['SYSTEM']['sort_pages'].'" selected', '<option value="0">стандартная сортировка</option>'.$GLOBALS['pages']-> fields_sort_options()).'
</select></td>
<td><img src="images/logotypes/edite.gif" id="wysiwyg"></td>';
//---------------------------------------------------------------------------------------------------------------------------------------
$hidden_fields = '';
function tr($data=array()){// создаем строки для заданной формы
	
	// если идентификатор формы не объявлен ИЛИ указанный идентификатор не существует: не создаю форму заполнения
	if(!$GLOBALS['SYSTEM']['communications']->form_id || !$GLOBALS['fields']->parent_id__field_id[ $GLOBALS['SYSTEM']['communications']->form_id ]){ return ; }
	
	$trs = ''; global $hidden_fields;
	
	foreach($GLOBALS['fields']->parent_id__field_id[ $GLOBALS['SYSTEM']['communications']->form_id ] as $field_id => $true){
		
		$f = $GLOBALS['fields']->field_id__data[ $field_id ];
		
		if(!$f['access']){// если не показываем поле
			$hidden_fields .= '<input type="hidden" name="'.$f['name'].'" value="'.htmlspecialchars($data[ $f['name'] ]).'">';
			continue;
		}
		
		$data[ $f['name'] ] = trim($data[ $f['name'] ]);// определяем данные поля
		
		if($f['type']=='1'){// однострочное текстовое (INPUT)
			
			if($f['checking']=='3'){// проверка: дата времени
				$time_class = ' class="datepickerTimeField"';
				$data[ $f['name'] ] = $data[ $f['name'] ]? (strstr($data[ $f['name'] ],'.')? $data[ $f['name'] ] : date('d.m.Y H:i:s', $data[ $f['name'] ]) ) : date('d.m.Y H:i:s');
			}else{
				$time_class = '';
			}
			
			$trs .= '<tr>
				<td><p><b>'.$f['title'].'</b></p></td>
				<td><input type="text" name="'.$f['name'].'" value="'.str_replace('&amp;', '&', htmlspecialchars($data[ $f['name'] ])).'"'.$time_class.''.($f['height']?'style="height:'.$f['height'].'px"':'').'></td>
			</tr>';
			
		}else if($f['type']=='2'){// многострочное текстовое (TEXTAREA)
			/*
			убрал следующий код, т.к. &amp;lt;div&amp;gt; превращался в &lt;div&gt; что в визуальном режиме делало div полноценным
			$data[ $f['name'] ] = str_replace('&amp;', '&', $data[ $f['name'] ]);
			*/
			$trs .= '<tr'.$tr.'>
				<td class="NoGreyB" colspan="2"><p><b>'.$f['title'].'</b></p></td>
			</tr><tr class="no">
				<td class="NoGreyB" colspan="2" style="padding: 5px 5px 5px 5px;"><textarea name="'.$f['name'].'"'.($f['wysiwyg']?' class="wysiwyg"':'').' style="height:'.$f['height'].'px">'.htmlspecialchars($data[ $f['name'] ]).'</textarea></td>
			</tr>';
			
		}else if($f['type']=='3' || $f['type']=='4'){// выбор одного из множества (SELECT) || выбор множества из множества (SELECT)
			
			if($GLOBALS['fields']->parent_id__field_id[ $field_id ]){// если имеются OPTION-ы
				
				$options = '';
				
				$value = $data[ $f['name'] ];
				
				$multiple = ($f['type']=='4')? array_flip(explode(',', $value)) : array();
				
				foreach($GLOBALS['fields']->parent_id__field_id[ $field_id ] as $id => $true){
					
					$a = $GLOBALS['fields']->field_id__data[ $id ];
					
					if(!$a['access']){ continue; }// если не показываем OPTION
					
					if($f['type']=='4'){
						$selected = isset($multiple[ $a['name'] ])? ' selected' : '';
					}else{
						$selected = ($value==$a['name'])? ' selected' : '';
					}
					
					$options .= '<option value="'.$a['name'].'"'.$selected.' title="'.$a['name'].'">'.$a['title'].'</option>';
					
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
				<td><input name="'.$f['name'].'" type="checkbox" '.(($data[ $f['name'] ] || (!$data[ $f['name'] ] && !$data['page_id'] && $f['value']=='checked'))?'checked':'').'></td>
			</tr>';
			
		}else if($f['type']=='6'){// выбор (RADIO)
			
			if($this->parent_id__field_id[ $field_id ]){// если имеются разные варианты
				
				$inputs = '';
				
				$d = array_flip($data[ $f['name'] ]);// меняем местами ключи и значения массива данных
				
				foreach($this->parent_id__field_id[ $field_id ] as $id => $true){
					
					$a = $this->field_id__data[ $id ];
					
					if(!$a['access']){ continue; }// если не показываем один из RADIO
					
					$checked = ($data[ $a['name'] ]==$a['name'])? ' checked' : '';
					
					$inputs .= '<label><input name="'.$f['name'].'['.$a['name'].']" type="radio"'.$checked.'> - '.$a['title'].'</label><br>';
					
				}
				
			}else{
				$inputs = '<input type="radio" name="'.$f['name'].'"'.($f['value']? ' value="'.$f['value'].'"':'').((($f['value']=='on' && !$data['page_id']) || $data[ $f['name'] ]=='on')?' checked':'').'>';
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
			
			$trs .= '<tr class="no"><td align="center" colspan="2"><input class="submit" type="submit" value="'.($data['page_id']?'Сохранить">&nbsp;&nbsp;<a href="'.$data['system_url'].'&delete_page_id='.$data['page_id'].'" onclick="return deletePage(this);" style="text-decoration: none; color: #FF0000;">Удалить</a>&nbsp;&nbsp;<a target="_blank" title="Предварительный осмотр" style="text-decoration: none;" href="/s/p?'.$data['page_id'].'">Посмотреть</a>&nbsp;<input onclick="copyMe(this);" class="submit" type="button" value="Копировать' : 'Создать').'"></td></tr>';
			
		}else{// type==0 - форма || type==7 - вариант выбора (OPTION или RADIO)
			
			$trs .= '';
			
		}
	}
	return $trs;
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head($headName? $headName : ( ($r['page_id']?'Редактирование':'Создание').' страницы'), $left, $right,'http://yapro.ru/documents/video/dobavlenie-redaktrirovanie-i-udalenie-stranic.html').'
<form action="'.$url.'" method="post" onsubmit="return CheckSubmitForm(this)" id="formPage">
<input type="hidden" name="reload_frame[parent_id]" value="'.$r['parent_id'].'">
<input type="hidden" name="reload_frame[name]" value="'.($r['name']? htmlspecialchars($r['name']) : 'нужно для reload_frame').'">
<input type="hidden" name="reload_frame[access]" value="'.$r['access'].'">
<input type="hidden" name="reload_frame[position]" value="'.$r['position'].'">
<div style="padding-top: 28px">
<table border="0" cellspacing="0" width="100%" id="TableEdite">
'.tr($r).'
</table>
</div>
'.$hidden_fields.'
</form>';
?>



<style type="text/css">
#TableEdite TD { border-bottom:1px solid #F0F0F0; height:25px; padding:0px 10px }/* padding:5px 10px*/
.NoGreyB { border-bottom: 0px !important; }
TEXTAREA { width: 100%; }
.NameTable { position:fixed; z-index:1; top:0px }
.NameTable TD.Image { padding:0 5px; white-space: nowrap }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; margin: 0 3px }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }

/* #TableEdite { margin-top:28px } */

.mceLast TD { height: 15px; }
.mceEditor TD { padding:0px !important; height: auto !important }
.mceToolbar TD { border: 0px !important; }
/*TD.mceStatusbar { border-top:1px solid #CCCCCC !important; border-bottom:1px solid #CCCCCC !important }*/
</style>



<?php echo Datepicker(); ?>



<script type="text/javascript">
var domain = "<?php echo $GLOBALS['SYSTEM']['config']['yapro_http_host']; ?>";// применяется при быстрой загрузке картинок
$(document).ready(function(){
	
	<?php echo $_GET['archive']? 'jAlert("Из-за возможного изменения полей в форме ввода, архивные данные могут быть отображены частично")':''; ?>
	
	$("#TableEdite tr").each(function(){ $("td:first", this).css("white-space", "nowrap"); });
	var td = $("#TableEdite tr:first td:eq(1)")[0];
	$(td).css("width", "100%").css("width", (td.offsetWidth - $(td).allPaddingsLR() )+"px");
	
	$("input[name=name]").focus();// ставим фокус на имя
	$("input:text").addClass("input_text");// изменяем CSS-правила
	$("input:checkbox").css("vertical-align","middle");// изменяем CSS-правила
	
	filesForm();// изменяем интерактивность файловых полей
	
	// если создание новой страницы - выставляем некоторые дефолтные настройки сохраненные в cookies
	var set = <?php echo (!$_GET['page_id'] && !$_POST)? 'true' : 'false'; ?>;
	<?php echo $user['save_selected']? 'onChangeToCookies("'.$GLOBALS['SYSTEM']['module_id'].'", ["article_tpl", "notice_tpl", "access"], set);' : ''; ?>
	
	// цветовое оформление поля "Доступа"
	var access_color = ["", "E1E1E1", "99FF99", "CCFFCC", "9999FF", "CCCCFF", "FF99FF", "FFCCFF", "FFB7FF", "ED8181"];
	$("select[name=access] option").each(function(i){
		if(access_color[ i ]){ $(this).css("background-color", "#"+access_color[ i ]); }
	});
	
	// цветовое оформление поля "URL-тип"
	var access_color = ["", "FFFFCC", "CFFFFF", "FFCCCC", "FFCCFF"];
	$("select[name=url_type] option").each(function(i){
		if(access_color[ i ]){ $(this).css("background-color", "#"+access_color[ i ]); }
	});
	
	<?php if($reload_frame){ echo 'reload_left_frame()'; } ?>// при необходимости перезагружаем левый фрейм
	
	<?php
	if($GLOBALS['message']){
		echo 'var e = $("td.Name"); var et = $(e).text(); $(e).text("'.$GLOBALS['message'].'").typewriter(et);';
	}
	?>
	var info_add = document.location.href.split("#info_add=");
	if(info_add[1]){
		var e = $("td.Name"); var et = $(e).text(); $(e).text("Запись добавлена").typewriter(et);
	}
	
	$("input[name=url]").attr("spellcheck","false");
});

var auto_save = false;

var textInformationChanged = false;// проверка изменения данных на странице

function wysiwygOnChange(){
	textInformationChanged = true;
}

window.onbeforeunload = function(e){
	if(textInformationChanged){
		return "ДАННЫЕ БЫЛИ ИЗМЕНЕНЫ, НО НЕ СОХРАНЕНЫ";
	}
};
$(document).ready(function(){
	$("TEXTAREA, INPUT, SELECT").change(function(){
		textInformationChanged = true;
	});
});

<?php if($user['auto_save']){ ?>
	
	var timerIDauto_save = setInterval(function(){
		if(textInformationChanged){
			var page_id_with_anchor = $("#formPage").attr("action").split("page_id=")[1];
			if(page_id_with_anchor){
				var page_id = page_id_with_anchor.split('#')[0];
				if(page_id){
					auto_save = true;
					$("#formPage :submit:first").trigger("click");
				}
			}
		}
	}, <?php echo ($user['auto_save']*60000); ?>);
	// в момент вызова jAlert можно очищать clearInterval(timerIDauto_save);
<?php } ?>



// изменяем вопрос подтверждения при удалении
confirm_message = "Так же будут удадлены все дочерние записи, если<br>таковые существуют. Подтверждаете удаление?";



function AjaxResult(form, msg){
	
	auto_save = false;
	$.fancybox.hideActivity();
	
	if(msg){// если кривой урл
		
		if(msg.substr(0,1)==","){// если это Ajax-сообщения
			eval('var a = {nothing:null'+msg+'}');
			if(a.error){
				var e = $("td.Name");
				var et = $(e).text();
				$(e).text(a.error).typewriter(et);
				return ;
			}
			if(a.reload_page && a.actionForm){ location.href = a.actionForm; }
			if(a.actionButtons){ $("#TableEdite :submit").firstParent("TD").html(a.actionButtons); }
			if(a.actionForm){ $("#formPage").attr("action", a.actionForm); }
			if(a.url){ $("[name=url]").val(a.url); }
			if(typeof(a.url_type)=='number'){// меняем выбор элемента
				var s = $("[name=url_type]");// находим наш элемент Select
				if(s[0]){
					var o = $(s)[0].selectedIndex;// определяем НОМЕР текущего выбранный элемента OPTION
					$("option:eq("+o+")", s).removeAttr('selected');// снимаем выбор с текущего выбранного элемента OPTION
					$("option:eq("+a.url_type+")", s).attr("selected","selected");// выбираем нужный элемент OPTION
				}
			}
			if(a.name){ $("[name=name], [name='reload_frame[name]']").val(a.name); }
			if(a.position){ $("[name=position], [name='reload_frame[position]']").val(a.position); }
			if(a.message){
				var e = $("td.Name");
				var et = $(e).text();
				$(e).text(a.message).typewriter(et);
				if(a.message=="Запись сохранена"){// обновляем информацию в полях reload_frame
					$("[name^=reload_frame]").each(function(){
						var ex = $(this).attr("name").split('[');
						var name = ex[1].substr(0, (ex[1].length - 1) );
						if(name){
							var e = $("[name="+name+"]");
							if(e.length>0 && typeof(e[0])=='object'){
								$(this).val( $(e).val() );
							}
						}
					});
				}
				// меняем href-ы активным кнопкам
				var ae = $(".NameTable A");
				var href = a.actionForm.split('page_id=');
				ae[1].href = href[0]+"parent_id="+$("[name=parent_id]").val();
				ae[2].href = href[0]+"parent_id="+href[1];
			}
			if(a.reload_frame){ $("#treeview20090605").remove(); selectAccess = true; reload_left_frame(); }
			
		}else{
			jAlert("Текущий URL для выбранного раздела, уже существет в базе данных. Пожалуйста, укажите другой URL", function(){
				$("[name=url]").val(msg).focus().Attention();
			});
		}
	}else{
		jAlert("Сервер не ответил или возникла ошибка программного характера в ф-ии AjaxResult. Попробуйте сохранить данные еще раз.");
	}
}



// проверка заполнения полей и переключение визуального редактора
var ajax_checked_url = false;
function CheckSubmitForm(form){
	
	textInformationChanged = false;
	
	var first_error_e = null;
	var error_message = "";
	
	fieldsValidCSS(form, true);
	
	if(error_message=="" && form.parent_id.value == "0" && form.template_0 && form.template_0.value == ""){
		first_error_e = form.template_0;
		error_message = "Основной документ должен иметь шаблон";
	}
	
	if(error_message==""){// проверка заполнения обязательных полей
		
		var check_must = <?php echo $fields-> javascript_names_must($GLOBALS['SYSTEM']['communications']->form_id); ?>
		
		for (var name in check_must){
			//+alert(name+' = ' + check_must[name]);
			
			error_message += fieldMust(form, name);
			
			if(error_message != "" && !first_error_e){// определяем первый элемент с неправильно введенными данными
				first_error_e = $('[name="'+ name +'"]', form).get(0);
			}
		}
	}
	
	if(error_message==""){// проверка валидации введеных данных
		
		var name_type = <?php echo $fields-> javascript_names_types($GLOBALS['SYSTEM']['communications']->form_id); ?>
		
		var types = ["x","w","n","d","p","e","t","i","t1","i1"];
		
		for (var name in name_type){
			
			var v = $('[name="'+ name +'"]',form).val();
			
			if(v!="0" && v!="" && v!=" "){
				
				error_message += fieldValid(form, name, types[ name_type[name] ]);
				
				if(error_message != "" && !first_error_e){// определяем первый элемент с неправильно введенными данными
					first_error_e = $('[name="'+ name +'"]', form).get(0);
				}
			}
		}
	}
	
	if(error_message != ""){
		
		jAlert(error_message, function(){
			$(first_error_e).Attention();
		});
		return false;
	}
	
	if(!auto_save){ $.fancybox.showActivity(); }
	
	if(typeof(tinyMCE)=='object'){
		$("TEXTAREA.wysiwyg",form).each(function(){
			if( $(this).css("display")=="none" ){
				/*h=h.replace(/<strong([^>]*)>/gi,'<b$1>');h=h.replace(/<\/strong>/gi,'</b>');*/
				$(this).val( tinyMCE.get( $(this).attr("name") ).save() );
			}
		});
	}
	if(typeof(withoutAjaxForm)=="undefined" || (typeof(withoutAjaxForm)!="undefined" && withoutAjaxForm==false)){
		$.post(form.action, formdata(form), function(msg){
			AjaxResult(form, msg);
		});
		return false;
	}
}

function treeviewSort(e){
	Cookie("system_sort_pages_right", e.value, function(){
		$("#treeview20090605").remove();
		selectAccess = true;
		$(".treeviewToogle").trigger("click");
	});
}
var allow_url_change = true;
$("[name=url]").change(function(){
	allow_url_change = false;
});
function copyMe(e){
	var form = $("#formPage")[0];
	$("input[name='reload_frame[name]']").val("нужно для reload_frame");
	if(allow_url_change){
		$("input[name=url]").val("");
	}
	setNewTime();
	form.action = form.action.replace("page_id","copy");
	CheckSubmitForm(form);
	$("input[name=name]").focus();
}
function deletePage(a){
	jConfirm(confirm_message, function(r) {
		if(r==true){
			textInformationChanged = false;
			document.location.href = a.href;
		}
	});
	return false;
}

wysiwygOnload();

var lockChecking = reloadPageAfterAlert = false;
var timer = setInterval(function(){
	if(!lockChecking){
		lockChecking = true;
		var action = $("#formPage").attr("action");
		var page_id_with_anchor = action.split('page_id=')[1];
		if(page_id_with_anchor){
			var page_id = page_id_with_anchor.split('#')[0];
			if(page_id){
				$.post(action, {"lock_page_id":page_id}, function(r){
					if(r && typeof(r)=="object" && r.message && typeof(r.message)=="string" && r.message!="" && r.message!=" "){
						jAlert(r.message, function(){
							lockChecking = false;
							reloadPageAfterAlert = true;
						});
					}else{
						if(reloadPageAfterAlert){
							document.location.href = document.location.href;
						}
						lockChecking = false;
					}
				},"json");
			}
		}
	}
},1234);
if($("#ADMIN_MODULE_ACCESS_W").val()!="1"){ window.clearInterval(timer); }

function setNewTime(){
	
	var Time = new Date();
	
	var hour   = Time.getHours();
	var minute = Time.getMinutes();
	var second = Time.getSeconds();
	
	var d = Time.getDate();
	var m = Time.getMonth()+1;
	var Y = Time.getFullYear();
	
	if( hour < 10 ){ hour = "0" + hour; }
	if( minute < 10 ){ minute = "0" + minute; }
	if( second < 10 ){ second = "0" + second; }
	
	if( d < 10 ){ d = "0" + d; }
	if( m < 10 ){ m = "0" + m; }
	
	$("input[name=time_created]").val("<?php echo date('d.m.Y H:i:s'); ?>");// d +"."+ m +"."+ Y +" "+ hour +":"+ minute +":"+ second
	
}
</script>
</body>
</html>
