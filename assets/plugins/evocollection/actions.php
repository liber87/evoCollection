<?php
	if(!isset($_SESSION['mgrValidated'])){ die();}
	switch($_REQUEST['q'])
	{		
		case 'generatephpto':				
			$iafn = explode('/',$_POST['img']);
			$folder = str_replace(end($iafn),'',$_POST['img']);			
			require MODX_BASE_PATH.'assets/snippets/phpthumb/phpthumb.class.php';
			$phpThumb = new phpthumb();					
			$pars = array('w' => 64,'h' => 64,'q' => 80,'f' => 'jpg');					
			$phpThumb->setSourceFilename(MODX_BASE_PATH.$_POST['img']);
			foreach($pars as $k => $v) 
			{
				$phpThumb->setParameter($k, $v);
			}
			if ($phpThumb->GenerateThumbnail()) 
			{						
				if(!is_dir(MODX_BASE_PATH.'assets/plugins/evocollection/cache/'.$folder)) mkdir(MODX_BASE_PATH.'assets/plugins/evocollection/cache/'.$folder,0777,true);		
				$img = '/assets/plugins/evocollection/cache/'.$_POST['img'];							
				$phpThumb->renderToFile(MODX_BASE_PATH.$img);										
			}			
			echo '<img src="./..'.$img.'" width="64" height="64">';
			exit();
		break;
		
		case 'getcontent':			
			if ($_POST['table']=='content') echo $modx->db->getValue('Select '.$modx->db->escape($_POST['field']).' from '.$modx->getFullTableName('site_content').' where id='.$modx->db->escape($_POST['id']));					
			else 
			{
				$idtv = $modx->db->getValue('Select `id` from '.$modx->getFullTableName('site_tmplvars').' where name="'.$_POST['field'].'"');	
				echo $modx->db->getValue('Select `value` from '.$modx->getFullTableName('site_tmplvar_contentvalues').' where contentid='.$_POST[id].' and `tmplvarid`='.$idtv);
			}
			exit();
		break;
		
		case 'getnewdoc':						
			$doc = array(
			'type'=>'document',
			'contentType'=>'text/html',
			'pagetitle'=>'New document',
			'longtitle'=>'',
			'description'=>'',
			'alias'=>'',
			'link_attributes'=>'',
			'published'=>'1',
			'pub_date'=>'0',
			'unpub_date'=>'0',
			'parent'=>$modx->db->escape($_POST['parent']),
			'isfolder'=>'0',
			'introtext'=>'',
			'content'=>'',
			'richtext'=>'1',
			'template'=>$modx->db->escape($_POST['template']),
			'menuindex'=>'0');
			
			$did = $modx->db->insert($doc,$modx->getFullTableName('site_content'));
			
			$modx->db->update(array('alias'=>$did),$modx->getFullTableName('site_content'),'id='.$did);
			echo $did;
			exit();		
		break;
		
		case 'set_field_value':	
						
			if ((!$_POST['id']) && (!$_POST['value'])) 
			{
				echo 'Wrong query!';
				exit();
			}
			
					
			if ($_POST['field']=="id") return;
			
			// Обработка поля			
			$val = get_output(array('did'=>$_POST['id'],
			'value'=>$_POST[value],
			'field'=>$_POST['field'],
			'table'=>$_POST['table'],
			'type'=>$_POST['type'],
			'user_func'=>$_POST['user_func'],
			'mode'=>'execute'));
						
			// Работаем с поялями документа
			if ($_POST['table']=='content') 			
			{			
				$modx->db->query('Update '.$modx->getFullTableName('site_content').' set '.$modx->db->escape($_POST['field']).'="'.$modx->db->escape($val).'" where id='.$modx->db->escape($_POST['id']));
			}
			
			
			// Работаем с ТВ-кой
			if ($_POST['table']=='tv')
			{
				$idtv = $modx->db->getValue('Select `id` from '.$modx->getFullTableName('site_tmplvars').' where name="'.$modx->db->escape($_POST['field']).'"');	
				if ($modx->db->getValue('Select count(*) from '.$modx->getFullTableName('site_tmplvar_contentvalues').' where contentid='.$modx->db->escape($_POST['id']).' and `tmplvarid`='.$idtv))
				{
					$modx->db->query('Update '.$modx->getFullTableName('site_tmplvar_contentvalues').' set value="'.$modx->db->escape($val).'" where contentid='.$modx->db->escape($_POST[id]).' and `tmplvarid`='.$modx->db->escape($idtv));
				}
				else $modx->db->insert(array('contentid'=>$modx->db->escape($_POST['id']),'tmplvarid'=>$modx->db->escape($idtv),'value'=>$modx->db->escape($val)),$modx->getFullTableName('site_tmplvar_contentvalues'));
			}
			
			echo get_output(array('did'=>$_POST[id],
			'value'=>$val,
			'field'=>$_POST['field'],
			'table'=>$_POST['table'],
			'type'=>$_POST['type'],
			'user_func'=>$_POST['user_func'],
			'mode'=>'output'));
			exit();
		break;		
	}
