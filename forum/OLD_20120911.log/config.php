<?php
if(stristr($_SERVER['REQUEST_URI'], '//')){ header('location: http://'. $_SERVER['HTTP_HOST'].'/404'); exit; }// ��� ������ ������������ ���
$acm_type = 'file';
$load_extensions = '';
$GLOBALS['system_include'] = true;
include_once($_SERVER['DOCUMENT_ROOT'].'/index.php');
$table_prefix = F;// ������� ������ ������
?>