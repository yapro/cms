<?php
// скрипт добавляет кнопки модерации в верхний левый угол экрана
$this->document = preg_replace("/<\/body>/i", '<div id="system_moderation_buttons">'.$GLOBALS['SYSTEM']['buttons'].'<img src="/admin/images/elements/del.gif" onclick="$(this).parent().hide();" title="Скрыть панель управления"></div>
<style>
#system_moderation_buttons {
	position: fixed;
	*position: absolute;
	z-index:7777777;
	left: 0%;
	top: 0%;
	background-color: #FFFF99;
}
#system_moderation_buttons A {
	padding: 0 10px;
	color: #FF0000;
	font: 12px Arial;
	border:none;
}
</style></body>', $this->document);
?>