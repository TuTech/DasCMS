<?php
class post_setup_SComponentIndex implements runnable 
{
	public function run()
	{
		if(!is_dir('Content/SComponentIndex/'))
		{
			mkdir('Content/SComponentIndex/');
		}
		$cdir = getcwd();
		$SCI = SComponentIndex::alloc()->init();
        $SCI->Index(false);
		chdir($cdir);
	}
}
?>