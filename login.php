<?php
	include('lib/session.lib.php');
	include('lib/config.lib.php');
	if (!file_exists('lib/shopify_api_config.php')) die('lib/shopify_api_config.php is missing!');
	include('lib/shopify_api_config.php');
	include('lib/shopify_api.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');
	
	if (isset($_SESSION['url'])){
    $action = (isset($_GET['action'])) ? $_GET['action'] : DEFAULT_ACTION;
    header("Location: index.php?action=" . $action);
	}else{
	  if (isset($_GET['shop'])){
	    if (isset($_GET['t'])){
  	    $_SESSION['url'] = $_GET['shop'];
    	  $_SESSION['token'] = $_GET['t'];
    	  $_SESSION['timestamp'] = $_GET['timestamp'];
    	  $_SESSION['signature'] = $_GET['signature'];

    		if (isset($_SESSION['nextAction'])){
    			$nextAction = $_SESSION['nextAction'];
    			unset($_SESSION['nextAction']);					
    			header("Location: index.php?action=" . $nextAction);
    		}else{
    			header("Location: index.php");
    		}	      
	    }else{
  			$url = $_GET['shop'];
  			$_SESSION['nextAction'] = (isset($_GET['action'])) ? $_GET['action'] : DEFAULT_ACTION;
  			$api = new Session($url, '', API_KEY, SECRET);
  			header("Location: " . $api->create_permission_url());
	    }
	  }else{
	    if (isset($_GET['action'])) $_SESSION['nextAction'] = $_GET['action'];
      header("Location: index.php?action=authorize");	    
	  }
	}
?>