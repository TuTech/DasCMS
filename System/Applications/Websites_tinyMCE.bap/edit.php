<?php
$enableWYSIWYG = Core::settings()->get('AWebsiteEditor_WYSIWYG');
$enableWYSIWYG = !empty ($enableWYSIWYG);

if($enableWYSIWYG){
	?>
	<script type="text/javascript">
		if(tinyMCE && tinyMCE.init){
			tinyMCE.init({
				// General options
				mode : "exact",
				theme : "advanced",
				skin : "o2k7",
				skin_variant : "black",
				elements : id,
				plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
				document_base_url:"<?php echo SLink::base()?>",

				// Theme options
				theme_advanced_buttons1 : "<?php echo Core::settings()->get('AWebsiteEditor_WYSIWYG_row1'); ?>",
				theme_advanced_buttons2 : "<?php echo Core::settings()->get('AWebsiteEditor_WYSIWYG_row2'); ?>",
				theme_advanced_buttons3 : "<?php echo Core::settings()->get('AWebsiteEditor_WYSIWYG_row3'); ?>",
				theme_advanced_buttons4 : "<?php echo Core::settings()->get('AWebsiteEditor_WYSIWYG_row4'); ?>",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true//,

				// Example content CSS (should be your site CSS)
				//content_css : "Content/stylesheets/default.css",

				// Drop lists for link/image/media/template dialogs
				//template_external_list_url : "lists/template_list.js",
				//external_link_list_url : "lists/link_list.js",
				//external_image_list_url : "lists/image_list.js",
				//media_external_list_url : "lists/media_list.js"
			});
		}
	</script>
	<?php
}
//FIXME tinyMCE Droplist support
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */
$Page = SApplication::getControllerContent();
if(isset($Page) && $Page instanceof CPage)
{
    echo new WContentTitle($Page);
    $editor = new WTextEditor($Page->Content);
	$editor->setCodeAssist(!$enableWYSIWYG);
    echo $editor;
}
?>