<?php
$comments_smiles = false;
include($_SERVER['DOCUMENT_ROOT'].'/inner/comments.php');

$document = str_replace('<div id="comments"></div>', '', $document);
$document = str_replace('<hr id="HRcommentForm">', '', $document);
$document = str_replace('<div id="commentFormName">Комментарий зрителя:</div>', '', $document);
$document = str_replace(' style="padding:10px 0"', '', $document);

echo '{"ajax_comment_form":'.ajaxHTML($document).'}';

exit;
?>
