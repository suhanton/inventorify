<?php
	//Grab stuff form the API
	$products = $api->product;
	$variants = $api->product_variant;
	$smart = $api->smart_collection;
	$custom = $api->custom_collection;
	$metafields = $api->metafield;
	$errorUpdating = false;
	
	$productParams = array();
	$search = 0;
	$productName = '';
	$collection = '';
	
	/*
		Get shop preferences (in metafields)
		
		If they don't have any preferences, create the default metafields
	*/
	if (sizeof($metafields->get()) == 0){
		$metafields->create(0, array(
									'namespace'=> 'preferences',
									'key' => 'perPage',
									'value' => '25', 
									'value-type' => 'integer'
								)
							);
		
		$perPage = 25;
	}else{
		if (isset($_POST['perPage']) && is_numeric($_POST['perPage'])){
			$perPage = $_POST['perPage'];
		}else{		
			foreach($metafields->get() as $k => $v){
				if ($v['key'] == "perPage"){
					$perPage = $v['value'];
				}
			}
		}
	}
	
	//Check the search parameters
	if (isset($_POST['search'])){
		$search = 1;
		
		if (!isEmpty($_POST['collection'])){
			$c = explode('_', $_POST['collection']);
			$collection = $c[1];
			$productParams['collection_id'] = $collection;
		}
		
		$productName = (!isEmpty($_POST['product_name'])) ? $_POST['product_name'] : '';
	}

	//pagination setup
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	if (!is_numeric($page)) $page = 1;
	$totalPages = ceil($products->count(0, $productParams) / $perPage);

	$tagArray = array(
		'PAGE' => $page,
		'JAVASCRIPT' => '',
		'SEARCH' => $search,
		'PRODUCT_NAME' => $productName,
		'COLLECTION' => $collection,
		'PERPAGE' => $perPage,
		'LOOP' => array('SMART_COLLECTION', 'CUSTOM_COLLECTION', 'PER_PAGE', 'PAGES', 'PRODUCT'),
	);

	$loopArray['SMART_COLLECTION'] = array();
	$loopArray['CUSTOM_COLLECTION'] = array();
	$loopArray['PER_PAGE'] = array();
	$loopArray['PAGES'] = array();
	$loopArray['PRODUCT'] = array();
	
	//get the count of Smart Collections
	if ($smart->count() > 0){	
		foreach($smart->get() as $k => $v){
			$r = sizeof($loopArray['SMART_COLLECTION']);
			$loopArray['SMART_COLLECTION'][$r]['id'] = $v['id'];
			$loopArray['SMART_COLLECTION'][$r]['title'] = $v['title'];
			$loopArray['SMART_COLLECTION'][$r]['selected'] = ($collection == $v['id']) ? 'selected="selected"' : '';
		}	
	}
	
	//get the count of Custom Collections
	if ($custom->count() > 0){
		foreach($custom->get() as $k => $v){
			$r = sizeof($loopArray['CUSTOM_COLLECTION']);
			$loopArray['CUSTOM_COLLECTION'][$r]['id'] = $v['id'];
			$loopArray['CUSTOM_COLLECTION'][$r]['title'] = $v['title'];
			$loopArray['CUSTOM_COLLECTION'][$r]['selected'] = ($collection == $v['id']) ? 'selected="selected"' : '';
		}
	}
	
	//set up the loop for how many products to be displayed per page
	$pageOptions = array(10, 25, 50, 100, 200, 250);
	foreach($pageOptions as $i => $v){
		$loopArray['PER_PAGE'][$i]['val'] = $v;
		$loopArray['PER_PAGE'][$i]['selected'] = ($v == $perPage) ? 'selected="selected"' : '';
	}
	
	//set up the loop for the the pages
	for ($i = 1; $i <= $totalPages; $i++){
		$r = sizeof($loopArray['PAGES']);
		$loopArray['PAGES'][$r]['page'] = $i;
		$loopArray['PAGES'][$r]['style'] = ($page == $i) ? 'font-weight:bold' : '';
	}
	
	//after POST update
	if (isset($_POST['updateInventory'])){
		//cycle products based on search parameters
		foreach($products->get(0, 0, $productParams) as $k => $v){
			
			//cycle variants from the products
			foreach($variants->get($v['id']) as $vk => $vv){
				$fields = array();
				$newQuantity = (isset($_POST['variant_' . $vv['id'] . '_quantity'])) ? $_POST['variant_' . $vv['id'] . '_quantity'] : '';
				$newSKU = $_POST['variant_' . $vv['id'] . '_sku'];
				$newManagement = (isset($_POST['variant_' . $vv['id'] . '_management']) &&  $_POST['variant_' . $vv['id'] . '_management'] == "on") ? 'shopify' : '';
				$fields = array('sku' => $newSKU, 'inventory-management' => $newManagement);
				if (is_numeric($newQuantity) && $newManagement == 'shopify') $fields['inventory-quantity'] = $newQuantity;
				if (!is_array($variants->modify($v['id'], $vv['id'], $fields))) $errorUpdating = true;
			}
		}
		
		$tagArray['JAVASCRIPT'] = ($errorUpdating) ? "showErrors('<li>There was an error updating the products. Please try again later...</li>');" : "showNotice('<li>Products updated succesfully...</li>');";
	}
	
	//cycle products based on search parameters
	foreach($products->get(0, 0, $productParams) as $k => $v){
		
		//because there is no parameter to pass to the API to search by product name
		//we do our own matching
		
		//if the product name is not empty (meaning search by name)
		if (!isEmpty($productName)){
			//check to see if the search term is a substring of the product name
			if (substr_count($v['title'], $productName) > 0){
				//setup loop for the product's variants
				$variantKey = 'VARIANT_' . $v['id'];
				$tagArray['LOOP'][sizeof($tagArray['LOOP'])] = $variantKey;
				$loopArray[$variantKey] = array();		
				
				$r = sizeof($loopArray['PRODUCT']);
				$loopArray['PRODUCT'][$r]['title'] = $v['title'];
				$loopArray['PRODUCT'][$r]['id'] = $v['id'];
			
				//cycle through the product's variants
				foreach($variants->get($v['id']) as $vk => $vv){
					$vr = sizeof($loopArray[$variantKey]);
					foreach($vv as $vvk => $vvv) $loopArray[$variantKey][$vr][$vvk] = $vvv;
					$loopArray[$variantKey][$vr]['display_quantity'] = ($vv['inventory-management'] == "shopify") ? 'inline' : 'none';
					$loopArray[$variantKey][$vr]['manage_text'] = ($vv['inventory-management'] == "") ? 'Manage' : 'Unmanage';
				}
			}
		}else{
			//setup loop for the product's variants
			$variantKey = 'VARIANT_' . $v['id'];
			$tagArray['LOOP'][sizeof($tagArray['LOOP'])] = $variantKey;
			$loopArray[$variantKey] = array();		
		
			$r = sizeof($loopArray['PRODUCT']);
			$loopArray['PRODUCT'][$r]['title'] = $v['title'];
			$loopArray['PRODUCT'][$r]['id'] = $v['id'];
		
			//cycle through the products variants
			foreach($variants->get($v['id']) as $vk => $vv){
				$vr = sizeof($loopArray[$variantKey]);
				foreach($vv as $vvk => $vvv) $loopArray[$variantKey][$vr][$vvk] = $vvv;
				$loopArray[$variantKey][$vr]['checked'] = ($vv['inventory-management'] == "shopify") ? 'checked="checked"' : '';
				$loopArray[$variantKey][$vr]['disabled'] = ($vv['inventory-management'] == "shopify") ? '' : 'disabled="disabled"';
			}			
		}
	}
?>