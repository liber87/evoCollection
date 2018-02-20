<?php
	if($_REQUEST['q']=='generatephpto')
	{
		if (!$_SESSION[mgrInternalKey]) { die('What are you doing? Get out of here!'); }		
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
	}
	if($_REQUEST['q']=='getcontent')
	{
		if (!$_SESSION[mgrInternalKey]) { die('What are you doing? Get out of here!'); }
		if ($_POST['table']=='content') echo $modx->db->getValue('Select `content` from '.$modx->getFullTableName('site_content').' where id='.$_POST['id']);					
		else 
		{
			$idtv = $modx->db->getValue('Select `id` from '.$modx->getFullTableName('site_tmplvars').' where name="'.$_POST['field'].'"');	
			echo $modx->db->getValue('Select `value` from '.$modx->getFullTableName('site_tmplvar_contentvalues').' where contentid='.$_POST[id].' and `tmplvarid`='.$idtv);
		}
		exit();
	}
	if($_REQUEST['q']=='getnewdoc')
	{
		if (!$_SESSION[mgrInternalKey]) { die('What are you doing? Get out of here!'); }
		
		
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
		'parent'=>$_POST['parent'],
		'isfolder'=>'0',
		'introtext'=>'',
		'content'=>'',
		'richtext'=>'1',
		'template'=>$_POST['template'],
		'menuindex'=>'0');
		
		$did = $modx->db->insert($doc,$modx->getFullTableName('site_content'));
		
		$modx->db->update(array('alias'=>$did),$modx->getFullTableName('site_content'),'id='.$did);
		echo $did;
		exit();		
	}
	
	if($_REQUEST['q']=='set_field_value')
	{	
		if (!$_SESSION[mgrInternalKey]) { die('What are you doing? Get out of here!'); }	
		
		
		if ((!$_POST['id']) && (!$_POST['value'])) 
		{
			echo 'Wrong query!';
			exit();
		}
		
				
		if ($_POST['field']=="id") return;
		
		// Обработка поля
		
		
		$val = get_output(array('did'=>$_POST[id],
		'value'=>$_POST[value],
		'field'=>$_POST['field'],
		'table'=>$_POST['table'],
		'type'=>$_POST['type'],
		'user_func'=>$_POST['user_func'],
		'mode'=>'execute'));
		
		
		
		// Работаем с поялями документа
		if ($_POST['table']=='content') 			
		{
			//$val = $_POST['value'];
			$modx->db->query('Update '.$modx->getFullTableName('site_content').' set '.$_POST['field'].'="'.$modx->db->escape($val).'" where id='.$_POST[id]);
		}
		
		
		// Работаем с ТВ-кой
		if ($_POST['table']=='tv')
		{
			$idtv = $modx->db->getValue('Select `id` from '.$modx->getFullTableName('site_tmplvars').' where name="'.$_POST['field'].'"');	
			if ($modx->db->getValue('Select count(*) from '.$modx->getFullTableName('site_tmplvar_contentvalues').' where contentid='.$_POST[id].' and `tmplvarid`='.$idtv))
			{
				$modx->db->query('Update '.$modx->getFullTableName('site_tmplvar_contentvalues').' set value="'.$modx->db->escape($val).'" where contentid='.$_POST[id].' and `tmplvarid`='.$idtv);
			}
			else $modx->db->insert(array('contentid'=>$_POST[id],'tmplvarid'=>$idtv,'value'=>$modx->db->escape($val)),$modx->getFullTableName('site_tmplvar_contentvalues'));
		}
		
		echo get_output(array('did'=>$_POST[id],
		'value'=>$val,
		'field'=>$_POST['field'],
		'table'=>$_POST['table'],
		'type'=>$_POST['type'],
		'user_func'=>$_POST['user_func'],
		'mode'=>'output'));
		exit();
	}		
