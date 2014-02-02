<?php
// плагин возвращает уникальное имя куки для пометки пользователя как не бота

error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

include_once($_SERVER['DOCUMENT_ROOT'].'/inner/libraries/functions.php');// основные функции

@header("content-type:application/x-javascript; charset=UTF-8");// кодировка страницы

if($_COOKIE[ $GLOBALS['notBot'] ]){ exit; }

$host = host_clear($_SERVER['HTTP_HOST']);

if(!strstr($host,'.')){ $host = ''; }// на случай тестирования в доменах вида http://localhost/ или http://test/

setcookie($GLOBALS['notBot'], 1, (time()+5555555), '/', $host);// устанавливаем на 2 месяца
?>
// Функция выбора нужной куки
function GetCookieDateStatistics(name_to_get) {
    var cookie_pair;
    var cookie_name;
    var cookie_value;
    // Разбиваем куку в массив
    var cookie_array = document.cookie.split('; ')
    // Пробегаем по массиву кук
    for (var counter = 0; counter < cookie_array.length; counter++) {
        // Разбиваю куку на имя/значение
        cookie_pair = cookie_array[counter].split('=');
        cookie_name = cookie_pair[0];
        cookie_value = cookie_pair[1];
        // Сравниваем имя куки с тем именем, которое нужно нам
        if (cookie_name == name_to_get) {
            return cookie_value;// Если нашли нужную нам куку, то возвращаем её значение
        }
    }
    // Если куку не нашли, возвращаем null
    return 'null';
}
var notBot = GetCookieDateStatistics("<?php echo $GLOBALS['notBot']; ?>");
if(notBot != null && notBot != 'null' && notBot != '' && notBot != ' ' && typeof(notBot)=='string'){
	$(".captcha_comments").hide();
}
