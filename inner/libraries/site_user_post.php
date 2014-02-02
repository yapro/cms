<?php
// библиотека для работы с данными блога юзера

// функция возвращает обработанные POST-данные пользователя
function site_user_post($text=''){
	
	if(!$text){ return ; }
	//log_(__FILE__.'=1='.$text);
	
	if(!$GLOBALS['blog']['video_width']){ $GLOBALS['blog']['video_width'] = 700; }// максимальна ширина видео
	if(!$GLOBALS['blog']['video_height']){ $GLOBALS['blog']['video_height'] = 1234; }// максимальна высота видео
	
	$text = str_replace('<p>&nbsp;</p>', '', $text);// избавляемся от пустых строк
	
	$delete = array('[', ']', '"');// реплэйс для защиты
	
	preg_match_all('/<blockquote>(.+)<\/blockquote>/sUi', $text, $found);// первым делом сохраняем код
	//-log_(print_r($found,1));
	if($found['1']){
		foreach($found['0'] as $k=>$v){// здесь удаляется так же <!-- -->
			$code = '<blockquote>'.str_replace("\n", '<br />', trim(strip_tags(str_replace('<br />', "\n", str_replace('<p>', '', str_replace('</p>', '<br>', $v)))))).'</blockquote>';
			$text = str_replace($v, HTMLSave($code), $text);
		}
	}
	//log_(__FILE__.'=blockquote='.$text);
	//log_(print_r($GLOBALS['HTML']['before'],1));
	// возможно кто-то запостит например видео прямиком в визуальный редактор:
	$text = str_replace('&amp;amp;', '&amp;', str_replace('&lt;', '<', str_replace('&gt;', '>', $text)));
	
	$video = array('video.google.com'=>1, 'youtube.com'=>1, 'www.youtube.com'=>1, 'vimeo.com'=>1, 'www.vimeo.com'=>1, 'player.vimeo.com'=>1, 'metacafe.com'=>1, 'www.metacafe.com'=>1, 'smotri.com'=>1, 'www.smotri.com'=>1, 'rutube.ru'=>1, 'www.rutube.ru'=>1, 'video.rutube.ru'=>1, 'myspacetv.com'=>1, ''=>1, 'vids.myspace.com'=>1, 'smotri.com'=>1, 'www.smotri.com'=>1, 'video.yahoo.com'=>1, 'gametrailers.com'=>1, 'www.gametrailers.com'=>1, 'gamespot.com'=>1, 'www.gamespot.com'=>1, 'blip.tv'=>1, 'www.blip.tv'=>1, 'vkontakte.ru'=>1, 'videosostav.ru'=>1);//, 'www.f5.ru' => 1
	
	$width_height = array('width','height');
	
	preg_match_all('/<object.+<\/object>/sUi', $text, $found);
	if($found['0']){
		$object = array();
		foreach($found['0'] as $k=>$v){
			preg_match_all('/<param.+name="(src|movie)".+value="(.+)"/sUi', $v, $param);
			if($param['2']['0']){
				$src = str_replace($delete, '', $param['2']['0']);
				$domain = explode('/', $src);// /
				if(!$domain['2'] || !$video[ $domain['2'] ]){
					continue;
				}
				$object[$k]['src'] = $src;
				foreach($width_height as $name){
					$x = explode($name.'="', $v);
					if($x['1']){
						$x = explode('"', $x['1']);
						if($x['0'] && is_numeric($x['0']) && !strstr($x['0'],'.') && $x['1']){
							$x = $x['0'];
						}
					}
					$object[$k][$name] = (int)$x;
				}
				$r = $object[$k];
				//-----------------------------
				
				//$hash = HTMLSave('<object data="'.$object[$k]['src'].'" type="application/x-shockwave-flash" height="'.($r['height']? $r['height']:'250').'" width="'.($r['width']? $r['width']:'250').'"><param name="data" value="'.$object[$k]['src'].'" /><param name="src" value="'.$object[$k]['src'].'" /></object>');
				
				$width = $r['width']? (($r['width'] > $GLOBALS['blog']['video_width'])? $GLOBALS['blog']['video_width'] : $r['width']) : 350;
				$height = $r['height']? (($r['height'] > $GLOBALS['blog']['video_height'])? $GLOBALS['blog']['video_height'] : $r['height']) : 250;
				
				if($GLOBALS['blog']['video_width_min'] && $width < $GLOBALS['blog']['video_width_min']){
					$height = (int)(($GLOBALS['blog']['video_width_min']*$height)/$width);
					$width = $GLOBALS['blog']['video_width_min'];
				}
				
				$hash = HTMLSave('<object width="'.$width.'" height="'.$height.'"><param name="movie" value="'.$r['src'].'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="'.$r['src'].'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>');
				
				$text = str_replace($v, $hash, $text);
				
				//$text = str_replace($v, '[flash='.($r['width']? $r['width']:'250').','.($r['height']? $r['height']:'250').']'.str_replace($delete, '', $r['src']).'[/flash]', $text);
			}
		}
	}
	
	// находим embed-видео
	preg_match_all('/<embed.+>/sUi', $text, $found);//
	//-log_(__FILE__.'=embed='.print_r($found,1).'=='.$text);
	if($found['0']){
		foreach($found['0'] as $k=>$v){
			$ex = explode('src="', $v);
			if($ex['1']){
				$ex = explode('"', $ex['1']);
				if($ex['0']){
					$object = array();
					$src = str_replace($delete, '', $ex['0']);
					$domain = explode('/', $src);
					if(!$domain['2'] || !$video[ $domain['2'] ]){
						continue;
					}
					$object[$k]['src'] = $src;
					foreach($width_height as $name){// находим ширину и высоту
						$x = explode($name.'="', $v);
						if($x['1']){
							$x = explode('"', $x['1']);
							if($x['0'] && is_numeric($x['0']) && !strstr($x['0'],'.') && $x['1']){
								$x = $x['0'];
							}
						}
						$object[$k][$name] = (int)$x;
					}
					$r = $object[$k];
					//-----------------------------
					
					$width = $r['width']? (($r['width'] > $GLOBALS['blog']['video_width'])? $GLOBALS['blog']['video_width'] : $r['width']) : 350;
					$height = $r['height']? (($r['height'] > $GLOBALS['blog']['video_height'])? $GLOBALS['blog']['video_height'] : $r['height']) : 250;
					
					if($GLOBALS['blog']['video_width_min'] && $width < $GLOBALS['blog']['video_width_min']){
						$height = (int)(($GLOBALS['blog']['video_width_min']*$height)/$width);
						$width = $GLOBALS['blog']['video_width_min'];
					}
					
					$hash = HTMLSave('<object width="'.$width.'" height="'.$height.'"><param name="movie" value="'.$r['src'].'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="'.$r['src'].'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>');
					
					$text = str_replace($v, $hash, $text);
				}
			}
		}
	}
	//log_(__FILE__.'=2='.$text);
	preg_match_all('/<iframe.+>/sUi', $text, $found);
	if($found['0']){
		foreach($found['0'] as $k=>$v){
			$ex = explode('src="', $v);
			if($ex['1']){
				$ex = explode('"', $ex['1']);
				if($ex['0']){
					$object = array();
					$src = str_replace($delete, '', $ex['0']);
					$domain = explode('/', $src);
					if(!$domain['2'] || !$video[ $domain['2'] ]){
						continue;
					}
					$object[$k]['src'] = $src;
					foreach($width_height as $name){// находим ширину и высоту
						$x = explode($name.'="', $v);
						if($x['1']){
							$x = explode('"', $x['1']);
							if($x['0'] && is_numeric($x['0']) && !strstr($x['0'],'.') && $x['1']){
								$x = $x['0'];
							}
						}
						$object[$k][$name] = (int)$x;
					}
					$r = $object[$k];
					//-----------------------------
					
					$width = $r['width']? (($r['width'] > $GLOBALS['blog']['video_width'])? $GLOBALS['blog']['video_width'] : $r['width']) : 350;
					$height = $r['height']? (($r['height'] > $GLOBALS['blog']['video_height'])? $GLOBALS['blog']['video_height'] : $r['height']) : 250;
					
					$hash = HTMLSave('<iframe src="'.$r['src'].'" frameborder="0" scrolling="no" width="'.$width.'" height="'.$height.'" hspace="0" vspace="0" marginwidth="0" marginheight="0"></iframe>');
					
					$text = str_replace($v, $hash, $text);
				}
			}
		}
	}
	/*preg_match_all('/<iframe.+src="(.+)".+>/sUi', $text, $found);
	if($found['1']){
		foreach($found['1'] as $k=>$v){
			
			$v = str_replace($delete, '', $v);
			
			$text = str_replace($found['0'][$k], HTMLSave('<a href="'.$v.'">'.$v.'</a>'), $text);
			
			//$text = str_replace($found['0'][$k], '[url='.$v.']'.$v.'[/url]', $text);
		}
	}*/
	//log_(__FILE__.'=3='.$text);
	// находим ссылки с возможными в них изображениями
	preg_match_all('/<a.+>/sUi', $text, $found);
	//log_(__FILE__.'=А='.print_r($found,1).'=='.$text);
	if($found['0']){
		foreach($found['0'] as $k=>$v){
			$ex = explode('href="', $v);
			if($ex['1']){
				$ex = explode('"', $ex['1']);
				if($ex['0']){
					
					if(mb_substr($ex['0'],0,8)=='uploads/'){ $ex['0'] = '/'.$ex['0']; }//addon
					
					if(mb_substr($ex['0'],0,6)=='forum/'){ $ex['0'] = '/'.$ex['0']; }//addon
					
					$text = str_replace($found['0'][$k], HTMLSave('<a href="'.str_replace($delete, '', $ex['0']).'">'), $text);
				}
			}
		}
	}
	//log_(__FILE__.'=А='.$text);
	// находим изображения с возможными в них изображениями
	preg_match_all('/<img.+>/sUi', $text, $found);//preg_match_all('/<img.+src="(.+)".*>/sUi', $text, $found);
	//-log_(__FILE__.'=img='.print_r($found,1).'=='.$text);
	if($found['0']){
		foreach($found['0'] as $k=>$v){
			$ex = explode('src="', $v);
			if($ex['1']){
				$ex = explode('"', $ex['1']);
				if($ex['0']){
					
					if(mb_substr($ex['0'],0,6)=='forum/'){ $ex['0'] = '/'.$ex['0']; }
					
					$align = (stristr($v,'float: right;'))? ' style="float: right; margin: 0 0 5px 5px;"' : ((stristr($v,'float: left;'))? ' style="float: left; margin: 0 5px 5px 0;"' : '');
					
					$width = '';
					$ex_w = explode('width="', $v);
					if($ex_w['1']){
						$ex_w = explode('"', $ex_w['1']);
						if(is_numeric($ex_w['0'])){
							$width = ' width="'.$ex_w['0'].'"';
						}
					}
					
					$text = str_replace($found['0'][$k], HTMLSave('<img src="'.str_replace($delete, '', $ex['0']).'" border="0"'.$align.$width.'>'), $text);
				}
			}
		}
	}
	// находим параграфы с отступами
	preg_match_all('/<p.*>/sUi', $text, $found);//.+style="padding-left: ([\d].+)px;"
	//-log_(__FILE__.'=p='.print_r($found,1).'=='.$text);
	if($found['0']){
		foreach($found['0'] as $k=>$v){
			$ex = explode('style="padding-left: ', $v);
			if($ex['1']){
				$ex = explode('px;"', $ex['1']);
				if(is_numeric($ex['0'])){
					$text = str_replace($found['0'][$k], HTMLSave('<p style="padding-left:'.$ex['0'].'px;">'), $text);
				}
			}
			$ex = explode('style="text-align: ', $v);
			if($ex['1']){
				$ex = explode(';"', $ex['1']);
				if($ex['0']=='left' || $ex['0']=='right'){
					$text = str_replace($found['0'][$k], HTMLSave('<p style="text-align:'.$ex['0'].';">'), $text);
				}
			}
		}
	}
	// находим оформление текста цветом
	preg_match_all('/<span.*>/sUi', $text, $found);//.+style="color: #([\d].+);"
	//-log_(__FILE__.'=span='.print_r($found,1).'=='.$text);
	if($found['0']){
		foreach($found['0'] as $k=>$v){
			$ex = explode('style="color: #', $v);
			if($ex['1']){
				$ex = explode(';"', $ex['1']);
				//if(eregi('^[a-zA-Z0-9]+$', $ex['0'])){
				if(preg_match('/^[a-zA-Z0-9]+$/', $ex['0'])){
					$text = str_replace($found['0'][$k], HTMLSave('<span style="color:#'.$ex['0'].';">'), $text);
				}
			}
		}
	}
	//log_(__FILE__.'=<span='.$text);
	if($table_tag = preg_replace('/<table.*>/sUei', "HTMLSave('<table class=\"cellpadding5 styleTable\" border=\"0\">')", $text)){
		$text = $table_tag;
	}
	//log_(__FILE__.'=table='.$text);
	if($table_tag = preg_replace('/<tr.*>/sUei', "HTMLSave('<tr>')", $text)){
		$text = $table_tag;
	}
	//log_(__FILE__.'=tr='.$text);
	if($table_tag = preg_replace('/<td.*>/sUei', "HTMLSave('<td>')", $text)){
		$text = $table_tag;
	}
	//log_(__FILE__.'=td='.$text);
	
	$text = str_replace('<br /><br />', HTMLSave('</p><p>'), $text);//addon
	$text = str_replace('<span style="text-decoration: underline;">', HTMLSave('<span style="text-decoration: underline;">'), $text);
	$text = str_replace('<!-- pagebreak -->', HTMLSave('<!-- pagebreak -->'), $text);
	$text = str_replace('<b>', HTMLSave('<b>'), str_replace('</b>', HTMLSave('</b>'), $text));
	$text = str_replace('<br />', HTMLSave('<br />'), $text);
	$text = str_replace('<hr />', HTMLSave('<hr />'), $text);
	
	//log_(__FILE__.'=4='.$text);//'blockquote'=>'quote', 
	$light = array('i'=>'i', 'u'=>'u', 'code'=>'code', 'ol'=>'ol', 'ul'=>'ul', 'li'=>'li', 'strike'=>'strike', 'p'=>'p', 'div'=>'div', 'br'=>'br', 'strong'=>'b', 'span'=>'span', 'em'=>'em', 'a'=>'a', 'table'=>'table', 'tr'=>'tr', 'td'=>'td');
	foreach($light as $k=>$v){
		
		$text = str_replace('<'.$k.'>', HTMLSave('<'.$v.'>'), $text);
		
		$text = str_replace('</'.$k.'>', HTMLSave('</'.$v.'>'), $text);
		//-log_(__FILE__.'=4.1==('.$v.'=='.$k.')=='.$text);
		//$text = str_replace('<'.$v.'>', '['.$k.']', str_replace('</'.$v.'>', '[/'.$k.']', $text));
	}
	//log_(__FILE__.'=5='.$text);
	$text = strip_tags($text);
	//log_(__FILE__.'=6='.$text);
	
	$text = str_replace('['.$GLOBALS['HTML']['uniqHTMLSave'], ' ['.$GLOBALS['HTML']['uniqHTMLSave'], $text);// добавляем пробел перед [хэш
	
	// находим автоматические текстовые ссылки и ссылки на изображения (превращая их в обычные изображения)
	preg_match_all("/(http|ftp|https):\/\/[^<>[:space:]]+[[:alnum:]а-яА-Я\/]/u", $text, $found);//[[:alpha:]]+
	//-log_(__FILE__.'=span='.print_r($found,1));
	if($found['0']){
		
		$images = array('gif'=>1,'png'=>1,'bmp'=>1,'jpg'=>1,'jpeg'=>1);
		
		foreach($found['0'] as $k=>$v){
			
			$v = str_replace($delete, '', $v);
			
			$ext = ext($v);
			//-log_(__FILE__.'=$ext='.$ext);
			if($ext && $images[ $ext ]){
				$v = ' <img src="'.$v.'" border="0">';
			}else{
				$v = ' <a href="'.$v.'">'.$v.'</a>';
			}
			//-log_(__FILE__.'=$found[0][$k]='.$found['0'][$k]);
			//-log_(__FILE__.'=$v='.$v);
			$text = str_replace($found['0'][$k], HTMLSave($v), $text);
			//-log_(__FILE__.'=$text='.$text);
		}
	}
	//-log_(__FILE__.'=before_text_links='.$text);
	$text = str_replace(' ['.$GLOBALS['HTML']['uniqHTMLSave'], '['.$GLOBALS['HTML']['uniqHTMLSave'], $text);// убираем пробел перед [хэш
	
	//log_(__FILE__.'=after_text_links='.$text);
	$text = HTMLBack($text);
	//log_(__FILE__.'=7='.$text);
	return $text;
}
?>
