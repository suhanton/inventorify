function showNotice(message){
	$('#flashnotice').append(message);
	$('#flashnotice').fadeIn('slow').delay(5000).fadeOut('slow', function(){
		$('#flashnotice').html('');
	});
}

function showErrors(message){
	$('#flasherrors').append(message);
	$('#flasherrors').fadeIn('slow').delay(5000).fadeOut('slow', function(){
		$('#flasherrors').html('');
	});
}

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