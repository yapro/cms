<?php
/* Плагин опроса/голосования основан на IP пользователей.
переменные, которые можно использовать в шаблонах:
в шаблонах опроса/голосования:
	$POLLNAME - название опроса/голосования
	$QUESTIONS - блок вопросов
	$SUBMIT - кнопка отправки данных
	$RESULT - ссылка показа результатов стастистики
в шаблонах вопросов:
	$QUESTION - название вопроса
	$VARIANTS - блок вариантов ответов
в шаблонах вариантов ответов:
	$VARIANT - вариант ответа
	$FIELD - поле варианта ответа
*/

$SUBMIT = 'Голосовать';// надпись на кнопке отправки данных

$RESULT = 'Результаты';// текст ссылки просмотров результата

$js = '/js/poll.js';// путь к Javascript-файлу от корня сайта

$css = '/css/poll.css';// путь к CSS-файлу от корня сайта

//---------------------------------------------------------------------------------------------------------------------------------------------------
$poll_id = (int)$poll_id;// чтобы запустить опрос/голосование его ID должен быть объявлен

if($poll_id){
	
	$cache_file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-3).$poll_id.'.html');// узнаем имя кэш-файла
	
	if(!$GLOBALS['system_cache']-> old('system_poll') && is_file($cache_file)){// обрабатываем кеширование информации
		
		$document = @file_get_contents($cache_file);
		
	}else{
		
		include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/poll.php');// определяем данные опроса/голосования с ID - $poll_id
		
		if($GLOBALS['poll'][$poll_id]){// если данные найдены
			
			$p = $GLOBALS['poll'][$poll_id];// создаем краткую переменную для удобства программирования)
			
			// выводим форму заполнения
			
			$type_field = array();// определяем типы полей
			$type_field['0'] = '<input name="_NAME_"_VALUE_ type="radio" class="radio check"_ADDITION_>';
			$type_field['1'] = '<input name="_NAME_"_VALUE_ type="checkbox" class="checkbox check"_ADDITION_>';
			$type_field['2'] = '<div class="input"><div><input name="_NAME_" value="" type="text"_ADDITION_></div></div>';
			$type_field['3'] = '<div class="textarea"><div><textarea name="_NAME_"_ADDITION_></textarea></div></div>';
			
			$QUESTIONS = '';
			
			foreach($p['questions'] as $q){// строим список вопросов: question_id, type, name
				
				$VARIANTS = '';
				
				// строим список вариантов ответов: variant_id, question_id, position, type, name, template, addition
				foreach($p['variants'] as $v){
					
					if($v['question_id']!=$q['question_id']){ continue; }// исключаем вариант ответа не текущего вопроса
					
					// если голосование - все имена полей должны быть одинаковы (кроме text ИЛИ textarea, т.к. они более приоритетны чем radio и checkbox)
					if($q['type'] && $v['type']<2){
						
						$NAME = 'poll[radio]['.$q['question_id'].']';
						$VALUE = ' value="'.$v['variant_id'].'"';
						
					}else{// если опрос - все имена полей должны быть уникальными
						
						$NAME = 'poll['.$v['variant_id'].']';
						$VALUE = '';
					}
					
					$v['name'] = ($v['name'] && mb_substr($v['name'],0,1)=='~')? '<b>'.mb_substr($v['name'],1).'</b>' : $v['name'];
					
					$FIELD = str_replace('_NAME_', $NAME, 
					str_replace('_VALUE_', $VALUE, 
					str_replace('_ADDITION_', ' id="variant_'.$v['variant_id'].'"'.($v['addition']?' '.$v['addition']:''), 
					$type_field[ $v['type'] ])));
					
					$VARIANTS .= str_replace('$VARIANT', '<label for="variant_'.$v['variant_id'].'">'.$v['name'].'</label>', 
					str_replace('$FIELD', $FIELD, 
					$GLOBALS['poll']['templates'][ $v['template'] ]));
				}
				
				$q['name'] = ($q['name'] && mb_substr($q['name'],0,1)=='~')? '<b>'.mb_substr($q['name'],1).'</b>' : $q['name'];
				
				$QUESTION = ($q['name'] && substr($q['name'],0,1)!='.')? $q['name']:'';
				
				$QUESTIONS .= '<div class="'.($q['type']?'RADIO':'NORADIO').'" alt="'.$q['question_id'].'">';
				
				$QUESTIONS .= str_replace('$QUESTION', $QUESTION, str_replace('$VARIANTS', $VARIANTS, $GLOBALS['poll']['templates'][ $q['template'] ]));
				
				$QUESTIONS .= '</div>';// RADIO/NORADIO закрыли
			}
			
			$p['name'] = ($p['name'] && mb_substr($p['name'],0,1)=='~')? '<b>'.mb_substr($p['name'],1).'</b>' : $p['name'];
			
			$POLLNAME = ($p['name'] && substr($p['name'],0,1)!='.')? $p['name']:'';
			
			// кнопка отправки данных
			$SUBMIT = '<input type="submit" value="'.$SUBMIT.'" class="SUBMIT"><input name="poll_id" type="hidden" value="'.$poll_id.'">';
			
			// ссылка просмотров результата
			$RESULT = $p['show_result']?'<a href="/s/poll?p='.$poll_id.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'" class="RESULT">'.$RESULT.'</a>' : '';
			
			if(!$GLOBALS['poll']['js'] && is_file($_SERVER['DOCUMENT_ROOT'].$js)){
				$GLOBALS['poll']['js'] = true;
				$document .= '<script type="text/javascript" charset="utf-8" src="'.$js.'"></script>';
			}
			
			if(!$GLOBALS['poll']['css'] && is_file($_SERVER['DOCUMENT_ROOT'].$css)){
				$GLOBALS['poll']['css'] = true;
				$document .= '<link rel="stylesheet" type="text/css" charset="utf-8" href="'.$css.'">';
			}
			
			
			$document .= '<form method="post" action="/s/poll'.$GLOBALS['SYSTEM']['config']['yapro_page_ext'].'">'.
			str_replace('$POLLNAME', $POLLNAME,
			str_replace('$QUESTIONS', $QUESTIONS,
			str_replace('$SUBMIT', $SUBMIT,
			str_replace('$RESULT', $RESULT,
			$GLOBALS['poll']['templates'][ $p['template'] ])))).'</form>';
		}
		write_($document, $cache_file);
	}
}
?>
