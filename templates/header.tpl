<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
	<meta http-equiv="imagetoolbar" content="no" /> 
	<title>{T_TITLE}</title>
	<link href="{DOMAIN}/css/css.css" media="screen" rel="stylesheet" type="text/css" /> 
	<script src="{DOMAIN}/js/jquery.js" type="text/javascript" language="javascript"></script>
</head>
<body>
	<div id="header"> 
		<h1><a href="/">{T_TITLE}</a></h1>      
	</div> 

	<div id="container" class="clearfix"> 

		<ul id="tabs"> 
			{LI}<li><a href="{LI_href}" id="{LI_class}">{LI_text}</a></li>
			{/LI}
		</ul>
		
		<div id="main" class="clearfix">
