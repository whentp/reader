<?php

$db = new PDO('sqlite:rssfeeddata.db');
// Set errormode to exceptions
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getIdIfExists($sql, $bindData = array(), $idName = 'id'){
	global $db;

	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute($bindData);
	$rs = $conn->fetchAll();

	foreach($rs as $a){
		return (int)$a[$idName];
	}
	return false;
}

function getIdByInsert($tableName, $values){
	global $db;
	$names = array();
	$values = array();
	$binddata = array();

	foreach($values as $k=>$v){
		$names[] = $k;
		$values[] = ':'.$k;
		$binddata[':'.$k] = $v;
	}
	$strNames = join(', ', $names);
	$strValues = join(', ', $values);
	$sql = "INSERT INTO $tableName($strNames) VALUES($strValues)";
	$conn = $db->prepare($sql);
	$conn->execute($binddata);
	return (int) $db->lastInsertId();
}
