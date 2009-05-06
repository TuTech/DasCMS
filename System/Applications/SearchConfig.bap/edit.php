<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2009-04-28
 * @version 1.0
 */
$Search = SApplication::getControllerContent();
if($Search instanceof CSearch)
{
    echo new WContentTitle($Search);
}
?>
<script type="text/javascript">
function ch(id, val)
{
	$(id+'_1').disabled = !(val & 1);
	$(id+'_2').disabled = !(val & 2);
}
</script>
<table>
	<tr>
		<th>Element</th>
		<th>Position</th>
		<th>Above-caption</th>
		<th>Below-caption</th>
	</tr>
	<tr>
		<th>Next-link</th>
		<td><select id="nl" onchange="ch(this.id, this.options[this.selectedIndex].value);">
			<option value="0">Disable</option>
			<option value="1">Above Results</option>
			<option value="2">Below Results</option>
			<option value="3">Above &amp; Below Results</option>
		</select></td>
		<td><input type="text" id="nl_1" /></td>
		<td><input type="text" id="nl_2" /></td>
	</tr>

	<tr>
		<th>Previous-link</th>
		<td><select id="pl" onchange="ch(this.id, this.options[this.selectedIndex].value);">
			<option value="0">Disable</option>
			<option value="1">Above Results</option>
			<option value="2">Below Results</option>
			<option value="3">Above &amp; Below Results</option>
		</select></td>
		<td><input type="text" id="pl_1" /></td>
		<td><input type="text" id="pl_2" /></td>
	</tr>

	<tr>
		<th>Result overview</th>
		<td><select id="ro" onchange="ch(this.id, this.options[this.selectedIndex].value);">
			<option value="0">Disable</option>
			<option value="1">Above Results</option>
			<option value="2">Below Results</option>
			<option value="3">Above &amp; Below Results</option>
		</select></td>
		<td><input type="text" id="ro_1" /></td>
		<td><input type="text" id="ro_2" /></td>
	</tr>

	<tr>
		<th>Search form</th>
		<td><select id="sf" onchange="ch(this.id, this.options[this.selectedIndex].value);">
			<option value="0">Disable</option>
			<option value="1">Above Results</option>
			<option value="2">Below Results</option>
			<option value="3">Above &amp; Below Results</option>
		</select></td>
		<td><input type="text" id="sf_1" /></td>
		<td><input type="text" id="sf_2" /></td>
	</tr>
</table>
+ Target View