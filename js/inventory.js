function manageVariant(variant_id){
	var currentManagement = $('#variant_' + variant_id + '_management:checked').val()
	
	if (currentManagement == null){
		$('#variant_' + variant_id + '_quantity').attr('disabled', 'disabled');
	}else{
		$('#variant_' + variant_id + '_quantity').removeAttr('disabled');
	}
}

function resetSearch(){
	$('#product_name').val('');
	$('#collection').val('');
	$('#search_form').submit();
}