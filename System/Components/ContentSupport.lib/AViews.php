<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AViews
    extends 
        BAppController 
    implements 
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.views';
        
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function save(array $data)
    {
        parent::requirePermission('org.bambuscms.view.spore.set');
    	foreach ($data as $key => $value) 
    	{
    		if(substr($key,0,5) == 'spore')
    		{
    			$spore = substr($key,6);
    			$delete = !empty($value);
    			
    			if($delete)
    			{
    				Controller_View_Content::remove($spore);
    			}
    			else
    			{
    				Controller_View_Content::set(
    					$spore, 
    					!empty($data['actv_'.$spore]),
    					$data['init_'.$spore], 
    					$data['err_'.$spore]
    				);
    			}
    		}
    	}
    	if(!empty($data['new_spore'])
    	    && !Controller_View_Content::exists($data['new_spore']))
    	{
    		try{
    			Controller_View_Content::set(
    			    $data['new_spore'],
    			    !empty($data['new_actv']),
    			    $data['new_init'],
    			    $data['new_err']
    			);
    		}
    		catch(Exception $e){
    			SNotificationCenter::report('warning', 'could_not_create_view');
    		}
    	}
    	Controller_View_Content::save();
    	SNotificationCenter::report('message', 'views_saved');
    }
}
?>