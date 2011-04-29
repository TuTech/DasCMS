<?php
require_once 'System/main.php';
PAuthentication::required();

try{
	$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
	$root = new API_Controller_Root();
	$root->initWithPath($path);
	echo json_encode($root->handleMethod($_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING'], file_get_contents('php://input')));
}
catch (Exception_HTTP $eh){
	header($eh->getMessage(), true, $eh->getCode());
	$eh->getTraceAsString();
	die($eh->getMessage());
}
catch(Exception $e){
	header('Server Error', true, 500);
	$e->getTraceAsString();
	die('Server Error');
}
?>