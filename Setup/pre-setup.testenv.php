<?php
class pre_setup_testenv implements runnable 
{
	const HEADER = "<?php exit(); ?>\n";
	
	private $dirs = array(
			'Content/CPage',
			'Content/document',
			"Content/CPage",
			'Content/NTreeNavigation',
	);
	private $files = array(
			'Content/document/index.php' => array(),
			'Content/document/meta.php' => array(),
			'Content/document/feedIndex.php' => array(),
			'Content/NTreeNavigation/index.php' => array()
		);
			
	
	private function write($file,$data)
	{
		if(!($fp = fopen($file, 'w+')) || !fwrite($fp, $data))
		{
			throw new Exception('cound not write to file '. $file);
		}
		fclose($fp);
	}
	public function cleanUp()
	{
		echo '<h4>Cleaning up older data</h4><ol style="color:#f57900">';
		foreach ($this->files as $file => $data) 
		{
			if(file_exists($file) && @unlink($file))printf("<li>deleting %s</li>\n", $file);
		}

		foreach ($this->dirs as $dir) 
		{
			if(is_dir($dir) && @rmdir($dir))printf("<li>removing %s/</li>\n", $dir);
		}
		echo '</ol>';
	}	
	public function run()
	{
		$this->cleanUp();
		echo '<h4>Setting up new data</h4>';
		if(!is_dir('Content/'))
		{
			@mkdir('Content/');
		}
		//default dirs
		foreach ($this->dirs as $dir) 
		{
			if(!is_dir($dir))
			{
				@mkdir($dir);
			}
			if(!is_dir($dir))
			{
				throw new Exception('Could not create folder "'.$dir.'"');
			}	
			if(!is_writable($dir))
			{
				throw new Exception($dir.' is not writeable');
			}	
		}
		///////////
		//Config
		///////////

		foreach ($this->files as $file => $data) 
		{
			printf("<li>creating %s</li>\n", $file);
			$this->write(
				$file,
				self::HEADER.serialize($data)
			);
		}
		

	}
}
?>