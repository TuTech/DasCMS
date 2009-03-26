<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-17
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class APersons
    extends 
        BAppController 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    const GUID = 'org.bambuscms.applications.persons';

    /**
	 * @var CPerson
     */
    protected $target = null;
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = CPerson::Open($target);
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function getPersonData(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.view');
        if($this->target != null)
        {
            $c = $this->target->getContent();
            if($c instanceof WCPersonAttributes)
            {
                return $c->asArray();
            }
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.create');
        if(!empty($param['create']))
        {
            $this->target = CPerson::Create($param['create']);
        }
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.change');
        if($this->target != null
            && isset($param['content']))
        {
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.delete');
        if($this->target != null)
        {
            $alias = $this->target->Alias;
            $this->target = null;
            CPerson::Delete($alias);
        }
    }
    
    public function commit()
    {
        if($this->target != null && $this->target->isModified())
        {
            $this->target->Save();
        }
    } 
    
    /**
     * array(BContent|string file, [string mimetype])
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
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        parent::requirePermission('org.bambuscms.content.cperson.view');
        $IDindex = CPerson::Index();
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/CUser.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/CUser.png'),
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
                'title' => SLocalization::get('title')
            )
        );
        return $data;
    }
}
?>