(function() {
	tinymce.create('tinymce.plugins.yaproPluginImage', {
		init : function(ed, url) {
			ed.addButton('yaproFastImage', {
				title : 'Fast Image',
				cmd : 'yaproImageCMD',
				image : url + '/magicWand.gif'
			});
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('yaproFastImage', tinymce.plugins.yaproPluginImage);
})();
