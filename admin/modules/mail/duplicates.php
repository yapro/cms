<?php
include_once('../../libraries/access.php');
//---------------------------------------------------------------------------------------------------
// добавляю
if(access('w') && $_POST['id'] && isset($_POST['important']) ){
	
	if(isset($_POST['important']) && !mysql_("UPDATE ".P."mail_duplicates SET important="._.$_POST['important']._." 
	"._."WHERE"._." id="._.$_POST['id']._."")){ echo 'MySQL ошибка'; }
	
	exit;
}

// удаляю
if(access('d') && $_POST['delete_id'] && mysql_("DELETE FROM ".P."mail_duplicates WHERE id="._.$_POST['delete_id']._."")){
	echo 'ajax_delete_ok'; exit;
}

// просматриваю письмо
if($_POST['id'] && is_numeric($_POST['id']) && $r = mysql_fetch_assoc(mysql_query("
	SELECT delivered, message FROM ".P."mail_duplicates 
	WHERE id='".$_POST['id']."'"))){
		
	if(!$r['delivered']){
		
		mysql_query("UPDATE ".P."mail_duplicates SET delivered='1' 
		WHERE id='".$_POST['id']."'");
		
	}
	echo $r['message']; exit;
}
//-------------------------------------------------------
$max = 50;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных
//-------------------------------------------------------
$url = $_SERVER['PHP_SELF'].'?nothing';// общий урл
$sql = "FROM ".P."mail_duplicates";
//-------------------------------------------------------
$where = '';
if($_POST['email']){// если выполняется поиск по определенному email-адресу
	$max = 500;// максимум строк в таблице
	$where .= " AND (from_email LIKE "._.trim($_POST['email'])._." OR to_email LIKE "._.trim($_POST['email'])._.")";
}
if($_POST['subj']){// если выполняется поиск по определенной фразе
	$max = 500;// максимум строк в таблице
	$where .= " AND subj LIKE "._."%".trim($_POST['subj'])."%"._."";
}
if($_POST['message']){// если выполняется поиск по определенной фразе
	$max = 500;// максимум строк в таблице
	$where .= " AND message LIKE "._."%".trim($_POST['message'])."%"._."";
}
if($where){
	$sql = $sql.' WHERE '.$where;
}
//-------------------------------------------------------
$r = @mysql_fetch_row(mysql_query("SELECT COUNT(*) ".$sql));// кол-во записей по запросу
$count = (int)$r['0'];
include_once('../../libraries/listing.php');
//---------------------------------------------------------------------------------------------------
if($q = mysql_("SELECT id, size, important, delivered, time_sent, from_email, to_email, subj ".$sql." ORDER BY time_sent DESC LIMIT ".$start.', '.$max)){
	while($r = mysql_fetch_assoc($q)){
		$str .= '<tr>
			<td><p>'.$r['id'].'</p></td>
			<td><p>'.date('d.m.Y H:i:s', $r['time_sent']).'</p></td>
			<td><p><input type="text" value="'.htmlspecialchars($r['from_email']).'" readonly></p></td>
			<td><p><input type="text" value="'.htmlspecialchars($r['to_email']).'" readonly></p></td>
			<td><p><input type="text" value="'.htmlspecialchars($r['subj']).'" readonly></p></td>
			<td><p><input type="text" value="'.filesize_($r['size']).'" readonly></p></td>
			<td width="20" title="Важность письма"><p><input type="checkbox" alt="'.$r['id'].'"'.($r['important']?' checked':'').'></p></td>
			<td width="20" title="Просмотреть сообщение"><p class="delivered"><img alt='.$r['id'].' src="images/types/add.gif"'.($r['delivered']?' class="messageDelivered"':'').' style="cursor:pointer;"></p></td>
			<td width="20"><p><img src="images/elements/del.gif" class="delete_id" title="Удалить" alt='.$r['id'].' style="cursor:pointer;"></p></td>
		</tr>';
	}
}

echo $_SERVER['SITE_HEADER'].Head('Отправленные с сайта письма','','<td style="white-space:nowrap">
	<form action="'.$url.'" method="post">
		<b>E-mail :</b> <input name="email" value="'.htmlspecialchars($_POST['email']).'" style="width:155px"> 
		<b>Тема :</b> <input name="subj" value="'.htmlspecialchars($_POST['subj']).'" style="width:155px"> 
		<b>Фраза :</b> <input name="message" value="'.htmlspecialchars($_POST['message']).'" style="width:155px"> 
		<input type="submit" value="Найти">
	</form>
</td><td style="white-space:nowrap" id="delete_selected_checkbox">
	<img src="images/elements/del.gif" title="Удалить выбранные">
</td>').($str?'
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
	<tr class="yaproSortTR">
		<td width="50"><b>ID</b></td>
		<td width="125"><b>Дата отправления</b></td>
		<td><b>От кого</b></td>
		<td><b>Кому</b></td>
		<td><b>Тема</b></td>
		<td width="90"><b>Размер</b></td>
		<td width="60" colspan=3><b>Действия</b></td>
	</tr>
	'.$str.'
</table>'.$list : '<p style="padding:10px">C сайта еще не отправлялись письма или были удалены.</p>');
//---------------------------------------------------------------------------------------------------
?>
<style type="text/css">
#delete_selected_checkbox {
	cursor:pointer;
	padding-left:10px;
}

.messageDelivered { opacity: 0.33; }

#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
<script type="text/javascript" src="/js/multi_checkbox.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	
	mouseMovementsClass("overflowTable");
	
	$(":text").addClass("input_text").each(function(){ $(this).attr("title", $(this).val() ); });
	
	$(".display_none").hide();
	
	$(":checkbox").click(function(){
		/* хитрая особенность проверки checked в момент клика заключается в том, что при клике сначала выполняется действие пользователя, 
		а затем срабатывают события, т.е. проверки должны быть обратные */
		if(this.checked == true){
			$(this).attr('checked','true');
			var important=1;
		}else{
			$(this).removeAttr('checked');
			var important=0;
		}
		$.post('<?php echo $url; ?>', {id:$(this).attr('alt'),important: important});
	});
	$(".delivered IMG").click(function(){
		if( $(this).attr("class")!="messageDelivered" ){
			$(this).attr("class","messageDelivered");
		}
		$.post('<?php echo $url; ?>', {id:$(this).attr('alt')}, function(r){
			$.fancybox("<table width=100% id=fancybox_lebnik_auto_resize><tr><td>"+ r +"</td></tr></table>", {
					"margin" : 0,
					"speedIn" : 0,
					"speedOut" : 0,
					"titleShow" : false,
					"useNextPrev":false
			});
			setTimeout(function(){
				var fancybox_wrap = $("#fancybox-wrap");
				var fancybox_wrap_width = $(fancybox_wrap).width();
				if( fancybox_wrap_width >= screenWidth() ){
					$(fancybox_wrap).width( fancybox_wrap_width - 50 );
					var fancybox_content = $("#fancybox-content");
					var fancybox_content_width = $(fancybox_content).width();
					$(fancybox_content).width( fancybox_content_width - 50 );
				}
				$.fancybox.resize();
			}, 123);
		});
	});
	$(".delete_id").click(function(){
		var img = this;
		var tr = $(this).closest("TR");
		jConfirm("Подтверждаете удаление?", function(r){
			if(r==true){
				$.post('<?php echo $url; ?>', {delete_id:$(img).attr('alt')}, function(r){
					if(r=="ajax_delete_ok"){
						$(tr).remove();
					}
				});
			}
		}, null, "Да", "Нет");
	});
});



$(".overflowTable").lebnik_multi_checkbox();


var delete_selected_checkbox_post = true;// удаляем выбранные чекбоксы
$("#delete_selected_checkbox").click(function(){
	
	jConfirm("Подтверждаете удаление?", function(r){
		if(r==true){
			
			$(".overflowTable :checkbox").each(function(){
				
				if($(this).attr("checked")){
					var tr = $(this).closest("TR");
					$.post('<?php echo $url; ?>', {delete_id:$(this).attr('alt')}, function(r){
						if(r=="ajax_delete_ok"){
							$(tr).remove();
						}
					});
				}
			});
		}
	}, null, "Да", "Нет");
});
</script>
</body>
</html>
