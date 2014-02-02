<?php
// скрипт по мотивам http://api.yandex.ru/share/

$name = $this->data('name');
$name = str_replace('&amp;', '&', str_replace('"', '\"', str_replace('&quot;', '"', trim( clear( $name )))));

$img = $this->data('img_front');

$url = 'http://'. $_SERVER['HTTP_HOST'].url();

$url_short = 'http://'. $_SERVER['HTTP_HOST'].'/s/p?'.$this->id;

//---------------------------------------

$notice = $this->data('article');

$notice .= $this->data('price')? 'Цена: '.$this->data('price') : '';

$notice .= $this->data('sizes')? 'Размеры в наличии: '.str_replace("\n",', ',$this->data('sizes')) : 'Возможен пошив любого размера на заказ.';

$notice = str_replace(':,', ':', str_replace(',', ', ', str_replace(',,', ',', str_replace('&amp;', '&', str_replace('"', '\"', trim(clear($notice)))))));

$notice_words = words($notice, 45);
if(mb_strlen($notice) > mb_strlen($notice_words)){
	$notice = $notice_words.'...';
}

echo '<noindex><!--googleoff: all-->
<span id="ya_share"></span>
<script type="text/javascript" src="http://yandex.st/share/share.js" charset="utf-8"></script>
<script type="text/javascript">
if(typeof(Ya)!="undefined"){
	new Ya.share({
		element: "ya_share",
		elementStyle: {
			"type": "icon",
			"border": true,
			"quickServices": ["vkontakte", "|", "facebook", "|", "moimir", "|", "yaru", "|", "gbuzz", "|", "odnoklassniki", "|", "friendfeed", "|", "lj", "|", "twitter"]
		},
		link: "'. $url.'",
		title: "'.$name.'",
		description: "'.$notice.'",
		image: "'.$img.'",
		popupStyle: {
			blocks: {
				"Поделиться": ["yaru", "twitter", "", "vkontakte"],
				"В заметки": ["evernote"]
			},
			copyPasteField: true
		},
		onready: function(instance){
			$("#ya_share A:first").remove();
			$("#ya_share A:last").attr("href","http://twitter.com/home/?status=' .urlencode(str_replace('\"', '"', $name).' '.$url_short).'");
		}
	});
}
</script>
<!--googleon: all--></noindex>';
?>