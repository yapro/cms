//этот файл нужно сохранять в UTF-8 с BOM(byte order mark), чтобы избежать проблем с кодировкой в Internet Explorer
window.mrc__sharePreload = function()
{
	var p = new Image();
	p.src = 'http://img1.imgsmail.ru/r/new_share_buttons_sprite.gif';
};
mrc__sharePreload();
if(!window.mrc__shareStart) {
	window.mrc__shareStart = (function(){
		var SHARE_URL='http://connect.mail.ru/share_count?func=mrc__shareInit&callback=1&url_list=',
		BUTTON_CLASS_MARKER='mrc__share',
		BUTTON_DONE_CLASS_MARKER='mrc__share_done',
		MICRO_REVERSE='mrc__share_reverse',
		CONNECT_URL='http://connect.mail.ru/share',
		IMGURL='http://img1.imgsmail.ru/r/',
		IEFIX='',//(/*@cc_on!@*/false && !(document.compatMode=='CSS1Compat' && +navigator.userAgent.match(/msie\s(\d+)/i )[1]>=8))?' top:0px;':'',
		CLASS_DEF='button_count',
		DEF_BUTTON_BG_POS1 = 50,
		DEF_BUTTON_BG_POS2 = '100% -19px',

		CLASS_VIEW={
			micro: [
				'background: none;width:auto;vertical-align:middle; outline:0; text-decoration:none;',
				'<img class="mrc__share_icon" src="'+ IMGURL+ 'button_micro.gif" style="width:auto;border:0; outline:0; vertical-align:middle;" alt="Поделиться ссылкой с друзьями"/>',
				'<b class="mrc__share_text" style="background: none;margin:0 1px 0 0; text-decoration:underline; color:#00468C; font:normal 12px/16px arial; vertical-align:middle;">###</b> '
			],
            button: [
                'overflow: hidden; white-space:nowrap;text-align:left;line-height:17px;position:relative;width:auto;outline:0; text-decoration:none; height:18px; font-size: 11px; line-height: 17px;overflow: hidden',
                '<small style="overflow: hidden; vertical-align: bottom;white-space:nowrap;text-align:left;line-height:17px;position:relative;width:auto;padding:0;margin:0;outline:0; text-decoration:none; display:inline-block; height:18px;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat 0 0; padding-left: 22px;line-height: 17px;"><i title="Поделиться страницей с друзьями в Моем Мире@Mail.Ru" style="vertical-align:top;float: none; white-space:nowrap;text-align:left;font-size:11px;line-height:100%;position:relative;width:auto;color:white;padding:0;margin:0;font-weight: bold; display:inline-block;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat right -19px; height: 14px; padding: 4px 5px 0 0; font-family: \'Lucida Grande\',Tahoma,Verdana,Arial,sans-serif;font-szie: 11px; font-style: normal;cursor: pointer; line-height: 9px; *line-height: 13px; *padding-top: 2px; *height: 18px;">##CAPTION##</i></small>'
            ],
            button_count: [
                'overflow: hidden; white-space:nowrap;text-align:left;line-height:17px;position:relative;width:auto;outline:0; text-decoration:none;  height:18px; font-size: 11px; line-height: 17px;overflow: hidden; font-weight: normal',
                '<small style="overflow: hidden; vertical-align: bottom;white-space:nowrap;text-align:left;line-height:17px;position:relative;width:auto;padding:0;margin:0;outline:0; text-decoration:none; display:inline-block; height:18px;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat 0 0; padding-left: 22px;line-height: 9px;"><i title="Поделиться страницей с друзьями в Моем Мире@Mail.Ru" style="vertical-align:top;float: none; white-space:nowrap;text-align:left;font-size:11px;line-height:9px;position:relative;width:auto;color:white;padding:0;margin:0;font-weight: bold; display:inline-block;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat right -19px; height: 14px; padding: 4px 5px 0 0; font-family: \'Lucida Grande\',Tahoma,Verdana,Arial,sans-serif; font-szie: 11px; font-style: normal;cursor: pointer; line-height: 9px; *line-height: 13px; *padding-top: 2px; *height: 18px; ">##CAPTION##</i><span title="Уже поделились" style="float: none; width:auto;color:black;padding:0;margin:0;display:none;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat left -123px; height: 13px; font-family: Tahoma; color: #000; padding: 5px 5px 0 10px; border-right: 1px solid #ffc70d; margin-left: 1px; cursor: pointer; font-weight: normal; font-style: normal; line-height: 8px; *line-height: 100%; *padding-top: 3px; *height: 18px">&nbsp;</span></small>'
            ],
            big: [
                'display: inline-block; white-space:nowrap;text-align:left;line-height:17px;position:relative;width:auto;outline:0; text-decoration:none;  height:18px; font-size: 11px; line-height: 17px;position: relative',
                '<b title="Уже поделились" style="font-style: normal;width:auto;padding:0;margin:0;width: 86px;cursor: pointer; right: 0; position: absolute;display: none; left: 0; top: 0; text-align: center; height: 42px; background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat left -76px; color: #333; padding: 5px 0 0 0; font-size: 10px; font-weight: normal"><span style="float: none; padding:0;margin:0;font-size: 16px;color: #333;font-weight: bold;">&nbsp;</span><br>Уже поделились<b style="position: absolute; right: 0 !important; right: -1px; top: 0; height: 41px; width: 1px; border-right: 1px solid #ffc70d"></b></b><small style="vertical-align: bottom;white-space:nowrap;text-align:left;line-height:17px;position:relative;width:auto;padding:0;margin:0;outline:0; text-decoration:none; display:inline-block; height:18px;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat 0 0; padding-left: 22px;line-height: 17px;"><i title="Поделиться страницей с друзьями в Моем Мире@Mail.Ru" style="float: none; white-space:nowrap;text-align:left;font-size:11px;line-height:9px;position:relative;color:white;padding:0;margin:0;font-weight: bold; display:inline-block;background:url('+IMGURL+'new_share_buttons_sprite.gif) no-repeat right -19px; height: 14px; padding: 4px 5px 0 0; font-family: \'Lucida Grande\',Tahoma,Verdana,Arial,sans-serif;font-szie: 11px; font-style: normal;font-style: normal;cursor: pointer; *line-height: 13%; *padding-top: 2px; *height: 18px; *overflow: hidden;">##CAPTION##</i></small>'
            ]
		},
		COUNTFOR={micro_count:1, button_count:1, big:1},
		CL=escape(document.location.toString()),
		d=document, a=d.getElementsByTagName('a'),
		p=[], u=[], count_req={}, type, innerHTML, newHTML, ca, share_url;

		for(var i=0; i<a.length; i++){
			if(a[i].className.indexOf(BUTTON_CLASS_MARKER) != -1 && a[i].className.indexOf(BUTTON_DONE_CLASS_MARKER) == -1){
				ca = a[i];
        ca.className = ca.className + ' ' + BUTTON_DONE_CLASS_MARKER;
				type = ca.getAttribute('type')||CLASS_DEF;
        if(!CLASS_VIEW[type])
        {
            type = "micro";
        }
				ca.style.cssText = CLASS_VIEW[type][0];
				
				share_url = (ca.href.match(/\?share_url=(.*)/i )||[0,''])[1];
				
				if(share_url.length==0){
					ca.href = CONNECT_URL+ '?share_url='+ CL;
					share_url = CL;
				}
				
				newHTML = CLASS_VIEW[type][1];

				if(type == 'micro' || type == 'micro_count' && ca.innerHTML.length){
					if(ca.className.indexOf(MICRO_REVERSE)==-1){
						newHTML = CLASS_VIEW[type][2].replace('###', ca.innerHTML) +  newHTML;
					} else {
						newHTML = newHTML+'&nbsp;'+  CLASS_VIEW[type][2].replace('###', ca.innerHTML);
					}
				}
        if(type == 'button' || type == 'button_count' || type == 'big'){
            if(ca.innerHTML.length)
                newHTML = newHTML.replace('##CAPTION##', ca.innerHTML);
            else
                newHTML = newHTML.replace('##CAPTION##', 'В Мой Мир');
        }

				ca.innerHTML = newHTML;
        if(type != 'micro')
        {
            ca.onmouseover = function(){
                var bigFix = 0;
                if(this.tagName == 'A'){
                    this.getElementsByTagName('small')[0].style.backgroundPosition = '0 -38px';
                    this.getElementsByTagName('i')[0].style.backgroundPosition = '100% -57px';
                }
            }
            ca.onmouseout = function(){
                var bigFix = 0;
                if(this.tagName == 'A'){
                    this.getElementsByTagName('small')[0].style.backgroundPosition = '0 0';
                    this.getElementsByTagName('i')[0].style.backgroundPosition = DEF_BUTTON_BG_POS2;
                }
            }
        }
				ca.onclick = (function(ca){
					return function(){
						window.open(ca.href, '_blank', 'width=540,height=385');
						return false;
					}
				})(ca)

				if(COUNTFOR[type]){
					if(!count_req[share_url])
          	u[u.length] = share_url;
					p[p.length] = ca;
					ca.share_url = unescape(share_url);
          count_req[share_url] = 1;
				}
			}
		}
		if(u.length > 0){
			window.mrc__shareInit = function(list){
				var s, c;
				for(var i=p.length; i--; ){
					s = list[p[i].share_url];
					c=(s && s.shares)||'0';
          if(c > 0){
              p[i].getElementsByTagName('span')[0].innerHTML = c;
              p[i].getElementsByTagName('span')[0].style.display = 'inline-block';
              if(p[i].type == "big")
              {
                  p[i].getElementsByTagName('b')[0].style.display = 'block';
                  p[i].style.paddingTop = '50px';
                  
                  if(p[i].getElementsByTagName('b')[0].clientWidth < p[i].clientWidth)
                      p[i].getElementsByTagName('b')[0].style.width = p[i].clientWidth + 'px';
                  if(p[i].getElementsByTagName('b')[0].clientWidth > p[i].clientWidth)
                      p[i].style.marginRight = p[i].getElementsByTagName('b')[0].clientWidth - p[i].clientWidth + 'px';
              }
          }
					p[i].onclick = (function(c, p){
						return function(){
							p.getElementsByTagName('span')[0].innerHTML = c+1;
              p.getElementsByTagName('span')[0].style.display = 'inline-block';
              if(p.type == "big")
              {
                  p.getElementsByTagName('b')[0].style.display = 'block';
                  p.style.paddingTop = '50px';
                  
                  if(p.getElementsByTagName('b')[0].clientWidth < p.clientWidth)
                      p.getElementsByTagName('b')[0].style.width = p.clientWidth + 'px';
                  if(p.getElementsByTagName('b')[0].clientWidth > p.clientWidth)
                      p.style.marginRight = p.getElementsByTagName('b')[0].clientWidth - p.clientWidth + 'px';
              }
							window.open(p.href, '_blank', 'width=540,height=385');
							return false;
						}
					})(+c, p[i])
				}
			}
			var current_location = window.document.location.href;
			for (var i = 0, l = u.length; i < l; i++) {
				if (!u[i].length) {
					u[i] = current_location;
				}
			}
			/* Lebnik [ отключаем скрытый сбор статистики Мэйл.ру
			with(document.getElementsByTagName('head')[0].appendChild(document.createElement('script'))){
				type = 'text/javascript';
				src = SHARE_URL+ encodeURIComponent(u.join(','));
			}*/
		}
	});
		
	if(/*@cc_on!@*/false == false)
	{
		mrc__shareStart();	
		var scanner  = setInterval('mrc__shareStart()', 500);
  }
	
	function onInit(){
		if(scanner)
			clearInterval(scanner);
		if ( window.addEventListener )
			window.removeEventListener( "DOMContentLoaded", onInit, false );

			mrc__shareStart();	

	}

	(function(){
		if ( document.addEventListener ) {
			document.addEventListener( "DOMContentLoaded", onInit, false );
		} else if ( document.attachEvent && window == top) (function(){
			try {
				document.documentElement.doScroll("left");
			} catch(e) {
				setTimeout(arguments.callee, 0);
				return;
			}
			onInit();
		})();
	})()
}
