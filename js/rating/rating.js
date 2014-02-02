function pages_rating(e){
	
	if(!e){ var e = ""; }
	
	$(e + ".rating").each(function(){
		
		var already = ~~$(this).attr("alt");
		
		var form = $(this).closest("FORM");
		$(form).submit(function(){
			
			$.post(this.action, $(this).serialize(), function(r){
				
				if(r.error){
					$.jGrowl(r.error, { speed: 0, life:7000, theme: "red" });
				}else{
					$.jGrowl("Спасибо за оценку");
				}
				
			},"json");
			
			return false;
		});
		
		var ul = this;
		var li = $('<li><a class="r1" href="#Ничтожно (1)" title="Ничтожно (1)">1</a></li>'+
		'<li><a class="r2" href="#Ужасно (2)" title="Ужасно (2)">2</a></li>'+
		'<li><a class="r3" href="#Плохо (3)" title="Плохо (3)">3</a></li>'+
		'<li><a class="r4" href="#Среднячок (4)" title="Среднячок (4)">4</a></li>'+
		'<li><a class="r5" href="#Приемлемо (5)" title="Приемлемо (5)">5</a></li>'+
		'<li><a class="r6" href="#Нормально (6)" title="Нормально (6)">6</a></li>'+
		'<li><a class="r7" href="#Неплохо (7)" title="Неплохо (7)">7</a></li>'+
		'<li><a class="r8" href="#Хорошо (8)" title="Хорошо (8)">8</a></li>'+
		'<li><a class="r9" href="#Отлично (9)" title="Отлично (9)">9</a></li>'+
		'<li><a class="r10" href="#Супер (10)" title="Супер (10)">10</a></li>');
		
		$(">A", li).click(function(){
			var i = $(">A", li).index(this);
			var c = $(".current",ul);
			var data = $(c).text().split(",");
			var u = eval(data[0]);
			var s = eval(data[1]);
			
			if(already != ""){
				s = s - already;
				u = u - 1;
			}
			
			var data = $(c).attr("alt").split(",");
			var stars = eval(data[0]);
			var star_width = eval(data[1]);
			var stars_width = stars * star_width;
			
			var average = (s + i + 1) / (u + 1);
			var average_degre = (average * 100) / stars;
			var width = average_degre * (stars_width / 100);
			
			$(c).css("width", width).show();
			
			var t = $(this).closest("TABLE");
			var r = $(".rating_right", t);
			if(r[0]){
				var rText = $(r).text().split(":");
				if(rText[1]){
					rText = rText[0] + ": ";
				}else{
					rText = "";
				}
				$(r).text( rText + (u + 1)+ " чел.");
			}
			
			$("[name=rating]", form).val( i + 1 );// выставленная оценка
			
			$(form).submit();
			
			return false;
		});
		
		$(ul).prepend(li);
		
	});
}
