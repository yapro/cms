<?php
$GLOBALS['SYSTEM']['notSaveUrl'] = true;
include_once('../../libraries/access.php');

ignore_user_abort('1');// включаем игнорирование отключения браузера
set_time_limit(300);// разрешаем скрипту выполняться 5 минут

$dir_root = $_SERVER['DOCUMENT_ROOT'];
if($GLOBALS['SYSTEM']['user']['user_type']!=3){
	$dir_root .= '/uploads/users/'.$GLOBALS['SYSTEM']['user']['user_id'];
}

// карта сайта
class dirMap{
	function dirMap($curpath) {
		if($dir = @dir($curpath)){
			while ($file = $dir-> read()){
				if($file != "." && $file != ".." && is_dir($curpath.$file)){
					
					$this->treeview .= '<li id="'.md5($curpath.$file).'"><a alt="'.urlencode(str_replace($GLOBALS['dir_root'], '', $curpath.$file)).'/">'.htmlspecialchars($file).'</a>';
					
					if ($this-> is_subdir($curpath.$file)){// если есть поддиректории то продолжаем рекурсию
						$this->treeview  .= '<ul>';
						$this-> dirMap($curpath.$file."/");
						$this->treeview  .= '</ul>';
					}
					$this->treeview .= '</li>';
				}
			}
			$dir->close();
		}
	}
	// метод проверки поддиректории в директории
	function is_subdir($folder){
		if($dir = @dir($folder)){
			while($file = $dir->read()){
				if($file!= "." && $file!= ".." && is_dir($folder.'/'.$file)){
					return $file;
				}
			}
			$dir->close();
		}
		return false;
	}
}
$dirMap = new dirMap($dir_root.'/');

result($_SERVER['SITE_HEADER'].'
<ul id="treeview20090824">'.$dirMap->treeview.'</ul>
<div style="padding:5px 0; background-color:#ECECEC; text-align: center; cursor:pointer; margin-top:5px" id="sh_20111125">свернуть / развернуть</div>
<link href="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>
<style>
BODY { overflow: auto; }
.treeview li.file div.mark { background-position: 0px -16px; margin-right: 2px }
</style>
<script type="text/javascript">
function TreeviewLinks(ul){
	
	$("li", ul).each(function(){
		
		var a = $("a:first", this).click(function(){
			window.parent.parent.right_frame.location.href = "'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'right.php?path="+$(this).attr("alt"); return false;
		});
		
	});
}
$(document).ready(function(){
	
	$("#treeview20090824").yaproTreeview(true,"filesLeft","TreeviewLinks");
	
	$("#sh_20111125").click(function(){
		$(".explorer").trigger("click");
	});
	
});
</script>
</body>
</html>');
?>
