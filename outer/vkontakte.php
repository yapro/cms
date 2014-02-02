<?php
// скрипт авторизации на сайте Вконтакте.ру
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

if(mb_substr($_SERVER['DOCUMENT_ROOT'], -1)=='/'){ $_SERVER['DOCUMENT_ROOT'] = mb_substr($_SERVER['DOCUMENT_ROOT'], 0, -1); }

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');

define ('APP_ID', 123456789);
define ('APP_SHARED_SECRET', '12345zxcvbn');

$vk = 'https://api.vkontakte.ru/';

if($_GET['code']){
	
	$f = file_get_contents($vk.'oauth/access_token?client_id='. APP_ID.'&client_secret='.APP_SHARED_SECRET.'&code='.$_GET['code']);
	
	if($f){
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/JSON.php');// подгружаю конфигурационный файл
		
		$json = new Services_JSON();
		$vk_a = $json-> decode($f);
		
		if($vk_a->access_token && $vk_a->user_id){
			
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/users.php');
			include_once($_SERVER['DOCUMENT_ROOT'].'/outer/cookies.php');
			include_once($_SERVER['DOCUMENT_ROOT'].'/inner/autoInclude/kino.php');// нужна ф-я bookmark_pages()
			
			$_POST['name'] = '';
			$_POST['email'] = $vk_a->user_id.'@vkontakte.ru';
			
			$f = file_get_contents($vk.'method/getProfiles?uid='.$vk_a->user_id.'&access_token='.$vk_a->access_token);
			//log_(print_r($f,1));
			if($f){// если данные получены
				
				$vk_a = $json-> decode($f);
				
				if($vk_a->response['0']->first_name){
					$_POST['name'] .= $vk_a->response['0']->first_name.' ';
				}
				
				if($vk_a->response['0']->last_name){
					$_POST['name'] .= $vk_a->response['0']->last_name.' ';
				}
				
			}else{
				
				header301('/s/vkontakte?a=4');
			}
			
			// проверяем пользователя по мылу в базе данных, и если что - добавляем юзера в базу
			
			// проверяем наличие мыла в базе
			$user_id = $GLOBALS['SYSTEM']['users']-> user_id_by_user_email($_POST['email']);
			
			if($user_id){// если пользователь НЕ впервые авторизовывается через вконтакт
				
				$GLOBALS['SYSTEM']['users']-> session_start($user_id, true);// заводим phpbb3 сессию пользователю
				
				include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/sync_cookies.php');
				
			}else{// если пользователь впервые авторизовывается через вконтакт
				
				$_POST['name'] = htmlAccess($_POST['name'], false);
				$user_regdate = time();
				$_POST['password'] = 'v'.mb_substr(md5($_POST['email']),0,3).mb_substr($user_regdate,-5);
				// автоматическое добавление пользователя и информации о нем (если возникает проблема - создает переменную $error с описанием)
				$_SERVER['HTTP_REFERER'] = 'http://'. $_SERVER['HTTP_HOST'];// меняем адрес чтобы в письме регистрации была нормальная ссылка
				include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/user_info.php');
				
			}
			
			header301($GLOBALS['SYSTEM']['user_links']['start']);// отправляем в личн. кабинет
			
		}else{
			header301('/s/vkontakte?a=3');
		}
		
	}else{
		header301('/s/vkontakte?a=2');
	}
	
}else{
	header301('/s/vkontakte?a=1');
}
?>
