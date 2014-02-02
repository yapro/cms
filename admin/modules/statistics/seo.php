<?php
include_once('../../libraries/access.php');

echo $_SERVER['SITE_HEADER'].Head('Продвижение сайта и различные проверки').'
<p style="padding:5px">
	<b>Сайт:</b>&nbsp;
	<input id="url" type="text" value="'.htmlspecialchars($GLOBALS['SYSTEM']['config']['yapro_http_host']).'" onFocus="foc(this)" onBlur="blu(this)" class="input_text" style="width: 250px;">
';
?>
	&nbsp;
	<b>проверить на</b> &nbsp;
	<a href="http://www.bname.ru/analysis/" onclick="return seoHref(this.href)">bname.ru</a>, &nbsp;
	<a href="http://www.be1.ru/stat/?url=http://" onclick="return seoHref(this.href, '/')">be1.ru</a>, &nbsp;
	<a href="http://www.setlinks.ru/stat/?url=http://" onclick="return seoHref(this.href)">setlinks.ru</a>, &nbsp;
	<a href="http://seo-rus.com/analysis/?url=" onclick="return seoHref(this.href)">seo-rus.com</a>, &nbsp;
	<a href="http://2ip.ru/guess-hosting/?domain=" onclick="return seoHref(this.href)">2ip.ru</a>
</p>
<p style="padding: 2px 5px 7px">
	<b>проверить</b> &nbsp;
	<a href="http://validator.w3.org/check?uri=http://" onclick="return seoHref(this.href)">валидацию</a>, &nbsp;
	<a href="http://webmaster.yandex.ru/wmconsole/public_robots_txt.xml?hostname=" onclick="return seoHref(this.href)">robots.txt</a>, &nbsp;
	<a href="http://wservices.ru/whoish.php" target="_blank">историю домена</a>
</p>
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
		<tr class="yaproSortTR">
			<td><b>Поисковые системы:</b></td>
			<td width="80"><b>Регистрация</b></td>
			<td width="80"><b>Индексация</b></td>
		</tr>
		<tr>
			<td><p>Яндекс</p></td>
			<td><p><a href="http://webmaster.yandex.ru/site/add.xml?hostname=http%3A%2F%2F" onclick="return seoHref(this.href, '&do=add')">Выполнить»</a></p></td>
			<td><p><a href="http://www.yandex.ru/yandsearch?how=tm&serverurl=" onclick="return seoHref(this.href)">Проверить»</a></p></td>
		</tr>
		<tr>
			<td><p>Google</p></td>
			<td><p><a href="http://www.google.com/addurl?q=http%3A%2F%2F" onclick="return seoHref(this.href, '%2F&hl=&dqq=')">Выполнить»</a></p></td>
			<td><p><a href="http://www.google.com/search?hl=ru&q=site%3A" onclick="return seoHref(this.href)">Проверить»</a></p></td>
		</tr>
		<tr>
			<td><p>Rambler</p></td>
			<td><p><a href="http://top100.rambler.ru/user/login" target="_blank">Выполнить»</a></p></td>
			<td><p><a href="http://search.rambler.ru/srch?oe=1251&and=2&news=2&sort=1&filter=" onclick="return seoHref(this.href)">Проверить»</a></p></td>
		</tr>
		<tr>
			<td><p>Yahoo</p></td>
			<td><p><a href="http://siteexplorer.search.yahoo.com/submit" target="_blank">Выполнить»</a></p></td>
			<td><p><a href="http://siteexplorer.search.yahoo.com/search?bwm=p&bwms=p&fr2=seo-rd-se&p=http%3A%2F%2F" onclick="return seoHref(this.href)">Проверить»</a></p></td>
		</tr>
		<tr>
			<td><p>MSN</p></td>
			<td><p><a href="http://search.msn.com/docs/submit.aspx" target="_blank">Выполнить»</a></p></td>
			<td><p>&nbsp;</p></td>
		</tr>
		<tr>
			<td><p>Mеta</p></td>
			<td><p><a href="http://meta.ua/webmaster/addurl.asp" target="_blank">Выполнить»</a></p></td>
			<td><p>&nbsp;</p></td>
		</tr>
</table>

