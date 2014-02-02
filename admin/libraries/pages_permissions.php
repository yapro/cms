<?php
// Класс для работы с доступом к страницам сайта в системе администрирования
class pages_permissions {
	
	function select($permission_id=0){
		
		if($permission_id && is_numeric($permission_id)){
			$data = @mysql_fetch_assoc(mysql_query("SELECT name FROM ".P."pages_permissions WHERE permission_id='".$permission_id."'"));
			if($data['name']){
				$permissions = array();
				if($q = mysql_query("SELECT page_id, allow FROM ".P."pages_permissions_data WHERE permission_id='".$permission_id."'")){
					while($r =mysql_fetch_assoc($q)){
						$permissions[ $r['page_id'] ] = $r['allow'];
					}
				}
				return array('name'=>$data['name'], 'permissions'=>$permissions);
			}
		}
	}
	function insert($permission_id=''){// записываем данные доступа и получаем ID настроек
		
		if($_POST['name'] && $_POST['permissions']){
			mysql_("INSERT INTO ".P."pages_permissions VALUES ("._.(int)$permission_id._.", "._.$_POST['name']._.")");
			$permission_id = mysql_insert_id();
			foreach($_POST['permissions'] as $page_id => $allow){
				mysql_query("INSERT INTO ".P."pages_permissions_data VALUES (".$permission_id.", ".(int)$page_id.", ".(int)$allow.")");
			}
			return $permission_id;
		}
	}
	function delete($permission_id=0){// удаляем данные доступа и получаем подтверждение удаления
		
		if($permission_id && is_numeric($permission_id)){
			@mysql_query("DELETE FROM ".P."pages_permissions_data WHERE permission_id='".$permission_id."'");
			@mysql_query("DELETE FROM ".P."pages_permissions WHERE permission_id='".$permission_id."'");
			return true;//mysql_affected_rows()
		}
	}
	function update($permission_id=0){// записываем данные доступа и получаем ID настроек
		
		if($permission_id && is_numeric($permission_id) && $_POST['name'] && $_POST['permissions']){
			
			if($this-> delete($permission_id)){
				
				return $this-> insert($permission_id);
			}
		}
	}
}
$GLOBALS['pages_permissions'] = new pages_permissions;
?>
