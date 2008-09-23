<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/

if(!isset($channel))
{
	echo new WScript('org.bambuscms.autorun.register(function(){OBJ_ofd.show()});');
}
else
{

	?>
		<script type="text/javascript">
		<!--
			function showTpl(options, selected, tplid)
			{
				document.getElementById(tplid).disabled = (options[selected].value != 'template');
			}
		// -->
		</script><div id="objectInspectorActiveFullBox">
	<h2><?php echo htmlspecialchars($channel->Title, ENT_QUOTES, 'utf-8'); ?></h2>
	<h3><?php SLocalization::out('general_settings'); ?></h3>
	<table cellspacing="0" class="borderedtable full">
		<tr>
			<th>
				<?php SLocalization::out('description'); ?>
			</th>
			<th>
				<?php SLocalization::out('value'); ?>
			</th>
		</tr>
		<tr class="flip_2" valign="top">
			<th scope="row">
				<label for="fileNameInput"><?php SLocalization::out('title'); ?>:</label>
			</th>
			<td>
				<input type="text" id="fileNameInput" name="fileNameInput" value="<?php echo htmlspecialchars($channel->Title, ENT_QUOTES, 'utf-8'); ?>" />
			</td>
		</tr>		
		<tr class="flip_1">
			<th scope="row">
				<label for="type1"><?php SLocalization::out('type'); ?>:</label>
			</th>
			<td>
				<select name="type" id="type1" onchange="if(this.options[this.selectedIndex].value.substr(0,6) != 'predef'){document.getElementById('filt1').disabled = true;document.getElementById('filt1').style.color='#d3d7cf';}else{document.getElementById('filt1').disabled = false;document.getElementById('filt1').style.color='#000';}">
					<option <?php if($channel->FilterType == 'tags' && $channel->Filter !== null)echo ' selected="selected"'; ?> value="predef-tags">News-feed filtered by tags</option> 
					<option disabled="disabled" <?php if($channel->FilterType == 'search' && $channel->Filter !== null)echo ' selected="selected"'; ?>value="predef-search">News-feed filtered by fulltext search</option>
					<option disabled="disabled" <?php if($channel->FilterType == 'tags' && $channel->Filter === null)echo ' selected="selected"'; ?>value="usr-tags">Tag based search</option> 
					<option disabled="disabled" <?php if($channel->FilterType == 'search' && $channel->Filter === null)echo ' selected="selected"'; ?>value="usr-search">Fulltext search</option>
				</select>
			</td>
		</tr>
		<tr class="flip_2" valign="top">
			<th scope="row">
				<label for="filt1"><?php SLocalization::out('define_filter'); ?>:</label>
			</th>
			<td>
				<textarea rows="2" cols="50" id="filt1" name="filter"><?php echo htmlspecialchars($channel->Filter, ENT_QUOTES, 'utf-8'); ?></textarea>
			</td>
		</tr>
	</table>
	
	<h3><?php SLocalization::out('behavior'); ?></h3>
	<table cellspacing="0" class="borderedtable full">
		<tr>
			<th>
				<?php SLocalization::out('description'); ?>
			</th>
			<th>
				<?php SLocalization::out('value'); ?>
			</th>
		</tr>
		<tr class="flip_1">
			<th scope="row">
				<label for="itemsperpage"><?php SLocalization::out('items_per_page'); ?>:</label>
			</th>
			<td>
				<input type="text" name="itemsperpage" id="itemsperpage" value="<?php echo htmlspecialchars($channel->ItemsPerPage, ENT_QUOTES, 'utf-8'); ?>" />
			</td>
		</tr>
		<?php
		$templates = DFileSystem::FilesOf(SPath::TEMPLATES, '/\.tpl$/');
		sort($templates, SORT_LOCALE_STRING);
		?>
		<tr class="flip_2" valign="top">
			<th scope="row">
				<label for="overview"><?php SLocalization::out('overview'); ?>:</label>
			</th>
			<td>
				<select name="overview" id="overview" onchange="showTpl(this.options, this.selectedIndex, 'otpl');">
					<option <?php if($channel->OverViewMode === CFeed::TITLE)echo ' selected="selected"'; ?>value="t">title</option>
					<option <?php if($channel->OverViewMode === CFeed::TITLE_AND_SUMMARY)echo ' selected="selected"'; ?>value="ts">title + summary</option>
					<option <?php if($channel->OverViewMode === CFeed::TITLE_AND_CONTENT)echo ' selected="selected"'; ?>value="tc">title + content</option>
					<option disabled="disabled" <?php if(!is_numeric($channel->OverViewMode))echo ' selected="selected"'; ?>value="template">Template:</option>
				</select>
				<select name="overview_template" disabled="disabled" id="otpl">
					<option value="">Template</option>
					<?php
					foreach ($templates as $tpl) 
					{
						printf('<option%s>%s</option>', ($tpl === $channel->OverViewMode ? ' selected="selected"' : ''), htmlspecialchars($tpl, ENT_QUOTES, 'utf-8'));
					}
					
					?>
				</select>
			</td>
		</tr>
	</table>
	</div>
	<?php
}
?>