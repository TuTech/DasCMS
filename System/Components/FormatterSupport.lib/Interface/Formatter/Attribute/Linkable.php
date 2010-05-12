<?php
/**
 * @deprecated
 */
interface Interface_Formatter_Attribute_Linkable
{
    public function isLinkingEnabled();

    public function setLinkingTarget($targetView);

    public function getLinkingTarget();
}
?>