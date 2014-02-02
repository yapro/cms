(function () {
	var w = window;
	var d = w.document;
	var selection = function () {
		try {
			var a = null;
			var b = null;
			if (w.getSelection) {
				b = w.getSelection();
			} else {
				if (d.getSelection) {
					b = d.getSelection();
				} else {
					b = d.selection;
				}
			}
			if (b != null) {
				var pre = "",
				a = null,
				suf = "",
				pos = -1;
				if (b.getRangeAt) {
					var r = b.getRangeAt(0);
					a = r.toString();
					var c = d.createRange();
					c.setStartBefore(r.startContainer.ownerDocument.body);
					c.setEnd(r.startContainer, r.startOffset);
					pre = c.toString();
					var cr = r.cloneRange();
					cr.setStart(r.endContainer, r.endOffset);
					cr.setEndAfter(r.endContainer.ownerDocument.body);
					suf = cr.toString();
				} else {
					if (b.createRange) {
						var r = b.createRange();
						a = r.text;
						var c = b.createRange();
						c.moveStart("character", -60);
						c.moveEnd("character", -a.length);
						pre = c.text;
						var cr = b.createRange();
						cr.moveEnd("character", 60);
						cr.moveStart("character", a.length);
						suf = cr.text;
					} else {
						a = "" + b;
					}
				}
				var p;
				var s = (p = a.match(/^(\s*)/)) && p[0].length;
				var e = (p = a.match(/(\s*)$/)) && p[0].length;
				pre = pre + a.substring(0, s);
				suf = a.substring(a.length - e, a.length) + suf;
				a = a.substring(s, a.length - e);
				if (a == "") {
					return null;
				}
				return {// 
					pre: pre,
					text: a,
					suf: suf,
					pos: pos
				};
			} else {
				return;
			}
		} catch(e) {
			return null;
		}
	};
	var data = function(){
		var s = selection();
		if (!s) {
			return;
		}
		with(s) {
			pre = pre.substring(pre.length - 60, pre.length).replace(/^\S{1,10}\s+/, "");
			suf = suf.substring(0, 60).replace(/\s+\S{1,10}$/, "");
		}
		var x = s.pre + "<!!!>" + s.text + "<!!!>" + s.suf;
		var str = ("" + x).replace(/[\r\n]+/g, " ").replace(/^\s+|\s+$/g, "");
		if (str.length > 256) {
			jAlert("Вы выбрали слишком большой объем текста!");
			return;
		}
		return s;
		// ;
	};
	d.onkeypress = function (e) {
		var ctrlEnter = 0;
		var we = w.event;
		if (we) {
			ctrlEnter = we.keyCode == 10 || (we.keyCode == 13 && we.ctrlKey);
		} else {
			if (e) {
				ctrlEnter = (e.which == 10 && e.modifiers == 2) || (e.keyCode == 0 && e.charCode == 106 && e.ctrlKey) || (e.keyCode == 13 && e.ctrlKey);
			}
		}
		if (ctrlEnter) {
			var x = data();
			if(x && typeof(x)=='object'){
				jPrompt('Обязательно опишите ошибку:', '', function(r) {
					if(r){
						x.info = r;
						$.post("/js/jquery.yapro.Spelling/latest.php", x, function(){
							jAlert("Спасибо за помощь!");
							setTimeout(function(){ $.alerts._hide(); }, 1000);
						});
					}
				});
			}
			return false;
		}
	};
})();
