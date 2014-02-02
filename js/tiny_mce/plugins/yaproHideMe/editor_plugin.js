(function() {
	tinymce.create('tinymce.plugins.yaproPluginHideMe', {
		init : function(ed, url) {
			ed.addButton('yaproHideMe', {
				title : 'Скрыть выделенное',
				cmd : 'yaproHideMeCMD',
				image : url + '/img.jpg'
			});
			ed.addCommand('yaproHideMeCMD', function() {
				tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<span class="HideMe">'+tinyMCE.activeEditor.selection.getContent({format : 'html'})+'</span>');
			});
			// Add a node change handler, selects the button in the UI when a image is selected
			/*ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('yaproHideMe', n.nodeName == 'IMG');
				
			});*/
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('yaproHideMe', tinymce.plugins.yaproPluginHideMe);
})();
