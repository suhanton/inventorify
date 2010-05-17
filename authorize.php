<?php
	if (!file_exists('lib/shopify_api_config.php')) die('lib/shopify_api_config.php is missing!');
	include('lib/shopify_api_config.php');
	include('lib/shopify_api.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');
	
	if (isset($_POST['shop'])){
		$api = new Session($_POST['shop'], '', API_KEY, SECRET);
		header("Location: " . $api->create_permission_url());
	}else{
    $action = (isset($_SESSION['nextAction'])) ? $_SESSION['nextAction'] : 'inventory';
		header("Location: index.php?action=" . $action);
	}
?>