<?php		
	if(!isset($_SESSION['mgrValidated'])){ die();}
	
	if (!function_exists('get_output'))
	{
		function get_output($config)
		{			
			global $modx;	
			if ($config['user_func']) return $modx->runSnippet($config['user_func'], $config);				
			extract($config);		
			
			switch($mode)
			{
				case 'output':				
				switch ($type)
				{
					case 'mediumtext':
					$return = mb_substr(strip_tags($value),0,75).'...';
					break;
					
					case 'number':
					if (!$value) $return = 'не задано';
					break;
					
					case 'date':
					
					if ($value)
					{
						$d = gmdate("d", $value); 
						$m = gmdate("m", $value); 
						if ($m==1) $m = 'января';
						if ($m==2) $m = 'февраля';
						if ($m==3) $m = 'марта';
						if ($m==4) $m = 'апреля';
						if ($m==5) $m = 'мая';
						if ($m==6) $m = 'июня';
						if ($m==7) $m = 'июля';
						if ($m==8) $m = 'августа';
						if ($m==9) $m = 'сентября';
						if ($m==10) $m = 'октября';
						if ($m==11) $m = 'ноября';
						if ($m==12) $m = 'деабря';
						$y = gmdate("Y", $value); 
						$return = $d.' '.$m.' '.$y; 
					}
					else $return = 'не задано';
					break;
					
					case 'image':						
					
					if ((file_exists(MODX_BASE_PATH.$value)) && ($value)) 
					{
						
						if (file_exists(MODX_BASE_PATH.'assets/plugins/evocollection/cache/'.$value)) 
						{
							return '<img src="./../assets/plugins/evocollection/cache/'.$value.'" width="64" height="64">';
						}
						else return '<i class="fa fa-spinner fa-spin noimgs" data-href="'.$value.'"></i>';
					}
					else return '<img src="./../assets/snippets/phpthumb/noimage.png" width="64" height="64">';					
					break;
					
					case 'file':						
					if ($value)
					{
						$path = MODX_BASE_PATH.''.$value;
						$name = basename($path);
						$a = explode('?',$name);
						$return = $a[0];
					}
					else $return = 'не указан';
					break;
					
					case 'richtext':							
					$value = mb_substr(strip_tags($value),0,75).'...';
					$value = str_replace('[','&#91;',$value);
					$value = str_replace(']','&#93;',$value);
					$return = $value;
					break;									
					
					case 'textarea':					
					$value = mb_substr(strip_tags($value),0,75).'...';
					$value = str_replace('[','&#91;',$value);
					$value = str_replace(']','&#93;',$value);
					$return = $value;
					break;
					
					case 'oncecheckbox':
					if ($value) return '<div style="text-align:center;">Да</div>';
					else return '<div style="text-align:center;">Нет</div>';
					break;
					
					case 'select':					
					$s='Не выбрано';
					foreach(explode($delimiter,$elements) as $str)
					{
						
						$col = explode('==',$str);
						if (isset($col[1]))
						{
							if ($col[1]==$value) $s= $col[0];							
						}
						else 
						{
							if ($col[0]==$value) $s= $col[0];							
						}
					}
					return $s;
					break;

					
					default:
					$return = strip_tags($value);
					break;		
					
				}
				if ((!$return) && (!$value)) $return = '<div class="extender">не задан</div>';
				break;
				
				
				
				
				case 'input':
				$value = str_replace('"','\"',$value);				
					switch($type)
					{
						default:
						
						$value = htmlspecialchars($value);
						$return = '<input type="text" value="'.$value.'">';
						break;
						
						
						case 'mediumtext':
						$return = mb_substr(strip_tags($value),0,75).'...';
						break;
						
						case 'number':
						$return = '<input type="number" value="'.strip_tags($value).'">';
						break;
						
						case 'date':						
						if ($value>0)
						{
							$value = gmdate("Y-m-d", $value); 
							$return = '<input type="date" value="'.strip_tags($value).'">';
						}
						else $return = '<input type="date">';
						break;
						
						case 'image':						
						$return = '<input type="text" id="tv_'.$field.'_'.$did.'" name="tv_'.$field.'_'.$did.'" value="'.strip_tags($value).'" data-id="tv_'.$field.'_'.$did.'" class="browser" data-browser="images">';
						break;
						
						case 'file':						
						$return = '<input type="text" id="tv_'.$field.'_'.$did.'" name="tv_'.$field.'_'.$did.'" value="'.strip_tags($value).'" data-id="tv_'.$field.'_'.$did.'" class="browser" data-browser="files">';
						break;
						
						case 'textarea':
						$return = '<div class="rte" data-type="textarea" id="ta_'.$field.'_'.$did.'"></div>';
						break;
						
						case 'richtext':
						$return = '<div class="rte" data-type="rte" id="ta_'.$field.'_'.$did.'"></div>';
						break;
						
						case 'oncecheckbox':
						if ($value) $chk=' checked="checked"';
						$return = '<input type="checkbox" value="1"'.$chk.' style="margin:0 auto;">';
						break;
						
						case 'select':
						$s = '<select>';					
						foreach(explode($delimiter,$elements) as $str)
						{
							$col = explode('==',$str);
							if (isset($col[1]))
							{
								if ($col[1]==$value) $s.= '<option value="'.$col[1].'" selected="selected">'.$col[0].'</option>';
								else $s.= '<option value="'.$col[1].'">'.$col[0].'</option>';
							}
							else 
							{
								if ($col[0]==$value) $s.= '<option value="'.$col[0].'" selected="selected">'.$col[0].'</option>';
								else $s.= '<option value="'.$col[0].'">'.$col[0].'</option>';
							}
						}
						return $s.'</select>';
						break;
						
					}
				break;
				
				case 'execute':							
					switch ($type) 
					{			
						case 'date':
						$startdate = date($value." 12:00:00");
						return strtotime($startdate);				
						break;
						
						case 'oncecheckbox':
						if ($value) return 1;
						else return 0;
						break;

					}			
				break;
			}	
			
			if (!$return) $return=$value;				
			return $return;				
		}		
	}
