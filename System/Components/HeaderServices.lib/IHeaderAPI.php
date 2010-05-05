<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-04
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface IHeaderAPI
{
    //html > head > script|meta|link|object|style
    
    public function setTitle($title);
    
    public function addScript($type, $src = null, $script = null);
        
    public function addMeta($content, $name = null, $httpEquiv = null, $scheme = null);
        
    public function addLink($charset = null, $href = null, $hreflang = null, $type = null, $title = null, $rel = null, $rev = null, $media = null);
}
?>