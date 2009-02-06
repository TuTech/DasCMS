<?php
chdir('..');
function exception_handler($exception) {
  echo "<div style=\"position:absolute; bottom:10px;left:10px; z-index:1000; background:#cc0000;padding:10px; border: 1px solid #a40000; color:white;\">",
	  "<h3 style=\"margin-top:2px;margin-bottom:2px;font-family:sans-serif;\">Uncaught exception: &lt;",get_class($exception),"&gt;</h3><strong>Message: \"" 
	  , $exception->getMessage(), "\"</strong><br />\n",
	  '<pre style="border:1px solid #a40000; padding:5px; background:#f68181; color:black;">', $exception->getTraceAsString(),'</pre></div>';
}
set_exception_handler('exception_handler');
require_once('./System/Component/Loader.php');
RSession::start();
PAuthentication::required();
//FIXME check for right
$SCI = SComponentIndex::alloc()->init();
$SCI->Index();
?>

<h1>Finished</h1>

