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

	private $data = array();
	
	public function __construct($name)
	{
    	$this->ID = crc32($name);
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
	    printf("
            <table class=\"WPropertyEditor\" id=\"WPropertyEditor_%s\">
            	<tr>
                    <th class=\"WPE_left\"></th>
                    <th>%s</th>
                    <th class=\"WPE_mid\"></th>
                    <th>%s</th>
            	</tr>
            	<tr>
            		<td class=\"WPE_left\">
            			<img src=\"%s\" alt=\"%s\" id=\"WPropertyEditor_%s_mv_up\" />
            			<img src=\"%s\" alt=\"%s\" id=\"WPropertyEditor_%s_mv_down\" />
            		</td>
            		<td>
						<select size=\"%d\" id=\"WPropertyEditor_%s_selector\">"
			,$this->ID
    		,SLocalization::get('property')
            ,SLocalization::get('settings')
            ,WIcon::pathFor('move-up', 'action', WIcon::SMALL)
            ,$this->ID
            ,WIcon::pathFor('move-down', 'action', WIcon::SMALL)
            ,$this->ID
            ,count($this->data)
            ,$this->ID
		);
		//select
		foreach ($this->data as $name => $data) 
		{
			printf("
							<option id=\"WPropertyEditor_%s_option_%s\" class=\"WPropertyEditor_%s\" value=\"%s\">%s</option>"
				,$this->ID
			    ,crc32($name)
			    ,($data[self::ACTIVE] === false) ? 'inactive' : 'active'
			    ,crc32($name)
			    ,htmlentities($data[self::TITLE], ENT_QUOTES)
			);
		}
		
		print("
						</select>
		    		</td>
					<td class=\"WPE_mid\"></td>
					<td>"
		);	    
		//props
		printf('<div id="WPropertyEditor_%s_options">', $this->ID);
        foreach ($this->data as $name => $data) 
        {
            $i = 1;
        	printf("
						<div id=\"WPropertyEditor_%s_option_%s_data\" class=\"WPropertyEditor_option\" style=\"display:none;\">
						<input type=\"hidden\" name=\"WPropertyEditor_%s_%s_position\" value=\"%s\" id=\"WPropertyEditor_%s_%s_position\" />"
				,$this->ID
			    ,crc32($name)
			    ,$this->ID
			    ,crc32($name)
			    ,$i++
				,$this->ID
			    ,crc32($name)
			);
			if($data[self::ACTIVE] !== null)
			{
			    printf("
							<p><input type=\"checkbox\" name=\"WPropertyEditor_%s_option_%s_active\" id=\"WPropertyEditor_%s_option_%s_active\" %s/>%s</p>"
					,$this->ID
			        ,crc32($name)
					,$this->ID
			        ,crc32($name)
			        ,$data[self::ACTIVE] ? 'checked="checked" ' : ''
					, SLocalization::get('active')
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
	    return intval(RSent::get(sprintf('WPropertyEditor_%s_%s_position', crc32($element), crc32($property)))); 
	}
	
	public static function getPropStatus($element, $property)
	{
	    return strtolower(RSent::get(sprintf('WPropertyEditor_%s_option_%s_active', crc32($element), crc32($property)))) == 'on'; 
	}	
}
?>