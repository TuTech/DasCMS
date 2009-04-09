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
        if(!empty($param['create'])
            || !empty($param['first_name'])
            || !empty($param['last_name'])
            || !empty($param['company']))
        {
            $this->target = CPerson::Create(isset($param['create']) ? $param['create'] : '');
            foreach(array(
                    'PersonTitle' => 'title',
                    'FirstName' => 'first_name',
                    'LastName' => 'last_name',
                    'Company' => 'company'
                ) as $func => $att)
            {
                if(!empty($param[$att]))
                {
                    $this->target->{'set'.$func}($param[$att]);
                }
            }
            $this->target->Save();
        }
    }
    
    //for post array
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.change');
        if (!$this->target instanceof CPerson) 
        {
        	return;
        }
		$_attribute = 'a_';
		$_entry = 'e_';
		$_count = 'n';
		$_contexts = 'c_';
		$_context = '_c';
		$_value = '_v';
		$_type = 't';
        if($this->target != null
            && isset($param['a_n']))
        {
            //set xattr
            foreach(array(
                    'PersonTitle' => 'title',
                    'FirstName' => 'first_name',
                    'LastName' => 'last_name',
                    'Company' => 'company'
                ) as $func => $att)
            {
                if(!empty($param[$att]))
                {
                    $this->target->{'set'.$func}($param[$att]);
                }
            }
            //attributes
            $attributes = new WCPersonAttributes();
            for($i = 1; $i <= intval($param[$_attribute.$_count],'10'); $i++)
            {
                $currentAttribute = $_attribute.$i.'_';
                if(!empty($param[$_attribute.$i])
                    && !empty($param[$currentAttribute.$_type])
                    && !empty($param[$currentAttribute.$_contexts.$_count])
                    )
                {
                    try
                    {
                        $attName = $param[$_attribute.$i];
                        $attType = $param[$currentAttribute.$_type];
                        $attContexts = array();
                        for($c = 1; $c <= intval($param[$currentAttribute.$_contexts.$_count]); $c++)
                        {
                            if(!empty($param[$currentAttribute.$_contexts.$c]))
                            {
                                $attContexts[] = $param[$currentAttribute.$_contexts.$c];
                            }
                        }
                        $attribute = new WCPersonAttribute($attName, $attType, $attContexts);
                        $attributes->addAttribute($attribute);
                        for($e = 1; $e <= intval($param[$currentAttribute.$_entry.$_count]); $e++)
                        {
                            if(!empty($param[$currentAttribute.$_entry.$e.$_context])
                                && !empty($param[$currentAttribute.$_entry.$e.$_value]))
                            {
                                $entry = new WCPersonEntry(
                                    $attribute, 
                                    $param[$currentAttribute.$_entry.$e.$_context], 
                                    $param[$currentAttribute.$_entry.$e.$_value]
                                );
                                $attribute->addEntry($entry);
                            }
                        }
                    }
                    catch (Exception $e){}
                }
            }
            $this->target->setContent($attributes);
        }
    }
    
    //for JSON object
    public function saveObject(array $param)
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
            if(CPerson::Delete($alias))
            {
                $this->target = null;
            }
        }
    }
    
    public function commit()
    {
        if($this->target != null && $this->target->isModified())
        {
            $this->target->Save();
        }
    } 
    
    public function revokeLogin(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.credentials.revoke');
        if($this->target != null 
            && !empty($param['user']) 
            && $param['user'] == $this->target->getLoginName()
            && $param['user'] != PAuthentication::getUserID())
        {
            $this->target->removeLogin();
        }
        elseif(!empty($param['user']) 
            &&$param['user'] == PAuthentication::getUserID())
        {
            SNotificationCenter::report('warning', 'you_should_not_and_can_not_revoke_your_login_rights');
        }
    }
    
    public function createLogin(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cperson.credentials.grant');
        if($this->target != null)
        {
            if(empty($param['loginName']) 
                || empty($param['password'])
                || empty($param['passwordCheck']) 
            )
            {
                return false;
            }
            if($param['password'] != $param['passwordCheck'])
            {
                SNotificationCenter::report('warning', 'password_does_not_match_password_check');
                return false;
            }
            if($this->target->hasLogin())
            {
                SNotificationCenter::report('warning', 'this_user_already_has_login_credentials');
                return false;
            }
            if(CPerson::isUser($param['loginName']))
            {
                SNotificationCenter::report('warning', 'username_already_in_use');
                return false;
            }
            if($this->target->createLogin($param['loginName'], $param['password']))
            {
                SNotificationCenter::report('message', 'login_created');
            }
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
        $IDindex = CPerson::IndexWithCompany();
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate, $type, $id, $company) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate), $company ? $company : '');
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/CUser.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/CUser.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3, 'company' => 4),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate', 'company' => 'company'),
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
                'company' => SLocalization::get('company'),
                'title' => SLocalization::get('title')
            )
        );
        return $data;
    }
}
?>