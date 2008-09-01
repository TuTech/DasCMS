<?php
/**
 * Setup runner script
 */
/**
 * En-/disable this setup script
 * __VAR MUST BE IN LINE 10__ 
 * @var boolean
 */
$ENABLED = true;


$err = <<<ERR
	<div style="font-family:sans-serif;border:1px solid #a40000;">
		<div style="border:1px solid #cc0000;padding:10px;background:#a40000;color:white;">
			<h1 style="border-bottom:1px solid #cc0000;font-size:16px;">%s <code>%d</code> in "%s" at line %d</h1>
			<p>%s</p>
			<p><pre>%s</pre></p>
		</div>
	</div>
ERR;
define('ERROR_TEMPLATE', $err);

function EX_Handler(Exception $e)
{
	printf(ERROR_TEMPLATE
		, get_class($e)
		, $e->getCode()
		, $e->getFile()
		, $e->getLine()
		, $e->getMessage()
		, $e->getTraceAsString());
	exit(1);
}

function ER_Handler( $errno ,  $errstr ,  $errfile ,  $errline ,  $errcontext  )
{
	ob_start();
	print_r($errcontext);
	$context = ob_get_contents();
	ob_end_clean();
	printf(ERROR_TEMPLATE
		, 'Error'
		, $errno
		, $errfile
		, $errline
		, $errstr
		, $context);
		exit(1);
	
}
function __autoload($class)
{
	$Components = array(
//		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'H' => 'EventHandler',
		'I' => 'Interface',
        'L' => 'Legacy',
		'M' => 'Manager',
		'N' => 'Navigator',
		'Q' => 'Query',
		'S' => 'System',
		'W' => 'Widget',
		'X' => 'Exception'
	);
	
	$fc = substr($class,0,1); //first char
	$sc = substr($class,1,1); //second char
	if($sc == strtolower($sc))//valid class name begins with to uppercase chars 
	{						  //use the content class as default
		$fc = 'M';
		$class = 'M'.$class;
	}
	$file = sprintf("./System/Component/%s/%s.php", $Components[$fc], $class);
	if(array_key_exists($fc, $Components) && file_exists($file))
	{
		include_once($file);
	}
	elseif(array_key_exists($fc, $Components) && file_exists('.'.$file))
	{
		include_once('.'.$file);
	}
	else
	{
		return false;
	}
}
set_error_handler('ER_Handler');
set_exception_handler('EX_Handler');
chdir(dirname(__FILE__));
//FIXME in production: chdir('..');

//is disable possible
$self = file(__FILE__);
if(!preg_match('/^\$ENABLED\s*=\s*(true|false)\s*;$/s',trim($self[9])))
{
	throw new Exception('line 10 of '.basename(__FILE__).' MUST be "$ENABLED = false;" or "$ENABLED = true;"');
}

//to disable itself the script needs to write itself
if(!is_writable(__FILE__))
{
	throw new Exception('Cound not disable setup. Script aborted for security reasons. Allow script to modify itself.');
}

//allow this script only once
if(!$ENABLED)
{
	throw new Exception('Setup is not enabled!');
}

//disable self
$self[9] = "\$ENABLED = false;\n";

//FIXME debug: off
//$fps = @fopen(__FILE__, 'w+');
//if(!is_resource($fps) || !fwrite($fps,implode($self)))
//{
//	throw new Exception('Cound not write to setup-file. Script aborted for security reasons. You might need a new script.');
//}
//fclose($fps);


/**
 * init script classes must implement this
 */
interface runnable
{
	public function run();
}

class SetupConfiguration
{
	private static $data = null;
	
	public static function setup(array $cfg)
	{
		if(self::$data == null)
		{
			self::$data = $cfg;
		}
	}
	
	public static function get($var)
	{
		return isset(self::$data[$var])
			? self::$data[$var]
			: null;
	}
	
	public static  function set($var, $value)
	{
		self::$data[$var] = $value;
	}
	
	public static function data()
	{
		return serialize(self::$data);
	}
}

define('CMS_ROOTDIR', realpath('.'));

/*
 *read setup scripts
 */
$prefixes = array('pre-','','post-');
$stages = array(
	'setup', //pre-setup: basic env (folders etc) 
			 //pre-setup: config values  
			 //pre-setup: admin account
			 //setup folders / database / database-tables
	'init' , //files
	'update' //pre-update: class index
			 //update: content index
			 //post-update: alias index
);
$scripts = array();
foreach ($stages as $stage) 
{
	foreach ($prefixes as $prefix) 
	{
		$scripts[$prefix.$stage] = array();
	}
}


$hdl = opendir('.');
while($item = readdir($hdl))
{
	if(is_dir($item) || $item == basename(__FILE__))
	{
		//skip self and directories silently
		continue;
	}
	if(!is_readable($item))
	{
		printf("<p>Skipping '%s' - <span style=\"color:red\">not readable</span></p>\n", $item);
		continue;
	}
	$matches = array();
	if(!preg_match(//prefix, stage, '.', classname, '.php
			'/^(('.implode('|', $prefixes).')'. //prefix
			'('.implode('|', $stages).'))\.'. //stage
			'([a-zA-Z0-9_]+)'.//class 
			'\.php$/is'
		,$item
		,$matches))
	{
		printf("<p>Skipping '%s' - <span style=\"color:#f57900\">name mismatch</span></p>\n", $item);
		continue;
	}
	$scripts[$matches[1]][$matches[4]] = realpath($item);
}


echo '<hr />';

chdir('..');
foreach ($scripts as $stage => $scriptfiles) 
{
	foreach ($scriptfiles as $target => $scriptfile) 
	{
		$class = str_replace('-','_',$stage.'_'.$target);
		printf("<p><b>running %s \"%s</b>\"...<ul>", $stage, substr($class,strlen($stage)+1));
		ob_start();
		include_once($scriptfile);
		$dat = ob_get_contents();
		ob_end_clean();
		if(!empty($dat))
		{
			printf('</ul><span style="color:red">Malformed script "%s" - Producing output on include</span></p>', $scriptfile);
			continue;
		}
		if(!class_exists($class))
		{
			printf('</ul><span style="color:red">Malformed script "%s" - Does not contain class "%s"</span></p>', $scriptfile, $class);
			continue;
		}
		try
		{
			$object = new $class();
			if(!$object instanceof runnable)
			{
				printf('</ul><span style="color:red">Malformed class - The function run() MUST be available</span></p>', $class);
				continue;
			}
			$object->run();
			unset($object);
		}
		catch(Exception $ex)
		{
			printf('</ul><span style="color:#f57900">Problem in class - Exception thrown</span></p>', $class);
			printf(ERROR_TEMPLATE
				, get_class($ex)
				, $ex->getCode()
				, $ex->getFile()
				, $ex->getLine()
				, $ex->getMessage()
				, $ex->getTraceAsString());
			continue;
		}
		
		echo '</ul><span style="color:#4e9a06">done!</span></p>';
	}
}
echo '<h2>Setup finished</h2>';


?>