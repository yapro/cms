<?php
$GLOBALS['SYSTEM']['notSaveUrl'] = true;

include_once('../../libraries/access.php');

include_once('../../libraries/fields.php');

$fields-> treeview();

echo $_SERVER['SITE_HEADER'].'
<ul id="treeview20090525">'.$fields->treeview.'</ul>
<link href="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="http://'. $_SERVER['HTTP_HOST'].'/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>';
?>
<script language="JavaScript" type="text/javascript">
$(document).ready(function(){
	
	$("#treeview20090525").yaproTreeview(true,"modulesLeft");
	
	$("#treeview20090525 li").each(function(){
		
		var a = $("a:first", this);
		var href = $(a).attr("href");
		
		if(href.substr(-2,1)=="#"){
			$(a).click(function(){ $(this).parents("li").filter(":first").find("div:first").trigger("click"); return false; });
		}else{
			$(a).attr("target", "right_frame");
		}
		
	});
});
</script>
</body>
</html>
