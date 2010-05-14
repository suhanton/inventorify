<h3>Search</h3>
<form method="post" action="index.php?action=inventory" id="search_form">
	<table cellspacing="0" cellpadding="1" border="0">
		<tr>
			<td width="100">Product Name</td>
			<td><input type="text" name="product_name" id="product_name" value="{PRODUCT_NAME}" /></td>
		</tr>
		<tr>
			<td>By Collection</td>
			<td> 
				<select name="collection" id="collection">
					<option value=""> </option>
					<optgroup label="Smart Collection">
					{SMART_COLLECTION}<option value="SC_{SMART_COLLECTION_id}" {SMART_COLLECTION_selected}>{SMART_COLLECTION_title}</option>
					{/SMART_COLLECTION}	
					<optgroup label="Custom Collection">
					{CUSTOM_COLLECTION}<option value="CC_{CUSTOM_COLLECTION_id}" {CUSTOM_COLLECTION_selected}>{CUSTOM_COLLECTION_title}</option>
					{/CUSTOM_COLLECTION}
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right;">
				<input type="submit" value="Search" />
				<input type="button" value="Reset" onclick="Javascript:resetSearch();" />
			</td>
		</tr>
	</table>
	<input type="hidden" name="perPage" value="{PERPAGE}" />
	<input type="hidden" name="search" value="1" />
</form>

<h3>Products</h3>
<div style="text-align:right;"><input type="button" value="Update" onclick="Javascript:$('#product_form').submit();" /></div>
<table width="100%" cellspacing="0" cellpadding="1" border="0" id="product_table">
<form method="post" action="index.php?action=inventory" id="per_page">
	<tr>
		<td width="25%">Display:
			<select name="perPage" onchange="Javascript:$('#per_page').submit();">
			{PER_PAGE}<option value="{PER_PAGE_val}" {PER_PAGE_selected}>{PER_PAGE_val}</option>
			{/PER_PAGE}
			</select>
		</td>
		<td style="text-align:right;">Page: 
			{PAGES}<a href="index.php?action=inventory&amp;page={P_page}" style="text-decoration:none;{P_style}">{PAGES_page}</a>&nbsp;{/PAGES}
		</td>
	</tr>
	
	<input type="hidden" name="product_name" value="{PRODUCT_NAME}" />
	<input type="hidden" name="collection" value="{COLLECTION}" />
	<input type="hidden" name="search" value="{SEARCH}" />
</form>	
<form method="post" action="index.php?action=inventory&amp;page={PAGE}" id="product_form">
{PRODUCT}
	<tr class="alt">
		<th style="text-align:left;" colspan="3">{PRODUCT_title}</th>
	</tr>
	<tr class="alt">
		<th style="text-align:left;width:50%;">SKU</th>
		<th style="text-align:left;width:25%;">Inventory</th>
		<th style="text-align:left;width:25%;">Manage</th>
	</tr>
	{VARIANT_{PRODUCT_id}}
	<tr>
		<td>
			<input type="text" id="variant_{VARIANT_{PRODUCT_id}_id}_sku" name="variant_{VARIANT_{PRODUCT_id}_id}_sku" value="{VARIANT_{PRODUCT_id}_sku}">
		</td>
		<td>
			<input type="text" id="variant_{VARIANT_{PRODUCT_id}_id}_quantity" name="variant_{VARIANT_{PRODUCT_id}_id}_quantity" value="{VARIANT_{PRODUCT_id}_inventory-quantity}" size="4" {VARIANT_{PRODUCT_id}_disabled} />
		</td>
		<td>
			<input type="checkbox" id="variant_{VARIANT_{PRODUCT_id}_id}_management" name="variant_{VARIANT_{PRODUCT_id}_id}_management" onclick="Javascript:manageVariant({VARIANT_{PRODUCT_id}_id});" {VARIANT_{PRODUCT_id}_checked}>
		</td>
	</tr>
	{/VARIANT_{PRODUCT_id}}
{/PRODUCT}

	<input type="hidden" name="perPage" value="{PERPAGE}" />
	<input type="hidden" name="product_name" value="{PRODUCT_NAME}" />
	<input type="hidden" name="collection" value="{COLLECTION}" />
	<input type="hidden" name="search" value="{SEARCH}" />
	<input type="hidden" name="updateInventory" value="1" />
</form>
</table>
<div style="text-align:right;"><input type="button" value="Update" onclick="Javascript:$('#product_form').submit();" /></div>


<div id="flashnotice" style="display:none;"></div>
<div id="flasherrors" style="display:none;"></div>

<script type="text/javascript" language="javascript">
	$(document).ready(function(){
		{JAVASCRIPT}
	});
</script>
