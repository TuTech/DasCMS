<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-02-24
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Information extends _View_UIElement implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * @return array
	 */
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(View_UIElement_SidePanel::PROPERTY_EDIT)
	    );
	}
	
	public function getName()
	{
	    return 'content_information';
	}
	
	public function getIcon()
	{
	    return new View_UIElement_Icon('inform','',View_UIElement_Icon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
		$html = '<div id="View_UIElement_Information">';
		try{
    		//init values
    		$Items = new View_UIElement_NamedList();
    	    $MetaItems = new View_UIElement_NamedList();
    	    $MetaItems->setTitleTranslation(true);
    		$meta = array(
    			'Alias' => 'alias',
    			'GUID' => 'id', 
    			'PubDate' => 'pubDate',
    			'ModifyDate' => 'modified',
    			'ModifiedBy' => 'modified_by', 
    			'CreateDate' => 'created', 
    			'CreatedBy' => 'created_by', 
    			'Size' => 'size',
    		    'LastAccess' => 'last_access',
    		    'AccessCount' => 'access_count',
    		    'AccessIntervalAverage' => 'average_time_between_accesses'
    		);
    		foreach ($meta as $key => $name) 
    		{
    		    $val = '-';
				$subjectValue = $this->targetObject->{'get'.$key}();
    		    if(strlen($subjectValue) > 0)
    		    {
    		        if(substr($key,-4) == 'Date' || $key == 'LastAccess') 
    		        {
    		            $val = $subjectValue > 0 ? date('Y-m-d H:i:s',$subjectValue) : '';
    		        }
    		        elseif(substr($key,-4) == 'Size')
    		        {
    		            $val = Core::FileSystem()->formatFileSize($subjectValue);
    		        }
    		        elseif($key == 'AccessIntervalAverage')
    		        {
    		            $currentUnit = 0;
    		            $units = array('seconds' => 60, 'minutes' => 60, 'hours' => 24, 'days' => 365, 'years' => 100);
    		            $val = floatval($subjectValue);
    		            if($val > 0)
    		            {
        		            foreach ($units as $unit => $mustBeUnder)
        		            {
        		                if($val < $mustBeUnder)
        		                {
        		                    $currentUnit = $unit;
        		                    break;
        		                }
        		                $val /= $mustBeUnder;
        		            }
        		            $val = round($val). ' '.SLocalization::get($currentUnit);
    		            }
    		        }
    		        else
    		        {
    		            $val = String::htmlEncode(wordwrap($subjectValue,15,'<wbr />',true));
    		            $val = str_replace('&lt;wbr /&gt;', '<wbr />', $val);
    		        }
    		    }
    		    $MetaItems->add($name, $val);
    		}
    		$Items->add(   
    		    sprintf("<label>%s</label>", SLocalization::get('meta_data')),
    		    $MetaItems
    	    );
    		$html .= $Items;
    		$html .= '</div>';
		}
		catch (Exception $e)
		{
		    echo $e->getMessage(),"\n",$e->getTraceAsString();
		}
		return $html;
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>