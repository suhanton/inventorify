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