<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.protectiontags
 * @since 2008-11-12
 * @version 1.0
 */
$tags =  implode(', ', STagPermissions::getProtectedTags());
echo new WIntroduction('define_restriction_tags', 'tags_listed_here_restrict_access_to_contents');
$editor = new WTextEditor($tags);
$editor->disableSpellcheck();
echo $editor;

?>