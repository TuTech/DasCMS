#!/usr/bin/php
<?php
function wl($line)
{
	fprintf(STDERR, $line."\n");
}
/*
applicationPreferences";a:2:{
	s:17:"WebsiteEditor.bap";a:1:{s:13:"WYSIWYGStatus";s:3:"off";}
	s:14:"PageEditor.bap";a:1:{s:18:"OI_current_palette";s:20:"OI_sect_informations";}
}
s:30:"applicationPreferenceKeyForces";a:2:{
	s:17:"WebsiteEditor.bap";a:1:{s:13:"WYSIWYGStatus";b:0;}
	s:14:"PageEditor.bap";a:1:{s:18:"OI_current_palette";b:0;}
}
s:27:"applicationPreferenceForces";a:0:{}
s:16:"preferenceForced";b:0;}s
*/
function datify($string)
{
	$string = mb_convert_encoding($string, "UTF-8");
	htmlentities($string, ENT_QUOTES, "UTF-8");
	return $string;
}
class bcmsuser{
	public $password,$realName,$email;
	public $groups = array();
	public $permissions = array();
	public $attributes = array();
	public $primaryGroup = '';
	
	public $applicationPreferences = array();
	public $applicationPreferenceKeyForces = array();
	public $applicationPreferenceForces = array();
	public $preferenceForced = false;
	
	private $systemGroups = array("Administrator", "CMS", "Create",  "Delete", "Update", "Rename", "Publish");
//	Edit=>Update&Publish
	
	public function xml($userid)
	{
		$pfx = "\t\t";
		$xml = sprintf($pfx."<user id=\"%s\">\n".
				$pfx."\t<name>%s</name>\n".
				$pfx."\t<email>%s</email>\n".
				$pfx."\t<password type=\"md5\">%s</password>\n".
				$pfx."\t<memberships>\n"
			,datify($userid)
			,datify($this->realName)
			,datify($this->email)
			,datify($this->password)
		);
		foreach ($this->groups as $name) 
		{
			$xml .= sprintf($pfx."\t\t<member of=\"%s\" primary=\"%s\" />\n"
				,datify(in_array($name, $this->systemGroups) ? "SYS_".$name : "USR_".$name)
				,datify(($name == $this->primaryGroup) ? "yes" : "no")
			);
		}
		foreach ($this->permissions as $app) 
		{
			$xml .= sprintf($pfx."\t\t<member of=\"APP_%s\" primary=\"no\" />\n"
				,datify($app)
			);
		}
		$xml .= $pfx."\t</memberships>\n".$pfx."\t<attributes>\n";
		foreach ($this->attributes as $key => $value) 
		{
			$xml .= sprintf($pfx."\t\t<attribute name=\"%s\">%s</attribute>\n"
				,datify($key)
				,datify($value)
			);
		}
		
		$xml .= $pfx."\t</attributes>\n".$pfx."</user>\n";
		return $xml;
	}
}
wl("\nBAMBUS CMS\n^^^^^//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//^^^^^");
wl("     || bambus 0.1x user data export ||");
wl("_____\\\\______________________________\\\\_____");

//param check
if($argc < 3  || $argc > 4 || !file_exists($argv[1]) || !is_dir($argv[1]))
{
	wl("usage:".$argv[0]." sourcedir targetformat [targetfile]".	
		"\n    targetformats are: xml".
		"\n    without targetfile output is dumped to stdout\n");
	exit();
}

//define out file
if($argc != 4 || !$fp = fopen($argv[3], "w"))
{
	$fp = STDOUT;
}

//check iput path
if(!file_exists($argv[1]."/users.php") || !file_exists($argv[1]."/groups.php"))
{
	wl("no data in given path: ".$argv[1]);
	if(file_exists($argv[1]."/Content/configuration/users.php") && file_exists($argv[1]."/Content/configuration/groups.php"))
	{
		wl("using: ".$argv[1]."/Content/configuration/");
		$argv[1] .="/Content/configuration/";
	}
	else
	{
		wl("no data in given path: ".$argv[1]."/Content/configuration/");
		exit();
	}
}

//check target format
if(strtolower($argv[2]) != 'xml')
{
	wl("unsupported output format: ".$argv[2]);
	exit();
	
}
//read data

fwrite($fp,"<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<bambus>\n");


$xml = sprintf("\t<export>\n\t\t<export_date>%s</export_date>\n\t\t<exporter>%s</exporter>\n\t</export>\n"
	,@date("r")
	,exec("whoami")
);
fwrite($fp,$xml);

fwrite($fp,"\t<accounts>\n");

//user data
$data = file($argv[1]."/users.php");
unset($data[0]);
$ser = implode("", $data);
$userdata = unserialize($ser);
$acc = 0;
foreach ($userdata as $userid => $udat) 
{
	fwrite($fp,$udat->xml($userid));
	$acc++;
}
//group data file
$data = file($argv[1]."/groups.php");
unset($data[0]);
$ser = implode("", $data);
$userdata = unserialize($ser);
$systemGroups = array("Administrator",  "CMS", "Create",  "Delete", "Edit", "Rename");
$grp = 0;
foreach ($userdata as $group => $desc) 
{
	$xml = sprintf("\t\t<group id=\"%s\">\n\t\t\t<description>%s</description>\n\t\t</group>\n"
		,datify(in_array($group, $systemGroups) ? "SYS_".$group : "USR_".$group)
		,datify($desc)
	);
	fwrite($fp,$xml);
	$grp++;
}


fwrite($fp,"\t</accounts>\n</bambus>");

if($fp != STDERR)
	fclose($fp);

//fwrite($fp, $ser);
//out data with targetformat matching dump function



wl("\n".$acc." accounts and ".$grp." groups exported\n");
?>