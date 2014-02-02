<?php
if($GLOBALS['SYSTEM']['user']['user_id'] && $GLOBALS['SYSTEM']['user']['user_id']===2){
	// скрипт добавляет в левый верхний угол сайта 2 активные кнопки для обновления CSS и JavaScript файлов
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/afterContent/css_js.php');
}

if($GLOBALS['SYSTEM']['user']['user_rank']==1){
	$GLOBALS['SYSTEM']['buttons'] .= '<a href="/admin/modules/pages/right.php?parent_id='.$this->id.'" target="_blank">Добавить</a><a href="/admin/modules/pages/right.php?page_id='.$this->id.'" target="_blank" onclick="if(window.opener){ var page_id =  window.opener.document.getElementById(\'ADMIN_PAGE_ID\').value; if(page_id=='.$this->id.'){ jAlert(\'Форма редактирования уже открыта.\'); return false; }}">Редактировать</a>';
}
if($GLOBALS['SYSTEM']['buttons']){
	// скрипт добавляет кнопки модерации в верхний левый угол экрана
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/afterContent/system_moderation_buttons.php');
}
?>