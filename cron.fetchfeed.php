<?php 
define('IS_IN_READER', true);
require_once 'common.php'; 
require_once 'rss.clawer.php';

$limit = getIdIfExists('SELECT COUNT(id) AS id FROM feeds WHERE timestamp<=:timestamp OR timestamp IS NULL', 
	array(':timestamp'=>time() - MINFETCHINTERVAL), 'id');
if(isset($_GET) && isset($_GET['limit'])){
	$limit = (int)$_GET['limit'];
}

$count = $limit;
$index = 0;
for($i = 0; $i < $limit; $i++){
	$items = feedList(1);
	if(!count($items)){
		break;
	}
	foreach($items as $a){
		$index++;
		$failedtime = $a['failedtime'];
		echo "$index/$count\t".$a['link']."\n";
		$possibility = rand(0, $failedtime + 1) >= $failedtime;
		if($possibility){
			fetchFeedItems($a['link'], $a['id']);
		}else{
			echo "Sorry. This link appears dead. Maybe I'll try next time.\n\n";
		}
	}
}
