<?php
// Плагин опроса/голосования, который собирает ответы посетителей (на основе IP или $GLOBALS['SYSTEM']['user']['user_id']). Плагин способен показывать результаты определенного опроса/голосования

$poll_answered_users_only_on_ip = true;// Если указать true - разрешать участие только 1 раз с 1 IP. Если указать false - фактически пользователь может принять участие 2 раза: 1 - как гость, 2 - как зарегистрированный пользователь

// текст, который получает пользователь после ответа на опрос/голосование (если этот текст не задан в админке, который является приоритетным)
$text_after_answer = 'Спасибо, Ваш ответ принят.';

$css = '/css/poll.css';// путь к CSS-файлу от корня сайта

//---------------------------------------------------------------------------------------------------------------------------------------------------

if($GLOBALS['SYSTEM']['page_url'][ $this->id ]['url_type']!='1'){ header404('1-'.__FILE__); }

if($_POST['poll_id']){
	
	$poll_id = $_POST['poll_id'];
	
}else{
	include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/folio.php');
	$poll_id = $folio;
}
$poll_id = (int)$poll_id;// чтобы запустить опрос/голосование его ID должен быть объявлен
//---------------------------------------------------------------------------------------------------------------------------------------------------
if($poll_id){
	
	include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/poll.php');
	
	$p = $GLOBALS['poll'][$poll_id];// создаем краткую переменную для удобства программирования
	
	if($p){// если данные poll_id найдены
		
		//-------------------------------------------------------------------------------------------------------------------------------------------
		
		if($_POST['poll'] && $_POST['poll_id']==$poll_id){// если пользователь ответил на опрос/голосование
			
			if(!ban()){// проверяем бан пользователя
				
				// находим ID пользователя - poll_user_id
				$poll_user_id = @mysql_fetch_row(mysql_("SELECT poll_user_id FROM ".P."poll_users
				WHERE user_ip = INET_ATON("._.$_SERVER['REMOTE_ADDR']._.") 
				".($poll_answered_users_only_on_ip? '' : "AND user_id = "._.$GLOBALS['SYSTEM']['user']['user_id']._."")." LIMIT 1"));
				
				if(!$poll_user_id['0']){// если пользователь не определен - добавляем пользователя
					
					if(mysql_("INSERT INTO ".P."poll_users VALUES(
					"._._.", "._.$GLOBALS['SYSTEM']['user']['user_id']._.", INET_ATON("._.$_SERVER['REMOTE_ADDR']._."))")){
						
						$poll_user_id['0'] = mysql_insert_id();
						
					}else{
						echo error('INSERT INTO poll_users IN '.__FILE__); exit;
					}
				}
				
				// делаем проверку на повторное голосование текущего посетителя, за текущий poll_id, текущего сайта
				$check_answered = @mysql_fetch_row(mysql_("SELECT poll_id FROM ".P."poll_answered_users
				WHERE poll_id = "._.$poll_id._." AND poll_user_id = "._.$poll_user_id['0']._." LIMIT 1"));
				
				if(!$check_answered['0']){// если посетитель еще не отвечал в данном опросе
					
					$insert = '';
					
					foreach($_POST['poll'] as $variant_id=>$answer){
						
						if(!$answer){ continue; }
						
						if($variant_id=='radio'){// у данного типа могут быть два вид ответа (выбрано или нет)
							
							foreach($answer as $k){
								
								if(!$p['variants'][$k]){ continue; }// если такой вариант ответа не заведен
								
								$insert .= "(
								"._.$k._.",
								"._.$poll_user_id['0']._.",
								"._.'1'._."
								),";
							}
							
						}else{
							
							if(!$p['variants'][$variant_id]){ continue; }// если такой вариант ответа не заведен
							
							$insert .= "(
							"._.$variant_id._.",
							"._.$poll_user_id['0']._.",
							"._.(($answer=='on')? 1 : $answer)._."
							),";
							
						}
						
					}
					
					if($insert){// добавляем ответы участника голосования
						
						if(mysql_("INSERT INTO ".P."poll_answers (variant_id, poll_user_id, answer) 
						VALUES ".substr($insert,0,-1)."")){
							
							// делаем отметку о том, что данный посетитель (poll_user_id) участвовав в текущем poll_id
							if(!mysql_("INSERT INTO ".P."poll_answered_users VALUES(
							"._.$poll_id._.", "._.$poll_user_id['0']._.", "._.time()._.")")){
								
								echo error('INSERT INTO poll_answered_users IN '.__FILE__); exit;
								
							}
							
							$document = $p['text_after_answer']? $p['text_after_answer'] : $text_after_answer;// выводим сообщение
							
						}else{
							$document = error('INSERT INTO poll_answers '.__FILE__);
						}
					}else{
						$document = 'Вы не указали ответ.';
					}
					
				}else{
					$document = 'Вы уже участвовали в данной теме.';
				}
			}else{
				$document = 'Вы заблокированы администратором сайта.';
			}
			if($_POST['ajax']){ echo strip_tags(str_replace('<br />', " \n", $document)); exit; }
		}
		//-------------------------------------------------------------------------------------------------------------------------------------------
		if($p['show_result']){// если разрешен просмор результатов опроса
			
			$in_variant_id = ''; $question_id__variant_id = $variant_id__answer = $question_id__answers = array();// предопределяем массивы
			
			foreach($p['questions'] as $q){// строим список вопросов: question_id, type, name
				
				// строим список вариантов ответов: variant_id, question_id, position, type, name, template, addition
				foreach($p['variants'] as $v){
					
					if($v['question_id']!=$q['question_id']){ continue; }// исключаем варианты ответа не текущего вопроса
					
					$in_variant_id .= $v['variant_id'].',';
				}
			}
			
			if($in_variant_id && $q=mysql_query("SELECT variant_id, answer FROM ".P."poll_answers 
			WHERE variant_id IN (".substr($in_variant_id,0,-1).")")){// находим данные ответов
				
				while($r=mysql_fetch_assoc($q)){
					
					$question_id = $p['variants'][ $r['variant_id'] ]['question_id'];
					
					$question_id__variant_id[ $question_id ][ $r['variant_id'] ]++;
					
					$question_id__answers[ $question_id ]++;
					
					$variant_id__answer[ $r['variant_id'] ][] = $r['answer'];
				}
			}
			
			if($question_id__variant_id){// если посетители отвечали
				
				if(!$GLOBALS['poll']['css'] && is_file($_SERVER['DOCUMENT_ROOT'].$css)){
					$GLOBALS['poll']['css'] = true;
					$document .= '<link rel="stylesheet" type="text/css" charset="utf-8" href="'.$css.'">';
				}
				
				foreach($p['variants'] as $variant_id => $v){// добавляем нулевой результат ответов для правильного отображения статистики
					
					$question_id = $p['variants'][ $variant_id ]['question_id'];
					
					if($question_id__variant_id[ $question_id ][ $variant_id ]){ continue; }// исключаем собранные данные из базы данных
					
					$question_id__variant_id[ $question_id ][ $variant_id ] = 0;
					
				}
				
				//$document .= '<div class="RESULTS">Результаты:</div>';
				
				$v['name'] = ($p['name'] && mb_substr($p['name'],0,1)=='~')? '<b>'.mb_substr($p['name'],1).'</b>' : $p['name'];
				
				$document .= ($p['name'] && substr($p['name'],0,1)!='.')? '<div class="POLLNAME">'.$p['name'].'</div>':'';
				
				$document .= '<div class="QUESTIONS">';// QUESTIONS открыли
				
				foreach($p['questions'] as $q){//question_id, type, template, name
					
					$q['name'] = ($q['name'] && mb_substr($q['name'],0,1)=='~')? '<b>'.mb_substr($q['name'],1).'</b>' : $q['name'];
					
					$document .= ($q['name'] && substr($q['name'],0,1)!='.')? '<div class="QUESTION">'.$q['name'].'</div>' : '';
					
					$document .= '<div class="VARIANTS">';// VARIANTS открыли
					
					$variants = $question_id__variant_id[ $q['question_id'] ];// находим варианты ответов
					
					arsort($variants);// сортируем варианты ответов по количеству ответов
					
					$all = (int)$question_id__answers[ $q['question_id'] ];// всего ответов
					
					foreach($variants as $variant_id => $votes){
						
						$v = $p['variants'][ $variant_id ];//variant_id, question_id, position, type, name, template, addition
						
						$percents = str_replace(',','.', round( (100 * $votes)/$all, 2) );// ответов в процентах
						
						$document .= '<div class="VARIANT">'.$v['name'].' '.$percents.'% (голосов: '.$votes.')</div>
						<div class="ANSWERS">';// ANSWERS открыли
						
						$document .= '<div class="ANSWERS_PERCENTS"><div style="width:'.$percents.'%"></div></div>';// процент голосов
						
						if($v['type']>1){// text ИЛИ textarea
							
							//$document .= '<div class="ANSWERS_USERS">Ответы участников:</div>';
							
							$document .= '<div class="ANSWERS_TEXT">';// ANSWERS_TEXT открыли
							
							foreach($variant_id__answer[ $variant_id ] as $answer){// перечисляем ответы
								
								$document .= '<div class="ANSWER"><div>'.htmlAccess($answer).'</div></div>';// ответы участников
								
							}
							
							$document .= '</div>';// ANSWERS_TEXT закрыли
						}
						
						$document .= '</div>';// ANSWERS закрыли
					}
					
					$document .= '</div>';// VARIANTS закрыли
					
					$document .= '<div class="ALL">Ответов на '.($q['type']?'голосование':'опрос').': <SPAN>'.$all.'</SPAN></div>';
					
				}
				
				$document .= '</div>';// QUESTIONS закрыли
				
			}else{
				$document .= '<div>Результатов нет, т.к. в данной теме еще никто не участвовал.</div>';
			}
		}
		
	}else{
		header404('Data is not found');
	}
}
?>
