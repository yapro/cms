<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_POST['get_ajax_comment_form']){
	include($_SERVER['DOCUMENT_ROOT'].'/inner/beforeContent/ajax_comment_form.php');
}
?>