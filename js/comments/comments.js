// добавляем сообщение
function CommentSubmit(form){
	var t = $('#textareaTable [name=comment]')[0];
	if($(t).val()==''){
		jAlert("Введите текст сообщения", function(){
			t.focus();
		});
	}else{
		$.fancybox.showActivity();
		$.post(form.action, $(form).serialize(), function(msg){
			CommentInsert(msg, COMMENTS_LINE, COMMENTS_PARENT);
		});
	}
	return false;
}
function replayID(e){
	var id = $(e).attr("id");
	var commentForm = $("#commentForm");
	if(id && id=="replayForm"){// возвращает форму обратно на место
		$(e).removeAttr("id");
		$(e).text("Ответить на");
		$(commentForm).insertAfter("#HRcommentForm");
		$("FORM", commentForm)[0].parent_id.value = "";
		$("[name=parent_id]", commentForm).val("");
	}else{// если форма где-то
		$(".replayIDcomment").text("Ответить на");
		$("#replayForm").removeAttr("id");
		$(e).attr("id","replayForm");
		$(e).text("Отменить ответ на");
		$(commentForm).insertAfter( $(e).closest("DIV") );
		var li = $(e).closest("LI")[0];
		var parent_id = li.id.split('_');
		$("[name=parent_id]", commentForm).val( parent_id[1] );
		$("TEXTAREA", commentForm).focus();
		/*if($.isFunction($.scrollTo)){
			var position = PosElement( $(e)[0] );
			$(window).scrollTo({top: (position.Bottom - $(commentForm).height() ), left:0}, 800);// 800 - скорость прокрутки
			$("TEXTAREA", commentForm).focus();
		}*/
	}
	return false;
}
function CommentInsert(msg, COMMENTS_LINE, COMMENTS_PARENT){
	
	$.fancybox.hideActivity();
	
	if(msg){// если это Ajax-сообщения
		
		if(msg.substr(0,5)=="error"){
			
			jAlert(msg.substr(6));
			
		}else{
			
			eval('var a = {'+msg+'}');
			
			if(a.user_id && a.user_id!='0'){ $("#commentUserReg").hide(); }// если пользователь зарегистрирован - скрываем регистрационные поля ввода данных
			
			var comment = a.comment;
			
			$(".captcha_comments").hide();
			var form = $("#commentForm FORM")[0];
			
			form.comment.value = "";
			
			if(form.parent_id.value){// если выбран ответ кому-либо
				
				var next = $("#comment_"+form.parent_id.value).next()[0];
				
				if(next && typeof(next)=="object"){// если есть подобные комментарии или ответы
					
					if(next.tagName=="UL"){// если есть ответы
						var answers = $("> LI", next);// находим все LI
						var e = answers[ (answers.length-1) ];// размещаем после самого последнего LI
					}else{// tagName==LI - если есть подобные комментарии
						var e = $("#comment_"+form.parent_id.value);
						comment = '<ul>'+comment+'</ul>';
					}
				}else{// если подобных комментариев или ответов нет
					var e = $("#comment_"+form.parent_id.value);
					comment = '<ul>'+comment+'</ul>';// class="comments"
				}
				
				replayID( $("#replayForm") );
				
			}else{// обычный коммент - добавляется вниз
				
				if(!COMMENTS_PARENT){ var COMMENTS_PARENT = null; }// дополнительно определяющий элемент вставки
				
				var answers = $("UL.comments > UL, UL.comments > LI", COMMENTS_PARENT);
				
				if(answers.length>0){// если на данной странице уже имеются комментарии
					if(comments_sort=='ASC'){
						var e = answers[ (answers.length-1) ];
					}else{
						$(comment).insertBefore(answers[0]);
						return false;
					}
				}else{// если на данной странице комментариев нет
					
					if(COMMENTS_LINE){
						$(COMMENTS_LINE).after('<ul class="comments">'+comment+'</ul>');
					}else{
						$("#commentForm").before('<ul class="comments">'+comment+'</ul>');
					}
					return false;
				}
			}
			comment = comment.replace('<ul>', '<ul class="'+(($(e).closest("UL").attr("class")=='commentBG1')?'commentBG2':'commentBG1')+'">');
			$(comment).insertAfter(e);
			location.href = '#commentNew';
		}
	}
}
function toTextarea(past, bb_contour){
	var t = $('#textareaTable [name=comment]')[0];
	t.focus();
	if($.browser.msie){
		var r = t.document.selection.createRange();// создаем на основе выделенного объект TextRange
		if(bb_contour){ past = '['+past+']'+t.document.selection.createRange().text+'[/'+past+']'; }
		t.document.selection.createRange().text = past;// вставляем нужное
		r.moveStart("character", past.length)// начальная позиция равна длинне нового текста
		r.moveEnd("character", 0); // конечная позиция равна минусовой длинне вставляемого текста
		r.select(); 
	}else{
		var start = t.selectionStart;// находим порядковый номер начала выделения
		var end = t.selectionEnd;// находим порядковый номер окончания выделения
		var last = t.value.length;// вычисляем длинну нашей textarea или находим последний номер "элемента"
		var range_text = t.value.substr(start, end-start);// текст выделения
		if(bb_contour){ past = '['+past+']'+range_text+'[/'+past+']'; }
		// делим строку на 2 части
		var first_part = t.value.substr(0,start);// берем 1-ую часть строки
		var two_part = t.value.substr(end,last);// берем 2-ую часть строки
		t.value = first_part + past + two_part;// вставляем нужное
		var to_textform = first_part.length + past.length;// к длине 1-ой части прибавили длину вставляемого слова
		t.setSelectionRange(to_textform,to_textform);// выставляем выделение в нужном порядковом номере, в данном случае выделение = 0
	}
}
function CommentsKarma(e,pluse){
	
	var li = $(e).closest("LI")[0];
	var comment_id = li.id.split('_');
	
	$.post("/js/comments/karma.php", {comment_id:comment_id[1], karma:(pluse? 1 : 0)}, function(r){
		
		if(!r || typeof(r)!="object"){
			
			jAlert("Возникла ошибка программного характера, пожалуйста сообщите нам об этом.");
			
		}else if(r.karma && r.karma=="ok"){
			
			var karma = $("#comment_"+comment_id[1]+" .karma");
			
			var v = (pluse? (~~$(karma).text() + ~~r.value) : (~~$(karma).text() - ~~r.value) );
			
			$(karma).removeClass("karma_pluse karma_minus").text( ((v>0)? "+":"")+v );
			
			if(v>0){
				$(karma).addClass("karma_pluse");
			}else if(v<0){
				$(karma).addClass("karma_minus");
			}
			
		}else if(r.error && r.error!=""){
			
			jAlert(r.error);
			
		}else{
			jAlert("Непредвиденная ситуация, пожалуйста сообщите нам об этом.");
		}
	}, "json");
}
$(document).ready(function(){
	
	$(".karma").css("padding","0 5px").before('<img onclick="CommentsKarma(this);" src="/js/comments/karma_down.gif" style="cursor:pointer;" title="Бесполезен">').after('<img onclick="CommentsKarma(this,1);" src="/js/comments/karma_up.gif" style="cursor:pointer;" title="Полезный">');
	
	$("#commentsList .Info").prepend('<a href="#" onclick="return replayID(this);" class="replayIDcomment">Ответить на</a> : ');
	
	/*if(typeof(comment_smiles) != "undefined"){
		
		$('<div id="commentSmiles" style="position:relative; height:25px; overflow:hidden; margin-top:10px"><div style="position:absolute; white-space:nowrap;">'+comment_smiles+'</div></div>').insertBefore('#textareaTable');
		
		$('#commentSmiles IMG').click(function(){ toTextarea(' '+$(this).attr("alt")+' '); });
	}*/
	
	$("#commentForm [name=comment]").keydown(function(e){
	    if(e.which == 13 && (e.ctrlKey || e.shiftKey) ){
			$("#commentForm :submit:first").trigger("click");
	    };
	});
	/*
	if($.isFunction($.fn.mousewheel)){
		$('#commentSmiles DIV').mousewheel(function(event, delta){
			var m = this.style.marginLeft? parseInt(this.style.marginLeft) : 0;// узнаем текущий отступ слева
			if(delta > 0){
				$(this).css("margin-left", (m + 10)+"px");
			}else{
				$(this).css("margin-left", (m - 10)+"px");
			}
			return false;
		});
	}
	if($.isFunction($.fn.autoResize)){
		$('#textareaTable textarea').autoResize().trigger("click");
	}*/
	var commentsX = commentsY = 0;
	if(!comments_notBot){
		$(document).bind('mousemove',function(e){
			if((e.pageX != commentsX || e.pageY!=commentsY) && !comments_notBot){
				comments_notBot = true;
				$.getScript("/js/comments/notBotMD5mousemove.php");
			}
		});
	}
});
