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
