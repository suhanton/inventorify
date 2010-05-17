<?php
	$tagArray = array(
		'DOMAIN' => HTTP_PATH,
		'T_TITLE' => 'Inventorify',
		'LOOP' => array('LI'),
	);
	
	$loopArray['LI'] = array();
		
	/* You can tab links here */	
	if (isset($_SESSION['url'])){
		$links = array(
			'Inventory' => 'inventory',
			'Preferences' => 'preferences',
			'Logout' => 'logout',
			'Return to My Store' => 'http://' . $url,
		);
	}else{
		$links = array(
			'Install' => 'authorize',
		);
	}
	
	foreach($links as $t => $l){
		$r = sizeof($loopArray['LI']);
		$loopArray['LI'][$r]['text'] = $t;
		$loopArray['LI'][$r]['href'] = (substr_count($l, 'http://') > 0) ? $l : 'index.php?action=' . $l;
		$loopArray['LI'][$r]['class'] = ($action == $l) ? 'current' : '';
	}
?>