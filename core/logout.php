<?php
	session_destroy();
	header("Location: http://" . $api->shop->shop['domain']);
?>