<?php
$name = str_replace('&amp;', '&', str_replace('"', '\"', trim($this->clear( $this-> data('name') ))));

$img = $this-> data('img')? 'http://'. $_SERVER['HTTP_HOST'].$this-> data('img') : '';

$url = 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$url_short = 'http://'. $_SERVER['HTTP_HOST'].'/system/to.'.$this-> id;

$roles = $this-> data('roles');
$genres = $this-> data('genres');
$quality = $this-> data('quality');
$translation = $this-> data('translation');

$tags = str_replace(',,', ',', $translation.','.$genres.','.$quality.','.$roles);

$notice = $genres? 'Жанр:'.$genres : '';

$notice .= $quality? ($notice?', ':'').'Качество:'.$quality : '';

$notice .= $translation? ($notice?', ':'').'Перевод:'.$translation: '';

$notice .= $roles? ($notice?', ':'').'В ролях:'.$roles : '';

$notice = str_replace(':,', ':', str_replace(',', ', ', str_replace(',,', ',', str_replace('&amp;', '&', str_replace('"', '\"', trim(clear($notice)))))));

echo '<noindex><!--googleoff: all-->
<link rel="stylesheet" href="/outer/kino_bookmarks/latest.css" type="text/css" media="screen" charset="utf-8">
<div>
	<ul class="b-article-actions">
		<li id="lebnikVkontakte">
			<script type="text/javascript" src="/outer/kino_bookmarks/vkontakte.ru.js"></script>
			<script type="text/javascript">
				document.write(VK.Share.button({title: "'.$name.'", description: "'.$notice.'", image: "'.$img.'", noparse: true},{type: "custom", text: "<img src=/outer/kino_bookmarks/vkontakte.ru.png>"}));
			</script>
		</li>
		<li id="lebnikFacebook">
			<a rel="nofollow" name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php">&nbsp;</a>
			<script src="/outer/kino_bookmarks/facebook.com.js" type="text/javascript"></script>
		</li>
		<li id="lebnikMailRu">
			<a rel="nofollow" class="mrc__share" type="button_count" href="http://connect.mail.ru/share"></a>
			<script src="/outer/kino_bookmarks/mail.ru.js" type="text/javascript" charset="UTF-8"></script>
		</li>
		<li id="lebnikZakladkiYandex">
			<a rel="nofollow" target="_blank" href="http://zakladki.yandex.ru/newlink.xml?url='. $url.'&name='.$name.'&descr='.$notice.'&tags='.$tags.'">
				<img src="/outer/kino_bookmarks/zakladki.yandex.ru.gif">
			</a>
		</li>
		<li id="lebnikGoogleBuzz">
			<a rel="nofollow" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="small-count" data-message="'.$name.'"></a>
			<script type="text/javascript" src="/outer/kino_bookmarks/google.com.js"></script>
		</li>
		<li id="lebnikOdnoklassniki">
			<a rel="nofollow" target="_blank" href="http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl='. urlencode($url).'">
				<img src="/outer/kino_bookmarks/odnoklassniki20x22.png">
			</a>
		</li>
		<li id="lebnikTwitter">
			<a rel="nofollow" target="_blank" href="http://twitter.com/home/?status=' .urlencode($name.' '.$url_short).'">
				<img src="/outer/kino_bookmarks/twitter.com.png">
			</a>
		</li>
		<li id="lebnikLivejournal">
			<a rel="nofollow" target="_blank" href="http://www.livejournal.com/update.bml?event=&lt;a href=&quot;'.$url.'&quot;&gt;&lt;img border=&quot;0&quot; align=&quot;left&quot; src=&quot;'.$img.'&quot; alt=&quot;&quot; /&gt;&lt;/a&gt'. $notice.'&lt;br&gt;&lt;a href=&quot;'.$url.'&quot;&gt;Подробнее...&lt;/a&gt;&amp;subject='.$name.'&amp;prop_taglist='.$tags.'">
				<img src="/outer/kino_bookmarks/livejournal.com.png">
			</a>
		</li>
	</ul>
</div>
<script type="text/javascript" src="/outer/kino_bookmarks/latest.js"></script>
<!--googleon: all--></noindex>';
?>
