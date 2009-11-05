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
        IGlobalUniqueId,
        ISupportsOpenDialog  
{
    const GUID = 'org.bambuscms.applications.files';
    
    /**
	 * @var CFile
     */
    protected $target = null;
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = Controller_Content::getSharedInstance()->openContent($target, 'CFile');
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfile.create');
        try
        {
            $this->target = CFile::Create(isset($param['create']) ? $param['create'] : '');
            $suffix = strtolower(substr($this->target->getFileName(),-4));
            $this->target->changeSearchIndexingStatus(in_array($suffix, array('.pdf','.ppt','.xls','.doc','.zip')));
        }
        catch (XFileNotFoundException $e)
        {
            return;/*nothing sent*/
        }
        catch (Exception $e)
        {
            SNotificationCenter::report('warning', 'file_not_created');
        }
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfile.change');
        if($this->target != null) 
        {
            if(!empty($param['title']))
            {
                $this->target->Title = $param['title'];
            }
            if(isset($param['subtitle']))
            {
                $this->target->SubTitle = $param['subtitle'];
            }
            //set new file content
            if(RFiles::has('CFile'))
            {
                $this->target->updateData();
            }
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfile.delete');
        if($this->target != null)
        {
            $dbid = $this->target->getId();
            $alias = $this->target->Alias;
            if(Controller_Content::getSharedInstance()->deleteContent($alias))
            {
                SErrorAndExceptionHandler::muteErrors();
                unlink('Content/CFile/'.$dbid.'.data');
                SErrorAndExceptionHandler::reportErrors();
                $this->target = null;
            }
        }
    }
    
    public function massDelete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfile.delete');
        if(!empty($param['delete']))
        {
            $aliases = explode(',', str_replace(';', ',', $param['delete']));
            foreach ($aliases as $alias)
            {
                $alias = trim($alias);
                if(!empty($alias))
                {
                    try
                    {
                        if(Controller_Content::getSharedInstance()->deleteContent($alias))
                        {
                            SNotificationCenter::report('message', 'file_deleted');
                        }
                    }
                    catch (Exception $e)
                    {
                    	/*ignore and go on*/
                        SNotificationCenter::report('warning', 'could_not_delete_file');
                    }
                }
            }
            $this->target = null;
        }
    }
    
    public function commit()
    {
        if($this->target != null)
        {
            $this->target->Save();
        }
    } 
    
    /**
     * array(CFile)
     * 
     * @return array
     */
    public function getSideBarTarget()
    {
        $ret = array();
        if($this->target)
        {
            $ret = array($this->target);
        }
        return $ret;
    }

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
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget()
    {
        return empty($this->target) ? null : $this->target->Alias;
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
        $IDindex = Controller_Content::getSharedInstance()->contentIndex('CFile');
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
            if(!WImage::supportedMimeType($type))
            {
                $xsi[$index] = WIcon::pathForMimeIcon($type, WIcon::EXTRA_SMALL);
                $li[$index] = WIcon::pathForMimeIcon($type, WIcon::LARGE);
            }
        }
        
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => $li,
            'smallIconMap' => $xsi,
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3, 'type' => 4),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate', 'type' => 'type'),
            'items' => $items
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