#!/usr/bin/php
<?php
if($argc < 1 || !empty($_GET))
{
	die('Please run script from terminal\n\n');
}
class SUser 
{
    public $password = '',$realName = '',$email = '';
    public $groups = array();
    public $permissions = array();
    public $attributes = array();
    public $primaryGroup = '';
    
    public $applicationPreferences = array();
    public $applicationPreferenceKeyForces = array();
    public $applicationPreferenceForces = array();
    public $preferenceForced = false;
}
$out = false;

$target = '../Content/configuration/users.php';

if($argc == 3)
{
    $format = '"%s","%s","%s","%s"'."\n";
    $file = $argv[2];
    if(!empty($argv[1]))
    {
        $oldsPWs = array();
        switch($argv[1])
        {
            case '-l'://load additional data
            case '-s'://set data
                if(file_exists($target))
                {
                    $data = file($target);
                    unset($data[0]);
                    $stream = implode('', $data);
                    $ulist = unserialize($stream);
                    foreach($ulist as $uid => $obj)
                    {
                        $oldsPWs[$uid] = $obj->password;
                    }
                }
                if($argv[1] == '-s')
                {
                    $ulist = array(); 
                }
                
                $fp = fopen($file, 'r');
                fgetcsv($fp, 4096);//skip header
                printf("skipped row 1 - assumed header\n");
                $rowNr = 2;
                while($row = fgetcsv($fp, 4096))
                {
                    if(count($row) >= 4)
                    {
                        printf("reading row %d\n", $rowNr);
                        list($user, $password, $name, $email) = $row;
                        printf("\t   uid: %s\n\tpasswd: %s\n\t  name: %s\n\t email: %s\n", $user, $password, $name, $email);
                        if(!array_key_exists($user, $ulist))
                        {
                            printf(" + creating user\n");
                            $ulist[$user] = new SUser();
                        }
                        if($password != '???')
                        {
                            printf(" + setting password\n");
                            $ulist[$user]->password = md5($password);
                        }
                        elseif(isset($oldsPWs[$user]))
                        {
                            printf(" - using previous password\n");
                            $ulist[$user]->password = $oldsPWs[$user];
                        }
                        if($name != '' && $name != $ulist[$user]->realName)
                        {
                            printf(" + setting name\n");
                            $ulist[$user]->realName = $name;
                        }
                        if($email != '' && $email != $ulist[$user]->email)
                        {
                            printf(" + setting email\n");
                            $ulist[$user]->email = $email;
                        }
                    if(isset($row[4]))// groups
                        {
                            printf(" + setting groups\n");
                            $ulist[$user]->groups = array();
                            if(!empty($row[4]))
                            {
                                $groups = explode(';',$row[4]);
                                foreach($groups as $g)
                                {
                                    $ulist[$user]->groups[] = trim($g);
                                    printf("   - %s\n", $g);
                                }
                            }
                            else
                            {
                                printf("   -- None --\n", $g);
                            }
                        }
                        if(isset($row[5]))// permissions
                        {
                            printf(" + setting permissions\n");
                            $ulist[$user]->permissions = array();
                            if(!empty($row[5]))
                            {
                                $perms = explode(';',$row[5]);
                                foreach($perms as $g)
                                {
                                    $ulist[$user]->permissions[] = trim($g);
                                    printf("   - %s\n", $g);
                                }
                            }
                            else
                            {
                                printf("   -- None --\n", $g);
                            }
                        }
                        printf("\n");
                    }
                    else
                    {
                        printf("malformed row %d\n", $rowNr);
                    }
                    $rowNr++;
                }
                $out = true;
                $header = "<?php exit(); ?>\n";
                $data = $header.serialize($ulist);
                $fp = fopen($target,'w+');
                fwrite($fp,$data);
                fclose($fp);
                break;
            case '-d': 
                $fp = fopen($file, 'w+');
                fputcsv($fp, array('user', 'password', 'name', 'email', 'groups', 'editors'));
                $data = file($target);
                unset($data[0]);
                $stream = implode('', $data);
                $ulist = unserialize($stream);
                foreach($ulist as $uid => $obj)
                {
                    echo "\t", $uid,"\n";
                    fputcsv($fp, array($uid, '???', 
                        addslashes($obj->realName), 
                        addslashes($obj->email), 
                        implode(';', $obj->groups),
                        implode(';', $obj->permissions)
                    ));
                } 
                fclose($fp);
                $out = true;
                break;
            case '-x':
                if(file_exists($target))
                {
                    $data = file($target);
                    unset($data[0]);
                    $stream = implode('', $data);
                    $ulist = unserialize($stream);
                }
                for($i = 2; $i < $argc; $i++)
                {
                    if(isset($ulist[$argv[$i]]))
                    {
                        printf(" + removed user %s\n", $argv[$i]);
                        unset($ulist[$argv[$i]]);
                    }
                }
                $out = true;
                $header = "<?php exit(); ?>\n";
                $data = $header.serialize($ulist);
                $fp = fopen($target,'w+');
                fwrite($fp,$data);
                fclose($fp);
                break;
        }
    }
}
if(!$out)
{
    echo "usage: \n\t";
    echo $argv[0]." <option> file\n";
    echo "options:";
    echo "\n\t-d to dump the current dataset to file";
    echo "\n\t-l to add/change logins from file";
    echo "\n\t-s to replace all logins with data from file";
    echo "\n\n";
    exit;
}
?>