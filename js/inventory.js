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
	var currentManagement = $('#variant_' + variant_id + '_management').val();
	
	if (currentManagement == ""){
		$('#variant_' + variant_id + '_quantity').css('display', 'inline');
		$('#variant_' + variant_id + '_manage_button').val('Unmanage');
		$('#variant_' + variant_id + '_management').val('shopify');
	}else{
		$('#variant_' + variant_id + '_quantity').css('display', 'none');
		$('#variant_' + variant_id + '_manage_button').val('Manage');
		$('#variant_' + variant_id + '_management').val('');		
	}
}

function resetSearch(){
	$('#product_name').val('');
	$('#collection').val('');
	$('#search_form').submit();
}