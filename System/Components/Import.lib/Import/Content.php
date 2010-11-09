<?php
class Import_Content
{
	protected static $resolvers = array();

	/**
	 * [alias => rel]
	 * @var array
	 */
	protected $previewImages = array();
	/**
	 * [alias => [rel]]
	 * @var array
	 */
	protected $attachments = array();
	protected $idToAlias = array();

	/**
	 * load data from url
	 * @param string $url
	 */
	public function fromURL($url){
		$this->fromFile($url);
	}

	/**
	 * load data from file
	 * @param string $file
	 */
	public function fromFile($file){
		//detect type
		$this->fromJSONString(file_get_contents($file));
	}

	/**
	 * load data from json string
	 * @param string $json
	 */
	public function fromJSONString($json){
		$data = new Import_Version1_JSON($json);
		if($data == null){
			throw new Exception('could not load data');
		}
		for($i = 0; $i < $data->getItemCount(); $i++){
			$this->import($data->getItem($i));
		}

		//assign references for the imported content
		$this->makeReferences();
	}

	/**
	 * import a single, validated content
	 * @param array $data
	 */
	protected function import(Import_Version1_Document $data){
		$content = $this->importerForType($data->getType())->createContentWithData($data);

		//store map importId => alias
		$this->registerImport($data->getImportId(), $content->getAlias());

		//store references importId => refs
		$this->prepareReferences($data->getReferences(), $content->getAlias());
	}

	/**
	 * @param string $type
	 * @return Import_Handler_ContentRequest
	 */
	protected function importerForType($type){
		if(!isset (self::$resolvers[$type])){
			$resolver = new Import_Request_Content($type);
			self::$resolvers[$type] = $resolver->getImporter();
		}
		return self::$resolvers[$type];
	}

	/**
	 * parse reference data an store them for post import processing
	 * @param array $references
	 * @param string $forAlias
	 */
	protected function prepareReferences(Import_Version1_References $references, $forAlias){
		$sections = $references->getReferenceSections();

		foreach ($references->getReferenceSections() as $section){
			//items in this section
			$itemCount = $references->getReferenceCountInSection($section);

			//link to preview image (1 allowed)
			if($section == 'previewImage' && $itemCount == 1){
				$this->previewImages[$forAlias] = $references->getReferenceInSection(0, $section);
			}

			//link to attachments 
			elseif($section == 'attachments' && $itemCount > 0){
				if(!isset($this->attachments[$forAlias])){
					$this->attachments[$forAlias] = array();
				}
				for($i = 0; $i < $itemCount; $i++){
					$this->attachments[$forAlias][] = $references->getReferenceInSection($i, $section);
				}
			}
		}
	}

	/**
	 * link all references for/to imported contents
	 */
	protected function makeReferences(){
		//link imported docs
		$c = Controller_Content::getInstance();

		//preview image
		foreach ($this->previewImages as $alias => $ref){
			$aliasRef = $this->resolveReference($ref);
			if($aliasRef != null){
				$content = $c->openContent($alias);
				if($content instanceof Interface_Content){
					$content->setPreviewImage($aliasRef);
					$content->save();
				}
			}
		}

		//attachments
		foreach ($this->attachments as $alias => $refs){
			$content = $c->openContent($alias);
			if($content instanceof Interface_Content && $content->hasComposite('AssignedRelations')){
				$attach = array();
				foreach ($refs as $ref){
					$attach[] = $this->resolveReference($ref);
				}
				$content->setAssignedRelationsData($attach);
				$content->save();
			}
		}
	}

	/**
	 * convert reference objects to aliases
	 * @param Import_Version1_Reference $ref
	 * @return string
	 */
	protected function resolveReference(Import_Version1_Reference $ref){
		switch ($ref->getReferenceType()){
			case Import_Version1_Reference::ALIAS:
				return $ref->getReferenceValue();
			case Import_Version1_Reference::IMPORT_ID:
				$v = $ref->getReferenceValue();
				if(isset($this->idToAlias[$v])){
					return $this->idToAlias[$v];
				}
				else return null;
			default:
				return null;

		}
	}

	/**
	 * register iportId => alias lookup for references
	 * @param string $importId
	 * @param string $alias
	 */
	protected function registerImport($importId, $alias){
		$this->idToAlias[$importId] = $alias;
	}
}
?>
