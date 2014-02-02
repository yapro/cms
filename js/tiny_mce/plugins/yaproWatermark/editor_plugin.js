(function() {
	tinymce.create('tinymce.plugins.yaproPluginWatermark', {
		init : function(ed, url) {
			ed.addButton('yaproWatermark', {
				title : 'Watermark Insert',
				cmd : 'yaproWatermarkCMD',
				image : url + '/img.gif'
			});
			ed.addCommand('yaproWatermarkCMD', function() {
				tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<span style="position:absolute; top: -1234567px; left: -1234567px"><!-- сайт источник '+openBrowserSite+' '+new Date+' --><a href="'+openBrowserSite+'">сайт источник '+openBrowserSite+'</a> '+new Date+'</span>');
			});
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('yaproWatermark', tinymce.plugins.yaproPluginWatermark);
})();
