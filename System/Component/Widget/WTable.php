<?php
class WTable extends BWidget 
{
    const HEADING_NONE = 0;
    const HEADING_TOP = 1;
    const HEADING_BOTTOM = 2;
    const HEADING_RIGHT = 4;
    const HEADING_LEFT = 8;
        
    protected static $CurrentWidgetID = 0;
    private $id;
    //display 
    private $topIsHeader = false;
    private $bottomIsHeader = false;
    private $rightIsHeader = false;
    private $leftIsHeader = false;
    private $title = null;
    private $translateHeadings = true;
    
    //data
    private $data = array();
    private $cols = 0;
        
    //Alteration
    private $cssClasses = array('alt_a', 'alt_b');

    public function setHeadings($headings)
    {
        $this->topIsHeader = $headings & self::HEADING_TOP;
        $this->bottomIsHeader = $headings & self::HEADING_BOTTOM;
        $this->rightIsHeader = $headings & self::HEADING_RIGHT;
        $this->leftIsHeader = $headings & self::HEADING_LEFT;
    }
    
    public function setData(array $data)
    {
        if(!isset($data[0]) || !is_array($data[0]))    
        {
            throw new XInvalidDataException('argument must be an array of arrays with numeric indexes');    
        }
        $this->data = $data;
    }
    
    public function addRow(array $row)
    {
        $this->data[] = $row;
    }
    
    public function setHeaderTranslation($yn)
    {
        $this->translateHeadings = $yn == true;
    }
    
    public function setCellAlteration($primaryClass, $secondaryClass)
    {
        $this->cssClasses = array($primaryClass, $secondaryClass);
    }
    
    public function setTitle($title = null, $translate = true)
    {
        if($title == null)
        {
            $this->title = null;
        }
        else
        {
            $this->title = $translate ? SLocalization::get($title) : $title;
        }
    }
    
    /**
     * @param int $headings
     * @param string $title
     */
    public function __construct($headings = 0, $title = null)
    {
        $this->setHeadings($headings);
        $this->setTitle($title);
        $this->id = ++parent::$CurrentWidgetID;
    }
    
    public function __toString()
    {
        ob_start();
        $this->render();
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }
    
    
    /**
     * process inputs etc
     *
     */
    public function run(){} 
    
    /**
     * echo html 
     */
    public function render()
    {
        if($this->title != null)
        {
            printf("<h3>%s</h3>\n", $this->title);
        }
        printf("<table class=\"WTable\" id=\"WTable_%d\">\n", $this->id);
        $rows = count($this->data);
        $cols = $rows > 0 ? count($this->data[0]) : 0;
        $flip = false;
        $headline = false;
        $bottomline = false;
        if($this->topIsHeader && $rows > 0)
        {
            $headline = array_shift($this->data);
            $rows--;
        }
        if($this->bottomIsHeader && $rows > 0)
        {
            $bottomline = array_pop($this->data);
            $rows--;
        }
        $datacols = $cols - ((($this->leftIsHeader) ? 1:0)+(($this->rightIsHeader) ? 1:0));
        if($rows > 0 && $datacols >= 0)
        {
            $rowTPL = "\t<tr class=\"%s\">\n";
            $colNo = 0;
            if($this->leftIsHeader)
            {
                ++$colNo;
                $rowTPL .= "\t\t<th scope=\"row\" class=\"head_x{$colNo} head_y%d\">%s</th>\n"; 
            }
            for ($i = 0; $i < $datacols; $i++)
            {
                ++$colNo;
                $rowTPL .= "\t\t<td class=\"cell_x{$colNo} cell_y%d\">%s</td>\n";    
            }
            if($this->rightIsHeader)
            {
                ++$colNo;
                $rowTPL .= "\t\t<th scope=\"row\" class=\"head_x{$colNo} head_y%d\">%s</th>\n";
            }
            $rowTPL .= "\t</tr>\n";
            
            if($headline !== false)
            {
                echo "\t<tr class=\"head\">\n";
                $cellNo = 0;
                foreach ($headline as $cell) 
                {
                    printf("\t\t<th class=\"head_x%d head_top\">%s</th>\n", ++$cellNo, $this->translateHeadings ? (SLocalization::get($cell)) : $cell);
                }
                echo "\t</tr>\n";
            }
            foreach ($this->data as $line) 
            {
                $lineData = array(
                    $this->cssClasses[$flip]
                );
            	for($i = 0; $i < $cols; $i++)
            	{
            	    $cell = isset($line[$i]) ? $line[$i] : '';
                    $lineData[] = $i+1;
                    $lineData[] = ($this->translateHeadings && (($this->leftIsHeader && $i == 0) || ($this->rightIsHeader && $i == $cols-1)))
                        ? (SLocalization::get($cell))
                        : $cell;
            	}
                vprintf($rowTPL, $lineData);         
                $flip = !$flip;
            }
            if($bottomline !== false)
            {
                echo "\t<tr class=\"head\">\n";
                $cellNo = 0;
                foreach ($bottomline as $cell) 
                {
                    printf("\t\t<th class=\"head_x%d head_top\">%s</th>\n", ++$cellNo, $this->translateHeadings ? (SLocalization::get($cell)) : $cell);
                }
                echo "\t</tr>\n";
            }
        }
        else
        {
            echo '<tr><td>(null)</td></tr>';
        }
        print("</table>\n");
    }
    /**
     * return ID of primary editable element or null 
     *
     * @return string|null
     */
    public function getPrimaryInputID()
    {
        return null;
    }
}
?>