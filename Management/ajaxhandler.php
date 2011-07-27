<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Handles all AJAX requests and redirects them to the Application AJAX-handler
* Version      0.9.0
************************************************/
chdir(dirname(__FILE__));
require_once '../System/main.php';
header('Content-Type: text/html; charset='.CHARSET);
//load the mighty bambus
if(!defined('ERROR_TEMPLATE'))
{
    define('ERROR_TEMPLATE', 
'{
	"error":"%s", 
	"errnr":"%d",
	"file":"%s",
	"line":"%d",
	"desc":"%s",
	"trace":"%s",
	"cwd":"%s"
}');
}
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
        $ctrlID = RURL::get('controller');
        $function = RURL::get('call');
        $controller = BObject::InvokeObjectByID($ctrlID);
        if(!$controller instanceof _Controller_Application && !$controller instanceof IAjaxAPI)
        {
            throw new XPermissionDeniedException('controller is not an ajax handler');
        }
        if($controller instanceof _Controller_Application && RURL::has('edit'))
        {
            $controller->setTarget(RURL::get('edit', CHARSET));
        }
        if(method_exists($controller, $function))
        {
            $paramStr = implode(file('php://input'));
            $parameters = null;
            if(!empty($paramStr))
            {
                $parameters = @json_decode($paramStr, true);
            }
            if(!is_array($parameters))
            {
                $parameters = array('strData' => $paramStr);
            }
            echo json_encode(call_user_func_array(array($controller, $function), array($parameters)));
        }
    }
    else
    {
        throw new XArgumentException('no arguments');
    }
}
catch(Exception $e)
{
    try
    {
        $user = PAuthentication::getUserID();
    }
    catch (Exception $e)
    {
        $user = '(null)';
    }
    $err = array(
        'error' 	=> 1,
        'exception' => get_class($e),
        'message' 	=> $e->getMessage(),
        'code' 		=> $e->getCode(),
        'trace' 	=> $e->getTraceAsString(),
        'file' 		=> $e->getFile(),
        'line' 		=> $e->getLine(),
        'user'		=> $user
    );
    echo json_encode($err);
}
?>
