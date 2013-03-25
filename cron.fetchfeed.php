<?php 
define('IS_IN_READER', true);
require_once 'common.php'; 
require_once 'rss.clawer.php';


$items = feedList();
$count = count($items);
foreach($items as $index=>$a){
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

