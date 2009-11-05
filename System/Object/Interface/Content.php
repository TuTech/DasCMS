<?php
interface Interface_Content
{
    public function implementsInterface($interface);
    public function implementsComposite($composite);
    public function attachComposite(Interface_Composites_Attachable $composite);
    
    public function setParentView(VSpore $pv);
    public function getParentView();
    
    public function getId();
    public function getGUID();
    public function getIcon();
    public function getPreviewImage();
    public function getTitle();
    public function getSubTitle();
    public function getMimeType();
    public function getTags();
    public function getAlias();
    public function getSize();
    public function getPubDate();
    public function getSource();
    public function getContent();
    public function getDescription();
    public function getText(); 
}
?>