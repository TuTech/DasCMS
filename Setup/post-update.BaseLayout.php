<?php
class post_update_BaseLayout implements runnable 
{
	const HEADER = "<?php /* Bambus Data File */ header(\"HTTP/1.0 404 Not Found\"); exit(); ?>\n";
	
	private function write($file,$data)
	{
		if(!($fp = fopen($file, 'w+')) || !fwrite($fp, $data))
		{
			throw new Exception('cound not write to file '. $file);
		}
		fclose($fp);
	}
	
	public function run()
	{
		echo '<li>saving setup configuration...</li>';
		$this->write(
			'Content/configuration/system.php',
			self::HEADER.SetupConfiguration::data()
		);
	}
}
?>