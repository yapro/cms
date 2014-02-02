<?php
$document = '<tr class="no">
	<td id="lebnik_bookmarks" colspan="2">
		<link rel="stylesheet" href="/outer/lebnik_bookmarks/latest.css" type="text/css" media="screen" charset="utf-8">
		<ul class="b-article-actions">
			<li id="lebnikZakladkiYandex">
				<a rel="nofollow" target="_blank" href="http://zakladki.yandex.ru/newlink.xml?url=~url_short~&name=~name~&descr=~notice~&tags=~tags~">
					<img src="/outer/lebnik_bookmarks/zakladki.yandex.ru.gif">
				</a>
			</li>
			<li id="lebnikVkontakte">
				<script type="text/javascript" src="/outer/lebnik_bookmarks/vkontakte.ru.js"></script>
				<script type="text/javascript">
					document.write(VK.Share.button({title: "~name~", description: "~notice~", image: "~img~", noparse: true},{type: "custom", text: "<img src=/outer/lebnik_bookmarks/vkontakte.ru.png>"}));
				</script>
			</li>
			<li id="lebnikFacebook">
				<a rel="nofollow" name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php">&nbsp;</a>
				<script src="/outer/lebnik_bookmarks/facebook.com.js" type="text/javascript"></script>
			</li>
			<li id="lebnikMailRu">
				<a rel="nofollow" class="mrc__share" type="button_count" href="http://connect.mail.ru/share"></a>
				<script src="/outer/lebnik_bookmarks/mail.ru.js" type="text/javascript" charset="UTF-8"></script>
			</li>
			<li id="lebnikTwitter">
				<a rel="nofollow" target="_blank" onclick="window.open(\'http://twitter.com/home/?status=\'+ encodeURIComponent(\'~name~ ~url_short~\'), this.target); return false;" href="#tweet">
					<img src="/outer/lebnik_bookmarks/twitter.com.png">
				</a>
			</li>
			<li id="lebnikGoogleBuzz">
				<a rel="nofollow" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="small-count" data-message="~name~"></a>
				<script type="text/javascript" src="/outer/lebnik_bookmarks/google.com.js"></script>
			</li>
			<li id="lebnikLivejournal">
				<a rel="nofollow" target="_blank" href="http://www.livejournal.com/update.bml?event=~notice~&lt;br&gt;&lt;a href=&quot;~url_short~&quot;&gt;Подробнее...&lt;/a&gt;&amp;subject=~name~">
					<img src="/outer/lebnik_bookmarks/livejournal.com.png">
				</a>
			</li>
		</ul>
<script>
/*$("#lebnikZakladkiYandex").attr("title","Добавить в Яндекс.Закладки");
$("#lebnikVkontakte").attr("title","Добавить в заметки Вконтакте");
$("#lebnikFacebook").attr("title","Опубликовать в Фэйсбуке");
$("#lebnikMailRu").attr("title","Опубликовать в Mail.Ru");
$("#lebnikTwitter").attr("title","Опубликовать в Twitter");
$("#lebnikGoogleBuzz").attr("title","Опубликовать в Google Buzz");
$("#lebnikLivejournal").attr("title","Опубликовать в LiveJournal (ЖЖ)");

var lebnik_bookmarks_name = $("input[name=name]").val().replace(\'"\', \'\"\');
var lebnik_bookmarks_img = $("input[name=img]").val();
var lebnik_bookmarks_url_short = "http://"+domain+"/system/to.";
var lebnik_bookmarks_notice = "";

function lebnik_bookmarks_admin(){
	lebnik_bookmarks_name = $("input[name=name]").val().replace(\'"\', \'\"\');
	vlebnik_bookmarks_img = $("input[name=img]").val();
	var page_id = $("#formPage").attr("action").split("page_id=");
	lebnik_bookmarks_url_short = "http://"+domain+"/system/to."+page_id[1];
	var article = $("[name=article]").val();
	lebnik_bookmarks_notice = "";
	if(article){
		var ex = article.split("<!-- pagebreak -->");
		if(ex[1]){
			lebnik_bookmarks_notice = strip_tags(ex[1]).replace(\'"\', \'\"\');
		}
	}
	var html = $("#lebnik_bookmarks UL:first").html();
	//$("#lebnik_bookmarks UL:first").html( html.replace("~name~", name).replace("~img~", img).replace("~url_short~", url_short).replace("~notice~", notice) );
	
}
$(document).ready(function(){
	lebnik_bookmarks_admin();
	$("#formPage").submit(function(){
		lebnik_bookmarks_admin();
	});
});*/
</script>
	</td>
</tr>';
?>
