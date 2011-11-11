<?php
class Authenticate_LDAP
	implements IAuthenticate
{
	const NAME = "authenticate_via_ldap";
	
    private $user;
    private $status;
    private $attemptedUserID = '';
	
	protected function ldapEscape($string){
		$escaped = "";
		for($i = 0; $i < strlen($string); $i++){
			$escaped .= '\\'.bin2hex($string{$i});
		}
		return $escaped;
	}
	
	protected function getLoginData(){
        $user = '';
        $password = '';
        $newLogin = false;
        if(!RURL::has('_destroy_session') && !RURL::has('_bambus_logout'))
        {
            //there might be something useful in here
            if(!RSession::has('bambus_cms_username') && !empty($_SERVER['PHP_AUTH_USER']))
            {
                $user = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];
            }            
            //this has priority
            if(RSent::hasValue('bambus_cms_username'))
            {
                $user = RSent::get('bambus_cms_username');
                $password = RSent::get('bambus_cms_password');
            }
            //save user name and password
            if(!empty($user))
            {
                $newLogin = true;
                RSession::reset();
                RSession::set('bambus_cms_username', $user);
                RSession::set('bambus_cms_password', $password);
            }
            //get username and password for later use
            if(RSession::hasValue('bambus_cms_username'))
            {
                $user = RSession::get('bambus_cms_username');
                $password = RSession::get('bambus_cms_password');
            }
        }
		return array($user, $password, $newLogin);
	}
	
	/**
	* try authentication 
	* @return void
	*/
	public function authenticate(){
		//system.ldap.(user|password|server|domain)
		$cfg = Core::Settings();
		$con = ldap_connect($cfg->get('system.ldap.server'));
		$bind = ldap_bind($con, $cfg->get('system.ldap.user'), $cfg->get('system.ldap.password'));
		$ldap_user = false;
		list($user, $password, $needsValidation) = $this->getLoginData();
		$isAuthenticated = !$needsValidation;
		
		if($needsValidation){
			$result = ldap_search($con, 
				$cfg->get('system.ldap.domain'), 
				"(&(objectClass=person)(sAMAccountName=". $this->ldapEscape($user) ."))", 
				array("sAMAccountName", "displayName", "dn", "memberOf")
			);
	    	$arr = ldap_get_entries($con, $result);
			if(isset($arr['count']) 
				&& $arr['count'] == '1' 
				&& isset($arr[0]["samaccountname"][0]) 
				&& $arr[0]["samaccountname"][0] == $user 
				&& $arr[0]['dn'])
			{
				//user found
				$ldap_user = $arr[0]['dn'];
			}
			ldap_close($con);
			
			if($ldap_user){
				$con = ldap_connect($cfg->get('system.ldap.server'));
				$bind = ldap_bind($con, $ldap_user, $password);
				ldap_close($con);
				$isAuthenticated = !!$bind;
			}
		}
		$this->attemptedUserID = $user;
		if($isAuthenticated && !empty($user)){
			$this->user = $user;
            $this->status = $needsValidation ? (PAuthentication::VALID_USER) : (PAuthentication::CONTINUED_SESSION);
		}
		else{
			$this->status = (empty($user)) ? (PAuthentication::NO_LOGIN) : (PAuthentication::FAILED_LOGIN) ;
		}
	}

	/**
	* returned value is PAuthentication::FAILED_LOGIN or PAuthentication::NO_LOGIN or PAuthentication::VALID_USER or PAuthentication::CONTINUED_SESSION;
	* 
	* @return int
	*/
	public function getAuthenticationState(){
		return $this->status;
	}

	/**
	* user login name
	* @return string
	*/
	public function getUserID(){
		return $this->user;
	}

	/**
	* user login name
	* @return string
	*/
	public function getAttemptedUserID(){
		return $this->attemptedUserID;
	}

	/**
	* users real name
	*
	* @return string
	*/
	public function getUserName(){
		return $this->user;
	}

	/**
	* users email address
	* 
	* @return string
	*/
	public function getUserEmail(){
		return '';
	}

	/**
	* successful authenticated
	*
	* @return boolean
	*/
	public function isAuthenticated(){
		return ($this->status >= PAuthentication::VALID_USER);
	}
}

?>