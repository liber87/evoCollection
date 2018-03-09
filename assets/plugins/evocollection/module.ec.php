<?php
	if(!isset($_SESSION['mgrValidated'])){ die();}
	require_once(MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php");
	
	$page = '
	<html>
	<head>
	<title>Модуль редактирования конфигурации evoCollection</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script src="media/script/jquery/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="media/script/tabpane.js"></script>
	<script type="text/javascript">';
	
	$optArr = array(
	'default'=>'По умолчанию',
	'user'=>'Пользовательский',
	'number'=>'Числовой',
	'date'=>'Дата',
	'image'=>'Картинка',
	'file'=>'Файл',
	'textarea'=>'Текстовое поле',
	'richtext'=>'Текстовый редактор',
	'oncecheckbox'=>'Одиночный чекбокс');
	$optSelect='';
	foreach($optArr as $key => $val) $optSelect.='<option value=\''.$key.'\'>'.$val.'</option>';
	$page.='optSelect = "'.$optSelect.'";'.PHP_EOL;
	
	
	$page.='select_doc = "<select name=\'value_row[]\' class=\'inputBox\'>';
	$res = $modx->db->query('SHOW COLUMNS FROM '.$modx->getFullTableName('site_content').' where field!="id"');
	while($row = $modx->db->getRow($res))
	{
		$page.='<option value=\''.$row['Field'].'\'>'.$row['Field'].'</option>';
	}
	$page.='</select>";
	
	
	
	
	select_tv = "<select name=\'value_row[]\' class=\'inputBox\'>';
	$res = $modx->db->query('SELECT DISTINCT(tmplvarid),name,caption,cat.category FROM '.$modx->getFullTableName('site_tmplvar_templates').'
	left join '.$modx->getFullTableName('site_tmplvars').' as vars
	on '.$modx->getFullTableName('site_tmplvar_templates').'.`tmplvarid` = vars.id
	left join '.$modx->getFullTableName('categories').' as cat
	on cat.id = vars.`category`
	order by `category`,`name`');
	$page.='<optgroup label=\'Без категории\'>';
	
	while($row = $modx->db->getRow($res))
	{
		if (($ortop!=$row['category']) && ($row['category']))
		{
			$ortop=$row['category'];
			$page.= '</optgroup><optgroup label=\''.$row['category'].'\'>';
		}
		if ($row['caption']) $page.='<option value=\''.$row['name'].'\'>'.$row['name'].' ('.$row['caption'].')</option>';
		else $page.='<option value=\''.$row['name'].'\'>'.$row['name'].' '.$row['caption'].'</option>';
		
	}
	$page.='</optgroup>';
	$page.='</select>";
	</script>
	<script type="text/javascript" src="./../assets/plugins/evocollection/js/module.js"></script>
	
	<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset='.$modx->config['modx_charset'].'" />
	<link rel="stylesheet" type="text/css" href="'.$modx->config[site_manager_url].'media/style/default/css/styles.min.css" />
	<link rel="stylesheet" type="text/css" href="'.$modx->config[site_manager_url].'media/style/'.$modx->config['manager_theme'].'/style.css" />
	<link rel="stylesheet" type="text/css" href="'.$modx->config[site_manager_url].'media/style/'.$modx->config['manager_theme'].'/css/fonts.css" />
	</head>
	<style>
	label{    font-size: 0.85rem;        color: rgba(128, 128, 128, 0.75); margin:10px 0 0 0; }
	td label{margin-top:0;}
	#new_str{display:none;}
	form{margin:bottom:0;}
	</style>
	<body>
	<h1><i class="fa fa-th"></i>{{pagetitle}}</h1>
	
	
	<div class="tab-page" id="tabGeneral">
	
	
	<div class="container container-body">
	{{module}}
	</div>
	
	</div>
	
	</body>
	</html>';
	
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	
	if ($action=='work')
	{
		
		$configuration = array();
		foreach ($_POST as $key => $val)
		{
			if (is_array($val))
			{
				foreach ($val as $k1 => $v1) $configuration[$key][$k1] = $modx->db->escape($v1);
			}
			else $configuration[$key] = $modx->db->escape($val);
		}
		$configuration = $_POST;
		unset($configuration['action']);
		if ($configuration['type']=='template') $configuration['value'] = $configuration[template]; 
		else $configuration['value'] = $configuration[ids];
		unset($configuration['template']);
		unset($configuration['ids']);
		
		if (count($configuration['value_row']))
		{
			foreach($configuration['value_row'] as $key=>$val)
			{
				if ($val)
				{
					$configuration['fields'][$val]['title'] = $configuration['title_row'][$key];
					$configuration['fields'][$val]['width'] = $configuration['width_row'][$key];
					$configuration['fields'][$val]['type'] = $configuration['type_row'][$key];
					$configuration['fields'][$val]['user'] = $configuration['user_row'][$key];
				}
			}
		}
		unset($configuration['value_row']);
		unset($configuration['title_row']);
		unset($configuration['width_row']);
		
		$idc = $configuration['id'];
		if (!$idc) $idc = count($config)+1;
		unset($configuration['id']);
		
		$config[$idc] = $configuration;
		$text = "<?php".PHP_EOL.'$config='.var_export($config,1).';';
		
		$f=fopen(MODX_BASE_PATH."assets/plugins/evocollection/config.inc.php",'w');
		fwrite($f,$text);
		fclose($f);
		$result = '<div class="alert alert-success">Конфигурация успешно сохранена!</div>';
	}
	
	if (!$idc) $idc = $_GET['idc'];
	
	if (($idc) or ($action=='new'))
	{
		if ($idc) $pagetitle= 'Редактирование коллекции';
		else $pagetitle= 'Новая коллекция';
		
		$module='<div id="actions">
		<div class="btn-group">
        <a id="Button2" class="btn" href="javascript:;" onclick="window.location.href=\'index.php?a=112&id='.$_GET['id'].'\'">
		<i class="fa fa-times-circle"></i><span>Отмена</span>
        </a>
		
		<a id="Button1" class="btn btn-success" href="javascript:;" onclick="$(\'#config_form\').submit();">
		<i class="fa fa-times-circle"></i><span>Сохранить</span>
        </a>
		
		</div>
		</div>';
		
		$module.= '
		
		'.$result.'			
		<form method="post" class="tab-page" id="config_form">
		<input type="hidden" name="action" value="work">
		<input type="hidden" name="id" value="'.$idc.'">			
		<div class="tab-section">
		<div class="tab-header">Общее</div>
		<div class="tab-body">			
		<div class="row">
		<div class="col-xs-12">
		<label>Название конфигурации</label>
		<input name="name" type="text" maxlength="255" value="'.$config[$idc]['name'].'" class="inputBox" >				
		
		<label>Описание</label>
		<textarea class="inputBox" name="description">'.$config[$idc]['description'].'</textarea>
		</div>	
		
		<div class="col-xs-12 col-sm-6">
		<label>Применять к</label>
		<select name="type" class="inputBox" id="elements_change">
		<option value="template"';
		if ($config[$idc]['type']=='template') $module.=' selected="selected" ';
		$module.= '>Шаблону</option>						
		<option value="ids"';
		if ($config[$idc]['type']=='ids') $module.=' selected="selected" ';
		$module.= '>Документам</option>						
		</select>
		</div>
		
		<div class="col-xs-12 col-sm-6 elements" id="template"';
		if ($config[$idc]['type']=='ids') $module.=' style="display:none;" ';
		$module.='><label>Значение</label>
		<select name="template" class="inputBox">
		<option value="">не выбрано</option>';
		$templates = $modx->db->query('Select template.id,templatename,cat.category from '.$modx->getFullTableName('site_templates').' as template
		left join '.$modx->getFullTableName('categories').' as cat
		on cat.id = template.`category`
		order by category,templatename');
		$ortop='';
		$module.='<optgroup label=\'Без категории\'>';
		while($row = $modx->db->getRow($templates))
		{
			if (($ortop!=$row['category']) && ($row['category']))
			{
				$ortop=$row['category'];
				$module.= '</optgroup><optgroup label=\''.$row['category'].'\'>';
			}
			if ($config[$idc]['type']=='template')
			{
				if ($row['id']==$config[$idc]['value']) $ss = 'selected="selected"';
				else $ss = '';
			}
			$module.='<option value="'.$row['id'].'" '.$ss.'>'.$row['templatename'].'</option>';
		}
		$module.='</optgroup>';
		$module.='</select>
		</div>
		<div class="col-xs-12 col-sm-6 elements" id="ids"';
		if ($config[$idc]['type']!='ids') $module.=' style="display:none;" ';
		$module.='>
		<label>Значение</label>
		<input type="text" maxlength="255" name="ids" placeholder="Через запятую" value="'.$config[$idc]['value'].'" class="inputBox" >					
		</div>
		<div class="col-xs-12">
		<label>Показывать документы в древе</label>
		<select name="show_child" class="inputBox">						
		<option value="0"';
		if ($config[$idc]['show_child']=='0') $module.=' selected="selected" ';
		$module.='>Нет</option>				
		<option value="1"';
		if ($config[$idc]['show_child']=='1') $module.=' selected="selected" ';
		$module.='>Да</option>						
		</select>
		</div>
		
		
		<div class="col-xs-12">
		<label>Расположение новых документов</label>
			<select name="new_doc" class="inputBox">
				<option value="down"';
				if ($config[$idc]['new_doc']=='down') $module.=' selected="selected" ';
				$module.='>Внизу</option>
				<option value="up"';
				if ($config[$idc]['new_doc']=='up') $module.=' selected="selected" ';
				$module.='>Вверху</option>
			</select>
		</div>
		
		
		
		
		</div>
		</div>
		</div>
		<div class="tab-section">
		<div class="tab-header">Настройки вкладки</div>
		<div class="tab-body">
		<label>Заголовок вкладки</label>
		<input type="text" maxlength="255" name="title" placeholder="по умолчанию: название ресурса" value="'.$config[$idc]['title'].'" class="inputBox" >				
		
		
		
		</div>
		</div>
		
		<div class="tab-section">
		<div class="tab-header">Используемые столбцы</div>
		<div class="tab-body">			
		<div class="row">
		
		<div class="col-xs-12" >
		
		<table class="grid" cellpadding="1" cellspacing="1" id="table_rows">
		<thead>
		<tr>
		<td class="gridHeader"><label>Заголовок</label></td>
		<td class="gridHeader"><label>Значение</label></td>
		<td class="gridHeader"><label>Вывод</label></td>							
		<td class="gridHeader"><label>Обработчик</label></td>							
		<td class="gridHeader"><label>Ширина</label></td>							
		<td></td>
		</tr>
		</thead>
		<tbody>';
		
		if (count($config[$idc]['fields']))
		{
			foreach($config[$idc]['fields'] as $field => $prop)
			{
				$module.='
				<tr>
				<td valign="top"><input type="text" maxlength="255" name="title_row[]" value="'.$prop['title'].'" class="inputBox" ></td>
				<td>
				<div>
				<input type="text" maxlength="255" name="value_row[]" value="'.$field.'" class="inputBox" >
				</div>
				<small>выбрать из: <a href="#" class="set_doc" style="margin-left:5px;">документа</a> <a href="#" class="set_tv" style="margin-left:5px;">ТВ-параметров</a> <a href="#" class="cancel" style="margin-left:5px;">отменть</a></small>
				</td>
				<td valign="top"><select class="inputBox" name="type_row[]">';
				
				foreach($optArr as $key => $val) 
				{
					if ($prop['type']==$key) $st = ' selected="selected"';
					else $st = '';
					$module.='<option value="'.$key.'" '.$st.'>'.$val.'</option>';
				}
				
				$module.='</select></div>
				<td valign="top"><input type="text" maxlength="255" name="user_row[]" value="'.$prop['user'].'" class="inputBox" ></div>
				<td valign="top"><input type="text" maxlength="255" name="width_row[]" value="'.$prop['width'].'" class="inputBox" ></td>
				<td class="minus_button" valign="top"><button><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
				</tr>';
			}
		}
		
		$module.='
		<tr id="last_str">
		<td valign="top"><input type="text" maxlength="255" name="title_row[]" value="" class="inputBox" ></td>
		<td>
		<div>
		<input type="text" maxlength="255" name="value_row[]" value="" class="inputBox" >
		</div>
		<small>выбрать из: <a href="#" class="set_doc" style="margin-left:5px;">документа</a> <a href="#" class="set_tv" style="margin-left:5px;">ТВ-параметров</a> <a href="#" class="cancel" style="margin-left:5px;">отменть</a></small>
		</td>
		<td valign="top"><select class="inputBox" name="type_row[]">';
		
		foreach($optArr as $key => $val) $module.='<option value="'.$key.'">'.$val.'</option>';							
		
		$module.='</select></td>
		<td valign="top"><input type="text" maxlength="255" name="user_row[]" value="" class="inputBox" ></td>
		<td valign="top"><input type="text" maxlength="255" name="width_row[]" value="" class="inputBox" ></td>							
		<td class="minus_button" valign="top"></td>
		</tr>';
		
		$module.='</tbody>
		</table>
		</div>
		
		</div>
		</div>
		</div>
		<div class="tab-section">
		<div class="tab-header">Прочее</div>
		<div class="tab-body">			
		<div class="row">
		
		<div class="col-xs-12 col-sm-4">
		<label>Сортировать по столбцу</label>
		<div>
		<input type="text" maxlength="255" name="sort" value="'.$config[$idc]['sort'].'" class="inputBox" >					
		</div>					
		</div>
		<div class="col-xs-12 col-sm-4">
		<label>Направление сортировки</label>
		<select name="direction" class="inputBox">						
		<option value="asc"';
		if ($config[$idc]['direction']=='asc') $module.=' selected="selected" ';
		$module.='>По возрстанию</option>				
		<option value="desc"';
		if ($config[$idc]['direction']=='desc') $module.=' selected="selected" ';
		$module.='>По убыванию</option>						
		</select>
		</div>
		<div class="col-xs-12 col-sm-4">
		<label>Показывать по</label>
		<input type="text" maxlength="255" name="limit" value="'.$config[$idc]['limit'].'" class="inputBox" >					
		</div>
		<div class="col-xs-12">
		<label>Шаблон по умолчанию <small>(для вновь созданных ресурсов)</small></label>
		<select name="template_default" class="inputBox">
		<option value="">не выбрано</option>';
		$templates = $modx->db->query('Select template.id,templatename,cat.category from '.$modx->getFullTableName('site_templates').' as template
		left join '.$modx->getFullTableName('categories').' as cat
		on cat.id = template.`category`
		order by category,templatename');
		$ortop='';
		$module.='<optgroup label=\'Без категории\'>';
		while($row = $modx->db->getRow($templates))
		{
			if (($ortop!=$row['category']) && ($row['category']))
			{
				$ortop=$row['category'];
				$module.= '</optgroup><optgroup label=\''.$row['category'].'\'>';
			}
			if ($row['id']==$config[$idc]['template_default']) $ss = ' selected="selected"';
			else $ss = '';
			$module.='<option value="'.$row['id'].'" '.$ss.'>'.$row['templatename'].'</option>';
		}
		$module.='</optgroup>';
		$module.='</select>
		</div>
		<div class="col-xs-12">
		<label>Способ редактирования</label>
		<select name="how_edit" class="inputBox">						
		<option value="dblclick"';
		if ($config[$idc]['how_edit']=='dblclick') $module.=' selected="selected" ';
		$module.='>По двойному клику</option>				
		<option value="contextmenu"';
		if ($config[$idc]['how_edit']=='contextmenu') $module.=' selected="selected" ';
		$module.='>Правой кнопкой</option>						
		</select>
		</div>
		
		</div>
		</div>
		</div>
		
		
		</form>';
		
		
		$module.= '</div>
		</div>
		</div>';
	}
	else
	{
		$pagetitle = 'Список коллекций';
		
		$module='<div id="actions">
		<div class="btn-group">
        <a id="Button1" class="btn btn-success" href="javascript:;" onclick="window.location.href=\'index.php?a=112&id='.$_GET['id'].'&action=new\'">
		<i class="fa fa-times-circle"></i><span>Создать новую конфигурацию</span>
        </a>
		</div>
		</div>';
		
		if (count($config))
		{
			$module.='
			<div class="tab-section">
			<div class="tab-header">Общее</div>
			<div class="tab-body">			
			<div class="row">
			<div class="col-xs-12">
			<table class="grid" cellpadding="1" cellspacing="1">
			<thead>
			<tr>
			<td class="gridHeader"><label>ID</label></td>
			<td class="gridHeader"><label>Название</label></td>
			<td class="gridHeader"><label>Описание</label></td>							
			<td style="width:1%;"></td>
			</tr>
			</thead>
			<tbody>';
			
			foreach($config as $k => $conf)
			{
				$module.='<tr>
				<td><label>'.$k.'</label></td>
				<td><label>'.$conf['name'].'</label></td>
				<td><label>'.$conf['description'].'</label></td>
				<td style="width:1%;">
				<label><a href="index.php?a=112&id='.$_GET['id'].'&idc='.$k.'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></label>
				</td>
				</tr>';
			}
			
			$module.='</tbody>
			</table>
			</div>
			</div>
			</div>
			</div>
			</div>';
		}
		else
		{
			$module.='<div class="alert alert-info">У вас пока нет не одной конфигурации. <a href="index.php?a=112&id='.$_GET['id'].'&action=new">Нажмите здесь</a> чтобы создать первую конфигурацию.</div>';
		}
		
	}
	
	
	
	$out=str_replace('{{module}}',$module,$page);
	$out=str_replace('{{pagetitle}}',$pagetitle,$out);
	
echo $out;
