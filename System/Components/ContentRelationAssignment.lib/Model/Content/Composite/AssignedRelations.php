<?php
class Model_Content_Composite_AssignedRelations
    extends _Model_Content_Composite
    implements Interface_Composites_Attachable,
			   Interface_Composites_AutoAttach
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
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
        try
        {
			$this->setAssignedRelationsFormatter($this->getFormatterFromDB());

			//read assignments
			$AssignCtrl = Controller_ContentRelationManager::getInstance();
			$assigned = $AssignCtrl->getAllRetainedByContentAndClass($compositeFor->getAlias(), $this);
			$this->setAssignedRelationsData($assigned);

        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
        }
    }

	protected function getFormatterFromDB(){
		$formatterName = Core::Database()
			->createQueryForClass($this)
			->call('contentFormatter')
			->withParameters($this->compositeFor->getId(), get_class($this))
			->fetchSingleValue();
		return empty($formatterName) ? null : $formatterName;
	}

    public function contentSaves(){
    	if(!$this->dataChanged){
    		return ;
    	}
    	try{
			//FIXME: bad access: missing formatter controller
			$f = $this->getAssignedRelationsFormatter();
			DSQL::getInstance()->beginTransaction();
			Core::Database()
				->createQueryForClass($this)
				->call('unlink')
				->withParameters($this->compositeFor->getId(), get_class($this))
				->execute();
			if($f != null){
				Core::Database()
					->createQueryForClass($this)
					->call('link')
					->withParameters($this->compositeFor->getId(), get_class($this), $f)
					->execute();
			}
			DSQL::getInstance()->commit();
			
			$AssignCtrl = Controller_ContentRelationManager::getInstance();
			$assigned = $this->getAssignedRelationsData();
			$compositeAlias = $this->compositeFor->getAlias();

			//save assignments
			DSQL::getInstance()->beginTransaction();
			$AssignCtrl->releaseAllRetainedByContentAndClass($this->compositeFor->getAlias(), $this);
			foreach ($assigned as $alias){
				$AssignCtrl->retain($alias, $compositeAlias, $this);
			}
			DSQL::getInstance()->commit();
    	}
    	catch(Exception $e){
			SNotificationCenter::report(SNotificationCenter::TYPE_WARNING, 'could_not_save_assigned_relations');
    	}
    }

    //formatted list
	public function getAssignedRelations(){
		$formatter = $this->formatter;
		if(empty ($formatter)){
			$formatter = Core::settings()->get('Settings_ContentRelationsView_relations');
		}

		if(empty ($formatter) || count($this->assigned) == 0){
			return '<!-- no relations -->';
		}

		//return widget?
		//return rendered list?
		$html = '<ul class="Content_AssignedRelations">';
		foreach ($this->assigned as $alias) {
			try{
				$c = Controller_Content::getInstance()->openContent($alias);
				$html .= '<li>'.Controller_View::getInstance()->display($c, $formatter).'</li>';
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
			$value = Controller_Tags::parseString($value);
		}
		$this->assigned = array();
		foreach ($value as $k => $v){
			if(is_string($v) && Controller_Content::getInstance()->contentExists($v)){
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
			elseif(Controller_View::getInstance()->hasView($value)){
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