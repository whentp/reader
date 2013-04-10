<?php
/* so nice to implement this useful script. @whentp */

if(file_exists('lock.txt')){
	die('remove lock.txt first then run again.');
}

require 'db.php';
error_reporting(E_ALL);
$sqltxt = file_get_contents('database.sql');
$sqls = explode(';', $sqltxt);

function executeSqlWithoutError($sql){
	try{
		executeSql($sql);
	} catch (Exception $e) {
		return;
	}
	echo "$sql -> ok\n";
}

foreach($sqls as $sql){
	if (trim($sql)=='') continue;
	$sql = trim($sql).";\n";
	executeSqlWithoutError($sql);
	
	$sql = preg_replace('/\s*--.*/i', '', $sql);
	preg_match_all('/create table\s+`?(\w+)`?/i', $sql, $out);
	if (count($out[0])){
		$tablename = $out[1][0];
		preg_match_all('/\s*`?(\w+)`?\s+([^,]+)/i', $sql, $outcols);
		for($i = 0; $i < count($outcols[0])-1; $i++){
			if(strtolower($outcols[1][$i]) != 'create' && strtolower($outcols[1][$i]) != 'foreign'){
				$newsql = "ALTER TABLE `$tablename` ADD COLUMN ".$outcols[1][$i]." ".$outcols[2][$i].";\n";
				executeSqlWithoutError($newsql);
			}
		}
	}
}

echo "DB updated.\n";
file_put_contents('lock.txt','locked');
