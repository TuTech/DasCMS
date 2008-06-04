#!/usr/bin/php
<?php
$_buffer = "";
$_usrmatch = null;
$_grpmatch = null;
$verbose = false;
$file = null;
for($i = 1; $i < $argc; $i++)
{
	$cmp = strtolower($argv[$i]);
	if(substr($cmp, 0, 2) == "u=")
	{
		$str = str_replace(",", ";", substr($argv[$i],2));
		$_usrmatch = explode(";", $str);
	}
	elseif(substr($cmp, 0, 2) == "g=")
	{
		$str = str_replace(",", ";", substr($argv[$i],2));
		$_grpmatch = explode(";", $str);
	}
	elseif($cmp == "-v")
	{
		$verbose = true;
	}
	else
	{
		$file = $argv[$i];
	}
}
function check($u = null, $g)
{
	global $_usrmatch,$_grpmatch;
	fprintf(STDERR, "****user = $u ****\n");
	if($_usrmatch == null && $_grpmatch == null)
	{
		
		return true;
	}
	elseif($u == null && is_string($g))
	{
		//export grp?
		if($_grpmatch == null || in_array($g, $_grpmatch))
			return true;
	}
	else
	{
		//export user?
		if(
			$_usrmatch == null || in_array($u, $_usrmatch)
		)
		{
			//user ok;
			if($_grpmatch == null)
				return true;
			$ok = false;
			foreach($g as $grp)
			{
				$ok = in_array($grp, $_grpmatch) | $ok;
			}
			return $ok;
		}
	}
	return false;
}


function wl($line, $data = "")
{
	global $_buffer;
	$_buffer .= sprintf("%-20s%s\n", $line, $data);
//	fprintf(STDERR, "%-20s%s\n", $line, $data);
}
function wl_commit($ok = true)
{
	global $_buffer;
	if($ok)
		fprintf(STDERR, $_buffer);
	else
		fprintf(STDERR, "    IGNORED!\n\n");
	$_buffer = "";
}

/**
 * convert bambus user exchange xml to native bambus cms 0.2x data format
 */

if ($file == null)
{
	wl("no input file");
	wl("reading from stdin");
	wl_commit();
	$xmlstring = implode(file("php://stdin"));
}
elseif(file_exists($file) && is_readable($file) && is_file($file))
{
	wl("reading file: ".$file);
	wl_commit();
	$xmlstring = implode(file($file));
}
else
{
	wl("failed to read file ".$file);
	wl_commit();
	exit();
}
wl("\nBAMBUS CMS\n^^^^^//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//^^^^^");
wl("     || bambus 0.2x user data import ||");
wl("_____\\\\______________________________\\\\_____");
wl("processing input");
wl_commit();
try
{	
	$dom = new DOMDocument("1.0", "UTF-8");
	if(!@$dom->loadXML($xmlstring))
	{
		throw new Exception("input is not xml");
	}
//	$dom = new DomDocument($xmlstring);
	$root = $dom->documentElement;
	if($root->tagName != "bambus")
	{
		throw new Exception("input is not bambus user xml");
	}
	$node = $root->firstChild;//node bambus
	do{
		if($node->nodeName == "accounts")
		{
//			wl("node ".$node->nodeName."->".$node->nodeValue);
			$account = $node->firstChild;
			do{//loop account data: users and groups
				if($account->nodeName == "user" )
				{	
					$uidnode = $account->attributes->getNamedItem("id")->value;//login name
					if($verbose)wl("account>user:",$uidnode);
					$username = $uidnode;
					$groupdata = array();
					foreach ($account->childNodes as $uinfo) 
					{
						if($uinfo->nodeType ==  XML_ELEMENT_NODE)
						{
							if($uinfo->childNodes->length == 1 
								&& $uinfo->firstChild->nodeType == XML_TEXT_NODE
								&& !ctype_space($uinfo->firstChild->nodeValue))
							{
								//user data
								if($verbose)wl("    ".$uinfo->nodeName.":",$uinfo->nodeValue);
							}
							elseif($uinfo->childNodes->length > 0 
								&& $uinfo->nodeType != XML_TEXT_NODE)
							{
								//user data in sub nodes
								if($uinfo->childNodes->length > 0)
									if($verbose)wl("    ".$uinfo->nodeName.">");
								foreach($uinfo->childNodes as $meta) 
								{
									if($meta->nodeType != XML_TEXT_NODE)
										if($verbose)wl("", "".$meta->nodeName.":".$meta->nodeValue);
									if($meta->attributes == null)
										continue;
									$i = 0;
									while($att = $meta->attributes->item($i))
									{
										if($att->nodeName == "of")
										{
											$groupdata[] = $att->nodeValue;
										}
										if($verbose)wl("", "    ".$att->nodeName."=".$att->nodeValue);
										$i++;
									}
								}
								
							}
						}
					}
					wl("    EXPORTED!\n");
					wl_commit(check($username, $groupdata));
					
//					wl($account->nodeValue);
//					wl(" +--> ".$uidnode);
				}
			}while($account = $account->nextSibling);
		}
		
	}while($node = $node->nextSibling);
//	if(!$dom->validate())
//	{//add dtd
//		wl("invalid xml");
//		exit();
//	}

}
catch(Exception $e)
{
	wl("***ERROR: ".$e->getMessage());
	exit();
}
class CFSGroup{
	/**
	 * Group name
	 *
	 * @var string
	 */
	private $Name = "";
	/**
	 * Group description
	 *
	 * @var string
	 */
	private $Description = "";
	
	/**
	 * plain array of other groups  
	 * wildcard * allowed at end of names e.g. APP_* 
	 * inherits all permissions
	 * TODO check recursion on implement 
	 *
	 * @var array
	 */
	private $Inherits = array();
}

class CFSUser{
	private $UserId = "";
	private $UserName = "";
	private $Email = "";
	private $PasswordHash = "";
	private $HashAlgorithm = "md5";
	private $Memberships = array();
	private $Attributes = array();
	private $Preferences; // user prefs are saved here// cfg has system and user prefs
	//check_pw_function($plainpw) converts plain with pwalg to hash and compares with pwhash
}

class MFSAuth{
	
}
?>