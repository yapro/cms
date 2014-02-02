<?php
// библиотек нахождения данных определенного опроса/голосования

$poll_id = (int)$poll_id;// чтобы запустить опрос/голосование его ID должен быть объявлен

if($poll_id && !$GLOBALS['poll'][$poll_id]){
	
	// по ID находим данные опроса/голосования
	$poll = @mysql_fetch_assoc(mysql_('SELECT show_result, template, name, text_after_answer FROM '.P.'poll 
	WHERE poll_id='.$poll_id.($poll_time_all?'':' AND  time_start < '.time().' AND time_end > '.time())));
	
	if($poll['template'] && $q=mysql_query("SELECT question_id, type, template, name 
	FROM ".P."poll_questions WHERE poll_id='".$poll_id."' ORDER BY position")){// находим данные вопросов
		
		$in_question_id = '';
		$question_id__data = array();
		
		while($r=mysql_fetch_assoc($q)){
			$in_question_id .= $r['question_id'].',';
			$question_id__data[ $r['question_id'] ] = $r;
		}
		
		// находим данные вариантов ответов: variant_id, question_id, position, type, name, template, addition
		if($in_question_id && $q=mysql_query("SELECT * FROM ".P."poll_variants WHERE question_id IN (".substr($in_question_id,0,-1).") ORDER BY position")){
			
			$variant_id__data = array();
			
			while($r=mysql_fetch_assoc($q)){
				$variant_id__data[ $r['variant_id'] ] = $r;
			}
			
			if($variant_id__data){
				$GLOBALS['poll'][$poll_id] = $poll;
				$GLOBALS['poll'][$poll_id]['questions'] = $question_id__data;
				$GLOBALS['poll'][$poll_id]['variants'] = $variant_id__data;
			}
			
			if(!isset($GLOBALS['poll']['templates'])){// определяем содержание файлов шаблонов
				$GLOBALS['poll']['templates'] = array();
				$folder = $_SERVER['DOCUMENT_ROOT'].'/templates/poll/';
				if($folder && $dir = @dir($folder)){
					while($file = $dir->read()){
						if(is_file($folder.'/'.$file)){
							$GLOBALS['poll']['templates'][$file] = file_get_contents($folder.'/'.$file);
						}
					}
					$dir->close();
				}
			}
		}
	}
}
?>
