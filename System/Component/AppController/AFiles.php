<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AFiles
    extends 
        BAppController 
    implements 
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.files';
    
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */    
    public function getClassGUID()
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
        $IDindex = CFile::Index();
        $items = array();
        $types = array();
        $i = 0;
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate, $type) = $data;
        	if(!array_key_exists($type, $types))
        	{
        	    $types[$type] = $i++;
        	}
        	$items[] = array($title, $alias, $types[$type], strtotime($pubdate), $type);
        }
        $xsi = array();
        $li = array();
        foreach ($types as $type => $index) 
        {
        	$xsi[$index] = WIcon::pathForMimeIcon($type, WIcon::EXTRA_SMALL);
        	$li[$index] = WIcon::pathForMimeIcon($type, WIcon::LARGE);
        }
        
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => $li,
            'smallIconMap' => $xsi,
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3, 'type' => 4),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate', 'type' => 'type'),
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
     * provide list of folders 
     * @param array $param
     * @throws XPermissionDeniedException
     * @return array
     */
    public function getFolders(array $param)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        //sleep(2);
        $data =  CFile::getFolders();
        return array(
            'folders' => array_values($data), 
            'folderIds' => array_keys($data));
    }
    
    /**
     * provide list of files in given folder
     * @param array $params
     * @return array
     */
    public function getFiles(array $params)
    {
        $folder = isset($params['folder']) ? $params['folder'] : null;
        //Contents.contentID => [Aliases.alias, Contents.title, Contents.size, Mimetypes.mimetype]
        $contents = CFile::getFilesOfFolder($folder);
        $typeMap = array();
        $out = array(
            'ids' => array(), 
            'items' => array(),
            'types' => array(),
            'typeNames' => array(),
            'typeIcons' => array()
        );
        foreach ($contents as $id => $data) 
        {
            $nr = count($out['ids']);
        	$out['ids'][$nr] = $id;
        	$out['items'][$nr] = $data[1];
        	if(array_key_exists($data[3], $typeMap))
        	{
        	    $out['types'][$nr] = $typeMap[$data[3]];
        	}
        	else
        	{
        	    $tnr = count($typeMap);
        	    $typeMap[$data[3]] = $tnr;
        	    $out['types'][$nr] = $tnr;
        	    $out['typeNames'][$tnr] = $data[3];
        	    $out['typeIcons'][$tnr] = WIcon::pathForMimeIcon($data[3], WIcon::EXTRA_SMALL);
        	}
        }
        return $out;
    }
}
?>