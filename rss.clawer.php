<?php

require_once 'common.php';
require_once 'autoloader.php';
require_once 'feed.mgr.php';

function feedList($conditions=''){
	global $db;

	$sql = 'SELECT id,title,link FROM feeds';
	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute();
	$rs = $conn->fetchAll();
	//var_dump($rs);
	return $rs;
}

function updateFeedItem($item){
/*
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	feed_id INTEGER,
	timestamp DATETIME,
	content TEXT,
	content_hash VARCHAR(50),
	url VARCHAR(50)
 */

	$id = getIdIfExists('SELECT id FROM items WHERE link=:link LIMIT 1', array(':link' => $item->link), 'id');
	if($id === false) {
		if (!isset($item->author)){
			$item->author = 'No Name';
		}
		getIdByInsert('items', array(
			'feed_id' => $item->feed_id,
			'title' => $item->title,
			'link' => $item->link,
			'description' => $item->description,
			'pubDate' => $item->pubDate,
			'author' => $item->author,
			'when_fetch' => $item->when_fetch
		));
	}
}

function fetchFeedItems($url, $url_id){
	try {
		$when_fetch = time();
		$feed = new SimplePie();
		$feed->set_feed_url($url);
		$feed->force_feed(true);
		$success = $feed->init();

		$feed->handle_content_type();

		foreach(array_reverse($feed->get_items()) as $item){
			$post = (object)(array(
				'link' => $item->get_permalink(),
				'title' => $item->get_title(),
				'pubDate' => $item->get_date('U'),
				'description' => $item->get_content(),
				'when_fetch' => $when_fetch
			));

			if($tmp = $item->get_author()){
				$post->author = $tmp->get_name();
			}

			if($post->pubDate == '' || ! $post->pubDate){
				$post->pubDate = $when_fetch;	
			}
			$post->feed_id = $url_id;
			updateFeedItem($post);
		}
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}

