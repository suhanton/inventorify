<?php
	include('lib/session.lib.php');
	include('lib/config.lib.php');
	include('lib/database.lib.php');
	if (!file_exists('lib/shopify_api_config.php')) die('lib/shopify_api_config.php is missing!');
	include('lib/shopify_api_config.php');
	include('lib/shopify_api.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');

	$db = new mysqlConn();

	if (isset($_GET['t'])){
		$token = mysql_escape_string($_GET['t']);
		$timestamp = mysql_escape_string($_GET['timestamp']);
		$result = $db->query("SELECT * FROM authorized_shops WHERE token = '$token'", __LINE__);
		if ($db->rows($result) == 0){
			$url = mysql_escape_string($_GET['shop']);
			$signature = mysql_escape_string($_GET['signature']);
			if ($db->query("INSERT INTO authorized_shops (shop, token, signature) VALUES ('$url', '$token', '$signature')", __LINE__)){
				$_SESSION['shop_id'] = $db->id();
				if (isset($_SESSION['nextAction'])){
					$nextAction = $_SESSION['nextAction'];
					unset($_SESSION['nextAction']);					
					header("Location: index.php?action=" . $nextAction);
				}else{
					header("Location: index.php");
				}
			}else{
				header("Location: index.php?action=error");
			}
		}else{		
			$result = $db->fetch($result);
			$_SESSION['shop_id'] = $result['id'];
			header("Location: index.php");
		}
	}else{
		if (isset($_GET['shop'])){
			$url = $_GET['shop'];
			$_SESSION['nextAction'] = (isset($_GET['action'])) ? $_GET['action'] : 'inventory';
			$api = new Session($url, '', API_KEY, SECRET);
			header("Location: " . $api->create_permission_url());
		}else{
			header("Location: index.php?action=authorize");
		}
	}
?>