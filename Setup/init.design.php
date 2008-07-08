<?php
class init_design implements runnable 
{
	private function write($file,$data)
	{
		if(!($fp = fopen($file, 'w+')) || !fwrite($fp, $data))
		{
			throw new Exception('cound not write to file '. $file);
		}
		fclose($fp);
	}
	
	private function reportSuccess($target, $type, $successful)
	{
		printf(
			"<li>Creating %s \"%s\"...%s</li>",
			$type,
			$target,
			($successful) ? '<span style="color:#4e9a06">OK</span>' : '<span style="color:red">FAILED</span>'
		);
	}
	
	public function run()
	{
		//write css
		$css =
			'body {font-family:sans-serif;}'."\n".
			'h1   {color:#4e9a06;border-bottom:1px solid #4e9a06;}'."\n".
			'h2   {color:#2e3436;}'."\n";
		$this->write('Content/stylesheets/default.css', $css);
		$this->reportSuccess('default','stylesheet',1);
		//write tpl
		$tpl = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
    <head>
        <base href=\"{Linker:myBase}\" />
        <meta http-equiv=Content-Type content=\"text/html; charset=UTF-8\" />
        <link rel=\"stylesheet\" href=\"./Content/stylesheets/default.css\" type=\"text/css\" media=\"all\" />
        <title>
             {Title}
        </title>
        <meta name=\"description\" content=\"{meta_description}\" />
        <meta name=\"generator\" content=\"Bambus CMS {cms:version}\" />
        <meta name=\"keywords\" content=\"{meta_keywords}\" />
    </head>
    <body>
        <h1>
    	   	{sitename}
        </h1>
	    <div id=\"main\">
            {ListNavigation:page}
       		<h2>
               	{Title}
           	</h2>
          	<div id=\"content\">
	        	{Content}
          	</div>
		</div>
    </body>
</html>";
		$this->write('Content/templates/page.tpl', $tpl);
		$this->reportSuccess('page','template',1);
	}
}
?>