{~templates/html/header.php~}
<style>

</style>
<script type="text/javascript">

</script>
<div class="centerOuter">
	<div class="centerInner Site">
		<div id="body" class="colmask leftmenu">
			<div class="colleft">
				<div class="col1">
					<!-- Column 1 start -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" id="fullContent">
								<!--MetaData-->
								{~$this-> data('article')~}
								<!--/MetaData-->
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
				{~templates/html/left.php~}
			</div>
		</div>
	</div>
</div>
{~templates/html/bottom.php~}