$(document).ready(function()
{
	$('#table_rows').on('keyup','#last_str td',function()
	{
		$(this).closest('tr').removeAttr('id');
		$('.minus_button').html('<button><i class="fa fa-minus-square" aria-hidden="true"></i></button>');
		
		$('#table_rows').append('<tr id="last_str"> <td valign="top"><input type="text" maxlength="255" name="title_row[]" value="" class="inputBox" ></td> <td> <div> <input type="text" maxlength="255" name="value_row[]" value="" class="inputBox" > </div> <small>выбрать из: <a href="#" class="set_doc" style="margin-left:5px;">документа</a> <a href="#" class="set_tv" style="margin-left:5px;">ТВ-параметров</a> <a href="#" class="cancel" style="margin-left:5px;">отменть</a></small> </td> <td valign="top"><select class="inputBox" name="type_row[]">'+optSelect+'</select></td> <td valign="top"><input type="text" maxlength="255" name="width_row[]" value="" class="inputBox" ></td>  <td valign="top"><input type="text" maxlength="255" name="width_row[]" value="" class="inputBox" ></td> <td class="minus_button" valign="top"></td> </tr>');		
	});
	$('#table_rows').on('click','.minus_button button',function()
	{
		$(this).closest('tr').remove();
	});
	$('#table_rows').on('click','a',function(event)
	{
		event.preventDefault;
		if ($(this).hasClass('cancel')) $(this).parent().prev().html('<input type="text" maxlength="255" name="value_row[]" value="" class="inputBox" >');
		if ($(this).hasClass('set_doc')) 
		{
			$(this).parent().prev().html(select_doc);
			$(this).parent().prev().children().focus();
		}
		if ($(this).hasClass('set_tv')) 
		{
			$(this).parent().prev().html(select_tv);
			$(this).parent().prev().children().focus();
		}
		
		return false;
	});
	$('#elements_change').change(function(){
		$('.elements').hide();
		$('.elements input,.elements select').val('');
		$('#'+$(this).val()).show();
	});
});

