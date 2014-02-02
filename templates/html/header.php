<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-ru" xml:lang="ru-ru">
<head>
	<title>[~title~]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Language" content="ru">
	<meta name="keywords" content="[~keywords~]">
	<meta name="description" content="[~description~]">
	<meta name="document-state" content="dynamic">
	<meta name="resource-type" content="document">
	<meta name="rating" content="General">
	<meta name="audience" content="all">
	<meta name="author" content="SMSdesign.com.ua : Lebedenko N.N. and Milinevskyy S.A.">
	<meta name="cmsmagazine" content="8b08a0aba856e5de7a2cdfe289358291" />
	<meta name="cmsmagazine" content="9371502b8645a0fd16d095fb68f9dcf4" />
	<link rel="SHORTCUT ICON" href="/favicon.ico">
<?php
/* плагин выводит ссылки:
на RSS-ленты страницы/раздела и родительского раздела (если таковой есть),
на комментарии страницы/раздела и родительского раздела (если таковой есть)
только для страниц которые не скрыты)
+ превью-изображение (если оно есть)
*/
if($GLOBALS['SYSTEM']['noindex_nofollow']){
	echo '<meta name="robots" content="noindex, nofollow" />';
}

$src = $this-> data('img');
if($src){
	echo '<link rel="image_src" href="http://'. $_SERVER['HTTP_HOST'].$src.'"/>';
}
$f = @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cache/css_js/css');
if($f){
	echo '<link rel="stylesheet" type="text/css" charset="utf-8" href="'.$f.'">';
}
$f = @file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cache/css_js/js');
if($f){
	echo '<script language="Javascript" type="text/javascript" charset="utf-8" src="'.$f.'"></script>';
}
?>
</head>
<body><noindex>
<script language="javascript">
var HTTP_HOST = "<?php echo $_SERVER['HTTP_HOST']; ?>";
var HTTP_HOST_NAME = "<?php echo $_SERVER['HTTP_HOST_NAME']; ?>";
var BROWSER_NAME_SHORT = "<?php echo $GLOBALS['browser']->short; ?>";
var BROWSER_VERSION_SHORT = "<?php echo $GLOBALS['browser']->version; ?>";
</script>
</noindex>