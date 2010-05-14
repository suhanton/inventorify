<h1>Preferences</h1>

<p>Here you can modify your Inventorify preferences.</p>

<form method="post" action="index.php?action=preferences">
<table width="100%" cellspacing="0" cellpadding="1" border="0">
	<tr>
		<td width="200">Default Products to Display</td>
		<td>
			<select name="perPage">
			{PER_PAGE}<option value="{PER_PAGE_val}" {PER_PAGE_selected}>{PER_PAGE_val}</option>
			{/PER_PAGE}
			</select>
		</td>		
	</tr>	
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Update Preferences" /></td>
	</tr>
</table>
<input type="hidden" name="updatePreferences" value="1" />
</form>

<div id="flashnotice" style="display:none;"></div>
<div id="flasherror" style="display:none;"></div>

<script type="text/javascript" language="javascript">
	$(document).ready(function(){
		{JAVASCRIPT}
	});
</script>