// ���� ������� ��������� "����������� redirect � URL ����� ������", �� ������ ��� ����� �������� ����� ��������� �����
$(".redirect").each(function(){
	var redirect = $(this).attr("href").split("/system/redirect?to=")[1];
	if(redirect){
		$(this).attr("href", redirect);
	}
});