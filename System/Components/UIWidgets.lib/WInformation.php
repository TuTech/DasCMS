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
class WInformation extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::PROPERTY_EDIT)
	    );
	}
	
	public function getName()
	{
	    return 'content_information';
	}
	
	public function getIcon()
	{
	    return new WIcon('inform','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
		$html = '<div id="WInformation">';
		try{
    		//init values
    		$Items = new WNamedList();
    	    $MetaItems = new WNamedList();
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
    		    if(isset($this->targetObject->{$key}) && strlen($this->targetObject->{$key}) > 0) 
    		    {
    		        if(substr($key,-4) == 'Date' || $key == 'LastAccess') 
    		        {
    		            $date = $this->targetObject->{$key};
    		            $val = $date > 0 ? date('Y-m-d H:i:s',$this->targetObject->{$key}) : '';
    		        }
    		        elseif(substr($key,-4) == 'Size')
    		        {
    		            $val = DFileSystem::formatSize($this->targetObject->{$key});
    		        }
    		        elseif($key == 'AccessIntervalAverage')
    		        {
    		            $currentUnit = 0;
    		            $units = array('seconds' => 60, 'minutes' => 60, 'hours' => 24, 'days' => 365, 'years' => 100);
    		            $val = floatval($this->targetObject->{$key});
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
    		            $val = htmlentities(wordwrap($this->targetObject->{$key},15,'<wbr />',true), ENT_QUOTES, CHARSET);
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