<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Handles all AJAX requests and redirects them to the Application AJAX-handler
* Version      0.9.0
************************************************/
header('Content-Type: text/html; charset=utf-8');
setlocale (LC_ALL, 'de_DE');
//load the mighty bambus
chdir('..');
require_once('./System/Component/Loader.php');

RSession::start();
PAuthentication::required();
try
{
    if(!PAuthorisation::has('org.bambuscms.login'))
    {
        throw new XPermissionDeniedException("No bambus for you, hungry Panda!");
    }
    if(RURL::hasValue('controller'))
    {
        $appCtrlID = RURL::get('controller');
        $function = RURL::get('call');
        $Interface = null;
        if(substr($function,0,7) == 'receive')
        {
            $Interface = 'IACReceiver'.substr($function,7);
        }
        elseif(substr($function,0,7) == 'provide')
        {
            $Interface = 'IACProvider'.substr($function,7);
        }
        else
        {
            $Interface = 'IACCapability'.ucfirst($function);
        }
        $controller = BAppController::getControllerForID($appCtrlID);
        if(in_array($Interface, class_implements($controller)))
        {
            $paramStr = file_get_contents('php://input');
            $parameters = null;
            if(!empty($paramStr))
            {
                $parameters = @json_decode($paramStr);
            }
            if(!is_array($parameters))
            {
                $parameters = array();
            }
            echo json_encode(call_user_func_array(array($controller, $function), array($parameters)));
        }
    }
    elseif(RURL::hasValue('_OpenFiles'))
    {
        $manager = RURL::get('_OpenFiles');
        if(!PAuthorisation::has('org.bambuscms.content.'.$manager.'.view'))
        {
            throw new XPermissionDeniedException("No bambus for you, hungry Panda!");
        }
        if(substr($manager,0,1) != 'M' || !class_exists($manager, true))
        {
            throw new XFileNotFoundException("No bambus for you, hungry Panda!");
        }
        $man = BObject::InvokeObjectByDynClass($manager);
        $SCI = SContentIndex::alloc()->init();
        $IDindex = $SCI->getIndex($man);
        $CMDIDS = array();
        foreach ($IDindex as $key => $ttl) 
        {
        	$CMDIDS[] = $manager.':'.$key;
        }
        $index = $SCI->getContentInformationBulk($CMDIDS);
        $items = array();
        foreach($index as $item)
        {
            $items[] = array($item['Title'], $item['MCID'], 0, $item['PubDate']);//
        }
        $OFD = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/Icons/tango/large/mimetypes/C'.substr($manager,1).'.png'),
            'smallIconMap' => array('System/Icons/tango/extra-small/mimetypes/C'.substr($manager,1).'.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate', 'icon' => 'type'),
            'items' => $items,
            'captions' => array(
                'detail' => SLocalization::get('detail'),
                'icon' => SLocalization::get('icon'),
                'list' => SLocalization::get('list'),
                'asc' => SLocalization::get('asc'),
                'desc' => SLocalization::get('desc'),
                'searchByTitle' => SLocalization::get('search_by_title'),
                'pubDate' => SLocalization::get('pubDate'),
                'notPublished' => SLocalization::get('not_published'),
                'title' => SLocalization::get('title'),
                'type' => SLocalization::get('type'),
            )
        );
        echo json_encode($OFD);
    }
    else
    {
        throw new XArgumentException('no arguments');
    }
}
catch(Exception $e)
{
    $err = array(
        'exception' => get_class($e),
        'message' 	=> $e->getMessage(),
        'code' 		=> $e->getCode(),
        'trace' 	=> $e->getTraceAsString(),
        'file' 		=> $e->getFile(),
        'line' 		=> $e->getLine(),
        '_GET' 		=> $_GET
    );
    echo json_encode($err);
}
?>