<?php
include_once('../../libraries/access.php');
//------------------------------------------------------------------------------------------------------------------------------------------
// удаляю
if(access('d')){
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
	//--------------------------------------------------
	$in_questions = $in_variants = '';
	//--------------------------------------------------
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
			
			$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена';
			
		}
	}
	//--------------------------------------------------
	if($_GET['delete_question_id'] && is_numeric($_GET['delete_question_id'])){
		
		$in_variants = in_variants($_GET['delete_question_id']);
		
		if($in_variants){
			mysql_query("DELETE FROM ".P."poll_variants WHERE variant_id IN (".substr($in_variants,0,-1).")");
			mysql_query("DELETE FROM ".P."poll_answers WHERE variant_id IN (".substr($in_variants,0,-1).")");
		}
		
		mysql_query("DELETE FROM ".P."poll_questions WHERE question_id='".$_GET['delete_question_id']."'");
		
		$_SERVER['SYSTEM']['MESSAGE'] = 'Запись удалена';
	}
	//--------------------------------------------------
	// cинхронизация информации: удалим лишние строки из таблиц poll_users и poll_answered_users
	if(($in_questions || $in_variants) && $q = mysql_query("SELECT DISTINCT poll_user_id FROM ".P."poll_answers")){
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
//------------------------------------------------------
if($_GET['copy_poll_id'] && is_numeric($_GET['copy_poll_id']) && access('w')){// создаю копию выбранной темы
	
	$r = @mysql_fetch_assoc(mysql_query("SELECT * FROM ".P."poll WHERE poll_id = ".$_GET['copy_poll_id']));
	
	if(mysql_("INSERT INTO ".P."poll SET 
	time_start = "._.$r['time_start']._.", 
	time_end = "._.$r['time_end']._.", 
	show_result = "._.$r['show_result']._.", 
	template = "._.$r['template']._.", 
	name = "._.$r['name']._.", 
	text_after_answer = "._.$r['text_after_answer']._."")){
		
		$poll_id = mysql_insert_id();
		
		if($poll_id && $q = mysql_query("SELECT * FROM ".P."poll_questions WHERE poll_id = ".$_GET['copy_poll_id'])){
			
			while($r = mysql_fetch_assoc($q)){
				
				if(mysql_("INSERT INTO ".P."poll_questions SET 
				poll_id = "._.$poll_id._.", 
				type = "._.$r['type']._.", 
				position = "._.$r['position']._.", 
				template = "._.$r['template']._.", 
				name = "._.$r['name']._."")){
					
					$question_id = mysql_insert_id();
					
					if($question_id && $q2 = mysql_query("SELECT * FROM ".P."poll_variants WHERE question_id = ".$r['question_id'])){
						
						while($v = mysql_fetch_assoc($q2)){
							
							if(!mysql_("INSERT INTO ".P."poll_variants SET 
							question_id = "._.$question_id._.", 
							position = "._.$v['position']._.", 
							type = "._.$v['type']._.", 
							name = "._.$v['name']._.", 
							template = "._.$v['template']._.", 
							addition = "._.$v['addition']._."")){
								
								echo error('5:'.__FILE__); exit;
								
							}
						}
					}else{
						echo error('4:'.__FILE__); exit;
					}
				}else{
					echo error('3:'.__FILE__); exit;
				}
			}
		}else{
			echo error('2:'.__FILE__); exit;
		}
		
		header("location: ".$_SERVER['SYSTEM']['URL_MODULE_PATH'], true, 301);exit;
		//$_SERVER['SYSTEM']['MESSAGE'] = 'Тема добавлена';
		
	}else{
		echo error('1:'.__FILE__); exit;
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------
$url = $_SERVER['PHP_SELF'];// общий урл

$dir = $_SERVER['SYSTEM']['URL_MODULE_DIR'];

$str = '';

if($p = mysql_query("SELECT * FROM ".P."poll ORDER BY time_start DESC, time_end DESC")){
	
	if($q = mysql_query("SELECT * FROM ".P."poll_questions ORDER BY position, question_id")){
		$poll_questions = array();
		while($r = mysql_fetch_assoc($q)){
			$poll_questions[ $r['poll_id'] ][] = $r;
		}
	}
	
	$poll_answered_users = array();
	if($q = mysql_query("SELECT poll_id, COUNT(*) FROM ".P."poll_answered_users GROUP BY poll_id")){
		while($r = mysql_fetch_row($q)){
			$poll_answered_users[ $r['0'] ] = $r['1'];
		}
	}
	
	$polls = array();
	
	while($r = mysql_fetch_assoc($p)){
		
		if($r['time_end']<time()){
			$polls['closed'][] = $r;
		}else{
			$polls['opened'][] = $r;
		}
	}
	if($polls){
		
		krsort($polls);
		
		foreach($polls as $k => $a){
			
			if($a){
				
				foreach($a as $r){
					
					$str .= '<tr class="yaproSortTR'.(($r['time_end']<time())?' pollClosed':'').'">
						<td><b>'.$r['poll_id'].') '.htmlspecialchars($r['name']).'</b> (тема доступна c '.date('d.m.Y', $r['time_start']).' по '.date('d.m.Y', $r['time_end']).')</td>
						<td class="edite" width="16"><a href="'.$dir.'poll.php?poll_id='.$r['poll_id'].'"></a></td>
						<td class="delete" width="16"><a href="'.$url.'?delete_poll_id='.$r['poll_id'].'" onclick="return checkDel(this);"></a></td>
					</tr>';
					if($poll_questions){
						foreach($poll_questions[ $r['poll_id'] ] as $q){
							
							$str .= '<tr>
								<td class="padding">'.htmlspecialchars($q['name']).'</a> ('.($q['type']?'Голосование':'Опрос').')</td>
								<td class="edite"><a href="'.$dir.'question_variants.php?poll_id='.$r['poll_id'].'&question_id='.$q['question_id'].'"></a></td>
								<td class="delete"><a href="'.$url.'?delete_question_id='.$q['question_id'].'" onclick="return checkDel(this);"></a></td>
							</tr>';
							
						}
					}
					$str .= '<tr><td colspan=3 class="padding">
						<a class="add_variants" href="'.$dir.'question_variants.php?poll_id='.$r['poll_id'].'">Добавить опрос / голосование »</a>&nbsp;&nbsp;&nbsp;
						'.($poll_answered_users[ $r['poll_id'] ]? '<a class="stat" href="'.$dir.'stat.php?poll_id='.$r['poll_id'].'">Результаты ответов »</a> ('.$poll_answered_users[ $r['poll_id'] ].')&nbsp;&nbsp;&nbsp;':'').'
						<a class="copy" href="'.$url.'?copy_poll_id='.$r['poll_id'].'">Создать копию темы »</a>
					</td></tr>';
					
				}
			}
		}
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------

$left[] = $dir.'poll.php'._.'images/elements/plus.gif'._.'Создать опрос/голосование';

echo $_SERVER['SITE_HEADER'].Head('Темы и заведенные в них опросы и голосования',$left,$right).
($str?'<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">'.$str.'</table>':'<p>Опросов/голосований в базе не обнаружено.</p>');
?>
<style type="text/css">
.NameTable { position:fixed; z-index:1; top:0px }
.NameTable TD.Image { padding:0 5px }
.NameTable TD.Image A IMG { border: 1px solid #CCCCCC; }
.NameTable TD.Image A:hover IMG { border: 1px solid #808080; }

.overflowTable { margin-top:28px }

.padding { padding-left:25px !important }
.pollClosed * { color: #777777; }
.edite, .delete { padding-left: 9px !important }
.edite A, .yaproSortTR .edite A:hover { background: url(images/elements/edit.gif) !important; display:block; height:16px; width:16px; }
.delete A, .yaproSortTR .delete A:hover { background: url(images/elements/del.gif) !important; display:block; height:16px; width:16px; }
.add_variants { background: url(images/elements/add.gif) no-repeat 0 3px; padding-left: 15px; }
.stat { background: url(images/elements/stat.gif) no-repeat; padding-left: 20px; }
.copy { background: url(images/elements/copyE.gif) no-repeat; padding-left: 20px; }
</style>
<script type="text/javascript">
$(document).ready(function(){
	
	$(".edite").attr("title","Редактировать");
	
	$(".delete").attr("title","Удалить");
	
	mouseMovementsClass("overflowTable");
	
	$(".copy").click(function(){
		checkDel(this, "Уверены в создании копии?");
		return false;
	});
});
</script>
</body>
</html>
