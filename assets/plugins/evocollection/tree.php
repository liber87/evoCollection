<?php
if(!isset($_SESSION['mgrValidated'])){ die();}

	$idsa = array();
	foreach($configuration as $conf)
	{
		if (($conf['type']=='ids') and ($conf['value']) and ($conf['show_child']==0)) $idsa[]=$conf['value'];			
		if (($conf['type']=='template') and ($conf['value']) and ($conf['show_child']==0)) $idsa[] = $modx->db->getValue('Select GROUP_CONCAT(id) from '.$modx->getFullTableName('site_content').' where template in ('.$conf['value'].')');
	}
	$ids = implode(',',$idsa);
	if (!$ids) return;	
	
	
	foreach (explode(',',$ids) as $i)
	{
		$i = trim($i);
		if($i)
		{
			if($ph['id'] == $i)
			{			
				$ph['showChildren'] = '0';
			}	
		}
	}
	
	
$modx->event->output(serialize($ph));
