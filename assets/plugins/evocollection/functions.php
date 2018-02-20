<?php		
	if (!function_exists('get_output'))
	{
		function get_output($config)
		{			
			global $modx;	
			if ($config['user_func']) return $modx->runSnippet($config['user_func'], $config);				
			extract($config);		
			
			
			if ($mode=='output')
			{	
				if (($type=='default') || ($type=='text'))
				{
					$return = strip_tags($value);
				}
				if ($type=='mediumtext') 
				{
					$return = mb_substr(strip_tags($value),0,75).'...';
				}
				if ($type=='number') 
				{
					if (!$value) $return = 'не задано';
				}
				if ($type=='date') 
				{
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
				}
				if ($type=='image') 
				{	
					
					if ((file_exists(MODX_BASE_PATH.$value)) && ($value)) 
					{
						
						if (file_exists(MODX_BASE_PATH.'assets/plugins/evocollection/cache/'.$value)) 
						{
							return '<img src="./../assets/plugins/evocollection/cache/'.$value.'" width="64" height="64">';
						}
						else return '<i class="fa fa-spinner fa-spin noimgs" data-href="'.$value.'"></i>';
					}
					else return '<img src="./../assets/snippets/phpthumb/noimage.png" width="64" height="64">';					
				}
				if ($type=='file') 
				{
					if ($value)
					{
						$path = MODX_BASE_PATH.''.$value;
						$name = basename($path);
						$a = explode('?',$name);
						$return = $a[0];
					}
					else $return = 'не указан';
				}
				if (($type=='richtext') || ($type=='textarea'))
				{
					
					$value = mb_substr(strip_tags($value),0,75).'...';
					$value = str_replace('[','&#91;',$value);
					$value = str_replace(']','&#93;',$value);
					$return = $value;
				}				
				if ((!$return) && (!$value)) $return = '<div class="extender">не задан</div>';
			}
			
			
			
			
			if ($mode=='input')
			{				
				if (($type=='default') || ($type=='text'))
				{
					$value = htmlspecialchars($value);
					$return = '<input type="text" value="'.$value.'">';
				}
				
				$value = str_replace('"','\"',$value);
				if ($type=='mediumtext') 
				{
					$return = mb_substr(strip_tags($value),0,75).'...';
				}
				if ($type=='number') 
				{
					$return = '<input type="number" value="'.strip_tags($value).'">';
				}
				if ($type=='date') 
				{
					if ($value>0)
					{
						$value = gmdate("Y-m-d", $value); 
						$return = '<input type="date" value="'.strip_tags($value).'">';
					}
					else $return = '<input type="date">';
				}
				
				if ($type=='image') 
				{
					$return = '<input type="text" id="tv_'.$field.'_'.$did.'" name="tv_'.$field.'_'.$did.'" value="'.strip_tags($value).'" data-id="tv_'.$field.'_'.$did.'" class="browser" data-browser="images">';
				}
				
				if ($type=='file') 
				{
					$return = '<input type="text" id="tv_'.$field.'_'.$did.'" name="tv_'.$field.'_'.$did.'" value="'.strip_tags($value).'" data-id="tv_'.$field.'_'.$did.'" class="browser" data-browser="files">';
				}
				if ($type=='textarea') 
				{
					$return = '<div class="rte" data-type="textarea" id="ta_'.$field.'_'.$did.'"></div>';
				}
				
				if ($type=='richtext') 
				{
					$return = '<div class="rte" data-type="rte" id="ta_'.$field.'_'.$did.'"></div>';
				}
			}
			
			if ($mode=='execute')
			{			
				if ($type=='date') 
				{				
					$startdate = date($value." 12:00:00");
					return strtotime($startdate);				
				}			
			}
			
			
			
			if (!$return) $return=$value;			
			
			
			return $return;
		}	
	}		
