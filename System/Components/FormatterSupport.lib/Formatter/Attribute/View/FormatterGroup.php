<?php
class Formatter_Attribute_View_FormatterGroup
    extends Formatter_Attribute_Info
    implements Interface_Formatter_Attribute_TextAppendable,
               Interface_Formatter_Attribute_TextPrependable
{
    protected $persistentAttributes = array('textAfter','textBefore', 'formatters', 'className');
    protected $formatters = array();
    protected $className = null;

    public function addChildFormatter(_Formatter_Attribute $formatter)
    {
        $this->formatters[] = $formatter;
    }

    public function getChildFormatters()
    {
        return $this->formatters;
    }

    public function setChildFormatters(array $formatters)
    {
        $this->formatters = array();
        foreach ($formatters as $f)
        {
            $this->addChildFormatter($f);
        }
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName($text)
    {
        $this->className = strval($text);
    }

    /**
     * @param array $formatters
     * @param string $preText
     * @param string $postText
     * @return Formatter_Attribute_View_FormatterGroup
     */
    public static function makeGroup(array $formatters, $preText = null, $postText = null)
    {
        $f = new Formatter_Attribute_View_FormatterGroup();
        if($preText !== null)
        {
            $f->setPrependedText($preText);
        }
        if($postText !== null)
        {
            $f->setAppendedText($postText);
        }
        $f->setChildFormatters($formatters);
        return $f;
    }

    protected function getFormatterClass()
    {
        return 'FormatterGroup'.($this->className !== null ? ' '.$this->className : '');
    }

    public function toXHTML($insertString = null)
    {
        $str = '';
        $class = ($this->className === null) ? '' : sprintf(' class="%s"', $this->className);

        foreach ($this->formatters as $f)
        {
            $f->setParentContainer($this->parentContainer);
            $str .= strval($f);//->toXHTML();
        }
        return parent::toXHTML($str);
    }
}
?>