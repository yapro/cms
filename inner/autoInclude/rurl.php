<?php
if(!$GLOBALS['rurl']){// класс работы с кэш-данными
	class rurl {
		// возвращает русский урл для заданного name_id
		function rurl(){// log_fs('rurl');
			// хотя символы ?=&# мнемонизируются и обрабатываются браузерами, они нежелательны - http://ru.wikipedia.org/wiki/URL
			$s = '=!"#%&*,:;<>?[]^`{|}\'';// preg_replace('/[^-а-яa-z0-9]/sUui', ' ', - не годится т.к. удаляет символы подобные ёєії
			$sl = mb_strlen($s);
			$this->s = array();
			for($i = 0; $i < $sl; $i++){
				$x = mb_substr($s,$i,1);
				$this->s[] = $x;
			}
		}
		// возвращает русский урл для заданного name_id
		function url_ok($x=''){// log_fs('url_ok');
			foreach($this->s as $v){
				$x = str_replace($v, ' ', $x);
			}
			return $x;
		}
		function urlencode_($url=''){// log_fs('urlencode_');
			return str_replace('%2F','/', rawurlencode($url));
		}
		// возвращает rudir для заданного name и кэширует данные
		function rudir($name='', $page_id=0){// log_fs('rudir');
			if(!$name){
				error(__FILE__.' : rudir : '.$name);
				return ;
			}
			$md5_name = md5($name);
			$dir = @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cache/url/md5_name__dir/'.$md5_name);
			if(!$dir){
				log_($dir);
				/* удаление точки нужно чтобы не было неправильного расширения + урл выглядило красиво, а не: ._
				str_replace('"_', '"',
				str_replace('_"', '"',
				проблемные урл
				*/
				$dir = preg_replace('/[\_]{2,}/', '_',// BBC. 9/1
				str_replace(' ','_',// символ пробела %20 в опере и гугл-ссылках результатов поиска не отображется как обычный пробел - поэтому википедия меняет его на _
				str_replace('~','_',// боты гугла не умеет правильно обрабатывать урл (заходить по страницам) в которых есть символ ~
				str_replace('$','S',// не поддерживающиеся браузерами в результате чего в урл получается %24
				str_replace('+',' плюс ',// символ + не поддерживающиеся браузерами в результате чего в урл получается %B2
				str_replace('.','_',// символ точка не нужен т.к. у страниц получается раширения не поддерживающиеся браузерами и поисковиками
				str_replace('/','_',// мнемоник слэша не поддерживается апачем, а в чистом виде мешает построению дерева адреса
				$this-> url_ok($name))))))));
				/*-if($name=='3 dogs and a pony'){
					log_('rudir='.$dir);
				}*/
				$dir = $this-> tl('-',$dir);// убираем знак - в начале
				$dir = $this-> tr('-',$dir);// убираем знак - в конце
				$dir = $this-> tl('_',$dir);// убираем знак _ в начале
				$dir = $this-> tr('_',$dir);// убираем знак _ в конце
				$dir = $this-> tr('(',$dir);// убираем знак ( в конце
				if(!$dir){ $dir = $page_id; }// на случай если после исправлений урл перестает существовать
				if(!$dir){ error(__FILE__.' : rudir:name_url_error - name = '.$name.' - page_id = '.$page_id); $dir = 'name_url_error'; }
				write_($dir, $_SERVER['DOCUMENT_ROOT'].'/cache/url/md5_name__dir/'.$md5_name);
			}
			return $dir;
		}
		// функция удаляет лишние символы вначале строки tl('.', '...And Justice for All')
		function tl($x='',$s=''){
			if($s && $x){
				while(true){
					if(mb_substr($s,0,1)===$x){
						$s = mb_substr($s,1);
					}else{
						break;
					}
				}
			}
			return trim($s);
		}
		function tr($x='',$s=''){
			if($s && $x){
				while(true){
					if(mb_substr($s,-1)===$x){
						$s = mb_substr($s,0,-1);
					}else{
						break;
					}
				}
			}
			return trim($s);
		}
	}
	$GLOBALS['rurl'] = new rurl();
}
?>
