/*
HTTP Host: static.ak.fbcdn.net
Generated: July 13th 2010 5:35:20 PM PDT
Machine: 10.140.112.103
Location: JIT Construction: v265153
Locale: en_US
*/

if (!window.FB) {FB = {};} if(!FB.dynData) { FB.dynData = {"site_vars":{"canvas_client_compute_content_size_method":1,"use_postMessage":0,"use_xdProxy":0,"use_ui_server":1,"monitor_usage_regex":"somethingtoputhere.com|huffingtonpost.com|lala.com","monitor_usage_rate":0.05,"enable_custom_href":1},"ui_server_dialogs":{"bookmark.add":1,"friends.add":1},"resources":{"base_url_format":"http:\/\/{0}.facebook.com\/","base_cdn_url":"http:\/\/static.ak.fbcdn.net\/","api_channel":1279032185,"api_server":1279032185,"www_channel":1279031884,"xd_proxy":1279031443,"xd_comm_swf_url":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z6D2S\/hash\/c729bxo3.swf","share_button":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zAB5S\/hash\/4273uaqa.gif","login_img_dark_small_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zF1W2\/hash\/a969rwcd.gif","login_img_dark_medium_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zEF9L\/hash\/156b4b3s.gif","login_img_dark_medium_long":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zBIU2\/hash\/85b5jlja.gif","login_img_dark_large_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z1UX3\/hash\/a22m3ibb.gif","login_img_dark_large_long":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z7SXD\/hash\/8mzymam2.gif","login_img_light_small_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zDGBW\/hash\/8t35mjql.gif","login_img_light_medium_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z38X1\/hash\/6ad3z8m6.gif","login_img_light_medium_long":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zB6N8\/hash\/4li2k73z.gif","login_img_light_large_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zA114\/hash\/7e3mp7ee.gif","login_img_light_large_long":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z4Z4Q\/hash\/8rc0izvz.gif","login_img_white_small_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z900E\/hash\/di0gkqrt.gif","login_img_white_medium_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z10GM\/hash\/cdozw38w.gif","login_img_white_medium_long":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zBT3E\/hash\/338d3m67.gif","login_img_white_large_short":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zCOUP\/hash\/8yzn0wu3.gif","login_img_white_large_long":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zC6AR\/hash\/5pwowlag.gif","logout_img_small":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/z2Y31\/hash\/cxrz4k7j.gif","logout_img_medium":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zAD8D\/hash\/4lsqsd7l.gif","logout_img_large":"http:\/\/static.ak.fbcdn.net\/rsrc.php\/zB36N\/hash\/4515xk7j.gif"}};} if (!FB.locale) {FB.locale = "en_US";} if (!FB.localeIsRTL) {FB.localeIsRTL = false;}


if(!window.FB)window.FB={};if(!window.FB.Share){FB.Share={results:{},resetUrls:function(){this.urls={};this.urlsA=[];},addQS:function(d,c){var a=[];for(var b in c)if(c[b])a.push(b.toString()+'='+encodeURIComponent(c[b]));return d+'?'+a.join('&');},getUrl:function(a){return a.getAttribute('share_url')||window.location.href;},getType:function(a){return a.getAttribute('type')||'button_count';},pretty:function(a){return a>=1e+07?Math.round(a/1e+06)+'M':(a>=10000?Math.round(a/1000)+'K':a);},updateButton:function(a){var b=this.getUrl(a);if(this.results[b])a.fb_count=this.results[b].total_count;this.displayBox(a,3);},displayBox:function(a,d){if(typeof(a.fb_count)=='number'&&a.fb_count>=d)for(var c=1;c<=2;c++){var b=a.firstChild.childNodes[c];b.className=b.className.replace('fb_share_no_count','');if(c==2)b.lastChild.innerHTML=this.pretty(a.fb_count);}},renderButton:function(c){var j=this.getUrl(c);var g=this.getType(c);var h=c.innerHTML.length>0?c.innerHTML:'Share';c.href=this.addQS('http://www.facebook.com/sharer.php',{u:j,t:j==window.location.href?document.title:null,src:'sp'});c.onclick=function(){if(!c.fb_clicked){c.fb_count+=1;FB.Share.displayBox(this,1);c.fb_clicked=true;}window.open(c.href,'sharer','toolbar=0,status=0,width=626,height=436');return false;},c.style.textDecoration='none';if(!this.results[j]&&(g.indexOf('count')>=0)){this.urls[j]=true;this.urlsA.push(j);}var i='Small';var a='<span class=\'FBConnectButton FBConnectButton_'+i+'\''+' style=\'cursor:pointer;\'>'+'<span class=\'FBConnectButton_Text\'>'+h+'</span></span>';if(g.indexOf('count')>=0){var e=(g=='box_count');var f=(e?'top':'right');var d='<span class=\'fb_share_size_'+i+' '+(e?'fb_share_count_wrapper':'')+'\'>';var b='<span class=\'fb_share_count_nub_'+f+' fb_share_no_count\'></span>';b+='<span class=\'fb_share_count fb_share_no_count'+' fb_share_count_'+f+'\'>'+'<span class=\'fb_share_count_inner\'>&nbsp;</span></span>';d+=(e)?'<span></span>'+b+a:a+b;}else if(g.indexOf('icon')>=0){var d='<span class=\'FBConnectButton_Simple\'>'+'<span class=\'FBConnectButton_Text_Simple\'>'+(g=='icon_link'?h:'&#xFEFF;')+'</span>';}else var d=a;c.innerHTML=d;c.fb_rendered=true;},insert:function(a){(document.getElementsByTagName('HEAD')[0]||document.body).appendChild(a);},renderAll:function(d){var c=document.getElementsByName('fb_share');var a=c.length;for(var b=0;b<a;b++){if(!c[b].fb_rendered)this.renderButton(c[b]);if(this.getType(c[b]).indexOf('count')>=0&&!c[b].fb_count&&this.results[this.getUrl(c[b])])this.updateButton(c[b]);}},fetchData:function(){var c=document.createElement('script');var a=[];for(var b=0;b<this.urlsA.length;++b)a.push('"'+this.urlsA[b].replace('\\','\\\\').replace('"','\\"')+'"');c.src=this.addQS(window.location.protocol+'//api.ak.facebook.com/restserver.php',{v:'1.0',method:'links.getStats',urls:'['+a.join(',')+']',format:'json',callback:'fb_sharepro_render'});this.resetUrls();this.insert(c);},stopScan:function(){clearInterval(FB.Share.scanner);FB.Share.renderPass();},renderPass:function(){FB.Share.renderAll();if(FB.Share.urlsA.length>0)FB.Share.fetchData();},_onFirst:function(){var b=document.createElement('link');b.rel='stylesheet';b.type='text/css';var a=(window.location.protocol=='https:'?'https://s-static.ak.fbcdn.net/':'http://static.ak.fbcdn.net/');b.href=a+'connect.php/css/share-button-css';this.insert(b);this.resetUrls();window.fb_sharepro_render=function(c){for(var d=0;c&&d<c.length;d++)FB.Share.results[c[d].url]=c[d];FB.Share.renderAll();};this.renderPass();this.scanner=setInterval(FB.Share.renderPass,700);if(window.attachEvent){window.attachEvent("onload",FB.Share.stopScan);}else window.addEventListener("load",FB.Share.stopScan,false);}};FB.Share._onFirst();}

if (FB && FB.Loader) { FB.Loader.onScriptLoaded(["FB.Share","FB.SharePro"]); }
