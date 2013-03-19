<?php 
define('IS_IN_READER', true);
require_once 'common.php'; 
require_once 'rss.clawer.php';

echo "Import is stopped.\n\n";

$items = feedList();
$count = count($items);
foreach($items as $index=>$a){
	$index++;
	echo "$index/$count\t".$a['link']."\n";
	fetchFeedItems($a['link'], $a['id']);
}

