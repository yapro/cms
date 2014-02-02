$(document).ready(function(){
	$(".mousewheelSelect SELECT").mousewheel(function(event, delta){
		
		var l = $("option",this).length;// ���������� ��������� OPTION
		
		var o = $(this)[0].selectedIndex;// ����� �������� ��������� �������� OPTION
		
		$("option:eq("+o+")", this).removeAttr('selected');// ������� ����� � �������� ���������� �������� OPTION
		
		if(delta > 0){
			
			if( (o+1)==l){ o = l-1; }else{ o += 1; }
			
		}else{
			
			if(o<=0){ o = 0; }else{ o -= 1; }
			
		}
		
		$("option:eq("+o+")", this).attr("selected","selected");// �������� ������ ������� OPTION
		
		return false;
	});
});
