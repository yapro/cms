/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.BBCodePlugin', {
		init : function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
//alert(o.content);
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});
		},

		getInfo : function() {
			return {
				longname : 'BBCode Plugin',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			// example: <strong> to [b]
			rep(/<a.*?href=\"(.+?)\".*?>(.+?)<\/a>/gi,"[url=$1]$2[/url]");
			rep(/<span style=\"color: ?(.+?);\">(.+?)<\/span>/gi,"[color=$1]$2[/color]");
			rep(/<p>(.+?)<\/p>/gi,"[p]$1[/p]");
			rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
			
			rep(/<blockquote>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");
			
			rep(/<strike>/gi,"[strike]");
			rep(/<\/strike>/gi,"[/strike]");
			
			rep(/<code>/gi,"[code]");
			rep(/<\/code>/gi,"[/code]");
			
			rep(/<(strong|b)>/gi,"[b]");
			rep(/<\/(strong|b)>/gi,"[/b]");
			
			rep(/<(em|i)>/gi,"[i]");
			rep(/<\/(em|i)>/gi,"[/i]");
			
			rep(/<p>/gi,"[p]");
			rep(/<\/p>/gi,"[/p]");
			
			rep(/<div>/gi,"[div]");
			rep(/<\/div>/gi,"[/div]");
			
			rep(/<u>/gi,"[u]");
			rep(/<\/u>/gi,"[/u]");
			
			rep(/<ol>/gi,"[ol]");
			rep(/<\/ol>/gi,"[/ol]");
			
			rep(/<ul>/gi,"[ul]");
			rep(/<\/ul>/gi,"[/ul]");
			
			rep(/<li>/gi,"[li]");
			rep(/<\/li>/gi,"[/li]");
			
			rep(/<br \/>/gi,"[br]");
			rep(/<br\/>/gi,"[br]");
			rep(/<br>/gi,"\n");
			rep(/<p>/gi,"");
			rep(/<\/p>/gi,"\n");
			
			rep(/\&/gi,"[amp]");
			//rep(/<br \/>/gi,"\n");
			//rep(/<br\/>/gi,"\n");
			//rep(/&nbsp;/gi," ");
			//rep(/&quot;/gi,"\"");
			//rep(/&lt;/gi,"<");
			//rep(/&gt;/gi,">");
			//rep(/&amp;/gi,"&");
			//rep(/<blockquote[^>]*>/gi,"[quote]");
			//rep(/<p.*?style=\".*?padding-left: ?([\d].+?).*?;\">(.+?)<\/p>/gi,"[p=$1]$2[/p]");
			return s; 
		},

		// BBCode -> HTML from PunBB dialect
		_punbb_bbcode2html : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			// example: [b] to <strong>
			rep(/\[url=([^\]]+)\](.+?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.+?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
			rep(/\[img\](.+?)\[\/img\]/gi,"<img src=\"$1\" />");
			rep(/\[p\](.+?)\[\/p\]/gi,"<p>$1</p>");
			rep(/\[color=(.+?)\](.+?)\[\/color\]/gi,"<span style=\"color: $1;\">$2</span>");
			
			rep(/\[quote\]/gi, "<blockquote>");
			rep(/\[\/quote\]/gi, "</blockquote>");
			
			rep(/\[code\]/gi,"<code>");
			rep(/\[\/code\]/gi,"</code>");
			
			rep(/\[(strong|b)\]/gi,"<strong>");
			rep(/\[\/(strong|b)\]/gi,"</strong>");
			
			rep(/\[(em|i)\]/gi,"<em>");
			rep(/\[\/(em|i)\]/gi,"</em>");
			
			rep(/\[u\]/gi,"<u>");
			rep(/\[\/u\]/gi,"</u>");
			
			rep(/\[p\]/gi,"<p>");
			rep(/\[\/p\]/gi,"</p>");
			
			rep(/\[div\]/gi,"<div>");
			rep(/\[\/div\]/gi,"</div>");
			
			rep(/\[br\]/gi,"<br>");
			rep(/\[amp\]/gi,"&");
			//rep(/\n/gi,"<br />");
			//rep(/\[p=([\d].+?)\](.+?)\[\/p\]/gi,"<p style=\"padding-left: $1px;\">$2</p>");
			
			return s; 
		}
	});

	// Register plugin
	tinymce.PluginManager.add('bbcodex', tinymce.plugins.BBCodePlugin);
})();
