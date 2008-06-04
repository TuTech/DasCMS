#!/usr/bin/php
<?php
if($argc < 1 || !empty($_GET))
{
	die('Please run script from terminal\n\n');
}
printf('Bambus rights repair\nfor: %s\n\n', getcwd());

$owner = 'lse';
$group = 'lse';

$cmsRights = array(

	'.' => array(
		'dirrights' => 0777,
		'filerights' => 0644,
		'owner' => $owner,
		'group' => $group,
		'recursive' => false
	),
	
	'Content' => array(
		'dirrights' => 0777,
		'filerights' => 0666,
		'owner' => $owner,
		'group' => $group,
		'recursive' => true
	),
	
	'System' => array(
		'dirrights' => 0777,
		'filerights' => 0644,
		'owner' => $owner,
		'group' => $group,
		'recursive' => true
	),

	'Management'  => array(
		'dirrights' => 0777,
		'filerights' => 0644,
		'owner' => $owner,
		'group' => $group,
		'recursive' => true
	),
	
	'Misc'  => array(
		'dirrights' => 0777,
		'filerights' => 0644,
		'owner' => $owner,
		'group' => $group,
		'recursive' => true
	),
	
	'Setup'  => array(
		'dirrights' => 0777,
		'filerights' => 0644,
		'owner' => $owner,
		'group' => $group,
		'recursive' => true
	)
);








$filecount = 0;
$dircount = 0;
$root = getcwd();

function repair($dir = './', $dirrights = 0777, $filerights = 0644, $user = 'www', $group = 'www', $recursive = true){
	global $root, $filecount, $dircount;
    $dirs = array();
    printf("\n%s\n",$dir);
    chdir($root);
    if(is_dir($dir)){
        if(chdir($dir))
        {
            $Directory = opendir ('./');
            while ($item = readdir ($Directory)) 
            {
            	
            	$rights = null;
                if(is_file($item))
                {
                    $filecount++;
                    $type =  'FILE';
                    $rights = $filerights;
                }
                elseif(is_dir($item) && $item != '.' && $item != '..')
                {
                    $dircount++;
                     $type = 'DIR ';
                    if($recursive)
                    {
                    	$dirs[] = $item;
                    }
                    $rights = $dirrights;
                }
                else
                {
                	continue;
                }
                print('[ ');
                if($user != null)
                {
                	$flag = (@chown($item, $user)) ? ' ' : '!';
                	printf("u:%s%s ", $user, $flag ? '':' (FAILED)');
                }
                if($group != null)
                {
                	$flag = (@chown($item, $group)) ? ' ' : '!';
                	printf("g:%s%s ", $group, $flag ? '':' (FAILED)');
                }
                if($rights != null)
                {
                	$flag = (@chmod($item, $rights)) ? ' ' : '!';
                	printf("r:%03o%s ", $rights, $flag ? '':' (FAILED)');
                }          
                printf("] %s %s\n", $type, $item);  
            }
            closedir($Directory);
        }
        chdir($root);
        foreach($dirs as $direc)
        {
            repair($dir.$direc.'/', $dirrights, $filerights, $user, $group);
        }
    }
    else
    {
    	print("! no dir\n\n");
    }
}
if(is_dir('Content') && is_dir('Management') && is_dir('System'))
{
	foreach ($cmsRights as $dir => $settings) 
	{
		chdir($root);
		repair(
			'./'.$dir.'/', 
			$settings['dirrights'], 
			$settings['filerights'], 
			$settings['owner'], 
			$settings['group'],
			$settings['recursive']
		);
	}
}
printf(
	"\ndone\n%8d files\n%8d directories\n"
	,$filecount
	,$dircount
);

?>