<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-20
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_PropertyEditor extends _View_UIElement
{
    const TITLE = 0;
    const DATA = 1;
    const ACTIVE = 2;
    
	private $id;
	private $autotranslate = true; 
	private $data = array();
	
	public function __construct($name, $title = null)
	{
    	$this->id = ($name);
    	$this->setTitle($title);
	}
	
    public function setTitleTranslation($yn)
    {
        $this->autotranslate = $yn == true;
    }
	
    public function setTitle($title = null)
    {
        if($title == null)
        {
            $this->title = null;
        }
        else
        {
            $this->title = $this->autotranslate ? SLocalization::get($title) : $title;
        }
    }
	
    /**
	 * return rendered html
	 * @return string
	 */
	public function __toString()
	{
	    ob_start();
	    $this->render();
	    return ob_get_clean();
	}
	
	/**
	 * echo html 
	 */
	public function render()
	{
        if($this->title != null)
        {
            printf("<h3>%s</h3>\n", $this->title);
        }
	    printf("
            <table class=\"View_UIElement_PropertyEditor\" id=\"View_UIElement_PropertyEditor_%s\">
            	<tr>
                    <th class=\"WPE_left\"></th>
                    <th class=\"WPE_property\">%s</th>
                    <th>%s</th>
            	</tr>
            	<tr>
            		<td class=\"WPE_left\">
            			<img src=\"%s\" alt=\"%s\" id=\"View_UIElement_PropertyEditor_%s_mv_up\" class=\"WPE_mv_up\" />
            		</td>
            		<td rowspan=\"2\" class=\"WPE_property\">
						<select size=\"%d\" id=\"View_UIElement_PropertyEditor_%s_selector\">"
			,$this->id
    		,SLocalization::get('property')
            ,SLocalization::get('settings')
            ,View_UIElement_Icon::pathFor('move-up', 'action', View_UIElement_Icon::SMALL)
            ,''
            ,$this->id
            ,count($this->data)
            ,$this->id
		);
		//select
		foreach ($this->data as $name => $data) 
		{
			printf("
							<option id=\"View_UIElement_PropertyEditor_%s_option_%s\" class=\"WPE_%s\" value=\"%s\">%s</option>"
				,$this->id
			    ,($name)
			    ,($data[self::ACTIVE] === false) ? 'inactive' : 'active'
			    ,($name)
			    ,String::htmlEncode($this->autotranslate ? (SLocalization::get($data[self::TITLE])) : mb_convert_encoding($data[self::TITLE], CHARSET, 'ISO-8859-1,UTF-8'))
			);
		}
		print("
						</select>
		    		</td>
					<td rowspan=\"2\">"
		);	    
		//props
		printf('<div id="View_UIElement_PropertyEditor_%s_options">', $this->id);
		$i = 1;
        foreach ($this->data as $name => $data) 
        {
        	printf("
						<div id=\"View_UIElement_PropertyEditor_%s_option_%s_data\" class=\"View_UIElement_PropertyEditor_option\" style=\"display:none;\">
							<input type=\"hidden\" name=\"View_UIElement_PropertyEditor_%s_%s_position\" value=\"%d\" id=\"View_UIElement_PropertyEditor_%s_%s_position\" />
							<h4>%s</h4>"
				,$this->id
			    ,($name)
			    ,$this->id
			    ,($name)
			    ,$i++
				,$this->id
			    ,($name)
			    ,String::htmlEncode($this->autotranslate ? (SLocalization::get($data[self::TITLE])) : mb_convert_encoding($data[self::TITLE], CHARSET, 'ISO-8859-1,UTF-8'))
			);
			if($data[self::ACTIVE] !== null)
			{
			    printf("
							<div class=\"View_UIElement_PropertyEditor_seg\"><input type=\"checkbox\" name=\"View_UIElement_PropertyEditor_%s_option_%s_active\" id=\"View_UIElement_PropertyEditor_%s_option_%s_active\" %s/><label for=\"View_UIElement_PropertyEditor_%s_option_%s_active\">%s</label></div>"
					,$this->id
			        ,($name)
					,$this->id
			        ,($name)
			        ,$data[self::ACTIVE] ? 'checked="checked" ' : ''
					,$this->id
			        ,($name)
					, SLocalization::get('activate_item')
		        );
			}
			print(strval($data[self::DATA]));
			print("</div>");
        }
        print('</div>');
		printf("
					</td>
				</tr>
				<tr class=\"highgravity\">
					<td class=\"WPE_left\">
            			<img src=\"%s\" alt=\"%s\" id=\"View_UIElement_PropertyEditor_%s_mv_down\" class=\"WPE_mv_down\" />
					</td>
				</tr>
			</table>"
            ,View_UIElement_Icon::pathFor('move-down', 'action', View_UIElement_Icon::SMALL)
            ,''
            ,$this->id
		);
		echo strval(new View_UIElement_Script('org.bambuscms.wpropertyeditor.init("'.$this->id.'");'));
	}
	
	/**
	 * add proerty
	 *
	 * @param string $name
	 * @param string $title
	 * @param string|_View_UIElement $data
	 * @param bool|null $activated
	 */
	public function add($name, $title, $data, $activated)
	{
        $this->data[$name] = array($title, $data, $activated);
	}
	
	public static function getPropPos($element, $property)
	{
	    return intval(RSent::get(sprintf('View_UIElement_PropertyEditor_%s_%s_position', ($element), ($property)))); 
	}
	
	public static function getPropStatus($element, $property)
	{
	    //want: View_UIElement_PropertyEditor_footerConfig_option_page_no_active
	    //is:   View_UIElement_PropertyEditor_footerConfig_option_previous_link_active
	    $elm = sprintf('View_UIElement_PropertyEditor_%s_option_%s_active', ($element), ($property));
	    $val = RSent::get($elm);
	    return strtolower($val) == 'on'; 
	}	
}
?>