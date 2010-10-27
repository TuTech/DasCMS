<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Settings extends _View_UIElement implements ISidebarWidget
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
	    return 'content_properties';
	}

	public function getIcon()
	{
	    return new View_UIElement_Icon('configure','',View_UIElement_Icon::SMALL,'action');
	}

	public function processInputs()
	{
	}

	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
		if(RSent::has('WSearch-Tags'))
		{
			$tagstr = RSent::get('WSearch-Tags', CHARSET);
			$chk = RSent::get('WSearch-Tags-chk', CHARSET);
			if($chk != md5($tagstr))
			{
				$this->targetObject->Tags = $tagstr;
			}
		}
		foreach(array('PubDate', 'RevokeDate') as $date){
			if(RSent::has('WSearch-'.$date))
			{
				$dat = RSent::get('WSearch-'.$date);
				$chk = $this->targetObject->{$date};
				if($chk != $dat)
				{
					$this->targetObject->{$date} = $dat;
				}
			}
		}
		$desc = RSent::get('WSearch-Desc', CHARSET);
		if(RSent::has('WSearch-Desc') && $desc != $this->targetObject->Description)
		{
			$this->targetObject->Description = $desc;
		}
		if(RSent::hasValue('WSearch-PreviewImage-Alias'))
		{
		    $prevAlias = RSent::get('WSearch-PreviewImage-Alias', CHARSET);
		    $this->targetObject->PreviewImage = $prevAlias;
		}
	}

	public function __toString()
	{
		$html = '<div id="WSearch">';
		try{
			//init values
			$Items = new View_UIElement_NamedList();
			$Items->setTitleTranslation(false);
		    $tags = $this->targetObject->Tags;
			$tagstr = (is_array($tags)) ? implode(', ', $tags) : '';
			$prev = $this->targetObject->PreviewImage;
			$alias = $prev->getAlias();
			$pubDate = $this->targetObject->PubDate;
			$revokeDate = $this->targetObject->RevokeDate;

			//preview
			if($alias !== null)
			{
			    $html .= sprintf('<input type="hidden" name="WSearch-PreviewImage-Alias" id="WSearch-PreviewImage-Alias" value="%s" />', String::htmlEncode($alias));
			}
			//tags changed?
			$html .= sprintf('<input type="hidden" class="hidden" name="WSearch-Tags-chk" value="%s" />', md5($tagstr));
			$html .= '<input type="hidden" class="hidden" name="WSearch-sent" value="1" />';


			$Items->add(
			    sprintf("<label>%s</label>", SLocalization::get('preview_image')),
			    sprintf('<div id="WSearch-PreviewImage"%s>%s</div>'
			        , ($alias === null) ? ' class="WSearch-PreviewImage-readonly"' : ''
			        ,$prev->scaled(128,96,View_UIElement_Image::MODE_SCALE_TO_MAX)->asUncachedImage()
			    )
			);
			$Items->add(
			    sprintf("<label for=\"WSearch-PubDate\">%s</label>", SLocalization::get('pubDate')),
			    sprintf(
			    	'<input type="text" id="WSearch-PubDate" name="WSearch-PubDate" value="%s" />'
			        , (is_numeric($pubDate) && !empty($pubDate))? date('Y-m-d H:i:s', $pubDate) : ''
		        )
			);
			$Items->add(
			    sprintf("<label for=\"WSearch-RevokeDate\">%s</label>", SLocalization::get('revokeDate')),
			    sprintf(
			    	'<input type="text" id="WSearch-RevokeDate" name="WSearch-RevokeDate" value="%s" />'
			        , (is_numeric($revokeDate) && !empty($revokeDate))? date('Y-m-d H:i:s', $revokeDate) : ''
		        )
			);
			$Items->add(
			    sprintf("<label for=\"WSearch-Tags\">%s</label>", SLocalization::get('tags')),
			    sprintf('<textarea id="WSearch-Tags" name="WSearch-Tags">%s</textarea>', String::htmlEncode($tagstr))
		    );

			$Items->add(
			    sprintf("<label for=\"WSearch-Desc\">%s</label>", SLocalization::get('description')),
			    sprintf('<textarea id="WSearch-Desc" name="WSearch-Desc">%s</textarea>', String::htmlEncode($this->targetObject->Description))
		    );
		    $si_on = true;
		    $si_changeable = false;
		    if($this->targetObject instanceof ISearchDirectives)
		    {
		        $si_on = $this->targetObject->allowSearchIndex();
		        $si_changeable = $this->targetObject->isSearchIndexingEditable();
		    }
			$html .= $Items;
			$html .= '</div>';
		}
		catch (Exception $e)
		{
		    return strval($e);
		}
		return $html;
	}

	public function associatedJSObject()
	{
	    return null;
	}
}
?>