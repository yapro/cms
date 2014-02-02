<?php
include_once('../../libraries/access.php');

// удаляю
if(access('d')){
	
	//+include_once($_SERVER['DOCUMENT_ROOT'].'/inner/Functions/system_cache.php');// класс работы с кэшем
	//+$GLOBALS['system_cache']->update('system_poll');
	
	function in_questions($poll_id){
		$in_questions = '';
		if($poll_id && $q = mysql_query("SELECT question_id FROM ".P."poll_questions WHERE poll_id='".$poll_id."'")){
			while($r = mysql_fetch_assoc($q)){
				$in_questions .= $r['question_id'].',';
			}
		}
		return $in_questions;
	}
	function in_variants($in_questions){
		
		$in_variants = '';
		
		if($in_questions && substr($in_questions,-1)==','){ $in_questions = substr($in_questions,0,-1); }
		
		if($in_questions && $q = mysql_query("SELECT variant_id FROM ".P."poll_variants WHERE question_id IN (".$in_questions.")")){
			while($r = mysql_fetch_assoc($q)){
				$in_variants .= $r['variant_id'].',';
			}
		}
		return 	$in_variants;
	}
	//------------------------------------------------------------------------------------------------------------------------------------------
	if($_GET['delete_poll_id'] && is_numeric($_GET['delete_poll_id'])){
		
		$in_questions = in_questions($_GET['delete_poll_id']);
		
		if($in_questions){
			
			mysql_query("DELETE FROM ".P."poll_questions WHERE poll_id='".$_GET['delete_poll_id']."'");
			
			$in_variants = in_variants($in_questions);
			
			if($in_variants){
				mysql_query("DELETE FROM ".P."poll_variants WHERE variant_id IN (".substr($in_variants,0,-1).")");
				mysql_query("DELETE FROM ".P."poll_answers WHERE variant_id IN (".substr($in_variants,0,-1).")");
			}
			
		}
		if(mysql_query("DELETE FROM ".P."poll WHERE poll_id='".$_GET['delete_poll_id']."'")){
			mysql_query("DELETE FROM ".P."poll_answered_users WHERE poll_id='".$_GET['delete_poll_id']."'");
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------
	if($_GET['delete_question_id'] && is_numeric($_GET['delete_question_id'])){
		
		$in_variants = in_variants($_GET['delete_question_id']);
		
		if($in_variants){
			mysql_query("DELETE FROM ".P."poll_variants WHERE variant_id IN (".substr($in_variants,0,-1).")");
			mysql_query("DELETE FROM ".P."poll_answers WHERE variant_id IN (".substr($in_variants,0,-1).")");
		}
		
		mysql_query("DELETE FROM ".P."poll_questions WHERE question_id='".$_GET['delete_question_id']."'");
		
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена!';
	}
	//------------------------------------------------------------------------------------------------------------------------------------------
	// cинхронизация информации: удалим лишние строки из таблиц poll_users и poll_answered_users
	if($q = mysql_query("SELECT DISTINCT poll_user_id FROM ".P."poll_answers")){
		$in_poll_user_id = '';
		while($r = mysql_fetch_assoc($q)){
			$in_poll_user_id .= $r['poll_user_id'].',';
		}
		if($in_poll_user_id){
			mysql_query("DELETE FROM ".P."poll_users WHERE poll_user_id NOT IN (".substr($in_poll_user_id,0,-1).")");
			mysql_query("DELETE FROM ".P."poll_answered_users WHERE poll_user_id NOT IN (".substr($in_poll_user_id,0,-1).")");
		}
	}
}

$url = $_SERVER['PHP_SELF'];// общий урл

$str = '';
//-------------------------------------------------------------------------------------------------------------------------------------------
$poll_id = (int)$_GET['poll_id'];// чтобы запустить опрос/голосование его ID должен быть объявлен

$poll_time_all = true;
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/poll.php');

$p = $GLOBALS['poll'][ $poll_id ];// создаем краткую переменную для удобства программирования

if($p){// если разрешен просмор результатов опроса
	
	$in_variant_id = ''; $question_id__variant_id = $variant_id__answer = $question_id__answers = array();// предопределяем массивы
	
	foreach($p['questions'] as $q){// строим список вопросов: question_id, type, name
		
		// строим список вариантов ответов: variant_id, question_id, position, type, name, template, addition
		foreach($p['variants'] as $v){
			
			if($v['question_id']!=$q['question_id']){ continue; }// исключаем варианты ответа не текущего вопроса
			
			$in_variant_id .= $v['variant_id'].',';
		}
	}
	
	$all_poll_question_id_users = array();
	
	if($in_variant_id && $q=mysql_query("SELECT variant_id, answer, poll_user_id FROM ".P."poll_answers 
	WHERE variant_id IN (".substr($in_variant_id,0,-1).")")){// находим данные ответов
		
		while($r=mysql_fetch_assoc($q)){
			
			$question_id = $p['variants'][ $r['variant_id'] ]['question_id'];
			
			$question_id__variant_id[ $question_id ][ $r['variant_id'] ]++;
			
			$question_id__answers[ $question_id ]++;
			
			$variant_id__answer[ $r['variant_id'] ][ $r['poll_user_id'] ] = $r['answer'];
			
			$all_poll_question_id_users[ $question_id ][ $r['poll_user_id'] ]++;
			
			$all_poll_users_uniq[ $r['poll_user_id'] ]++;
		}
	}
	
	if($question_id__variant_id){// если посетители отвечали
		
		$poll_user_time = array();// находим участников и даты времени их ответов
		if($q=mysql_query("SELECT poll_user_id, time_created FROM ".P."poll_answered_users 
		WHERE poll_id = ".$poll_id." ORDER BY time_created")){
			
			while($r=mysql_fetch_assoc($q)){
				
				$poll_user_time[ $r['poll_user_id'] ] = $r['time_created'];
			}
		}
		
		foreach($p['variants'] as $variant_id => $v){// добавляем нулевой результат ответов для правильного отображения статистики
			
			$question_id = $p['variants'][ $variant_id ]['question_id'];
			
			if($question_id__variant_id[ $question_id ][ $variant_id ]){ continue; }// исключаем собранные данные из базы данных
			
			$question_id__variant_id[ $question_id ][ $variant_id ] = 0;
			
		}
		
		//$str .= '<div class="RESULTS">Результаты:</div>';
		
		$all_answers = 0;
		foreach($p['questions'] as $q){//question_id, type, template, name
			$all_answers += (int)$question_id__answers[ $q['question_id'] ];// всего ответов
		}
		
		$str .= ($p['name'] && substr($p['name'],0,1)!='.')? '<div class="POLLNAME">'.$p['name'].'</div>':'';
		
		$str .= '<div class="QUESTIONS">';// QUESTIONS открыли
		
		foreach($p['questions'] as $q){//question_id, type, template, name
			
			//$q['name'] = (substr($q['name'],0,1)=='.')? substr($q['name'],1) : $q['name'];
			
			$all = (int)$question_id__answers[ $q['question_id'] ];// всего ответов
			
			$str .= '<div class="QUESTION">'.$q['name'].'</div>';
			
			$str .= '<div class="VARIANTS">';// VARIANTS открыли
			
			$variants = $question_id__variant_id[ $q['question_id'] ];// находим варианты ответов
			
			arsort($variants);// сортируем варианты ответов по количеству ответов
			
			foreach($variants as $variant_id => $votes){
				
				$excel = '';
				
				$v = $p['variants'][ $variant_id ];//variant_id, question_id, position, type, name, template, addition
				
				$percents = str_replace(',','.', round( (100 * $votes)/$all, 2) );// ответов в процентах
				
				$str .= '<div class="VARIANT">'.$v['name'].' '.$percents.'% (голосов: '.$votes.') <img src="images/types/xls.gif" class="exelButton" title="Вставка в Excel" alt="excel'.$variant_id.'"></div>
				<div class="ANSWERS">';// ANSWERS открыли
				
				$str .= '<div class="ANSWERS_PERCENTS"><div style="width:'.$percents.'%"></div></div>';// процент голосов
				
				$str .= '<div class="ANSWERS_TEXT">';// ANSWERS_TEXT открыли
				
				if($v['type']>1){// text ИЛИ textarea
					
					$time__poll_user_id__answer = array();
					
					foreach($variant_id__answer[ $variant_id ] as $poll_user_id => $answer){// перечисляем ответы
						
						$time__poll_user_id__answer[ $poll_user_time[ $poll_user_id ] ][ $poll_user_id ] = trim(htmlspecialchars($answer));
						
					}
					ksort($time__poll_user_id__answer);
					
					foreach($time__poll_user_id__answer as $time => $r){// перечисляем ответы
						
						$time = date('Y.m.d', $time);
						
						foreach($r as $poll_user_id => $answer){
							
							$str .= '<div class="ANSWER"><div>'.$time.' участник <span class="upIdent up'.$poll_user_id.'" alt="up'.$poll_user_id.'">'.$poll_user_id.'</span> : '.$answer.'</div></div>';// процент голосов
							
							$excel .= $time."\t".$poll_user_id."\t".$answer."\n";
							
						}
					}
					
					//$str .= '<div class="ANSWERS_USERS">Время участия - участник : Ответ участника</div>';
					/*
					ksort($variant_id__answer[ $variant_id ]);
					
					foreach($variant_id__answer[ $variant_id ] as $poll_user_id => $answer){// перечисляем ответы
						
						$answer = trim(htmlspecialchars($answer));
						
						$str .= '<div class="ANSWER"><div>'.$poll_user_time[ $poll_user_id ].' участник '.$poll_user_id.' : '.$answer.'</div></div>';// процент голосов
						
						$excel .= $poll_user_time[ $poll_user_id ]."\t".$poll_user_id."\t".$answer."\n";
					}*/
				}else{
					
					$str .= '<div class="ANSWER"><div>участники: ';
					
					$variant_users = '';
					
					foreach($variant_id__answer[ $variant_id ] as $poll_user_id => $answer){// перечисляем идентификаторы участников
						
						$time = date('Y.m.d', $poll_user_time[ $poll_user_id ]);
						
						$variant_users .= '<span class="upIdent up'.$poll_user_id.'" alt="up'.$poll_user_id.'">'.$poll_user_id.'</span>, ';// идентификаторы участников
						
						$excel .= $time."\t".$poll_user_id."\n";
					}
					
					$str .= substr($variant_users,0,-2).'</div></div>';
					
				}
				$str .= '</div>';// ANSWERS_TEXT закрыли
				
				$str .= '</div>';// ANSWERS закрыли
				
				$str .= '<div id="excel'.$variant_id.'" class="Hidden"><textarea class="excel" onclick="this.select()">'.$excel.'</textarea></div>';// EXCEL-текстареа
			}
			
			$str .= '</div>';// VARIANTS закрыли
			
			$all_percents = str_replace(',','.', round( (100 * $all)/$all_answers, 2) );// ответов в процентах
			
			$question_id_users = count($all_poll_question_id_users[ $q['question_id'] ]);
			
			$all_poll_users_percents = str_replace(',','.', round( (100 * $question_id_users)/count($all_poll_users_uniq), 2) );// ответов в процентах
			
			$str .= '<div class="ALL">Ответов на '.($q['type']?'голосование':'опрос').': <SPAN>'.$all.' ('.$all_percents.'% от общего кол-ва голосов)</SPAN> &nbsp; Уникальных участников: <SPAN>'.$question_id_users.' ('.$all_poll_users_percents.'% от общего кол-ва уникальных участников)</SPAN></div>';
			
		}
		
		$str .= '</div>';// QUESTIONS закрыли
		$str .= '<div style="padding:10px 0;"><b>Всего ответов:</b> '.$all_answers.' &nbsp; <b>Всего уникальных участников:</b> '.count($all_poll_users_uniq).'</div>';
		
	}else{
		$str .= '<div>Результатов нет, т.к. в данной теме еще никто не участвовал.</div>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------------------
$left[] = $_SERVER['SYSTEM']['URL_MODULE_DIR'].'polls_questions.php'._.'images/elements/insert.png'._.'Вернуться в Опросы и голосования';

echo $_SERVER['SITE_HEADER'].Head('Результаты опросов/голосований',$left).'<div class="overflowTable">'.
($str? $str : '<p>В данном опросе/голосовании еще никто не участвовал.</p>').'</div>';
?>
<style type="text/css">
/*.NameTable { position:fixed; z-index:1; top:0px }
.NameTable td.Image { padding:0 7px }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }*/

.overflowTable { padding:5px 15px }
/*********************************/
.POLLNAME {/* название опроса/голосования */
	font-size: 14px;
	font-weight: bold;
}
.QUESTIONS {/* блок вопросов */
	border-bottom: 1px solid #CCCCCC;
	margin-top: 3px;
	padding-top: 2px;
}
.QUESTION {/* вопрос */
	padding-top: 5px;
	font-weight: bold;
}
.VARIANTS {/* варианты вопросов */
	padding-left: 15px;
}
.VARIANT {/* вариант */
	padding-top: 10px;
}
.ANSWER {/* Сообщения посетителей */
	padding-top: 3px;
	padding-left: 15px;
}
.ANSWER DIV {/* Сообщения посетителей */
	font-size: 11px;
	border-bottom: 1px dotted #DEDEDE;
}
.ANSWERS_PERCENTS {/* Полоса 100% процентов */
	background: #F0F0F0
}
.ANSWERS_PERCENTS DIV {/* Полоса процентов */
	background: #00AFF0 none repeat scroll 0%;
	height: 5px;
}
.QUESTIONS .ALL {/* Всего ответов */
	font-style:oblique;
	font-weight:bold;
	padding-bottom:15px;
	padding-left:15px;
	padding-top:10px;
}
.FIELD .check {/* инпуты типа радио и чекбокс */
	border: medium none;
	vertical-align: middle
}
.POLLFORM .VARIANTS {/* варианты вопросов */
	padding-left: 0px;
}
.POLLFORM .VARIANTS DIV {/* варианты вопросов */
	padding: 2px 0px;
}
.POLLFORM .VARIANTS DIV.textarea {
	margin:3px 12px 5px 0;
}
.POLLFORM .VARIANTS DIV TEXTAREA {
	background-color:#FFFFFF;
	border:1px solid #E2E2E2;
	color:#000000;
}
.POLLFORM .VARIANT {/* вариант */
	padding-top: 0px;
}
.excel {
	width: 700px;
	height: 500px;
}
.exelButton {
	vertical-align: middle;
	cursor: pointer;
	margin-left: 5px;
}
.upIdent {
	cursor:pointer;
}
</style>
<script type="text/javascript">
$(".exelButton").click(function(){
	
	$.fancybox( ""+ $( "#"+$(this).attr("alt") ).html(), {
		'margin' : 0,
		'speedIn' : 0,
		'speedOut' : 0,
		'titleShow' : false
	});
	setTimeout(function(){
		$.fancybox.resize();
		$("#fancybox-inner TEXTAREA").trigger("click");
	},567);
});
//--------------------------------------------------------
var documentClickStatPoll = null;

$(".upIdent").attr("title","Подсветить ответы пользователя").click(function(){
	
	$(".upIdent").css("background-color", "transparent");
	
	documentClickStatPoll = false;
	
	setTimeout(function(){
		
		documentClickStatPoll = true;
		
	},123);
	
	var uniq_class = $(this).attr("alt");
	
	$("."+uniq_class).css("background-color", "#FFFF99");
	
});

$(document).click(function(){
	
	if( documentClickStatPoll ){
		
		$(".upIdent").css("background-color", "transparent");
		
	}
});

</script>
</body>
</html>
