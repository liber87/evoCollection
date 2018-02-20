<?php
	/**
		* EvoCollection
		*
		* 
		*
		* @category    plugin
		* @version     0.1a
		* @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
		inlineTheme=<b>Inline-Mode</b><br/>Theme;text;inline &browser_spellcheck=<b>Browser Spellcheck</b><br/>At least one dictionary must be installed inside your browser;list;enabled,disabled;disabled
		* @internal    @events OnManagerNodePrerender,OnPageNotFound,OnDocFormRender
		* @internal    @modx_category Manager and Admin
		* @author      Alexey Liber
	* @lastupdate  25.07.2017 */
	$e = &$modx->Event;
	require MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php";
	require MODX_BASE_PATH."assets/plugins/evocollection/functions.php"; 	
		
	
	if (!count($config)) return;
	else $configuration = $config;
		
	
	//Actions
	if ($e->name=='OnPageNotFound')
	{
		require MODX_BASE_PATH."assets/plugins/evocollection/actions.php";
	}
	if ($e->name=='OnDocFormRender')
	{			
		require MODX_BASE_PATH."assets/plugins/evocollection/table.php"; 		
	}
	if ($e->name=="OnManagerNodePrerender")
	{
		require MODX_BASE_PATH."assets/plugins/evocollection/tree.php"; 			
		
	}