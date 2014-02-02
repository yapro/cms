<?php
// класс защищает пост-данные от всяческих XSS-атак (подключается например в файле /index.php)
class postAccess{
	function postAccess(&$a){
		if(is_array($a) && !empty($a)){
			foreach($a as $k=>$v){
				if(is_array($v) && !empty($a)){
					$this-> postAccess($a[$k]);
				}else{
					if(ini_get('magic_quotes_gpc')=='1'){ $v = str_replace("\'", "'", str_replace('\"', '"', $v)); }
					$a[$k] = $v;
				}
			}
		}
	}
}
$postAccess = new postAccess($_POST);
?>
