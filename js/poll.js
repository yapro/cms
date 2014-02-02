jQuery(document).ready(function($){
	$(".RADIO *").each(function(){
		if(this.tagName=="INPUT" || this.tagName=="TEXTAREA"){
			$(this).click(function(){
				var current = this;
				var RADIO = $(this).closest(".RADIO");
				$("*", RADIO).each(function(){
					if(this != current){
						if(this.type=='radio' || this.type=='checkbox'){ this.checked = false; }
						if(this.type=='text' || this.tagName=='TEXTAREA'){ $(this).val(''); }
					}
				});
			});
		}
	});
});
