<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UCFileUpload 
    extends BPlugin 
    implements ITemplateSupporter, IGlobalUniqueId 
{
    const GUID = 'org.bambuscms.plugins.fileupload';
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    private static $uploaded = false;
    private static $message = '';
    
    public function processUpload($param)
    {
        if(!is_array($param))
        {
            $param = array();
        }        
        $tags = '';
        $pub = false;
        if(array_key_exists('tags', $param))
        {
            $tags = $param['tags'];
        }
        if(array_key_exists('publish', $param))
        {
            $pub = !empty($param['publish']);
        }
        if(RFiles::has('CFile') && PAuthorisation::has('org.bambuscms.content.cfile.create'))
        { 
        	try
        	{
        	    $f = CFile::Create('');
        	    $f->setTags($tags);
        	    if($pub)
        	    {
        	        $f->setPubDate(time());
        	    }
        	    $f->Save();
        	    self::$uploaded = true;
        	    self::$message = 'uploaded';
        	    SNotificationCenter::report('message', 'file_uploaded');
        	}
        	catch(Exception $e)
        	{
        	    self::$message = 'upload_failed';
        	    SNotificationCenter::report('warning', 'upload_failed'.$e->getMessage());
        	}
        }
    }
    
    public function uploadMessage($param)
    {
        $html = '';
        if(self::$message != '')
        {
            $succ = self::$uploaded ? 'ok' : 'failed';
            $html = sprintf(
                '<div class="UCFileUpload_upload_%s">%s</div>'
				,$succ
                ,array_key_exists($succ.'Message', $param) 
                    ? $param[$succ.'Message'] 
                    : (SLocalization::get(self::$message))
            );
        }
        return $html;
    }
    
    public function uploadForm($param)
    {
        $html = '';
        if(PAuthorisation::has('org.bambuscms.content.cfile.create'))
        {
            if(!is_array($param))
            {
                $param = array();
            }
            $maxSize = (array_key_exists('maxSize', $param)) ? $param['maxSize'] : '1000000000';
            $html = '<div class="UCFileUpload_form">';
            $html .= sprintf('<p>%s</p>', (array_key_exists('text', $param)) ? $param['text'] : '');
            $html .= sprintf('<form action="%s" method="post"  enctype="multipart/form-data">', SLink::buildURL());
            $html .= sprintf('<input type="hidden" name="MAX_FILE_SIZE" value="%s" />', $maxSize);
            $html .= '<input type="file" name="CFile" />';
            $html .= sprintf('<input type="submit" value="%s" />', (array_key_exists('submitText', $param)) ? $param['submitText'] : 'OK');
            $html .= '</form></div>';
        }
        return $html;
    }
    
/////////////////////////////////
    private static $functions = array(
        'processUpload' => array('tags', 'publish'),
        'uploadMessage' => array('okMessage', 'failedMessage'),
        'uploadForm' => array('maxSize', 'text', 'submitText')
    );

    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function TemplateProvidedFunctions()
    {
        return self::$functions;
    }
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function TemplateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function TemplateCallable($function)
	{
	    return in_array($function, array_keys(self::$functions));
	}
	
	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function TemplateCall($function, array $namedParameters)
	{
	    if(!$this->TemplateCallable($function))
	    {
	        throw new XTemplateException('called undefined function');
	    }
        return $this->{$function}($namedParameters);
	}
	
	/**
	 * @param string $property
	 * @return string in utf-8
	 */
	public function TemplateGet($property)
	{
	    return '';
	}
}
?>