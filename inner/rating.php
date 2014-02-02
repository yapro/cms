<?php
if(!$rating_stars){ $rating_stars = 10; }// количество звездочек в рейтинге
if(!$rating_width){ $rating_width = 17; }// ширина 1 звездочки (в пикселах)
if(!$rating_left){ $rating_left = 'Оцени: '; }
if(!$rating_right){ $rating_right = ''; }//'по мнению ';
//--------------------------------------------------------------------------
$page_id = $this-> id;//$_POST['page_id']? (int)$_POST['page_id'] : $this-> id;
$rating = (int)$_POST['rating'];
//--------------------------------------------------------------------------
// проверяю в прошлом, голосование текущего IP за данный объект
$already = @mysql_fetch_assoc(mysql_('SELECT rating_id, rating FROM '.P.'pages_rating WHERE page_id = '.$page_id.' 
AND '.($GLOBALS['SYSTEM']['user']['user_id']?'user_id = '.$GLOBALS['SYSTEM']['user']['user_id']:'user_ip = INET_ATON('._.$_SERVER['REMOTE_ADDR']._.')')));
/*т.к. настроить проверку на основе индексов не получается из-за конфликта:
user_id - может быть много на одном IP
делаю проверку с помощью пхп и скл*/
//--------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $rating && $page_id){
	
	if($_POST['rating']<0 || $_POST['rating']>$rating_stars){
		
		echo '{"error":"Указана невозможная оценка рейтинга"}';
		exit;
		
	}else{
		
		if(mysql_(($already['rating_id']?'UPDATE':'INSERT INTO').' '.
		P.'pages_rating SET 
			page_id = '.$page_id.', 
			visitor_id = '.(int)$_SESSION['SYSTEM']['VISITOR_ID'].', 
			user_id = '.(int)$GLOBALS['SYSTEM']['user']['user_id'].', 
			user_ip = INET_ATON('._.$_SERVER['REMOTE_ADDR']._.'), 
			rating = '.$_POST['rating'].', 
			time_created = '.time().
		($already['rating_id']?' WHERE rating_id = '.$already['rating_id']:''))){
			
			$r = @mysql_fetch_assoc(mysql_query("SELECT SUM(rating) AS s, COUNT(user_ip) AS u 
			FROM ".P."pages_rating WHERE page_id = ".$page_id));
			
			if(!mysql_query("UPDATE ".P."pages SET 
			rating_sum = '".str_replace(',','.', $r['s'])."', rating_users = '".$r['u']."' WHERE page_id=".$page_id)){
				
				echo '"error":'.ajaxHTML(error('UPDATE pages IN '.__FILE__)).'}';
				exit;
				
			}
			echo '{"ok":"true"}';
			
		}else{
			echo '{"error":'.ajaxHTML(error('При добавлении/обновлении рейтинга: '.__FILE__)).'}';
		}
	}
	exit;
}else{
	
	// нахожу данные голосования
	if($this->fields['rating_sum'] && $this->fields['rating_users']){
		
		$data['s'] = $this-> pages[ $this->id ]['rating_sum'];
		$data['u'] = $this-> pages[ $this->id ]['rating_users'];
		
	}else{
		
		$data = @mysql_fetch_assoc(mysql_query('SELECT SUM(rating) AS s, COUNT(user_ip) AS u FROM '.P.'pages_rating WHERE page_id = '.$page_id));
		
	}
	
	$average = $data['s']/$data['u'];
	
	if($average){
		
		$one_degree = ($rating_stars * $rating_width) / 100;// один процент = (85пикселов/100%)
		$average_degree = ($average * 100)/$rating_stars;
		$width = $one_degree * $average_degree;
		
	}else{
		
		$width = 0;
		
	}
	
	$document = '<table class="TableRating" border="0" cellpadding="0" cellspacing="0"><tr>
	'.($rating_left? '<td class="rating_left">'.$rating_left.'</td>' : '').'
	<td class="rating_center"><form action="'.url().'" method="post">
		<input name="rating" type="hidden" value="">
		<ul class="rating" alt="'.$already['rating'].'">
			<li class="current" style="width:'.str_replace(',','.',$width).'px;" alt="'.$rating_stars.','.$rating_width.'">'.(int)$data['u'].','.(int)$data['s'].'</li>
		</ul>
	</form></td>
	'.(($data['u'])? '<td class="rating_right">'.$rating_right.(int)$data['u'].' чел.</td>' : '').'
	</table>';
}
?>