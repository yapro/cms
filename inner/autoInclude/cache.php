<?php
if(!$GLOBALS['system_cache']){// класс работы с кэш-данными
	class system_cache{
		function system_cache(){
			if($q=mysql_query("SELECT name, time_modified FROM ".P."cache")){
				while($r=mysql_fetch_array($q)){
					$this->name[$r['name']] = $r['time_modified'];
				}
			}
		}
		function file_($path='', $ext='', $prefix='', $pathroot=false){// возвращает имя кэш-файла
			if(!$path){
				$path = $_SERVER['DOCUMENT_ROOT'].'/cache'.$_SERVER['PHP_SELF'];
			}else if(!$pathroot){
				$ex = @explode($_SERVER['DOCUMENT_ROOT'], str_replace('\\','/',$path));
				$path = $_SERVER['DOCUMENT_ROOT'].'/cache'.($ex['1']? $ex['1'] : $ex['0']);
			}
			$filename = array_reverse(explode('/', $path));
			return str_replace($filename['0'], $prefix.$filename['0'], $path) . $ext;
		}
		function old($name=''){// узнаем, устарел ли кэш-файл с данными
			
			if(!$name){ $name = $this->file_(); }
			
			$time_modified = @filemtime($name);
			
			if(!$time_modified || $time_modified < $this->name[ $name ]){
				return true;// устарел
			}else{
				return false;// не устарел
			}
		}
		function update($name=''){// сохраняем время изменения кэша
			
			if(!$name){ $name = $this->file_(); }
			
			$time_modified = time();//(time()+1);
			
			if($this->name[ $name ]){
				mysql_("UPDATE ".P."cache SET time_modified="._.$time_modified._." "._."WHERE"._." name="._.$name._."");
			}else{
				mysql_("INSERT INTO ".P."cache VALUES ("._.$name._.", "._.$time_modified._.")");
			}
			$this->name[ $name ] = $time_modified;
		}
	}
	$GLOBALS['system_cache'] = new system_cache();
}
?>
