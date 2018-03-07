var lastImageCtrl;
var lastFileCtrl;



function $_GET(key) {
    var p = window.location.search;
    p = p.match(new RegExp(key + '=([^&=]+)'));
    return p ? p[1] : false;
}

function OpenServerBrowser(url, width, height ) 
{	
	
	var iLeft = (screen.width  - width) / 2 ;
	var iTop  = (screen.height - height) / 2 ;
	var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
	sOptions += ',width=' + width ;
	sOptions += ',height=' + height ;
	sOptions += ',left=' + iLeft ;
	sOptions += ',top=' + iTop ;	
	var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;	
}

function BrowseServerEC(ctrl,t) {
	lastImageCtrl = ctrl;	
	var w = screen.width * 0.5;
	var h = screen.height * 0.5;
	OpenServerBrowser(manager_url+'media/browser/mcpuk/browser.php?Type='+t, w, h);
}


function SetUrlChange(el) 
{
	if ('createEvent' in document) {
		var evt = document.createEvent('HTMLEvents');
		evt.initEvent('blur', false, true);
		el.dispatchEvent(evt);
		} else {
		el.fireEvent('blur');
	}
}

function SetUrl(url, width, height, alt) {
	if(lastFileCtrl) {
		var c = document.getElementById(lastFileCtrl);
		if(c && c.value != url) {
			c.value = url;
			SetUrlChange(c);
		}
		lastFileCtrl = '';
		} else if(lastImageCtrl) {
		var c = document.getElementById(lastImageCtrl);
		if(c && c.value != url) {
			c.value = url;
			SetUrlChange(c);
		}
		lastImageCtrl = '';
		} else {
		return;
	}
}

function act()
{	
	var url = manager_url+'?a='+$_GET('a')+'&id='+$_GET('id');
	if ($j("#show").val()!="") url=url+"&show="+$j("#show").val();
	if ($j("#act").val()!="") url=url+"&act="+$j("#act").val();
	var checks = $j(".docid:checked").serialize();
	if (checks) url = url+"&"+checks;
	
	location.href = url;	
	
}
function set_field_value(tag,value)
{	
	$j("#mainloader").css({"opacity": 1,"visibility": "initial"});
	tag.parent().find('.output').html('<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');	
	$j.post(document.location.protocol+'//'+document.location.host+'/set_field_value',
	{
		"table":tag.data("table"),
		"id":tag.closest("tr").data("id"),
		"parent":$_GET('id'),		
		"field":tag.data("field"),
		"type":tag.data("type"),
		"user_func":tag.data("user_func"),
		"value":value
	},
	function(data)
	{		
		tag.parent().find('.output').html(data);
		idx=0;
		$j('.noimgs').each(function(){
			imgs.push($j(this).data('href'));
		});
		if (imgs.length>0)
		{
			set_photo(idx);
		}	
		
	}
	);
}
function strip(html)
{
	var tmp = document.createElement("DIV");
	tmp.innerHTML = html;
	return tmp.textContent || tmp.innerText;
}
function truncate(str, maxlength) 
{
	return (str.length > maxlength) ?
	str.slice(0, maxlength - 3) + "..." : str;
}

function blur_input(el)
{
	el.closest('.input').hide();
	el.closest('.input').next().show();
	set_field_value(el.parent(),el.val());	
	not_submit = false;
}
function set_photo(idx)
{
	$j.post(document.location.protocol+'//'+document.location.host+'/generatephpto',{'img':imgs[idx]},
	function(res)
	{
		$j('.noimgs[data-href="'+imgs[idx]+'"]').replaceWith(res);
		
		idx = idx + 1; 
		if (idx<imgs.length) set_photo(idx);
	});	
}

document.mutate.onsubmit = function(event) {
	if (not_submit) event.preventDefault();
}
$j(document).ready(function(){
	
	
	not_submit = false;
	t='';
	ta_id = '';
	imgs = [];
	idx = 0;
	
	$j('.noimgs').each(function(){
		imgs.push($j(this).data('href'));
	});
	if (imgs.length>0)
	{
		set_photo(idx);
	}	
	
	
	$j($j('h2[data-target="#tabProducts"]').parent()).prepend($j('h2[data-target="#tabProducts"]'));
	
	$j('#table_doc').on(how_click,".output",function(e){
		e.preventDefault();
		not_submit = true;
		
		if ($j(this).prev().children('input').hasClass('browser'))
		{
			var iid = $j(this).prev().children('input').data('id');
			var t = $j(this).prev().children('input').data('browser')
			$j(this).prev().show();			
			BrowseServerEC(iid,t);	
			return false;
		}
		
		if ($j(this).prev().children('div').hasClass('rte'))
		{
			t = $j(this).prev().children('div').data('type');
			$j(".save_content").data({"t":t});			
			ta_id = '#'+$j(this).prev().children('div').attr('id')
			var data = $j(this).prev().data();
			$j.post(document.location.protocol+'//'+document.location.host+'/getcontent',
			{
				"table":data['table'],
				"id":data['id'],						
				"field":data['field']				
			},
			function(res)
			{				
				
				$j("#rta").html('<textarea id="popup_rich_area">'+res+'</textarea>');
				if (t=='rte') tinymce.init(config_tinymce4_custom);
				
				$j("#popup_rich").show();
			}
			)			
			return false;
		}
		
		$j(this).hide();
		$j(this).prev().show().children('input, select').focus();			
		
	});
	
	$j('#table_doc').on('change','.browser',function(){
		//$j('.browser').change(function(){
		blur_input($j(this));
	});
	
	$j('#table_doc').on('keyup',".input input",function(e)
	{			
		e.preventDefault;
		if (e.keyCode==13)
		{
			blur_input($j(this));		
		}
	});
	
	
	$j('#table_doc').on('blur',".input input,.input select",function(e)
	{		
		blur_input($j(this));		
	});
	
	
	
	$j("#checkall").change(function()
	{
		if($j(this).prop("checked")) $j(".docid").attr({"checked":"checked"});
		else  $j(".docid").removeAttr("checked");		
	});		
	
	
	
	var config_tinymce4_custom = 
	{
		relative_urls:true,
		remove_script_host:true,
		convert_urls:false,
		resize:true,
		height:400,
		extended_valid_elements : "*",
		selector:"#popup_rich textarea",			
		document_base_url: document.location.protocol+'//'+document.location.host
	}	
	
	
	$j("#close").click(function(){
		$j("#popup_rich").hide();
	});
	
	$j(".save_content").click(function(e){
		t = $j(this).data('t');
		console.log(t);
		if (t=='rte')
		{
			if (tinymce.activeEditor === null) return;
			var text = tinyMCE.activeEditor.getContent();
			tinyMCE.activeEditor.destroy();
		}
		else var text = $j('#popup_rich_area').val();
		
		set_field_value($j(ta_id).parent(),text);		
		$j("#popup_rich").hide();		
		not_submit = false;
	});
	
	$j('#news_str').click(function(){
		$j(this).hide();
		$j('#spiner_new_str').show();
		$j.post(document.location.protocol+'//'+document.location.host+'/getnewdoc',{'parent':$j(this).data('parent'),'template':$j(this).data('template')}, 
		function(id)
		{
			$j.post(location.href+'&onlyid='+id,{},
			function(res)
			{
				if (new_doc=='down') $j('#newstrbutt').before($j(res).find('#getstr'));	
				else $j('#newstrbutt').after($j(res).find('#getstr'));	
				$j('#spiner_new_str').hide();
				$j('#news_str').show();
			}
			);
		});				
	});	
});
