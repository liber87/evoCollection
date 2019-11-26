<?php	
	/**
		* EvoCollection
		*
		* 
		*
		* @category    plugin
		* @version     0.1a
		* @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)		
		* @internal    @events OnManagerNodePrerender,OnPageNotFound,OnDocFormRender
		* @internal    @modx_category Manager and Admin
		* @author      Alexey Liber
	* @lastupdate  02.03.2018 */
	
	if (isset($_SESSION['mgrValidated']))
	{		
		if (!file_exists(MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php"))
		{
			rename(MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php.blank",MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php");
		}
		require MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php";
		require MODX_BASE_PATH."assets/plugins/evocollection/functions.php"; 	
	}	
	
	if ((is_array($config)) && (!count($config))) return;
	else $configuration = $config;
		
	
	//Actions
	switch($modx->event->name)
	{
		case 'OnPageNotFound':		
		require MODX_BASE_PATH."assets/plugins/evocollection/actions.php";
		break;
		
		case 'OnDocFormRender':
		require MODX_BASE_PATH."assets/plugins/evocollection/table.php"; 		
		break;
		
		case 'OnManagerNodePrerender':
		require MODX_BASE_PATH."assets/plugins/evocollection/tree.php"; 			
		break;		
	}
