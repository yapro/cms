<!--NoRedirect-->
<div id="footer" class="centerOuter">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="centerInner Site" style="color: #5F5E62; font-size: 10px; margin-top:15px; margin-bottom:15px;">
	    <tr>
	        <td style="text-align:center;">
При полном или частичном использовании материалов данного сайта, ссылка на сайт &quot;<a href="http://{~$_SERVER['HTTP_HOST']~}/" style="color: #5F5E62; font-size: 10px;">{~$_SERVER['HTTP_HOST_NAME']~}</a>&quot; обязательна как на источник информации.<br>
Импорт материалов и информации с сайта запрещен.<br>
Copyrights {~echo date('Y');.$~} &#169; {~$_SERVER['HTTP_HOST_NAME']~}
	        </td>
	    </tr>
	</table>
</div>

<!--NoRedirect-->
<div id="topMenu" class="centerOuter">
	<div class="centerInner Site">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		    <tr>
		        <td style="text-align:right; white-space: nowrap; padding: 10px 0;">
					<span id="SpellingStr">Ошибка? Выделите её мышкой и нажмите <span class="keyButton">Ctrl</span> + <span class="keyButton">Enter</span></span> <span>|</span> 
<?php
if($GLOBALS['SYSTEM']['user']['user_id']){
	
	if($GLOBALS['SYSTEM']['page_user_profile']){
		echo '<a href="'.$GLOBALS['SYSTEM']['user_links']['edit'].'" id="user_home_link" style="color:#FF0000">Редактировать</a>';
	}else{
		echo '<a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'" id="user_home_link">Личный кабинет</a>';
	}
	
	echo ' <span>|</span> 
	<a href="'.$GLOBALS['SYSTEM']['user_links']['out'].'" title="Завершить сеанс пользователя" id="user_logout_link">Выйти</a>';
	
}else{
	
	echo 'Привет, <b>Гость</b> <span>|</span> 
	<a href="'.$GLOBALS['SYSTEM']['user_links']['start'].'" title="Авторизоваться на сайте" id="user_auth_link">Войти</a> <span>|</span> 
	<a href="'.$GLOBALS['SYSTEM']['user_links']['registration'].'" title="Зарегистрироваться на сайте" id="user_reg_link">Регистрация</a>';
	
}
?>
					 <span>|</span> 
					<a href="<?php echo $GLOBALS['SYSTEM']['user_page']; ?>/bookmarks" id="pages_bookmarks">Закладки</a> <span>|</span> 
					<a href="<?php echo $GLOBALS['SYSTEM']['user_page']; ?>/rating" id="pages_favourites">Любимое</a> <span>|</span> 
					<a href="/s/map" style="font-size: 14px;" title="Карта сайта"><img src="/images/map_button.gif" alt="Карта сайта"></a>
		        </td>
		    </tr>
		</table>
		<div class="Search">
			<form action="/s/s<?php echo $GLOBALS['SYSTEM']['config']['yapro_page_ext']; ?>" method="post">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td class="SearchSubmit"><input type="submit" title="Найти" value="&nbsp;"></td>
						<td class="SearchField"><input type="text" name="search" value="<?php echo htmlAccess($_POST['search']); ?> "></td>
						<td class="SearchRight"></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>

<noindex>

<!-- Yandex.Metrika counter -->

</noindex>
<!--/NoRedirect-->
</body>
</html>