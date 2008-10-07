<?php
class AWebsiteEditor 
    extends 
        BAppController 
    implements 
        IACProviderOpenDialogData 
{
    const GUID = 'org.bambuscms.applications.websiteeditor';
    
    protected function __construct()
    {
        //get an instance by id from the base class
    }
    
    public function getGUID()
    {
        return self::GUID;
    }
    
    /**
     * returns all data necessary for the open dialog
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        $SCI = SContentIndex::alloc()->init();
        $IDindex = $SCI->getIndex('MPageManager');
        $CMDIDS = array();
        foreach ($IDindex as $key => $ttl) 
        {
        	$CMDIDS[] = 'MPageManager:'.$key;
        }
        $index = $SCI->getContentInformationBulk($CMDIDS);
        $items = array();
        foreach($index as $item)
        {
            $items[] = array($item['Title'], $item['MCID'], 0, $item['PubDate']);//
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/Icons/tango/large/mimetypes/CPage.png'),
            'smallIconMap' => array('System/Icons/tango/extra-small/mimetypes/CPage.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items,
            'captions' => array(
                'detail' => SLocalization::get('detail'),
                'icon' => SLocalization::get('icon'),
                'list' => SLocalization::get('list'),
                'asc' => SLocalization::get('asc'),
                'desc' => SLocalization::get('desc'),
                'searchByTitle' => SLocalization::get('search_by_title'),
                'pubDate' => SLocalization::get('pubDate'),
                'notPublished' => SLocalization::get('not_published'),
                'title' => SLocalization::get('title'),
                'type' => SLocalization::get('type'),
            )
        );
        return $data;
    }
    
    /**
     * delete a bunch of items
     */
    public function delete(array $items)
    {
        
    }
    
    /**
     * create a new item
     */
    public function create($title, array $options)
    {
        
    }
    
    /**
     * set all kinds of possible meta attributes
     */
    public function setAttributes(array $attributes)
    {
        
    }
    
    
    
    
}
?>