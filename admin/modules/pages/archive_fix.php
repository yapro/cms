<?php
/* запускать только 1 раз, перез запуском скрипта нужно создать таблицу в базе данных:
CREATE TABLE pages_archive (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`page_id` INT UNSIGNED NOT NULL DEFAULT '0',
`user_id` INT UNSIGNED NOT NULL DEFAULT '0',
`module_id` INT UNSIGNED NOT NULL DEFAULT '0',
`form_id` INT UNSIGNED NOT NULL DEFAULT '0',
`time_modified` INT UNSIGNED NOT NULL DEFAULT '0',
`pagename` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `page_id` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_bin;
*/
$GLOBALS['SYSTEM']['module_id'] = '1';
include_once('../../libraries/access.php');// проверка доступа к данному модулю
//---------------------------------------------------------------------------------------------------------------------------------------

ignore_user_abort('1');// включаем игнорирование отключения пользовательского браузера
set_time_limit(555);

function get_page_name_201105($file=''){
	include($file);
	return $r['name'];
}

$folder = dirname(__FILE__).'/archive';
if($folder && $dir = @dir($folder)){
	
	while($file = $dir->read()){
		
		if($file!="." && $file!=".." && !is_dir($folder.'/'.$file)){
			
			$ex = explode('.',$file);
			
			mysql_('INSERT IGNORE INTO '.P.'pages_archive SET 
			page_id = '._.$ex['1']._.',
			user_id = '._.$ex['2']._.',
			module_id = '._.$ex['3']._.',
			form_id = '._.$ex['4']._.',
			time_modified = '._.$ex['5']._.',
			pagename = '._.get_page_name_201105($folder.'/'.$file)._);
		}
	}
	$dir->close();
	
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].Head('Обновление архива');
?>
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="1"><b>Данные архива успешно обновлены</b></td>
	</tr>
</table>
</body>
</html>
