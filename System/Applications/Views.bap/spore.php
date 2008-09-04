<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');

if(BAMBUS_GRP_EDIT)
{
	printf(
		'<form method="post" id="documentform" name="documentform" action="%s"><input type="hidden" name="posted" value="1" />', SLink::link()
	);
}
?>
<div id="objectInspectorActiveFullBox">
<h3><?php SLocalization::out('create_new_view'); ?></h3>
<table id="spores" cellspacing="0" class="borderedtable full">
	<tr>
		<th class="td20">
			<?php SLocalization::out('access_var'); ?>
		</th>
		<th class="tdicon">
			<?php SLocalization::out('active'); ?>
		</th>
		<th colspan="1">
			<?php SLocalization::out('default_content'); ?>
		</th>
		<th colspan="1">
			<?php SLocalization::out('error_content'); ?>
		</th>
	</tr>
	<tr class="flip_1" valign="top">
		<td class="td20">
			<input type="text" name="new_spore" value="" onkeyup="validateField(this, 'spore');" onblur="validateField(this, 'spore');" onchange="validateField(this, 'spore');"/>
		</td>
		<td>
			<input type="checkbox" name="new_actv" />
		</td>
		<td>
			<a class="right" href="javascript:clearOpt('new_init');">
				<img src="System/Icons/16x16/actions/delete.png" alt="remove" title="remove" />
			</a>
			<input readonly="readonly" type="hidden" onfocus="lastFocus = 'new_init';" id="new_init" name="new_init" value="" />
			<input readonly="readonly" type="text"   onfocus="lastFocus = 'new_init';" id="new_init_t" value="" />
		</td>
		<td>
			<a class="right" href="javascript:clearOpt('new_err');">
				<img src="System/Icons/16x16/actions/delete.png" alt="remove" title="remove" />
			</a>
			<input readonly="readonly" type="hidden" onfocus="lastFocus = 'new_err';" id="new_err" name="new_err" value="" />
			<input readonly="readonly" type="text"   onfocus="lastFocus = 'new_err';" id="new_err_t" value="" />
		</td>
	</tr>
</table>
<?php
$sporeData = QSpore::getSpores();
$spores = array_keys($sporeData);
if(count($spores) > 0)
{
	echo '<h3>',SLocalization::get('current_views'), '</h3>'
		,'<table id="spores" cellspacing="0" class="borderedtable full">'
		,'<tr><th class="td20">'
			,SLocalization::get('access_var')
		,'</th><th class="tdicon">'
			,SLocalization::get('active')
		,'</th><th>'
			,SLocalization::get('default_content')
		,'</th><th>'
			,SLocalization::get('error_content')
		,'</th></tr>'
		; 

	$flip = true;
		
	foreach ($sporeData as $spore => $data) 
	{
		$initCTitle = '';
		$initCID = '';
		if(!empty($data[QSpore::INIT_CONTENT]))
		{
			list($man, $id) = explode(':', $data[QSpore::INIT_CONTENT]);
			$initCMan = BObject::InvokeObjectByDynClass($man);
			if($initCMan != null && $initCMan->Exists($id))
			{
				$initCTitle = substr($man,1).': '.$initCMan->Open($id)->Title;
				$initCID = $data[QSpore::INIT_CONTENT];
			}
		}
		
		$errCTitle = '';
		$errCID = '';
		if(!empty($data[QSpore::ERROR_CONTENT]))
		{
			list($man, $id) = explode(':', $data[QSpore::ERROR_CONTENT]);
			$errCMan = BObject::InvokeObjectByDynClass($man);
			if($errCMan != null && $errCMan->Exists($id))
			{
				$errCTitle = substr($man,1).': '.$errCMan->Open($id)->Title;
				$errCID = $data[QSpore::ERROR_CONTENT];
			}
		}
		
		$check = ($data[QSpore::ACTIVE]) ? ' checked="checked"' : '';
		
		$outSpore = htmlentities($spore, ENT_QUOTES, 'utf-8');
		echo 
		'<tr class="flip_',($flip+1),'" valign="top">',
			'<td>',
				'<a class="right" href="javascript:toggleSporeRemove(\'',$outSpore,'\');">',
					'<img id="spore_',$outSpore,'_rm" src="System/Icons/16x16/actions/delete.png" alt="set remove flag" title="set remove flag" />',
					'<img id="spore_',$outSpore,'_norm" style="display:none;" src="System/Icons/16x16/actions/refresh.png" alt="unset remove flag" title="unset remove flag" />',
				'</a>',
				'<span id="spore_',$outSpore,'_t">',
					$outSpore,
				'</span>',
				'<input type="hidden" id="spore_',$outSpore,'" name="spore_',$outSpore,'"value="" />',
			'</td>',
			'<td>',
				'<input type="checkbox" name="actv_',$outSpore,'"',$check,' />',
			'</td>',
			'<td>',
				'<a class="right" href="javascript:clearOpt(\'init_',$outSpore,'\');">',
					'<img src="System/Icons/16x16/actions/delete.png" alt="remove" title="remove" />',
				'</a>',
				'<input readonly="readonly" type="hidden" onfocus="lastFocus = \'init_',$outSpore,'\';" id="init_',$outSpore,'" name="init_',$outSpore,'" value="',$initCID,'" />',
				'<input readonly="readonly" type="text"   onfocus="lastFocus = \'init_',$outSpore,'\';" id="init_',$outSpore,'_t" value="',$initCTitle,'" />',
			'</td>',
			'<td>',
				'<a class="right" href="javascript:clearOpt(\'err_',$outSpore,'\');">',
					'<img src="System/Icons/16x16/actions/delete.png" alt="remove" title="remove" />',
				'</a>',
				'<input readonly="readonly" type="hidden" onfocus="lastFocus = \'err_',$outSpore,'\';" id="err_',$outSpore,'" name="err_',$outSpore,'" value="',$errCID,'" />',
				'<input readonly="readonly" type="text"   onfocus="lastFocus = \'err_',$outSpore,'\';" id="err_',$outSpore,'_t" value="',$errCTitle,'" />',
			'</td>',
		'</tr>'
		;
		$flip = !$flip;
	}
	echo '</table>';
}
else
{
	echo '<h3>', SLocalization::get('please_add_at_least_one_-_you_need_them_for_viewing_any_content,_really'),'</h3>';
}
?>
</div>
<?php
if(BAMBUS_GRP_EDIT)
{
	echo '</form>';
}	
	?>
