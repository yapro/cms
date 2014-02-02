<?php
if($this->id == 33363){// Учетная запись
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/inner/user.php');
	
}else if($this->id == 33112){// Авторизация Вконтакте - возникла ошибка
	
	include_once($_SERVER['DOCUMENT_ROOT'].'/templates/html/vkontakte_error.php');
	
}else{
	$document = $this-> data('article');
}
?>
{~templates/html/header.php~}
<div class="centerOuter">
	<div class="centerInner Site">
		<div id="body" class="colmask leftmenu">
			<div class="colleft">
				<div class="col1">
					<!-- Column 1 start -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top">
								<h1 class="box-head">{~$this-> data('name')~}</h1>
							</td>
						</tr>
						<tr>
							<td valign="top" id="fullContent">
								<?php echo $document; ?>
							</td>
						</tr>
						<tr>
							<td valign="top" id="td_listing">
								{~$GLOBALS['system_listing']~}
							</td>
						</tr>
					</table>
					<!-- Column 1 end -->
				</div>
				<?php echo $without_left? '' : '{~templates/html/left.php~}'; ?>
			</div>
		</div>
	</div>
</div>
<?php echo $without_left? '</body></html>' : '{~templates/html/bottom.php~}'; ?>