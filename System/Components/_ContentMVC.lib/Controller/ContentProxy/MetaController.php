<?php
class ContentProxy_MetaController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
}
?>