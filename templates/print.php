<?php
include($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/folio.php');
$this->id = $folio;
$this-> data('name');
if(!$this->pages[ $this->id ]['name']){
	header404('error_print page_id');
}
?>
{~header.html~}
<style>
BODY {
	padding: 25px;
	background-color: #FFF;
}
</style>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td>
			<h1><!--NoReplace-->{~$this-> data('name')~}<!--/NoReplace--></h1>
        </td>
    </tr>
    <tr>
        <td class="article">
        	{~$this-> data('article')~}
        </td>
    </tr>
</table>
</body>
</html>
