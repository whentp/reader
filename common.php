<?php

checkSafe();

require_once 'db.php';
require_once 'feeds.php';
require_once 'items.php';
require_once 'users.php';

require_once 'config.php';

function checkSafe(){
	if (!defined('IS_IN_READER')){
		die('Bye.');
	}
}

function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}

		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
}

function object_to_array($obj) {
	$arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
	foreach ($arrObj as $key => $val) {
		$val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
		$arr[$key] = $val;
	}
	return $arr;
}

function JSON($obj) {
	return json_encode($obj);
}


function merge_options($default, $to){
	$default = (object)$default;
	$to = (object)$to;
	foreach($default as $k=>$v){
		if(!isset($to->$k)){
			$to->$k = $v;
		}
	}
	return $to;
}

function addWhere($conditionstr){
	$conditionstr = trim($conditionstr);
	return ($conditionstr == '')?'':('WHERE '.$conditionstr);
}

function isPostBack(){
	return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}

function getUserId(){
	return getUserIdFromOpenId();
}

