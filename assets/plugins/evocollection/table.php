<?php
	
	if (!$_SESSION[mgrInternalKey]) { die('What are you doing? Get out of here!'); }	
	if (!$_GET[id]) return;	
	if ($_GET['a']==4) return;	
	if(($_GET['act']) && ($_GET['docid']))
	{
		$ids = implode(',',$_GET['docid']);
		if ($ids)
		{
			if ($_GET['act']=='del')  $modx->db->query('Update '.$modx->getFullTableName('site_content').' set deleted=1 where id in ('.$ids.')');
			if ($_GET['act']=='restore')  $modx->db->query('Update '.$modx->getFullTableName('site_content').' set deleted=0 where id in ('.$ids.')');
			if ($_GET['act']=='pub')  $modx->db->query('Update '.$modx->getFullTableName('site_content').' set published=1 where id in ('.$ids.')');
			if ($_GET['act']=='unpub')  $modx->db->query('Update '.$modx->getFullTableName('site_content').' set published=0 where id in ('.$ids.')');	
		}
	}
	
	$cf = array();
	
	
	$output.='';
	
	$tid = $modx->db->getValue('Select template from '.$modx->getFullTableName('site_content').' where id='.$id);
	
	foreach($configuration as $key => $conf)
	{
		if (($conf['type']=='ids') && ($conf['value']))
		{
			$arr = explode(',',$conf['value']);
			if (in_array($id,$arr))
			{					
				if (count($conf[fields]))
				{
					$fields_a = array('id');
					foreach($conf[fields] as $k => $v) if ($k!='id') $fields_a[] = $k;
					$fields = implode(',',$fields_a);
					$idc = $key;
					break;
				}
			}
		}
		if (($conf['type']=='template') && ($conf['value']))
		{
			$arr = explode(',',$conf['value']);
			if (in_array($tid,$arr))
			{					
				if (count($conf[fields]))
				{
					$fields_a = array('id');
					foreach($conf[fields] as $k => $v) if ($k!='id') $fields_a[] = $k;
					$fields = implode(',',$fields_a);
					$idc = $key;
					break;
				}
			}
		}
		
	}
	
	if (!$fields) return;
	
	
	// Filed for sort
	if ($_GET['sorter']) $sorter = $_GET['sorter'];
	else 
	{
		if ($configuration[$idc]['sort'])
		{
			$c = $modx->db->getValue('SELECT count(*) FROM '.$modx->getFullTableName('site_tmplvars').' where name="'.$configuration[$idc]['sort'].'"');
			if ($c) $soretr = 'tv.'.$configuration[$idc]['sort'];
			else 
			{
				if ($modx->db->getValue('SHOW COLUMNS FROM '.$modx->getFullTableName('site_content').' where Field="'.$configuration[$idc]['sort'].'"')) $sorter = 'c.'.$configuration[$idc]['sort'];
				else $sorter='c.pagetitle';	
			}
		}
		else $sorter='c.pagetitle';	
	}
	
	// direction for sort
	if ($_GET['direction']) $direction = $_GET['direction'];
	else 
	{
		if ($configuration[$idc]['direction']) $direction = $configuration[$idc]['direction'];
	}
	
	// Limit to show
	if ($configuration[$idc]['limit']) $limit = $configuration[$idc]['limit'];
	else $limit=10;	
	if ($_GET['show']) 
	{  
		if ($_GET['show']!='all')
		{
			$start = ($_GET['page']-1)*$limit;
			if ($_GET['page']) $l = 'LIMIT '.$start.', '.$_GET['show'];
			else $l='LIMIT 0,'.$_GET['show'];
		}
		
	}
	else
	{
		$start = ($_GET['page']-1)*$limit;
		if ($_GET['page']) $l = 'LIMIT '.$start.', '.$limit;
		else $l='LIMIT 0,'.$limit;
	}
	
	
	$prefix = $modx->db->config[table_prefix];
	$lng = include MGR_DIR.'/includes/lang/'.$modx->config[manager_language].'.inc.php'; 
	
	
	$res = $modx->db->query('select `column_name`,`column_type` from INFORMATION_SCHEMA.Columns where table_name = "'.$prefix.'site_content" order by ordinal_Position');
	while($row = $modx->db->getRow($res))
	{
		$at = explode('(',$row['column_type']);
		$type = $at[0];
		if ($at[1]) 
		{
			$len = str_replace(')','',$at[1]);
			if ($len==1) $type = 'yn'; // Yes/No - checkbox				
		}
		if (($type=='int') or ($type=='tinyint')) $type = 'number';
		if (($type=='varchar') or ($type=='text'))  $type = 'text';
		if ($_lang[$row['column_name']]) $caption = $_lang[$row['column_name']];
		else $caption = $row['column_name'];
		$cf[$row['column_name']] = array('type'=>$type,'length'=>$len,'caption'=>$caption);	
		
		
	}
	
	$res = $modx->db->query('Select `id`,`type`,`name`,`caption`,`description`,`elements`,`display_params` from '.$modx->getFullTableName('site_tmplvars'));
	
	while($row = $modx->db->getRow($res))
	{
		$delim =explode('&format=',$row['display_params']);
		if ($delim[1]) $delimiter = $delim[1];
		else $delimiter = '||';
		$cf[$row['name']] = array('type'=>$row['type'],'table'=>'tv','tmplvarid'=>$row['id'],'caption'=>$row['caption'],'elements'=>$row['elements'],'delimiter'=>$delimiter);		
	}
	
	
	$array = explode(',',$fields);	
	
	$tv_fields = array();
	$tv_join = array();
	$c_fields = array();
	$ff = array();	
	foreach($array as $key => $val)	
	{
		
		if($cf[$val]['table']=='tv')
		{
			$tv_fields[] = "tv".$key.".value as '".$val."'";
			$tv_join[] = "left join ".$modx->getFullTableName('site_tmplvar_contentvalues')." as tv".$key." ON c.id = tv".$key.".contentId and tv".$key.".tmplvarid = ".$cf[$val]['tmplvarid']; 
			$ff[]=$val;
		}
		else if ($cf[$val]) {$c_fields[] = 'c.'.$val; $ff[]=$val;}
	}
	
	
	$fa_sql = array_merge($tv_fields,$c_fields);	
	
	$fsql = implode(',',$fa_sql);	
	if (!array_key_exists('id',$fa_sql)) $fsql='c.id,'.$fsql;	
	if (!array_key_exists('deleted',$fa_sql)) $fsql='c.deleted,'.$fsql;		
	
	if ($_GET['onlyid']) 
	{
		$onlyid = 'and c.id='.$_GET['onlyid'];
		$getsr = 'id="getstr"';
	}
	
	$sql = "SELECT ".$fsql." FROM ".$modx->getFullTableName('site_content')." as c ".implode(' ',$tv_join)." where c.parent=".$id." ".$onlyid."  order by ".$sorter." ".$direction." ".$l;
	
	$tbl='<div class="row"><div class="table-responsive"><table class="table data" id="table_doc"><thead><tr class="">';
	
	// Head table
	foreach($ff as $f) 
	{
		if ($config[$idc]['fields'][$f]['title']) $title = $config[$idc]['fields'][$f]['title'];
		else $title = $cf[$f]['caption'];
		
		if ($config[$idc]['fields'][$f]['width']) $width = $config[$idc]['fields'][$f]['width'];
		else $width = '';
		
		
		if ($cf[$f]['table']!='tv')
		{			
			$url = $modx->config[site_manager_url];
			$url.= '?a=27&id='.$_GET[id];			
			if ($_GET['show']) $url.='&show='.$_GET['show'];
			$url.='&sorter=c.'.$cf[$f]['caption'];
			if ($_GET['direction']=='asc')
			{
				$url.='&direction=desc';
				if ($_GET['sorter']=='c.'.$cf[$f]['caption']) $di = '<i class="fa fa-sort-amount-asc" aria-hidden="true"></i>';
				else $di='';
			}
			else 
			{
				$url.='&direction=asc';
				if ($_GET['sorter']=='c.'.$cf[$f]['caption']) $di = '<i class="fa fa-sort-amount-desc" aria-hidden="true"></i>';
				else $di='';
			}
			
			
			$caption = '<a href="'.$url.'">'.$title.' '.$di.'</a>';
		}
		else $caption=$title;
		$tbl.='<td width="'.$width.'">'.$caption.'</td>';
	}
	
	$tbl.='<th width="1%"></th><th><input type="checkbox"  id="checkall" ></th></tr></thead><tbody>';
	
	$res = $modx->db->query($sql);	
	$arr = $modx->db->makeArray($res);
		
	if ($config[$idc]['new_doc']=='up') $tbl.='<tr id="newstrbutt"><td colspan="'.(count($ff)+1).'"></td><td><i class="fa fa-plus" aria-hidden="true" id="news_str" data-template="'.$configuration[$idc]['template_default'].'" data-parent="'.$_GET['id'].'"></i><i class="fa fa-spinner fa-spin"  id="spiner_new_str"></i></td></tr>';	
	
	$hiddenStr = 0;
	for($i=0; $i<count($arr);$i++)
	{
		$row = $arr[$i];
		
		
		if ($row['deleted']==1) $deltr = "style='background:pink;'";
		else $deltr='';
		
		$tbl.= '<tr data-id="'.$row['id'].'" '.$deltr.' '.$getsr.'>';
		foreach($ff as $f)
		{
			
			
			if($cf[$f]['table']=='tv') $table="tv";
			else $table = "content";
			
						
			if ($config[$idc]['fields'][$f]['type']!='default') $type = $config[$idc]['fields'][$f]['type'];
			else $type = $cf[$f]['type'];
			if ($config[$idc]['fields'][$f]['type']=='user') $user = $config[$idc]['fields'][$f]['user'];
			else $user = '';
			if ($f=='id') $tbl.='<td>'.$row[$f].'</td>';
			else 
			{
				$tbl.='
				<td>
				<div class="input" 
				data-id="'.$row[id].'"
				data-table="'.$table.'"
				data-field="'.$f.'"
				data-user_func="'.$user.'"									
				data-type="'.$type.'"																		
				>'.get_output(
				array('did'=>$row[id],
				'value'=>$row[$f],
				'field'=>$f,
				'table'=>$table,
				'type'=>$type,
				'user_func'=>$user,
				'mode'=>'input')).'</div>
				
				
				<div class="output">'.get_output(array('did'=>$row[id],
				'value'=>$row[$f],
				'field'=>$f,
				'table'=>$table,
				'type'=>$type,
				'user_func'=>$user,
				'mode'=>'output')).'</div>
				</td>';
			}
			
		}
		
		$tbl.= '<td><div class="actions text-center text-nowrap"><a href="index.php?a=27&amp;id='.$row[id].'&amp;dir=DESC&amp;sort=createdon" title="Редактировать"><i class="fa fa-pencil-square-o"></i></a></div></td><td><input type="checkbox" name="docid[]" value="'.$row[id].'" class="docid"></td></tr>';
		
	}
	
	
	
	if ($config[$idc]['new_doc']=='down') $tbl.='<tr id="newstrbutt"><td colspan="'.(count($ff)+1).'"></td><td><i class="fa fa-plus" aria-hidden="true" id="news_str" data-template="'.$configuration[$idc]['template_default'].'" data-parent="'.$_GET['id'].'"></i><i class="fa fa-spinner fa-spin"  id="spiner_new_str"></i></td></tr>';
	
	
	$tbl.='</tbody></table></div></div>';

	if ($config[$idc]['title']) $title = $config[$idc]['title'];
	else $title = $modx->db->getValue('Select `pagetitle` from '.$modx->getFullTableName('site_content').' where id='.$id);

	$output.='		
	<div class="tab-page" id="tabProducts">
	<h2 class="tab">'.$title.'</h2>
	
	<div class="btn-group" style="float:right;">
	<div class="btn-group dropdown" style=" margin:10px 10px 10px; ">					
	
	<select name="show" id="show" style="width:100px;">
	<option value="">Показывать по</option>				
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="1000">1000</option>
	</select>
	<select name="act" id="act" style="width:100px; margin-right: 5px;">
	<option value="">Действия</option>				
	<option value="del">Удалить</option>
	<option value="restore">Восстановить</option>
	<option value="unpub">Снять с публикации</option>
	<option value="pub">Опубликовать</option>
	</select>
	<a  class="btn btn-success" href="javascript:;" onclick="act();" style="width:100px; float:right;">
	<span>Применить</span>
	</a>
	</div>
	
	
	</div>
	'.$tbl;
	
	$cq = $modx->db->getValue('Select count(*) from '.$modx->getFullTableName('site_content').' where parent='.$id);
	if ($_GET['show']) $limit = $_GET['show'];
	$pages = ceil($cq/$limit);
	if ($pages>1)
	{
	$output.='<div id="pagination" style="text-align:center;"><ul>';
	for ($i=1;$i<=$pages;$i++) 
	{
		$url = $modx->config[site_manager_url].'?a='.$_GET['a'].'&id='.$_GET['id'];
		if ($_GET['show']) $url.='&show='.$_GET['show'];
		if ($_GET['order']) $url.='&show='.$_GET['order'];
		
		$output.='<li><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
	}
	$output.='</ul></div>';
	}
	$output.='</div>';
	$template = $modx->db->getValue('Select template from '.$modx->getFullTableName('site_content').' where parent='.$_GET[id]);
	if (!$template) $template=$default_template;
	$output.='<div id="popup_rich"><div id="close"><i class="fa fa-close"></i></div><h2>Редактирование содержимого</h2><div id="rta"></div><div style="text-align:center; margin-top:10px;"><a  class="btn btn-success save_content">Сохранить</a></div></div></div>
	
	
	
	<link rel="stylesheet" type="text/css" href="/assets/plugins/evocollection/js/evocollection.css">
	<script>
	manager_url = "'.$modx->config[site_manager_url].'";
	how_click = "'.$config[$idc]['how_edit'].'";
	new_doc = "'.$config[$idc]['new_doc'].'";
	</script>
	<script src="/assets/plugins/evocollection/js/evocollection.js?v=1" type="text/javascript"></script>	
	<script src="/assets/plugins/tinymce4/tinymce/tinymce.min.js"></script>
	
	
	';		
	
	
$e->output($output);
