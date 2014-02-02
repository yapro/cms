<?php
$document = '<input name="time_modified" type="text" value="'.date('d.m.Y H:i:s', time()).'" class="datepickerTimeField">'.($data['time_modified']?'&nbsp;&nbsp;&nbsp;&nbsp;Последнее изменение: '.date('d.m.Y H:i:s', $data[ $f['name'] ]):'').'';
?>
