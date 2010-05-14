<?php
	
	/*
		I am using Metafields for the preferences page instead of the database in case you do not have database
		access to save data.	
	*/	
	
	$metafields = $api->metafield;
	$errorUpdating = false;
	
	$tagArray = array(
		'JAVASCRIPT' => '',
		'LOOP' => array('PER_PAGE'),
	);
	
	/*
		Preferences have not been yet created
	*/
	
	$preferences = array();
		
	if (sizeof($metafields->get()) == 0){
		$metafields->create(0, array(
									'namespace'=> 'preferences',
									'key' => 'perPage',
									'value' => '25', 
									'value-type' => 'integer'
								)
							);
		
		$preferences['perPage'] = 25;
	}else{
		foreach($metafields->get() as $k => $v){
			if (isset($_POST[$v['key']])){
				if (is_array($metafields->modify(0, $v['id'], array('value' => $_POST[$v['key']])))){
					$preferences[$v['key']] = $_POST[$v['key']];
				}else{
					$errorUpdating = true;
				}
			}else{
				$preferences[$v['key']] = $v['value'];
			}
		}
	}
	
	$loopArray['PER_PAGE'] = array();
	$pageOptions = array(10, 25, 50, 100, 200, 250);
	foreach($pageOptions as $i => $v){
		$loopArray['PER_PAGE'][$i]['val'] = $v;
		$loopArray['PER_PAGE'][$i]['selected'] = ($v == $preferences['perPage']) ? 'selected="selected"' : '';
	}
	
	if (isset($_POST['updatePreferences'])){
		if ($errorUpdating){
			$tagArray['JAVASCRIPT'] = "showError('There was an error updating your preferences. Please try again later...');";
		}else{
			$tagArray['JAVASCRIPT'] = "showNotice('Your preferences have been updated...');";
		}
	}

?>