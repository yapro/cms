(function() {
	tinymce.create('tinymce.plugins.yaproPluginInsertHTML', {
		init : function(ed, url) {
			ed.addButton('yaproInsertHTML', {
				title : 'Insert HTML',
				cmd : 'yaproInsertHTMLCMD',
				image : url + '/img.jpg',
				/*
				onclick : function(e) {
					alert(this.tagName+"=="+e.tagName);
				}*/
			});
			ed.addCommand('yaproInsertHTMLCMD', function(ui, v) {
				OpenWindow(700,500, url+'/textarea.html');
			});
			
			/*
			ed.addCommand('mycommand', function(ui, v) {
	           ed.windowManager.alert('Hello world!! Selection: ' + ed.selection.getContent({format : 'text'}));
	        });
			ed.onMouseUp.add(function(ed, e) {
	          console.debug('Mouse up event: ' + e.target.nodeName);
	        });
			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('yaproFastImage', n.nodeName == 'IMG');
			});
			ed.onClick.add(function(ed, e) {// - onMouseover
			   alert("1");
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				alert("1");
			});
			ed.onChange.add(function(ed, l) {
		          alert("2");
			});
			var ctrl = editor.controlManager.createButton('mybutton', {
			   title : 'My button',
			   'class' : 'mybutton',
			   onclick : function() {
			       editor.execCommand('Bold');
			   }
			});

			*/
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('yaproInsertHTML', tinymce.plugins.yaproPluginInsertHTML);
})();


// Add a class to all paragraphs in the editor.
tinyMCE.activeEditor.dom.addClass(tinyMCE.activeEditor.dom.select('p'), 'someclass');

// Gets the current editors selection as text
tinyMCE.activeEditor.selection.getContent({format : 'text'});

// Creates a new editor instance
var ed = new tinymce.Editor('textareaid', {
   some_setting : 1
});

// Select each item the user clicks on
ed.onClick.add(function(ed, e) {
   ed.selection.select(e.target);
});

ed.render();
