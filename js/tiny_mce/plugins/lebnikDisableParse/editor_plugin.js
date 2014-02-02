(function() {
	tinymce.create('tinymce.plugins.lebnikDisableParsePlugin', {
		init : function(ed, url) {
			ed.addButton('lebnikDisableParse', {
				title : 'Disable Parser',
				cmd : 'lebnikDisableParseCMD',
				image : url + '/img.gif'
			});
			ed.addCommand('lebnikDisableParseCMD', function() {
				tinyMCE.activeEditor.execCommand('mceReplaceContent',false,tinyMCE.activeEditor.selection.getContent({format : 'html'}).replace(/{~/g,"{<!-- -->~"));
			});
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('lebnikDisableParse', tinymce.plugins.lebnikDisableParsePlugin);
})();
