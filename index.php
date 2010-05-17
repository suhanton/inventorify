<?php
	/* Libraries */
	include('lib/session.lib.php');
	include('lib/config.lib.php');
	include('lib/template.lib.php');
	if (!file_exists('lib/shopify_api_config.php')) die('lib/shopify_api_config.php is missing!');
	include('lib/shopify_api_config.php');
	include('lib/shopify_api.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');
	/* End Libraries */

	$action = (isset($_GET['action'])) ? $_GET['action'] : 'inventory';
	
	if (isset($_SESSION['url'])){
		$url = $_SESSION['url'];
		$token = $_SESSION['token'];
		$signature = $_SESSION['signature'];
		$timestamp = $_SESSION['timestamp'];
	}else{
		$action = "authorize";
	}
	
	$tagArray = array();
	$loopArray = array();
	
	if (!file_exists('core/' . $action . '.php') || !file_exists('templates/' . $action . '.tpl')) $action = "error";
	
	if (isset($_SESSION['token'])){
		$api = new Session($url, $token, API_KEY, SECRET);
		if (!$api->valid()) $action = "error";
	}
	
	//Header
	include('header.php');
	$t = new Template('templates/header.tpl');
	$output = $t->output($tagArray, $loopArray);	
	empty($tagArray); empty($loopArray);
	
	//Index
	include('core/' . $action . '.php');
	$t = new Template('templates/' . $action . '.tpl');
	$tagArray['JAVASCRIPT_INCLUDE'] = (file_exists('js/' . $action .'.js')) ? file_get_contents('js/' . $action . '.js') : '';
	$output .= $t->output($tagArray, $loopArray);
	empty($tagArray); empty($loopArray);
	
	//Footer
	include('footer.php');
	$t = new Template('templates/footer.tpl');
	$output .= $t->output($tagArray, $loopArray);
	empty($tagArray); empty($loopArray);
	
	echo $output;
?>