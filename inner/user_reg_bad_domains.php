<?php
if(!function_exists('user_reg_bad_domains')){
	function user_reg_bad_domains($email=''){
		if(!$email){ return ; }
		$m = explode('@',$email);
		if(!$m['1']){ return ; }
		$s = 'pnpbiz.com
shophall.net
theexitgroup.com
shotarou.com
zonedating.info
monctonlife.com
gothentai.com
myabccompany.info';
		$e = explode("\n",$s);
		foreach($e as $v){
			if($v == $m['1']){
				echo 'Извините, домен емэйла на который Вы регистрируетесь - заблокирован.<br>
				Для разблокировки свяжитесь с нами с помощью страницы Контакты.';
				exit;
			}
		}
	}
}
user_reg_bad_domains($email);
?>