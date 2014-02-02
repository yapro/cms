<div class="col2">
	<!-- Column 2 start -->
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td valign="top" id="search_right">
				<ul id="menu_left">
<?php
// создает меню древовидного вида UL LI, с сортировкой position ASC

$GLOBALS['lebnik_menu_left'] = '';//'<div class="MenuTop"></div><table border="0" cellpadding="0" cellspacing="0" class="MenuLink"><tr><td class="MenuLeft"></td><td class="MenuCenter">';
$GLOBALS['lebnik_menu_rigth'] = '';//'</td><td class="MenuRight"></td></tr></table><div class="MenuBottom"></div>';

$file = $GLOBALS['system_cache']-> file_(substr(__FILE__,0,-3),'html');// формируем имя кэш-файла

if($GLOBALS['system_cache']->name['pages'] && $GLOBALS['system_cache']->name['pages'] < filemtime($file)){
	
	$d = @file_get_contents($file);
	
}else{
	
	class lebnik_menu{
		function lebnik_menu(){
			if($q = mysql_(sql('page_id, parent_id, name', 'menu != 0', 'menu'))){
				while($r = mysql_fetch_assoc($q)){
					$this->parent_id__page_id[ $r['parent_id'] ][ $r['page_id'] ] = $r['name'];
				}
				if($this->parent_id__page_id){
					$this->explorer();
				}
			}
		}
		function explorer($parent_id=0){
			
			if(!$this->parent_id__page_id[ $parent_id ]){ return false; }
			
			$i = 0;
			$count = count($this->parent_id__page_id[ $parent_id ]);
			
			foreach($this->parent_id__page_id[ $parent_id ] as $page_id => $name){
				
				$i++;
				
				$class = '';
				
				if(!$this->i){
					$class .= ' root-item';
				}
				
				if(!$this->i && $i==$count){
					$class .= ' root-item-latest';
				}
				
				$url = ($page_id == $GLOBALS['SYSTEM']['config']['yapro_index_id'])? '/' : url($page_id);
				
				$this->s .= '<li'.($class?' class="'.trim($class).'"':'').'>'.($this->i? $GLOBALS['lebnik_menu_left']:'').'<a href="'.$url.'" alt="'.$page_id.'">'.$name.'</a>'.($this->i? $GLOBALS['lebnik_menu_rigth']:'');
				
				if($this->parent_id__page_id[ $page_id ]){
					
					$this->i++;
					
					$this->s .= '<ul class="nesting'.$this->i.'">';
					
					$this-> explorer($page_id);
					
					$this->s .= '</ul>';
					
					$this->i--;
					
				}
				$this->s .= '</li>';
			}
		}
	}
	$lebnik_menu = new lebnik_menu();
	$d = $lebnik_menu->s;
	write_($d, $file);
}
echo str_replace('alt="'.$GLOBALS['system']->id.'"', 'class="selected"', $d);
?>
				</ul>
				<style>
				.QUESTIONS { padding-left: 30px !important; }
				.POLLFORM .VARIANTS { padding-left: 10px !important; }
				.POLLFORM TABLE { margin-left: 30px !important; }
				</style>
				{~$poll_id=1;~AND~poll.php~}
			</td>
		</tr>
	</table>
	<!-- Column 2 end -->
</div>