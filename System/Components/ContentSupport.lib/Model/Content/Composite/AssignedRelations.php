<?php
class Model_Content_Composite_AssignedRelations
    extends _Model_Content_Composite
    implements Interface_Composites_Attachable,
			   Interface_Composite_AutoAttach
{
    protected $assigned = array(), $formatter = null, $file;
	protected $dataChanged = false;
    public static function getCompositeMethods()
    {
        return array(
        	'getAssignedRelations',
        	'getAssignedRelationsData',
        	'setAssignedRelationsData',
            'getAssignedRelationsFormatter',
        	'setAssignedRelationsFormatter'
        );
    }

    public function __construct(BContent $compositeFor)
    {
        parent::__construct($compositeFor);
        $this->file = sprintf(
        	'%s/%s_%d',
        	SPath::CONFIGURATION,
        	get_class($this),
        	$compositeFor->getId()
        );
        try
        {
			if(file_exists($this->file)){
				$data = DFileSystem::LoadData($this->file);
				if(isset($data['assigned'])){
					$this->setAssignedRelationsData($data['assigned']);
				}
				if(isset($data['formatter'])){
					$this->setAssignedRelationsFormatter($data['formatter']);
				}
			}
        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
        }
    }

    public function contentSaves(){
    	if(!$this->dataChanged){
    		return ;
    	}
    	try{
	    	$data = array(
	    		'assigned'  => $this->getAssignedRelationsData(),
	    		'formatter' => $this->getAssignedRelationsFormatter()
	    	);
			DFileSystem::SaveData($this->file, $data);
    	}
    	catch(Exception $e){
			SNotificationCenter::report(SNotificationCenter::TYPE_WARNING, 'could_not_save_assigned_relations');
    	}
    }

    //formatted list
	public function getAssignedRelations(){
		$formatter = $this->formatter;
		if(empty ($formatter)){
			$formatter = LConfiguration::get('Settings_ContentView_relations');
		}

		if(empty ($formatter) || count($this->assigned) == 0){
			return '<!-- no relations -->';
		}

		//return widget?
		//return rendered list?
		$html = '<ul class="Content_AssignedRelations">';
		foreach ($this->assigned as $alias) {
			try{
				$c = Controller_Content::getSharedInstance()->openContent($alias);
				$html .= '<li>'.Formatter_Container::unfreezeForFormatting($formatter, $c).'</li>';
			}
			catch(XFileNotFoundException $fnf){
				return '';
			}
			catch (Exception $e){
				//ignore
			}
		}
		$html .= '</ul>';
		return $html;
	}

	//list of elements
    public function getAssignedRelationsData(){
    	return  $this->assigned;
    }

	public function setAssignedRelationsData($value){
		if(!is_array($value)){
			$value = STag::parseTagStr($value);
		}
		$this->assigned = array();
		foreach ($value as $k => $v){
			if(is_string($v) && Controller_Content::getSharedInstance()->contentExists($v)){
				$this->assigned[] = $v;
			}
		}
		$this->dataChanged = true;
	}

	//formatter name
	public function getAssignedRelationsFormatter(){
		return $this->formatter;
	}

	public function setAssignedRelationsFormatter($value){
		try{
			if(empty ($value)){
				$this->formatter = null;
			}
			elseif(Formatter_Container::exists($value)){
				$this->formatter = strval($value);
			}

		}
		catch(Exception $e){
			//formatter loading failed
			$this->formatter = null;
		}
		$this->dataChanged = true;
	}
}
?>