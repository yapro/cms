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
								<h1>{~$this-> data('name')~}</h1>
							</td>
						</tr>
						<tr>
							<td valign="top" id="fullContent">
								<!--MetaData-->
								{~$this-> data('article')~}
								<!--/MetaData-->
							</td>
						</tr>
						<tr>
							<td valign="top" style="padding:10px 0px;">
								<div id="innerArticleActions">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="1" class="nowrap">{~bookmark_button.php~}</td>
											<td width="1" class="nowrap">{~rating.php~}</td>
											<td>&nbsp;</td>
											<td width="260">{~share.php~}</td>
											<td width="1" class="nowrap" style="vertical-align:bottom"><g:plusone size="medium"></g:plusone></td>
										</tr>
									</table>
								</div>
								{~comments.php~}
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
<script>
// функция добавления страницы в закладки
function bookmarks_ajax(a, href){
	$.post(href,function(r){
		
		if(r.info!="ok"){
			
			jAlert(r.info);
			
		}else if($(a).hasClass("selected")){
			var title = "В закладки";
			$(a).removeClass("selected").attr("title",title).trigger("mouseover");
			$.jGrowl("Закладка удалена");
			if( $("#innerArticleActions").length ){
				$(a).text(title).tipsy({gravity: 'ne'});
			}
			
		}else{
			var title = "Из закладок";
			$(a).addClass("selected").attr("title",title).trigger("mouseover");
			$.jGrowl("Закладка добавлена");
			if( $("#innerArticleActions").length ){
				$(a).text(title);
			}
			
		}
	},"json");
}
$(document).ready(function(){
	
	$(".bookmarks").click(function(){
		
		bookmarks_ajax(this, this.href);
		
		return false;
		
	}).tipsy();
	
	pages_rating();
	
	$("#fullContent .produce_box A").attr("rel", "gallery").fancybox({
		'padding' : 0,
		'margin' : 0,
		'speedIn' : 0,
		'speedOut' : 0,
		'titleShow' : false,
		'transitionIn' : 'none',
		'transitionOut' : 'none',
		'changeSpeed' : 0,
		'changeFade' : 0,
		// 'autoScale' : false,
		//'centerOnScroll' : true,
		//'showCloseButton' : false,
		'cyclic' : true,
		'type' : 'image'
	});
	
	$("#fullContent .produce_box A IMG").attr("title", "Кликните чтобы увеличить").tipsy({gravity: "n"});
	
	$("#captchaImgcomments").tipsy();
});
</script>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
  {lang: 'ru'}
</script>
{~templates/html/bottom.php~}