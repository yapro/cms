<?php
// скрипт показывает информацию о пользователе + несколько закладок + несколько любимых И дает ссылки на просмотр полных списков

$e = explode('?', $_SERVER['REQUEST_URI']);
if($GLOBALS['SYSTEM']['config']['yapro_page_ext']){
	$e = explode($GLOBALS['SYSTEM']['config']['yapro_page_ext'], $e['0']);
}
$url_page_20111125 = $e['0'];
$e = explode('/', $e['0']);
//$document = print_r($e,1);

if(!isset($e['2']) && !$_GET){// /i
	
	
	if($GLOBALS['SYSTEM']['user']['user_id']){// если зарегистрирован - отображаем возможность отредактировать свои данные
		
		$h1 = 'Редактирование личных данных';
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_edit.php');
		
	}else{// посетитель - информацию о том как класнно быть пользователем
		
		$h1 = 'Личный кабинет';
		
		$document .= '<p><a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'">Авторизуйтесь</a> или <a href="'.$GLOBALS['SYSTEM']['user_links']['registration'].'">зарегистрируйтесь</a>, и Вы сможете воспользоваться всем функционалом сайта.</p>
		<p>Если не зарегистрированы, то можете потерять закладки и любимое, а если авторизованы - можете просматривать закладки с любого компьютера.</p>';
	}
	
}else if($e['2'] && !isset($e['3']) && !$_GET){// /i/человек
	
	//несколько фильмов из закладок + несколько видео из любимых И дает ссылки на просмотр полных списков
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_outer.php');
	
}else if($e['3'] && !isset($e['4'])){// /i/коля/любимое|избранное
	
	$dir = rawurldecode($e['3']);
	
	$subdomain = rawurldecode($e['2']);
	
	if($dir!=='rating' && $dir!=='bookmarks'){
		header404('1: '.__FILE__);
	}
	
	//просмотр полных списков - PAGE_USER_ID или PAGE_VISITOR_ID
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_detect.php');
	
	if($dir=='rating'){
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_pages_rating_inner.php');
		
	}else{
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/user_bookmarks_inner.php');
	}
	
}else{
	header404('2: '.__FILE__);
}
?>
{~templates/html/header.php~}
<style>

</style>
<div class="centerOuter">
	<div class="centerInner Site">
		<div id="body" class="colmask leftmenu">
			<div class="colleft">
				<div class="col1">
					<!-- Column 1 start -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<?php echo $h1?'<tr>
							<td valign="top" style="padding-bottom:10px;">
								<h1 class="box-head">'.$h1.'</h1>
							</td>
						</tr>':''; ?>
						<tr>
							<td valign="top" id="fullContent" style="padding:0px">
								<!--MetaData--><?php echo $document; ?><!--/MetaData-->
							</td>
						</tr>
						<tr>
							<td valign="top" id="td_listing">
								{~$GLOBALS['system_listing']~}
							</td>
						</tr>
					</table>
					<!-- Column 1 end -->
				</div>
				{~templates/html/left.php~}
			</div>
		</div>
	</div>
</div>
{~templates/html/bottom.php~}