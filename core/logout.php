<?php
	session_destroy();
	$shop = $api->shop->get();
	header("Location: http://" . $shop['domain']);
?>