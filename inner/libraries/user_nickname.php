<?php
// Проверка на существование данного username и получение $nickname['id']
$nickname['id'] = 0;

if($_POST['email']){
	$ex = explode('@',$_POST['email']);
}else{
	$ex = array(0=>'');
}

$nick = trim( $_POST['username']? $_POST['username'] : ($_POST['name']? $_POST['name'] : $ex['0']) );

if($nick){
	
	$ex = explode(' ', $nick);
	$nick = '';
	foreach($ex as $v){// переводим имя в Имя
		$nick .= strtolower_(mb_substr($v,0,1),'up').mb_substr( strtolower_($v), 1).' ';
	}
	$nick = trim($nick);
	
	if($nick && 
	!mb_stristr($nick, 'admin') && // eng
	!mb_stristr($nick, 'админ') && // рус
	!mb_stristr($nick, 'аdmin') && // рус (а) + eng (dmin)
	!mb_stristr($nick, 'aдмин')){  // eng (a) + рус (дмин)
		
		$nickname = @mysql_fetch_assoc(mysql_("SELECT id FROM ".N."nicknames WHERE nickname="._.$nick._.""));
		if(!$nickname['id']){
			
			if(!mysql_("INSERT INTO ".N."nicknames VALUES ("._._.","._.$nick._.")")){
				$error = error(__FILE__.' : Невозможно добавить nickname');
			}else{
				$nickname['id'] = mysql_insert_id();
			}
		}
		setcookie('username', addslashes($nick), (time() + 622080000), "/");
	}else{
		$nick = '';
	}
}
?>
