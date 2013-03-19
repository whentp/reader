<?php

require_once 'db.php';

function feedExists($url){
	global $db;

	$sql = 'SELECT id, link FROM feeds WHERE link = :url';
	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute(array(':url' => $url));
	$rs = $conn->fetchAll();

	foreach($rs as $a){
		return $a['id'];
	}
	return false;
}

function feedAdd($link, $title='', $description=''){
	global $db;
	$feed_id = feedExists($link);
	if ($feed_id === false){
		$sql = 'INSERT INTO feeds(link, title, description) VALUES(:link, :title, :description)';
		$conn = $db->prepare($sql);
		$conn->execute(array(':link' => $link, ':title' => $title, ':description' => $description));
		$feed_id = $db->lastInsertId();
	}
	return $feed_id;
}

function outlineExists($title, $text, $user_id){
	global $db;

	$sql = 'SELECT id, title, text FROM outlines WHERE title = :title AND text = :text AND user_id = :user';
	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute(array(
		':title' => $title,
		':text' => $text,
		':user' => $user_id
	));
	$rs = $conn->fetchAll();

	foreach($rs as $a){
		return (int)$a['id'];
	}
	return false;
}

function outlineAdd($title='', $text='', $user_id=''){
	global $db;
	if (($outline_id = feedExists($title)) !== false){
		return $outline_id;
	}
	$sql = 'INSERT INTO outlines(text, title, user_id) VALUES(:text, :title, :user)';
	$conn = $db->prepare($sql);
	$conn->execute(array(
		':title' => $title,
		':text' => $text,
		':user' => $user_id
	));
	return (int)$db->lastInsertId();
}

function feedStatusAdd($feed_id, $outline_id, $user_id){
	global $db;

	$sql = 'INSERT INTO feed_statuses(feed_id, outline_id, user_id, read, read_until_id) VALUES(:feed_id, :outline_id, :user_id, 0, 0)';
	$conn = $db->prepare($sql);
	$conn->execute(array(
		':feed_id' => $feed_id,
		':outline_id' => $outline_id,
		':user_id' => $user_id
	));
}

function loadFromGoogleReaderFiles($filename, $user_id){
	$txt = file_get_contents($filename);
	importFromOpmlText($txt, $user_id);
	
}

function importFromOpmlText($text, $user_id){
	$txt = $text;
	$x = simplexml_load_string($txt);

	/*
	<outline title="English" text="English">
	    <outline text="FMyLife" title="FMyLife" type="rss"
		xmlUrl="http://feeds.feedburner.com/fmylife" htmlUrl="http://www.fmylife.com"/> 
	</outline>
	* */

	foreach($x->body->outline as $outlines){
		$tmp_attr = $outlines->attributes();
		$outline_id = outlineAdd($tmp_attr->title, $tmp_attr->text, $user_id);
		foreach($outlines as $outline)
		{
			$attr = $outline->attributes();
			$r = (object)(array(
				'title'=> (string) $attr->title,
				'link'=> (string) $attr->xmlUrl,
				'description'=> (string) $attr->text
			));
			$feed_id = feedAdd($r->link, $r->title, $r->description);
			feedStatusAdd($feed_id, $outline_id, $user_id);
		}
	}
}

function importFromGoogleReader($filename){
	if(getUserId() && getUserId() > 0){
		loadFromGoogleReaderFiles($filename, getUserId());
		return true;
	}
	return false;
}

