(function() {
	tinymce.create('tinymce.plugins.yaproPluginInsertHTML', {
		init : function(ed, url) {
			ed.addButton('yaproInsertHTML', {
				title : 'Вставить HTML',
				cmd : 'yaproInsertHTMLCMD',
				image : url + '/img.jpg',
			});
			ed.addCommand('yaproInsertHTMLCMD', function(ui, v) {
				ed.windowManager.open({
						url : url+'/textarea.html',
						width : 670,
						height : 400,
						inline : true
				},{
					theme_url : this.url
				});
			});
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('yaproInsertHTML', tinymce.plugins.yaproPluginInsertHTML);
})();
