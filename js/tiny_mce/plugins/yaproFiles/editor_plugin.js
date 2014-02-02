// плагин не разработан
(function() {
	tinymce.create('tinymce.plugins.yaproPluginFiles', {
		init : function(ed, url) {
			ed.addButton('yaproFiles', {
				title : 'Hide This',
				cmd : 'yaproFilesCMD',
				image : url + '/img.png'
			});
			ed.addCommand('yaproFilesCMD', function(){
				/*
var openBrowserSite = null;
var system_target_form_element = null;
var system_tinyMCE_img_id = null;
var system_tinyMCE_type = null;
var system_tinyMCE_option = null;
function openBrowser(img_id, target_form_element, type, option) {
   var img = document.getElementById(img_id);
   if (img.className != "mceButtonDisabled"){
      
      // если iframe
      if(window.opener){
         var op = window.opener.openBrowserPuth;
         var os = window.opener.openBrowserSite;
         var ol = window.opener.location;
         var ow = window.opener.screen.width;
         var oh = window.opener.screen.height;
      }else if(window.parent){
         var op = window.parent.openBrowserPuth;
         var os = window.parent.openBrowserSite;
         var ol = window.parent.location;
         var ow = window.parent.screen.width;
         var oh = window.parent.screen.height;
      }else{
         return false;
      }
      
      if(!op || op==""){
         alert("В файле "+ol+" не указан путь к окну файлов! Javascript переменная openBrowserPuth не существует или пуста!");
      }else if(!os || os==""){
         alert("В файле "+ol+" не указан URL сайта! Javascript переменная openBrowserSite не существует или пуста!");
      }else{
         system_tinyMCE_img_id = img_id;
         system_target_form_element = target_form_element;
         system_tinyMCE_type = type;
         system_tinyMCE_option = option;
         openBrowserSite = os;
         var window_name = Math.round(Math.random() * 1000000000);
         var w = "770";
         var h = "570";
         var valLeft = (ow) ? (ow-w)/2 : 0;// отступ слева
         var valTop = (oh) ? (oh-h)/2 : 0;// отступ сверху
         var features = "width="+w+",height="+h+",left="+valLeft+",top="+valTop+",";
         window.open(op, window_name, features+'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1');
      }
   }
}*/
				tinyMCE.activeEditor.execCommand('mceReplaceContent',false,'<span class="Files">{$selection}</span>');
			});
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('yaproFiles', tinymce.plugins.yaproPluginFiles);
})();
