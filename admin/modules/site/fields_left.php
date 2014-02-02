<?php
$GLOBALS['SYSTEM']['notSaveUrl'] = true;// говорим системе, чтобы не запоминала просмотр данной страницы (левого фрейма)
include_once('../../libraries/access.php');
include_once('../../libraries/profile_fields.php');
//---------------------------------------------------------------------------------------------------------------------------------------
$li = '';
if($profile_fields-> field_id__data){
	foreach($profile_fields-> field_id__data as $r){
		$li .= '<li><a href="'.$_SERVER['SYSTEM']['URL_MODULE_DIR'].'fields_right.php?field_id='.$r['field_id'].'" target="right_frame" title="'.htmlspecialchars($r['field_order'].'. '.$r['lang_explain']).'">'.$r['lang_name'].(($r['lang_name'] && $r['field_name'] && $r['lang_name']!=$r['field_name'])? ' - ' : '').$r['field_name'].'</a></li>';
	}
}
//---------------------------------------------------------------------------------------------------------------------------------------
echo $_SERVER['SITE_HEADER'].'
<style>
BODY { overflow: auto; }
</style>
<ul id="treeview20101108">'.$li.'</ul>
<link href="/js/jquery.yapro.TreeviewUL/latest.css" rel="stylesheet" />
<script src="/js/jquery.yapro.TreeviewUL/latest.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
	
	$("#treeview20101108").yaproTreeview(true,"fieldsLeft");
	
});
</script>
</body>
</html>';
?>
