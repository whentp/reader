<?php
require_once 'db.php';

session_start();

function userExists($md5){
	global $db;

	$sql = 'SELECT id, openidmd5 FROM users WHERE openidmd5 = :md5';
	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute(array(':md5' => $md5));
	$rs = $conn->fetchAll();

	foreach($rs as $a){
		return $a['id'];
	}
	return false;
}

function userAdd($md5){
	global $db;
	$user_id = userExists($md5);
	if ($user_id === false){
		$sql = 'INSERT INTO users(openidmd5) VALUES(:md5)';
		$conn = $db->prepare($sql);
		$conn->execute(array(':md5' => $md5));
		$user_id = $db->lastInsertId();
	}
	return $user_id;
}

function getUserIdFromOpenId(){
	if(isset($_SESSION['usermd5']) && $_SESSION['usermd5']){
		$md5 = $_SESSION['usermd5'];
		return userAdd($md5);
	}
	return false;
}

function setUserMd5($md5){
	$_SESSION['usermd5'] = $md5;
}

function logoutOpenId(){
	$_SESSION['usermd5'] = '';
	unset($_SESSION['usermd5']);
}

function getUserIdByName($username){
	return getIdIfExists('SELECT id FROM users WHERE name=:username', array(':username'=>$username), 'id');
}

function getConfigById($userid){
	$filename = 'data/userconfig/'.$userid.'.json';
	return file_exists($filename)? file_get_contents($filename):'{}';
}

function setConfigById($userid, $key, $value){
	$filename = 'data/userconfig/'.$userid.'.json';
	$obj = json_decode(file_exists($filename)? file_get_contents($filename):'{}');
	$obj->$key = $value;
	file_put_contents($filename, json_encode($obj));
}

function getUserNameById($id){
	global $db;

	$sql = 'SELECT id, name FROM users WHERE id=:id';
	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute(array(':id' => $id));
	$rs = $conn->fetchAll();

	foreach($rs as $a){
		return $a['name'];
	}
	return false;
}

function getCurrentUserUrl(){
	$tmp = getCurrentUrl();
	return substr($tmp, 0, strrpos($tmp, "/"));
}

