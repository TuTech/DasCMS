<?php
class Formatter_Container
    extends _Formatter
    implements
        Interface_View_DisplayXHTML,
        Interface_View_DisplayJSON,
        Interface_View_DisplayAtom,
		Interface_AcceptsContent
{
	const CLASS_NAME = 'Formatter_Container';
    protected static $availableAttributes = null;
    protected $attachedAttributes = array();
    protected $uniqueName = null;
    protected $persistentAttributes = array('uniqueName', 'attachedAttributes');
    /**
     * @var Interface_Content
     */
    protected $targetContent = null;

    protected static $Formatters = array();

    public function __construct($uniqueName)
    {
        $this->uniqueName = $uniqueName;
    }

    public function setTargetContent(Interface_Content $content)
    {
        $this->targetContent = $content;
    }

	public function acceptContent(Interface_Content $content){
		$this->setTargetContent($content);
	}

    public function resetAttributes()
    {
        $this->attachedAttributes = array();
    }

    public function attachAttribute(_Formatter_Attribute $attribute)
    {
        $this->attachedAttributes[] = $attribute;
    }

    public function getAttachedAttributes()
    {
        return $this->attachedAttributes;
    }

    public function getAvailableAttributes()
    {
        if(!is_array(self::$availableAttributes))
        {
            self::$availableAttributes = array();
            //EGatherFormaterAttributes
            //IFormaterAttributeProvider: -getRestoreHash()/+restoreFromHash()
        }
        return self::$availableAttributes;
    }

    public function toJSON()
    {
        $data = array(
            'name' => $this->uniqueName,
            'attributes' => array()
        );
        foreach ($this->attachedAttributes as $attribute)
        {
        	$data['attributes'][] = $attribute->toJSON();
        }
        print_r($data);
        return json_encode($data);
    }

    public function toXHTML()
    {
        //IF has content to format format content - else show config
        $str = '';
        if($this->isVisible())
        {
            $str = "<div class=\"".htmlentities($this->uniqueName, ENT_QUOTES, CHARSET)."\">\n";
            foreach ($this->attachedAttributes as $attribute)
            {
                $attribute->setParentContainer($this);
                $str .= strval($attribute);
            }
            $str.= "</div>\n";
        }
        return $str;
    }

    public function getAtomTag()
    {
        return 'entry';
    }

    /**
     * @return XML_Atom_Entry
     * (non-PHPdoc)
     * @see System/Object/Interface/View/Interface_View_DisplayAtom#toAtom()
     */
    public function toAtom()
    {
        $entry = XML_Atom_Entry::createWriteableInstance();
        foreach ($this->attachedAttributes as $attribute)
        {
            if($attribute instanceof Interface_View_DisplayAtom)
            {
                $entry->addElement($attribute->getAtomTag(), $attribute->toAtom());
            }
            $attribute->setParentContainer($this);
        }
        return $entry;
    }

    public function __toString()
    {
        return $this->toXHTML();
    }

    public function freeze($name = null, $object = null)
    {
		Formatter_Container::freezeFormatter($this->uniqueName, $this);
    }

	public static function getFormatterList(){
		return Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('list')
			->withoutParameters()
			->fetchList();
	}

	public static function freezeFormatter($name, $object){
		$data = 'base64:'.base64_encode(serialize($object));
		return Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('set')
			->withParameters($data, $name, $data)
			->execute();
	}

	public static function deleteFormatter($name){
		return Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('del')
			->withParameters($name)
			->execute();
	}

	public static function exists($name){
		return !!Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('exists')
			->withParameters($name)
			->fetchSingleValue();
	}

    /**
     * @param string $data
     * @return Formatter_Container
     */
    public static function unfreeze($name)
    {
        //reverse evil
		$res = Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('load')
			->withParameters($name);
		if($res->getRows() != 1){
			throw new XFileNotFoundException('no formatter named '.$name);
		}
        $data = $res->fetchSingleValue();
		if(substr($data,0,7) == 'base64:'){
			$data = base64_decode(substr($data,7));
		}
        $container = unserialize($data);
        return $container;
    }

    /**
     * @param string $data
     * @param Interface_Content $content
     * @return Formatter_Container
     */
    public static function unfreezeForFormatting($name, Interface_Content $content)
    {
		$obj = self::unfreeze($name);
		if($obj instanceof Interface_AcceptsContent){
			$obj->acceptContent($content);
		}
        return $obj;
    }
}
?>