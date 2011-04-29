<?php
class API_Controller_AppInfo extends _API_Controller_Object implements API_Interface_AcceptsGet
{
	protected $app;

	public function __construct(Application_Interface_AppController $app) {
		$this->app = $app;
		if(!PAuthorisation::has($app->getClassGUID()))
		{
			throw new Exception_HTTP(403);
		}
	}

	public function getControllerName() {
		return $this->app->getClassGUID();
	}

	public function resolveSubPath(array $path){
		$controller = $this;
		if(count($path) > 0){
			$alias = array_shift($path);
			$content = Controller_Content::getInstance()->openContent($alias);
			$controller = new API_Controller_ContentInfo($content);
			$controller = $controller->resolveSubPath($path);
		}
		return $controller;
	}

	public function httpGet($queryString) {
		return array(
			'guid' => $this->app->getClassGUID(),
			'title' => $this->app->getTitle(),
			'icon' => $this->app->getIcon(),
			'contentObjects' => $this->app->getContentObjects(),
			'description' => $this->app->getDescription(),
			'editor' => $this->app->getEditor(),
		);
	}
}
?>