<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 20.10.2008
 * @license GNU General Public License 3
 */
class WPropertyEditor extends BWidget
{
    const TITLE = 0;
    const DATA = 1;
    const ACTIVE = 2;
    
	private $ID;
	private $autotranslate = true; 
	private $data = array();
	
	public function __construct($name, $title = null)
	{
    	$this->ID = ($name);
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
            <table class=\"WPropertyEditor\" id=\"WPropertyEditor_%s\">
            	<tr>
                    <th class=\"WPE_left\"></th>
                    <th class=\"WPE_property\">%s</th>
                    <th>%s</th>
            	</tr>
            	<tr>
            		<td class=\"WPE_left\">
            			<img src=\"%s\" alt=\"%s\" id=\"WPropertyEditor_%s_mv_up\" class=\"WPE_mv_up\" />
            			<img src=\"%s\" alt=\"%s\" id=\"WPropertyEditor_%s_mv_down\" class=\"WPE_mv_down\" />
            		</td>
            		<td class=\"WPE_property\">
						<select size=\"%d\" id=\"WPropertyEditor_%s_selector\">"
			,$this->ID
    		,SLocalization::get('property')
            ,SLocalization::get('settings')
            ,WIcon::pathFor('move-up', 'action', WIcon::SMALL)
            ,''
            ,$this->ID
            ,WIcon::pathFor('move-down', 'action', WIcon::SMALL)
            ,''
            ,$this->ID
            ,count($this->data)
            ,$this->ID
		);
		//select
		foreach ($this->data as $name => $data) 
		{
			printf("
							<option id=\"WPropertyEditor_%s_option_%s\" class=\"WPE_%s\" value=\"%s\">%s</option>"
				,$this->ID
			    ,($name)
			    ,($data[self::ACTIVE] === false) ? 'inactive' : 'active'
			    ,($name)
			    ,htmlentities($this->autotranslate ? (SLocalization::get($data[self::TITLE])) : mb_convert_encoding($data[self::TITLE], 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
			);
		}
		print("
						</select>
		    		</td>
					<td>"
		);	    
		//props
		printf('<div id="WPropertyEditor_%s_options">', $this->ID);
        foreach ($this->data as $name => $data) 
        {
            $i = 1;
        	printf("
						<div id=\"WPropertyEditor_%s_option_%s_data\" class=\"WPropertyEditor_option\" style=\"display:none;\">
						<input type=\"hidden\" name=\"WPropertyEditor_%s_%s_position\" value=\"%s\" id=\"WPropertyEditor_%s_%s_position\" />
						<h4>%s</h4>"
				,$this->ID
			    ,($name)
			    ,$this->ID
			    ,($name)
			    ,$i++
				,$this->ID
			    ,($name)
			    ,htmlentities($this->autotranslate ? (SLocalization::get($data[self::TITLE])) : mb_convert_encoding($data[self::TITLE], 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
			);
			if($data[self::ACTIVE] !== null)
			{
			    printf("
							<p><input type=\"checkbox\" name=\"WPropertyEditor_%s_option_%s_active\" id=\"WPropertyEditor_%s_option_%s_active\" %s/><label for=\"WPropertyEditor_%s_option_%s_active\">%s</label></p>"
					,$this->ID
			        ,($name)
					,$this->ID
			        ,($name)
			        ,$data[self::ACTIVE] ? 'checked="checked" ' : ''
					,$this->ID
			        ,($name)
					, SLocalization::get('activate_item')
		        );
			}
			print(strval($data[self::DATA]));
			print("</div>");
        }
        print('</div>');
		print("
					</td>
				</tr>
			</table>"
		);
		echo strval(new WScript('org.bambuscms.wpropertyeditor.init("'.$this->ID.'");'));
	}
	
	/**
	 * add proerty
	 *
	 * @param string $name
	 * @param string $title
	 * @param string|BWidget $data
	 * @param bool|null $activated
	 */
	public function add($name, $title, $data, $activated)
	{
        $this->data[$name] = array($title, $data, $activated);
	}
	
	public static function getPropPos($element, $property)
	{
	    return intval(RSent::get(sprintf('WPropertyEditor_%s_%s_position', ($element), ($property)))); 
	}
	
	public static function getPropStatus($element, $property)
	{
	    return strtolower(RSent::get(sprintf('WPropertyEditor_%s_option_%s_active', ($element), ($property)))) == 'on'; 
	}	
}
?>