<?php
define('IS_IN_READER', true);
require_once 'common.php';

$objGET = (object)$_GET;
$objPOST = (object)$_POST;

$controller = $objGET->c;
$action = $objGET->a;

function header_json(){
	if(!headers_sent()){
		header('Content-Type: application/json; charset=utf-8', true,200);
	}
}

require_once 'controller.'.$controller.'.php';

$tmp = new $controller();

function toActionName($str){
	$x = explode('-', $str);
	$tmp = array();
	foreach($x as $v){
		$tmp[] = ucwords($v);
	}
	return join('', $tmp);
}

if(isPostBack()){
	$method = 'set'.toActionName($action);
}else{
	$method = 'get'.toActionName($action);

}

if (!method_exists($tmp, $method)){
	throw new Exception("Lack of method $method.");
}

$tmp->$method();