<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td><b>Яндекс:</b></td>
	</tr>
	<tr>
		<td><a href="http://help.yandex.ru/webmaster/" target="_blank">Кабинет Вебмастера»</a> (<a href="http://webmaster.yandex.ru/" target="_blank">управление сайтом</a>)</td>
	</tr>
	<tr>
		<td><a href="http://metrika.yandex.ru" target="_blank">Метрика»</a> (статистика)</td>
	</tr>
	<tr>
		<td><a href="http://help.yandex.ru/webmaster/?id=1108938" target="_blank">Рекомендации»</a></td>
	</tr>
	<tr>
		<td><a href="http://help.yandex.ru/webmaster/?id=995298" target="_blank">Советы»</a></td>
	</tr>
	<tr>
		<td>Написать в Support через <a href="http://feedback.yandex.ru/?from=webmaster" target="_blank">форму обратной связи</a> или на E-mail: addurl@yandex-team.ru ( addurl@yandex.ru )</a></td>
	</tr>
	<tr>
		<td><a href="http://blogs.yandex.ru/search.xml?text=<?php echo htmlspecialchars($_SERVER['HTTP_HOST_NAME']); ?>" target="_blank">О Вашем сайте в блогах»</a> (справа RSS-подписка)</td>
	</tr>
	<tr>
		<td><a href="http://wordstat.yandex.ru/" target="_blank">Подбор ключевых слов»</a> (что ищут в интернете)</td>
	</tr>
	<tr class="yaproSortTR">
		<td><b>Google:</b></td>
	</tr>
	<tr>
		<td><a href="https://www.google.com/webmasters/tools/" target="_blank">Инструменты»</a> (<a href="https://www.google.com/webmasters/tools/dashboard?hl=ru&siteUrl=http%3A%2F%2F<?php echo $GLOBALS['SYSTEM']['config']['yapro_http_host']; ?>%2F" target="_blank">управление сайтом</a>)</td>
	</tr>
	<tr>
		<td><a href="http://www.google.com/support/webmasters/" target="_blank">Справочный центр»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.google.com/support/webmasters/bin/topic.py?topic=28806" target="_blank">Как содержать сайт»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.google.com/support/webmasters/bin/topic.py?hl=ru&topic=20985" target="_blank">Как влиять на результат поиска»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.google.com/support/webmasters/bin/topic.py?hl=ru&topic=28800" target="_blank">Как оптимизировать сайт»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.google.com/support/forum/p/webmasters?hl=ru&utm_source=HC&utm_medium=leftnav&utm_campaign=webmaster" target="_blank">Справочный форум»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.google.com/support/forum/signup?hl=ru" target="_blank">Отправить вопрос в Support»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.google.ru/alerts" target="_blank">Подписаться на оповещения о сайте»</a> например "<?php echo htmlspecialchars($_SERVER['HTTP_HOST_NAME']); ?> ошибки" или "<?php echo htmlspecialchars($_SERVER['HTTP_HOST_NAME']); ?> задолбали"</td>
	</tr>
	<tr>
		<td><a href="https://www.google.com/analytics/settings/" target="_blank">Analytics»</a> (статистика)</td>
	</tr>
	<tr class="yaproSortTR">
		<td><b>Советы экспертов:</b></td>
	</tr>
	<tr>
		<td><a href="http://optimization.ru/subscribe/list.html" target="_blank">Актуальные»</a></td>
	</tr>
	<tr>
		<td><a href="http://yapro.ru/web-master/promo.html" target="_blank">Фундаментальная информация»</a></td>
	</tr>
	<tr>
		<td><a href="http://host-tracker.com/" target="_blank">Установите проверку бесперебойной работы сайта»</a></td>
	</tr>
	<tr>
		<td><a href="http://www.facebook.com/pages/create.php?ref_type=sitefooter" target="_blank">Создайте группу в FaceBook »</a></td>
	</tr>
</table>
<style type="text/css">

</style>
<script type="text/javascript">
$(document).ready(function(){
	mouseMovementsClass("overflowTable");
});
function seoHref(){
	if(typeof(seoHref.arguments[1])=='undefined'){ var plus = ''; }else{ var plus = seoHref.arguments[1]; }
	window.open(seoHref.arguments[0] + document.getElementById('url').value + plus);
	return false;
}
</script>
</body>
</html>
