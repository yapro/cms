<?php
include_once('../../includes/system_access.php');

// отписка
if(access('w') && $_GET['desubscribe_id'] 
&& mysql_query("UPDATE ".P."subscribe SET subscribe='' WHERE id='".$_GET['desubscribe_id']."'")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Отписка выполнена';
}
// удаление
if(access('d') && $_GET['delete_id'] && mysql_query("DELETE FROM ".P."subscribe WHERE id='".(int)$_GET['delete_id']."'")){
	$_SERVER['SYSTEM']['MESSAGE'] = 'Отписка выполнена';
}

$url = $_SERVER['REQUEST_URI'];// общий урл
//------------------------------------------------------------------------------------------
$variants = array();
if($q = mysql_query("SELECT * FROM ".P."subscribe_variants ORDER BY position")){
	while($r=mysql_fetch_assoc($q)){
		$variants[ $r['variant_id'] ] = $r['name'];
	}
}
//------------------------------------------------------------------------------------------
$str = '';
if($variants && $q = mysql_query("SELECT id, user_id, variant_id, time_add FROM ".P."subscribe ORDER BY id DESC")){
	
	$users = $users_array = $variant_users = array();
	
	while($r=mysql_fetch_assoc($q)){
		$users_array[ $r['user_id'] ]++;
		$variant_users[ $r['variant_id'] ][] = $r;
	}
	
	if($GLOBALS['SYSTEM']['settings']['3'] && $users_array){
		
		$in_user_id = '';
		
		foreach($users_array as $user_id=>$true){
			if($user_id){ $in_user_id .= $user_id.','; }
		}
		
		if($in_user_id && $q = mysql_query("SELECT user_id, username, user_email FROM ".$GLOBALS['SYSTEM']['settings']['3']."users 
		WHERE username!='Anonymous' AND user_type IN (0,1) AND user_id IN(".substr($in_user_id,0,-1).")")){
			while($r=mysql_fetch_assoc($q)){
				$users[ $r['user_id'] ] = $r;
			}
		}
	}
	
	foreach($variants as $id=>$name){
		
		$str .= '<tr><td><p><b>'.htmlspecialchars($name).'</b></p></td><td>&nbsp;</td><td>&nbsp;</td></tr>';
		
		if($variant_users[ $id ]){
			
			foreach($variant_users[ $id ] as $r){
				
				$username = $users[ $r['user_id'] ]['username'];
				$user_email = $users[ $r['user_id'] ]['user_email'];
				
				$str .= '<tr>
					<td><p><a href="mailto:'.$user_email.'">'.htmlspecialchars($username).'</a> / '.htmlspecialchars($user_email).'</p></td>
					<td><p>'.date('d.m.Y H:i:s', $r['time_add']).'</p></td>
					<td><p align="center"><a href="'.$url.'&delete_id='.$r['id'].'" onclick="return checkDel();" title="Отписать"><img src="images/elements/del.gif"></a></p></td>
				</tr>';
			}
		}
	}
}

echo $_SERVER['SITE_HEADER'].Head('Подписавшиеся пользователи').($str?'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>E-mail</b></td>
		<td width="115"><b>Дата подписки</b></td>
		<td width="55"><b>Отписка</b></td>
	</tr>
	'.$str.'
</table>':'<p style="padding:10px">Для выбранного сайта подписавшиеся пользователи не найдены.</p>');
?>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
});
</script>
</body>
</html>
